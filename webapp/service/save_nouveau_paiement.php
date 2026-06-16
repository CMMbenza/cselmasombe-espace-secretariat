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

// Démarrer transaction
$con->begin_transaction();

try {
    // Insertion paiement global
    $stmt = $con->prepare("INSERT INTO paiement (menage, montantAPayer, montantPayer, resteAPayer, observation, dateCreated, anneeScolaire) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $observation = 'Paiement encours.';
    $reste = max(0, $montant_global - $montant_paye);
    $stmt->bind_param("idddsss", $menage_id, $montant_global, $montant_paye, $reste, $observation, $dateNow, $annee_scolaire);

    if (!$stmt->execute()) {
        throw new Exception('Erreur insertion paiement global');
    }

    $paiement_id = $stmt->insert_id;
    $stmt->close();

    // Préparer insertion paiement detail
    $stmtDetail = $con->prepare("INSERT INTO paiement_detail (paiement_id, menage_id, eleve_id, tranche_id, montant, date_created) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmtDetail) {
        throw new Exception('Erreur préparation insertion détails');
    }

    // Préparer insertion caisse frais scolaire
    $stmtCaisse = $con->prepare("INSERT INTO caisse_frais_scolaire (cycle_id, montant_paye, date_paiement, annee_scolaire) VALUES (?, ?, ?, ?)");
    if (!$stmtCaisse) {
        throw new Exception('Erreur préparation insertion caisse frais scolaire');
    }

    foreach ($eleves as $eleve) {
        $eleve_id = intval($eleve['eleve_id']);
        $cycle_id = intval($eleve['cycle_id'] ?? 0); // Doit être envoyé côté front
        if ($cycle_id === 0) {
            throw new Exception("Cycle ID manquant pour élève $eleve_id");
        }

        $montant_paye_eleve = 0;

        $tranches = $eleve['tranches'] ?? [];
        foreach ($tranches as $tranche) {
            $tranche_id = intval($tranche['tranche_id']);
            $montant_paye_tranche = floatval($tranche['montant_paye']);
            if ($montant_paye_tranche <= 0) continue;

            // Insert paiement_detail
            $stmtDetail->bind_param("iiiids", $paiement_id, $menage_id, $eleve_id, $tranche_id, $montant_paye_tranche, $dateNow);
            if (!$stmtDetail->execute()) {
                throw new Exception('Erreur insertion paiement detail');
            }

            $montant_paye_eleve += $montant_paye_tranche;
        }

        // Insert dans caisse_frais_scolaire si paiement > 0
        if ($montant_paye_eleve > 0) {
            $stmtCaisse->bind_param("idss", $cycle_id, $montant_paye_eleve, $dateNow, $annee_scolaire);
            if (!$stmtCaisse->execute()) {
                throw new Exception('Erreur insertion caisse frais scolaire');
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
