<?php declare(strict_types=1); ?>

<!-- partial:partials/_sidebar.php -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <div class="sidebar-brand-wrapper d-lg-flex align-items-center justify-content-center fixed-top">
        <a class="sidebar-brand brand-logo" href="index.php">
            <img src="../../assets/images/logo-mini.svg" alt="logo" />
        </a>
        <a class="sidebar-brand brand-logo-mini" href="index.php">
            <img src="../../assets/images/logo-mini.svg" alt="logo" />
        </a>
    </div>
</nav>
<!-- partial -->

<style>
.card:hover {
    transform: translateY(-3px);
    transition: 0.2s ease-in-out;
}

.card {
    transition: 0.2s ease-in-out;
}
</style>
<!-- partial:partials/_navbar.php -->
<nav class="navbar p-0 fixed-top d-flex flex-row">
    <div class="navbar-brand-wrapper d-flex d-lg-none align-items-center justify-content-center">
        <a class="navbar-brand brand-logo-mini" href="index.php">
            <img src="../../assets/images/logo-mini.svg" alt="logo" />
        </a>
    </div>
    <div class="navbar-menu-wrapper flex-grow d-flex align-items-stretch">
        <button class="d-none navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
        </button>

        <ul class="navbar-nav w-100">
            <li class="nav-item menu-items">
                <a class="nav-link" href="../dashboard/">
                    <span class="menu-icon"><i class="mdi mdi-home"></i></span>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>

            <li class="nav-item menu-items">
                <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false"
                    aria-controls="ui-basic">
                    <span class="menu-icon"><i class="fa fa-users"></i></span>
                    <span class="menu-title">Gestion des élèves</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-basic">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="../eleve/">Elèves</a></li>
                        <li class="nav-item"><a class="nav-link" href="../menage/">Dossier famille</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">...</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item menu-items">
                <!-- <a class="nav-link" href="../paiement-divers-frais/"> -->
                <a class="nav-link" href="../encaissement/">
                    <span class="menu-icon"><i class="fa fa-money"></i></span>
                    <span class="menu-title">Scolarité</span>
                </a>
            </li>

            <li class="nav-item menu-items">
                <a class="nav-link" href="../depense/">
                    <span class="menu-icon"><i class="fa fa-user"></i></span>
                    <span class="menu-title">Bon de sortie</span>
                </a>
            </li>

            <li class="nav-item menu-items">
                <a class="nav-link" href="../setting/">
                    <span class="menu-icon"><i class="mdi mdi-view-grid"></i></span>
                    <span class="menu-title">Settings</span>
                </a>
            </li>
        </ul>

        <!-- Bouton Déconnexion (ajuste le chemin si besoin) -->
        <ul class="navbar-nav navbar-nav-right ms-auto">
            <li class="nav-item">
                <a class="nav-link text-danger fw-semibold" href="../../auth/logout.php">
                    <i class="mdi mdi-logout me-1"></i> Déconnexion
                </a>
            </li>
        </ul>

        <button class="d-none navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-toggle="offcanvas">
            <span class="mdi mdi-format-line-spacing"></span>
        </button>
    </div>
</nav>
<!-- partial -->

<script>
// Anti Back/Forward Cache : si la page revient depuis l'historique, on force le refresh
window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && performance.getEntriesByType)) {
                // Certains navigateurs remettent une page du BFCache : on recharge
                if (event.persisted) location.reload();
            });
</script>