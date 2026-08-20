<?php
require_once('../../layouts/constants/head.php');
require_once('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/config.php');

/* Helpers */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt($n){ return number_format((float)$n, 2, '.', ' '); }

/* 1. FRAIS SCOLAIRES */
$totalScolairePayer = 0.0;
$sqlScol = "SELECT COALESCE(SUM(montantPayer), 0) AS total FROM paiement";
if ($st = $con->prepare($sqlScol)) {
    $st->execute();
    $totalScolairePayer = (float)($st->get_result()->fetch_assoc()['total'] ?? 0);
    $st->close();
}

/* 2. FRAIS CONNEXES */
$totalConnexePayer = 0.0;
$sqlDiv = "SELECT COALESCE(SUM(montantPayer), 0) AS total FROM paiement_divers";
if ($st = $con->prepare($sqlDiv)) {
    $st->execute();
    $totalConnexePayer = (float)($st->get_result()->fetch_assoc()['total'] ?? 0);
    $st->close();
}

/* 3. DÉPENSES */
$totalDepensesScolaire = 0.0;
$totalDepensesConnexe  = 0.0;
$totalDepensesAutres   = 0.0;
$totalDepensesGlobal   = 0.0;

if ($st = $con->prepare("SELECT reference, description, montant FROM depenses")) {
    $st->execute();
    $rs = $st->get_result();
    while ($r = $rs->fetch_assoc()) {
        $m = (float)$r['montant'];
        $ref = strtolower($r['reference']);
        $desc = strtolower($r['description']);

        $totalDepensesGlobal += $m;

        if (strpos($ref, 'scol') !== false || strpos($desc, 'scol') !== false) {
            $totalDepensesScolaire += $m;
        } elseif ($r['reference'] === 'Frais Connexe' || strpos($ref, 'connexe') !== false || strpos($desc, 'connexe') !== false) {
            $totalDepensesConnexe += $m;
        } else {
            $totalDepensesAutres += $m;
        }
    }
    $st->close();
}

/* Totaux et Soldes */
$totalEntreesGlobal = $totalScolairePayer + $totalConnexePayer;
$netScolaire        = $totalScolairePayer - $totalDepensesScolaire;
$netConnexe         = $totalConnexePayer - $totalDepensesConnexe;
$netGlobal          = $totalEntreesGlobal - $totalDepensesGlobal;
?>

<style>
.kpi-card {
    border: none;
    border-radius: 12px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
}

.kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-soft-primary {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.bg-soft-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.bg-soft-success {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.bg-soft-info {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.caisse-box {
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #edf2f7;
}
</style>

<body>
    <div class="main-panel">
        <div class="content-wrapper">

            <!-- En-tête principal -->
            <div class="mb-4">
                <h3 class="fw-bold mb-1 text-dark">
                    <i class="mdi mdi-view-dashboard text-primary me-2"></i>Vue d'ensemble Financière
                </h3>
                <span class="text-muted fs-6">Suivi synthétique des entrées, dépenses et soldes par caisse</span>
            </div>

            <!-- Section 1: KPIs Principaux -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card kpi-card shadow-sm h-100 bg-white">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase text-muted fw-bold fs-7">Recettes Totales</span>
                                <h3 class="fw-bold text-dark my-1"><?= fmt($totalEntreesGlobal) ?> $</h3>
                                <small class="text-success fw-semibold">
                                    <i class="mdi mdi-arrow-down-bold-circle me-1"></i> Entrées scolaires & connexes
                                </small>
                            </div>
                            <div class="kpi-icon bg-soft-primary">
                                <i class="mdi mdi-cash-multiple fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card kpi-card shadow-sm h-100 bg-white">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase text-muted fw-bold fs-7">Dépenses Totales</span>
                                <h3 class="fw-bold text-dark my-1"><?= fmt($totalDepensesGlobal) ?> $</h3>
                                <small class="text-danger fw-semibold">
                                    <i class="mdi mdi-arrow-up-bold-circle me-1"></i> Sorties de caisse cumulées
                                </small>
                            </div>
                            <div class="kpi-icon bg-soft-danger">
                                <i class="mdi mdi-cart-outline fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card kpi-card shadow-sm h-100 bg-white">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase text-muted fw-bold fs-7">Solde Réel En Caisse</span>
                                <h3 class="fw-bold my-1 <?= ($netGlobal < 0 ? 'text-danger' : 'text-success') ?>">
                                    <?= fmt($netGlobal) ?> $
                                </h3>
                                <small class="text-muted fw-semibold">
                                    <i class="mdi mdi-bank me-1"></i> Résultat net disponible
                                </small>
                            </div>
                            <div class="kpi-icon <?= ($netGlobal < 0 ? 'bg-soft-danger' : 'bg-soft-success') ?>">
                                <i class="mdi mdi-scale-balance fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Cartes Caisses Détaillées -->
            <div class="row g-4 mb-4">

                <!-- Carte Caisse Frais Scolaires -->
                <div class="col-lg-6">
                    <div class="caisse-box p-4 shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="kpi-icon bg-soft-primary">
                                    <i class="mdi mdi-school fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Caisse Frais Scolaires</h5>
                                    <span class="text-muted fs-7">Recettes & dépenses scolaires</span>
                                </div>
                            </div>
                            <a href="ventiallation_caisse_frais_scolaire.php"
                                class="btn btn-primary btn-sm rounded-pill px-3">
                                Consulter <i class="mdi mdi-arrow-right ms-1"></i>
                            </a>
                        </div>

                        <div class="row text-center g-2 pt-3 border-top">
                            <div class="col-4 border-end">
                                <span class="text-muted fs-7 d-block mb-1">
                                    <i class="mdi mdi-plus-circle text-success me-1"></i>Entrées
                                </span>
                                <span class="fw-bold text-success fs-6">+<?= fmt($totalScolairePayer) ?> $</span>
                            </div>
                            <div class="col-4 border-end">
                                <span class="text-muted fs-7 d-block mb-1">
                                    <i class="mdi mdi-minus-circle text-danger me-1"></i>Dépenses
                                </span>
                                <span class="fw-bold text-danger fs-6">-<?= fmt($totalDepensesScolaire) ?> $</span>
                            </div>
                            <div class="col-4">
                                <span class="text-muted fs-7 d-block mb-1">
                                    <i class="mdi mdi-chart-pie me-1"></i>Solde Net
                                </span>
                                <span
                                    class="fw-bold fs-6 <?= ($netScolaire < 0 ? 'text-danger' : 'text-dark') ?>"><?= fmt($netScolaire) ?>
                                    $</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carte Caisse Frais Connexes -->
                <div class="col-lg-6">
                    <div class="caisse-box p-4 shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="kpi-icon bg-soft-info">
                                    <i class="mdi mdi-receipt fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Caisse Frais Connexes</h5>
                                    <span class="text-muted fs-7">Frais divers et accessoires</span>
                                </div>
                            </div>
                            <a href="ventiallation_caisse_frais_connexe.php"
                                class="btn btn-success btn-sm rounded-pill px-3">
                                Consulter <i class="mdi mdi-arrow-right ms-1"></i>
                            </a>
                        </div>

                        <div class="row text-center g-2 pt-3 border-top">
                            <div class="col-4 border-end">
                                <span class="text-muted fs-7 d-block mb-1">
                                    <i class="mdi mdi-plus-circle text-success me-1"></i>Entrées
                                </span>
                                <span class="fw-bold text-success fs-6">+<?= fmt($totalConnexePayer) ?> $</span>
                            </div>
                            <div class="col-4 border-end">
                                <span class="text-muted fs-7 d-block mb-1">
                                    <i class="mdi mdi-minus-circle text-danger me-1"></i>Dépenses
                                </span>
                                <span class="fw-bold text-danger fs-6">-<?= fmt($totalDepensesConnexe) ?> $</span>
                            </div>
                            <div class="col-4">
                                <span class="text-muted fs-7 d-block mb-1">
                                    <i class="mdi mdi-chart-pie me-1"></i>Solde Net
                                </span>
                                <span
                                    class="fw-bold fs-6 <?= ($netConnexe < 0 ? 'text-danger' : 'text-dark') ?>"><?= fmt($netConnexe) ?>
                                    $</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Remarque sur les dépenses hors-caisses -->
            <?php if ($totalDepensesAutres > 0): ?>
            <div
                class="p-3 bg-white border-start border-4 border-warning shadow-sm rounded-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="mdi mdi-alert text-warning fs-4"></i>
                    <span class="fs-7 text-dark">
                        <strong>Dépenses hors-catégorie :</strong> Un montant de
                        <strong><?= fmt($totalDepensesAutres) ?> $</strong> n'a pas été affecté directement aux frais
                        scolaires ni connexes.
                    </span>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <?php require_once('../../layouts/constants/footer.php'); ?>
    </div>
</body>
<html>