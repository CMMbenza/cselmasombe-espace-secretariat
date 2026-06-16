<?php require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/cycle.service.create.update.php'); 
require_once ('../../layouts/navbar/navbar.php');
 ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <!-- <div class="col-12 grid-margin">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title"><span class="menu-icon">
                                            <i class="mdi mdi-speedometer"></i>
                                        </span>Elève</h3>
                                    <h6 class="wrapper-filtrage">Filtrage des données :</h6>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="wrapper-entity">
                                            <a href="create-update.php" class="btn btn-success btn-block enter-btn">Nouveau élève</a>
                                        </div>
                                        <input type="text" class="form-control p_input" placeholder="Entrez nom élève">

                                    </div>

                                </div>
                            </div>
                        </div> -->
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" role="form">
                <div class="col-lg-7 col-sm-12 grid-margin">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4"><button class="btn btn-primary btn-block enter-btn me-2" name=""
                                    id=""><span class="menu-icon">
                                        <i class="mdi mdi-speedometer"></i>
                                    </span></button>Gestion des cycle</h4>

                            <!-- <div class="row">
                                        <div class="form-group col-lg-12">
                                            <label for="exampleInputCycle" class="form-label">Cycle</label>
                                            <div class="d-flex align-items-center">
                                                <select class="form-control" name="" id="">
                                                    <option value="#" disabled selected>Sélectionnez le cyle</option>
                                                    <option value="M">M</option>
                                                    <option value="F">F</option>
                                                </select>
                                                <span class="input-group-append ms-2">
                                                    <a href="../classe/index.php"
                                                        class="file-upload-browse btn btn-primary"
                                                        type="button">Ajouter</a>
                                                </span>
                                            </div>

                                        </div>
                                    </div> -->
                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="exampleInputDescription" class="form-label">Description</label>
                                        <input type="text" class="form-control" name="description" id="description"
                                            placeholder="Entrez le Description">
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-success btn-block enter-btn" name="submit"
                                id="submit">Sauvegarder</button>
                            <button type="reset" class="btn btn-secondary btn-block enter-btn" name=""
                                id="">Annuler</button>
                        </div>
                    </div>
                </div>
            </form>
            <!-- <div class="col-lg-5 col-sm-12 grid-margin">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Autres informations</h4>
                                    <div class="row">
                                        <div class="col-lg-12 col-sm-12">
                                            <div class="form-group">
                                                <label for="exampleInputCycle" class="form-label">Cycle</label>
                                                <input type="text" class="form-control" id="exampleInputCycle"
                                                    placeholder="Entrez le cycle">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
        </div>

    </div>
    <!-- content-wrapper ends -->

    <?php require_once ('../../layouts/constants/footer.php'); ?>