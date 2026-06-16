<?php
require_once('../../layouts/constants/head.php');
require_once('../../webapp/database/config.php');
require_once('../../layouts/navbar/navbar.php');

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($n){ return number_format((float)$n, 2, '.', ' ') . ' $'; }

/* ---- Barèmes connexe par cycle ---- */
const TARIF_MP = 50.0; // Maternelle + Primaire
const TARIF_HS = 70.0; // Humanité + Secondaire

/* ---- Filtres ---- */
$annee  = isset($_GET['annee']) ? trim($_GET['annee']) : '';
$du     = isset($_GET['du']) && $_GET['du']!=='' ? $_GET['du'] : '';
$au     = isset($_GET['au']) && $_GET['au']!=='' ? $_GET['au'] : '';
$cycleF = isset($_GET['cycle_id']) && $_GET['cycle_id']!=='' ? (int)$_GET['cycle_id'] : null; // filtre d’affichage côté élèves
$q      = isset($_GET['q']) ? trim($_GET['q']) : '';
$do_csv = (isset($_GET['export']) && $_GET['export']==='csv');

/* ---- 1) Charger tous les cycles & faire 2 groupes par heuristique ---- */
$cycles = [];
$MP_ids = [];  // Maternelle/Primaire
$HS_ids = [];  // Humanité/Secondaire

if ($rs = $con->query("SELECT id, description FROM cycle ORDER BY id")) {
  while($r=$rs->fetch_assoc()){
    $cycles[] = $r;
    if (preg_match('/mat|prim/i', $r['description'])) {
      $MP_ids[] = (int)$r['id'];
    } elseif (preg_match('/human|second/i', $r['description'])) {
      $HS_ids[] = (int)$r['id'];
    } else {
      // Par défaut, on met en Hum./Sec. (ajustez si besoin)
      $HS_ids[] = (int)$r['id'];
    }
  }
}

/* ---- 2) Charger tous les élèves (avec classe/cycle) par ménage + montantAPayer ---- */
$sql = "
  SELECT
    m.id  AS menage_id,
    m.noms AS menage,
    COALESCE(m.montantAPayer,0) AS scol_due,   -- montant à payer (scolaire)
    e.id  AS eleve_id,
    c.id  AS classe_id,
    c.description AS classe_lib,
    cy.id AS cycle_id,
    cy.description AS cycle_lib
  FROM menage m
  LEFT JOIN eleve e  ON e.menage = m.id
  LEFT JOIN classe c ON c.id = e.classe
  LEFT JOIN cycle cy ON cy.id = c.cycle
  WHERE 1=1
";
$types=''; $params=[];

if ($q!==''){ $sql.=" AND m.noms LIKE ?"; $types.='s'; $params[]='%'.$q.'%'; }
if (!is_null($cycleF)){ $sql.=" AND cy.id = ?"; $types.='i'; $params[]=$cycleF; }
if ($annee!==''){ $sql.=" AND (e.anneeScolaire = ? OR e.anneeScolaire IS NULL)"; $types.='s'; $params[]=$annee; }

$sql.=" ORDER BY m.noms, e.id";

$st = $con->prepare($sql);
if ($types!=='') $st->bind_param($types, ...$params);
$st->execute();
$rs = $st->get_result();
$st->close();

/* ---- 3) Regrouper & compter MP/HS + init champs scolaires ---- */
$families = []; // menage_id => info
if ($rs) {
  while($row=$rs->fetch_assoc()){
    $mid = (int)$row['menage_id'];
    if (!isset($families[$mid])) {
      $families[$mid] = [
        'menage_id'     => $mid,
        'menage'        => $row['menage'],
        'nb_total'      => 0,
        'nb_mp'         => 0,
        'nb_hs'         => 0,
        'classes_list'  => [],
        'montant_mp'    => 0.0,
        'montant_hs'    => 0.0,
        'montant_due'   => 0.0, // connexe dû
        'paye_divers'   => 0.0, // connexe payé
        'reste'         => 0.0, // connexe reste

        // --- FRAIS SCOLAIRE ---
        'scol_due'      => (float)$row['scol_due'], // menage.montantAPayer
        'scol_paye'     => 0.0,                    // SUM(paiement.montantPayer)
        'scol_reste'    => 0.0,
      ];
    }

    if (!empty($row['eleve_id'])) {
      $families[$mid]['nb_total']++;
      $families[$mid]['classes_list'][] = $row['classe_lib'].' ('.$row['cycle_lib'].')';

      $cyid = (int)$row['cycle_id'];
      if (in_array($cyid, $MP_ids, true)) {
        $families[$mid]['nb_mp']++;
      } else {
        $families[$mid]['nb_hs']++;
      }
    }
  }
}

/* ---- 4) Calculer montants Connexe par famille ---- */
foreach($families as &$f){
  $f['montant_mp']  = $f['nb_mp'] * TARIF_MP;
  $f['montant_hs']  = $f['nb_hs'] * TARIF_HS;
  $f['montant_due'] = $f['montant_mp'] + $f['montant_hs']; // connexe dû
  $f['classes_str'] = empty($f['classes_list']) ? '' : implode(', ', array_unique($f['classes_list']));
}
unset($f);

/* ---- 5) Paiements : Connexe (paiement_divers) + Scolaire (paiement) ---- */
$ids = array_keys($families);
if (!empty($ids)) {
  $in = implode(',', array_fill(0, count($ids), '?'));

  // --- Connexe ---
  $sqlP = "SELECT menage, COALESCE(SUM(montantPayer),0) AS tot
           FROM paiement_divers
           WHERE menage IN ($in)";
  $typesP = str_repeat('i', count($ids));
  $paramsP = $ids;

  if ($annee!==''){ $sqlP.=" AND (anneeScolaire = ? OR anneeScolaire IS NULL)"; $typesP.='s'; $paramsP[]=$annee; }
  if ($du!==''){    $sqlP.=" AND DATE(dateCreated) >= ?";                       $typesP.='s'; $paramsP[]=$du; }
  if ($au!==''){    $sqlP.=" AND DATE(dateCreated) <= ?";                       $typesP.='s'; $paramsP[]=$au; }

  $sqlP.=" GROUP BY menage";
  $st=$con->prepare($sqlP);
  $st->bind_param($typesP, ...$paramsP);
  $st->execute();
  $r=$st->get_result();
  while($p=$r->fetch_assoc()){
    $mid=(int)$p['menage'];
    if (isset($families[$mid])) {
      $families[$mid]['paye_divers'] = (float)$p['tot']; // connexe payé
    }
  }
  $st->close();

  // --- Scolaire ---
  $sqlS = "SELECT menage, COALESCE(SUM(montantPayer),0) AS tot
           FROM paiement
           WHERE menage IN ($in)";
  $typesS = str_repeat('i', count($ids));
  $paramsS = $ids;

  if ($annee!==''){ $sqlS.=" AND (anneeScolaire = ? OR anneeScolaire IS NULL)"; $typesS.='s'; $paramsS[]=$annee; }
  if ($du!==''){    $sqlS.=" AND DATE(dateCreated) >= ?";                       $typesS.='s'; $paramsS[]=$du; }
  if ($au!==''){    $sqlS.=" AND DATE(dateCreated) <= ?";                       $typesS.='s'; $paramsS[]=$au; }

  $sqlS.=" GROUP BY menage";
  $st=$con->prepare($sqlS);
  $st->bind_param($typesS, ...$paramsS);
  $st->execute();
  $r=$st->get_result();
  while($p=$r->fetch_assoc()){
    $mid=(int)$p['menage'];
    if (isset($families[$mid])) {
      $families[$mid]['scol_paye'] = (float)$p['tot']; // scolaire payé
    }
  }
  $st->close();
}

/* ---- 6) Finaliser RESTES & totaux ---- */
$total_nb         = 0;
$total_nb_mp      = 0;
$total_nb_hs      = 0;
$total_mp_amt     = 0.0;   // Somme générale Montant Mat./Prim. (connexe)
$total_hs_amt     = 0.0;   // Somme générale Montant Hum./Sec. (connexe)
$total_due        = 0.0;   // Connexe dû total (MP + HS)
$total_pay        = 0.0;   // Connexe payé total
$total_reste      = 0.0;   // Connexe reste total

$total_scol_due   = 0.0;   // Scolaire dû (menage.montantAPayer)
$total_scol_paye  = 0.0;   // Scolaire payé (paiement)
$total_scol_reste = 0.0;   // Scolaire reste

foreach($families as &$f){
  // Connexe
  $f['reste'] = max($f['montant_due'] - $f['paye_divers'], 0.0);

  // Scolaire
  $f['scol_reste'] = max($f['scol_due'] - $f['scol_paye'], 0.0);

  // Totaux
  $total_nb     += $f['nb_total'];
  $total_nb_mp  += $f['nb_mp'];
  $total_nb_hs  += $f['nb_hs'];

  $total_mp_amt += $f['montant_mp'];
  $total_hs_amt += $f['montant_hs'];
  $total_due    += $f['montant_due'];
  $total_pay    += $f['paye_divers'];
  $total_reste  += $f['reste'];

  $total_scol_due   += $f['scol_due'];
  $total_scol_paye  += $f['scol_paye'];
  $total_scol_reste += $f['scol_reste'];
}
unset($f);

$rows = array_values($families);
usort($rows, fn($a,$b)=> strcmp($a['menage'],$b['menage']));

/* ---- 7) EXPORT CSV (avec colonnes scolaires ajoutées) ---- */
if ($do_csv) {
  $fname = 'situation_frais_connexe_'.date('Ymd_His').'.csv';
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="'.$fname.'"');
  header('Pragma: no-cache');
  header('Expires: 0');

  $out = fopen('php://output', 'w');
  // BOM UTF-8
  fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

  // En-tête contexte
  fputcsv($out, ['Situation financière — Frais connexes (familles) + Frais scolaires']);
  $per = [];
  if ($annee!=='') $per[] = 'Année scolaire: '.$annee;
  if ($du!=='')    $per[] = 'Du: '.$du;
  if ($au!=='')    $per[] = 'Au: '.$au;
  if (!empty($per)) fputcsv($out, [implode(' | ', $per)]);
  fputcsv($out, []);

  // Colonnes
  fputcsv($out, [
    'Ménage',
    'Classes & cycles',
    '# Enfants',
    '# Mat./Prim.',
    'Montant Mat./Prim. (connexe)',
    '# Hum./Sec.',
    'Montant Hum./Sec. (connexe)',
    'Connexe à payer (total)',
    'Connexe payé',
    'Connexe reste',
    'Scolaire à payer',
    'Scolaire payé',
    'Scolaire reste',
  ]);

  foreach($rows as $r){
    fputcsv($out, [
      $r['menage'],
      $r['classes_str'],
      (int)$r['nb_total'],
      (int)$r['nb_mp'],
      number_format((float)$r['montant_mp'], 2, '.', ''),
      (int)$r['nb_hs'],
      number_format((float)$r['montant_hs'], 2, '.', ''),
      number_format((float)$r['montant_due'], 2, '.', ''),
      number_format((float)$r['paye_divers'], 2, '.', ''),
      number_format((float)$r['reste'], 2, '.', ''),

      number_format((float)$r['scol_due'], 2, '.', ''),
      number_format((float)$r['scol_paye'], 2, '.', ''),
      number_format((float)$r['scol_reste'], 2, '.', ''),
    ]);
  }

  // Totaux
  fputcsv($out, []);
  fputcsv($out, [
    'TOTALS', '',
    (int)$total_nb,
    (int)$total_nb_mp,
    number_format($total_mp_amt, 2, '.', ''),
    (int)$total_nb_hs,
    number_format($total_hs_amt, 2, '.', ''),
    number_format($total_due, 2, '.', ''),
    number_format($total_pay, 2, '.', ''),
    number_format($total_reste, 2, '.', ''),
    number_format($total_scol_due, 2, '.', ''),
    number_format($total_scol_paye, 2, '.', ''),
    number_format($total_scol_reste, 2, '.', ''),
  ]);

  fclose($out);
  exit;
}
?>
<style>
  .filters{display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:12px}
  .filters .field{display:flex;flex-direction:column;gap:4px}
  .table-responsive{overflow:auto}
  table.sf{width:100%;border-collapse:collapse;font-size:13px}
  table.sf th, table.sf td{border:1px solid #e5e7eb;padding:6px 8px;vertical-align:top}
  table.sf thead th{background:#f8fafc;font-weight:800}
  .text-end{text-align:right}
  .mono{font-family: ui-monospace, Menlo, Consolas, "Courier New", monospace}
  .nowrap{white-space:nowrap}
  .grp{background:#f1f5f9}
</style>

<div class="main-panel">
  <div class="content-wrapper">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><i class="fa fa-users"></i> Situation financière — Connexe + Scolaire (familles)</h3>
        <div class="mb-2 text-muted">
          Barèmes connexes : <strong>Maternelle &amp; Primaire = <?=money(TARIF_MP)?></strong> •
          <strong>Humanité &amp; Secondaire = <?=money(TARIF_HS)?></strong> (par enfant).
        </div>

        <form class="filters" method="get">
          <div class="field">
            <label>Année scolaire</label>
            <input type="text" name="annee" class="form-control" placeholder="ex: 2024-2025" value="<?=h($annee)?>">
          </div>
          <div class="field">
            <label>Du</label>
            <input type="date" name="du" class="form-control" value="<?=h($du)?>">
          </div>
          <div class="field">
            <label>Au</label>
            <input type="date" name="au" class="form-control" value="<?=h($au)?>">
          </div>
          <div class="field">
            <label>Cycle (affichage)</label>
            <select name="cycle_id" class="form-control">
              <option value="">-- Tous --</option>
              <?php foreach($cycles as $c): ?>
                <option value="<?=$c['id']?>" <?=(!is_null($cycleF)&&$cycleF==$c['id'])?'selected':''?>><?=h($c['description'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field" style="min-width:280px;">
            <label>Recherche ménage</label>
            <input type="text" name="q" class="form-control" placeholder="Nom ménage…" value="<?=h($q)?>">
          </div>
          <div class="field">
            <label>&nbsp;</label>
            <button class="btn btn-primary">Filtrer</button>
          </div>
          <div class="field">
            <label>&nbsp;</label>
            <a class="btn btn-success"
               href="?export=csv&annee=<?=h($annee)?>&du=<?=h($du)?>&au=<?=h($au)?>&cycle_id=<?=h($cycleF)?>&q=<?=h($q)?>">
              Export CSV
            </a>
          </div>
        </form>

        <div class="table-responsive">
          <table class="sf">
            <thead>
              <tr>
                <th rowspan="2">Ménage</th>
                <th rowspan="2">Classes & cycles</th>
                <th rowspan="2" class="text-end"># Enfants</th>

                <th colspan="4" class="grp">Connexe (par groupe)</th>
                <th colspan="3" class="grp">Connexe (récap)</th>
                <th colspan="3" class="grp">Frais scolaire</th>
              </tr>
              <tr>
                <th class="text-end grp"># Mat./Prim.</th>
                <th class="text-end grp">Montant Mat./Prim.</th>
                <th class="text-end grp"># Hum./Sec.</th>
                <th class="text-end grp">Montant Hum./Sec.</th>

                <th class="text-end grp">À payer (total)</th>
                <th class="text-end grp">Payé</th>
                <th class="text-end grp">Reste</th>

                <th class="text-end grp">Montant à payer</th>
                <th class="text-end grp">Montant payé</th>
                <th class="text-end grp">Reste</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr><td colspan="13" class="text-center text-muted"><em>Aucune donnée.</em></td></tr>
              <?php else: foreach($rows as $r): ?>
                <tr>
                  <td><?= h($r['menage']) ?></td>
                  <td><?= h($r['classes_str']) ?></td>
                  <td class="text-end mono"><?= (int)$r['nb_total'] ?></td>

                  <td class="text-end mono"><?= (int)$r['nb_mp'] ?></td>
                  <td class="text-end mono"><?= money($r['montant_mp']) ?></td>

                  <td class="text-end mono"><?= (int)$r['nb_hs'] ?></td>
                  <td class="text-end mono"><?= money($r['montant_hs']) ?></td>

                  <td class="text-end mono"><strong><?= money($r['montant_due']) ?></strong></td>
                  <td class="text-end mono text-success"><?= money($r['paye_divers']) ?></td>
                  <td class="text-end mono text-danger"><strong><?= money($r['reste']) ?></strong></td>

                  <td class="text-end mono"><?= money($r['scol_due']) ?></td>
                  <td class="text-end mono text-success"><?= money($r['scol_paye']) ?></td>
                  <td class="text-end mono text-danger"><strong><?= money($r['scol_reste']) ?></strong></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>

            <?php if (!empty($rows)): ?>
            <tfoot>
              <tr>
                <th colspan="2" class="text-end">TOTAL</th>
                <th class="text-end mono"><?= (int)$total_nb ?></th>

                <th class="text-end mono"><?= (int)$total_nb_mp ?></th>
                <th class="text-end mono"><?= money($total_mp_amt) ?></th>

                <th class="text-end mono"><?= (int)$total_nb_hs ?></th>
                <th class="text-end mono"><?= money($total_hs_amt) ?></th>

                <th class="text-end mono"><?= money($total_due) ?></th>
                <th class="text-end mono"><?= money($total_pay) ?></th>
                <th class="text-end mono"><?= money($total_reste) ?></th>

                <th class="text-end mono"><?= money($total_scol_due) ?></th>
                <th class="text-end mono"><?= money($total_scol_paye) ?></th>
                <th class="text-end mono"><?= money($total_scol_reste) ?></th>
              </tr>
            </tfoot>
            <?php endif; ?>
          </table>
        </div>

      </div>
    </div>
  </div>
  <?php require_once('../../layouts/constants/footer.php'); ?>
</div>
