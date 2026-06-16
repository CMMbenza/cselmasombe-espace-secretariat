<?php
require_once ('../../layouts/constants/head.php');
require_once ('../../layouts/navbar/navbar.php');
require_once ('../../webapp/database/config.php'); // doit fournir $con (mysqli)
mysqli_set_charset($con, 'utf8mb4');

/* ---------- Helpers ---------- */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('moneyf')) {
  function moneyf($n){ return number_format((float)$n, 2, ',', ' '); }
}

/* =========================================================
   PÉRIODE : année scolaire encours (dateDebut/dateFin) sinon 12 derniers mois
   ========================================================= */
$debut = null; $fin = null; $annee_label = null;

$resY = mysqli_query($con, "
  SELECT annee_scolaire, dateDebut, dateFin
  FROM annee_scolaire
  WHERE status='encours'
  ORDER BY id DESC
  LIMIT 1
");

if ($resY && mysqli_num_rows($resY) === 1) {
  $y = mysqli_fetch_assoc($resY);
  $tryDeb = DateTime::createFromFormat('Y-m-d', (string)$y['dateDebut']);
  $tryFin = DateTime::createFromFormat('Y-m-d', (string)$y['dateFin']);
  if ($tryDeb && $tryFin) {
    $debut = $tryDeb->format('Y-m-d');
    $fin   = $tryFin->format('Y-m-d');
    $annee_label = $y['annee_scolaire'];
  }
}

if (!$debut || !$fin) {
  $dStart = new DateTime('first day of -11 month');
  $dEnd   = new DateTime('last day of this month');
  $debut  = $dStart->format('Y-m-d');
  $fin    = $dEnd->format('Y-m-d');
  $annee_label = date('M Y', strtotime($debut)).' → '.date('M Y', strtotime($fin));
}

/* =========================================================
   KPIs FINANCIERS (sur la période)
   Encaissements = paiement + paiement_divers
   Décaissements = depenses
   ========================================================= */
$q = mysqli_query($con, "
  SELECT COALESCE(SUM(montantPayer),0) AS s
  FROM paiement
  WHERE dateCreated BETWEEN '$debut' AND '$fin'
");
$encaiss_paiement = (float)mysqli_fetch_assoc($q)['s'];

$q = mysqli_query($con, "
  SELECT COALESCE(SUM(montantPayer),0) AS s
  FROM paiement_divers
  WHERE dateCreated BETWEEN '$debut 00:00:00' AND '$fin 23:59:59'
");
$encaiss_divers = (float)mysqli_fetch_assoc($q)['s'];

$encaissements_total = $encaiss_paiement + $encaiss_divers;

$q = mysqli_query($con, "
  SELECT COALESCE(SUM(montant),0) AS s
  FROM depenses
  WHERE dateCreaty BETWEEN '$debut' AND '$fin'
");
$decaissements_total = (float)mysqli_fetch_assoc($q)['s'];

$solde_total = $encaissements_total - $decaissements_total;

/* =========================================================
   NOUVEAUX KPIs : Garçons / Filles / Élèves / Ménages (global) Actif
   ========================================================= */
$sqlEleves = "
  SELECT
    SUM(CASE WHEN genre='M' THEN 1 ELSE 0 END) AS garcons,
    SUM(CASE WHEN genre='F' THEN 1 ELSE 0 END) AS filles,
    COUNT(*) AS total_eleves
  FROM eleve WHERE status = 'actif'
";
$resE = mysqli_query($con, $sqlEleves);
$kpi = $resE ? mysqli_fetch_assoc($resE) : ['garcons'=>0,'filles'=>0,'total_eleves'=>0];

$nb_garcons    = (int)$kpi['garcons'];
$nb_filles     = (int)$kpi['filles'];
$total_eleves  = (int)$kpi['total_eleves'];

$resM = mysqli_query($con, "SELECT COUNT(*) AS total FROM menage WHERE status = 'actif'");
$rowM = $resM ? mysqli_fetch_assoc($resM) : ['total'=>0];
$nb_menages = (int)$rowM['total'];

/* =========================================================
   NOUVEAUX KPIs : Garçons / Filles / Élèves / Ménages (global) Inactif
   ========================================================= */
$sqlEleves = "
  SELECT
    SUM(CASE WHEN genre='M' THEN 1 ELSE 0 END) AS garcons,
    SUM(CASE WHEN genre='F' THEN 1 ELSE 0 END) AS filles,
    COUNT(*) AS total_eleves
  FROM eleve WHERE status = 'inactif'
";
$resEInactif = mysqli_query($con, $sqlEleves);
$kpiEInactif = $resEInactif ? mysqli_fetch_assoc($resEInactif) : ['garcons'=>0,'filles'=>0,'total_eleves'=>0];

$nb_garcons_inactif    = (int)$kpiEInactif['garcons'];
$nb_filles_inactif     = (int)$kpiEInactif['filles'];
$total_eleves_inactif  = (int)$kpiEInactif['total_eleves'];

$resMInactif = mysqli_query($con, "SELECT COUNT(*) AS total FROM menage WHERE status = 'inactif'");
$rowMInactif = $resMInactif ? mysqli_fetch_assoc($resMInactif) : ['total'=>0];
$nb_menages_inactif = (int)$rowMInactif['total'];

/* =========================================================
   NOUVEAUX KPIs : Garçons / Filles / Élèves / Ménages (global) TotalAnnuel
   ========================================================= */
$sqlEleves = "
  SELECT
    SUM(CASE WHEN genre='M' THEN 1 ELSE 0 END) AS garcons,
    SUM(CASE WHEN genre='F' THEN 1 ELSE 0 END) AS filles,
    COUNT(*) AS total_eleves
  FROM eleve
";
$resETotalAnnuel = mysqli_query($con, $sqlEleves);
$kpiETotalAnnuel = $resETotalAnnuel ? mysqli_fetch_assoc($resETotalAnnuel) : ['garcons'=>0,'filles'=>0,'total_eleves'=>0];

$nb_garcons_TotalAnnuel    = (int)$kpiETotalAnnuel['garcons'];
$nb_filles_TotalAnnuel     = (int)$kpiETotalAnnuel['filles'];
$total_eleves_TotalAnnuel  = (int)$kpiETotalAnnuel['total_eleves'];

$resMTotalAnnuel = mysqli_query($con, "SELECT COUNT(*) AS total FROM menage");
$rowMTotalAnnuel = $resMTotalAnnuel ? mysqli_fetch_assoc($resMTotalAnnuel) : ['total'=>0];
$nb_menages_TotalAnnuel = (int)$rowMTotalAnnuel['total'];

/* =========================================================
   SÉRIES MENSUELLES POUR LE GRAPHIQUE (entre $debut et $fin)
   ========================================================= */
$labels = []; $enc_series = []; $dec_series = [];

$cursor = new DateTime($debut);
$end    = new DateTime($fin);
$cursor->modify('first day of this month');
$end->modify('last day of this month');

while ($cursor <= $end) {
  $ym = $cursor->format('Y-m');
  $labels[] = $cursor->format('M Y');

  // Encaissements par mois
  $r = mysqli_query($con, "
    SELECT COALESCE(SUM(montantPayer),0) AS s
    FROM paiement
    WHERE DATE_FORMAT(dateCreated,'%Y-%m') = '$ym'
  ");
  $m1 = (float)mysqli_fetch_assoc($r)['s'];

  $r = mysqli_query($con, "
    SELECT COALESCE(SUM(montantPayer),0) AS s
    FROM paiement_divers
    WHERE DATE_FORMAT(dateCreated,'%Y-%m') = '$ym'
  ");
  $m2 = (float)mysqli_fetch_assoc($r)['s'];

  $enc_series[] = $m1 + $m2;

  // Décaissements par mois (depenses)
  $r = mysqli_query($con, "
    SELECT COALESCE(SUM(montant),0) AS s
    FROM depenses
    WHERE DATE_FORMAT(dateCreaty,'%Y-%m') = '$ym'
  ");
  $d1 = (float)mysqli_fetch_assoc($r)['s'];

  $dec_series[] = $d1;

  $cursor->modify('first day of next month');
}

/* Données JSON pour Chart.js */
$labels_js = json_encode($labels, JSON_UNESCAPED_UNICODE);
$enc_js    = json_encode($enc_series, JSON_UNESCAPED_UNICODE);
$dec_js    = json_encode($dec_series, JSON_UNESCAPED_UNICODE);

/* =========================================================
   LISTES : 5 derniers paiements (paiement), 5 derniers paiements (paiement_divers),
   5 dernières inscriptions (élèves + ménage)
   ========================================================= */
$last_paiements = [];
$q = mysqli_query($con, "
  SELECT p.id, p.menage, p.montantPayer, p.dateCreated, m.noms
  FROM paiement p
  LEFT JOIN menage m ON m.id = p.menage
  ORDER BY p.dateCreated DESC, p.id DESC
  LIMIT 5
");
while($q && $row = mysqli_fetch_assoc($q)) { $last_paiements[] = $row; }

$last_paiements_div = [];
$q = mysqli_query($con, "
  SELECT d.id, d.menage, d.type_frais, d.montantPayer, d.dateCreated, m.noms
  FROM paiement_divers d
  LEFT JOIN menage m ON m.id = d.menage
  ORDER BY d.dateCreated DESC, d.id DESC
  LIMIT 5
");
while($q && $row = mysqli_fetch_assoc($q)) { $last_paiements_div[] = $row; }

$last_eleves = [];
$q = mysqli_query($con, "
  SELECT e.id, e.nom, e.postnom, e.prenom, e.genre, e.dateCreated,
         e.menage AS menage_id, m.noms AS menage_noms
  FROM eleve e
  LEFT JOIN menage m ON m.id = e.menage
  ORDER BY e.dateCreated DESC, e.id DESC
  LIMIT 5
");
while($q && $row = mysqli_fetch_assoc($q)) { $last_eleves[] = $row; }
?>