<?php require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/cycle.service.php');  
require_once ('../../layouts/navbar/navbar.php');
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title mb-3"><span class="menu-icon">
                                <i class="fa fa-user-circle"></i>
                            </span>Cycle</h3>
                        <!-- <h6 class="wrapper-filtrage">Filtrage des données :</h6> -->
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="wrapper-entity">
                                <a href="create-update.php" class="btn btn-success btn-block enter-btn">Nouveau cycle
                                </a>
                            </div>
                            <a class="btn border me-2" href="../classe/">Gestion des classes</a>
                            <!-- <input type="text" class="form-control p_input" placeholder="Entrez cycle"> -->

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
                            <table class="table">
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
                                        <th> ID Cycle </th>
                                        <th> Description </th>
                                        <th> Création </th>
                                        <th> Modification </th>
                                        <th> </th>
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
                                        <td> <?php echo $row['description'];?> </td>
                                        <td> <?php echo $row['dateCreaty'];?> </td>
                                        <td> <?php echo $row['dateUpdate'];?> </td>
                                        <td class="text-success"> <?php echo $row['createby'];?> </td>
                                        <!-- <td> 04 Dec 2019 </td> -->
                                        <td>
                                            <!-- <button class="btn btn-primary">Modifier</button>
                                                        <button class="btn btn-danger">Supprimer</button> -->
                                            <button class="btn btn-primary">Voir</button>
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