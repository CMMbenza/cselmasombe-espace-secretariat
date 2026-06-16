<?php
// view/balance/index.php

declare(strict_types=1);

// ---- Includes de ton projet (adapte les chemins si besoin)
require_once('../../layouts/constants/head.php');
require_once('../../layouts/navbar/navbar.php');

// Si ton head ne garantit pas $con, sécurise :
if (!isset($con) || !($con instanceof mysqli)) {
  require_once('../../webapp/database/config.php');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
/** @var mysqli $con */
$con->set_charset('utf8mb4');

/* ================= Helpers ================= */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('nf')) {
  function nf($n){ return number_format((float)$n, 2, ',', ' '); }
}
if (!function_exists('keep_qs')) {
  function keep_qs(array $extra = [], array $drop = []): string {
    $qs = $_GET;
    foreach ($drop as $d) unset($qs[$d]);
    foreach ($extra as $k=>$v) $qs[$k] = $v;
    return http_build_query($qs);
  }
}

/* ================= Inputs (GET) ================= */
$q              = trim($_GET['q']              ?? '');       // recherche libre (type_virement, année)
$mois_param     = trim($_GET['mois']           ?? '');       // YYYY-MM
$annee_scolaire = trim($_GET['annee_scolaire'] ?? '');       // 2024-2025
$type_virement  = trim($_GET['type_virement']  ?? '');       // ex: 'cloture_mensuelle' ou vide=tous
$export         = trim($_GET['export']         ?? '');       // csv | xls | ''

// Tri
$sort = $_GET['sort'] ?? 'date';    // id|type|entre|sorti|reste|periode|date|annee
$dir  = strtolower((string)($_GET['dir'] ?? 'desc'));
$allowedSort = [
  'id'     => 'b.id',
  'type'   => 'b.type_virement',
  'entre'  => 'b.entre',
  'sorti'  => 'b.sorti',
  'reste'  => 'b.reste',
  'periode'=> 'b.periode_annee,b.periode_mois',
  'date'   => 'b.dateBalance',
  'annee'  => 'b.anneeScolaire',
];
$orderBy = $allowedSort[$sort] ?? 'b.dateBalance';
$dir     = in_array($dir, ['asc','desc'], true) ? $dir : 'desc';

/* ================= Construction WHERE + binds ================= */
$where  = [];
$types  = '';
$params = [];

// Recherche libre: on fait un LIKE sur type_virement et anneeScolaire
if ($q !== '') {
  $where[] = "(b.type_virement LIKE CONCAT('%', ?, '%') OR b.anneeScolaire LIKE CONCAT('%', ?, '%'))";
  $types  .= 'ss';
  $params[] = $q;
  $params[] = $q;
}

// annee scolaire stricte
if ($annee_scolaire !== '') {
  $where[] = "b.anneeScolaire = ?";
  $types  .= 's';
  $params[] = $annee_scolaire;
}

// mois YYYY-MM -> déduire periode_annee et periode_mois
if (preg_match('~^\d{4}-\d{2}$~', $mois_param)) {
  $Y = (int)substr($mois_param, 0, 4);
  $M = (int)substr($mois_param, 5, 2);
  $where[] = "b.periode_annee = ? AND b.periode_mois = ?";
  $types  .= 'ii';
  $params[] = $Y;
  $params[] = $M;
}

// type_virement
if ($type_virement !== '') {
  $where[] = "b.type_virement = ?";
  $types  .= 's';
  $params[] = $type_virement;
}

$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

/* ================= Récupération (liste filtrée) ================= */
$sql = "
  SELECT b.id, b.type_virement, b.entre, b.sorti, b.reste,
         b.periode_mois, b.periode_annee, b.dateBalance, b.anneeScolaire, b.created_at
  FROM balance b
  $whereSql
  ORDER BY $orderBy $dir
";

$st = $con->prepare($sql);
if ($types !== '') { $st->bind_param($types, ...$params); }
$st->execute();
$res = $st->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) { $rows[] = $r; }
$st->close();

/* ================= Totaux (après filtres) ================= */
$total_entre = 0.0; $total_sorti = 0.0; $total_reste = 0.0;
foreach ($rows as $r) {
  $total_entre += (float)$r['entre'];
  $total_sorti += (float)$r['sorti'];
  $total_reste += (float)$r['reste'];
}

/* ================= Export ================= */
if ($export === 'csv') {
  $filename = 'balance_'.date('Ymd_His').'.csv';
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  echo "\xEF\xBB\xBF"; // BOM UTF-8
  $out = fopen('php://output', 'w');
  fputcsv($out, ['ID','Type','Année scolaire','Période','Entrée','Sortie','Reste','Date balance','Créé le']);
  foreach ($rows as $r) {
    fputcsv($out, [
      $r['id'],
      $r['type_virement'],
      $r['anneeScolaire'],
      sprintf('%04d-%02d', (int)$r['periode_annee'], (int)$r['periode_mois']),
      number_format((float)$r['entre'], 2, '.', ''),
      number_format((float)$r['sorti'], 2, '.', ''),
      number_format((float)$r['reste'], 2, '.', ''),
      $r['dateBalance'],
      $r['created_at'],
    ]);
  }
  fclose($out);
  exit;
}
if ($export === 'xls') {
  $filename = 'balance_'.date('Ymd_His').'.xls';
  header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  echo "<table border='1'>";
  echo "<tr>
          <th>ID</th><th>Type</th><th>Ann&eacute;e scolaire</th><th>P&eacute;riode</th>
          <th>Entr&eacute;e</th><th>Sortie</th><th>Reste</th><th>Date balance</th><th>Cr&eacute;&eacute; le</th>
        </tr>";
  foreach ($rows as $r) {
    echo "<tr>";
    echo "<td>".e($r['id'])."</td>";
    echo "<td>".e($r['type_virement'])."</td>";
    echo "<td>".e($r['anneeScolaire'])."</td>";
    echo "<td>".e(sprintf('%04d-%02d', (int)$r['periode_annee'], (int)$r['periode_mois']))."</td>";
    echo "<td>".e(number_format((float)$r['entre'], 2, '.', ''))."</td>";
    echo "<td>".e(number_format((float)$r['sorti'], 2, '.', ''))."</td>";
    echo "<td>".e(number_format((float)$r['reste'], 2, '.', ''))."</td>";
    echo "<td>".e($r['dateBalance'])."</td>";
    echo "<td>".e($r['created_at'])."</td>";
    echo "</tr>";
  }
  echo "</table>";
  exit;
}

/* ================= UI helpers ================= */
$toggleDir = ($dir === 'asc') ? 'desc' : 'asc';
$mkSort = function(string $key, string $label) use ($toggleDir, $sort, $dir) {
  $icon = ($sort === $key) ? ($dir === 'asc' ? ' ▲' : ' ▼') : '';
  $qs   = keep_qs(['sort'=>$key, 'dir'=>($sort===$key ? $toggleDir : 'asc')]);
  return '<a href="?'.$qs.'" class="text-decoration-none">'.$label.$icon.'</a>';
};

// récupérer les types distincts pour le select (facultatif mais pratique)
$types_virements = [];
try {
  $qTypes = "SELECT DISTINCT type_virement FROM balance ORDER BY type_virement";
  $rt = $con->query($qTypes);
  while ($row = $rt->fetch_assoc()) { $types_virements[] = $row['type_virement']; }
} catch (Throwable $t) {
  // ignore
}

?>
<div class="main-panel-copy">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h3 class="card-title d-flex align-items-center gap-2">
              <span class="menu-icon"><i class="fa fa-balance-scale"></i></span>
              Balance &mdash; R&eacute;capitulatif mensuel
            </h3>

            <!-- Filtres -->
            <form class="mt-3" method="get" id="filterForm">
              <div class="row g-3 align-items-end">
                <div class="col-md-3">
                  <label class="form-label">Recherche</label>
                  <input type="text" name="q" value="<?php echo e($q); ?>" class="form-control"
                         placeholder="type ou ann&eacute;e scolaire">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Mois</label>
                  <input type="month" name="mois" value="<?php echo e($mois_param); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Ann&eacute;e scolaire</label>
                  <input type="text" name="annee_scolaire" value="<?php echo e($annee_scolaire); ?>" class="form-control" placeholder="2024-2025">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Type virement</label>
                  <select name="type_virement" class="form-control">
                    <option value="">— Tous —</option>
                    <?php foreach ($types_virements as $tv): ?>
                      <option value="<?php echo e($tv); ?>" <?php echo ($type_virement===$tv?'selected':''); ?>>
                        <?php echo e($tv); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-1">
                  <button type="submit" class="btn btn-primary w-100"><i class="fa fa-filter me-1"></i> OK</button>
                </div>
              </div>

              <div class="row g-3 mt-2">
                <div class="col-md-3">
                  <a href="?<?php echo keep_qs([], array_keys($_GET)); ?>" class="btn btn-outline-secondary w-100">
                    R&eacute;initialiser
                  </a>
                </div>
                <div class="col-md-3 ms-auto">
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

            <!-- Totaux -->
            <div class="mt-3 small text-muted">
              <span class="me-3">Total lignes : <strong><?php echo count($rows); ?></strong></span>
              <span class="me-3">Entr&eacute;e : <strong><?php echo nf($total_entre); ?> $</strong></span>
              <span class="me-3">Sortie : <strong><?php echo nf($total_sorti); ?> $</strong></span>
              <span>Reste (net) : <strong><?php echo nf($total_reste); ?> $</strong></span>
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
            <h4 class="card-title">Liste des enregistrements</h4>
            <div class="table-responsive">
              <table class="table align-middle" id="balanceTable">
                <thead>
                  <tr>
                    <th><?php echo $mkSort('id','ID'); ?></th>
                    <th><?php echo $mkSort('type','Type'); ?></th>
                    <th><?php echo $mkSort('annee','Ann&eacute;e scolaire'); ?></th>
                    <th><?php echo $mkSort('periode','P&eacute;riode'); ?></th>
                    <th class="text-end"><?php echo $mkSort('entre','Entr&eacute;e'); ?></th>
                    <th class="text-end"><?php echo $mkSort('sorti','Sortie'); ?></th>
                    <th class="text-end"><?php echo $mkSort('reste','Reste'); ?></th>
                    <th><?php echo $mkSort('date','Date balance'); ?></th>
                    <th>Cr&eacute;&eacute; le</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($rows)): ?>
                    <tr>
                      <td colspan="9">
                        <div class="alert alert-warning mb-0">Aucun enregistrement trouv&eacute; avec ces crit&egrave;res.</div>
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                      <tr>
                        <td><?php echo e($r['id']); ?></td>
                        <td><?php echo e($r['type_virement']); ?></td>
                        <td><?php echo e($r['anneeScolaire']); ?></td>
                        <td><?php echo e(sprintf('%04d-%02d', (int)$r['periode_annee'], (int)$r['periode_mois'])); ?></td>
                        <td class="text-end"><?php echo nf($r['entre']); ?> $</td>
                        <td class="text-end"><?php echo nf($r['sorti']); ?> $</td>
                        <td class="text-end"><?php echo nf($r['reste']); ?> $</td>
                        <td><?php echo e($r['dateBalance']); ?></td>
                        <td><?php echo e($r['created_at']); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
                <?php if (!empty($rows)): ?>
                <tfoot>
                  <tr>
                    <th colspan="4" class="text-end">Totaux (apr&egrave;s filtres)</th>
                    <th class="text-end"><?php echo nf($total_entre); ?> $</th>
                    <th class="text-end"><?php echo nf($total_sorti); ?> $</th>
                    <th class="text-end"><?php echo nf($total_reste); ?> $</th>
                    <th colspan="2"></th>
                  </tr>
                </tfoot>
                <?php endif; ?>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div><!-- /.content-wrapper -->

  <?php require_once('../../layouts/constants/footer.php'); ?>
</div>

<script>
/* Recherche instantanée côté client (optionnelle). 
   Si tu en veux une, ajoute un input et filtre ici comme dans tes autres pages. */
</script>
