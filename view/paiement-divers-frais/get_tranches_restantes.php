<?php
require_once('../../webapp/database/dbcongig.php');
header('Content-Type: application/json');

$menage_id = $_GET['menage_id'] ?? null;

if (!$menage_id) {
    echo json_encode([]);
    exit;
}

$eleves = [];

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

    // 🔹 Récupération du montant total du cycle (via scolarite)
    $totalCycleQuery = "SELECT montant FROM scolarite WHERE cycle = ?";
    $stmtTC = $conn->prepare($totalCycleQuery);
    $stmtTC->bind_param("i", $cycle_id);
    $stmtTC->execute();
    $resTC = $stmtTC->get_result();
    $totalCycle = $resTC->fetch_assoc()['montant'] ?? 0;

    // 🔹 Récupération des tranches du cycle
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

        // 🔹 Vérifier si la tranche est soldée
        $sqlP = "SELECT SUM(montant) AS total 
                 FROM paiement_detail 
                 WHERE eleve_id = ? AND tranche_id = ?";
        $stmtP = $conn->prepare($sqlP);
        $stmtP->bind_param("ii", $eleve_id, $tranche_id);
        $stmtP->execute();
        $resP = $stmtP->get_result();
        $paiement = $resP->fetch_assoc();
        $totalPaye = $paiement['total'] ?? 0;

        if ($totalPaye < $tranche['montant']) {
            // Cette tranche n'est pas encore soldée
            $tranches_restantes[] = [
                "tranche_id" => $tranche_id,
                "numero_tranche" => $tranche['numero_tranche'],
                "montant" => $tranche['montant'] - $totalPaye
            ];
            break;
        }
    }

    $eleves[] = [
        'eleve_id' => $eleve_id,
        'nom_eleve' => $eleve['nom'] . ' ' . $eleve['postnom'],
        'total_cycle' => $totalCycle,
        'tranches_restantes' => $tranches_restantes
    ];
}

echo json_encode($eleves);
exit;
