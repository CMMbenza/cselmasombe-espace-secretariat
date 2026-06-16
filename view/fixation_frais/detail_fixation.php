<?php
// Inclusion de la config de connexion
require_once('../../webapp/database/config.php'); // suppose que le code de connexion est dans config.php
require_once ('../../layouts/navbar/navbar.php');
require_once ('../../layouts/constants/head.php');

// Vérifie si le paramètre 'id' est passé
if (!isset($_GET['cycle']) || !is_numeric($_GET['cycle'])) {
    echo "Paramètre ID invalide.";
    exit;
}

$id = intval($_GET['cycle']); // Sécurisation de l'ID

// Préparation de la requête SQL
$sql = "
    SELECT
        c.description AS cycle,
        f.anneeScolaire,
        t.numero_tranche,
        t.montant
    FROM
        tranche t
    JOIN scolarite f ON
        t.frais_id = f.id
    JOIN cycle c ON
        f.cycle = c.id
    WHERE f.id = ?
    ORDER BY
        c.id,
        t.numero_tranche
";

// Utilisation de requête préparée
$stmt = mysqli_prepare($con, $sql);
if (!$stmt) {
    echo "Erreur dans la préparation de la requête : " . mysqli_error($con);
    exit;
}

// Bind du paramètre
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

// Récupération des résultats
$result = mysqli_stmt_get_result($stmt);
?>

<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Détail des frais pour le cycle </h4>
                        <div class="table-responsive">
                            <table class="table" id="myTable">
                                <thead>
                                    <tr>
                                        <th> Cycle </th>
                                        <th> Tranche </th>
                                        <th> Montant fixé </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                                    ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($row['cycle']);?>
                                        </td>
                                        <td> <?php echo htmlspecialchars($row['numero_tranche']);?>é Tranche</td>
                                        <td> <?php echo htmlspecialchars($row['montant']);?>$</td>
                                    </tr>
                                    <?php }}else {
                                        echo "Aucun résultat trouvé pour l'ID donné.";
                                    } // Fermeture
                                    mysqli_stmt_close($stmt);
                                    mysqli_close($con);?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- content-wrapper ends -->
</div>
<?php require_once ('../../layouts/constants/footer.php'); ?>