<?php
declare(strict_types=1);

require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/depenses.service.create.update.php'); // définit $depense_error / $depense_success et traite le POST
require_once ('../../layouts/navbar/navbar.php');

if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('nf')) {
  function nf($n){ return number_format((float)$n, 2, ',', ' '); }
}
?>
<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">

            <!-- Formulaire Dépense -->
            <div class="col-lg-7 col-sm-12 grid-margin">
                <form action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post" role="form" autocomplete="on">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">
                                <button class="btn btn-primary btn-block enter-btn me-2" type="button" disabled>
                                    <span class="menu-icon"><i class="mdi mdi-speedometer"></i></span>
                                </button>
                                Gestion de sortie
                            </h4>

                            <?php if (!empty($depense_error)): ?>
                            <div class="alert alert-danger"><?php echo e($depense_error); ?></div>
                            <?php elseif (!empty($depense_success)): ?>
                            <div class="alert alert-success"><?php echo e($depense_success); ?>
                        <a href="../depense/" class="btn btn-dark">Retour</a></div>
                            <?php endif; ?>

                            <!-- Référence -->
                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Référence</label>
                                        <select name="reference" id="reference" class="form-control" required>
                                            <option value="">— Sélectionnez —</option>
                                            <option value="Frais Scolaire"
                                                <?php echo (($_POST['reference'] ?? '')==='Frais Scolaire')?'selected':''; ?>>
                                                Frais Scolaire</option>
                                            <option value="Frais Connexe"
                                                <?php echo (($_POST['reference'] ?? '')==='Frais Connexe')?'selected':''; ?>>
                                                Frais Connexe</option>
                                        </select>
                                        <!-- Ajout informatif (optionnel) juste sous la liste Référence -->
<p class="text-muted" style="margin-top:-10px">
  Le <strong>numéro de référence</strong> sera attribué automatiquement lors de l’enregistrement.
</p>

                                    </div>
                                </div>
                            </div>

                            <!-- Bénéficiaire -->
                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Bénéficiaire</label>
                                        <input type="text" class="form-control" name="beneficiaire" id="beneficiaire"
                                            placeholder="Nom du bénéficiaire" maxlength="150"
                                            value="<?php echo e($_POST['beneficiaire'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Motif (description) -->
                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Motif</label>
                                        <input type="text" class="form-control" name="description" id="description"
                                            placeholder="Entrer le motif du bon"
                                            value="<?php echo e($_POST['description'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Montant -->
                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Montant $</label>
                                        <input type="number" step="0.01" class="form-control" name="montant"
                                            id="montant" placeholder="Entrer le montant"
                                            value="<?php echo e($_POST['montant'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Date dépense (dateCreaty) -->
                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label">Date dépense</label>
                                        <input type="date" class="form-control" name="date_depense" id="date_depense"
                                            value="<?php echo e($_POST['date_depense'] ?? date('Y-m-d')); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-success btn-block enter-btn" name="submit"
                                id="submit">Sauvegarder</button>
                            <button type="reset" class="btn btn-dark btn-block enter-btn">Annuler</button>
                        </div>
                    </div>
                </form>
            </div>

        </div><!-- /.row -->
    </div><!-- /.content-wrapper -->

    <?php require_once ('../../layouts/constants/footer.php'); ?>
</div>