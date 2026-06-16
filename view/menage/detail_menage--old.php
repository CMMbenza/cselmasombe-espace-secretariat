<?php 
require_once ('../../layouts/constants/head.php');
require_once ('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/config.php');

/** Helpers */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt($n){ return number_format((float)$n, 2, '.', ' '); }

/** ID ménage */
$codeMenage = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($codeMenage <= 0) {
  echo '<div class="container mt-4"><div class="alert alert-danger">Ménage introuvable (id invalide).</div></div>';
  require_once ('../../layouts/constants/footer.php'); exit;
}

/* ===================== Ménage ===================== */
$sqlMenage = "
  SELECT 
    men.id, men.noms, men.telephone, men.numero, men.avenue, men.quartier, men.commune,
    men.dateCreated, men.dateUpdate, men.createdby, men.anneeScolaire, men.montantAPayer,
    (SELECT COUNT(*) FROM eleve e WHERE e.menage = men.id) AS nbreEnfant,
    (SELECT COALESCE(SUM(p.montantPayer),0) FROM paiement p WHERE p.menage = men.id) AS montantDejaPayerScol,
    /* Total payé DIVERS réel (pour l'affichage payé) */
    (SELECT COALESCE(SUM(pd.montantPayer),0) FROM paiement_divers pd WHERE pd.menage = men.id) AS totalDiversPayer
  FROM menage men
  WHERE men.id = ?
  LIMIT 1
";
$stM = $con->prepare($sqlMenage);
$stM->bind_param('i', $codeMenage);
$stM->execute();
$rsM = $stM->get_result();
if ($rsM->num_rows === 0) {
  echo '<div class="container mt-4"><div class="alert alert-warning">Ménage introuvable.</div></div>';
  require_once ('../../layouts/constants/footer.php'); exit;
}
$men = $rsM->fetch_assoc();
$stM->close();

/* Vars ménage */
$id             = (int)$men['id'];
$nom            = (string)$men['noms'];
$telephone      = (string)$men['telephone'];
$nbre           = (int)$men['nbreEnfant'];
$numero         = (string)$men['numero'];
$avenue         = (string)$men['avenue'];
$quartier       = (string)$men['quartier'];
$commune        = (string)$men['commune'];
$montantAPayer  = (float)$men['montantAPayer'];         // scolarité annuelle (famille, info menage)
$dateCreated    = (string)$men['dateCreated'];
$dateUpdate     = (string)$men['dateUpdate'];
$anneeScolaire  = (string)$men['anneeScolaire'];
$createdby      = (string)$men['createdby'];

$montantDejaPayerScol = (float)$men['montantDejaPayerScol']; // payé scolarité (réel)
$totalDiversPayer     = (float)$men['totalDiversPayer'];     // payé divers (réel)

/* ===================== Élèves (avec cycle) ===================== */
$sqlEleves = "
  SELECT 
    el.id, el.nom, el.postnom, el.prenom,
    cl.description AS classe,
    cy.id AS cycle_id, cy.description AS cycle
  FROM eleve el
  JOIN classe cl ON el.classe = cl.id
  JOIN cycle  cy ON cl.cycle  = cy.id
  WHERE el.menage = ?
  ORDER BY el.nom ASC
";
$stE = $con->prepare($sqlEleves);
$stE->bind_param('i', $id);
$stE->execute();
$rstEleve = $stE->get_result();
$eleves = [];
while ($r = $rstEleve->fetch_assoc()) $eleves[] = $r;
$stE->close();

/* ===================== Référence “Montant à payer (divers)” depuis cycle+scolarite ===================== */
/* Pour chaque élève -> cycle -> scolarite (même année), lignes dont description contient 'diver' ou 'connex' */
$diversAPayerRef = 0.0;
if (!empty($eleves)) {
  $sqlDiversRef = "
    SELECT COALESCE(SUM(s2.montant),0) AS total_divers_tarif
    FROM eleve e
    JOIN classe cl ON e.classe = cl.id
    JOIN cycle  cy ON cl.cycle  = cy.id
    JOIN scolarite s2 ON s2.cycle = cy.id AND s2.anneeScolaire = ?
    WHERE e.menage = ?
      AND (LOWER(s2.description) LIKE '%diver%' OR LOWER(s2.description) LIKE '%connex%')
  ";
  $stDR = $con->prepare($sqlDiversRef);
  $stDR->bind_param('si', $anneeScolaire, $id);
  $stDR->execute();
  $rsDR = $stDR->get_result();
  if ($row = $rsDR->fetch_assoc()) {
    $diversAPayerRef = (float)$row['total_divers_tarif'];
  }
  $stDR->close();
}

/* ===================== A PAYER PAR TRANCHE (scolaire par tranche) ===================== */
$apayerByTranche = [];   // num => montant total famille (scolaire)
$tranchesNums    = [];

if (!empty($eleves)) {
  $sqlAgg = "
    SELECT 
      t.numero_tranche AS num,
      SUM(t.montant)   AS total_tranche
    FROM eleve e
    JOIN classe cl ON e.classe = cl.id
    JOIN cycle  cy ON cl.cycle  = cy.id
    JOIN scolarite s ON s.cycle = cy.id AND s.anneeScolaire = ?
    JOIN tranche   t ON t.frais_id = s.id
    WHERE e.menage = ?
    GROUP BY t.numero_tranche
    ORDER BY t.numero_tranche
  ";
  $stAgg = $con->prepare($sqlAgg);
  $stAgg->bind_param('si', $anneeScolaire, $id);
  $stAgg->execute();
  $rsAgg = $stAgg->get_result();
  while ($row = $rsAgg->fetch_assoc()) {
    $num = (int)$row['num'];
    $apayerByTranche[$num] = (float)$row['total_tranche'];
    $tranchesNums[$num]    = true;
  }
  $stAgg->close();
}

/* >>> Garder une copie “scolaire uniquement” AVANT d’injecter les divers */
$apayerByTrancheScolOnly = $apayerByTranche;

/* ===================== Injection des DIVERS “référence” dans la 1ère tranche (robuste) ===================== */
/* Sûr que ce sont des tableaux */
if (!isset($apayerByTranche) || !is_array($apayerByTranche)) $apayerByTranche = [];
if (!isset($tranchesNums)    || !is_array($tranchesNums))    $tranchesNums    = [];

/* Déterminer la première tranche */
$trancheOneKey = 1; // défaut
if (!empty($tranchesNums)) {
  $numsTmp = array_keys($tranchesNums);
  $numsTmp = array_map('intval', $numsTmp);
  sort($numsTmp);
  $trancheOneKey = in_array(1, $numsTmp, true) ? 1 : (int)$numsTmp[0];
}
$trancheOneKey = (int)$trancheOneKey;

/* S'assurer de la clé, puis ajouter les divers de référence */
if (!isset($apayerByTranche[$trancheOneKey])) $apayerByTranche[$trancheOneKey] = 0.0;
$apayerByTranche[$trancheOneKey] += (float)$diversAPayerRef;

/* Marquer la tranche 1 comme présente */
$tranchesNums[$trancheOneKey] = true;

/* Ordonner et total “à payer” (après injection) */
$nums = array_keys($tranchesNums);
$nums = array_map('intval', $nums);
sort($nums);

$totalAPayerToutesTranches = 0.0;
foreach ($nums as $n) {
  $totalAPayerToutesTranches += (float)($apayerByTranche[$n] ?? 0.0);
}

/* ===================== Synthèse annuelle (basée sur tranches ajustées) ===================== */
$totalAnnuelAPayer = $totalAPayerToutesTranches;                    // scolaire + divers(ref) (dans tranche 1)
$totalAnnuelPaye   = $montantDejaPayerScol + $totalDiversPayer;     // payé scolarité + payé divers (réel)
$totalAnnuelReste  = max($totalAnnuelAPayer - $totalAnnuelPaye, 0.0);

/* ===================== Répartition du payé annuel par tranches (cascade) ===================== */
$paidByTranche  = []; // num => payé alloué
$resteByTranche = []; // num => reste
$pool = $totalAnnuelPaye;

foreach ($nums as $n) {
  $due  = (float)($apayerByTranche[$n] ?? 0.0);
  $pay  = min($pool, $due);
  $paidByTranche[$n]  = $pay;
  $resteByTranche[$n] = max($due - $pay, 0.0);
  $pool -= $pay;
}

/* ===================== Paiements (tables existantes) ===================== */
$stPS = $con->prepare("
  SELECT id, montantAPayer, montantPayer, resteAPayer, observation, dateCreated
  FROM paiement
  WHERE menage = ?
  ORDER BY dateCreated DESC, id DESC
");
$stPS->bind_param('i', $id);
$stPS->execute();
$rstsScol = $stPS->get_result();

$stPD = $con->prepare("
  SELECT id, type_frais, montantAPayer, montantPayer, resteAPayer, observation, dateCreated
  FROM paiement_divers
  WHERE menage = ?
  ORDER BY dateCreated DESC, id DESC
");
$stPD->bind_param('i', $id);
$stPD->execute();
$rstsDivers = $stPD->get_result();
?>

<body>
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <!-- Infos ménage -->
                <div class="col-lg-5 col-sm-12 grid-margin">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase d-flex align-items-center justify-content-between">
                                <span>Détails sur la famille (Ménage)</span>
                                <button type="button" class="btn btn-primary" onclick="history.back()">&lt;
                                    Retour</button>
                            </h5>
                            <hr>
                            <dl class="row-md jh-entity-details">
                                <dt>Noms</dt>
                                <dd><span><?= h($nom) ?>, <?= h($telephone) ?></span></dd>

                                <dt>Localisation</dt>
                                <dd><span>AV. <?= h($avenue) ?>, N° <?= h($numero) ?>, Q. <?= h($quartier) ?>, c.
                                        <?= h($commune) ?></span></dd>

                                <div class="d-flex">
                                    <div class="cols me-2">
                                        <dt>Date inscription :</dt>
                                        <dd><span><?= h($dateCreated) ?></span></dd>
                                    </div>
                                    <div class="d-none cols">
                                        <dt>Date Modification</dt>
                                        <dd><span><?= h($dateUpdate) ?></span></dd>
                                    </div>
                                </div>

                                <dt>Année scolaire</dt>
                                <dd><span><?= h($anneeScolaire) ?></span></dd>

                                <dt>Créer par</dt>
                                <dd><span><?= h($createdby) ?></span></dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Élèves -->
                <div class="col-md-7 col-sm-12">
                    <div class="col-lg-12 col-sm-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-uppercase">Élève (enfant)s</h5>
                                <hr>
                                <div class="row mb-3">
                                    <div class="table-responsive">
                                        <table class="table" id="myTableEleves">
                                            <thead>
                                                <tr>
                                                    <th>ID élève</th>
                                                    <th>Noms</th>
                                                    <th>Classe</th>
                                                    <!-- <th>Cycle</th> -->
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($eleves)): ?>
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="alert alert-warning mb-0">Aucun élève enregistré.
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php else: foreach ($eleves as $rowEleve): ?>
                                                <tr>
                                                    <td><a
                                                            href="../eleve/detail_eleve.php?id=<?= (int)$rowEleve['id']; ?>"><?= (int)$rowEleve['id']; ?></a>
                                                    </td>
                                                    <td><?= h($rowEleve['nom'].' '.$rowEleve['postnom'].' '.$rowEleve['prenom']) ?>
                                                    </td>
                                                    <td><?= h($rowEleve['classe']) ?> <?= h($rowEleve['cycle']) ?></td>
                                                    <!-- <td></td> -->
                                                    <td><a href="../eleve/detail_eleve.php?id=<?= (int)$rowEleve['id']; ?>"
                                                            class="btn btn-primary">Voir</a></td>
                                                </tr>
                                                <?php endforeach; endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row mt-3">
                                        <dt>Total : <?= (int)$nbre ?> enfant(s)</dt>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /élèves -->
            </div><!-- /row -->

            <!-- >>> CARTE : Montant par tranche (à payer / payé (annuel) / reste) + Synthèse annuelle dans la carte -->
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="col-lg-12 col-sm-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-uppercase">Montant par tranche (réf. cycles des élèves) — À
                                    payer / Payé (annuel) / Reste</h5>
                                <hr>

                                <?php if (empty($nums)): ?>
                                <div class="alert alert-info">
                                    Aucune tranche trouvée pour les cycles des élèves et l’année
                                    <strong><?= h($anneeScolaire) ?></strong>.
                                    Vérifiez <code>scolarite.cycle</code> / <code>scolarite.anneeScolaire</code> et
                                    <code>tranche.frais_id</code>.
                                </div>
                                <?php else: ?>

                                <!-- Synthèse ANNUELLE -->
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <dt>Montant annuel à payer</dt>
                                        <dd><span><?= fmt($totalAnnuelAPayer) ?></span> $</dd>
                                    </div>
                                    <div class="col-4">
                                        <dt>Montant payé (annuel)</dt>
                                        <dd class="text-primary"><span><?= fmt($totalAnnuelPaye) ?></span> $</dd>
                                    </div>
                                    <div class="col-4">
                                        <dt>Reste (annuel)</dt>
                                        <dd class="text-danger"><span><?= fmt($totalAnnuelReste) ?></span> $</dd>
                                    </div>
                                </div>

                                <div class="d-none row mb-2">
                                    <div class="col-6">
                                        <dt>Année scolaire</dt>
                                        <dd><span><?= h($anneeScolaire) ?></span></dd>
                                    </div>
                                    <div class="col-6">
                                        <dt>Total “À payer” (toutes tranches)</dt>
                                        <dd><span><?= fmt($totalAPayerToutesTranches) ?></span> $</dd>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table" id="tableMontantParTranche">
                                        <thead>
                                            <tr>
                                                <th>Tranche</th>
                                                <th>À payer</th>
                                                <th>Payé (annuel alloué)</th>
                                                <th>Reste</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                      $totPaidAlloc = 0.0; 
                      $totReste     = 0.0;
                      foreach ($nums as $num):
                        $due  = (float)($apayerByTranche[$num] ?? 0.0);
                        $pay  = (float)($paidByTranche[$num] ?? 0.0);
                        $rest = (float)($resteByTranche[$num] ?? max($due - $pay, 0.0));
                        $totPaidAlloc += $pay;
                        $totReste     += $rest;

                        // Pour libellé Tranche 1 : afficher aussi le montant de la tranche (scolaire) avant injection
                        $labelSuffix = '';
                        if ((int)$num === (int)$trancheOneKey) {
                          $scolOnly = (float)($apayerByTrancheScolOnly[$trancheOneKey] ?? 0.0);
                          $labelSuffix = ' <small class="text-muted">(incl. frais connexes: '.fmt($diversAPayerRef).' $; tranche: '.fmt($scolOnly).' $)</small>';
                        }
                    ?>
                                            <tr>
                                                <td>Tranche <?= (int)$num ?><?= $labelSuffix ?></td>
                                                <td><?= fmt($due) ?> $</td>
                                                <td class="<?= $rest == 0.0 ? 'text-primary' : '' ?>"><?= fmt($pay) ?> $
                                                </td>
                                                <td class="<?= $rest > 0.0 ? 'text-danger' : '' ?>"><?= fmt($rest) ?> $
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-end">Totaux :</th>
                                                <th><?= fmt($totalAPayerToutesTranches) ?> $</th>
                                                <th><?= fmt($totPaidAlloc) ?> $</th>
                                                <th><?= fmt($totReste) ?> $</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <?php if ($pool > 0.0): ?>
                                <div class="alert alert-success mt-2">
                                    Surplus payé non affecté (au-delà de toutes les tranches) :
                                    <strong><?= fmt($pool) ?> $</strong>.
                                </div>
                                <?php endif; ?>

                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <<< FIN CARTE TRANCHES -->

            <!-- Paiements scolarité (cumul) -->
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="col-lg-12 col-sm-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-uppercase">Paiements scolarité</h5>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <dt>Montant à payer (scol.) :</dt>
                                        <dd><span><?= fmt($montantAPayer) ?></span> $</dd>
                                    </div>
                                    <div class="col-4">
                                        <dt>Montant payé (cumul scol.) :</dt>
                                        <dd class="text-primary"><span><?= fmt($montantDejaPayerScol) ?></span> $</dd>
                                    </div>
                                    <div class="col-4">
                                        <dt>Solde scolarité :</dt>
                                        <dd class="text-danger">
                                            <span><?= fmt(max($montantAPayer - $montantDejaPayerScol, 0.0)) ?></span> $
                                        </dd>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="table-responsive">
                                        <table class="table" id="myTablePaieScol">
                                            <thead>
                                                <tr>
                                                    <th>Reçu</th>
                                                    <th>Montant payé</th>
                                                    <th>Solde</th>
                                                    <th>Obs.</th>
                                                    <th>Date paiement</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($rstsScol->num_rows === 0): ?>
                                                <tr>
                                                    <td colspan="6">
                                                        <div class="alert alert-info mb-0">Aucun paiement scolarité.
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php else: while ($row = $rstsScol->fetch_assoc()):
                          $paiementId = (int)$row['id']; $toggleId = "detail-paiement-scol-".$paiementId;
                    ?>
                                                <tr>
                                                    <td><?= $paiementId ?><button
                                                            class="d-none btn btn-sm btn-secondary toggle-btn"
                                                            data-toggle="#<?= h($toggleId) ?>">▼</button></td>
                                                    <td><?= fmt($row['montantPayer']) ?> $</td>
                                                    <td><?= fmt($row['resteAPayer']) ?> $</td>
                                                    <td><?= h($row['observation']) ?></td>
                                                    <td><?= h($row['dateCreated']) ?></td>
                                                    <td><a href="../paiement-divers-frais/apercu_recu.php?ordre=<?= $paiementId ?>"
                                                            class="btn btn-danger" target="_blank">Imprimer</a></td>
                                                </tr>

                                                <!-- Détails (élèves / tranches) -->
                                                <tr id="<?= h($toggleId) ?>" style="display:none;">
                                                    <td colspan="6">
                                                        <table class="table table-bordered table-sm mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Élève</th>
                                                                    <th>Tranche</th>
                                                                    <th>Montant payé</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                              $sqlDetails = "
                                SELECT e.nom, e.postnom, e.prenom, t.numero_tranche, pd.montant
                                FROM paiement_detail pd
                                JOIN eleve   e ON pd.eleve_id  = e.id
                                LEFT JOIN tranche t ON pd.tranche_id = t.id
                                WHERE pd.paiement_id = ?
                                ORDER BY e.nom ASC, t.numero_tranche ASC
                              ";
                              $stD = $con->prepare($sqlDetails);
                              $stD->bind_param('i', $paiementId);
                              $stD->execute();
                              $details = $stD->get_result();
                              if ($details->num_rows === 0) {
                                echo '<tr><td colspan="3"><em>Aucun détail pour ce reçu.</em></td></tr>';
                              } else {
                                while ($det = $details->fetch_assoc()) {
                                  echo '<tr>';
                                  echo '<td>'.h($det['nom'].' '.$det['postnom'].' '.$det['prenom']).'</td>';
                                  echo '<td>'.($det['numero_tranche'] !== null ? 'Tranche '.h($det['numero_tranche']) : '-').'</td>';
                                  echo '<td>'.fmt($det['montant']).' $</td>';
                                  echo '</tr>';
                                }
                              }
                              $stD->close();
                            ?>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <?php endwhile; endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div><!-- /scol -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paiements DIVERS -->
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="col-lg-12 col-sm-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-uppercase">Paiements frais connexe</h5>
                                <hr>

                                <!-- Résumé DIVERS (référence scolarite/cycle) -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <dt>Montant à payer (divers)</dt>
                                        <dd><span><?= fmt($diversAPayerRef) ?></span> $</dd>
                                    </div>
                                    <div class="col-md-4">
                                        <dt>Montant payé (divers)</dt>
                                        <dd class="text-primary"><span><?= fmt($totalDiversPayer) ?></span> $</dd>
                                    </div>
                                    <div class="col-md-4">
                                        <dt>Reste (divers)</dt>
                                        <dd class="text-danger">
                                            <span><?= fmt(max($diversAPayerRef - $totalDiversPayer, 0.0)) ?></span> $
                                        </dd>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="table-responsive">
                                        <table class="table" id="myTablePaieDivers">
                                            <thead>
                                                <tr>
                                                    <th>Reçu</th>
                                                    <th class="d-none">Type de frais</th>
                                                    <th class="d-none">Montant à payer</th>
                                                    <th>Montant payé</th>
                                                    <th class="d-none">Reste</th>
                                                    <th>Obs.</th>
                                                    <th>Date paiement</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                      $sumDiversAPayerRows = 0.0; 
                      $sumDiversPayerRows  = 0.0; 
                      if ($rstsDivers->num_rows === 0): ?>
                                                <tr>
                                                    <td colspan="8">
                                                        <div class="alert alert-info mb-0">Aucun paiement frais connexe.
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php else: 
                      while ($row = $rstsDivers->fetch_assoc()):
                        $pdId = (int)$row['id']; 
                        $toggleId = "detail-paiement-divers-".$pdId;

                        $sumDiversAPayerRows += (float)$row['montantAPayer'];
                        $sumDiversPayerRows  += (float)$row['montantPayer'];
                        $resteLigne = max((float)$row['montantAPayer'] - (float)$row['montantPayer'], 0);
                    ?>
                                                <tr>
                                                    <td><?= $pdId ?><button
                                                            class="d-none btn btn-sm btn-secondary toggle-btn"
                                                            data-toggle="#<?= h($toggleId) ?>">▼</button></td>
                                                    <td class="d-none"><?= h($row['type_frais']) ?></td>
                                                    <td class="d-none"><?= fmt($row['montantAPayer']) ?> $</td>
                                                    <td><?= fmt($row['montantPayer']) ?> $</td>
                                                    <td class="d-none"><?= fmt($resteLigne) ?> $</td>
                                                    <td><?= h($row['observation']) ?></td>
                                                    <td><?= h($row['dateCreated']) ?></td>
                                                    <td><a href="../paiement-divers-frais/apercu_recu_divers.php?ordre=<?= $pdId ?>"
                                                            class="btn btn-danger" target="_blank">Imprimer</a></td>
                                                </tr>

                                                <!-- Détails (par élève) -->
                                                <tr id="<?= h($toggleId) ?>" style="display:none;">
                                                    <td colspan="8">
                                                        <table class="table table-bordered table-sm mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Élève</th>
                                                                    <th>Montant Frais</th>
                                                                    <th>Montant Payé</th>
                                                                    <th>Solde</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                              $sqlDetailsDivers = "
                                SELECT e.nom, e.postnom, e.prenom, ped.montantFrais, ped.montantPaye, ped.solde
                                FROM paiement_eleve_divers ped
                                JOIN eleve e ON ped.eleve_id = e.id
                                WHERE ped.paiement_id = ?
                                ORDER BY e.nom ASC
                              ";
                              $stDD = $con->prepare($sqlDetailsDivers);
                              $stDD->bind_param('i', $pdId);
                              $stDD->execute();
                              $detailsD = $stDD->get_result();
                              if ($detailsD->num_rows === 0) {
                                echo '<tr><td colspan="4"><em>Aucun détail pour ce reçu divers.</em></td></tr>';
                              } else {
                                while ($det = $detailsD->fetch_assoc()) {
                                  echo '<tr>';
                                  echo '<td>'.h($det['nom'].' '.$det['postnom'].' '.$det['prenom']).'</td>';
                                  echo '<td>'.fmt($det['montantFrais']).' $</td>';
                                  echo '<td>'.fmt($det['montantPaye']).' $</td>';
                                  echo '<td>'.fmt($det['solde']).' $</td>';
                                  echo '</tr>';
                                }
                              }
                              $stDD->close();
                            ?>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <?php endwhile; 
                      endif; 
                    ?>
                                            </tbody>
                                            <tfoot class="d-none">
                                                <?php 
                        $sumDiversResteRows = max($sumDiversAPayerRows - $sumDiversPayerRows, 0.0); 
                      ?>
                                                <tr>
                                                    <th colspan="2" class="text-end">Totaux (sur reçus saisis) :</th>
                                                    <th><?= fmt($sumDiversAPayerRows) ?> $</th>
                                                    <th><?= fmt($sumDiversPayerRows) ?> $</th>
                                                    <th><?= fmt($sumDiversResteRows) ?> $</th>
                                                    <th colspan="3"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div><!-- /divers -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /content-wrapper -->

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const targetSel = btn.getAttribute('data-toggle');
                    const targetRow = document.querySelector(targetSel);
                    if (!targetRow) return;
                    const hidden = (targetRow.style.display === 'none' || targetRow.style
                        .display === '');
                    targetRow.style.display = hidden ? '' : 'none';
                    btn.textContent = hidden ? 'Masquer détail' : 'Voir détail';
                });
            });
        });
        </script>

        <?php require_once ('../../layouts/constants/footer.php'); ?>
</body>