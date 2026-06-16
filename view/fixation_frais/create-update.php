<?php require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/cycle.service.php'); 
require_once ('../../webapp/service/fixation.service.create.update.php');
require_once ('../../layouts/navbar/navbar.php');
?>

<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-7 col-sm-12 grid-margin">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" role="form">

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4"><button class="btn btn-primary btn-block enter-btn me-2" name=""
                                    id=""><span class="menu-icon">
                                        <i class="mdi mdi-speedometer"></i>
                                    </span></button>Gestion de fixation des frais</h4>

                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <label for="exampleInputCycle" class="form-label">Cycle</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-control" name="cycle" id="cycle">
                                            <option value="#" disabled selected>Sélectionnez le cyle</option>
                                            <?php while ($row = $rst->fetch_assoc()) {
                                                            ?>
                                            <option value="<?php echo $row['id'];?>">
                                                <?php echo $row['description'];?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                        <span class="input-group-append ms-2">
                                            <a href="../cycle/create-update.php"
                                                class="file-upload-browse btn btn-primary" type="button">Ajouter</a>
                                        </span>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="exampleInputLibelle" class="form-label">Type de frais
                                            scolaire</label>
                                        <select class="form-control" name="description" id="description">
                                            <option disabled selected>Sélection le type de frais</option>
                                            <option value="Frais scolaire">Frais scolaire</option>
                                            <option value="Frais connexe">Frais connexe</option>
                                            <option value="Autre...">Autre...</option>
                                        </select>
                                        <!-- <input type="text" class="form-control" name="description" id="description"
                                            placeholder="Entrez la description du frais"> -->
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="exampleInputLibelle" class="form-label">Montant $</label>
                                        <input type="number" class="form-control" name="montant" id="montant"
                                            placeholder="Entrez le montant du frais">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-5 d-none">
                                <div class="col">
                                    <label for="exampleInputLibelle" class="form-label">1e trimestre</label>
                                    <input type="number" class="form-control" name="premierTrimestre"
                                        id="premierTrimestre" placeholder="Premier trimestre" value="0">
                                </div>
                                <div class="col">
                                    <label for="exampleInputLibelle" class="form-label">2e Trimestre</label>
                                    <input type="number" class="form-control" name="deuxiemeTrimestre"
                                        id="deuxiemeTrimestre" placeholder="Entrez le montant" value="0">
                                </div>
                                <div class="col">
                                    <label for="exampleInputLibelle" class="form-label">3e Trimestre</label>
                                    <input type="number" class="form-control" name="troisiemeTrimestre"
                                        id="troisiemeTrimestre" placeholder="Entrez le montant" value="0">
                                </div>
                            </div>
                            <button class="btn btn-success btn-block enter-btn" name="submit"
                                id="submit">Sauvegarder</button>
                            <button type="reset" class="btn btn-secondary btn-block enter-btn" name=""
                                id="">Annuler</button>
                        </div>
                    </div>
            </div>
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