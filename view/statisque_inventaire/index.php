<?php
require_once('../../layouts/constants/head.php');
require_once('../../webapp/database/config.php');
require_once('../../layouts/navbar/navbar.php');

function money($v)
{
    return number_format((float)$v, 2, '.', ' ') . ' $';
}

/* =====================================================
   FONCTION REQUETE INVENTAIRE (Utilise la table menage)
===================================================== */
function getInventaireDirect(mysqli $con, string $whereClause)
{
    $sql = "
    SELECT
        m.id,
        m.noms,
        m.anneeScolaire,
        m.montantAPayer AS scolaire_a_payer,
        m.montantAPayerFC AS connexe_a_payer,

        GROUP_CONCAT(
            DISTINCT CONCAT(e.nom, ' ', e.postnom, ' ', e.prenom)
            SEPARATOR '<br>'
        ) AS enfants,

        GROUP_CONCAT(
            DISTINCT CONCAT(c.description, ' (', cy.description, ')')
            SEPARATOR '<br>'
        ) AS classes,

        COALESCE(ps.total_paye_scol, 0) AS scolaire_paye,
        COALESCE(pc.total_paye_connexe, 0) AS connexe_paye

    FROM menage m

    LEFT JOIN eleve e ON e.menage = m.id
    LEFT JOIN classe c ON c.id = e.classe
    LEFT JOIN cycle cy ON cy.id = c.cycle

    -- Somme des paiements scolaires (montantPayer dans paiement)
    LEFT JOIN (
        SELECT menage, SUM(montantPayer) AS total_paye_scol
        FROM paiement
        GROUP BY menage
    ) ps ON ps.menage = m.id

    -- Somme des paiements connexes (montantPayer dans paiement_divers)
    LEFT JOIN (
        SELECT menage, SUM(montantPayer) AS total_paye_connexe
        FROM paiement_divers
        GROUP BY menage
    ) pc ON pc.menage = m.id

    $whereClause

    GROUP BY m.id
    ORDER BY m.noms
    ";

    $res = mysqli_query($con, $sql);
    $data = [];

    while ($row = mysqli_fetch_assoc($res)) {
        // Calculs des restes
        $row['scolaire_a_payer'] = (float)$row['scolaire_a_payer'];
        $row['scolaire_paye']    = (float)$row['scolaire_paye'];
        $row['scolaire_reste']   = max($row['scolaire_a_payer'] - $row['scolaire_paye'], 0);

        $row['connexe_a_payer']  = (float)$row['connexe_a_payer'];
        $row['connexe_paye']     = (float)$row['connexe_paye'];
        $row['connexe_reste']    = max($row['connexe_a_payer'] - $row['connexe_paye'], 0);

        $data[] = $row;
    }

    return $data;
}

/* =====================================================
   RECUPERATION DES DONNEES
===================================================== */

$dataActifs    = getInventaireDirect($con, "WHERE m.STATUS='actif' AND UPPER(m.noms) NOT LIKE '%PERSONNEL%'");
$dataInactifs  = getInventaireDirect($con, "WHERE m.STATUS='inactif' AND UPPER(m.noms) NOT LIKE '%PERSONNEL%'");
$dataPersonnel = getInventaireDirect($con, "WHERE UPPER(m.noms) LIKE '%PERSONNEL%'");

/* =====================================================
   CYCLES
===================================================== */

$cycles = [];
$resCycles = mysqli_query($con, "SELECT * FROM cycle ORDER BY id");
while ($c = mysqli_fetch_assoc($resCycles)) {
    $cycles[] = $c;
}

/* =====================================================
   STATISTIQUES & COMPTEURS
===================================================== */

function calculateStats(mysqli $con, array $dataList): array
{
    $stats = [
        'familles' => count($dataList),
        'garcons' => 0,
        'filles' => 0,
        'scolaire_a_payer' => 0,
        'scolaire_paye' => 0,
        'scolaire_reste' => 0,
        'connexe_a_payer' => 0,
        'connexe_paye' => 0,
        'connexe_reste' => 0,
        'cycles' => []
    ];

    foreach ($dataList as $row) {
        $stats['scolaire_a_payer'] += $row['scolaire_a_payer'];
        $stats['scolaire_paye']    += $row['scolaire_paye'];
        $stats['scolaire_reste']   += $row['scolaire_reste'];

        $stats['connexe_a_payer']  += $row['connexe_a_payer'];
        $stats['connexe_paye']     += $row['connexe_paye'];
        $stats['connexe_reste']    += $row['connexe_reste'];

        $res = mysqli_query($con, "
            SELECT e.genre, c.cycle
            FROM eleve e
            LEFT JOIN classe c ON c.id = e.classe
            WHERE e.menage = " . (int)$row['id']
        );

        while ($el = mysqli_fetch_assoc($res)) {
            if (strtoupper($el['genre']) === 'M') {
                $stats['garcons']++;
            } else {
                $stats['filles']++;
            }

            $cId = $el['cycle'];
            if ($cId) {
                $stats['cycles'][$cId] = ($stats['cycles'][$cId] ?? 0) + 1;
            }
        }
    }

    return $stats;
}

$statActifs    = calculateStats($con, $dataActifs);
$statInactifs  = calculateStats($con, $dataInactifs);
$statPersonnel = calculateStats($con, $dataPersonnel);

// Totaux Globaux (Synthèse Générale)
$globalFamilles = $statActifs['familles'] + $statInactifs['familles'] + $statPersonnel['familles'];
$globalGarcons  = $statActifs['garcons'] + $statInactifs['garcons'] + $statPersonnel['garcons'];
$globalFilles   = $statActifs['filles'] + $statInactifs['filles'] + $statPersonnel['filles'];

$globalScolaireAPayer = $statActifs['scolaire_a_payer'] + $statInactifs['scolaire_a_payer'] + $statPersonnel['scolaire_a_payer'];
$globalScolairePaye   = $statActifs['scolaire_paye'] + $statInactifs['scolaire_paye'] + $statPersonnel['scolaire_paye'];
$globalScolaireReste  = $statActifs['scolaire_reste'] + $statInactifs['scolaire_reste'] + $statPersonnel['scolaire_reste'];

$globalConnexeAPayer  = $statActifs['connexe_a_payer'] + $statInactifs['connexe_a_payer'] + $statPersonnel['connexe_a_payer'];
$globalConnexePaye    = $statActifs['connexe_paye'] + $statInactifs['connexe_paye'] + $statPersonnel['connexe_paye'];
$globalConnexeReste   = $statActifs['connexe_reste'] + $statInactifs['connexe_reste'] + $statPersonnel['connexe_reste'];

$globalCycles = [];
foreach ($cycles as $cycle) {
    $cid = $cycle['id'];
    $globalCycles[$cid] = ($statActifs['cycles'][$cid] ?? 0)
                        + ($statInactifs['cycles'][$cid] ?? 0)
                        + ($statPersonnel['cycles'][$cid] ?? 0);
}
?>

<div class="main-panel">
    <div class="content-wrapper">

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <button onclick="history.back()" class="btn btn-dark">Retour</button>
                        <a href="export_csv.php?export=csv" class="btn btn-success">
                            Export l'inventaire en fichier Excel
                        </a>
                    </div>
                </div>

                <!-- SYNTHÈSE GÉNÉRALE -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Synthèse générale de l'inventaire</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>Catégorie</th>
                                    <th>Familles</th>
                                    <th>Garçons</th>
                                    <th>Filles</th>
                                    <th>A payer scolaire</th>
                                    <th>Payé scolaire</th>
                                    <th>Reste scolaire</th>
                                    <th>A payer connexe</th>
                                    <th>Payé connexe</th>
                                    <th>Reste connexe</th>
                                    <?php foreach ($cycles as $cycle): ?>
                                        <th><?= htmlspecialchars($cycle['description']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-primary fw-bold">
                                    <td>GLOBAL</td>
                                    <td><?= $globalFamilles ?></td>
                                    <td><?= $globalGarcons ?></td>
                                    <td><?= $globalFilles ?></td>
                                    <td><?= money($globalScolaireAPayer) ?></td>
                                    <td><?= money($globalScolairePaye) ?></td>
                                    <td><?= money($globalScolaireReste) ?></td>
                                    <td><?= money($globalConnexeAPayer) ?></td>
                                    <td><?= money($globalConnexePaye) ?></td>
                                    <td><?= money($globalConnexeReste) ?></td>
                                    <?php foreach ($cycles as $cycle): ?>
                                        <td><?= $globalCycles[$cycle['id']] ?? 0 ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr class="table-success">
                                    <td>Familles Actives</td>
                                    <td><?= $statActifs['familles'] ?></td>
                                    <td><?= $statActifs['garcons'] ?></td>
                                    <td><?= $statActifs['filles'] ?></td>
                                    <td><?= money($statActifs['scolaire_a_payer']) ?></td>
                                    <td><?= money($statActifs['scolaire_paye']) ?></td>
                                    <td><?= money($statActifs['scolaire_reste']) ?></td>
                                    <td><?= money($statActifs['connexe_a_payer']) ?></td>
                                    <td><?= money($statActifs['connexe_paye']) ?></td>
                                    <td><?= money($statActifs['connexe_reste']) ?></td>
                                    <?php foreach ($cycles as $cycle): ?>
                                        <td><?= $statActifs['cycles'][$cycle['id']] ?? 0 ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr class="table-warning">
                                    <td>Familles Inactives</td>
                                    <td><?= $statInactifs['familles'] ?></td>
                                    <td><?= $statInactifs['garcons'] ?></td>
                                    <td><?= $statInactifs['filles'] ?></td>
                                    <td><?= money($statInactifs['scolaire_a_payer']) ?></td>
                                    <td><?= money($statInactifs['scolaire_paye']) ?></td>
                                    <td><?= money($statInactifs['scolaire_reste']) ?></td>
                                    <td><?= money($statInactifs['connexe_a_payer']) ?></td>
                                    <td><?= money($statInactifs['connexe_paye']) ?></td>
                                    <td><?= money($statInactifs['connexe_reste']) ?></td>
                                    <?php foreach ($cycles as $cycle): ?>
                                        <td><?= $statInactifs['cycles'][$cycle['id']] ?? 0 ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr class="table-secondary">
                                    <td>Personnel</td>
                                    <td><?= $statPersonnel['familles'] ?></td>
                                    <td><?= $statPersonnel['garcons'] ?></td>
                                    <td><?= $statPersonnel['filles'] ?></td>
                                    <td><?= money($statPersonnel['scolaire_a_payer']) ?></td>
                                    <td><?= money($statPersonnel['scolaire_paye']) ?></td>
                                    <td><?= money($statPersonnel['scolaire_reste']) ?></td>
                                    <td><?= money($statPersonnel['connexe_a_payer']) ?></td>
                                    <td><?= money($statPersonnel['connexe_paye']) ?></td>
                                    <td><?= money($statPersonnel['connexe_reste']) ?></td>
                                    <?php foreach ($cycles as $cycle): ?>
                                        <td><?= $statPersonnel['cycles'][$cycle['id']] ?? 0 ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FAMILLES ACTIVES -->
                <div class="table-responsive mb-4">
                    <h3 class="mb-3 text-uppercase">Situation des Familles Actives</h3>
                    <table class="table table-bordered table-striped" id="inventaireTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Ménage</th>
                                <th>Enfants</th>
                                <th>Classe(s)</th>
                                <th>A payer scolaire</th>
                                <th>Payé scolaire</th>
                                <th>Reste scolaire</th>
                                <th>A payer connexe</th>
                                <th>Payé connexe</th>
                                <th>Reste connexe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataActifs as $row): ?>
                                <tr>
                                    <td><?= $row['noms'] ?></td>
                                    <td><?= $row['enfants'] ?></td>
                                    <td><?= $row['classes'] ?></td>
                                    <td><?= money($row['scolaire_a_payer']) ?></td>
                                    <td><?= money($row['scolaire_paye']) ?></td>
                                    <td><?= money($row['scolaire_reste']) ?></td>
                                    <td><?= money($row['connexe_a_payer']) ?></td>
                                    <td><?= money($row['connexe_paye']) ?></td>
                                    <td><?= money($row['connexe_reste']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="3">TOTAL FAMILLES ACTIVES</th>
                                <th><?= money($statActifs['scolaire_a_payer']) ?></th>
                                <th><?= money($statActifs['scolaire_paye']) ?></th>
                                <th><?= money($statActifs['scolaire_reste']) ?></th>
                                <th><?= money($statActifs['connexe_a_payer']) ?></th>
                                <th><?= money($statActifs['connexe_paye']) ?></th>
                                <th><?= money($statActifs['connexe_reste']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <hr>

                <!-- FAMILLES INACTIVES -->
                <div class="table-responsive mb-4">
                    <h3 class="mb-3 text-uppercase">Situation des Familles Inactives</h3>
                    <table class="table table-bordered table-striped">
                        <thead class="table-warning">
                            <tr>
                                <th>Ménage</th>
                                <th>Enfants</th>
                                <th>Classe(s)</th>
                                <th>A payer scolaire</th>
                                <th>Payé scolaire</th>
                                <th>Reste scolaire</th>
                                <th>A payer connexe</th>
                                <th>Payé connexe</th>
                                <th>Reste connexe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataInactifs as $row): ?>
                                <tr>
                                    <td><?= $row['noms'] ?></td>
                                    <td><?= $row['enfants'] ?></td>
                                    <td><?= $row['classes'] ?></td>
                                    <td><?= money($row['scolaire_a_payer']) ?></td>
                                    <td><?= money($row['scolaire_paye']) ?></td>
                                    <td><?= money($row['scolaire_reste']) ?></td>
                                    <td><?= money($row['connexe_a_payer']) ?></td>
                                    <td><?= money($row['connexe_paye']) ?></td>
                                    <td><?= money($row['connexe_reste']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-warning">
                            <tr>
                                <th colspan="3">TOTAL FAMILLES INACTIVES</th>
                                <th><?= money($statInactifs['scolaire_a_payer']) ?></th>
                                <th><?= money($statInactifs['scolaire_paye']) ?></th>
                                <th><?= money($statInactifs['scolaire_reste']) ?></th>
                                <th><?= money($statInactifs['connexe_a_payer']) ?></th>
                                <th><?= money($statInactifs['connexe_paye']) ?></th>
                                <th><?= money($statInactifs['connexe_reste']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <hr>

                <!-- PERSONNELS -->
                <div class="table-responsive mb-4">
                    <h3 class="mb-3 text-uppercase">Situation des Personnels</h3>
                    <table class="table table-bordered table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th>Ménage</th>
                                <th>Enfants</th>
                                <th>Classe(s)</th>
                                <th>A payer scolaire</th>
                                <th>Payé scolaire</th>
                                <th>Reste scolaire</th>
                                <th>A payer connexe</th>
                                <th>Payé connexe</th>
                                <th>Reste connexe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataPersonnel as $row): ?>
                                <tr>
                                    <td><?= $row['noms'] ?></td>
                                    <td><?= $row['enfants'] ?></td>
                                    <td><?= $row['classes'] ?></td>
                                    <td><?= money($row['scolaire_a_payer']) ?></td>
                                    <td><?= money($row['scolaire_paye']) ?></td>
                                    <td><?= money($row['scolaire_reste']) ?></td>
                                    <td><?= money($row['connexe_a_payer']) ?></td>
                                    <td><?= money($row['connexe_paye']) ?></td>
                                    <td><?= money($row['connexe_reste']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="3">TOTAL PERSONNEL</th>
                                <th><?= money($statPersonnel['scolaire_a_payer']) ?></th>
                                <th><?= money($statPersonnel['scolaire_paye']) ?></th>
                                <th><?= money($statPersonnel['scolaire_reste']) ?></th>
                                <th><?= money($statPersonnel['connexe_a_payer']) ?></th>
                                <th><?= money($statPersonnel['connexe_paye']) ?></th>
                                <th><?= money($statPersonnel['connexe_reste']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once('../../layouts/constants/footer.php'); ?>