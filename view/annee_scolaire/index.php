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
                                        <!-- <th> # </th> -->
                                        <th> Année scolaire </th>
                                        <th> Début de l'année </th>
                                        <th> Fin de l'année </th>
                                        <th> Status </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($rst->num_rows <= 0) {
                                                    ?>
                                    <div class="alert alert-danger">List vide</div>
                                    <?php } else {
                                                        while ($row = $rst->fetch_assoc()) {
                                                            ?>
                                    <tr>
                                        <!-- <td>
                                                        <?php echo $row['id'];?>
                                                    </td> -->
                                        <?php if ($row['status'] == 'Encours') {
                                                      ?>
                                        <div class="">
                                            <td>
                                                <?php echo $row['annee_scolaire'];?>
                                            </td>
                                            <td>
                                                <?php echo $row['dateDebut'];?>
                                            </td>
                                            <td>
                                                <?php echo $row['dateFin'];?>
                                            </td>
                                            <td class="text-success">
                                                <?php echo $row['status'];?>
                                        </div>
                                        <?php
                                                    } else { ?>
                                        <div style="color:red !important">
                                            <td class="text-danger">
                                                <?php echo $row['annee_scolaire'];?>
                                            </td>
                                            <td class="text-danger">
                                                <?php echo $row['dateDebut'];?>
                                            </td>
                                            <td class="text-danger">
                                                <?php echo $row['dateFin'];?>
                                            </td>
                                            <td class="text-danger">
                                                <?php echo $row['status'];?>
                                        </div>
                                        <?php } ?>


                                        </td>
                                        <!-- <td>
                                                        <button class="btn btn-primary">Modifier</button>
                                                        <button class="btn btn-danger">Supprimer</button>
                                                        <button class="btn btn-primary">Voir</button>
                                                    </td> -->
                                    </tr>
                                    <?php }} ?>
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