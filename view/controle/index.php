<?php
require_once('../../layouts/constants/head.php');
require_once('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/config.php');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$con->set_charset('utf8mb4');

/* ================= Helpers ================= */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt($n){ return number_format((float)$n, 2, '.', ' '); }

/* ================= Requête de base — ménages ================= */
$sqlMenages = "
  SELECT 
    men.id, men.noms, men.anneeScolaire, men.montantAPayer,
    men.start_tranche,
    (SELECT COUNT(*) FROM eleve e WHERE e.menage = men.id) AS nbreEnfant,
    (SELECT COALESCE(SUM(p.montantPayer),0) FROM paiement p WHERE p.menage = men.id) AS montantDejaPayerScol,
    (SELECT COALESCE(SUM(pd.montantPayer),0) FROM paiement_divers pd WHERE pd.menage = men.id) AS totalDiversPayer
  FROM menage men 
  WHERE men.status = 'actif' AND men.noms NOT LIKE '%PERSONNEL%'
  ORDER BY men.noms ASC, men.id ASC
";

$MENAGES = [];
$rs = $con->query($sqlMenages);
while ($r = $rs->fetch_assoc()) $MENAGES[] = $r;
$rs->close();

/* ================= Fonctions calcul (identique à la fiche ménage) ================= */

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

  return [
    'nums'        => $nums,
    'apayer'      => $apayerByTranche,
    'paidBy'      => $paidBy,
    'resteBy'     => $resteBy,
    'totalAPayer' => $totalAPayer,
    'totalPaye'   => $totalPaye,
    'totalReste'  => $totalReste,
  ];
}

/* ================= Construire toutes les grilles + déterminer le nb max de tranches ================= */
$GRIDS = [];      // menage_id => grid
$MAX_TR = 0;      // nb max de colonnes tranches à afficher
foreach ($MENAGES as $m) {
  $g = build_grid($con, $m);
  $GRIDS[(int)$m['id']] = $g;
  if (!empty($g['nums'])) {
    $MAX_TR = max($MAX_TR, (int)max($g['nums']));
  }
}
if ($MAX_TR === 0) $MAX_TR = 1; // au moins 1 colonne

$filterTranches = $_GET['tranche'] ?? []; // array
$filterTranches = array_map('intval', $filterTranches);

if (isset($_GET['export']) && $_GET['export']==1) {

    $colsToShow = !empty($filterTranches) ? $filterTranches : range(1,$MAX_TR);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=controle_menages.csv');

    $output = fopen('php://output','w');

    // ===== INITIALISATION TOTAUX =====
    $grandDue  = 0.0;
    $grandPaid = 0.0;
    $grandRest = 0.0;

    $grandDueByTranche  = [];
    $grandPaidByTranche = [];
    $grandRestByTranche = [];

    foreach ($colsToShow as $n) {
        $grandDueByTranche[$n]  = 0.0;
        $grandPaidByTranche[$n] = 0.0;
        $grandRestByTranche[$n] = 0.0;
    }

    // Header
    $header = ['ID','Ménage','Nb enfants'];
    foreach ($colsToShow as $n) {
        $header[] = "Tranche $n (payé / à payer / reste)";
    }
    $header = array_merge($header,['Total à payer','Total payé','Total reste']);
    fputcsv($output,$header,';');

    // ===== LIGNES =====
    foreach ($MENAGES as $m) {

        $id   = (int)$m['id'];
        $name = (string)$m['noms'];
        $kids = (int)$m['nbreEnfant'];
        $g    = $GRIDS[$id] ?? null;
        if (!$g) continue;

        $row = [$id,$name,$kids];

        $dueTot = 0.0;
        $paidTot = 0.0;
        $restTot = 0.0;

        foreach ($colsToShow as $n) {

            $due  = $g['apayer'][$n] ?? 0.0;
            $pay  = $g['paidBy'][$n] ?? 0.0;
            $rest = $g['resteBy'][$n] ?? max($due-$pay,0.0);

            $row[] = fmt($pay).' / '.fmt($due).' / '.fmt($rest);

            $dueTot  += $due;
            $paidTot += $pay;
            $restTot += $rest;

            $grandDueByTranche[$n]  += $due;
            $grandPaidByTranche[$n] += $pay;
            $grandRestByTranche[$n] += $rest;
        }

        $grandDue  += $dueTot;
        $grandPaid += $paidTot;
        $grandRest += $restTot;

        $row[] = fmt($dueTot);
        $row[] = fmt($paidTot);
        $row[] = fmt($restTot);

        fputcsv($output,$row,';');
    }

    // ===== LIGNE TOTAL GÉNÉRAL =====
    $totalRow = ['','','TOTAL GÉNÉRAL'];

    foreach ($colsToShow as $n) {
        $totalRow[] =
            fmt($grandPaidByTranche[$n]).' / '.
            fmt($grandDueByTranche[$n]).' ('.
            fmt($grandRestByTranche[$n]).')';
    }

    $totalRow[] = fmt($grandDue);
    $totalRow[] = fmt($grandPaid);
    $totalRow[] = fmt($grandRest);

    fputcsv($output,$totalRow,';');

    fclose($output);
    exit;
}

?>
<div class="main-panel">
    <div class="content-wrapper">

        <?php if (empty($MENAGES)): ?>
        <div class="alert alert-info">Aucune famille.</div>
        <?php else: ?>
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <?php
                                $filterTranches = isset($_GET['tranche']) ? $_GET['tranche'] : []; // tableau
                                if (!is_array($filterTranches)) $filterTranches = [$filterTranches]; 
                                $filterTranches = array_map('intval', $filterTranches);
                            ?>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h4 class="card-title mb-2">Contrôle — Synthèse par ménage (Tranches en colonnes)</h4>
                                <p class="text-muted">
                                    Calculs alignés avec la fiche ménage : <em>frais connexes (référence)</em> injectés
                                    dans la
                                    <strong>Tranche 1</strong>,
                                    et ventilation du <strong>payé annuel</strong> (scolarité + divers payés)
                                    séquentiellement
                                    tranche par tranche.
                                </p>
                                <form method="get">
                                    <label class="form-label fw-bold">Filtrer par tranche</label>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <?php for ($n=1; $n <= $MAX_TR; $n++): ?>
                                        <div class="form-check me-2">
                                            <input class="form-check-input" type="checkbox" name="tranche[]"
                                                value="<?= $n ?>" <?= in_array($n, $filterTranches) ? 'checked' : '' ?>>
                                            <label class="form-check-label">
                                                Tranche <?= $n ?>
                                            </label>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
                                    <button type="submit" name="export" value="1" class="btn btn-success btn-sm ms-2">
                                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                                    </button>
                                    <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="btn btn-danger ms-2">
                                        Réinitialiser
                                    </a>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:70px;">ID</th>
                                        <th>Ménage</th>
                                        <th class="text-end">Nb enfants</th>
                                        <?php
                                            $colsToShow = $filterTranches && !in_array(0,$filterTranches,true) ? $filterTranches : range(1,$MAX_TR);
                                            foreach ($colsToShow as $n):
                                            ?>
                                        <th class="text-end">
                                            Tranche <?= $n ?><br>
                                            <small class="text-muted">payé / à payer <span
                                                    class="text-danger">(reste)</span></small>
                                        </th>
                                        <?php endforeach; ?>
                                        <th class="text-end">Total à payer</th>
                                        <th class="text-end text-primary">Total payé</th>
                                        <th class="text-end text-danger">Total reste</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $grandDue  = 0.0;
                                        $grandPaid = 0.0;
                                        $grandRest = 0.0;

                                        $grandDueByTranche  = [];
                                        $grandPaidByTranche = [];
                                        $grandRestByTranche = [];

                                        foreach ($colsToShow as $n) {
                                            $grandDueByTranche[$n]  = 0.0;
                                            $grandPaidByTranche[$n] = 0.0;
                                            $grandRestByTranche[$n] = 0.0;
                                        }
                                    ?>
                                    <?php foreach ($MENAGES as $m): 
                                        $id   = (int)$m['id'];
                                        $name = (string)$m['noms'];
                                        $kids = (int)$m['nbreEnfant'];
                                        $g    = $GRIDS[$id] ?? null;
                                        if (!$g) continue;

                                        $dueTot = $paidTot = $restTot = 0.0;
                                    ?>

                                    <tr>
                                        <td><?= $id ?></td>
                                        <td><?= h($name) ?></td>
                                        <td class="text-end"><?= $kids ?></td>

                                        <?php foreach ($colsToShow as $n): 
                                            $due  = $g['apayer'][$n] ?? 0.0;
                                            $pay  = $g['paidBy'][$n] ?? 0.0;
                                            $rest = $g['resteBy'][$n] ?? max($due - $pay,0.0);

                                            $dueTot  += $due;
                                            $paidTot += $pay;
                                            $restTot += $rest;

                                            $grandDueByTranche[$n]  += $due;
                                            $grandPaidByTranche[$n] += $pay;
                                            $grandRestByTranche[$n] += $rest;
                                        ?>
                                        <td class="text-end">
                                            <?= fmt($pay) ?> / <?= fmt($due) ?> <span
                                                class="text-danger">(<?= fmt($rest) ?>)</span>
                                        </td>
                                        <?php endforeach; ?>

                                        <?php
                                            $grandDue  += $dueTot;
                                            $grandPaid += $paidTot;
                                            $grandRest += $restTot;
                                        ?>

                                        <td class="text-end"><?= fmt($dueTot) ?> $</td>
                                        <td class="text-end text-primary"><?= fmt($paidTot) ?> $</td>
                                        <td class="text-end text-danger"><?= fmt($restTot) ?> $</td>
                                    </tr>

                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background:#1f2937; font-weight:bold;">
                                        <td colspan="3" class="text-end text-light">TOTAL GÉNÉRAL</td>

                                        <?php foreach ($colsToShow as $n): ?>
                                        <td class="text-end text-light">
                                            <?= fmt($grandPaidByTranche[$n]) ?> /
                                            <?= fmt($grandDueByTranche[$n]) ?>
                                            (<?= fmt($grandRestByTranche[$n]) ?>)
                                        </td>
                                        <?php endforeach; ?>

                                        <td class="text-end text-light"><?= fmt($grandDue) ?> $</td>
                                        <td class="text-end text-primary"><?= fmt($grandPaid) ?> $</td>
                                        <td class="text-end text-danger"><?= fmt($grandRest) ?> $</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once('../../layouts/constants/footer.php'); ?>