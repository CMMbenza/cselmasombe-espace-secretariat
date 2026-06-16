<?php
// webapp/service/paiement.service.php
declare(strict_types=1);

require_once __DIR__ . '/../../webapp/database/config.php';
require_once __DIR__ . '/annee_scolaire.encours.php'; // doit définir $annee_scolaire
$date_du_jour = date('Y-m-d');

/** Fallback si $annee_scolaire est vide */
if (empty($annee_scolaire)) {
    $q = $con->query("SELECT annee_scolaire FROM annee_scolaire WHERE status='encours' ORDER BY id DESC LIMIT 1");
    if ($q && $q->num_rows > 0) {
        $annee_scolaire = $q->fetch_assoc()['annee_scolaire'];
    } else {
        $q2 = $con->query("SELECT annee_scolaire FROM annee_scolaire ORDER BY id DESC LIMIT 1");
        $annee_scolaire = ($q2 && $q2->num_rows > 0) ? $q2->fetch_assoc()['annee_scolaire'] : date('Y');
    }
}

/**
 * Construit WHERE + bind pour les filtres (GET):
 * - date=YYYY-MM-DD
 * - mois=1..12
 * - annee=YYYY (année civile sur dateCreated)
 * - du=YYYY-MM-DD & au=YYYY-MM-DD
 * - menage=substring (LIKE)
 * - annee_sco=AAAA-AAAA (force année scolaire)
 */
function build_filters(mysqli $con, string $annee_scolaire): array {
    $anneeSco = $_GET['annee_sco'] ?? $annee_scolaire;

    $where  = " paie.anneeScolaire = ? ";
    $types  = "s";
    $params = [$anneeSco];

    $labels = ["Année scolaire = $anneeSco"];

    if (!empty($_GET['date'])) {
        $where .= " AND DATE(paie.dateCreated) = ? ";
        $types .= "s";
        $params[] = $_GET['date'];
        $labels[] = "Date = " . $_GET['date'];
    }

    if (!empty($_GET['du']) && !empty($_GET['au'])) {
        $where .= " AND DATE(paie.dateCreated) BETWEEN ? AND ? ";
        $types .= "ss";
        $params[] = $_GET['du'];
        $params[] = $_GET['au'];
        $labels[] = "Période " . $_GET['du'] . " → " . $_GET['au'];
    }

    if (!empty($_GET['mois'])) {
        $m = (int)$_GET['mois'];
        if ($m >= 1 && $m <= 12) {
            $where .= " AND MONTH(paie.dateCreated) = ? ";
            $types .= "i";
            $params[] = $m;
            $labels[] = "Mois = " . $m;
        }
    }

    if (!empty($_GET['annee'])) {
        $a = (int)preg_replace('/[^0-9]/', '', $_GET['annee']);
        if ($a > 0) {
            $where .= " AND YEAR(paie.dateCreated) = ? ";
            $types .= "i";
            $params[] = $a;
            $labels[] = "Année = " . $a;
        }
    }

    if (!empty($_GET['menage'])) {
        $where .= " AND men.noms LIKE ? ";
        $types .= "s";
        $params[] = '%' . $_GET['menage'] . '%';
        $labels[] = 'Ménage ~ "' . $_GET['menage'] . '"';
    }

    return [
        'where'      => $where,
        'types'      => $types,
        'params'     => $params,
        'labels'     => $labels,
        'annee_sco'  => $anneeSco,
    ];
}

/** Export CSV minimal : mêmes filtres, colonnes = EXACTEMENT celles du tableau, sans entêtes */
function export_paiements_csv(mysqli $con, string $annee_scolaire): void {
    $flt = build_filters($con, $annee_scolaire);

    $sql = "SELECT paie.id AS id,
                   men.noms AS menage,
                   paie.montantAPayer,
                   paie.montantPayer,
                   paie.resteAPayer,
                   paie.observation,
                   paie.dateCreated
            FROM paiement paie
            JOIN menage men ON men.id = paie.menage
            WHERE {$flt['where']}
            ORDER BY paie.dateCreated DESC, paie.id DESC";
    $stmt = $con->prepare($sql);
    $stmt->bind_param($flt['types'], ...$flt['params']);
    $stmt->execute();
    $res = $stmt->get_result();

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="paiements_' . date('Ymd_His') . '.csv' );

    // Pas d’en-têtes CSV, justes les lignes du tableau, séparateur ;
    $out = fopen('php://output', 'w');
    while ($row = $res->fetch_assoc()) {
        fputcsv($out, [
            (int)$row['id'],
            $row['menage'],
            number_format((float)$row['montantAPayer'], 2, '.', ''), // valeurs brutes (2 décimales)
            number_format((float)$row['montantPayer'], 2, '.', ''),
            number_format((float)$row['resteAPayer'], 2, '.', ''),
            $row['observation'],
            $row['dateCreated'],
        ], ';'); // séparateur point-virgule
    }
    fclose($out);
    exit;
}

/* ------------------ Données pour la page ------------------ */

/* TOP 5 du jour (si utilisé) */
$sqlTop5 = "SELECT paie.id AS id,
                   men.noms AS menage,
                   paie.montantAPayer,
                   paie.montantPayer,
                   paie.observation,
                   paie.dateCreated,
                   paie.resteAPayer
            FROM paiement paie
            JOIN menage men ON paie.menage = men.id
            WHERE DATE(paie.dateCreated) = ?
              AND paie.anneeScolaire   = ?
            ORDER BY paie.id DESC
            LIMIT 5";
$stTop = $con->prepare($sqlTop5);
$stTop->bind_param('ss', $date_du_jour, $annee_scolaire);
$stTop->execute();
$rstCinqPaiement = $stTop->get_result();

/* LISTE avec FILTRES (par défaut : toute l’année scolaire active) */
$flt = build_filters($con, $annee_scolaire);

$sqlList = "SELECT paie.id AS id,
                   men.id AS idMenage,
                   men.noms AS menage,
                   paie.montantAPayer,
                   paie.montantPayer,
                   paie.resteAPayer,
                   paie.observation,
                   paie.dateCreated
            FROM paiement paie
            JOIN menage men ON paie.menage = men.id
            WHERE {$flt['where']}
            ORDER BY paie.dateCreated DESC, paie.id DESC";
$stList = $con->prepare($sqlList);
$stList->bind_param($flt['types'], ...$flt['params']);
$stList->execute();
$rstlistPaiement = $stList->get_result();

/* TOTAL période filtrée */
$sqlSum = "SELECT COALESCE(SUM(paie.montantPayer),0) AS montantPayer
           FROM paiement paie
           JOIN menage men ON paie.menage = men.id
           WHERE {$flt['where']}";
$stSum = $con->prepare($sqlSum);
$stSum->bind_param($flt['types'], ...$flt['params']);
$stSum->execute();
$sumResult = $stSum->get_result()->fetch_assoc();
$afficheTotalDePaiements = ['montantPayer' => $sumResult['montantPayer']];

/* TOTAL du jour si "date" exact */
$montantJour = null;
if (!empty($_GET['date'])) {
    $sqlDay = "SELECT COALESCE(SUM(paie.montantPayer),0) AS totalJour
               FROM paiement paie
               WHERE paie.anneeScolaire = ?
                 AND DATE(paie.dateCreated) = ?";
    $stDay = $con->prepare($sqlDay);
    $stDay->bind_param('ss', $flt['annee_sco'], $_GET['date']);
    $stDay->execute();
    $montantJour = $stDay->get_result()->fetch_assoc()['totalJour'] ?? 0;
}

/* Meta pour l’UI */
$currentFilters = [
    'labels'     => $flt['labels'],
    'date'       => $_GET['date']  ?? '',
    'mois'       => $_GET['mois']  ?? '',
    'annee'      => $_GET['annee'] ?? '',
    'du'         => $_GET['du']    ?? '',
    'au'         => $_GET['au']    ?? '',
    'menage'     => $_GET['menage']?? '',
    'annee_sco'  => $flt['annee_sco'],
    'total_jour' => $montantJour,
];
