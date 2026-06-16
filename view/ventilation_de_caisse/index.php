<?php
require_once ('../../layouts/constants/head.php');
require_once ('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/config.php');

/* Helpers */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt($n){ return number_format((float)$n, 2, '.', ' '); }

/* Filtres GET (communs) */
$anneeScolaire = isset($_GET['annee']) ? trim($_GET['annee']) : '';
$startDate     = isset($_GET['start']) ? trim($_GET['start']) : '';
$endDate       = isset($_GET['end'])   ? trim($_GET['end'])   : '';

/* Validation date simple */
$reDate = '/^\d{4}-\d{2}-\d{2}$/';
if ($startDate !== '' && !preg_match($reDate, $startDate)) $startDate = '';
if ($endDate   !== '' && !preg_match($reDate, $endDate))   $endDate   = '';

/* ===========================
   Totaux — FRAIS SCOLAIRE
   =========================== */
/* Entrées: paiement */
$wherePS = [];
$bindPS  = '';
$argsPS  = [];

if ($anneeScolaire !== '') { $wherePS[] = 'p.anneeScolaire = ?'; $bindPS .= 's'; $argsPS[] = $anneeScolaire; }
if ($startDate !== '')     { $wherePS[] = 'p.dateCreated >= ?';  $bindPS .= 's'; $argsPS[] = $startDate; }
if ($endDate !== '')       { $wherePS[] = 'p.dateCreated <= ?';  $bindPS .= 's'; $argsPS[] = $endDate; }

$sqlPS = "
  SELECT COALESCE(SUM(p.montantPayer),0) AS total_entrees
  FROM paiement p
  ".(count($wherePS) ? "WHERE ".implode(' AND ', $wherePS) : "")."
";
$totalEntreesScol = 0.0;
if ($st = $con->prepare($sqlPS)) {
  if ($bindPS !== '') $st->bind_param($bindPS, ...$argsPS);
  $st->execute(); $rs = $st->get_result();
  if ($row = $rs->fetch_assoc()) $totalEntreesScol = (float)$row['total_entrees'];
  $st->close();
}

/* Dépenses: depenses (réf/desc contient “scol”) */
$whereDS = [];
$bindDS  = '';
$argsDS  = [];

$whereDS[] = '(LOWER(d.reference) LIKE "%scol%" OR LOWER(d.description) LIKE "%scol%")';
if ($anneeScolaire !== '') { $whereDS[] = 'd.anneeScolaire = ?'; $bindDS .= 's'; $argsDS[] = $anneeScolaire; }
if ($startDate !== '')     { $whereDS[] = 'd.dateCreaty >= ?';   $bindDS .= 's'; $argsDS[] = $startDate; }
if ($endDate !== '')       { $whereDS[] = 'd.dateCreaty <= ?';   $bindDS .= 's'; $argsDS[] = $endDate; }

$sqlDS = "
  SELECT COALESCE(SUM(d.montant),0) AS total_depenses
  FROM depenses d
  ".(count($whereDS) ? "WHERE ".implode(' AND ', $whereDS) : "")."
";
$totalDepensesScol = 0.0;
if ($st = $con->prepare($sqlDS)) {
  if ($bindDS !== '') $st->bind_param($bindDS, ...$argsDS);
  $st->execute(); $rs = $st->get_result();
  if ($row = $rs->fetch_assoc()) $totalDepensesScol = (float)$row['total_depenses'];
  $st->close();
}

$netScol = $totalEntreesScol - $totalDepensesScol;

/* ===========================
   Totaux — FRAIS CONNEXE
   =========================== */
/* Entrées: paiement_divers (pas de filtre type) */
$wherePC = [];
$bindPC  = '';
$argsPC  = [];

if ($anneeScolaire !== '') { $wherePC[] = 'pdv.anneeScolaire = ?'; $bindPC .= 's'; $argsPC[] = $anneeScolaire; }
if ($startDate !== '')     { $wherePC[] = 'DATE(pdv.dateCreated) >= ?'; $bindPC .= 's'; $argsPC[] = $startDate; }
if ($endDate !== '')       { $wherePC[] = 'DATE(pdv.dateCreated) <= ?'; $bindPC .= 's'; $argsPC[] = $endDate; }

$sqlPC = "
  SELECT COALESCE(SUM(pdv.montantPayer),0) AS total_entrees
  FROM paiement_divers pdv
  ".(count($wherePC) ? "WHERE ".implode(' AND ', $wherePC) : "")."
";
$totalEntreesConnexe = 0.0;
if ($st = $con->prepare($sqlPC)) {
  if ($bindPC !== '') $st->bind_param($bindPC, ...$argsPC);
  $st->execute(); $rs = $st->get_result();
  if ($row = $rs->fetch_assoc()) $totalEntreesConnexe = (float)$row['total_entrees'];
  $st->close();
}

/* Dépenses: depenses (reference = 'Frais Connexe') */
$whereDC = ["d.reference = 'Frais Connexe'"];
$bindDC  = '';
$argsDC  = [];

if ($anneeScolaire !== '') { $whereDC[] = 'd.anneeScolaire = ?'; $bindDC .= 's'; $argsDC[] = $anneeScolaire; }
if ($startDate !== '')     { $whereDC[] = 'd.dateCreaty >= ?';   $bindDC .= 's'; $argsDC[] = $startDate; }
if ($endDate !== '')       { $whereDC[] = 'd.dateCreaty <= ?';   $bindDC .= 's'; $argsDC[] = $endDate; }

$sqlDC = "
  SELECT COALESCE(SUM(d.montant),0) AS total_depenses
  FROM depenses d
  ".(count($whereDC) ? "WHERE ".implode(' AND ', $whereDC) : "")."
";
$totalDepensesConnexe = 0.0;
if ($st = $con->prepare($sqlDC)) {
  if ($bindDC !== '') $st->bind_param($bindDC, ...$argsDC);
  $st->execute(); $rs = $st->get_result();
  if ($row = $rs->fetch_assoc()) $totalDepensesConnexe = (float)$row['total_depenses'];
  $st->close();
}

$netConnexe = $totalEntreesConnexe - $totalDepensesConnexe;

/* URLs vers les pages détail avec les mêmes filtres */
$queryFilters = http_build_query([
  'annee' => $anneeScolaire,
  'start' => $startDate,
  'end'   => $endDate,
]);
$linkScol   = "ventiallation_caisse_frais_scolaire.php".($queryFilters ? "?$queryFilters" : "");
$linkConn   = "ventiallation_caisse_frais_connexe.php".($queryFilters ? "?$queryFilters" : "");
?>
<body>
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">

            <h5 class="card-title text-uppercase d-flex align-items-center justify-content-between">
              <span>Index — Ventilation Caisse (Résumé)</span>
            </h5>
            <hr>

            <!-- Filtres -->
            <form method="get" class="row g-2 mb-4">
              <div class="col-md-3">
                <label class="form-label">Année scolaire</label>
                <input type="text" name="annee" class="form-control" placeholder="2024-2025" value="<?= h($anneeScolaire) ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Du</label>
                <input type="date" name="start" class="form-control" value="<?= h($startDate) ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Au</label>
                <input type="date" name="end" class="form-control" value="<?= h($endDate) ?>">
              </div>
              <div class="col-md-3 d-flex align-items-end gap-2">
                <button class="btn btn-primary w-100" type="submit">Filtrer</button>
                <a class="btn btn-secondary" href="?">Réinitialiser</a>
              </div>
            </form>

            <!-- Cartes Résumé -->
            <div class="row">
              <!-- Scolaire -->
              <div class="col-md-6">
                <div class="card shadow-sm">
                  <div class="card-body">
                    <h6 class="text-uppercase mb-3">Frais Scolaire — Résumé</h6>
                    <div class="row mb-2">
                      <div class="col-6"><dt>Entrées</dt><dd class="text-primary"><?= fmt($totalEntreesScol) ?> $</dd></div>
                      <div class="col-6"><dt>Dépenses</dt><dd class="text-danger"><?= fmt($totalDepensesScol) ?> $</dd></div>
                    </div>
                    <div class="row">
                      <div class="col-12">
                        <dt>Net</dt>
                        <dd class="<?= ($netScol<0?'text-danger':'text-success') ?>"><strong><?= fmt($netScol) ?> $</strong></dd>
                      </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                      <a href="<?= h($linkScol) ?>" class="btn btn-outline-primary">Voir ventilation scolaire</a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Connexe -->
              <div class="col-md-6">
                <div class="card shadow-sm">
                  <div class="card-body">
                    <h6 class="text-uppercase mb-3">Frais Connexe — Résumé</h6>
                    <div class="row mb-2">
                      <div class="col-6"><dt>Entrées</dt><dd class="text-primary"><?= fmt($totalEntreesConnexe) ?> $</dd></div>
                      <div class="col-6"><dt>Dépenses</dt><dd class="text-danger"><?= fmt($totalDepensesConnexe) ?> $</dd></div>
                    </div>
                    <div class="row">
                      <div class="col-12">
                        <dt>Net</dt>
                        <dd class="<?= ($netConnexe<0?'text-danger':'text-success') ?>"><strong><?= fmt($netConnexe) ?> $</strong></dd>
                      </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                      <a href="<?= h($linkConn) ?>" class="btn btn-outline-primary">Voir ventilation connexe</a>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- /row cartes -->

            <!-- Rappel filtres -->
            <div class="mt-3">
              <small class="text-muted">
                Année: <strong><?= $anneeScolaire !== '' ? h($anneeScolaire) : '—' ?></strong> |
                Période: <strong><?= $startDate !== '' ? h($startDate) : '—' ?></strong> → <strong><?= $endDate !== '' ? h($endDate) : '—' ?></strong>
              </small>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div><!-- /content-wrapper -->

<?php require_once ('../../layouts/constants/footer.php'); ?>
</body>
