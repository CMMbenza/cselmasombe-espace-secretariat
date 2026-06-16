<?php
    require_once ('../../layouts/constants/head.php');
    require_once ('../../webapp/service/dashboard.service.php'); // Doit fournir $rst (mysqli_result) avec: id, noms, nbreEnfant, montantAPayer, telephone, avenue, numero, quartier, commune
    require_once ('../../layouts/navbar/navbar.php');
?>

<style>
/* ——— Style minimal propre ——— */
.card {
    border: 0;
    border-radius: 18px;
    box-shadow: 0 4px 20px rgba(16, 24, 40, .06);
}

.kpi .icon {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef2ff;
    color: #4f46e5;
}

.kpi h2 {
    margin: 0;
    font-weight: 800;
    font-size: 28px;
}

.kpi small {
    color: #6b7280
}

.text-muted-2 {
    color: #6b7280;
}

.badge-soft {
    background: #eef2ff;
    color: #3730a3;
}

.table thead th {
    color: #6b7280;
    font-weight: 600;
    letter-spacing: .02em;
}

/* ===== BLOCS INACTIF + TOTAL ===== */
.card .block-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 20px;
}

/* ICON PLUS GRAND */
.icon-box {
    width: 65px;
    height: 65px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    /* 👈 ICON AGRANDI */
}

/* KPI VALUE (CHIFFRE PRINCIPAL) */
.kpi-value {
    font-size: 32px;
    /* 👈 PLUS GRAND */
    font-weight: 900;
    line-height: 1;
}

/* TEXTE SOUS TITRE */
.text-muted-2 {
    font-size: 14px;
    color: #6b7280;
}

/* DETAIL BOX (AGRANDI + PLUS LISIBLE) */
.detail-box {
    border: 1px solid #eee;
    border-radius: 16px;
    padding: 18px;
    background: #fff;
    transition: .2s;
}

.detail-box:hover {
    transform: translateY(-2px);
    border-color: #ddd;
}

/* ICON DANS TEXT (mdi icons) */
.detail-box i {
    font-size: 20px;
    /* 👈 ICON INLINE AGRANDI */
    margin-right: 6px;
}

/* TITRE BLOCS COLORÉS */
.text-danger.block-title {
    color: #dc3545 !important;
}

.text-primary.block-title {
    color: #0d6efd !important;
}

/* forcer les colonnes à avoir la même hauteur */
.equal-row {
    display: flex;
    flex-wrap: wrap;
}

/* chaque colonne prend la même hauteur */
.equal-col {
    display: flex;
}

/* la card remplit toute la hauteur */
.equal-card {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100%;
}

/* contenu interne bien aligné */
.equal-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* option: éviter débordement */
.equal-card {
    min-height: 100%;
}

.equal-card:hover {
    transform: translateY(-3px);
    transition: .2s;
}

.kpi {
    padding: 12px;
    border-radius: 12px;
    transition: 0.2s;
    border: 1px solid #eaeaeab9;
}

.kpi:hover {
    background: #f9fafb;
}

.icon {
    min-width: 55px;
    /* évite que ça bouge */
}
</style>

<div class="main-panel-copy">
    <div class="content-wrapper">

        <!-- Header -->
        <div class="d-none d-flex flex-wrap align-items-end justify-content-between mb-4">
            <div>
                <h3 class="mb-0 fw-bold">Dashboard scolaire</h3>
                <div class="text-muted-2">Période: <?= e($debut) ?> → <?= e($fin) ?> (<?= e($annee_label) ?>)</div>
            </div>
        </div>

        <!-- KPIs : Garçons / Filles / Élèves / Ménages Actif -->
        <div class="row g-3 g-md-4 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <h5 class="text-success">
                                <i class="mdi mdi-alert-circle"></i> Famille actif
                            </h5>
                            <label class="lead">Les familles qui sont actives tout au long de l'année.</label>
                            <!-- Garçons -->
                            <div class="col-12 col-md-3">
                                <div class="kpi d-flex align-items-center gap-3">
                                    <div class="icon">
                                        <i class="mdi mdi-face-man fs-3"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted-2 mb-1">Nbre des garçons</div>
                                        <h2><?= e($nb_garcons) ?></h2>
                                    </div>
                                </div>
                            </div>

                            <!-- Filles -->
                            <div class="col-12 col-md-3">
                                <div class="kpi d-flex align-items-center gap-3">
                                    <div class="icon" style="background:#ffe9f4;color:#e83e8c">
                                        <i class="mdi mdi-face-woman fs-3"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted-2 mb-1">Nbre des filles</div>
                                        <h2><?= e($nb_filles) ?></h2>
                                    </div>
                                </div>
                            </div>

                            <!-- Élèves -->
                            <div class="col-12 col-md-3">
                                <div class="kpi d-flex align-items-center gap-3">
                                    <div class="icon" style="background:#e9fbff;color:#17a2b8">
                                        <i class="mdi mdi-school fs-3"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted-2 mb-1">Nbre des élèves</div>
                                        <h2><?= e($total_eleves) ?></h2>
                                    </div>
                                </div>
                            </div>

                            <!-- Ménages -->
                            <div class="col-12 col-md-3">
                                <div class="kpi d-flex align-items-center gap-3">
                                    <div class="icon" style="background:#fff6e0;color:#ff9800">
                                        <i class="mdi mdi-home-group fs-3"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted-2 mb-1">Nbre des ménages</div>
                                        <h2><?= e($nb_menages) ?></h2>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row equal-row g-3 mb-3">
            <!-- INACTIF -->
            <div class="col-md-6 equal-col">
                <div class="card equal-card">
                    <div class="card-body">
                        <h5 class="text-danger">
                            <i class="mdi mdi-alert-circle"></i> Famille Inactif
                        </h5>
                        <label class="lead">Les familles ont été actives tout au long de l'année, mais elles ont fini
                            par abandonner.</label>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="detail-box">
                                    <div class="text-muted-2">Garçons</div>
                                    <div class="kpi-value text-dark">
                                        <i class="mdi mdi-face-man text-primary"></i>
                                        <?= e($nb_garcons_inactif) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="detail-box">
                                    <div class="text-muted-2">Filles</div>
                                    <div class="kpi-value text-dark">
                                        <i class="mdi mdi-face-woman" style="color:#e83e8c"></i>
                                        <?= e($nb_filles_inactif) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="detail-box">
                                    <div class="text-muted-2">Élèves</div>
                                    <div class="kpi-value text-dark">
                                        <i class="mdi mdi-school text-info"></i>
                                        <?= e($total_eleves_inactif) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="detail-box">
                                    <div class="text-muted-2">Ménages</div>
                                    <div class="kpi-value text-dark">
                                        <i class="mdi mdi-home-group text-warning"></i>
                                        <?= e($nb_menages_inactif) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 equal-col">
                <div class="card equal-card">
                    <div class="card-body">
                        <h5 class="text-primary">
                            <i class="mdi mdi-calendar"></i> Total Annuel (Actif/Inactif)
                        </h5>
                        <label class="lead">Toutes les familles actives ou abandonnées au cours de l'année.</label>
                        <div class="row g-3">

                            <div class="col-md-3">
                                <div class="detail-box">
                                    <div class="text-muted-2">Garçons</div>
                                    <div class="kpi-value">
                                        <i class="mdi mdi-face-man text-primary"></i>
                                        <?= e($nb_garcons_TotalAnnuel) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="detail-box">
                                    <div class="text-muted-2">Filles</div>
                                    <div class="kpi-value">
                                        <i class="mdi mdi-face-woman" style="color:#e83e8c"></i>
                                        <?= e($nb_filles_TotalAnnuel) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="detail-box">
                                    <div class="text-muted-2">Élèves</div>
                                    <div class="kpi-value">
                                        <i class="mdi mdi-school text-info"></i>
                                        <?= e($total_eleves_TotalAnnuel) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="detail-box">
                                    <div class="text-muted-2">Ménages</div>
                                    <div class="kpi-value">
                                        <i class="mdi mdi-home-group text-warning"></i>
                                        <?= e($nb_menages_TotalAnnuel) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs FINANCIERS -->
        <div class="d-none row g-3 g-md-4 mb-2">
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body kpi d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted-2 mb-1">Encaissements (total)</div>
                            <h2><?= moneyf($encaissements_total) ?></h2>
                        </div>
                        <div class="icon"><i class="mdi mdi-cash-plus fs-4"></i></div>
                    </div>
                </div>
            </div>
            <div class="d-none col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body kpi d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted-2 mb-1">Décaissements (total)</div>
                            <h2><?= moneyf($decaissements_total) ?></h2>
                        </div>
                        <div class="icon" style="background:#fff1f2;color:#be123c"><i
                                class="mdi mdi-cash-minus fs-4"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body kpi d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted-2 mb-1">Solde (Enc. − Déc.)</div>
                            <h2 class="<?= $solde_total>=0?'text-success':'text-danger' ?>">
                                <?= moneyf($solde_total) ?>
                            </h2>
                        </div>
                        <div class="icon" style="background:#ecfeff;color:#0e7490"><i
                                class="mdi mdi-scale-balance fs-4"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="row g-3 g-md-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0">Encaissements vs Décaissements</h5>
                            <span class="text-muted-2 small"><?= e($annee_label) ?></span>
                        </div>
                        <canvas id="fluxChart" height="110"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables -->
        <div class="row g-3 g-md-4">
            <!-- Derniers paiements (scolaires) -->
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="mb-0">Derniers paiements (scolaires)</h6>
                            <span class="badge badge-soft rounded-pill">Frais Scolaire</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ménage</th>
                                        <th class="text-end">Montant payé</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!$last_paiements): ?>
                                    <tr>
                                        <td colspan="4" class="text-muted">Aucun paiement trouvé.</td>
                                    </tr>
                                    <?php else: foreach($last_paiements as $p): ?>
                                    <tr>
                                        <td><?= (int)$p['id'] ?></td>
                                        <td><?= e($p['noms'] ?: ('ID '.$p['menage'])) ?></td>
                                        <td class="text-end fw-semibold"><?= moneyf($p['montantPayer']) ?>$</td>
                                        <td><span class="text-muted-2"><?= e($p['dateCreated']) ?></span></td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Derniers paiements (divers) -->
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="mb-0">Derniers paiements (divers)</h6>
                            <span class="badge badge-soft rounded-pill">Frais Connexe</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ménage</th>
                                        <th class="d-none">Type de frais</th>
                                        <th class="text-end">Montant payé</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!$last_paiements_div): ?>
                                    <tr>
                                        <td colspan="5" class="text-muted">Aucun paiement divers trouvé.</td>
                                    </tr>
                                    <?php else: foreach($last_paiements_div as $p): ?>
                                    <tr>
                                        <td><?= (int)$p['id'] ?></td>
                                        <td><?= e($p['noms'] ?: ('ID '.$p['menage'])) ?></td>
                                        <td class="d-none"><?= e($p['type_frais']) ?></td>
                                        <td class="text-end fw-semibold"><?= moneyf($p['montantPayer']) ?>$</td>
                                        <td><span class="text-muted-2"><?= e($p['dateCreated']) ?></span></td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dernières inscriptions (élèves + ménage) -->
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="mb-0">Dernières inscriptions (avec ménage)</h6>
                            <span class="badge badge-soft rounded-pill">élèves</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Élève</th>
                                        <th>Genre</th>
                                        <th>Ménage</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!$last_eleves): ?>
                                    <tr>
                                        <td colspan="5" class="text-muted">Aucune inscription trouvée.</td>
                                    </tr>
                                    <?php else: foreach($last_eleves as $el): ?>
                                    <tr>
                                        <td><?= (int)$el['id'] ?></td>
                                        <td class="fw-semibold">
                                            <?= e($el['nom'].' '.$el['postnom'].' '.$el['prenom']) ?></td>
                                        <td><?= $el['genre']==='M'?'Garçon':'Fille' ?></td>
                                        <td><?= e($el['menage_noms'] ?: 'ID '.$el['menage_id']) ?></td>
                                        <td><span class="text-muted-2"><?= e($el['dateCreated']) ?></span></td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /row tables -->

    </div><!-- /content-wrapper -->

    <?php require_once ('../../layouts/constants/footer.php'); ?>
</div><!-- /main-panel-copy -->

<!-- Chart.js (si non déjà chargé par ton layout) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const labels = <?= $labels_js ?>;
const encData = <?= $enc_js ?>;
const decData = <?= $dec_js ?>;

const ctx = document.getElementById('fluxChart');
if (ctx) {
    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                    label: 'Encaissements',
                    data: encData,
                    tension: .25
                },
                {
                    label: 'Décaissements',
                    data: decData,
                    tension: .25
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
</script>