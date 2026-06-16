<?php require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/eleve.service.create.update.php'); 
require_once ('../../webapp/service/classe.service.php');  
require_once ('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/dbcongig.php'); // expose $conn
require_once('../../webapp/service/annee_scolaire.encours.php');

// ====== Mode Edition ? ======
$isEdit = false;
$eleveEdit = null;

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $isEdit = true;
    $editId = (int)$_GET['id'];
    // Récupération de l'élève pour pré-remplir
    $sqlEdit = "SELECT e.*, 
                       m.noms AS menage_noms, m.montantAPayer AS menage_montant, 
                       c.description AS classe_desc
                  FROM eleve e
             LEFT JOIN menage m ON m.id = e.menage
             LEFT JOIN classe c ON c.id = e.classe
                 WHERE e.id = ?";
    $stmt = $conn->prepare($sqlEdit);
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $eleveEdit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ====== Menages (familles) ======
$sql = "SELECT id, noms, montantAPayer FROM menage WHERE anneeScolaire = ? ORDER BY noms";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $annee_scolaire);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) $products[] = $row;
$stmt->close();

// ====== Classes & frais scolarité ======
$sql = "SELECT cls.id AS idClasse, cls.description AS description, cy.id AS cycleId, cy.description as descriptionCycle, sc.montant AS montantPayer
          FROM classe cls 
          JOIN cycle cy ON cls.cycle = cy.id
          JOIN scolarite sc ON cy.id = sc.cycle
      ORDER BY cls.description ASC";
$rstClass = $conn->query($sql);
$row_classe = [];
while ($row = $rstClass->fetch_assoc()) $row_classe[] = $row;

$conn->close();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// --- Changer le montant ménage et frais scolarité selon sélections ---
$(document).ready(function() {
    $('#select_product').on('change', function() {
        var selectedPrice = $(this).find('option:selected').data('price');
        $('#montant').val(selectedPrice || '');
    });
    $('#classe').on('change', function() {
        var selectedPrice = $(this).find('option:selected').data('price');
        $('#frais_scolaire').val(selectedPrice || '');
    });

    // Pré-remplissage en mode édition
    <?php if ($isEdit && $eleveEdit): ?>
        // Set selects
        $('#genre').val('<?php echo addslashes($eleveEdit['genre']); ?>');
        $('#select_product').val('<?php echo (int)$eleveEdit['menage']; ?>').trigger('change');
        $('#classe').val('<?php echo (int)$eleveEdit['classe']; ?>');

        // Frais scolaire (prend le data-price de l'option classe correspondante)
        var clsOpt = $('#classe').find('option:selected');
        var frais = clsOpt.data('price') || '<?php echo (float)$eleveEdit['montant_a_payer']; ?>';
        $('#frais_scolaire').val(frais);

        // Montant ménage actuel
        $('#montant').val('<?php echo isset($eleveEdit['menage_montant']) ? (float)$eleveEdit['menage_montant'] : 0; ?>');
    <?php endif; ?>
});
</script>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-7 col-sm-12 grid-margin">
                <form action="<?php echo $_SERVER['PHP_SELF'] . ($isEdit ? '?id='.(int)$eleveEdit['id'] : ''); ?>" method="post" role="form">
                    <input type="hidden" name="eleve_id" value="<?php echo $isEdit ? (int)$eleveEdit['id'] : ''; ?>">

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">
                                <button class="btn btn-primary btn-block enter-btn me-2" type="button">
                                    <span class="menu-icon"><i class="mdi mdi-speedometer"></i></span>
                                </button>
                                <?php echo $isEdit ? "Modifier l'élève #".(int)$eleveEdit['id'] : "Gestion d'élève"; ?>
                            </h4>

                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Nom</label>
                                        <input type="text" class="form-control" name="nom" id="nom"
                                            value="<?php echo $isEdit ? htmlspecialchars($eleveEdit['nom']) : ''; ?>"
                                            placeholder="Nom élève" oninput="this.value = this.value.toUpperCase();" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Post nom</label>
                                        <input type="text" class="form-control" name="postnom" id="postnom"
                                            value="<?php echo $isEdit ? htmlspecialchars($eleveEdit['postnom']) : ''; ?>"
                                            placeholder="post nom élève" oninput="this.value = this.value.toUpperCase();" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Prénom</label>
                                        <input type="text" class="form-control" name="prenom" id="prenom"
                                            value="<?php echo $isEdit ? htmlspecialchars($eleveEdit['prenom']) : ''; ?>"
                                            placeholder="prénom élève" oninput="this.value = this.value.toUpperCase();" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Genre</label>
                                        <select class="form-control" name="genre" id="genre" required>
                                            <option value="#" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>Sélectionnez genre</option>
                                            <option value="M">M</option>
                                            <option value="F">F</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Lieu de naissance</label>
                                        <input type="text" class="form-control" name="lieuDeNaissance" id="lieuDeNaissance"
                                            value="<?php echo $isEdit ? htmlspecialchars($eleveEdit['lieu']) : ''; ?>"
                                            placeholder="Lieu de naissance" oninput="this.value = this.value.toUpperCase();" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Date de naissance</label>
                                        <input type="date" class="form-control" name="dateDeNaissance" id="dateDeNaissance"
                                            value="<?php echo $isEdit ? htmlspecialchars($eleveEdit['dateDeNaissance']) : ''; ?>"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-lg-12 d-flex align-items-center">
                                    <input type="text" class="form-control file-upload-info" disabled=""
                                        placeholder="Téléverser l'Image">
                                    <span class="input-group-append ms-2">
                                        <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

            </div>
            <div class="col-lg-5 col-sm-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Autres informations</h4>

                        <div class="row">
                            <div class="form-group col-lg-12">
                                <label class="form-label">Famille</label>
                                <div class="d-flex align-items-center">
                                    <select class="form-control" name="menage" id="select_product" required>
                                        <option value="#" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>Sélectionnez la famille de l'élève</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?= $product['id']; ?>" data-price="<?= $product['montantAPayer']; ?>"
                                                <?php echo ($isEdit && (int)$eleveEdit['menage'] === (int)$product['id']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($product['noms']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="input-group-append ms-2">
                                        <a href="../menage/create-update.php" class="file-upload-browse btn btn-primary" type="button">Ajouter</a>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-12">
                                <label class="form-label">Classe</label>
                                <div class="d-flex align-items-center">
                                    <select class="form-control" name="classe" id="classe" required>
                                        <option value="#" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>Définir la classe de l'élève</option>
                                        <?php foreach ($row_classe as $product): ?>
                                            <option value="<?= $product['idClasse']; ?>" data-price="<?= $product['montantPayer']; ?>"
                                                <?php echo ($isEdit && (int)$eleveEdit['classe'] === (int)$product['idClasse']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($product['description']); ?> <?= htmlspecialchars($product['descriptionCycle']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="input-group-append ms-2">
                                        <a href="../classe/create-update.php" class="file-upload-browse btn btn-primary" type="button">Ajouter</a>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">Frais scolaire</label>
                                    <input type="number" class="form-control" name="frais_scolaire" id="frais_scolaire"
                                        placeholder="Frais scolaire" readonly required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">Montant à payer (Famille)</label>
                                    <input type="number" class="form-control" name="montant" id="montant"
                                        placeholder="montant" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <?php if ($isEdit): ?>
                                <button class="btn btn-success btn-block enter-btn" name="update" id="update">Mettre à jour</button>
                                <a href="../eleve/" class="btn btn-dark btn-block enter-btn">Annuler</a>
                            <?php else: ?>
                                <button class="btn btn-success btn-block enter-btn" name="submit" id="submit">Sauvegarder</button>
                                <button type="reset" class="btn btn-dark btn-block enter-btn">Annuler</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>

    </div>
    <!-- content-wrapper ends -->

    <?php require_once ('../../layouts/constants/footer.php'); ?>
