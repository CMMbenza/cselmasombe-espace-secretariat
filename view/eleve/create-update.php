<?php 
require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/eleve.service.create.update.php'); 
require_once ('../../webapp/service/classe.service.php');  
require_once ('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/dbcongig.php'); // expose $conn
require_once('../../webapp/service/annee_scolaire.encours.php');

/* ===== Helper d’échappement HTML ===== */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

// ====== Mode Édition ? ======
$isEdit = false;
$eleveEdit = null;

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $isEdit = true;
    $editId = (int)$_GET['id'];
    $sqlEdit = "SELECT e.*, 
                       m.noms AS menage_noms, 
                       m.montantAPayer AS menage_montant_usd, 
                       m.montantAPayerFC AS menage_montant_fc,
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

// ====== Paramètre id_menage ======
$id_menage = isset($_GET['id_menage']) && ctype_digit($_GET['id_menage']) ? (int)$_GET['id_menage'] : null;

if ($isEdit && $eleveEdit && !$id_menage) {
    $id_menage = (int)$eleveEdit['menage'];
}

$enfantsMenage = [];
$selectedMenageInfo = null;

if ($id_menage) {
    $stmtM = $conn->prepare("SELECT id, noms, montantAPayer, montantAPayerFC FROM menage WHERE id = ?");
    $stmtM->bind_param("i", $id_menage);
    $stmtM->execute();
    $selectedMenageInfo = $stmtM->get_result()->fetch_assoc();
    $stmtM->close();

    $sqlKids = "SELECT e.*, 
                       c.description AS classe_desc, 
                       cy.description AS cycle_desc 
                  FROM eleve e 
             LEFT JOIN classe c ON c.id = e.classe 
             LEFT JOIN cycle cy ON cy.id = c.cycle 
                 WHERE e.menage = ?
              ORDER BY e.nom ASC, e.postnom ASC";
    $stmtK = $conn->prepare($sqlKids);
    $stmtK->bind_param("i", $id_menage);
    $stmtK->execute();
    $resK = $stmtK->get_result();
    while ($rowK = $resK->fetch_assoc()) {
        $enfantsMenage[] = $rowK;
    }
    $stmtK->close();
}

// ====== Liste des Ménages ======
$sql = "SELECT id, noms, montantAPayer, montantAPayerFC FROM menage ORDER BY noms ASC";
$stmt = $conn->prepare($sql);

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$stmt->close();

// ====== Classes & Frais ======
$sqlClasses = "
    SELECT 
        cls.id AS idClasse, 
        cls.description AS descriptionClasse, 
        cy.id AS cycleId, 
        cy.description AS descriptionCycle,
        MAX(CASE WHEN sc.description = 'Frais scolaire' THEN sc.montant ELSE 0 END) AS frais_scolaire,
        MAX(CASE WHEN sc.description = 'Frais connexe' THEN sc.montant ELSE 0 END) AS frais_connexe
    FROM classe cls
    JOIN cycle cy ON cls.cycle = cy.id
    LEFT JOIN scolarite sc ON cy.id = sc.cycle
    GROUP BY cls.id, cls.description, cy.id, cy.description
    ORDER BY cls.description ASC
";

$rstClass = $conn->query($sqlClasses);
$row_classe = [];
while ($row = $rstClass->fetch_assoc()) {
    $row_classe[] = $row;
}

$conn->close();

$queryParams = [];
if ($isEdit) $queryParams[] = 'id=' . (int)$eleveEdit['id'];
if ($id_menage) $queryParams[] = 'id_menage=' . (int)$id_menage;
$formAction = $_SERVER['PHP_SELF'] . (!empty($queryParams) ? '?' . implode('&', $queryParams) : '');
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#select_product').on('change', function() {
        var opt = $(this).find('option:selected');
        var priceUsd = opt.data('price-usd');
        var priceFc = opt.data('price-fc');
        $('#menage_montant_usd').val(priceUsd !== undefined ? priceUsd : '');
        $('#menage_montant_fc').val(priceFc !== undefined ? priceFc : '');
    });

    $('#classe').on('change', function() {
        var opt = $(this).find('option:selected');
        var fraisScolaire = opt.data('frais-scolaire');
        var fraisConnexe = opt.data('frais-connexe');

        $('#eleve_montant_usd').val(fraisScolaire !== undefined ? fraisScolaire : '');
        $('#eleve_montant_fc').val(fraisConnexe !== undefined ? fraisConnexe : '');
    });

    // Génération dynamique du matricule (Preview) lors de la saisie
    function updateMatriculePreview() {
        <?php if (!$isEdit || empty($eleveEdit['matricule'])): ?>
        var nom = $('#nom').val().trim();
        var postnom = $('#postnom').val().trim();
        var prenom = $('#prenom').val().trim();

        var iNom = nom.length > 0 ? nom.charAt(0).toUpperCase() : 'X';
        var iPostnom = postnom.length > 0 ? postnom.charAt(0).toUpperCase() : 'X';
        var iPrenom = prenom.length > 0 ? prenom.charAt(0).toUpperCase() : 'X';

        var eleveId = '<?php echo $isEdit ? (int)$eleveEdit['id'] : "ID"; ?>';
        var anneeCourt = new Date().getFullYear().toString().slice(-2);

        var preview = eleveId + '-' + iNom + iPostnom + iPrenom + '-' + anneeCourt;
        $('#matricule_preview').val(preview);
        <?php endif; ?>
    }

    $('#nom, #postnom, #prenom').on('input', updateMatriculePreview);

    if ($('#select_product').val() && $('#select_product').val() !== '#') {
        $('#select_product').trigger('change');
    }

    <?php if ($isEdit && $eleveEdit): ?>
    $('#genre').val('<?php echo addslashes($eleveEdit['genre']); ?>');
    $('#select_product').val('<?php echo (int)$eleveEdit['menage']; ?>').trigger('change');
    $('#classe').val('<?php echo (int)$eleveEdit['classe']; ?>');

    var clsOpt = $('#classe').find('option:selected');
    var fraisScolaire = clsOpt.data('frais-scolaire') || '<?php echo (float)$eleveEdit['montant_a_payer']; ?>';
    var fraisConnexe = clsOpt.data('frais-connexe') ||
        '<?php echo (float)($eleveEdit['montantAPayerFC'] ?? 0); ?>';

    $('#eleve_montant_usd').val(fraisScolaire);
    $('#eleve_montant_fc').val(fraisConnexe);

    $('#menage_montant_usd').val(
        '<?php echo isset($eleveEdit['menage_montant_usd']) ? (float)$eleveEdit['menage_montant_usd'] : ''; ?>'
    );
    $('#menage_montant_fc').val(
        '<?php echo isset($eleveEdit['menage_montant_fc']) ? (float)$eleveEdit['menage_montant_fc'] : ''; ?>'
    );
    <?php endif; ?>
});
</script>

<div class="main-panel">
    <div class="content-wrapper">

        <!-- BLOC DYNAMIQUE : Liste des enfants du ménage -->
        <?php if ($id_menage && !empty($enfantsMenage)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title text-primary mb-0">
                                <i class="mdi mdi-account-group me-2"></i>
                                Enfants enregistrés pour la famille :
                                <strong><?php echo e($selectedMenageInfo['noms'] ?? 'Ménage #'.$id_menage); ?></strong>
                            </h4>
                            <div>
                                <a href="create-update.php?id_menage=<?php echo (int)$id_menage; ?>"
                                    class="btn btn-sm btn-success me-2">
                                    <i class="mdi mdi-plus-circle me-1"></i> Ajouter un élève dans cette famille
                                </a>
                                <span class="badge bg-primary fs-6">
                                    <?php echo count($enfantsMenage); ?> enfant(s) inscrit(s)
                                </span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Matricule</th>
                                        <th>Nom Complet</th>
                                        <th>Genre</th>
                                        <th>Classe</th>
                                        <th>Lieu & Date Naiss.</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enfantsMenage as $index => $enfant): ?>
                                    <tr
                                        class="<?php echo ($isEdit && (int)$eleveEdit['id'] === (int)$enfant['id']) ? 'table-secondary' : ''; ?>">
                                        <td><?php echo $index + 1; ?></td>
                                        <td><span
                                                class="badge bg-outline-dark text-dark fw-bold"><?php echo e($enfant['matricule']); ?></span>
                                        </td>
                                        <td><strong><?php echo e($enfant['nom'] . ' ' . $enfant['postnom'] . ' ' . $enfant['prenom']); ?></strong>
                                        </td>
                                        <td><span class="badge bg-primary"><?php echo e($enfant['genre']); ?></span>
                                        </td>
                                        <td><?php echo e($enfant['classe_desc'] . ' ' . $enfant['cycle_desc']); ?></td>
                                        <td><?php echo e($enfant['lieu']); ?>,
                                            <?php echo e($enfant['dateDeNaissance']); ?></td>
                                        <td class="text-center">
                                            <a href="create-update.php?id=<?php echo (int)$enfant['id']; ?>&id_menage=<?php echo (int)$id_menage; ?>#formUpdateEleve"
                                                class="btn btn-sm btn-warning me-1">
                                                <i class="mdi mdi-pencil"></i> Modifier
                                            </a>
                                            <a href="../../webapp/service/eleve.service.create.update.php?action=delete_eleve&id=<?php echo (int)$enfant['id']; ?>&id_menage=<?php echo (int)$id_menage; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Êtes-vous sûr de vouloir retirer cet enfant ? Les montants de la famille seront automatiquement mis à jour.');">
                                                <i class="mdi mdi-delete"></i> Retirer
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- FORMULAIRE PRINCIPAL -->
        <form action="<?php echo $formAction; ?>" method="post" role="form">
            <div class="row" id="formUpdateEleve">
                <!-- CÔTÉ ÉLÈVE -->
                <div class="col-lg-7 col-sm-12 grid-margin">
                    <input type="hidden" name="eleve_id" value="<?php echo $isEdit ? (int)$eleveEdit['id'] : ''; ?>">

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">
                                <button class="btn btn-primary btn-block enter-btn me-2" type="button">
                                    <span class="menu-icon"><i class="mdi mdi-account-school"></i></span>
                                </button>
                                <?php 
                                    if ($isEdit) {
                                        echo "Modifier l'élève #".(int)$eleveEdit['id'];
                                    } else if ($id_menage) {
                                        echo "Ajouter un enfant à la famille " . e($selectedMenageInfo['noms'] ?? '');
                                    } else {
                                        echo "Informations Élève";
                                    }
                                ?>
                            </h4>

                            <!-- CHAMP MATRICULE DYNAMIQUE / CONDITIONNEL -->
                            <?php if (!$isEdit || empty($eleveEdit['matricule'])): ?>
                            <!-- MASQUÉ LORS DE L'ÉDITION, AFFICHÉ UNIQUEMENT LORS DE LA CRÉATION (`id_menage=val`) -->
                            <div class="row mb-3">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label text-primary fw-bold">Matricule</label>
                                        <input type="text" id="matricule_preview"
                                            class="form-control fw-bold text-primary"
                                            value="<?php echo ($isEdit && !empty($eleveEdit['matricule'])) ? e($eleveEdit['matricule']) : 'Génération automatique...'; ?>"
                                            readonly>
                                        <small class="text-muted text-danger">Un matricule sera attribué automatiquement
                                            à l'enregistrement.</small>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-lg-4 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Nom</label>
                                        <input type="text" class="form-control" name="nom" id="nom"
                                            value="<?php echo $isEdit ? e($eleveEdit['nom']) : ''; ?>"
                                            placeholder="Nom élève" oninput="this.value = this.value.toUpperCase();"
                                            required>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Post nom</label>
                                        <input type="text" class="form-control" name="postnom" id="postnom"
                                            value="<?php echo $isEdit ? e($eleveEdit['postnom']) : ''; ?>"
                                            placeholder="Post nom élève"
                                            oninput="this.value = this.value.toUpperCase();" required>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Prénom</label>
                                        <input type="text" class="form-control" name="prenom" id="prenom"
                                            value="<?php echo $isEdit ? e($eleveEdit['prenom']) : ''; ?>"
                                            placeholder="Prénom élève" oninput="this.value = this.value.toUpperCase();"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Genre</label>
                                        <select class="form-control" name="genre" id="genre" required>
                                            <option value="#" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>
                                                Sélectionnez genre</option>
                                            <option value="M">M</option>
                                            <option value="F">F</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Nationalité</label>
                                        <input type="text" class="form-control" name="nationalite" id="nationalite"
                                            value="<?php echo $isEdit ? e($eleveEdit['nationalite'] ?? 'CONGOLAISE') : 'CONGOLAISE'; ?>"
                                            placeholder="Nationalité" oninput="this.value = this.value.toUpperCase();"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Lieu de naissance</label>
                                        <input type="text" class="form-control" name="lieuDeNaissance"
                                            id="lieuDeNaissance"
                                            value="<?php echo $isEdit ? e($eleveEdit['lieu']) : ''; ?>"
                                            placeholder="Lieu de naissance"
                                            oninput="this.value = this.value.toUpperCase();" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Date de naissance</label>
                                        <input type="date" class="form-control" name="dateDeNaissance"
                                            id="dateDeNaissance"
                                            value="<?php echo $isEdit ? e($eleveEdit['dateDeNaissance']) : ''; ?>"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">
                            <h5 class="text-dark mb-3">Scolarité & Tarification Élève</h5>

                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <label class="form-label">Classe</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-control" name="classe" id="classe" required>
                                            <option value="#" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>Définir
                                                la classe de l'élève</option>
                                            <?php foreach ($row_classe as $product): ?>
                                            <option value="<?= $product['idClasse']; ?>"
                                                data-frais-scolaire="<?= $product['frais_scolaire']; ?>"
                                                data-frais-connexe="<?= $product['frais_connexe']; ?>"
                                                <?php echo ($isEdit && (int)$eleveEdit['classe'] === (int)$product['idClasse']) ? 'selected' : ''; ?>>
                                                <?= e($product['descriptionClasse']); ?> -
                                                <?= e($product['descriptionCycle']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="input-group-append ms-2">
                                            <a href="../classe/create-update.php" class="btn btn-primary">Ajouter</a>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Frais scolaire ($ USD)</label>
                                        <input type="number" step="0.01" class="form-control" name="montant_a_payer"
                                            id="eleve_montant_usd"
                                            value="<?php echo $isEdit ? e($eleveEdit['montant_a_payer']) : ''; ?>"
                                            placeholder="Frais scolaire ($)" readonly required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Frais connexe ($ USD)</label>
                                        <input type="number" step="0.01" class="form-control" name="montantAPayerFC"
                                            id="eleve_montant_fc"
                                            value="<?php echo $isEdit ? e($eleveEdit['montantAPayerFC'] ?? '') : ''; ?>"
                                            placeholder="Frais connexe ($)">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- CÔTÉ MÉNAGE / FAMILLE -->
                <div class="col-lg-5 col-sm-12 grid-margin">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Informations Famille / Ménage</h4>

                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <label class="form-label">Famille</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-control" name="menage" id="select_product" required>
                                            <option value="#" disabled
                                                <?php echo (!$isEdit && !$id_menage) ? 'selected' : ''; ?>>Sélectionnez
                                                la famille</option>
                                            <?php foreach ($products as $product): ?>
                                            <?php 
                                                $selectedMenageId = $isEdit ? (int)$eleveEdit['menage'] : $id_menage;
                                                $isSelected = ($selectedMenageId && (int)$selectedMenageId === (int)$product['id']);
                                            ?>
                                            <option value="<?= $product['id']; ?>"
                                                data-price-usd="<?= $product['montantAPayer']; ?>"
                                                data-price-fc="<?= $product['montantAPayerFC']; ?>"
                                                <?php echo $isSelected ? 'selected' : ''; ?>>
                                                <?= e($product['noms']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="input-group-append ms-2">
                                            <a href="../menage/create-update.php" class="btn btn-primary">Ajouter</a>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">
                            <h5 class="text-dark mb-3">Situation Famille</h5>

                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Frais scolaire ($ USD)</label>
                                        <input type="number" step="0.01" class="form-control" name="menage_montant_usd"
                                            id="menage_montant_usd" placeholder="Montant Ménage ($)" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Frais connexe ($ USD)</label>
                                        <input type="number" step="0.01" class="form-control" name="menage_montant_fc"
                                            id="menage_montant_fc" placeholder="Montant Ménage FC" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <?php if ($isEdit): ?>
                                <button class="btn btn-success btn-block enter-btn" name="update" id="update">Mettre à
                                    jour</button>
                                <a href="create-update.php<?php echo $id_menage ? '?id_menage='.(int)$id_menage : ''; ?>"
                                    class="btn btn-dark btn-block enter-btn">Annuler</a>
                                <?php else: ?>
                                <button class="btn btn-success btn-block enter-btn" name="submit"
                                    id="submit">Sauvegarder</button>
                                <button type="reset" class="btn btn-dark btn-block enter-btn">Annuler</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>

    <?php require_once ('../../layouts/constants/footer.php'); ?>