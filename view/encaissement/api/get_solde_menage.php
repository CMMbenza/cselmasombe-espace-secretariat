<?php
require_once('../../../webapp/database/dbcongig.php');
header('Content-Type: application/json');

if (!isset($_GET['menage_id'])) {
    echo json_encode(['error' => 'ID ménage requis']);
    exit;
}

$menageId = intval($_GET['menage_id']);

// 1. Récupération des montants initiaux configurés sur le ménage
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
// A. SCOLARITÉ : Vérification du dernier resteAPayer enregistre
// -------------------------------------------------------------
$sqlLastScolaire = "SELECT resteAPayer FROM paiement WHERE menage = ? ORDER BY id DESC LIMIT 1";
$stmtS = $conn->prepare($sqlLastScolaire);
$stmtS->bind_param("i", $menageId);
$stmtS->execute();
$resScolaire = $stmtS->get_result();

if ($rowS = $resScolaire->fetch_assoc()) {
    // Si au moins un paiement existe déjà, on prend le solde restant de la dernière transaction
    $soldeScolaire = (float)$rowS['resteAPayer'];
} else {
    // Premier paiement : on charge le montant total initial configuré pour ce ménage
    $soldeScolaire = (float)$menage['montantAPayer'];
}
$stmtS->close();

// -------------------------------------------------------------
// B. FRAIS DIVERS : Vérification du dernier resteAPayer enregistré
// -------------------------------------------------------------
$sqlLastDivers = "SELECT resteAPayer FROM paiement_divers WHERE menage = ? ORDER BY id DESC LIMIT 1";
$stmtD = $conn->prepare($sqlLastDivers);
$stmtD->bind_param("i", $menageId);
$stmtD->execute();
$resDivers = $stmtD->get_result();

if ($rowD = $resDivers->fetch_assoc()) {
    // Si au moins un paiement divers existe déjà, on prend le dernier resteAPayer
    $soldeDivers = (float)$rowD['resteAPayer'];
} else {
    // Premier paiement divers : on charge le montantAPayerFC initial du ménage
    $soldeDivers = (float)$menage['montantAPayerFC'];
}
$stmtD->close();

// -------------------------------------------------------------
// Rendu JSON
// -------------------------------------------------------------
echo json_encode([
    'scolarite' => [
        'solde_du' => $soldeScolaire
    ],
    'divers' => [
        'solde_du' => $soldeDivers
    ]
]);