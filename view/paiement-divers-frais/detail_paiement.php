<?php
require_once('../../webapp/database/config.php');

if (!isset($_GET['paiement_id'])) {
    echo "Paiement introuvable.";
    exit;
}

$paiement_id = intval($_GET['paiement_id']);

// Détails du paiement global
$sql = "SELECT p.*, m.noms AS famille_nom FROM paiement p
        JOIN menage m ON p.menage = m.id
        WHERE p.id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $paiement_id);
$stmt->execute();
$paiement = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$paiement) {
    echo "Aucun paiement trouvé.";
    exit;
}

// Détails des tranches par élève
$sql = "SELECT e.nom, e.postnom, e.prenom, t.numero_tranche, pd.montant
        FROM paiement_detail pd
        JOIN eleve e ON pd.eleve_id = e.id
        JOIN tranche t ON pd.tranche_id = t.id
        WHERE pd.paiement_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $paiement_id);
$stmt->execute();
$details = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail du Paiement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin: auto;
            max-width: 800px;
            padding: 20px;
            border-radius: 15px;
        }
        h3, h5 {
            color: #0d6efd;
        }
    </style>
</head>
<body class="p-4">
    <div class="card shadow">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Détail du paiement #<?= $paiement_id ?></h3>
            <button onclick="window.print()" class="d-none btn btn-outline-primary">Imprimer</button>
        </div>
        <p><strong>Famille :</strong> <?= htmlspecialchars($paiement['famille_nom']) ?></p>
        <p><strong>Date :</strong> <?= date("d/m/Y à H:i", strtotime($paiement['dateCreated'])) ?></p>
        <p><strong>Montant payé :</strong> <?= number_format($paiement['montantPayer'], 2) ?> $</p>
        <p><strong>Solde :</strong> <?= number_format($paiement['resteAPayer'], 2) ?> $</p>
        <?php if (!empty($paiement['mode'])): ?>
            <p><strong>Mode de paiement :</strong> <?= htmlspecialchars($paiement['mode']) ?></p>
        <?php endif; ?>
        <p><strong>Observation :</strong> <?= nl2br(htmlspecialchars($paiement['observation'])) ?></p>

        <hr>
        <h5 class="mb-3">Détails par élève et tranche</h5>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Élève</th>
                    <th>Tranche</th>
                    <th>Montant payé ($)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($details->num_rows > 0): ?>
                    <?php while ($row = $details->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nom'] . ' ' . $row['postnom'] . ' ' . $row['prenom']) ?></td>
                            <td>Tranche <?= $row['numero_tranche'] ?></td>
                            <td><?= number_format($row['montant'], 2) ?> $</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">Aucun détail de tranche disponible.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="javascript:history.back()" class="btn btn-secondary mt-3">Retour</a>
    </div>
</body>
</html>
