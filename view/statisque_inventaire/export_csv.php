<?php

declare(strict_types=1);

require_once('../../webapp/database/config.php');
require_once('inventaire_functions.php');

if (ob_get_length()) {
    ob_end_clean();
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=inventaire_complet.csv');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

/* =====================================================
   SYNTHÈSE GLOBAL (NOUVEAU)
===================================================== */
function calcTotaux(mysqli $con, string $whereClause): array
{
    $res = getInventaire($con, $whereClause);

    $tot = [
        'familles' => 0,
        'scolaire_apayer' => 0,
        'scolaire_paye' => 0,
        'scolaire_reste' => 0,
        'connexe_apayer' => 0,
        'connexe_paye' => 0,
        'connexe_reste' => 0
    ];

    while ($row = mysqli_fetch_assoc($res)) {

        $grid = build_grid($con, [
            'id' => $row['id'],
            'anneeScolaire' => $row['anneeScolaire'],
            'start_tranche' => $row['start_tranche'],
            'montantDejaPayerScol' => $row['scolaire_paye'],
            'totalDiversPayer' => $row['connexe_paye']
        ]);

        $scolaireAPayer = $grid['totalScolaireOnly'];
        $scolaireReste = max($scolaireAPayer - $row['scolaire_paye'], 0);
        $connexeReste = max($row['connexe_a_payer'] - $row['connexe_paye'], 0);

        $tot['familles']++;

        $tot['scolaire_apayer'] += $scolaireAPayer;
        $tot['scolaire_paye'] += $row['scolaire_paye'];
        $tot['scolaire_reste'] += $scolaireReste;

        $tot['connexe_apayer'] += $row['connexe_a_payer'];
        $tot['connexe_paye'] += $row['connexe_paye'];
        $tot['connexe_reste'] += $connexeReste;
    }

    return $tot;
}

/* =====================================================
   EXPORT SECTION
===================================================== */
function exportSection($out, $con, $titre, $whereClause)
{
    fputcsv($out, []);
    fputcsv($out, [$titre]);

    fputcsv($out, [
        'Menage','Enfants','Classes',
        'A payer scolaire','Paye scolaire','Reste scolaire',
        'A payer connexe','Paye connexe','Reste connexe'
    ]);

    $result = getInventaire($con, $whereClause);

    while ($row = mysqli_fetch_assoc($result)) {

        $grid = build_grid($con, [
            'id' => $row['id'],
            'anneeScolaire' => $row['anneeScolaire'],
            'start_tranche' => $row['start_tranche'],
            'montantDejaPayerScol' => $row['scolaire_paye'],
            'totalDiversPayer' => $row['connexe_paye']
        ]);

        $scolaireAPayer = $grid['totalScolaireOnly'];
        $scolaireReste = max($scolaireAPayer - $row['scolaire_paye'], 0);
        $connexeReste = max($row['connexe_a_payer'] - $row['connexe_paye'], 0);

        fputcsv($out, [
            $row['noms'] ?? '',
            strip_tags($row['enfants'] ?? ''),
            strip_tags($row['classes'] ?? ''),
            $scolaireAPayer,
            $row['scolaire_paye'] ?? 0,
            $scolaireReste,
            $row['connexe_a_payer'] ?? 0,
            $row['connexe_paye'] ?? 0,
            $connexeReste
        ]);
    }
}

/* =====================================================
   1. SYNTHÈSE GLOBALE (AJOUT ICI)
===================================================== */

$actifs = calcTotaux($con,
    "WHERE m.STATUS='actif' AND UPPER(m.noms) NOT LIKE '%PERSONNEL%'"
);

$inactifs = calcTotaux($con,
    "WHERE m.STATUS='inactif' AND UPPER(m.noms) NOT LIKE '%PERSONNEL%'"
);

$personnel = calcTotaux($con,
    "WHERE UPPER(m.noms) LIKE '%PERSONNEL%'"
);

/* TABLE SYNTHÈSE */
fputcsv($out, []);
fputcsv($out, ['SYNTHÈSE GLOBALE']);

fputcsv($out, [
    'Categorie','Familles',
    'Scolaire A payer','Scolaire Paye','Scolaire Reste',
    'Connexe A payer','Connexe Paye','Connexe Reste'
]);

fputcsv($out, ['ACTIFS',
    $actifs['familles'],
    $actifs['scolaire_apayer'],
    $actifs['scolaire_paye'],
    $actifs['scolaire_reste'],
    $actifs['connexe_apayer'],
    $actifs['connexe_paye'],
    $actifs['connexe_reste']
]);

fputcsv($out, ['INACTIFS',
    $inactifs['familles'],
    $inactifs['scolaire_apayer'],
    $inactifs['scolaire_paye'],
    $inactifs['scolaire_reste'],
    $inactifs['connexe_apayer'],
    $inactifs['connexe_paye'],
    $inactifs['connexe_reste']
]);

fputcsv($out, ['PERSONNEL',
    $personnel['familles'],
    $personnel['scolaire_apayer'],
    $personnel['scolaire_paye'],
    $personnel['scolaire_reste'],
    $personnel['connexe_apayer'],
    $personnel['connexe_paye'],
    $personnel['connexe_reste']
]);

/* =====================================================
   2. DÉTAILS
===================================================== */

exportSection($out, $con,
    'FAMILLES ACTIVES',
    "WHERE m.STATUS='actif' AND UPPER(m.noms) NOT LIKE '%PERSONNEL%'"
);

exportSection($out, $con,
    'FAMILLES INACTIVES',
    "WHERE m.STATUS='inactif' AND UPPER(m.noms) NOT LIKE '%PERSONNEL%'"
);

exportSection($out, $con,
    'PERSONNEL',
    "WHERE UPPER(m.noms) LIKE '%PERSONNEL%'"
);

fclose($out);
exit;