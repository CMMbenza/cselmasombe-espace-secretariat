<?php
declare(strict_types=1);

/**
 * Service de listing des dépenses avec filtres/tri/pagination sécurisés.
 * Expose à index.php :
 *   - $rows : array des lignes
 *   - $total_count : nb total (après filtres, avant pagination)
 *   - $total_montant_filtered : somme montant de la vue filtrée (sans pagination)
 *   - $total_mois_encours : somme du mois en cours (indépendant des filtres)
 *   - $page, $per_page, $sort, $dir, $q, $ref, $ben, $annee, $mois
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once('../../webapp/database/config.php');  // doit définir $con (mysqli)
require_once('annee_scolaire.encours.php');        // peut définir $annee_scolaire (ex: "2024-2025")

if (!isset($con) || !($con instanceof mysqli)) {
  http_response_code(500);
  exit('Connexion MySQL indisponible. Vérifie /webapp/database/config.php.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$con->set_charset('utf8mb4');

/* ===== Helpers ===== */
function first_day_of_month(string $ym): string { // "YYYY-MM" -> "YYYY-MM-01"
  return $ym.'-01';
}
function last_day_of_month(string $ym): string {  // "YYYY-MM" -> "YYYY-MM-<t>"
  $ts = strtotime($ym.'-01 00:00:00');
  return date('Y-m-t', $ts ?: time());
}

/* ===== Inputs GET ===== */
$q      = trim($_GET['q']      ?? '');
$ref    = trim($_GET['ref']    ?? '');
$ben    = trim($_GET['ben']    ?? '');
$annee  = trim($_GET['annee']  ?? '');
$mois   = trim($_GET['mois']   ?? '');    // "YYYY-MM"
$export = trim($_GET['export'] ?? '');    // '', 'csv', 'xls'

$page     = max(1, (int)($_GET['page']     ?? 1));
$per_page = max(5, min(200, (int)($_GET['per_page'] ?? 25)));

$sort = $_GET['sort'] ?? 'id';
$dir  = strtolower((string)($_GET['dir'] ?? 'desc'));
$allowedSort = [
  'id'             => 'id',
  'reference'      => 'reference',
  'numero'         => 'numero_reference',
  'beneficiaire'   => 'beneficiaire',
  'description'    => 'description',
  'montant'        => 'montant',
  'date'           => 'dateCreaty',
  'annee'          => 'anneeScolaire',
];
$sortKey = $allowedSort[$sort] ?? 'id';
$dir     = in_array($dir, ['asc','desc'], true) ? $dir : 'desc';

/* ===== WHERE dynamique (préparé) ===== */
$where   = [];
$params  = [];
$types   = '';

if ($annee !== '') {
  $where[] = 'anneeScolaire = ?';
  $types  .= 's';
  $params[] = $annee;
} elseif (!empty($annee_scolaire)) {
  // Comportement précédent : par défaut, filtrer sur l’année scolaire en cours si fournie par le système
  $where[] = 'anneeScolaire = ?';
  $types  .= 's';
  $params[] = (string)$annee_scolaire;
}

if ($q !== '') {
  $like = '%'.$q.'%';
  $where[] = '(reference LIKE ? OR beneficiaire LIKE ? OR description LIKE ?)';
  $types  .= 'sss';
  array_push($params, $like, $like, $like);
}
if ($ref !== '') {
  $where[] = 'reference LIKE ?';
  $types  .= 's';
  $params[] = '%'.$ref.'%';
}
if ($ben !== '') {
  $where[] = 'beneficiaire LIKE ?';
  $types  .= 's';
  $params[] = '%'.$ben.'%';
}
if ($mois !== '' && preg_match('~^\d{4}-\d{2}$~', $mois)) {
  $d1 = first_day_of_month($mois);
  $d2 = last_day_of_month($mois);
  $where[] = 'dateCreaty BETWEEN ? AND ?';
  $types  .= 'ss';
  array_push($params, $d1, $d2);
}

$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

/* ===== Compte total filtré (pour pagination) ===== */
$sqlCount = "SELECT COUNT(*) AS c FROM depenses $whereSql";
$st = $con->prepare($sqlCount);
if ($types !== '') $st->bind_param($types, ...$params);
$st->execute();
$res = $st->get_result();
$total_count = (int)($res->fetch_assoc()['c'] ?? 0);
$st->close();

/* ===== Total montant filtré (sans pagination) ===== */
$sqlSum = "SELECT COALESCE(SUM(montant),0) AS s FROM depenses $whereSql";
$st = $con->prepare($sqlSum);
if ($types !== '') $st->bind_param($types, ...$params);
$st->execute();
$res = $st->get_result();
$total_montant_filtered = (float)($res->fetch_assoc()['s'] ?? 0.0);
$st->close();

/* ===== Total mois en cours (indépendant des filtres) ===== */
$ym_now = date('Y-m');
$d1_now = first_day_of_month($ym_now);
$d2_now = last_day_of_month($ym_now);
$sqlMonth = "SELECT COALESCE(SUM(montant),0) AS s FROM depenses WHERE dateCreaty BETWEEN ? AND ?";
$st = $con->prepare($sqlMonth);
$st->bind_param('ss', $d1_now, $d2_now);
$st->execute();
$res = $st->get_result();
$total_mois_encours = (float)($res->fetch_assoc()['s'] ?? 0.0);
$st->close();

/* ===== Sélection paginée ===== */
$offset = ($page - 1) * $per_page;
$rows   = [];

// Pas de LIMIT/OFFSET si export (on exporte *toutes* les lignes filtrées)
$limitSql = ($export === '') ? " LIMIT $per_page OFFSET $offset" : "";

$sqlList = "
  SELECT id, reference, numero_reference, beneficiaire, description, montant,
         dateCreaty, dateUpdate, createdby, anneeScolaire
  FROM depenses
  $whereSql
  ORDER BY $sortKey $dir
  $limitSql";

$st = $con->prepare($sqlList);
if ($types !== '') $st->bind_param($types, ...$params);
$st->execute();
$res = $st->get_result();
while ($r = $res->fetch_assoc()) $rows[] = $r;
$st->close();
