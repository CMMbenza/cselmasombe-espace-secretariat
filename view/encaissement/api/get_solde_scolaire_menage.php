<?php
require_once('../../../webapp/database/dbcongig.php');
header('Content-Type: application/json');

if (!isset($_GET['menage_id'])) {
    echo json_encode(['error' => 'ID ménage requis']);
    exit;
}

$menageId = intval($_GET['menage_id']);

// 1. Récupérer les montants de référence du ménage
$sqlMenage = "SELECT id, montantAPayer, montantAPayerFC FROM menage WHERE id = ? AND status = 'actif'";
$stmtM = $conn->prepare($sqlMenage);
$stmtM->bind_param("i", $menageId);
$stmtM->execute();
$menage = $stmtM->get_result()->fetch_assoc();
$stmtM->close();

if (!$menage) {
    echo json_encode(['error' => 'Ménage introuvable']);
    exit;
}

// -------------------------------------------------------------
// A. SCOLARITÉ : Vérifier le dernier paiement enregistré
// -------------------------------------------------------------
$sqlLastScolaire = "SELECT resteAPayer FROM paiement WHERE menage = ? ORDER BY id DESC LIMIT 1";
$stmtS = $conn->prepare($sqlLastScolaire);
$stmtS->bind_param("i", $menageId);
$stmtS->execute();
$resScolaire = $stmtS->get_result();

if ($rowS = $resScolaire->fetch_assoc()) {
    // Une ligne existe déjà : on prend le dernier resteAPayer
    $soldeScolaire = (float)$rowS['resteAPayer'];
    $isFirstScolaire = false;
} else {
    // Aucun paiement préalable : on prend le montantAPayer initial du ménage
    $soldeScolaire = (float)$menage['montantAPayer'];
    $isFirstScolaire = true;
}
$stmtS->close();

// -------------------------------------------------------------
// B. FRAIS DIVERS : Vérifier le dernier paiement_divers enregistré
// -------------------------------------------------------------
$sqlLastDivers = "SELECT resteAPayer FROM paiement_divers WHERE menage = ? ORDER BY id DESC LIMIT 1";
$stmtD = $conn->prepare($sqlLastDivers);
$stmtD->bind_param("i", $menageId);
$stmtD->execute();
$resDivers = $stmtD->get_result();

if ($rowD = $resDivers->fetch_assoc()) {
    // Une ligne existe déjà : on prend le dernier resteAPayer
    $soldeDivers = (float)$rowD['resteAPayer'];
    $isFirstDivers = false;
} else {
    // Aucun paiement préalable : on prend le montantAPayerFC initial du ménage
    $soldeDivers = (float)$menage['montantAPayerFC'];
    $isFirstDivers = true;
}
$stmtD->close();

// -------------------------------------------------------------
// C. Réponse JSON claire
// -------------------------------------------------------------
echo json_encode([
    'scolarite' => [
        'solde_du' => $soldeScolaire,
        'est_premier_paiement' => $isFirstScolaire
    ],
    'divers' => [
        'solde_du' => $soldeDivers,
        'est_premier_paiement' => $isFirstDivers
    ]
]);