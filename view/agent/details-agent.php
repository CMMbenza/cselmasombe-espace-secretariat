<?php 
    require_once ('../../layouts/constants/head.php'); 
    require_once ('../../webapp/service/agent.service.create.update.php');
    require_once ('../../webapp/service/fonction.service.php');
    require_once ('../../webapp/service/grade.service.php');
    require_once ('../../layouts/navbar/navbar.php');
 ?>

<div class="main-panel-copy">
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

            <div class="col-lg-10 col-sm-12 grid-margin">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" role="form">

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4"><button class="btn btn-primary btn-block enter-btn me-2" name=""
                                    id=""><span class="menu-icon">
                                        <i class="mdi mdi-speedometer"></i>
                                    </span></button>Agent</h4>

                            <div class="row mb-3">
                                <div class="form-group">
                                    <label for="exampleInputLibelle" class="form-label">ID</label>
                                    <input type="text" class="form-control" name="id" id="id" placeholder="ID">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-sm-4">
                                    <div class="form-group">
                                        <label for="exampleInputLibelle" class="form-label">Nom</label>
                                        <input type="text" class="form-control" name="nom" id="nom"
                                            placeholder="Entrer nom agent">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-4">
                                    <div class="form-group">
                                        <label for="exampleInputLibelle" class="form-label">Postnom</label>
                                        <input type="text" class="form-control" name="postnom" id="postnom"
                                            placeholder="Entrer postnom">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-4">
                                    <div class="form-group">
                                        <label for="exampleInputLibelle" class="form-label">Prénom</label>
                                        <input type="text" class="form-control" name="prenom" id="prenom"
                                            placeholder="Entrer prénom">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <label for="exampleInputLibelle" class="form-label">Genre</label>
                                    <select class="form-control" name="genre" id="genre">
                                        <option value="null" selected disabled>Sélectionner le genre</option>
                                        <option value="M">M</option>
                                        <option value="F">F</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-sm-4">
                                    <div class="form-group">
                                        <label for="exampleInputLibelle" class="form-label">Lieu</label>
                                        <input type="text" class="form-control" name="lieu" id="lieu"
                                            placeholder="Lieu de naissance">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-4">
                                    <div class="form-group">
                                        <label for="exampleInputLibelle" class="form-label">Date de naissance</label>
                                        <input type="date" class="form-control" name="dateDeNaissance"
                                            id="dateDeNaissance" placeholder="Date de naissance">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="exampleInputLibelle" class="form-label">Nieau d'étude</label>
                                        <select class="form-control" name="niveau" id="niveau">
                                            <option value="null" selected disabled>Niveau d'étude</option>
                                            <option value="D6">D6</option>
                                            <option value="BAC + 3">BAC + 3</option>
                                            <option value="BAC + 4">BAC + 4</option>
                                            <option value="BAC + 5">BAC + 5</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-lg-6">
                                    <label for="exampleInputCycle" class="form-label">Grade agent</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-control" name="grade" id="grade">
                                            <option value="#" selected disabled>Sélectionner la grade</option>
                                            <?php while ($row = $rst->fetch_assoc()) {
                                                            ?>
                                            <option value="<?php echo $row['id'];?>">
                                                <?php echo $row['description'];?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                        <!-- <span class="input-group-append ms-2">
                                                    <a href="../cycle/create-update.php"
                                                        class="file-upload-browse btn btn-primary"
                                                        type="button">Ajouter</a>
                                                </span> -->
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label for="exampleInputCycle" class="form-label">Fonction agent</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-control" name="fonction" id="fonction">
                                            <option value="#" selected disabled>Sélectionner la fonction</option>
                                            <?php while ($row = $rstFonction->fetch_assoc()) {
                                                            ?>
                                            <option value="<?php echo $row['id'];?>">
                                                <?php echo $row['description'];?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                        <!-- <span class="input-group-append ms-2">
                                                    <a href="../cycle/create-update.php"
                                                        class="file-upload-browse btn btn-primary"
                                                        type="button">Ajouter</a>
                                                </span> -->
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="form-group">
                                    <label for="exampleInputLibelle" class="form-label">Salaire de base</label>
                                    <input type="number" class="form-control" name="salaire" id="salaire"
                                        placeholder="0.00">
                                </div>
                            </div>

                            <button class="btn btn-success btn-block enter-btn" name="submit"
                                id="submit">Sauvegarder</button>
                            <button type="reset" class="btn btn-dark btn-block enter-btn" name="" id="">Annuler</button>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->

    <?php require_once ('../../layouts/constants/footer.php'); ?>