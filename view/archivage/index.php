<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once ('../../layouts/constants/head.php'); 
require_once ('../../webapp/service/annee_scolaire.service.php'); 
require_once ('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/config.php');
mysqli_set_charset($con, 'utf8mb4');
// session_start();

if (!$con) {
    die("Erreur de connexion à la base de données");
}

/* =====================================================
   1. RECUPERATION DES STATISTIQUES GLOBALES TOUTES ARCHIVES
===================================================== */
$sql_totals = "
    SELECT 
        COUNT(DISTINCT s.id) AS total_sessions,
        SUM(st.total_menages) AS cumul_menages,
        SUM(st.total_eleves) AS cumul_eleves,
        SUM(st.encaissement_scolaire + st.encaissement_connexe) AS cumul_recettes,
        SUM(st.depenses) AS cumul_depenses,
        SUM(st.benefice) AS cumul_benefice
    FROM archive_session s
    LEFT JOIN archive_statistiques st ON st.archive_id = s.id
    WHERE s.statut = 'termine'
";
$res_totals = mysqli_query($con, $sql_totals);
$totals = mysqli_fetch_assoc($res_totals);

/* =====================================================
   2. RECUPERATION DE TOUTES LES SESSIONS D'ARCHIVES
===================================================== */
$sql_sessions = "
    SELECT 
        s.id AS session_id,
        s.annee_scolaire_id AS annee_scolaire_id,
        s.annee_scolaire,
        s.commentaire,
        s.date_archivage,
        st.total_menages,
        st.total_eleves,
        st.encaissement_scolaire,
        st.encaissement_connexe,
        st.depenses,
        st.benefice
    FROM archive_session s
    LEFT JOIN archive_statistiques st ON st.archive_id = s.id
    WHERE s.statut = 'termine'
    ORDER BY s.id DESC
";
$result_sessions = mysqli_query($con, $sql_sessions);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archives - Tableau de Bord</title>
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .card-stat {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s;
    }

    .card-stat:hover {
        transform: translateY(-3px);
    }

    .icon-shape {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
    </style>
</head>

<body class="bg-light">
    <div class="container py-5">

        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1"><i class="fas fa-box-archive text-primary me-2"></i> Tableau de Bord
                    des Archives</h2>
                <p class="text-muted mb-0">Consultez l'historique complet des années scolaires clôturées.</p>
            </div>
            <a href="../annee_scolaire/" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Années Scolaires
            </a>
        </div>

        <!-- Cartes Statistiques Générales -->
        <div class="row g-3 mb-5">
            <div class="col-md-3">
                <div class="card card-stat bg-white shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape bg-primary bg-opacity-10 text-primary me-3">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-semibold">Années Clôturées</small>
                            <h4 class="mb-0 fw-bold"><?= number_format($totals['total_sessions'] ?? 0) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat bg-white shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape bg-info bg-opacity-10 text-info me-3">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-semibold">Élèves Archivés</small>
                            <h4 class="mb-0 fw-bold"><?= number_format($totals['cumul_eleves'] ?? 0) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat bg-white shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape bg-success bg-opacity-10 text-success me-3">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-semibold">Recettes Totales</small>
                            <h4 class="mb-0 fw-bold"><?= number_format($totals['cumul_recettes'] ?? 0, 2, ',', ' ') ?> $
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat bg-white shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape bg-danger bg-opacity-10 text-danger me-3">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-semibold">Dépenses Totales</small>
                            <h4 class="mb-0 fw-bold"><?= number_format($totals['cumul_depenses'] ?? 0, 2, ',', ' ') ?> $
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des Années Scolaires Archivées -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i> Historique
                    des Sessions Clôturées</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-responsive mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Année Scolaire</th>
                                <th>Ménages</th>
                                <th>Élèves</th>
                                <th>Recettes</th>
                                <th>Dépenses</th>
                                <th>Bénéfice Net</th>
                                <th>Date Clôture</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_sessions && mysqli_num_rows($result_sessions) > 0): ?>
                            <?php while ($s = mysqli_fetch_assoc($result_sessions)): ?>
                            <?php 
                                        $recette_totale = $s['encaissement_scolaire'] + $s['encaissement_connexe'];
                                        $benefice = $s['benefice'];
                                    ?>
                            <tr>
                                <td>
                                    <span
                                        class="badge bg-primary fs-6 px-3 py-2"><?= htmlspecialchars($s['annee_scolaire']) ?></span>
                                </td>
                                <td><strong><?= number_format($s['total_menages'] ?? 0) ?></strong></td>
                                <td><strong><?= number_format($s['total_eleves'] ?? 0) ?></strong></td>
                                <td class="text-success fw-bold"><?= number_format($recette_totale, 2, ',', ' ') ?> $
                                </td>
                                <td class="text-danger fw-bold"><?= number_format($s['depenses'] ?? 0, 2, ',', ' ') ?> $
                                </td>
                                <td>
                                    <span class="badge <?= $benefice >= 0 ? 'bg-success' : 'bg-danger' ?> fs-6">
                                        <?= number_format($benefice, 2, ',', ' ') ?> $
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($s['date_archivage'])) ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="../annee_scolaire/ouvrir_consulter_annee.php?id=<?= $s['annee_scolaire_id'] ?>"
                                        class="btn btn-primary btn-sm px-3 fw-semibold">
                                        <i class="fas fa-folder-open me-1"></i> Consulter
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block text-secondary"></i>
                                    Aucune année scolaire n'a encore été archivée.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once ('../../layouts/constants/footer.php'); ?>
</body>

</html>