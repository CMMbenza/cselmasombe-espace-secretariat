<?php 
require_once ('../../layouts/constants/head.php');
require_once ('../../layouts/navbar/navbar.php');

require_once('../../webapp/database/config.php');

  if (isset($_GET['id'])) {

    $codeEleve = $_GET['id']; 

    $sql = "SELECT el.id, el.nom, el.postnom, el.prenom, el.genre, el.lieu, el.dateDeNaissance, el.dateCreated, el.classe, el.dateUpdate, el.anneeScolaire, el.createdby, men.id AS id_menage, men.noms AS menage, cla.description AS classe, cy.description AS cycle FROM menage men, classe cla, cycle cy JOIN eleve el WHERE el.menage = men.id AND el.classe = cla.id AND cla.cycle = cy.id AND el.id ='$codeEleve'";

      $result = mysqli_query($con, $sql);     

        while ($row = $result->fetch_assoc()) {

            $nom = $row['nom'];
            $postnom = $row['postnom'];
            $prenom = $row['prenom'];
            $genre = $row['genre'];
            $lieu = $row['lieu'];
            $dateDeNaissance = $row['dateDeNaissance'];
            $classe = $row['classe'];
            $menage = $row['menage'];
            $id_menage = $row['id_menage'];
            $dateCreated = $row['dateCreated'];
            $dateUpdate = $row['dateUpdate'];
            $anneeScolaire = $row['anneeScolaire'];
            $createdby = $row['createdby']; 
      } 
?>



<style>
dt {
    font-weight: 500;
}
</style>

<body>
    <div class="main-panel-copy">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12 col-sm-12 grid-margin">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase"><span class="me-2"><button type="reset" class="btn btn-primary"
                                    onclick="history.back()"></span>
                                    < Retour </button>Détails de l'élève</h5>
                            <hr class="">
                            <dl class="row-md jh-entity-details">
                                <dt><span jhiTranslate="gestionnaireApp.produit.categorie">Noms</span>
                                </dt>
                                <dd>
                                    <span>
                                        <?php echo $nom;?>
                                        <?php echo $postnom;?>
                                        <?php echo $prenom;?>,
                                        <?php echo $genre;?>
                                    </span>
                                </dd>
                                <dt><span jhiTranslate="gestionnaireApp.produit.categorie">Du menage
                                        (famille)</span>
                                </dt>
                                <dd>
                                    <span>
                                         <a href="../../view/menage/detail_menage.php?id=<?php echo $id_menage;?>"><?php echo $menage;?></a> 
                                    </span>
                                </dd>
                                <dt><span jhiTranslate="gestionnaireApp.produit.categorie">Classe encours</span>
                                </dt>
                                <dd>
                                    <span>
                                        <?php echo $classe;?>
                                    </span>
                                </dd>
                                <dt><span jhiTranslate="gestionnaireApp.produit.categorie">Lieu et date de
                                        naissance</span>
                                </dt>
                                <dd>
                                    <span>
                                        <?php echo $lieu;?>,
                                        <?php echo $dateDeNaissance;?>
                                    </span>
                                </dd>

                                <div class="d-flex">
                                    <div class="cols me-2">
                                        <dt><span jhiTranslate="gestionnaireApp.produit.categorie">Date
                                                inscription</span>
                                        </dt>
                                        <dd>
                                            <span>
                                                <?php echo $dateCreated;?>
                                            </span>
                                        </dd>
                                    </div>
                                    <div class="cols">
                                        <dt><span jhiTranslate="gestionnaireApp.produit.categorie">Date
                                                Modification</span>
                                        </dt>
                                        <dd>
                                            <span>
                                                <?php echo $dateUpdate;?>
                                            </span>
                                        </dd>
                                    </div>
                                </div>
                                <dt><span jhiTranslate="gestionnaireApp.produit.categorie">Année scolaire</span>
                                </dt>
                                <dd>
                                    <span>
                                        <?php echo $anneeScolaire;?>
                                    </span>
                                </dd>
                                <dt><span jhiTranslate="gestionnaireApp.produit.categorie">Créer par </span>
                                </dt>
                                <dd>
                                    <span>
                                        <?php echo $createdby;?>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <?php  }   ?>
            </div>

            <!-- Paiements de l’élève -->
            <div class="d-none row mt-4">
                <div class="col-lg-12 grid-margin">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Détail des paiements de l'élève</h5>
                            <hr>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tranche</th>
                                            <th>Montant attendu</th>
                                            <th class="text-primary">Montant payé</th>
                                            <th class="text-danger">Reste</th>
                                            <th>Date de paiement</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                            $sql = "SELECT 
                                        t.numero_tranche,
                                        t.montant,
                                        pd.montant AS montant_paye,
                                        p.dateCreated
                                    FROM paiement_detail pd
                                    JOIN tranche t ON pd.tranche_id = t.id
                                    JOIN paiement p ON pd.paiement_id = p.id
                                    WHERE pd.eleve_id = '$codeEleve'
                                    ORDER BY t.numero_tranche ASC";
                            
                            $rsts = mysqli_query($con, $sql);
                            if ($rsts->num_rows > 0):
                                while ($row = $rsts->fetch_assoc()):
                                    $reste = $row['montant'] - $row['montant_paye'];
                            ?>
                                        <tr>
                                            <td>Tranche <?= $row['numero_tranche'] ?></td>
                                            <td><?= number_format($row['montant'], 2) ?> $</td>
                                            <td class="text-primary"><?= number_format($row['montant_paye'], 2) ?> $
                                            </td>
                                            <td class="text-danger"><?= number_format($reste, 2) ?> $</td>
                                            <td><?= $row['dateCreated'] ?></td>
                                        </tr>
                                        <?php endwhile; else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Aucun paiement enregistré
                                                pour
                                                cet
                                                élève.</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</body>
<?php require_once ('../../layouts/constants/footer.php'); ?>