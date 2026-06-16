<?php
require_once ('../../layouts/constants/head.php');
require_once ('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/config.php');

/* Helpers */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt($n){ return number_format((float)$n, 2, '.', ' '); }
/* Date FR pour affichage utilisateur (indépendant du setlocale) */
function date_fr(string $ymd, bool $long = false): string {
  if (!preg_match('~^\d{4}-\d{2}-\d{2}$~', $ymd)) return h($ymd);
  [$Y,$m,$d] = explode('-', $ymd);
  $ts = @strtotime("$ymd 00:00:00");
  if ($ts === false) return h($ymd);
  $jours  = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
  $moisL  = [1=>'janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
  $moisC  = [1=>'janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
  $w = (int)date('w', $ts);
  $day = (int)$d;
  if ($long) return $jours[$w].' '.$day.' '.$moisL[(int)$m].' '.$Y;
  return $day.' '.$moisC[(int)$m].' '.$Y;
}

/* Filtres GET */
$anneeScolaire = isset($_GET['annee']) ? trim($_GET['annee']) : '';
$startDate     = isset($_GET['start']) ? trim($_GET['start']) : '';
$endDate       = isset($_GET['end'])   ? trim($_GET['end'])   : '';

$reDate = '/^\d{4}-\d{2}-\d{2}$/';
if ($startDate !== '' && !preg_match($reDate, $startDate)) $startDate = '';
if ($endDate   !== '' && !preg_match($reDate, $endDate))   $endDate   = '';

/* Conditions dynamiques */
$wherePaiement = [];
$whereDepense  = [];
$bindPaiement  = $bindDepense = '';
$argsPaiement  = $argsDepense = [];

if ($anneeScolaire !== '') {
  $wherePaiement[] = 'p.anneeScolaire = ?'; $bindPaiement .= 's'; $argsPaiement[] = $anneeScolaire;
  $whereDepense[]  = 'd.anneeScolaire = ?'; $bindDepense  .= 's'; $argsDepense[]  = $anneeScolaire;
}
if ($startDate !== '') {
  $wherePaiement[] = 'p.dateCreated >= ?'; $bindPaiement .= 's'; $argsPaiement[] = $startDate;
  $whereDepense[]  = 'd.dateCreaty >= ?'; $bindDepense  .= 's'; $argsDepense[]  = $startDate;
}
if ($endDate !== '') {
  $wherePaiement[] = 'p.dateCreated <= ?'; $bindPaiement .= 's'; $argsPaiement[] = $endDate;
  $whereDepense[]  = 'd.dateCreaty <= ?'; $bindDepense  .= 's'; $argsDepense[]  = $endDate;
}
/* dépenses scolaires : on capte le mot-clé "scol" (modifiable si besoin) */
$whereDepense[] = "(LOWER(d.reference) LIKE '%scol%' OR LOWER(d.description) LIKE '%scol%')";

/* Requêtes agrégées par jour */
$sqlPaiement = "SELECT p.dateCreated AS d, COALESCE(SUM(p.montantPayer),0) AS entre
                FROM paiement p
                ".(count($wherePaiement) ? "WHERE ".implode(' AND ', $wherePaiement) : "")."
                GROUP BY p.dateCreated ORDER BY p.dateCreated ASC";

$sqlDepense  = "SELECT d.dateCreaty AS d, COALESCE(SUM(d.montant),0) AS depense
                FROM depenses d
                ".(count($whereDepense) ? "WHERE ".implode(' AND ', $whereDepense) : "")."
                GROUP BY d.dateCreaty ORDER BY d.dateCreaty ASC";

/* Exécution */
$datesEntre = []; $datesDep = [];
if ($stP = $con->prepare($sqlPaiement)) {
  if ($bindPaiement !== '') $stP->bind_param($bindPaiement, ...$argsPaiement);
  $stP->execute(); $rs = $stP->get_result();
  while($row=$rs->fetch_assoc()){ $datesEntre[$row['d']] = (float)$row['entre']; }
  $stP->close();
}
if ($stD = $con->prepare($sqlDepense)) {
  if ($bindDepense !== '') $stD->bind_param($bindDepense, ...$argsDepense);
  $stD->execute(); $rs = $stD->get_result();
  while($row=$rs->fetch_assoc()){ $datesDep[$row['d']] = (float)$row['depense']; }
  $stD->close();
}

/* Fusion dates */
$allDates = array_unique(array_merge(array_keys($datesEntre), array_keys($datesDep)));
sort($allDates);

/* Totaux */
$totalEntre=0.0; $totalDep=0.0; $soldeCumul=0.0; $soldeOuverture=0.0;
?>
<div class="main-panel">
  <div class="content-wrapper">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title d-flex justify-content-between">
          <span>Ventilation — Caisse Frais Scolaire</span>
          <a href="javascript:history.back()" class="btn btn-light">&lt; Retour</a>
        </h5>
        <hr>

        <!-- Filtres (facultatif) -->
        <form class="row g-2 mb-3" method="get">
          <div class="col-md-3">
            <label class="form-label">Année scolaire</label>
            <input type="text" class="form-control" name="annee" placeholder="2024-2025" value="<?= h($anneeScolaire) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Du</label>
            <input type="date" class="form-control" name="start" value="<?= h($startDate) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Au</label>
            <input type="date" class="form-control" name="end" value="<?= h($endDate) ?>">
          </div>
          <div class="col-md-3 d-flex align-items-end gap-2">
            <button class="btn btn-primary w-100">Filtrer</button>
            <a class="btn btn-secondary" href="?">Réinitialiser</a>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Date</th>
                <th class="text-end">Entrées (paiement)</th>
                <th class="text-end">Dépenses (Frais scolaire)</th>
                <th class="text-end">Net</th>
                <th class="text-end">Solde cumulé</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($allDates)): ?>
                <tr><td colspan="5"><div class="alert alert-info mb-0">Aucun mouvement.</div></td></tr>
              <?php else: ?>
                <?php foreach($allDates as $d):
                  $e=(float)($datesEntre[$d]??0.0);
                  $x=(float)($datesDep[$d]??0.0);
                  $net=$e-$x; $soldeCumul+=$net; $totalEntre+=$e; $totalDep+=$x;
                ?>
                <tr>
                  <td><?= date_fr($d) /* affichage FR */ ?></td>
                  <td class="text-end">
                    <a class="text-decoration-underline fw-semibold" href="detail_ventillation.php?type=scolaire&date=<?= urlencode($d) ?>"><?= fmt($e) ?> $</a>
                  </td>
                  <td class="text-end">
                    <a class="text-decoration-underline fw-semibold" href="detail_ventillation.php?type=scolaire&date=<?= urlencode($d) ?>"><?= fmt($x) ?> $</a>
                  </td>
                  <td class="text-end <?= $net < 0 ? 'text-danger' : '' ?>"><?= fmt($net) ?> $</td>
                  <td class="text-end"><?= fmt($soldeCumul) ?> $</td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
            <tfoot>
              <tr>
                <th>Totaux</th>
                <th class="text-end"><?= fmt($totalEntre) ?> $</th>
                <th class="text-end"><?= fmt($totalDep) ?> $</th>
                <th class="text-end"><?= fmt($totalEntre-$totalDep) ?> $</th>
                <th class="text-end"><?= fmt($soldeOuverture + ($totalEntre-$totalDep)) ?> $</th>
              </tr>
            </tfoot>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
<?php require_once('../../layouts/constants/footer.php'); ?>
