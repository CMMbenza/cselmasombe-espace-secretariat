<?php
require_once('../../webapp/database/config.php');
require_once ('../../layouts/constants/head.php'); 
require_once ('../../webapp/service/annee_scolaire.service.php'); 
require_once ('../../layouts/navbar/navbar.php');

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($n){ return number_format((float)$n, 2, '.', ' ') . ' $'; }

/* ------------------ Mois FR ------------------ */
$MONTHS_FR = [
  1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
  7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'
];

$nowY = (int)date('Y');
$nowM = (int)date('n');

$type  = (isset($_GET['type']) && $_GET['type']==='connexe') ? 'connexe' : 'scolaire';
$mois  = isset($_GET['mois']) && (int)$_GET['mois']>=1 && (int)$_GET['mois']<=12 ? (int)$_GET['mois'] : $nowM;
$annee = isset($_GET['annee']) && (int)$_GET['annee']>=2000 ? (int)$_GET['annee'] : $nowY;
if (isset($_GET['q']) && $_GET['q']==='month'){ $mois=$nowM; $annee=$nowY; }

$minDate = sprintf('%04d-%02d-01',$annee,$mois);
$maxDate = date('Y-m-t', strtotime($minDate));

$isConnexe = ($type==='connexe');

/* =========================================================
   RECETTES — total par jour (mois courant)
   ========================================================= */
$rec_day_tot = [];   // 'YYYY-MM-DD' => total du jour
$totalRec = 0.0;

if ($isConnexe){
  $sql="SELECT DATE(pd.dateCreated) AS dte, SUM(pd.montantPayer) AS tot
        FROM paiement_divers pd
        WHERE DATE(pd.dateCreated) BETWEEN ? AND ?
        GROUP BY DATE(pd.dateCreated)
        ORDER BY dte";
  $st=$con->prepare($sql); $st->bind_param('ss',$minDate,$maxDate);
}else{
  $sql="SELECT DATE(p.dateCreated) AS dte, SUM(p.montantPayer) AS tot
        FROM paiement p
        WHERE DATE(p.dateCreated) BETWEEN ? AND ?
        GROUP BY DATE(p.dateCreated)
        ORDER BY dte";
  $st=$con->prepare($sql); 
  $st->bind_param('ss',$minDate,$maxDate);
}
$st->execute(); $rs=$st->get_result();
while($r=$rs->fetch_assoc()){
  $rec_day_tot[$r['dte']] = (float)$r['tot'];
  $totalRec += (float)$r['tot'];
}
$st->close();

/* =========================================================
   DÉPENSES — groupées par date (détaillées + total jour)
   ========================================================= */
$dep_by_date = [];     // 'YYYY-MM-DD' => [ ['numref'=>..,'libelle'=>'..','montant'=>..], ...]
$dep_day_tot = [];     // 'YYYY-MM-DD' => total du jour
$totalDep = 0.0;

$refIn = $isConnexe ? "('Frais Connexe','Connexe')" : "('Frais Scolaire','Scolaire')";
$sql="SELECT d.dateCreaty AS dte, d.numero_reference AS numref, d.description AS libelle, d.montant
      FROM depenses d
      WHERE d.dateCreaty BETWEEN ? AND ? AND d.reference IN $refIn
      ORDER BY d.dateCreaty, d.numero_reference";
$st=$con->prepare($sql); $st->bind_param('ss',$minDate,$maxDate);
$st->execute(); $rs=$st->get_result();
while($d=$rs->fetch_assoc()){
  $day = $d['dte'];
  if (!isset($dep_by_date[$day])) { $dep_by_date[$day]=[]; $dep_day_tot[$day]=0.0; }
  $dep_by_date[$day][] = [
    'numref'  => $d['numref'],   // N° = numero_reference
    'libelle' => $d['libelle'],
    'montant' => (float)$d['montant']
  ];
  $dep_day_tot[$day] += (float)$d['montant'];
  $totalDep          += (float)$d['montant'];
}
$st->close();

/* =========================================================
   REPORT ANTÉRIEUR (avant le mois)
   ========================================================= */
if ($isConnexe){
  $sql="SELECT COALESCE(SUM(pd.montantPayer),0) AS tot_before
        FROM paiement_divers pd
        WHERE DATE(pd.dateCreated) < ?";
  $st=$con->prepare($sql); $st->bind_param('s',$minDate);
}else{
  $sql="SELECT COALESCE(SUM(p.montantPayer),0) AS tot_before
        FROM paiement p
        WHERE p.dateCreated < ?";
  $st=$con->prepare($sql); $st->bind_param('s',$minDate);
}
$st->execute(); $r=$st->get_result()->fetch_assoc();
$rec_before = (float)($r['tot_before'] ?? 0.0);
$st->close();

$sql="SELECT COALESCE(SUM(d.montant),0) AS tot_before
      FROM depenses d
      WHERE d.dateCreaty < ? AND d.reference IN $refIn";
$st=$con->prepare($sql); $st->bind_param('s',$minDate);
$st->execute(); $r=$st->get_result()->fetch_assoc();
$dep_before = (float)($r['tot_before'] ?? 0.0);
$st->close();

$report_anterieur = $rec_before - $dep_before;     // solde cumulé avant le mois
$solde_mois       = $totalRec - $totalDep;         // solde uniquement du mois
$solde_cloture    = $report_anterieur + $solde_mois; // (info)

/* =========================================================
   SOLDES DES MOIS ANTÉRIEURS — contexte
   ========================================================= */
$rec_month = []; $dep_month = [];
if ($isConnexe){
  $sql="SELECT DATE_FORMAT(DATE(pd.dateCreated),'%Y-%m') AS ym, SUM(pd.montantPayer) AS tot
        FROM paiement_divers pd GROUP BY ym";
  $rs=$con->query($sql);
}else{
  $sql="SELECT DATE_FORMAT(p.dateCreated,'%Y-%m') AS ym, SUM(p.montantPayer) AS tot
        FROM paiement p GROUP BY ym";
  $rs=$con->query($sql);
}
if ($rs){ while($row=$rs->fetch_assoc()){ $rec_month[$row['ym']] = (float)$row['tot']; } }

$sql="SELECT DATE_FORMAT(d.dateCreaty,'%Y-%m') AS ym, SUM(d.montant) AS tot
      FROM depenses d WHERE d.reference IN $refIn GROUP BY ym";
$rs=$con->query($sql);
if ($rs){ while($row=$rs->fetch_assoc()){ $dep_month[$row['ym']] = (float)$row['tot']; } }

$selectedYM = sprintf('%04d-%02d', $annee, $mois);
$months_all = array_unique(array_merge(array_keys($rec_month), array_keys($dep_month)));
sort($months_all);

$mois_anterieurs = [];
$cum_solde = 0.0;
foreach($months_all as $ym){
  if ($ym >= $selectedYM) break;
  $rec = $rec_month[$ym] ?? 0.0;
  $dep = $dep_month[$ym] ?? 0.0;
  if ($rec==0.0 && $dep==0.0) continue;
  $sm  = $rec - $dep;
  $cum_solde += $sm;
  [$y,$m] = explode('-', $ym);
  $mois_anterieurs[] = [
    'ym'          => $ym,
    'y'           => (int)$y,
    'm'           => (int)$m,
    'rec'         => $rec,
    'dep'         => $dep,
    'solde_mois'  => $sm,
    'solde_cum'   => $cum_solde
  ];
}

/* ------------------ Titres ------------------ */
$TITRE   = 'VENTILATION MENSUELLE DU '.($isConnexe?'FRAIS CONNEXE':'FRAIS SCOLAIRE');
$MOISLIB = $MONTHS_FR[$mois].' '.$annee;

/* ------------ Helpers de rendu ------------ */
function render_recettes_totaux_par_jour($rec_day_tot){
  if (empty($rec_day_tot)){
    echo '<tr><td colspan="2" class="cell-empty"><em>Aucune recette</em></td></tr>';
    return;
  }
  ksort($rec_day_tot);
  foreach($rec_day_tot as $d=>$tot){
    echo '<tr>
            <td>'.h(date("d/m/Y",strtotime($d))).'</td>
            <td class="text-end"><strong>'.money($tot).'</strong></td>
          </tr>';
  }
}
function render_depenses_group($dep_by_date,$dep_day_tot){
  if (empty($dep_by_date)){
    echo '<tr><td colspan="3" class="cell-empty"><em>Aucune dépense</em></td></tr>';
    return;
  }
  ksort($dep_by_date);
  foreach($dep_by_date as $d=>$rows){
    echo '<tr class="group-date"><td colspan="3"><span class="chip-date">'.h(date("d/m/Y",strtotime($d))).'</span></td></tr>';
    foreach($rows as $r){
      $num = ($r['numref']!==null && $r['numref']!=='') ? h($r['numref']) : '-';
      echo '<tr>
              <td class="text-center mono">'.$num.'</td>
              <td>'.h($r['libelle']).'</td>
              <td class="text-end mono">'.money($r['montant']).'</td>
            </tr>';
    }
    echo '<tr class="subtotal">
            <td></td>
            <td class="text-end"><em>Total du jour</em></td>
            <td class="text-end mono"><strong>'.money($dep_day_tot[$d]).'</strong></td>
          </tr>';
  }
}

/* =========================================================
   EXPORT CSV (sections demandées UNIQUEMENT)
   ========================================================= */
if (isset($_GET['export']) && $_GET['export']==='csv') {
  $filename = sprintf('rapport_%s_%04d_%02d_sections.csv', $type, $annee, $mois);

  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  header('Pragma: no-cache');
  header('Expires: 0');

  $out = fopen('php://output', 'w');
  fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

  // Titre + période
  fputcsv($out, [$TITRE]);
  fputcsv($out, ['Période', $MOISLIB]);
  fputcsv($out, []);

  // Soldes caisse — Mois antérieurs
  fputcsv($out, ['SOLDES CAISSE — Mois antérieurs']);
  fputcsv($out, ['Mois', 'Recettes', 'Dépenses', 'Solde du mois', 'Solde caisse cumulé']);
  if (empty($mois_anterieurs)){
    fputcsv($out, ['(aucun)', '', '', '', '']);
  } else {
    foreach($mois_anterieurs as $row){
      fputcsv($out, [
        $MONTHS_FR[$row['m']].' '.$row['y'],
        $row['rec'],
        $row['dep'],
        $row['solde_mois'],
        $row['solde_cum']
      ]);
    }
  }
  fputcsv($out, []);

  // Recettes — Somme par jour
  fputcsv($out, ['RECETTES — Somme par jour']);
  fputcsv($out, ['Date', 'Total jour']);
  ksort($rec_day_tot);
  foreach($rec_day_tot as $d=>$tot){
    fputcsv($out, [date('d/m/Y', strtotime($d)), $tot]);
  }
  fputcsv($out, ['TOTAL RECETTE', $totalRec]);
  fputcsv($out, []);

  // Dépenses
  fputcsv($out, ['DÉPENSES']);
  fputcsv($out, ['Date', 'N°', 'Libellé', 'Montant']);
  ksort($dep_by_date);
  foreach($dep_by_date as $d=>$rows){
    foreach($rows as $r){
      $num = ($r['numref']!==null && $r['numref']!=='') ? $r['numref'] : '-';
      fputcsv($out, [date('d/m/Y', strtotime($d)), $num, $r['libelle'], $r['montant']]);
    }
    fputcsv($out, [date('d/m/Y', strtotime($d)), '', 'Total du jour', $dep_day_tot[$d]]);
  }
  fputcsv($out, ['TOTAL DÉPENSE', '', '', $totalDep]);
  fputcsv($out, []);

  // SOLDE CAISSE (mois)
  fputcsv($out, ['SOLDE CAISSE (mois)', $solde_mois]);

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

.toolbar .field {
    display: flex;
    flex-direction: column;
    gap: 6px;
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
    /* background: #111827; */
    /* color: #fff; */
    text-decoration: none;
}

.btn:hover {
    filter: brightness(0.95);
    border: 1px solid;
}

/* .btn-outline {
    background: #fff;
    color: #111827;
    border-color: #cbd5e1;
} */

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

.rpt tfoot th {
    background: #f8fafc;
}

.group-date td {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.chip-date {
    display: inline-block;
    background: #e2e8f0;
    color: #0f172a;
    padding: 4px 10px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 12px;
}

.subtotal td {
    background: #fafafa;
    font-weight: 700;
}

.text-end {
    text-align: right;
}

.cell-empty {
    text-align: center;
    color: #94a3b8;
}

.pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 12px;
    background: #f8fafc;
    font-weight: 800;
}

.cards-row {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}

.col {
    flex: 1;
    min-width: 320px;
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
                <h3 class="card-title">Rapport mensuel — <?= h($isConnexe ? 'Frais connexe' : 'Frais scolaire') ?></h3>
                <div class="muted"><strong>Période :</strong> <?= h($MONTHS_FR[$mois].' '.$annee) ?></div>
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
                            <label>Mois</label>
                            <select name="mois" class="form-control">
                                <?php foreach($MONTHS_FR as $n=>$nom): ?>
                                <option value="<?=$n?>" <?=$mois==$n?'selected':''?>><?=h($nom)?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label>Année</label>
                            <input type="number" class="form-control" name="annee" value="<?=h($annee)?>" min="2000"
                                max="2100">
                        </div>
                    </div>

                    <div class="btn-group mt-2">
                        <button class="btn btn-primary" type="submit">Afficher</button>
                        <a href="?type=<?=h($type)?>&q=month" class="btn btn-dark">Ce mois-ci</a>
                        <a class="btn btn-success"
                            href="?type=<?=h($type)?>&mois=<?=h($mois)?>&annee=<?=h($annee)?>&export=csv">
                            Export CSV
                        </a>
                        <button type="button" class="btn btn-danger" onclick="window.print()">Imprimer
                        </button>
                        <a href="report_annuel.php" class="btn btn-dark">Report annuel</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- ====== CONTENU IMPRIMABLE ====== -->
        <div class="sheet" id="exportable">

            <!-- Soldes des mois antérieurs -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Soldes caisse — Mois antérieurs</h4>
                </div>
                <div class="card-body">
                    <table class="rpt">
                        <thead>
                            <tr>
                                <th style="width:220px;">Mois</th>
                                <th class="text-end" style="width:160px;">Recettes</th>
                                <th class="text-end" style="width:160px;">Dépenses</th>
                                <th class="text-end" style="width:160px;">Solde du mois</th>
                                <th class="text-end" style="width:180px;">Solde caisse cumulé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mois_anterieurs)): ?>
                            <tr>
                                <td colspan="5" class="cell-empty"><em>Aucun mois antérieur avec mouvement.</em></td>
                            </tr>
                            <?php else: ?>
                            <?php foreach($mois_anterieurs as $row): ?>
                            <tr>
                                <td><?= h($MONTHS_FR[$row['m']].' '.$row['y']) ?></td>
                                <td class="text-end mono"><?= money($row['rec']) ?></td>
                                <td class="text-end mono"><?= money($row['dep']) ?></td>
                                <td class="text-end mono"><?= money($row['solde_mois']) ?></td>
                                <td class="text-end mono"><strong><?= money($row['solde_cum']) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Deux colonnes : Recettes / Dépenses -->
            <div class="cards-row">

                <!-- RECETTES (Somme par jour) -->
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Recettes — Somme par jour</h4>
                            <div class="muted">Type : <strong><?= h($isConnexe ? 'Connexe' : 'Scolaire') ?></strong>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="rpt">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th class="text-end" style="width:180px;">Total du jour</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php render_recettes_totaux_par_jour($rec_day_tot); ?>
                                </tbody>
                            </table>
                            <div style="margin-top:10px;">
                                <span class="pill">TOTAL RECETTE : <span
                                        class="mono"><?= money($totalRec) ?></span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DÉPENSES (détaillées + total jour) -->
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Dépenses</h4>
                            <div class="muted">Référence :
                                <strong><?= h($isConnexe ? 'Frais Connexe' : 'Frais Scolaire') ?></strong>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="rpt">
                                <thead>
                                    <tr>
                                        <th style="width:90px;">N°</th>
                                        <th>Libellé</th>
                                        <th class="text-end" style="width:180px;">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php render_depenses_group($dep_by_date,$dep_day_tot); ?>
                                </tbody>
                            </table>
                            <div style="margin-top:10px;">
                                <span class="pill">TOTAL DÉPENSE : <span
                                        class="mono"><?= money($totalDep) ?></span></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- SOLDE DU MOIS -->
            <div class="summary">
                SOLDE CAISSE (mois) : <span class="mono"><?= money($solde_mois) ?></span>
            </div>

        </div><!-- /exportable -->
    </div>

    <?php require_once('../../layouts/constants/footer.php'); ?>
</div>