<?php

/** Elèves + cycles (utilisé pour savoir quels cycles participent) */
function fetch_eleves_cycles(mysqli $con, int $menageId): array {
  $sql = "
    SELECT e.id AS eleve_id, cy.id AS cycle_id
    FROM eleve e
    JOIN classe cl ON e.classe = cl.id
    JOIN cycle  cy ON cl.cycle  = cy.id
    WHERE e.menage = ?
  ";
  $st = $con->prepare($sql);
  $st->bind_param('i', $menageId);
  $st->execute();
  $rs = $st->get_result();
  $rows = [];
  while ($r = $rs->fetch_assoc()) $rows[] = $r;
  $st->close();
  return $rows;
}

/** Montant “divers de référence” (tarifs scolarite/cycle de l’année ménage : description LIKE diver/connex) */
function fetch_divers_ref(mysqli $con, int $menageId, string $anneeScolaire): float {
  $sql = "
    SELECT COALESCE(SUM(s2.montant),0) AS total_divers_tarif
    FROM eleve e
    JOIN classe cl ON e.classe = cl.id
    JOIN cycle  cy ON cl.cycle  = cy.id
    JOIN scolarite s2 ON s2.cycle = cy.id AND s2.anneeScolaire = ?
    WHERE e.menage = ?
      AND (LOWER(s2.description) LIKE '%diver%' OR LOWER(s2.description) LIKE '%connex%')
  ";
  $st = $con->prepare($sql);
  $st->bind_param('si', $anneeScolaire, $menageId);
  $st->execute();
  $rs = $st->get_result();
  $val = 0.0;
  if ($row = $rs->fetch_assoc()) $val = (float)$row['total_divers_tarif'];
  $st->close();
  return $val;
}

/** Somme “à payer” par tranche (scolarité) pour TOUS les élèves du ménage */
function fetch_apayer_by_tranche(mysqli $con, int $menageId, string $anneeScolaire): array {
  $out = [];
  $sql = "
    SELECT t.numero_tranche AS num, SUM(t.montant) AS total_tranche
    FROM eleve e
    JOIN classe cl ON e.classe = cl.id
    JOIN cycle  cy ON cl.cycle  = cy.id
    JOIN scolarite s ON s.cycle = cy.id AND s.anneeScolaire = ?
    JOIN tranche   t ON t.frais_id = s.id
    WHERE e.menage = ?
    GROUP BY t.numero_tranche
    ORDER BY t.numero_tranche
  ";
  $st = $con->prepare($sql);
  $st->bind_param('si', $anneeScolaire, $menageId);
  $st->execute();
  $rs = $st->get_result();
  while ($row = $rs->fetch_assoc()) {
    $out[(int)$row['num']] = (float)$row['total_tranche'];
  }
  $st->close();
  ksort($out);
  return $out;
}

/** Construit la grille par ménage : tranches => (apayer, paye, reste) + totaux */
function build_grid(mysqli $con, array $m): array {
  $id         = (int)$m['id'];
  $annee      = (string)$m['anneeScolaire'];
  $payeScol   = (float)$m['montantDejaPayerScol'];
  $payeDivers = (float)$m['totalDiversPayer'];

  // 👉 NOUVEAU : start_tranche du ménage
  $start = isset($m['start_tranche']) ? (int)$m['start_tranche'] : 1;
  if ($start <= 0) $start = 1;

  $eleves = fetch_eleves_cycles($con, $id);
  $apayerByTranche = (!empty($eleves)) ? fetch_apayer_by_tranche($con, $id, $annee) : [];
  $apayerScolOnly  = $apayerByTranche;

  // 👉 Filtrer : ignorer les tranches avant start
  foreach ($apayerByTranche as $n => $val) {
    if ($n < $start) {
      $apayerByTranche[$n] = 0.0;
    }
  }

  // 👉 Divers
  $diversRef = (!empty($eleves)) ? fetch_divers_ref($con, $id, $annee) : 0.0;

  // 👉 IMPORTANT : la première tranche = start_tranche
  $trancheOneKey = $start;

  if (!isset($apayerByTranche[$trancheOneKey])) {
    $apayerByTranche[$trancheOneKey] = 0.0;
  }

  // 👉 Ajouter les frais connexes ici
  $apayerByTranche[$trancheOneKey] += (float)$diversRef;

  // Trier
  $nums = array_keys($apayerByTranche);
  $nums = array_map('intval', $nums);
  sort($nums);

  // Totaux
  $totalAPayer = 0.0;
  foreach ($nums as $n) $totalAPayer += (float)$apayerByTranche[$n];

  $totalPaye   = $payeScol + $payeDivers;
  $totalReste  = max($totalAPayer - $totalPaye, 0.0);

  // 👉 Paiement commence à partir de start
  $paidLeft = $totalPaye;
  $paidBy = []; 
  $resteBy = [];

  foreach ($nums as $n) {

    $due = (float)$apayerByTranche[$n];

    if ($n < $start) {
      $paidBy[$n]  = 0.0;
      $resteBy[$n] = 0.0;
      continue;
    }

    $pay = min($paidLeft, $due);
    $paidBy[$n]  = $pay;
    $resteBy[$n] = max($due - $pay, 0.0);

    $paidLeft -= $pay;
  }

  $totalScolaireOnly = 0;

    foreach ($apayerScolOnly as $num => $montant) {

        if ($num >= $start) {
            $totalScolaireOnly += $montant;
        }

    }

  return [
    'nums'        => $nums,
    'apayer'      => $apayerByTranche,
    'paidBy'      => $paidBy,
    'resteBy'     => $resteBy,

    'totalScolaireOnly' => $totalScolaireOnly,

    'totalAPayer' => $totalAPayer,
    'totalPaye'   => $totalPaye,
    'totalReste'  => $totalReste,
  ];
}

function getInventaire($con, $whereClause)
{
    $sql = "
    SELECT
        m.id,
        m.noms,

        m.anneeScolaire,
        m.start_tranche,

        GROUP_CONCAT(
            DISTINCT CONCAT(e.nom,' ',e.postnom,' ',e.prenom)
            SEPARATOR '<br>'
        ) AS enfants,

        GROUP_CONCAT(
            DISTINCT CONCAT(c.description,' (',cy.description,')')
            SEPARATOR '<br>'
        ) AS classes,

        m.montantAPayer AS scolaire_a_payer,

        COALESCE(ps.total_paye,0) AS scolaire_paye,

        (m.montantAPayer - COALESCE(ps.total_paye,0)) AS scolaire_reste,

        COALESCE(pc.connexe_a_payer,0) AS connexe_a_payer,

        COALESCE(pc.connexe_paye,0) AS connexe_paye,

        (COALESCE(pc.connexe_a_payer,0) - COALESCE(pc.connexe_paye,0)) AS connexe_reste

    FROM menage m

    LEFT JOIN eleve e
        ON e.menage = m.id

    LEFT JOIN classe c
        ON c.id = e.classe

    LEFT JOIN cycle cy
        ON cy.id = c.cycle

    LEFT JOIN (
        SELECT
            menage,
            SUM(montantPayer) total_paye
        FROM paiement
        GROUP BY menage
    ) ps ON ps.menage=m.id

    LEFT JOIN (
        SELECT
            p1.menage,

            (
                SELECT montantAPayer
                FROM paiement_divers p2
                WHERE p2.menage = p1.menage
                ORDER BY p2.id ASC
                LIMIT 1
            ) AS connexe_a_payer,

            SUM(p1.montantPayer) AS connexe_paye

        FROM paiement_divers p1
        GROUP BY p1.menage
    ) pc ON pc.menage = m.id

    $whereClause

    GROUP BY m.id
    ORDER BY m.noms
    ";

    return mysqli_query($con, $sql);
}
?>