<?php require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/fixation.service.php');
require_once ('../../layouts/navbar/navbar.php');
?>

<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title mb-3"><span class="menu-icon">
                                <i class="fa fa-user-circle"></i>
                            </span>Fixation des frais</h3>
                        <!-- <h6 class="wrapper-filtrage">Filtrage des données :</h6> -->
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="wrapper-entity">
                                <a href="create-update.php" class="btn btn-success btn-block enter-btn">Nouveau
                                    frais</a>
                            </div>
                            <!-- <input type="text" class="form-control p_input" id="noms"
                                placeholder="Entrez la description du frais" onkeyup="myFunctionSearch()"> -->

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
                                        <th> ID FF </th>
                                        <th> Description </th>
                                        <th> Cycle </th>
                                        <th> Montant fixé </th>
                                        <th> </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($rst->num_rows <= 0) {                                                    ?>
                                    <div class="alert alert-danger">List vide</div>
                                    <?php } else {
                                        while ($row = $rst->fetch_assoc()) {
                                        ?>
                                    <tr>
                                        <td>
                                            <?php echo $row['id'];?>
                                        </td>
                                        <td> <?php echo $row['description'];?> </td>
                                        <td> <?php echo $row['cycle'];?> </td>
                                        <td> <?php echo $row['montant'];?>$</td>
                                        <td>
                                            <a href="detail_fixation.php?cycle=<?php echo $row['cycle_id']; ?>" class="btn btn-primary">Voir</a>
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
</div>

<?php require_once ('../../layouts/constants/footer.php'); ?>