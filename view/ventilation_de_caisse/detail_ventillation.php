<?php
// detail_ventillation.php
require_once('../../layouts/constants/head.php');
require_once('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/config.php');

/* Helpers */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt($n){ return number_format((float)$n, 2, '.', ' '); }

/**
 * date_fr: formate 'YYYY-MM-DD' en français pour l'utilisateur.
 * Ex: '2025-10-20' -> 'lundi 20 octobre 2025'
 * Si besoin d’une version courte, on peut faire une variante.
 */
function date_fr(string $ymd, bool $long = true): string {
  if (!preg_match('~^\d{4}-\d{2}-\d{2}$~', $ymd)) return h($ymd);
  [$Y,$m,$d] = explode('-', $ymd);
  $ts = @strtotime("$ymd 00:00:00");
  if ($ts === false) return h($ymd);

  // Noms FR (sans dépendre de setlocale)
  $jours  = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
  $mois   = [1=>'janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];

  $w = (int)date('w', $ts); // 0..6
  $day = (int)$d;
  $monthName = $mois[(int)$m] ?? $m;

  if ($long) {
    return $jours[$w].' '.$day.' '.$monthName.' '.$Y;
  } else {
    // format court : 20 oct. 2025
    $moisCourt = [
      1=>'janv.',2=>'févr.',3=>'mars',4=>'avr.',5=>'mai',6=>'juin',
      7=>'juil.',8=>'août',9=>'sept.',10=>'oct.',11=>'nov.',12=>'déc.'
    ];
    $mC = $moisCourt[(int)$m] ?? $m;
    return $day.' '.$mC.' '.$Y;
  }
}

/* Paramètres requis */
$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
$date = isset($_GET['date']) ? trim($_GET['date']) : '';

$errors = [];
if (!in_array($type, ['scolaire','connexe'], true)) {
  $errors[] = "Paramètre 'type' invalide (attendu : scolaire | connexe).";
}
if (!preg_match('~^\d{4}-\d{2}-\d{2}$~', $date)) {
  $errors[] = "Paramètre 'date' invalide (format attendu : YYYY-MM-DD).";
}

/* Gestion erreurs */
if ($errors) {
  echo '<div class="container mt-4">';
  foreach ($errors as $e) {
    echo '<div class="alert alert-danger">'.h($e).'</div>';
  }
  echo '<a href="javascript:history.back()" class="btn btn-secondary">Retour</a>';
  echo '</div>';
  require_once('../../layouts/constants/footer.php');
  exit;
}

/* Récupération données */
$entries = []; // ENTRÉES du jour
$exits   = []; // DÉPENSES du jour
$totalE  = 0.0;
$totalD  = 0.0;

if ($type === 'scolaire') {
  // ENTRÉES = paiements scolarité (paiement) à la date
  $sqlE = "
    SELECT p.id, m.noms AS menage_nom, p.montantPayer AS montant, p.dateCreated AS dte, p.observation
    FROM paiement p
    JOIN menage m ON m.id = p.menage
    WHERE p.dateCreated = ?
    ORDER BY p.id DESC
  ";
  if ($st = $con->prepare($sqlE)) {
    $st->bind_param('s', $date);
    $st->execute();
    $rs = $st->get_result();
    while ($r = $rs->fetch_assoc()) {
      $entries[] = $r;
      $totalE += (float)$r['montant'];
    }
    $st->close();
  }

  // DÉPENSES = références/description contenant "scol"
  $sqlD = "
    SELECT d.id, d.beneficiaire, d.reference, d.description, d.montant, d.dateCreaty AS dte
    FROM depenses d
    WHERE d.dateCreaty = ?
      AND (LOWER(d.reference) LIKE '%scol%' OR LOWER(d.description) LIKE '%scol%')
    ORDER BY d.id DESC
  ";
  if ($st = $con->prepare($sqlD)) {
    $st->bind_param('s', $date);
    $st->execute();
    $rs = $st->get_result();
    while ($r = $rs->fetch_assoc()) {
      $exits[] = $r;
      $totalD += (float)$r['montant'];
    }
    $st->close();
  }
} else {
  // type = 'connexe'
  // ENTRÉES = paiements divers (paiement_divers) à la date
  $sqlE = "
    SELECT pdv.id, m.noms AS menage_nom, pdv.montantPayer AS montant, DATE(pdv.dateCreated) AS dte, pdv.observation
    FROM paiement_divers pdv
    JOIN menage m ON m.id = pdv.menage
    WHERE DATE(pdv.dateCreated) = ?
    ORDER BY pdv.id DESC
  ";
  if ($st = $con->prepare($sqlE)) {
    $st->bind_param('s', $date);
    $st->execute();
    $rs = $st->get_result();
    while ($r = $rs->fetch_assoc()) {
      $entries[] = $r;
      $totalE += (float)$r['montant'];
    }
    $st->close();
  }

  // DÉPENSES = référence exacte 'Frais Connexe'
  $sqlD = "
    SELECT d.id, d.beneficiaire, d.reference, d.description, d.montant, d.dateCreaty AS dte
    FROM depenses d
    WHERE d.dateCreaty = ?
      AND d.reference = 'Frais Connexe'
    ORDER BY d.id DESC
  ";
  if ($st = $con->prepare($sqlD)) {
    $st->bind_param('s', $date);
    $st->execute();
    $rs = $st->get_result();
    while ($r = $rs->fetch_assoc()) {
      $exits[] = $r;
      $totalD += (float)$r['montant'];
    }
    $st->close();
  }
}

$title = ($type === 'scolaire') ? "Détail — Frais Scolaire" : "Détail — Frais Connexe";
$dateAff = date_fr($date, true); // version longue FR
?>
<body>
<div class="main-panel">
  <div class="content-wrapper">

    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">

            <h5 class="card-title d-flex align-items-center justify-content-between">
              <span><?= h($title) ?> — <small class="text-muted"><?= h($dateAff) ?></small></span>
              <a href="javascript:history.back()" class="btn btn-light">&lt; Retour</a>
            </h5>
            <hr>

            <!-- Bandeau résumé -->
            <div class="row mb-3">
              <div class="col-md-4">
                <div class="alert alert-primary mb-2">
                  <dt>Entrées</dt>
                  <dd class="mb-0"><strong><?= fmt($totalE) ?> $</strong></dd>
                </div>
              </div>
              <div class="col-md-4">
                <div class="alert alert-danger mb-2">
                  <dt>Dépenses</dt>
                  <dd class="mb-0"><strong><?= fmt($totalD) ?> $</strong></dd>
                </div>
              </div>
              <div class="col-md-4">
                <?php $net = $totalE - $totalD; ?>
                <div class="alert <?= ($net<0?'alert-warning':'alert-success') ?> mb-2">
                  <dt>Net</dt>
                  <dd class="mb-0"><strong><?= fmt($net) ?> $</strong></dd>
                </div>
              </div>
            </div>

            <!-- ENTRÉES -->
            <h6 class="mt-3">Entrées du <?= h($dateAff) ?></h6>
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead>
                  <tr>
                    <th># Reçu</th>
                    <th>Ménage</th>
                    <th class="text-end">Montant</th>
                    <th>Observation</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($entries)): ?>
                    <tr><td colspan="6"><em>Aucune entrée pour cette date.</em></td></tr>
                  <?php else: ?>
                    <?php foreach ($entries as $r): ?>
                      <tr>
                        <td><?= (int)$r['id'] ?></td>
                        <td><?= h($r['menage_nom'] ?? '') ?></td>
                        <td class="text-end"><?= fmt($r['montant'] ?? 0) ?> $</td>
                        <td><?= h($r['observation'] ?? '') ?></td>
                        <td><?= h($r['dte'] ?? $date) ?></td>
                        <td>
                          <?php if ($type === 'scolaire'): ?>
                            <a class="btn btn-outline-primary btn-sm" target="_blank" href="../paiement-divers-frais/apercu_recu.php?ordre=<?= (int)$r['id'] ?>">Imprimer</a>
                          <?php else: ?>
                            <a class="btn btn-outline-primary btn-sm" target="_blank" href="../paiement-divers-frais/apercu_recu_divers.php?ordre=<?= (int)$r['id'] ?>">Imprimer</a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="2" class="text-end">Total entrées</th>
                    <th class="text-end"><?= fmt($totalE) ?> $</th>
                    <th colspan="3"></th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- DÉPENSES -->
            <h6 class="mt-4">Dépenses du <?= h($dateAff) ?></h6>
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Bénéficiaire</th>
                    <th>Référence</th>
                    <th>Description</th>
                    <th class="text-end">Montant</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($exits)): ?>
                    <tr><td colspan="6"><em>Aucune dépense pour cette date.</em></td></tr>
                  <?php else: ?>
                    <?php foreach ($exits as $r): ?>
                      <tr>
                        <td><?= (int)$r['id'] ?></td>
                        <td><?= h($r['beneficiaire'] ?? '') ?></td>
                        <td><?= h($r['reference'] ?? '') ?></td>
                        <td><?= h($r['description'] ?? '') ?></td>
                        <td class="text-end"><?= fmt($r['montant'] ?? 0) ?> $</td>
                        <td><?= h($r['dte'] ?? $date) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="4" class="text-end">Total dépenses</th>
                    <th class="text-end"><?= fmt($totalD) ?> $</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
  <?php require_once('../../layouts/constants/footer.php'); ?>
</div>
</body>
