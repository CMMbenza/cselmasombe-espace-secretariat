<?php
require_once('../../webapp/database/dbcongig.php');
header('Content-Type: application/json');

$menage_id = $_GET['menage_id'] ?? null;

if (!$menage_id) {
    echo json_encode([]);
    exit;
}

$eleves = [];

// Récupération des élèves actifs du ménage avec leur classe et cycle
$sql = "SELECT e.id, e.nom, e.postnom, cl.cycle 
        FROM eleve e 
        JOIN classe cl ON e.classe = cl.id 
        WHERE e.menage = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $menage_id);
$stmt->execute();
$result = $stmt->get_result();

while ($eleve = $result->fetch_assoc()) {
    $eleve_id = $eleve['id'];
    $cycle_id = $eleve['cycle'];
    $tranches_restantes = [];

    // Récupération du montant total scolarité pour le cycle
    $totalCycleQuery = "SELECT montant FROM scolarite WHERE cycle = ? AND LOWER(description) = 'frais scolaire' LIMIT 1";
    $stmtTC = $conn->prepare($totalCycleQuery);
    $stmtTC->bind_param("i", $cycle_id);
    $stmtTC->execute();
    $resTC = $stmtTC->get_result();
    $totalCycle = $resTC->fetch_assoc()['montant'] ?? 0;
    $stmtTC->close();

    // Récupération des tranches applicables à ce cycle
    $sqlTranches = "SELECT t.id AS tranche_id, t.numero_tranche, t.montant
                    FROM tranche t
                    JOIN scolarite s ON t.frais_id = s.id
                    WHERE s.cycle = ?
                    ORDER BY t.numero_tranche ASC";
    $stmtT = $conn->prepare($sqlTranches);
    $stmtT->bind_param("i", $cycle_id);
    $stmtT->execute();
    $resTranches = $stmtT->get_result();

    while ($tranche = $resTranches->fetch_assoc()) {
        $tranche_id = $tranche['tranche_id'];

        // Calcul des versements déjà effectués sur cette tranche
        $sqlP = "SELECT SUM(montant) AS total FROM paiement_detail WHERE eleve_id = ? AND tranche_id = ?";
        $stmtP = $conn->prepare($sqlP);
        $stmtP->bind_param("ii", $eleve_id, $tranche_id);
        $stmtP->execute();
        $resP = $stmtP->get_result();
        $totalPaye = $resP->fetch_assoc()['total'] ?? 0;
        $stmtP->close();

        // Si la tranche n'est pas encore totalement payée
        if ($totalPaye < $tranche['montant']) {
            $tranches_restantes[] = [
                "tranche_id"     => $tranche_id,
                "numero_tranche" => $tranche['numero_tranche'],
                "montant"        => (float)($tranche['montant'] - $totalPaye)
            ];
            break; // On ne prend que la première tranche impayée en cours
        }
    }
    $stmtT->close();

    $eleves[] = [
        'eleve_id'           => $eleve_id,
        'cycle_id'           => $cycle_id,
        'nom_eleve'          => trim($eleve['nom'] . ' ' . $eleve['postnom']),
        'total_cycle'        => (float)$totalCycle,
        'tranches_restantes' => $tranches_restantes
    ];
}
$stmt->close();

echo json_encode($eleves);
exit;