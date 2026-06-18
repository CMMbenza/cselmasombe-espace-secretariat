<?php
require_once('../../layouts/constants/head.php');
require_once('../../layouts/navbar/navbar.php');
?>

<style>
.main-panel-copy {
    background: #f4f6f9;
    min-height: 100vh;
}

/* CARD TITLE */
.settings-card .card-title {
    display: flex;
    align-items: center;
    gap: .6rem;
}

/* ICON BASE */
.menu-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-right: .6rem;
    flex-shrink: 0;
}

/* MODULE COLORS */
.finances {
    background: #e8f5e9;
    color: #2e7d32;
}

.report {
    background: #ffebee;
    color: #c62828;
}

.school {
    background: #e3f2fd;
    color: #1565c0;
}

.chart {
    background: #fff3e0;
    color: #ef6c00;
}

.security {
    background: #f3e5f5;
    color: #6a1b9a;
}

.calendar {
    background: #e0f2f1;
    color: #00695c;
}

.archive {
    background: #eceff1;
    color: #37474f;
}

/* BUTTON STYLE */
.quick-actions .btn {
    display: flex;
    align-items: center;
    text-align: left;
    padding: 16px;
    border-radius: 12px;
    transition: all 0.25s ease;
    font-weight: 500;
}

.quick-actions .btn:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

/* RESPONSIVE GRID IMPROVEMENT */
.quick-actions .col-12,
.quick-actions .col-md-6,
.quick-actions .col-lg-3 {
    display: flex;
}

/* CARD */
.settings-card {
    border-radius: 15px;
}

/* DIVIDER */
.divider-soft {
    border: 0;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
    margin: 15px 0 25px;
}
</style>

<div class="main-panel-copy">
    <div class="content-wrapper container-fluid py-4">

        <div class="row justify-content-center">
            <div class="col-12 col-xl-12">

                <div class="card shadow-sm settings-card">
                    <div class="card-body">

                        <!-- HEADER -->
                        <h4 class="card-title">
                            <span class="menu-icon school">
                                <i class="mdi mdi-view-grid"></i>
                            </span>
                            Settings
                        </h4>

                        <p class="text-muted mb-0">Accès rapide aux modules du système</p>
                        <hr>

                        <!-- LIGNE 1 -->
                        <div class="row quick-actions g-3">

                            <div class="col-12 col-md-6 col-lg-3">
                                <a class="btn btn-outline-success w-100" href="../balance/">
                                    <span class="menu-icon finances"><i class="mdi mdi-cash"></i></span>
                                    Caisse centrale
                                </a>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <a class="btn btn-outline-primary w-100" href="../caisse/">
                                    <span class="menu-icon finances"><i class="mdi mdi-wallet"></i></span>
                                    Caisse scolaire / connexes
                                </a>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <a class="btn btn-outline-danger w-100" href="../report/">
                                    <span class="menu-icon report"><i class="mdi mdi-file-chart"></i></span>
                                    Rapports
                                </a>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <a class="btn btn-outline-warning w-100"
                                    href="../ventilation_de_caisse/ventiallation_caisse_frais_scolaire.php">
                                    <span class="menu-icon chart"><i class="mdi mdi-chart-pie"></i></span>
                                    Ventilation FS/FC
                                </a>
                            </div>

                        </div>

                        <!-- LIGNE 2 -->
                        <div class="row quick-actions g-3 mt-2">

                            <div class="col-12 col-md-6 col-lg-3">
                                <a class="btn btn-outline-warning w-100" href="../statisque_inventaire">
                                    <span class="menu-icon chart"><i class="mdi mdi-chart-donut"></i></span>
                                    Statisque inventaire
                                </a>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <a class="btn btn-outline-danger w-100" href="../controle/">
                                    <span class="menu-icon security"><i class="mdi mdi-shield-check"></i></span>
                                    Contrôle frais FS/FC
                                </a>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <a class="btn btn-outline-primary w-100" href="../classe/">
                                    <span class="menu-icon school"><i class="mdi mdi-school"></i></span>
                                    Classes / cycles
                                </a>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <a class="btn btn-outline-warning w-100" href="../fixation_frais/">
                                    <span class="menu-icon finances"><i class="mdi mdi-currency-usd"></i></span>
                                    Fixation frais
                                </a>
                            </div>

                        </div>

                        <!-- LIGNE 3 -->
                        <div class="row quick-actions g-3 mt-2">

                            <div class="col-12 col-md-6 col-lg-3">
                                <a class="btn btn-dark w-100" href="../prestation/">
                                    <span class="menu-icon calendar"><i class="mdi mdi-calendar-check"></i></span>
                                    Présences
                                </a>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <a class="btn btn-danger w-100" href="../annee_scolaire/">
                                    <span class="menu-icon calendar"><i class="mdi mdi-calendar"></i></span>
                                    Année scolaire
                                </a>
                            </div>

                            <!-- <div class="col-12 col-md-6 col-lg-3">
                                <a class="btn btn-secondary w-100" href="../archivage/">
                                    <span class="menu-icon archive"><i class="mdi mdi-archive"></i></span>
                                    Archivage des données
                                </a>
                            </div> -->

                        </div>

                        <hr>

                        <!-- LOGOUT -->
                        <div class="row mt-4">
                            <div class="col-12 col-md-4">
                                <a class="btn btn-danger w-100" href="../../authenticated/login/logout.php">
                                    <span class="menu-icon report"><i class="mdi mdi-logout"></i></span>
                                    Quitter l'application
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once('../../layouts/constants/footer.php'); ?>