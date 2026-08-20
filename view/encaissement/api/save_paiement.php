<?php
// Force l'affichage de l'erreur exacte
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Inclusion dynamique de la connexion DB
require_once('../../../webapp/database/dbcongig.php');

if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur de connexion à la base de données.']);
    exit;
}

$action = $_POST['action'] ?? '';

    $sql_annee_encours = "SELECT * FROM annee_scolaire WHERE status = 'encours'";
    $anneeEnCours = mysqli_query($conn, $sql_annee_encours);
    $annee = mysqli_fetch_assoc($anneeEnCours);
    $annee_scolaire = $annee['annee_scolaire'];

// =========================================================================
// A. ENREGISTREMENT PAIEMENT SCOLAIRE
// =========================================================================
if ($action === 'save_scolaire') {
    $menageId     = intval($_POST['menage_id'] ?? 0);
    $montantPayer = floatval($_POST['montant_payer'] ?? 0);
    $soldeDu      = floatval($_POST['solde_du'] ?? 0);
    $userObs      = trim($_POST['observation'] ?? '');

    if ($menageId <= 0 || $montantPayer <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Montant ou ménage invalide.']);
        exit;
    }

    $nouveauReste = max(0, $soldeDu - $montantPayer);
    
    // Date et Heure précises (ex: 2026-07-23 14:30:00)
    $dateCreated  = date('Y-m-d H:i:s'); 
    $anneeScolaire = $annee_scolaire; // Ajustez selon votre contexte

    // 1. Détermination automatique du statut d'observation
    if ($nouveauReste <= 0) {
        $observation = "Paiement soldé";
    } else {
        $observation = "Paiement encours";
    }
    
    // Si l'utilisateur a saisi une note supplémentaire, on la concatène
    if (!empty($userObs)) {
        $observation .= " - " . $userObs;
    }

    // 2. Insertion dans la table `paiement`
    $sql = "INSERT INTO paiement (menage, montantAPayer, montantPayer, resteAPayer, observation, dateCreated, anneeScolaire) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur SQL : ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("idddsss", $menageId, $soldeDu, $montantPayer, $nouveauReste, $observation, $dateCreated, $anneeScolaire);

    if ($stmt->execute()) {
        $lastId = $stmt->insert_id;

        // 3. Calcul de la Recette du Jour Scolaire mis à jour
        $today = date('Y-m-d');
        $sqlTotalDay = "SELECT SUM(montantPayer) AS total_jour FROM paiement WHERE DATE(dateCreated) = ?";
        $stmtT = $conn->prepare($sqlTotalDay);
        $stmtT->bind_param("s", $today);
        $stmtT->execute();
        $resT = $stmtT->get_result()->fetch_assoc();
        $recetteJour = (float)($resT['total_jour'] ?? 0);
        $stmtT->close();

        echo json_encode([
            'status' => 'success',
            'message' => 'Paiement scolaire enregistré avec succès !',
            'paiement_id' => $lastId,
            'recette_jour' => $recetteJour
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Échec de l\'enregistrement : ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}

// =========================================================================
// B. ENREGISTREMENT PAIEMENT DIVERS
// =========================================================================
if ($action === 'save_divers') {
    $menageId     = intval($_POST['menage_id'] ?? 0);
    $typeFrais    = trim($_POST['scolarite_id'] ?? '');
    $montantPayer = floatval($_POST['montant_payer'] ?? 0);
    $soldeDu      = floatval($_POST['solde_du'] ?? 0);
    $userObs      = trim($_POST['observation'] ?? '');

    if ($menageId <= 0 || $montantPayer <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Montant ou ménage invalide.']);
        exit;
    }

    $nouveauReste = max(0, $soldeDu - $montantPayer);
    $dateCreated  = date('Y-m-d H:i:s'); 
    $anneeScolaire = $annee_scolaire;

    // Détermination automatique du statut d'observation pour les frais divers
    if ($nouveauReste <= 0) {
        $observation = "Paiement soldé";
    } else {
        $observation = "Paiement encours";
    }
    if (!empty($userObs)) {
        $observations = $observation . " - " . $userObs;
    }

    $sql = "INSERT INTO paiement_divers (menage, type_frais, montantAPayer, montantPayer, resteAPayer, observation, anneeScolaire, dateCreated) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur SQL : ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("isdddsss", $menageId, $typeFrais, $soldeDu, $montantPayer, $nouveauReste, $observations, $anneeScolaire, $dateCreated);

    if ($stmt->execute()) {
        $lastId = $stmt->insert_id;
        echo json_encode([
            'status' => 'success',
            'message' => 'Paiement divers enregistré avec succès !',
            'paiement_id' => $lastId
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Échec de l\'enregistrement : ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Action non reconnue.']);