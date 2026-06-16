<?php
// /votre/chemin/vers/depenses/index.php
declare(strict_types=1);

require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/depenses.service.php'); // expose $rows, $total_count, $total_montant_filtered, $total_mois_encours, etc.
require_once ('../../layouts/navbar/navbar.php');

/* Helpers */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('fmt_money')) {
  function fmt_money($n){ return number_format((float)$n, 2, ',', ' '); }
}
if (!function_exists('fmt_date')) {
  function fmt_date($d){ return ($d && $d !== '0000-00-00') ? date('d/m/Y', strtotime($d)) : ''; }
}
if (!function_exists('str_limit')) {
  function str_limit($s, int $n = 64){
    $s = (string)$s;
    if ($n <= 0) return '';
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
      return (mb_strlen($s, 'UTF-8') <= $n) ? $s : (mb_substr($s, 0, $n, 'UTF-8').'…');
    } else {
      return (strlen($s) <= $n) ? $s : (substr($s, 0, $n).'…');
    }
  }
}
function keep_qs(array $extra = [], array $drop = []): string {
  $qs = $_GET;
  foreach ($drop as $d) unset($qs[$d]);
  foreach ($extra as $k=>$v) $qs[$k] = $v;
  return http_build_query($qs);
}

/* Lire les GET actuels exposés par le service */
$q      = $q      ?? '';
$ref    = $ref    ?? '';
$ben    = $ben    ?? '';
$annee  = $annee  ?? '';
$mois   = $mois   ?? '';
$sort   = $sort   ?? 'id';
$dir    = $dir    ?? 'desc';
$page   = $page   ?? 1;
$per_page = $per_page ?? 25;

$toggleDir = ($dir === 'asc') ? 'desc' : 'asc';
$makeSort = function(string $key, string $label) use ($toggleDir, $sort, $dir) {
  $icon = '';
  if ($sort === $key) $icon = $dir === 'asc' ? ' ▲' : ' ▼';
  $qs = keep_qs(['sort'=>$key, 'dir'=>($sort===$key ? $toggleDir : 'asc'), 'page'=>1]); // reset page on new sort
  return '<a href="?'.$qs.'" class="text-decoration-none">'.$label.$icon.'</a>';
};

/* ===== Exports (sur la vue filtrée complète, sans pagination) ===== */
$export = trim($_GET['export'] ?? '');
if ($export === 'csv' || $export === 'xls') {
  $filename = 'depenses_'.date('Ymd_His').'.'.$export;

  if ($export === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    echo "\xEF\xBB\xBF"; // BOM
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Référence','N°','Bénéficiaire','Motif','Montant','Date sortie','Année','Créé par']);
    foreach ($rows as $r) {
      fputcsv($out, [
        $r['id'] ?? '',
        $r['reference'] ?? '',
        $r['numero_reference'] ?? '',
        $r['beneficiaire'] ?? '',
        $r['description'] ?? '',
        number_format((float)($r['montant'] ?? 0), 2, '.', ''),
        $r['dateCreaty'] ?? '',
        $r['anneeScolaire'] ?? '',
        $r['createdby'] ?? '',
      ]);
    }
    fclose($out);
    exit;
  } else {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th><th>Référence</th><th>N°</th><th>Bénéficiaire</th><th>Motif</th>
            <th>Montant</th><th>Date sortie</th><th>Année</th><th>Créé par</th>
          </tr>";
    foreach ($rows as $r) {
      echo "<tr>";
      echo "<td>".e($r['id'] ?? '')."</td>";
      echo "<td>".e($r['reference'] ?? '')."</td>";
      echo "<td>".e($r['numero_reference'] ?? '')."</td>";
      echo "<td>".e($r['beneficiaire'] ?? '')."</td>";
      echo "<td>".e($r['description'] ?? '')."</td>";
      echo "<td>".e(number_format((float)($r['montant'] ?? 0), 2, '.', ''))."</td>";
      echo "<td>".e($r['dateCreaty'] ?? '')."</td>";
      echo "<td>".e($r['anneeScolaire'] ?? '')."</td>";
      echo "<td>".e($r['createdby'] ?? '')."</td>";
      echo "</tr>";
    }
    echo "</table>";
    exit;
  }
}

/* Pagination UI */
$total_count = $total_count ?? 0;
$total_pages = max(1, (int)ceil($total_count / $per_page));
$start_idx   = ($page - 1) * $per_page + 1;
$end_idx     = min($total_count, $page * $per_page);
$total_montant_filtered = $total_montant_filtered ?? 0.0;
$total_mois_encours     = $total_mois_encours     ?? 0.0;
$ym_now                 = date('Y-m');
?>
<style>
.table thead th {
    position: sticky;
    top: 0;
    background: #fff;
    z-index: 5;
}
</style>

<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title d-flex align-items-center gap-2">
                            <span class="menu-icon"><i class="fa fa-money"></i></span>
                            Gestion des dépenses
                        </h3>

                        <div class="mt-2 d-flex gap-2 flex-wrap">
                            <a href="create-update.php" class="btn btn-success enter-btn">
                                <i class="fa fa-plus-circle me-1"></i> Nouvelle sortie
                            </a>
                        </div>

                        <!-- Filtres -->
                        <form class="mt-3" method="get" id="filterForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Recherche globale</label>
                                    <input type="text" name="q" value="<?php echo e($q); ?>" class="form-control"
                                        placeholder="réf, bénéficiaire, motif">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Référence</label>
                                    <!-- <input type="text" name="ref" value="<?php echo e($ref); ?>" class="form-control"
                                        placeholder="Frais Scolaire / Connexe"> -->
                                        <select name="ref" id="ref" class="form-control">
                                            <option value="#" disabled selected>Sélectionner réf</option>
                                            <option value="Frais Scolaire">Frais Scolaire</option>
                                            <option value="Frais Connexe">Frais Connexe</option>
                                        </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Bénéficiaire</label>
                                    <input type="text" name="ben" value="<?php echo e($ben); ?>" class="form-control"
                                        placeholder="Nom du bénéficiaire">
                                </div>
                                <div class="col-md-2">
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

                            <div class="row g-3 mt-2 align-items-end">
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fa fa-filter me-1"></i> Filtrer
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <a href="index.php" class="btn btn-danger w-100">Réinitialiser</a>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Lignes par page</label>
                                    <select class="form-control"
                                        onchange="location.href='?<?php echo keep_qs(['per_page'=>'__pp__','page'=>1]); ?>'.replace('__pp__', this.value)">
                                        <?php foreach ([10,25,50,100,200] as $pp): ?>
                                        <option value="<?php echo $pp; ?>"
                                            <?php echo ($per_page==$pp)?'selected':''; ?>><?php echo $pp; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 ms-auto">
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
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-primary me-2">
                                    Total mois en cours (<?php echo e($ym_now); ?>) :
                                    <strong><?php echo fmt_money($total_mois_encours); ?> $</strong>
                                </span>
                                <span class="badge bg-dark">
                                    Total affiché (après filtres) :
                                    <strong><?php echo fmt_money($total_montant_filtered); ?> $</strong>
                                </span>
                            </div>
                            <div class="small text-muted">
                                Lignes <?php echo $total_count ? ($start_idx.'–'.$end_idx.' / '.$total_count) : '0'; ?>
                            </div>
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
                        <h4 class="card-title">Liste des dépenses</h4>

                        <div class="table-responsive">
                            <table class="table align-middle" id="myTable">
                                <thead>
                                    <tr>
                                        <th><?php echo $makeSort('id','ID'); ?></th>
                                        <th><?php echo $makeSort('reference','Référence'); ?></th>
                                        <th><?php echo $makeSort('numero','N°'); ?></th>
                                        <th><?php echo $makeSort('beneficiaire','Bénéficiaire'); ?></th>
                                        <th><?php echo $makeSort('description','Motif'); ?></th>
                                        <th class="text-end"><?php echo $makeSort('montant','Montant'); ?></th>
                                        <th><?php echo $makeSort('date','Date sortie'); ?></th>
                                        <th><?php echo $makeSort('annee','Année'); ?></th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rows)): ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class="alert alert-warning mb-0">Aucune dépense trouvée avec ces
                                                critères.</div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <td><?php echo e($row['id']); ?></td>
                                        <td><?php echo e($row['reference'] ?? ''); ?></td>
                                        <td><?php echo e($row['numero_reference'] ?? ''); ?></td>
                                        <td><?php echo e($row['beneficiaire'] ?? ''); ?></td>
                                        <td title="<?php echo e($row['description'] ?? ''); ?>">
                                            <?php echo e(str_limit($row['description'] ?? '', 80)); ?>
                                        </td>
                                        <td class="text-end"><?php echo fmt_money($row['montant'] ?? 0); ?> $</td>
                                        <td><?php echo fmt_date($row['dateCreaty'] ?? null); ?></td>
                                        <td><?php echo e($row['anneeScolaire'] ?? ''); ?></td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-primary"
                                                href="show.php?id=<?php echo e($row['id']); ?>">
                                                <i class="fa fa-eye me-1"></i> Voir
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination justify-content-end flex-wrap">
                                <?php
                    $prev_disabled = ($page <= 1) ? ' disabled' : '';
                    $next_disabled = ($page >= $total_pages) ? ' disabled' : '';
                  ?>
                                <li class="page-item<?php echo $prev_disabled; ?>">
                                    <a class="page-link" href="?<?php echo keep_qs(['page'=>max(1, $page-1)]); ?>">«</a>
                                </li>
                                <?php
                    // Fenêtre de pages compacte
                    $win = 2; // pages autour
                    $start = max(1, $page - $win);
                    $end   = min($total_pages, $page + $win);
                    if ($start > 1) {
                      echo '<li class="page-item"><a class="page-link" href="?'.keep_qs(['page'=>1]).'">1</a></li>';
                      if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                    }
                    for ($p=$start; $p<=$end; $p++){
                      $active = ($p === $page) ? ' active' : '';
                      echo '<li class="page-item'.$active.'"><a class="page-link" href="?'.keep_qs(['page'=>$p]).'">'.$p.'</a></li>';
                    }
                    if ($end < $total_pages) {
                      if ($end < $total_pages-1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                      echo '<li class="page-item"><a class="page-link" href="?'.keep_qs(['page'=>$total_pages]).'">'.$total_pages.'</a></li>';
                    }
                  ?>
                                <li class="page-item<?php echo $next_disabled; ?>">
                                    <a class="page-link"
                                        href="?<?php echo keep_qs(['page'=>min($total_pages, $page+1)]); ?>">»</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once ('../../layouts/constants/footer.php'); ?>
</div>

<script>
/* Recherche instantanée client (facultatif — agit sur la page courante seulement) */
function myFunctionSearch() {
    const input = document.getElementById('searchInput');
    if (!input) return;
    const filter = (input.value || '').toLowerCase();
    const table = document.getElementById('myTable');
    const trs = table.getElementsByTagName('tr');
    for (let i = 1; i < trs.length; i++) {
        const tds = trs[i].getElementsByTagName('td');
        if (!tds || tds.length === 0) continue;
        let rowText = '';
        for (let j = 0; j < tds.length; j++) rowText += (tds[j].innerText || tds[j].textContent) + ' ';
        trs[i].style.display = rowText.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
    }
}
</script>