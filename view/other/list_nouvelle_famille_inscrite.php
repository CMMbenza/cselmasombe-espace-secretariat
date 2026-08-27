<?php
require_once('../../layouts/constants/head.php');
require_once('../../webapp/database/config.php');
require_once('../../layouts/navbar/navbar.php');

$sql = "
    SELECT 
        m.id AS id_menage,
        m.noms AS nom_menage,
        m.nom_du_pere,
        m.nom_de_la_mere,
        m.telephone,
        m.commune,
        m.quartier,
        m.avenue,
        m.numero,
        m.dateCreated,
        m.anneeScolaire,
        COUNT(e.id) AS total_enfants,
        
        GROUP_CONCAT(
            DISTINCT CONCAT(e.nom, ' ', e.postnom, ' ', e.prenom) 
            SEPARATOR '<br>'
        ) AS liste_enfants,
        
        GROUP_CONCAT(
            DISTINCT CONCAT(c.description, ' (', cy.description, ')') 
            SEPARATOR '<br>'
        ) AS liste_classes

    FROM menage m
    LEFT JOIN eleve e ON e.menage = m.id
    LEFT JOIN classe c ON c.id = e.classe
    LEFT JOIN cycle cy ON cy.id = c.cycle
    
    WHERE m.id_original IS NULL 
      AND m.STATUS = 'actif'
      
    GROUP BY m.id
    ORDER BY m.id DESC
";

$result = mysqli_query($con, $sql);

$data = [];
$total_menages = 0;
$total_eleves_global = 0;

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
        $total_menages++;
        $total_eleves_global += (int)$row['total_enfants'];
    }
}
?>

<div class="main-panel">
    <div class="content-wrapper">

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title text-uppercase font-weight-bold mb-0">
                        Liste des Nouvelles Familles Inscrites
                    </h4>
                    <div>
                        <a href="export_nouvelles_familles.php" class="btn btn-success btn-sm me-2">
                            Exporter en Excel
                        </a>
                        <button onclick="history.back()" class="btn btn-dark btn-sm">Retour</button>
                    </div>
                </div>

                <!-- BADGES RECAPITULATIFS -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white p-3">
                            <h6 class="mb-1 text-uppercase">Total Ménages</h6>
                            <h3 class="mb-0 font-weight-bold"><?= $total_menages ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white p-3">
                            <h6 class="mb-1 text-uppercase">Total Élèves</h6>
                            <h3 class="mb-0 font-weight-bold"><?= $total_eleves_global ?></h3>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="tableNouvellesFamilles">
                        <thead class="table text-center">
                            <tr>
                                <th>#</th>
                                <th>Ménage</th>
                                <th>Parents</th>
                                <th>Téléphone</th>
                                <th>Adresse</th>
                                <th>Enfants Inscrits</th>
                                <th>Classe(s)</th>
                                <th>Enfants</th>
                                <th>Inscrit le</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (!empty($data)): 
                                $i = 1;
                                foreach ($data as $row): 
                            ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?= $i++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nom_menage']) ?></strong>
                                </td>
                                <td>
                                    <small>
                                        <strong>Père :</strong> <?= htmlspecialchars($row['nom_du_pere'] ?: 'N/A') ?><br>
                                        <strong>Mère :</strong> <?= htmlspecialchars($row['nom_de_la_mere'] ?: 'N/A') ?>
                                    </small>
                                </td>
                                <td><?= htmlspecialchars($row['telephone']) ?></td>
                                <td>
                                    <small>
                                        <?= htmlspecialchars($row['avenue'] . ' n°' . $row['numero']) ?><br>
                                        Q/ <?= htmlspecialchars($row['quartier']) ?>, C/ <?= htmlspecialchars($row['commune']) ?>
                                    </small>
                                </td>
                                <td><?= $row['liste_enfants'] ?: '<span class="badge bg-warning text-dark">Aucun enfant</span>' ?></td>
                                <td><?= $row['liste_classes'] ?: '-' ?></td>
                                <td class="text-center fw-bold">
                                    <span class="badge bg-info text-dark">
                                        <?= $row['total_enfants'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?= date('d/m/Y', strtotime($row['dateCreated'])) ?>
                                </td>
                            </tr>
                            <?php 
                                endforeach; 
                            else: 
                            ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    Aucune nouvelle famille trouvée.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($data)): ?>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="7">TOTAL GENERAL (Ménages : <?= $total_menages ?>)</td>
                                <td class="text-center"><?= $total_eleves_global ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once('../../layouts/constants/footer.php'); ?>