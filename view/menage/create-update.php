<?php
require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/menage.service.create.update.php'); // gère submit/update
require_once ('../../layouts/navbar/navbar.php');
require_once ('../../webapp/database/config.php'); // $con (mysqli)
require_once ('../../webapp/service/annee_scolaire.encours.php');

/* ===== Helper d’échappement (fallback) ===== */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

/* ===== Mode édition ===== */
$isEdit = false;
$menage = null;

// Vérification de la présence et de la validité du paramètre ?id=val
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $con->prepare("SELECT * FROM menage WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  // Si le ménage existe bien en BDD, on passe en mode édition
  if ($row = $result->fetch_assoc()) {
    $isEdit = true;
    $menage = $row;
  }
  $stmt->close();
}
?>

<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-7 col-sm-12 grid-margin">
                <form action="<?php echo e($_SERVER['PHP_SELF']) . ($isEdit ? '?id='.(int)$menage['id'] : ''); ?>"
                    method="post" role="form">

                    <input type="hidden" name="id" value="<?php echo $isEdit ? (int)$menage['id'] : ''; ?>">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

                                <h4 class="card-title d-flex align-items-center mb-2 mb-md-0">
                                    <button class="btn btn-primary me-2" type="button">
                                        <i class="mdi mdi-speedometer"></i>
                                    </button>

                                    <?php echo $isEdit ? "Modifier la famille #".(int)$menage['id'] : "Gestion des familles"; ?>
                                </h4>

                                <a href="../other/extraire_inscription.php" class="btn btn-outline-success">
                                    <i class="mdi mdi-file-export me-1"></i>
                                    Extraire l'inscription
                                </a>

                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Noms</label>
                                        <input type="text" class="form-control" name="noms" id="noms"
                                            value="<?php echo $isEdit ? e($menage['noms']) : ''; ?>"
                                            placeholder="Nom famille" oninput="this.value=this.value.toUpperCase();"
                                            required>
                                        <small class="text-danger">Si vous enregistrez une famille de personnes,
                                            assurez-vous de toujours inclure le nom
                                            <strong>(PERSONNEL)</strong>.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control" name="telephone" id="telephone"
                                            value="<?php echo $isEdit ? e($menage['telephone']) : ''; ?>"
                                            placeholder="Entrez le téléphone du titulaire" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Adresse email (Optionnel)</label>
                                        <input type="email" class="form-control" name="email" id="email"
                                            value="<?php echo $isEdit ? e($menage['email']) : ''; ?>"
                                            placeholder="Entrez l'adresse email">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Avenue</label>
                                        <input type="text" class="form-control" name="avenue" id="avenue"
                                            value="<?php echo $isEdit ? e($menage['avenue']) : ''; ?>"
                                            placeholder="Nom de l'avenue" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Numéro</label>
                                        <input type="text" class="form-control" name="numero" id="numero"
                                            value="<?php echo $isEdit ? e($menage['numero']) : ''; ?>"
                                            placeholder="Numéro de l'avenue" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Quartier</label>
                                        <input type="text" class="form-control" name="quartier" id="quartier"
                                            value="<?php echo $isEdit ? e($menage['quartier']) : ''; ?>"
                                            placeholder="Quartier" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Commune</label>
                                        <input type="text" class="form-control" name="commune" id="commune"
                                            value="<?php echo $isEdit ? e($menage['commune']) : ''; ?>"
                                            placeholder="Commune" required>
                                    </div>
                                </div>
                            </div>

                            <?php if ($isEdit): ?>
                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Frais scolaire</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo number_format((float)($menage['montantAPayer'] ?? 0), 2, ',', ' '); ?> $"
                                            disabled>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Frais connexe</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo number_format((float)($menage['montantAPayerFC'] ?? 0), 2, ',', ' '); ?> $"
                                            disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label text-danger">Tranches (actif)</label>
                                        <input type="number" class="form-control" name="start_tranche"
                                            id="start_tranche" value="<?php echo e($menage['start_tranche'] ?? ''); ?>"
                                            placeholder="start_tranche" required>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="mt-2">
                                <?php if ($isEdit): ?>
                                <button class="btn btn-success btn-block enter-btn" name="update" id="update">Mettre à
                                    jour</button>
                                <button class="btn btn-primary btn-block enter-btn" name="update_continue"
                                    id="update">Modifier et Continuer</button>
                                <?php else: ?>
                                <button class="btn btn-success btn-block enter-btn" name="submit"
                                    id="submit">Sauvegarder</button>
                                <button class="btn btn-primary btn-block enter-btn" name="submit_continue"
                                    id="submit">Sauvegarder et continuer</button>
                                <?php endif; ?>
                                <a href="../menage/" class="btn btn-secondary btn-block enter-btn">Annuler</a>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once ('../../layouts/constants/footer.php'); ?>