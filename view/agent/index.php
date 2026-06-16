<?php 
require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/agent.service.php'); 
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
                            </span>Agent</h3>
                        <h6 class="wrapper-filtrage">Filtrage des données :</h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="wrapper-entity">
                                <a href="create-update.php" class="btn btn-success btn-block enter-btn">Nouveau</a>
                            </div>
                            <a class="btn border me-2" href="../prestation/">Prestation</a> 
                            <a class="btn border me-2" href="../fonction/">Fonction</a> 
                            <a class="btn border me-2" href="../grade/">Grade</a>
                            <input type="text" class="form-control p_input" id="noms"
                                placeholder="Entrez le nom de l'agent" onkeyup="myFunctionSearch()">

                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Order Status</h4>
                        <div class="table-responsive">
                            <table class="table" id="myTable">
                                <thead>
                                    <tr>
                                        <!-- <th>
                                                        <div class="form-check form-check-muted m-0">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input"
                                                                    id="check-all">
                                                            </label>
                                                        </div>
                                                    </th> -->
                                        <th> ID </th>
                                        <th> Noms </th>
                                        <th> Genre </th>
                                        <th> Lieu et date de naissance </th>
                                        <th> Grade </th>
                                        <th> Fonction </th>
                                        <th> Niveau d'étude </th>
                                        <th> Créer le </th>
                                        <th> </th>
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
                                                        <div class="form-check form-check-muted m-0">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input">
                                                            </label>
                                                        </div>
                                                    </td> -->
                                        <td>
                                            <?php echo $row['id'];?>
                                        </td>
                                        <td> <?php echo $row['nom'];?> <?php echo $row['postnom'];?>
                                            <?php echo $row['prenom'];?> </td>
                                        <td> <?php echo $row['genre'];?> </td>
                                        <td> <?php echo $row['lieu'];?>, le <?php echo $row['dateDeNaissance'];?> </td>
                                        <td> <?php echo $row['grade'];?></td>
                                        <td> <?php echo $row['fonction'];?> </td>
                                        <td> <?php echo $row['niveau_d_etude'];?> </td>
                                        <td class="text-success"> <?php echo $row['dateCreated'];?> </td>
                                        <!-- <td> 04 Dec 2019 </td> -->
                                        <td>
                                            <!-- <button class="btn btn-primary">Modifier</button>
                                                        <button class="btn btn-danger">Supprimer</button> -->
                                            <button class="btn btn-primary"><span class="menu-icon">
                                                    <i class="mdi mdi-eye"></i>
                                                </span></button>
                                        </td>
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