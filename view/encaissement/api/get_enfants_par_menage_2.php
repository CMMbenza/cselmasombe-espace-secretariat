<?php
require_once('../../../webapp/database/dbcongig.php');
header('Content-Type: application/json');

if (!isset($_GET['menage_id']) || !isset($_GET['scolarite_desc'])) {
    echo json_encode(['error' => 'Paramètres requis manquants']);
    exit;
}

$menageId = intval($_GET['menage_id']);
$scolariteDesc = trim($_GET['scolarite_desc']);

$children = [];

// Récupérer les élèves rattachés au ménage avec leur classe et cycle
$sql = "
    SELECT 
        e.id AS eleve_id,
        CONCAT(e.nom, ' ', e.postnom, ' ', e.prenom) AS nom_complet,
        cl.description AS classe,
        c.id AS cycle_id
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

    // Récupérer le tarif de ce frais selon le cycle de l'élève
    $stmt2 = $conn->prepare("SELECT montant FROM scolarite WHERE description = ? AND cycle = ?");
    $stmt2->bind_param("si", $scolariteDesc, $cycleId);
    $stmt2->execute();
    $resFrais = $stmt2->get_result();

    $montant = 0;
    if ($data = $resFrais->fetch_assoc()) {
        $montant = (float)$data['montant'];
    }
    $stmt2->close();

    $children[] = [
        'id' => $row['eleve_id'],
        'nom_complet' => $row['nom_complet'],
        'classe' => $row['classe'],
        'montant' => $montant
    ];
}
$stmt->close();

echo json_encode($children);