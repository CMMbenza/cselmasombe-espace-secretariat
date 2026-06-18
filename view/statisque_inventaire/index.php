<?php
require_once('../../layouts/constants/head.php');
require_once('../../webapp/database/config.php');
require_once('../../layouts/navbar/navbar.php');
require_once('inventaire_functions.php');

function money($v)
{
    return number_format((float)$v, 2, '.', ' ') . ' $';
}

/* =====================================================
   DONNEES
===================================================== */

function calculMontantReelScolaire(mysqli $con, $menageId, $anneeScolaire, $startTranche)
{
    $sql = "
        SELECT
            t.numero_tranche,
            SUM(t.montant) montant
        FROM eleve e
        INNER JOIN classe c ON c.id = e.classe
        INNER JOIN cycle cy ON cy.id = c.cycle
        INNER JOIN scolarite s
            ON s.cycle = cy.id
            AND s.anneeScolaire = ?
        INNER JOIN tranche t
            ON t.frais_id = s.id
        WHERE e.menage = ?
        GROUP BY t.numero_tranche
    ";

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "si", $anneeScolaire, $menageId);
    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);

    $total = 0;

    while ($r = mysqli_fetch_assoc($res)) {

        if ((int)$r['numero_tranche'] >= (int)$startTranche) {
            $total += (float)$r['montant'];
        }
    }

    return $total;
}

$resultActifs = getInventaire(
    $con,
    "WHERE m.STATUS='actif'
    AND UPPER(m.noms) NOT LIKE '%PERSONNEL%'"
);

$resultInactifs = getInventaire(
    $con,
    "WHERE m.STATUS='inactif'
    AND UPPER(m.noms) NOT LIKE '%PERSONNEL%'"
);

$resultPersonnel = getInventaire(
    $con,
    "WHERE UPPER(m.noms) LIKE '%PERSONNEL%'"
);

$totalFamilles = 0;
$totalScolaireAPayer = 0;
$totalScolairePaye = 0;
$totalScolaireReste = 0;
$totalConnexeAPayer = 0;
$totalConnexePaye = 0;
$totalConnexeReste = 0;

$dataActifs = [];

    while ($row = mysqli_fetch_assoc($resultActifs)) {

    $grid = build_grid($con, [
        'id'                   => $row['id'],
        'anneeScolaire'        => $row['anneeScolaire'],
        'start_tranche'        => $row['start_tranche'],
        'montantDejaPayerScol' => $row['scolaire_paye'],
        'totalDiversPayer'     => $row['connexe_paye']
    ]);

    $row['scolaire_a_payer'] = $grid['totalScolaireOnly'];

    $row['scolaire_reste'] =
    max(
        $grid['totalScolaireOnly'] - $row['scolaire_paye'],
        0
    );

    $dataActifs[] = $row;

    $totalFamilles++;

    $totalScolaireAPayer += $grid['totalScolaireOnly'];
    $totalScolairePaye += $row['scolaire_paye'];
    $totalScolaireReste += $row['scolaire_reste'];

    $totalConnexeAPayer += $row['connexe_a_payer'];
    $totalConnexePaye += $row['connexe_paye'];
    $totalConnexeReste += $row['connexe_reste'];
}

$dataInactifs = [];

while ($row = mysqli_fetch_assoc($resultInactifs)) {

    $grid = build_grid($con, [
        'id'                   => $row['id'],
        'anneeScolaire'        => $row['anneeScolaire'],
        'start_tranche'        => $row['start_tranche'],
        'montantDejaPayerScol' => $row['scolaire_paye'],
        'totalDiversPayer'     => $row['connexe_paye']
    ]);

    
    $row['scolaire_a_payer'] = $grid['totalScolaireOnly'];

$row['scolaire_reste'] =
    max(
        $grid['totalScolaireOnly'] - $row['scolaire_paye'],
        0
    );

    $dataInactifs[] = $row;
}

$dataPersonnel = [];

while ($row = mysqli_fetch_assoc($resultPersonnel)) {

    $grid = build_grid($con, [
        'id'                   => $row['id'],
        'anneeScolaire'        => $row['anneeScolaire'],
        'start_tranche'        => $row['start_tranche'],
        'montantDejaPayerScol' => $row['scolaire_paye'],
        'totalDiversPayer'     => $row['connexe_paye']
    ]);

    $row['scolaire_a_payer'] = $grid['totalScolaireOnly'];

$row['scolaire_reste'] =
    max(
        $grid['totalScolaireOnly'] - $row['scolaire_paye'],
        0
    );

    $dataPersonnel[] = $row;
}

/* =====================================================
   CYCLES
===================================================== */

$cycles = [];

$resCycles = mysqli_query($con,"
    SELECT *
    FROM cycle
    ORDER BY id
");

while($c = mysqli_fetch_assoc($resCycles)){
    $cycles[] = $c;
}

/* =====================================================
   COMPTEURS
===================================================== */

$globalFamilles = 0;
$globalGarcons = 0;
$globalFilles = 0;

$globalScolaireAPayer = 0;
$globalScolairePaye = 0;
$globalScolaireReste = 0;

$globalConnexeAPayer = 0;
$globalConnexePaye = 0;
$globalConnexeReste = 0;

$globalCycles = [];

$actifFamilles = count($dataActifs);
$actifGarcons = 0;
$actifFilles = 0;

$actifScolaireAPayer = 0;
$actifScolairePaye = 0;
$actifScolaireReste = 0;

$actifConnexeAPayer = 0;
$actifConnexePaye = 0;
$actifConnexeReste = 0;

$actifCycles = [];

$inactifFamilles = count($dataInactifs);
$inactifGarcons = 0;
$inactifFilles = 0;

$inactifScolaireAPayer = 0;
$inactifScolairePaye = 0;
$inactifScolaireReste = 0;

$inactifConnexeAPayer = 0;
$inactifConnexePaye = 0;
$inactifConnexeReste = 0;

$inactifCycles = [];

$personnelFamilles = count($dataPersonnel);
$personnelGarcons = 0;
$personnelFilles = 0;

$personnelScolaireAPayer = 0;
$personnelScolairePaye = 0;
$personnelScolaireReste = 0;

$personnelConnexeAPayer = 0;
$personnelConnexePaye = 0;
$personnelConnexeReste = 0;

$personnelCycles = []; 

/* =====================================================
   STATISTIQUES FAMILLES ACTIVES
===================================================== */

foreach ($dataActifs as $row) {

    $actifScolaireAPayer += $row['scolaire_a_payer'];
    $actifScolairePaye += $row['scolaire_paye'];
    $actifScolaireReste += $row['scolaire_reste'];

    $actifConnexeAPayer += $row['connexe_a_payer'];
    $actifConnexePaye += $row['connexe_paye'];
    $actifConnexeReste += $row['connexe_reste'];

    $res = mysqli_query($con,"
        SELECT
            e.genre,
            c.cycle
        FROM eleve e
        LEFT JOIN classe c ON c.id=e.classe
        WHERE e.menage=".$row['id']."
    ");

    while($el=mysqli_fetch_assoc($res)){

        if(strtoupper($el['genre'])=='M'){
            $actifGarcons++;
        }else{
            $actifFilles++;
        }

        if(!isset($actifCycles[$el['cycle']])){
            $actifCycles[$el['cycle']] = 0;
        }

        $actifCycles[$el['cycle']]++;
    }
}

/* =====================================================
   STATISTIQUES FAMILLES INACTIVES
===================================================== */

foreach ($dataInactifs as $row) {

    $inactifScolaireAPayer += $row['scolaire_a_payer'];
    $inactifScolairePaye += $row['scolaire_paye'];
    $inactifScolaireReste += $row['scolaire_reste'];

    $inactifConnexeAPayer += $row['connexe_a_payer'];
    $inactifConnexePaye += $row['connexe_paye'];
    $inactifConnexeReste += $row['connexe_reste'];

    $res = mysqli_query($con,"
        SELECT
            e.genre,
            c.cycle
        FROM eleve e
        LEFT JOIN classe c ON c.id=e.classe
        WHERE e.menage=".$row['id']."
    ");

    while($el=mysqli_fetch_assoc($res)){

        if(strtoupper($el['genre'])=='M'){
            $inactifGarcons++;
        }else{
            $inactifFilles++;
        }

        if(!isset($inactifCycles[$el['cycle']])){
            $inactifCycles[$el['cycle']] = 0;
        }

        $inactifCycles[$el['cycle']]++;
    }
}

/* =====================================================
   STATISTIQUES PERSONNEL
===================================================== */

foreach ($dataPersonnel as $row) {

    $personnelScolaireAPayer += $row['scolaire_a_payer'];
    $personnelScolairePaye += $row['scolaire_paye'];
    $personnelScolaireReste += $row['scolaire_reste'];

    $personnelConnexeAPayer += $row['connexe_a_payer'];
    $personnelConnexePaye += $row['connexe_paye'];
    $personnelConnexeReste += $row['connexe_reste'];

    $res = mysqli_query($con,"
        SELECT
            e.genre,
            c.cycle
        FROM eleve e
        LEFT JOIN classe c ON c.id=e.classe
        WHERE e.menage=".$row['id']."
    ");

    while($el=mysqli_fetch_assoc($res)){

        if(strtoupper($el['genre'])=='M'){
            $personnelGarcons++;
        }else{
            $personnelFilles++;
        }

        if(!isset($personnelCycles[$el['cycle']])){
            $personnelCycles[$el['cycle']] = 0;
        }

        $personnelCycles[$el['cycle']]++;
    }
}

/* =====================================================
   TOTAUX GLOBAUX
===================================================== */

$globalFamilles =
    $actifFamilles +
    $inactifFamilles +
    $personnelFamilles;

$globalGarcons =
    $actifGarcons +
    $inactifGarcons +
    $personnelGarcons;

$globalFilles =
    $actifFilles +
    $inactifFilles +
    $personnelFilles;

$globalScolaireAPayer =
    $actifScolaireAPayer +
    $inactifScolaireAPayer +
    $personnelScolaireAPayer;

$globalScolairePaye =
    $actifScolairePaye +
    $inactifScolairePaye +
    $personnelScolairePaye;

$globalScolaireReste =
    $actifScolaireReste +
    $inactifScolaireReste +
    $personnelScolaireReste;

$globalConnexeAPayer =
    $actifConnexeAPayer +
    $inactifConnexeAPayer +
    $personnelConnexeAPayer;

$globalConnexePaye =
    $actifConnexePaye +
    $inactifConnexePaye +
    $personnelConnexePaye;

$globalConnexeReste =
    $actifConnexeReste +
    $inactifConnexeReste +
    $personnelConnexeReste;

foreach($cycles as $cycle){

    $cid = $cycle['id'];

    $globalCycles[$cid] =
        ($actifCycles[$cid] ?? 0)
        + ($inactifCycles[$cid] ?? 0)
        + ($personnelCycles[$cid] ?? 0);
}

?>

<div class="main-panel">
    <div class="content-wrapper">

        <div class="card mb-3">

            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">

                    <div>
                        <button onclick="history.back()" class="btn btn-dark">
                            Retour
                        </button>

                        <a href="export_csv.php?export=csv" class="btn btn-success">
                            Export l'inventaire en fichier Excel
                        </a>

                    </div>

                </div>
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

                                    <?php foreach($cycles as $cycle): ?>
                                    <th><?= $cycle['description'] ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>

                            <tbody>

                                <!-- GLOBAL -->
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

                                    <?php foreach($cycles as $cycle): ?>
                                    <td><?= $globalCycles[$cycle['id']] ?? 0 ?></td>
                                    <?php endforeach; ?>
                                </tr>

                                <!-- ACTIFS -->
                                <tr class="table-success">
                                    <td>Familles Actives</td>
                                    <td><?= $actifFamilles ?></td>
                                    <td><?= $actifGarcons ?></td>
                                    <td><?= $actifFilles ?></td>

                                    <td><?= money($actifScolaireAPayer) ?></td>
                                    <td><?= money($actifScolairePaye) ?></td>
                                    <td><?= money($actifScolaireReste) ?></td>

                                    <td><?= money($actifConnexeAPayer) ?></td>
                                    <td><?= money($actifConnexePaye) ?></td>
                                    <td><?= money($actifConnexeReste) ?></td>

                                    <?php foreach($cycles as $cycle): ?>
                                    <td><?= $actifCycles[$cycle['id']] ?? 0 ?></td>
                                    <?php endforeach; ?>
                                </tr>

                                <!-- INACTIFS -->
                                <tr class="table-warning">
                                    <td>Familles Inactives</td>
                                    <td><?= $inactifFamilles ?></td>
                                    <td><?= $inactifGarcons ?></td>
                                    <td><?= $inactifFilles ?></td>

                                    <td><?= money($inactifScolaireAPayer) ?></td>
                                    <td><?= money($inactifScolairePaye) ?></td>
                                    <td><?= money($inactifScolaireReste) ?></td>

                                    <td><?= money($inactifConnexeAPayer) ?></td>
                                    <td><?= money($inactifConnexePaye) ?></td>
                                    <td><?= money($inactifConnexeReste) ?></td>

                                    <?php foreach($cycles as $cycle): ?>
                                    <td><?= $inactifCycles[$cycle['id']] ?? 0 ?></td>
                                    <?php endforeach; ?>
                                </tr>

                                <!-- PERSONNEL -->
                                <tr class="table-secondary">
                                    <td>Personnel</td>
                                    <td><?= $personnelFamilles ?></td>
                                    <td><?= $personnelGarcons ?></td>
                                    <td><?= $personnelFilles ?></td>

                                    <td><?= money($personnelScolaireAPayer) ?></td>
                                    <td><?= money($personnelScolairePaye) ?></td>
                                    <td><?= money($personnelScolaireReste) ?></td>

                                    <td><?= money($personnelConnexeAPayer) ?></td>
                                    <td><?= money($personnelConnexePaye) ?></td>
                                    <td><?= money($personnelConnexeReste) ?></td>

                                    <?php foreach($cycles as $cycle): ?>
                                    <td><?= $personnelCycles[$cycle['id']] ?? 0 ?></td>
                                    <?php endforeach; ?>
                                </tr>

                            </tbody>

                        </table>

                    </div>
                </div>

                <div class="table-responsive">
                    <h3 class="mb-3 text-uppercase">
                        Situation des Familles Actives
                    </h3>
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
                            <?php
                                $totalActifScolaireAPayer = 0;
                                $totalActifScolairePaye = 0;
                                $totalActifScolaireReste = 0;

                                $totalActifConnexeAPayer = 0;
                                $totalActifConnexePaye = 0;
                                $totalActifConnexeReste = 0;
                            ?>
                            <?php foreach($dataActifs as $row): ?>
                            <?php
                                    $totalActifScolaireAPayer += $row['scolaire_a_payer'];
                                    $totalActifScolairePaye += $row['scolaire_paye'];
                                    $totalActifScolaireReste += $row['scolaire_reste'];

                                    $totalActifConnexeAPayer += $row['connexe_a_payer'];
                                    $totalActifConnexePaye += $row['connexe_paye'];
                                    $totalActifConnexeReste += $row['connexe_reste'];
                                ?>
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

                                <th><?= money($totalActifScolaireAPayer) ?></th>

                                <th><?= money($totalActifScolairePaye) ?></th>

                                <th><?= money($totalActifScolaireReste) ?></th>

                                <th><?= money($totalActifConnexeAPayer) ?></th>

                                <th><?= money($totalActifConnexePaye) ?></th>

                                <th><?= money($totalActifConnexeReste) ?></th>

                            </tr>

                        </tfoot>
                    </table>

                    <!-- famille inactives -->
                    <hr>

                    <h3 class="mb-3 text-uppercase">
                        Situation des Familles Inactives
                    </h3>

                    <div class="table-responsive">

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

                                <?php
                                    $totalInactifScolaireAPayer = 0;
                                    $totalInactifScolairePaye = 0;
                                    $totalInactifScolaireReste = 0;

                                    $totalInactifConnexeAPayer = 0;
                                    $totalInactifConnexePaye = 0;
                                    $totalInactifConnexeReste = 0;
                                ?>
                                <?php foreach($dataInactifs as $row): ?>
                                <?php
                                        $totalInactifScolaireAPayer += $row['scolaire_a_payer'];
                                        $totalInactifScolairePaye += $row['scolaire_paye'];
                                        $totalInactifScolaireReste += $row['scolaire_reste'];

                                        $totalInactifConnexeAPayer += $row['connexe_a_payer'];
                                        $totalInactifConnexePaye += $row['connexe_paye'];
                                        $totalInactifConnexeReste += $row['connexe_reste'];
                                    ?>

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

                                    <th><?= money($totalInactifScolaireAPayer) ?></th>
                                    <th><?= money($totalInactifScolairePaye) ?></th>
                                    <th><?= money($totalInactifScolaireReste) ?></th>

                                    <th><?= money($totalInactifConnexeAPayer) ?></th>
                                    <th><?= money($totalInactifConnexePaye) ?></th>
                                    <th><?= money($totalInactifConnexeReste) ?></th>

                                </tr>

                            </tfoot>
                        </table>

                        <!-- familles des personnelles -->
                        <hr>

                        <h3 class="mb-3 text-uppercase">
                            Situation des Personnels
                        </h3>

                        <div class="table-responsive">

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
                                    <?php
                                        $totalPersonnelScolaireAPayer = 0;
                                        $totalPersonnelScolairePaye = 0;
                                        $totalPersonnelScolaireReste = 0;

                                        $totalPersonnelConnexeAPayer = 0;
                                        $totalPersonnelConnexePaye = 0;
                                        $totalPersonnelConnexeReste = 0;
                                    ?>
                                    <?php foreach($dataPersonnel as $row): ?>
                                    <?php
                                            $totalPersonnelScolaireAPayer += $row['scolaire_a_payer'];
                                            $totalPersonnelScolairePaye += $row['scolaire_paye'];
                                            $totalPersonnelScolaireReste += $row['scolaire_reste'];

                                            $totalPersonnelConnexeAPayer += $row['connexe_a_payer'];
                                            $totalPersonnelConnexePaye += $row['connexe_paye'];
                                            $totalPersonnelConnexeReste += $row['connexe_reste'];
                                        ?>
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

                                        <th><?= money($totalPersonnelScolaireAPayer) ?></th>
                                        <th><?= money($totalPersonnelScolairePaye) ?></th>
                                        <th><?= money($totalPersonnelScolaireReste) ?></th>

                                        <th><?= money($totalPersonnelConnexeAPayer) ?></th>
                                        <th><?= money($totalPersonnelConnexePaye) ?></th>
                                        <th><?= money($totalPersonnelConnexeReste) ?></th>

                                    </tr>

                                </tfoot>
                            </table>

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
<?php require_once('../../layouts/constants/footer.php'); ?>