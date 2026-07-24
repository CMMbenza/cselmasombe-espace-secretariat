<?php 
require_once ('../../layouts/constants/head.php'); 
require_once ('../../webapp/service/annee_scolaire.service.php'); 
require_once ('../../layouts/navbar/navbar.php');

?>

<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title"><span class="menu-icon">
                                <i class="fa fa-user-circle"></i>
                            </span>Année scolaire</h3>
                        <!-- <h4 class="card-title">Order Status</h4> -->
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" role="form">

                            <div class="d-flex mt-4 mb-4">
                                <div class="form-group m-0 me-3">
                                    <label for="exampleInputAnneeScolaire" class="form-label">Année
                                        Scolaire</label>
                                    <input type="text" class="form-control" name="anneeScolaire" id="anneeScolaire"
                                        placeholder="$annee_scolaire">
                                </div>
                                <div class="form-group m-0 me-3">
                                    <label for="exampleInputDebut" class="form-label">Début de l'année</label>
                                    <input type="date" class="form-control" name="debut" id="debut" placeholder=" ">
                                </div>
                                <div class="form-group m-0 me-3">
                                    <label for="exampleInputFin" class="form-label">Fin de l'année</label>
                                    <input type="date" class="form-control" name="fin" id="fin" placeholder=" ">
                                </div>
                                <div class="form-group m-0 me-3">
                                    <label for="exampleInputFin" class="form-label"> </label> <br>
                                    <button name="submit" id="submit" class="btn btn-primary">Sauvegarder</button>
                                </div>
                            </div>
                        </form>


                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th> Année scolaire </th>
                                        <th> Début de l'année </th>
                                        <th> Fin de l'année </th>
                                        <th> Status </th>
                                        <th> </th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php if ($rst->num_rows <= 0) { ?>

                                    <tr>
                                        <td colspan="5">
                                            <div class="alert alert-danger">
                                                Liste vide
                                            </div>
                                        </td>
                                    </tr>

                                    <?php } else { ?>

                                    <?php while ($row = $rst->fetch_assoc()) { ?>

                                    <tr>

                                        <td class="<?= ($row['status'] == 'encours') ? '' : 'text-danger' ?>">
                                            <?= $row['annee_scolaire']; ?>
                                        </td>

                                        <td class="<?= ($row['status'] == 'encours') ? '' : 'text-danger' ?>">
                                            <?= $row['dateDebut']; ?>
                                        </td>

                                        <td class="<?= ($row['status'] == 'encours') ? '' : 'text-danger' ?>">
                                            <?= $row['dateFin']; ?>
                                        </td>

                                        <td
                                            class="<?= ($row['status'] == 'encours') ? 'text-primary' : 'text-danger' ?>">
                                            <?= $row['status']; ?>
                                        </td>


                                        <td>

                                            <?php if ($row['status'] == 'encours') { ?>

                                            <a href="cloturer_annee.php?id=<?= $row['id']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Voulez-vous vraiment clôturer cette année scolaire ? Les données seront archivées.')">

                                                <i class="fa fa-lock"></i>
                                                Clôturer

                                            </a>

                                            <?php } else { ?>

                                            <span class="badge bg-dark">
                                                Fermée
                                            </span>

                                            <a href="ouvrir_consulter_annee.php?id=<?= $row['id'] ?>"
                                                class="btn btn-sm <?= $row['status'] === 'encours' ? 'btn-success' : 'btn-primary' ?>">
                                                <?php if ($row['status'] === 'encours'): ?>
                                                <i class="fas fa-folder-open me-1"></i> Gérer la session
                                                <?php else: ?>
                                                <i class="fas fa-eye me-1"></i> Consulter l'archive
                                                <?php endif; ?>
                                            </a>

                                            <?php } ?>

                                        </td>
                                    </tr>

                                    <?php } ?>

                                    <?php } ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->

    <?php require_once ('../../layouts/constants/footer.php'); ?>