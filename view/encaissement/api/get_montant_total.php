<?php
require_once('../../../webapp/database/dbcongig.php');
header('Content-Type: application/json');

if (!isset($_GET['menage_id']) || !isset($_GET['scolarite_id'])) {
    echo json_encode(['montant' => 0, 'error' => 'Paramètres manquants']);
    exit;
}

$menageId = intval($_GET['menage_id']);
$scolariteId = intval($_GET['scolarite_id']);

$total = 0;

// Étape 1 : récupérer tous les cycles des enfants du ménage
$sql = "
    SELECT DISTINCT c.id AS cycle_id
    FROM eleve e
    JOIN classe cl ON e.classe = cl.id
    JOIN cycle c ON cl.cycle = c.id
    WHERE e.menage = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $menageId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $cycleId = $row['cycle_id'];

    // Étape 2 : récupérer le montant correspondant à ce cycle et frais
    $sqlFrais = "SELECT montant FROM scolarite WHERE id = ? AND cycle = ?";
    $stmt2 = $conn->prepare($sqlFrais);
    $stmt2->bind_param("ii", $scolariteId, $cycleId);
    $stmt2->execute();
    $resFrais = $stmt2->get_result();

    if ($data = $resFrais->fetch_assoc()) {
        $total += $data['montant'];
    }
}

echo json_encode(['montant' => $total]);
