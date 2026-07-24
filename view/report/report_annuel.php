<?php
require_once ('../../layouts/constants/head.php'); 
require_once ('../../webapp/service/annee_scolaire.service.php'); 
require_once ('../../layouts/navbar/navbar.php');

require_once('../../webapp/database/config.php');

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($n){ return number_format((float)$n, 2, '.', ' ') . ' $'; }

/* ------------------ Mois FR dans l'ordre scolaire (Septembre -> Août) ------------------ */
$MONTHS_SCHOLAR = [
  9  => 'Septembre',
  10 => 'Octobre',
  11 => 'Novembre',
  12 => 'Décembre',
  1  => 'Janvier',
  2  => 'Février',
  3  => 'Mars',
  4  => 'Avril',
  5  => 'Mai',
  6  => 'Juin',
  7  => 'Juillet',
  8  => 'Août'
];

// Détermination de l'année scolaire par défaut selon la date du jour
$currentMonth = (int)date('n');
$currentYear  = (int)date('Y');
$defaultStartYear = ($currentMonth >= 9) ? $currentYear : ($currentYear - 1);

$type  = (isset($_GET['type']) && $_GET['type']==='connexe') ? 'connexe' : 'scolaire';
$annee = isset($_GET['annee']) && (int)$_GET['annee']>=2000 ? (int)$_GET['annee'] : $defaultStartYear;

if (isset($_GET['q']) && $_GET['q']==='year'){ $annee = $defaultStartYear; }

// Bornes de l'année scolaire sélectionnée : 01/09/Annee -> 31/08/(Annee+1)
$startYear = $annee;
$endYear   = $annee + 1;

$minDate = sprintf('%04d-09-01 00:00:00', $startYear);
$maxDate = sprintf('%04d-08-31 23:59:59', $endYear);

$isConnexe = ($type==='connexe');
$refIn = $isConnexe ? "('Frais Connexe','Connexe')" : "('Frais Scolaire','Scolaire')";

/* =========================================================
   RECETTES ET DÉPENSES — Groupées par mois (Année scolaire)
   ========================================================= */
$rec_by_month = array_fill(1, 12, 0.0);
$dep_by_month = array_fill(1, 12, 0.0);
$totalRec = 0.0;
$totalDep = 0.0;

// -- Recettes
if ($isConnexe){
  $sql="SELECT MONTH(pd.dateCreated) AS m, SUM(pd.montantPayer) AS tot
        FROM paiement_divers pd
        WHERE pd.dateCreated BETWEEN ? AND ?
        GROUP BY MONTH(pd.dateCreated)";
  $st=$con->prepare($sql); $st->bind_param('ss',$minDate,$maxDate);
}else{
  $sql="SELECT MONTH(p.dateCreated) AS m, SUM(p.montantPayer) AS tot
        FROM paiement p
        WHERE p.dateCreated BETWEEN ? AND ?
        GROUP BY MONTH(p.dateCreated)";
  $st=$con->prepare($sql); $st->bind_param('ss',$minDate,$maxDate);
}
$st->execute(); $rs=$st->get_result();
while($r=$rs->fetch_assoc()){
  $m = (int)$r['m'];
  $rec_by_month[$m] = (float)$r['tot'];
  $totalRec += (float)$r['tot'];
}
$st->close();

// -- Dépenses
$sql="SELECT MONTH(d.dateCreaty) AS m, SUM(d.montant) AS tot
      FROM depenses d
      WHERE d.dateCreaty BETWEEN ? AND ? AND d.reference IN $refIn
      GROUP BY MONTH(d.dateCreaty)";
$st=$con->prepare($sql); $st->bind_param('ss',$minDate,$maxDate);
$st->execute(); $rs=$st->get_result();
while($r=$rs->fetch_assoc()){
  $m = (int)$r['m'];
  $dep_by_month[$m] = (float)$r['tot'];
  $totalDep += (float)$r['tot'];
}
$st->close();

$solde_annee = $totalRec - $totalDep;

/* =========================================================
   SOLDES DES ANNÉES SCOLAIRES ANTÉRIEURES
   ========================================================= */
// On récupère toutes les transactions antérieures au début de l'année scolaire (avant Sept/startYear)
$rec_past = []; $dep_past = [];

if ($isConnexe){
  $sql="SELECT 
          IF(MONTH(pd.dateCreated) >= 9, YEAR(pd.dateCreated), YEAR(pd.dateCreated) - 1) AS sy,
          SUM(pd.montantPayer) AS tot 
        FROM paiement_divers pd 
        WHERE pd.dateCreated < ?
        GROUP BY sy";
  $st=$con->prepare($sql); $st->bind_param('s', $minDate);
}else{
  $sql="SELECT 
          IF(MONTH(p.dateCreated) >= 9, YEAR(p.dateCreated), YEAR(p.dateCreated) - 1) AS sy,
          SUM(p.montantPayer) AS tot 
        FROM paiement p 
        WHERE p.dateCreated < ?
        GROUP BY sy";
  $st=$con->prepare($sql); $st->bind_param('s', $minDate);
}
$st->execute(); $rs=$st->get_result();
while($row=$rs->fetch_assoc()){ $rec_past[(int)$row['sy']] = (float)$row['tot']; }
$st->close();

$sql="SELECT 
        IF(MONTH(d.dateCreaty) >= 9, YEAR(d.dateCreaty), YEAR(d.dateCreaty) - 1) AS sy,
        SUM(d.montant) AS tot 
      FROM depenses d 
      WHERE d.dateCreaty < ? AND d.reference IN $refIn 
      GROUP BY sy";
$st=$con->prepare($sql); $st->bind_param('s', $minDate);
$st->execute(); $rs=$st->get_result();
while($row=$rs->fetch_assoc()){ $dep_past[(int)$row['sy']] = (float)$row['tot']; }
$st->close();

$past_years = array_unique(array_merge(array_keys($rec_past), array_keys($dep_past)));
sort($past_years);

$annees_anterieures = [];
$cum_solde = 0.0;
foreach($past_years as $y){
  $rec = $rec_past[$y] ?? 0.0;
  $dep = $dep_past[$y] ?? 0.0;
  if ($rec==0.0 && $dep==0.0) continue;
  
  $sy = $rec - $dep;
  $cum_solde += $sy;
  
  $annees_anterieures[] = [
    'y'           => $y . ' - ' . ($y + 1),
    'rec'         => $rec,
    'dep'         => $dep,
    'solde_annee' => $sy,
    'solde_cum'   => $cum_solde
  ];
}

/* ------------------ Titres ------------------ */
$TITRE    = 'VENTILATION ANNUELLE DU '.($isConnexe?'FRAIS CONNEXE':'FRAIS SCOLAIRE');
$ANNEELIB = 'Année scolaire ' . $startYear . ' - ' . $endYear;

/* =========================================================
   EXPORT CSV
   ========================================================= */
if (isset($_GET['export']) && $_GET['export']==='csv') {
  $filename = sprintf('rapport_annuel_%s_%d-%d.csv', $type, $startYear, $endYear);

  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  header('Pragma: no-cache');
  header('Expires: 0');

  $out = fopen('php://output', 'w');
  fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

  // Titre + période
  fputcsv($out, [$TITRE]);
  fputcsv($out, ['Période', $ANNEELIB]);
  fputcsv($out, []);

  // Soldes caisse — Années antérieures
  fputcsv($out, ['SOLDES CAISSE — Années scolaires antérieures']);
  fputcsv($out, ['Année Scolaire', 'Recettes', 'Dépenses', 'Solde de l\'année', 'Solde caisse cumulé']);
  if (empty($annees_anterieures)){
    fputcsv($out, ['(aucune)', '', '', '', '']);
  } else {
    foreach($annees_anterieures as $row){
      fputcsv($out, [
        $row['y'],
        $row['rec'],
        $row['dep'],
        $row['solde_annee'],
        $row['solde_cum']
      ]);
    }
  }
  fputcsv($out, []);

  // Bilan de l'année scolaire par mois
  fputcsv($out, ['BILAN DE L\'ANNÉE SCOLAIRE ' . $startYear . ' - ' . $endYear . ' (par mois)']);
  fputcsv($out, ['Mois', 'Recettes', 'Dépenses', 'Solde du mois']);
  
  foreach($MONTHS_SCHOLAR as $m_num => $m_label){
      $r = $rec_by_month[$m_num];
      $d = $dep_by_month[$m_num];
      $s = $r - $d;
      fputcsv($out, [$m_label, $r, $d, $s]);
  }
  
  fputcsv($out, []);
  fputcsv($out, ['TOTAL ANNUEL', $totalRec, $totalDep, $solde_annee]);
  fputcsv($out, []);

  // SOLDE CAISSE
  fputcsv($out, ['SOLDE CAISSE (Année ' . $startYear . '-' . $endYear . ')', $solde_annee]);

  fclose($out);
  exit;
}

require_once('../../layouts/constants/head.php');
require_once('../../layouts/navbar/navbar.php');
?>
<style>
/* ---------- RESET DOUX ---------- */
.clean * {
    box-sizing: border-box;
}

.clean {
    color: #0f172a;
}

.clean .muted {
    color: #64748b;
}

.clean .mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

/* ---------- ENCADRÉS / LAYOUT ---------- */
.page-wrap {
    padding: 14px;
}

.card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.04);
    margin-bottom: 14px;
}

.card-header {
    padding: 14px 16px;
    border-bottom: 1px solid #eef2f7;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.card-title {
    margin: 0;
    font-weight: 800;
    font-size: 18px;
    letter-spacing: .2px;
}

.card-body {
    padding: 16px;
}

.toolbar {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.toolbar label {
    font-size: 12px;
    color: #475569;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
}

.toolbar .row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 8px;
    padding: 10px 12px;
    font-weight: 700;
    border: 1px solid;
    text-decoration: none;
}

.btn:hover {
    filter: brightness(0.95);
    border: 1px solid;
}

.sheet {
    background: #ffffff;
    border: 1px dashed #e5e7eb;
    border-radius: 10px;
    padding: 18px;
}

/* ---------- TABLES ---------- */
.rpt {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
    overflow: hidden;
    border-radius: 10px;
}

.rpt th,
.rpt td {
    border-bottom: 1px solid #eef2f7;
    padding: 10px 12px;
}

.rpt thead th {
    background: linear-gradient(180deg, #f8fafc, #f1f5f9);
    color: #0f172a;
    font-weight: 800;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: .35px;
}

.rpt tbody tr:nth-child(odd) {
    background: #fff;
}

.rpt tbody tr:nth-child(even) {
    background: #fcfdff;
}

.rpt tfoot th,
.rpt tfoot td {
    background: #f8fafc;
    font-weight: bold;
    border-top: 2px solid #e2e8f0;
}

.text-end {
    text-align: right;
}

.cell-empty {
    text-align: center;
    color: #94a3b8;
}

.summary {
    margin-top: 16px;
    text-align: right;
    font-weight: 900;
    font-size: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 10px 12px;
    border-radius: 10px;
}

.col {
    flex: 1;
    min-width: 250px;
}

/* ---------- PRINT : n’imprimer QUE #exportable ---------- */
@media print {
    body * {
        visibility: hidden !important;
    }

    #exportable,
    #exportable * {
        visibility: visible !important;
    }

    #exportable {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    .no-print {
        display: none !important;
    }
}
</style>

<div class="main-panel clean">
    <div class="content-wrapper page-wrap">

        <!-- ====== EN-TÊTE / OUTILS ====== -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rapport annuel — <?= h($isConnexe ? 'Frais connexe' : 'Frais scolaire') ?></h3>
                <div class="muted"><strong>Période :</strong> <?= h($ANNEELIB) ?></div>
            </div>
            <div class="card-body">
                <form class="toolbar" method="get">
                    <div class="row">
                        <div class="col">
                            <label>Type</label>
                            <select name="type" class="form-control">
                                <option value="scolaire" <?= !$isConnexe?'selected':''; ?>>Frais scolaire</option>
                                <option value="connexe" <?=  $isConnexe?'selected':''; ?>>Frais connexe</option>
                            </select>
                        </div>
                        <div class="col">
                            <label>Début Année Scolaire (Septembre)</label>
                            <select name="annee" class="form-control">
                                <?php for($y = $defaultStartYear + 1; $y >= 2018; $y--): ?>
                                <option value="<?= $y ?>" <?= $y === $annee ? 'selected' : '' ?>>
                                    <?= $y ?> - <?= $y + 1 ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="btn-group mt-3">
                        <button class="btn btn-primary" type="submit">Afficher</button>
                        <a href="?type=<?=h($type)?>&q=year" class="btn btn-dark">Cette année</a>
                        <a class="btn btn-success" href="?type=<?=h($type)?>&annee=<?=h($annee)?>&export=csv">
                            Export CSV
                        </a>
                        <button type="button" class="btn btn-danger" onclick="window.print()">Imprimer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ====== CONTENU IMPRIMABLE ====== -->
        <div class="sheet" id="exportable">

            <!-- Soldes des années antérieures -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Soldes caisse — Années scolaires antérieures</h4>
                </div>
                <div class="card-body">
                    <table class="rpt">
                        <thead>
                            <tr>
                                <th style="width:220px;">Année Scolaire</th>
                                <th class="text-end" style="width:160px;">Recettes</th>
                                <th class="text-end" style="width:160px;">Dépenses</th>
                                <th class="text-end" style="width:160px;">Solde de l'année</th>
                                <th class="text-end" style="width:180px;">Solde caisse cumulé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($annees_anterieures)): ?>
                            <tr>
                                <td colspan="5" class="cell-empty"><em>Aucune année antérieure enregistrée.</em></td>
                            </tr>
                            <?php else: ?>
                            <?php foreach($annees_anterieures as $row): ?>
                            <tr>
                                <td><strong><?= h($row['y']) ?></strong></td>
                                <td class="text-end mono"><?= money($row['rec']) ?></td>
                                <td class="text-end mono"><?= money($row['dep']) ?></td>
                                <td class="text-end mono"><?= money($row['solde_annee']) ?></td>
                                <td class="text-end mono"><strong><?= money($row['solde_cum']) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bilan de l'année (par mois) -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Bilan - Année Scolaire <?= h($startYear . ' - ' . $endYear) ?> (par mois)
                    </h4>
                    <div class="muted">Type : <strong><?= h($isConnexe ? 'Connexe' : 'Scolaire') ?></strong></div>
                </div>
                <div class="card-body">
                    <table class="rpt">
                        <thead>
                            <tr>
                                <th>Mois</th>
                                <th class="text-end" style="width:200px;">Recettes</th>
                                <th class="text-end" style="width:200px;">Dépenses</th>
                                <th class="text-end" style="width:200px;">Solde du mois</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($MONTHS_SCHOLAR as $m_num => $m_label): 
                                $r = $rec_by_month[$m_num];
                                $d = $dep_by_month[$m_num];
                                $s = $r - $d;
                            ?>
                            <tr>
                                <td><strong><?= h($m_label) ?></strong></td>
                                <td class="text-end mono"><?= money($r) ?></td>
                                <td class="text-end mono"><?= money($d) ?></td>
                                <td class="text-end mono"
                                    style="color: <?= $s < 0 ? '#ef4444' : ($s > 0 ? '#10b981' : 'inherit') ?>">
                                    <strong><?= money($s) ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>TOTAL ANNUEL</td>
                                <td class="text-end mono"><?= money($totalRec) ?></td>
                                <td class="text-end mono"><?= money($totalDep) ?></td>
                                <td class="text-end mono"><strong><?= money($solde_annee) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- SOLDE DE L'ANNÉE -->
            <div class="summary">
                SOLDE CAISSE (Année Scolaire <?= h($startYear . ' - ' . $endYear) ?>) :
                <span class="mono"
                    style="color: <?= $solde_annee < 0 ? '#ef4444' : '#10b981' ?>"><?= money($solde_annee) ?></span>
            </div>

        </div><!-- /exportable -->
    </div>

    <?php require_once('../../layouts/constants/footer.php'); ?>
</div>