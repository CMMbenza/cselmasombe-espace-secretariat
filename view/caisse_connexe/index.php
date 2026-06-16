<?php
require_once('../../webapp/database/dbcongig.php');
require_once ('../../layouts/constants/head.php');  
require_once ('../../layouts/navbar/navbar.php');

// Initialiser l'année scolaire
$annee_scolaire = $_GET['annee_scolaire'] ?? '2025-2026';
$jour_actuel = $_POST['date_versement'] ?? date('Y-m-d');
$mois_courant = date('m', strtotime($jour_actuel));

// Traitement du versement
if (isset($_POST['verser_caisse_divers'])) {
    // Vérifier si le versement a déjà été effectué pour cette date
    $verif_sql = "SELECT id FROM balance 
                  WHERE typeReference = 'versement frais divers' 
                  AND dateUpdate = ? 
                  AND anneeScolaire = ?";
    $stmt_check = $conn->prepare($verif_sql);
    $stmt_check->bind_param("ss", $jour_actuel, $annee_scolaire);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "<div class='alert alert-warning text-center'>💡 Versement déjà effectué pour le $jour_actuel.</div>";
    } else {
        // Calcul du total à verser
        $sql_total = "SELECT SUM(montant_paye) AS total 
                      FROM caisse_frais_divers 
                      WHERE DATE(date_paiement) = ? AND annee_scolaire = ?";
        $stmt_total = $conn->prepare($sql_total);
        $stmt_total->bind_param("ss", $jour_actuel, $annee_scolaire);
        $stmt_total->execute();
        $res_total = $stmt_total->get_result()->fetch_assoc();
        $total = $res_total['total'] ?? 0;

        if ($total > 0) {
            // Insertion dans balance
            $stmt_bal = $conn->prepare("INSERT INTO balance (typeReference, entre, sorti, reste, dateUpdate, anneeScolaire)
                                        VALUES ('versement frais divers', ?, 0, ?, ?, ?)");
            $stmt_bal->bind_param("ddss", $total, $total, $jour_actuel, $annee_scolaire);
            $stmt_bal->execute();

            // Virement par cycle
            $sql_cycles = "SELECT cycle_id, SUM(montant_paye) AS total_cycle 
                           FROM caisse_frais_divers 
                           WHERE DATE(date_paiement) = ? AND annee_scolaire = ? 
                           GROUP BY cycle_id";
            $stmt_cy = $conn->prepare($sql_cycles);
            $stmt_cy->bind_param("ss", $jour_actuel, $annee_scolaire);
            $stmt_cy->execute();
            $res_cy = $stmt_cy->get_result();

            while ($ligne = $res_cy->fetch_assoc()) {
                $cy = $ligne['cycle_id'];
                $mnt = $ligne['total_cycle'];

                $stmt_vr = $conn->prepare("INSERT INTO virement (typeReference, cycle_id, montant, periode_mois, date_virement, annee_scolaire)
                                           VALUES ('frais divers vers caisse centrale', ?, ?, ?, NOW(), ?)");
                $stmt_vr->bind_param("iids", $cy, $mnt, $mois_courant, $annee_scolaire);
                $stmt_vr->execute();
            }

            // Suppression des lignes versées
            $stmt_del = $conn->prepare("DELETE FROM caisse_frais_divers 
                                        WHERE DATE(date_paiement) = ? AND annee_scolaire = ?");
            $stmt_del->bind_param("ss", $jour_actuel, $annee_scolaire);
            $stmt_del->execute();

            echo "<div class='alert alert-success text-center'>✅ Versement effectué avec succès pour le $jour_actuel.</div>";
            echo "<script>setTimeout(() => location.reload(), 2000);</script>";
        } else {
            echo "<div class='alert alert-info text-center'>ℹ️ Aucun montant à verser pour la date du $jour_actuel.</div>";
        }
    }
}

// Récupérer les montants par cycle
$sql = "SELECT c.id AS cycle_id, c.description AS nom_cycle, 
               IFNULL(SUM(cfd.montant_paye), 0) AS montant_total
        FROM cycle c
        LEFT JOIN caisse_frais_divers cfd 
            ON c.id = cfd.cycle_id AND cfd.annee_scolaire = ?
        GROUP BY c.id
        ORDER BY c.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $annee_scolaire);
$stmt->execute();
$result = $stmt->get_result();

$cycles = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $cycles[] = $row;
    $total += $row['montant_total'];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Frais Divers - par Cycle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h3 class="mb-4 text-center text-uppercase">Frais Divers - par Cycle <small
                class="text-muted">(<?= htmlspecialchars($annee_scolaire) ?>)</small></h3>

        <div class="row">
            <?php foreach ($cycles as $cycle): ?>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="alert alert-secondary text-center">
                    <strong><?= htmlspecialchars($cycle['nom_cycle']) ?></strong><br>
                    <span class="h5"><?= number_format($cycle['montant_total'], 2) ?> $</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="alert alert-info text-center mt-4 text-uppercase">
            <h5>Total Frais Divers</h5>
            <h3><?= number_format($total, 2) ?> $</h3>
        </div>

        <form method="POST" class="text-center mt-3 mb-5">
            <label for="date_versement" class="d-none"><strong>Date de versement :</strong></label>
            <input type="date" name="date_versement" value="<?= $jour_actuel ?>" class="d-none form-control mb-2"
                style="max-width: 250px; margin: 0 auto;">
            <button type="submit" name="verser_caisse_divers" class="btn btn-primary mt-2">
                💸 Verser vers la caisse centrale
            </button>
        </form>
    </div>
</body>

<?php require_once ('../../layouts/constants/footer.php'); ?>

</html>