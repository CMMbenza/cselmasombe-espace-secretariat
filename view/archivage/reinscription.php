<?php
session_start();
require_once('../../layouts/constants/head.php');
require_once('../../webapp/database/config.php');
require_once('../../layouts/navbar/navbar.php');

if (!isset($con) && isset($conn)) { $con = $conn; }
mysqli_set_charset($con, 'utf8mb4');

/* =====================================================
   HELPERS
===================================================== */
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/* =====================================================
   RÉCUPÉRATION DE L'ANNÉE SCOLAIRE EN COURS
===================================================== */
$annee_active = null;
$res = mysqli_query($con, "
    SELECT *
    FROM annee_scolaire
    WHERE status='encours'
    ORDER BY id DESC
    LIMIT 1
");

if ($res && mysqli_num_rows($res) > 0) {
    $annee_active = mysqli_fetch_assoc($res);
}

/* =====================================================
   LISTE DES MÉNAGES ARCHIVÉS NON RESTAURÉS
===================================================== */
$menages_archive = mysqli_query($con, "
    SELECT am.*, a.annee_scolaire AS session_nom
    FROM archive_menage am
    INNER JOIN archive_session a ON a.id = am.archive_id
    WHERE am.restaure = 0
    ORDER BY am.noms ASC
");

/* =====================================================
   MÉNAGE SÉLECTIONNÉ (VIA CODE_MENAGE) & ÉLÈVES ASSOCIÉS
===================================================== */
$menage = null;
$eleves = [];

if (isset($_GET['code_menage']) && !empty(trim($_GET['code_menage']))) {
    $code_menage = trim($_GET['code_menage']);

    // 1. Récupération du ménage archivé par son CODE_MENAGE
    $stmt = mysqli_prepare($con, "
        SELECT am.*, a.annee_scolaire AS session_nom 
        FROM archive_menage am
        INNER JOIN archive_session a ON a.id = am.archive_id
        WHERE am.code_menage = ?
    ");
    mysqli_stmt_bind_param($stmt, "s", $code_menage);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $menage = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);

    // 2. Récupération des élèves où la colonne 'menage' correspond au CODE_MENAGE
    if ($menage) {
        $stmt_eleves = mysqli_prepare($con, "
            SELECT * 
            FROM archive_eleve 
            WHERE menage = ? AND (restaure = 0 OR restaure IS NULL)
            ORDER BY nom, postnom, prenom ASC
        ");
        mysqli_stmt_bind_param($stmt_eleves, "s", $menage['code_menage']);
        mysqli_stmt_execute($stmt_eleves);
        $resEleve = mysqli_stmt_get_result($stmt_eleves);

        while ($row = mysqli_fetch_assoc($resEleve)) {
            $eleves[] = $row;
        }
        mysqli_stmt_close($stmt_eleves);
    }
}
?>

<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <h3 class="card-title text-dark font-weight-bold mb-4">
                            <i class="fa fa-refresh text-warning mr-2"></i>
                            Réinscription par Ménage Archivé
                        </h3>

                        <!-- MESSAGES DE NOTIFICATION -->
                        <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle mr-2"></i><?= $_SESSION['success']; ?>
                        </div>
                        <?php unset($_SESSION['success']); endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-triangle mr-2"></i><?= $_SESSION['error']; ?>
                        </div>
                        <?php unset($_SESSION['error']); endif; ?>

                        <!-- =====================================================
                             SÉLECTION DU MÉNAGE (PAR CODE_MENAGE)
                        ===================================================== -->
                        <div class="bg-light p-4 rounded mb-4 border">
                            <form method="GET" action="">
                                <div class="row align-items-end">
                                    <div class="col-md-8">
                                        <label class="form-label font-weight-bold text-dark">
                                            <i class="fa fa-search mr-1"></i> Sélectionner un ménage à réinscrire :
                                        </label>
                                        <select name="code_menage" class="form-control form-select shadow-sm"
                                            onchange="this.form.submit()" style="height: 45px;">
                                            <option value="">-- Sélectionner un ménage --</option>
                                            <?php while ($m = mysqli_fetch_assoc($menages_archive)): ?>
                                            <option value="<?= e($m['code_menage']); ?>"
                                                <?= (isset($_GET['code_menage']) && $_GET['code_menage'] === $m['code_menage']) ? 'selected' : '' ?>>
                                                Code: <?= e($m['code_menage']); ?> | <?= e($m['noms']); ?> — Tél:
                                                <?= e($m['telephone']); ?> (Session: <?= e($m['session_nom']); ?>)
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <?php if ($menage): ?>
                        <hr class="my-4">

                        <!-- =====================================================
                             1. FICHE DU MÉNAGE ARCHIVÉ
                        ===================================================== -->
                        <div class="card border-left-primary shadow-sm mb-4"
                            style="border-left: 5px solid #4e73df; background-color: #f8f9fc;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="text-primary font-weight-bold mb-0">
                                        <i class="fa fa-home mr-2"></i> Fiche du Ménage Archivé
                                        #<?= e($menage['code_menage']); ?>
                                    </h5>
                                    <span
                                        class="badge badge-<?= ($menage['STATUS'] === 'actif') ? 'success' : 'danger'; ?> px-3 py-2">
                                        Statut : <?= strtoupper(e($menage['STATUS'])); ?>
                                    </span>
                                </div>

                                <div class="row text-dark">
                                    <!-- Identité & Contact -->
                                    <div class="col-md-4 mb-3">
                                        <small class="text-muted font-weight-bold d-block text-uppercase">Responsable /
                                            Nom :</small>
                                        <strong style="font-size: 1.1rem;"><?= e($menage['noms']); ?></strong>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <small class="text-muted font-weight-bold d-block text-uppercase">Téléphone
                                            :</small>
                                        <i
                                            class="fa fa-phone text-secondary mr-1"></i><strong><?= e($menage['telephone']); ?></strong>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <small class="text-muted font-weight-bold d-block text-uppercase">Adresse Email
                                            :</small>
                                        <span><?= !empty($menage['email']) ? e($menage['email']) : '<em class="text-muted">Non renseignée</em>'; ?></span>
                                    </div>

                                    <!-- Adresse Civile -->
                                    <div class="col-md-8 mb-3">
                                        <small class="text-muted font-weight-bold d-block text-uppercase">Adresse
                                            Complète :</small>
                                        <i class="fa fa-map-marker text-danger mr-1"></i>
                                        N° <?= e($menage['numero']); ?>, Av. <?= e($menage['avenue']); ?>, Quartier
                                        <?= e($menage['quartier']); ?>, Commune de <?= e($menage['commune']); ?>
                                    </div>

                                    <!-- Informations d'Archivage -->
                                    <div class="col-md-4 mb-3">
                                        <small class="text-muted font-weight-bold d-block text-uppercase">Session &
                                            Année d'archive :</small>
                                        <span class="badge badge-info p-2 font-weight-bold">
                                            <?= e($menage['session_nom']); ?> (<?= e($menage['annee_archive']); ?>)
                                        </span>
                                    </div>

                                    <!-- Données Financières -->
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted font-weight-bold d-block text-uppercase">Montant
                                            Archivé ($) :</small>
                                        <strong class="text-primary"
                                            style="font-size: 1rem;"><?= number_format((float)$menage['montantAPayer'], 2, ',', ' '); ?>
                                            $</strong>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted font-weight-bold d-block text-uppercase">Montant
                                            Archivé ($) :</small>
                                        <strong class="text-primary"
                                            style="font-size: 1rem;"><?= number_format((float)$menage['montantAPayerFC'], 2, ',', ' '); ?>
                                            $</strong>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted font-weight-bold d-block text-uppercase">Date
                                            d'archivage :</small>
                                        <small class="text-dark"><i
                                                class="fa fa-clock-o mr-1"></i><?= date('d/m/Y H:i', strtotime($menage['date_archivage'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- =====================================================
                             2. LISTE DES ENFANTS RATTACHÉS
                        ===================================================== -->
                        <div class="mt-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-primary font-weight-bold mb-0">
                                    <i class="fa fa-graduation-cap mr-2"></i> Élève(s) rattaché(s) au ménage Code :
                                    <?= e($menage['code_menage']); ?>
                                </h5>
                                <span class="badge badge-secondary p-2">Total : <?= count($eleves); ?> enfant(s)</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 50px;">ID</th>
                                            <th>Nom Complet</th>
                                            <th class="text-center">Genre</th>
                                            <th class="text-center">Lieu & Date de Naissance</th>
                                            <th class="text-center">Classe Archivée</th>
                                            <th class="text-right">Frais scolaire</th>
                                            <th class="text-right">Frais connexe</th>
                                            <th class="text-center">Créé par</th>
                                            <th class="text-center">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($eleves)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted font-italic py-4">
                                                <i class="fa fa-info-circle mr-1"></i> Aucun élève trouvé pour le ménage
                                                Code : <strong><?= e($menage['code_menage']); ?></strong>.
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($eleves as $e): ?>
                                        <tr>
                                            <!-- ID Eleve -->
                                            <td class="text-center">
                                                #<?= e($e['id']); ?>
                                            </td>

                                            <!-- Nom Complet -->
                                            <td class="">
                                                <?= e($e['nom']); ?> <?= e($e['postnom']); ?> <?= e($e['prenom']); ?>
                                            </td>

                                            <!-- Genre -->
                                            <td class="text-center">
                                                <?php if (strtoupper($e['genre']) === 'M'): ?>
                                                <span class="badge badge-pill badge-info px-2 py-1"><i
                                                        class="fa fa-mars mr-1"></i>Garçon</span>
                                                <?php else: ?>
                                                <span class="badge badge-pill badge-danger px-2 py-1"><i
                                                        class="fa fa-venus mr-1"></i>Fille</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Lieu & Date de Naissance -->
                                            <td class="text-center small">
                                                <i
                                                    class="fa fa-calendar text-secondary mr-1"></i><?= date('d/m/Y', strtotime($e['dateDeNaissance'])); ?><br>
                                                <span class="text-muted"><i class="fa fa-map-marker mr-1"></i>à
                                                    <?= e($e['lieu']); ?></span>
                                            </td>

                                            <!-- Classe -->
                                            <td class="text-center">
                                                <span class="badge badge-dark px-3 py-2 font-weight-bold">
                                                    Classe <?= e($e['classe']); ?>
                                                </span>
                                            </td>

                                            <!-- Montant à Payer frais scolaire-->
                                            <td class="text-right font-weight-bold text-primary">
                                                <?= number_format((float)$e['montant_a_payer'], 2, ',', ' '); ?> $
                                            </td>

                                            <!-- Montant à Payer Frais connexe-->
                                            <td class="text-right font-weight-bold text-primary">
                                                <?= number_format((float)$e['montantAPayerFC'], 2, ',', ' '); ?> $
                                            </td>

                                            <!-- Créateur -->
                                            <td class="text-center small">
                                                <span
                                                    class="d-block font-weight-bold text-dark"><?= e($e['createdby']); ?></span>
                                                <span
                                                    class="text-muted"><?= date('d/m/Y', strtotime($e['dateCreated'])); ?></span>
                                            </td>

                                            <!-- Statut -->
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-<?= ($e['STATUS'] === 'actif') ? 'success' : 'danger'; ?> px-2 py-1">
                                                    <?= ucfirst(e($e['STATUS'])); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- =====================================================
                             3. ACTION DE RÉINSCRIPTION
                        ===================================================== -->
                        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border">
                            <span class="text-dark">
                                <i class="fa fa-info-circle text-info mr-1"></i> La réinscription restaurera la famille
                                <strong><?= e($menage['noms']); ?></strong> (Code: <?= e($menage['code_menage']); ?>)
                                ainsi que ses enfants pour la nouvelle année.
                            </span>
                            <form method="POST" action="service/create.update.service.php">
                                <input type="hidden" name="action" value="restaurer_menage">
                                <input type="hidden" name="code_menage" value="<?= e($menage['code_menage']); ?>">
                                <button class="btn btn-success font-weight-bold shadow-sm px-4 py-2"
                                    name="btn_restaurer" type="submit">
                                    <i class="fa fa-check-circle mr-1"></i> Valider la Réinscription du Ménage
                                </button>
                            </form>
                        </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('../../layouts/constants/footer.php'); ?>