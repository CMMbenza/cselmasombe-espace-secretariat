<?php
require_once('../../webapp/database/config.php');
require_once('../../webapp/service/annee_scolaire.encours.php');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

$menage_id = intval($data['menage_id']);
$montant_global = floatval($data['montant_global']);
$montant_paye = floatval($data['montant_paye']);
$eleves = $data['eleves'] ?? [];

if ($montant_paye <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Montant à payer doit être supérieur à 0']);
    exit;
}

$dateNow = date('Y-m-d H:i:s');

// Démarrer la transaction
$con->begin_transaction();

try {
    // Insertion dans la table paiement
    $stmt = $con->prepare("INSERT INTO paiement (menage, montantAPayer, montantPayer, resteAPayer, observation, dateCreated, anneeScolaire) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $observation = 'Paiement effectué.';
    $reste = max(0, $montant_global - $montant_paye);
    $stmt->bind_param("idddsss", $menage_id, $montant_global, $montant_paye, $reste, $observation, $dateNow, $annee_scolaire);

    if (!$stmt->execute()) {
        throw new Exception('Erreur lors de l\'insertion du paiement principal.');
    }

    $paiement_id = $stmt->insert_id;
    $stmt->close();

    // Préparer les requêtes pour détails
    $stmtDetail = $con->prepare("INSERT INTO paiement_detail (paiement_id, menage_id, eleve_id, tranche_id, montant, date_created) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmtDetail) {
        throw new Exception('Erreur préparation insertion paiement détail');
    }

    $stmtCaisse = $con->prepare("INSERT INTO caisse_frais_scolaire (cycle_id, montant_paye, date_paiement, annee_scolaire) VALUES (?, ?, ?, ?)");
    if (!$stmtCaisse) {
        throw new Exception('Erreur préparation insertion caisse');
    }

    foreach ($eleves as $eleve) {
        $eleve_id = intval($eleve['eleve_id']);
        $montant_paye_eleve = 0;

        // 🔍 Récupérer le cycle_id via la classe de l'élève
        $cycleQuery = $con->prepare("
            SELECT cl.cycle 
            FROM eleve e
            JOIN classe cl ON e.classe = cl.id
            WHERE e.id = ?
        ");
        $cycleQuery->bind_param("i", $eleve_id);
        $cycleQuery->execute();
        $result = $cycleQuery->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Cycle introuvable pour élève $eleve_id");
        }
        $cycleRow = $result->fetch_assoc();
        $cycle_id = intval($cycleRow['cycle']);
        $cycleQuery->close();

        // Détails des tranches payées
        $tranches = $eleve['tranches'] ?? [];
        foreach ($tranches as $tranche) {
            $tranche_id = intval($tranche['tranche_id']);
            $montant_paye_tranche = floatval($tranche['montant_paye']);
            if ($montant_paye_tranche <= 0) continue;

            // Insertion dans paiement_detail
            $stmtDetail->bind_param("iiiids", $paiement_id, $menage_id, $eleve_id, $tranche_id, $montant_paye_tranche, $dateNow);
            if (!$stmtDetail->execute()) {
                throw new Exception("Erreur insertion paiement détail pour élève $eleve_id");
            }

            $montant_paye_eleve += $montant_paye_tranche;
        }

        // Insertion dans caisse si le montant est > 0
        if ($montant_paye_eleve > 0) {
            $stmtCaisse->bind_param("idss", $cycle_id, $montant_paye_eleve, $dateNow, $annee_scolaire);
            if (!$stmtCaisse->execute()) {
                throw new Exception("Erreur insertion caisse pour élève $eleve_id");
            }
        }
    }

    $stmtDetail->close();
    $stmtCaisse->close();
    $con->commit();
    $con->close();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $con->rollback();
    $con->close();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
