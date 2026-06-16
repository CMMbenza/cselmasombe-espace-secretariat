<?php
// /view/paiement_divers/index.php
require_once('../../layouts/constants/head.php');
require_once('../../layouts/navbar/navbar.php');

// Helpers
if (!function_exists('e'))  { function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('nf')) { function nf($n){ return number_format((float)$n, 2, ',', ' '); } }
if (!function_exists('fmt_ts')) { function fmt_ts($ts){ return $ts ? date('d/m/Y H:i', strtotime($ts)) : ''; } }
if (!function_exists('truncate')) { function truncate($s, $len=10){ $s=(string)$s; return mb_strlen($s,'UTF-8')>$len ? mb_substr($s,0,$len,'UTF-8').'…' : $s; } }
function keep_qs(array $extra = [], array $drop = []): string {
  $qs = $_GET;
  foreach ($drop as $d) unset($qs[$d]);
  foreach ($extra as $k=>$v) $qs[$k] = $v;
  return http_build_query($qs);
}

// Connexion
if (!isset($con) || !($con instanceof mysqli)) {
  require_once('../../webapp/database/config.php'); // doit définir $con
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$con->set_charset('utf8mb4');

// Inputs (GET)
$q        = trim($_GET['q']        ?? '');    // recherche globale
$type     = trim($_GET['type']     ?? '');    // type_frais exact
$menageId = trim($_GET['menage']   ?? '');    // id ménage
$menageNm = trim($_GET['menage_nm']?? '');    // nom ménage (like)
$annee    = trim($_GET['annee']    ?? '');    // année scolaire
$mois     = trim($_GET['mois']     ?? '');    // YYYY-MM
$export   = trim($_GET['export']   ?? '');    // '', 'csv', 'xls

// Tri (côté PHP)
$sort = $_GET['sort'] ?? 'id';
$dir  = strtolower((string)($_GET['dir'] ?? 'desc'));
$allowedSort = [
  'id'         => 'id',
  'menage_id'  => 'menage_id',
  'menage_nom' => 'menage_nom',
  'telephone'  => 'telephone',
  'type_frais' => 'type_frais',
  'mapayer'    => 'montantAPayer',
  'mpayer'     => 'montantPayer',
  'reste'      => 'resteAPayer',
  'date'       => 'dateCreated',
  'annee'      => 'anneeScolaire',
  'createdby'  => 'createdby',
];
$sortKey = $allowedSort[$sort] ?? 'id';
$dir     = in_array($dir, ['asc','desc'], true) ? $dir : 'desc';

// Récupération JOIN paiement_divers -> menage
$rows = [];
$types_uniques = [];
try {
  $sql = "
    SELECT
      pd.id,
      pd.menage               AS menage_id,
      m.noms                  AS menage_nom,
      m.telephone             AS telephone,
      pd.type_frais,
      pd.montantAPayer,
      pd.montantPayer,
      pd.resteAPayer,
      pd.observation,
      pd.createdby,
      pd.anneeScolaire,
      pd.dateCreated
    FROM paiement_divers pd
    JOIN menage m ON pd.menage = m.id
    WHERE NOT EXISTS (
      SELECT 1 FROM recu_annule ra
      WHERE ra.recu_id = pd.id
        AND ra.recu_type = 'connexe'
    )
    ORDER BY pd.id DESC
  ";
  $rs = $con->query($sql);
  while ($r = $rs->fetch_assoc()) {
    $rows[] = $r;
    if (!empty($r['type_frais'])) $types_uniques[$r['type_frais']] = true;
  }
} catch (Throwable $t) {
  $rows = [];
}
$types_uniques = array_keys($types_uniques);
sort($types_uniques, SORT_FLAG_CASE | SORT_STRING);

// Filtres (PHP)
$rows = array_values(array_filter($rows, function(array $r) use ($q,$type,$menageId,$menageNm,$annee,$mois){
  $ok = true;

  if ($q !== '') {
    $hay = mb_strtolower(
      ($r['type_frais'] ?? '').' '.
      ($r['observation'] ?? '').' '.
      ($r['createdby'] ?? '').' '.
      ($r['anneeScolaire'] ?? '').' '.
      ($r['menage_nom'] ?? '').' '.
      ($r['telephone'] ?? '').' '.
      (string)($r['menage_id'] ?? '')
    ,'UTF-8');
    $ok = $ok && (mb_strpos($hay, mb_strtolower($q,'UTF-8')) !== false);
  }
  if ($type !== '') {
    $ok = $ok && (strcasecmp((string)$r['type_frais'], $type) === 0);
  }
  if ($menageId !== '') {
    $ok = $ok && ((string)($r['menage_id'] ?? '') === $menageId);
  }
  if ($menageNm !== '') {
    $ok = $ok && (mb_stripos((string)($r['menage_nom'] ?? ''), $menageNm, 0, 'UTF-8') !== false);
  }
  if ($annee !== '') {
    $ok = $ok && ((string)($r['anneeScolaire'] ?? '') === $annee);
  }
  if ($mois !== '' && preg_match('~^\d{4}-\d{2}$~', $mois)) {
    $ok = $ok && (substr((string)($r['dateCreated'] ?? ''), 0, 7) === $mois);
  }
  return $ok;
}));

// Tri (PHP)
usort($rows, function($a,$b) use ($sortKey,$dir){
  $va = $a[$sortKey] ?? null;
  $vb = $b[$sortKey] ?? null;

  if (in_array($sortKey, ['id','menage_id'], true)) {
    $va = (int)$va; $vb = (int)$vb;
  } elseif (in_array($sortKey, ['montantAPayer','montantPayer','resteAPayer'], true)) {
    $va = (float)$va; $vb = (float)$vb;
  } elseif ($sortKey === 'dateCreated') {
    $va = strtotime((string)$va); $vb = strtotime((string)$vb);
  } else {
    $va = mb_strtolower((string)$va,'UTF-8');
    $vb = mb_strtolower((string)$vb,'UTF-8');
  }
  if ($va == $vb) return 0;
  $cmp = ($va < $vb) ? -1 : 1;
  return $dir === 'asc' ? $cmp : -$cmp;
});

// Totaux (vue filtrée)
$tot_a_payer = 0.0;
$tot_paye    = 0.0;
$tot_reste   = 0.0;
foreach ($rows as $r) {
  $tot_a_payer += (float)($r['montantAPayer'] ?? 0);
  $tot_paye    += (float)($r['montantPayer'] ?? 0);
  $tot_reste   += (float)($r['resteAPayer'] ?? 0);
}

// Exports
if ($export === 'csv') {
  $filename = 'paiement_divers_'.date('Ymd_His').'.csv';
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  echo "\xEF\xBB\xBF";
  $out = fopen('php://output', 'w');
  fputcsv($out, ['ID','Ménage ID','Ménage (Nom)','Téléphone','Type frais','À payer','Payé','Reste','Observation','Créé par','Année','Date']);
  foreach ($rows as $r) {
    fputcsv($out, [
      $r['id'] ?? '',
      $r['menage_id'] ?? '',
      $r['menage_nom'] ?? '',
      $r['telephone'] ?? '',
      $r['type_frais'] ?? '',
      number_format((float)($r['montantAPayer'] ?? 0), 2, '.', ''),
      number_format((float)($r['montantPayer'] ?? 0), 2, '.', ''),
      number_format((float)($r['resteAPayer'] ?? 0), 2, '.', ''),
      $r['observation'] ?? '',
      $r['createdby'] ?? '',
      $r['anneeScolaire'] ?? '',
      $r['dateCreated'] ?? '',
    ]);
  }
  fclose($out);
  exit;
}
if ($export === 'xls') {
  $filename = 'paiement_divers_'.date('Ymd_His').'.xls';
  header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  echo "<table border='1'>";
  echo "<tr>
          <th>ID</th><th>Ménage ID</th><th>Ménage (Nom)</th><th>Téléphone</th>
          <th>Type frais</th><th>À payer</th><th>Payé</th><th>Reste</th>
          <th>Observation</th><th>Créé par</th><th>Année</th><th>Date</th>
        </tr>";
  foreach ($rows as $r) {
    echo "<tr>";
    echo "<td>".e($r['id'] ?? '')."</td>";
    echo "<td>".e($r['menage_id'] ?? '')."</td>";
    echo "<td>".e($r['menage_nom'] ?? '')."</td>";
    echo "<td>".e($r['telephone'] ?? '')."</td>";
    echo "<td>".e($r['type_frais'] ?? '')."</td>";
    echo "<td>".e(number_format((float)($r['montantAPayer'] ?? 0), 2, '.', ''))."</td>";
    echo "<td>".e(number_format((float)($r['montantPayer'] ?? 0), 2, '.', ''))."</td>";
    echo "<td>".e(number_format((float)($r['resteAPayer'] ?? 0), 2, '.', ''))."</td>";
    echo "<td>".e($r['observation'] ?? '')."</td>";
    echo "<td>".e($r['createdby'] ?? '')."</td>";
    echo "<td>".e($r['anneeScolaire'] ?? '')."</td>";
    echo "<td>".e($r['dateCreated'] ?? '')."</td>";
    echo "</tr>";
  }
  echo "</table>";
  exit;
}
?>
<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">

                        <h3 class="card-title d-flex align-items-center gap-2">
                            <span class="menu-icon"><i class="fa fa-list"></i></span>
                            Paiements divers (avec Ménage)
                        </h3>
                        <button class="btn btn-dark" onclick="history.back()">Retour</button>
                        <!-- Filtres -->
                        <form class="mt-3" method="get" id="filterForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Recherche globale</label>
                                    <input type="text" name="q" value="<?php echo e($q); ?>" class="form-control"
                                        placeholder="type, obs., année, ménage, téléphone, créé par">
                                </div>
                                <div class="d-none col-md-3">
                                    <label class="form-label">Type de frais</label>
                                    <input list="types_frais" name="type" value="<?php echo e($type); ?>"
                                        class="form-control" placeholder="ex: Transport">
                                    <datalist id="types_frais">
                                        <?php foreach ($types_uniques as $t): ?>
                                        <option value="<?php echo e($t); ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                                <div class="d-none col-md-2">
                                    <label class="form-label">Ménage (ID)</label>
                                    <input type="number" name="menage" value="<?php echo e($menageId); ?>"
                                        class="form-control" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Nom ménage</label>
                                    <input type="text" name="menage_nm" value="<?php echo e($menageNm); ?>"
                                        class="form-control" placeholder="Nom du ménage">
                                </div>
                                <div class="d-none col-md-2">
                                    <label class="form-label">Année scolaire</label>
                                    <input type="text" name="annee" value="<?php echo e($annee); ?>"
                                        class="form-control" placeholder="2025-2026">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Mois</label>
                                    <input type="month" name="mois" value="<?php echo e($mois); ?>"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-2">
                                    <label class="form-label text-muted">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fa fa-filter me-1"></i> Filtrer
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted">&nbsp;</label>
                                    <a href="?<?php echo keep_qs([], array_keys($_GET)); ?>"
                                        class="btn btn-danger w-100">
                                        Réinitialiser
                                    </a>
                                </div>
                                <div class="col-md-3 ms-auto">
                                    <label class="form-label text-muted">&nbsp;</label>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a class="btn btn-outline-success"
                                            href="?<?php echo keep_qs(['export'=>'csv']); ?>">
                                            <i class="fa fa-file-excel-o me-1"></i> CSV
                                        </a>
                                        <a class="btn btn-outline-success"
                                            href="?<?php echo keep_qs(['export'=>'xls']); ?>">
                                            <i class="fa fa-file-excel-o me-1"></i> XLS
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Bandeau totaux -->
                        <div class="mt-3 small text-muted">
                            <span class="me-3">Lignes : <strong><?php echo count($rows); ?></strong></span>
                            <span class="me-3">À payer total : <strong><?php echo nf($tot_a_payer); ?> $</strong></span>
                            <span class="me-3">Payé total : <strong><?php echo nf($tot_paye); ?> $</strong></span>
                            <span>Reste total : <strong><?php echo nf($tot_reste); ?> $</strong></span>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau -->
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Liste</h4>

                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input type="text" class="form-control" id="searchInput" style="max-width:320px"
                                placeholder="Recherche instantanée (toutes colonnes)" onkeyup="instantSearch()">
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle" id="myTable">
                                <thead>
                                    <?php
                    $toggleDir = ($dir === 'asc') ? 'desc' : 'asc';
                    $makeSort = function(string $key, string $label) use ($toggleDir, $sort, $dir) {
                      $icon = '';
                      if ($sort === $key) $icon = $dir === 'asc' ? ' ▲' : ' ▼';
                      $qs = keep_qs(['sort'=>$key, 'dir'=>($sort===$key ? $toggleDir : 'asc')]);
                      return '<a href="?'.$qs.'" class="text-decoration-none">'.$label.$icon.'</a>';
                    };
                  ?>
                                    <tr>
                                        <th><?php echo $makeSort('id','ID'); ?></th>
                                        <th><?php echo $makeSort('menage_nom','Ménage (Nom)'); ?></th>
                                        <th><?php echo $makeSort('telephone','Téléphone'); ?></th>
                                        <th class="text-end"><?php echo $makeSort('mapayer','À payer'); ?></th>
                                        <th class="text-end"><?php echo $makeSort('mpayer','Payé'); ?></th>
                                        <th class="text-end"><?php echo $makeSort('reste','Reste'); ?></th>
                                        <th>Observation</th>
                                        <th><?php echo $makeSort('date','Date'); ?></th>
                                        <th>Imprimer</th>
                                        <th>Annuler</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    <?php if (empty($rows)): ?>
                                    <tr>
                                        <td colspan="10">
                                            <div class="alert alert-warning mb-0">Aucun paiement trouvé.</div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td><?php echo e($r['id']); ?></td>
                                        <td>
                                          <span class="badge bg-secondary me-1">Connexe</span>
                                          <?php echo e($r['menage_nom']); ?>
                                        </td>
                                        <td><?php echo e($r['telephone']); ?></td>
                                        <td class="text-end"><?php echo nf($r['montantAPayer']); ?> $</td>
                                        <td class="text-end"><?php echo nf($r['montantPayer']); ?> $</td>
                                        <td class="text-end"><?php echo nf($r['resteAPayer']); ?> $</td>
                                        <td title="<?php echo e($r['observation']); ?>">
                                            <?php echo e(truncate($r['observation'], 10)); ?></td>
                                        <td><?php echo e(fmt_ts($r['dateCreated'])); ?></td>
                                        <td>
                                            <a class="btn btn-danger"
                                               href="apercu_recu_divers.php?ordre=<?php echo (int)$r['id']; ?>">
                                               Imprimer
                                            </a>
                                        </td>
                                        <td>
                                            <button
                                              class="btn btn-outline-warning btn-annuler"
                                              data-recu-id="<?php echo (int)$r['id']; ?>"
                                              data-recu-type="connexe"
                                              data-montant="<?php echo nf($r['montantPayer']); ?>"
                                            >Annuler</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php require_once('../../layouts/constants/footer.php'); ?>
</div>

<!-- Modal Annulation (commun) -->
<div class="modal fade" id="modalAnnuler" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAnnuler" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Annuler le reçu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="recu_id" id="annul_recu_id">
        <input type="hidden" name="recu_type" id="annul_recu_type">
        <div class="mb-2">
          <label class="form-label">Montant</label>
          <input type="text" id="annul_montant" class="form-control" disabled>
        </div>
        <div class="mb-2">
          <label class="form-label">Motif (optionnel)</label>
          <input type="text" name="motif" class="form-control" placeholder="Ex: erreur de saisie">
        </div>
        <div class="mb-2">
          <label class="form-label">Code de confirmation</label>
          <input type="password" name="code" class="form-control" placeholder="Tapez 1024" required>
        </div>
        <div class="alert alert-warning small mb-0">
          L’annulation retirera ce reçu de tous les totaux et historiques (sans le supprimer physiquement).
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Fermer</button>
        <button class="btn btn-warning" type="submit">Confirmer l’annulation</button>
      </div>
    </form>
  </div>
</div>

<script>
function instantSearch() {
    const input = document.getElementById('searchInput');
    const filter = (input.value || '').toLowerCase();
    const table = document.getElementById('myTable');
    const trs = table.getElementsByTagName('tr');

    for (let i = 1; i < trs.length; i++) { // skip thead
        const tds = trs[i].getElementsByTagName('td');
        if (!tds || tds.length === 0) continue;

        let rowText = '';
        for (let j = 0; j < tds.length; j++) {
            rowText += (tds[j].innerText || tds[j].textContent) + ' ';
        }
        trs[i].style.display = rowText.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
    }
}

// Modal Annulation : ouverture + submit
(function(){
  let modalEl = document.getElementById('modalAnnuler');
  let bsModal = null;
  function ensureModal(){
    if (!bsModal && window.bootstrap && bootstrap.Modal) {
      bsModal = new bootstrap.Modal(modalEl);
    }
  }

  document.querySelectorAll('.btn-annuler').forEach(btn => {
    btn.addEventListener('click', function(){
      ensureModal();
      document.getElementById('annul_recu_id').value = this.dataset.recuId;
      document.getElementById('annul_recu_type').value = this.dataset.recuType;
      document.getElementById('annul_montant').value = this.dataset.montant + ' $';
      if (bsModal) bsModal.show();
    });
  });

  const form = document.getElementById('formAnnuler');
  form && form.addEventListener('submit', async function(e){
    e.preventDefault();
    const fd = new FormData(this);
    try{
      // cancel_recu.php placé dans le même dossier
      const r = await fetch('cancel_recu.php', { method: 'POST', body: fd });
      const js = await r.json();
      if(!js.ok){ alert(js.message || 'Échec annulation'); return; }
      alert('Reçu annulé ✔');
      window.location.reload();
    }catch(err){
      alert('Erreur réseau.');
    }
  });
})();
</script>
