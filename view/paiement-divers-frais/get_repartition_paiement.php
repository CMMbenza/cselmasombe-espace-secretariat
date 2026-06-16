<?php
require_once('../../webapp/database/dbcongig.php');

$menage_id = intval($_GET['menage_id'] ?? 0);
$montant_total = floatval($_GET['montant'] ?? 0);

if (!$menage_id || $montant_total <= 0) {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

// 1. Récupérer les élèves et leur progression
$url = "get_tranche_progression.php?menage_id=$menage_id";
$data = json_decode(file_get_contents($url), true);

$resultat = [];

foreach ($data as $eleve) {
    if ($montant_total <= 0) break;

    $a_payer = min($montant_total, $eleve['reste']);
    $resultat[] = [
        'nom' => $eleve['nom'],
        'tranche' => 'Tranche ' . $eleve['numero_tranche'],
        'montant' => $a_payer
    ];

    $montant_total -= $a_payer;
}

header('Content-Type: application/json');
echo json_encode($resultat);
