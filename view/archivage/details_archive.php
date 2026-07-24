<?php
session_start();

require_once ('../../layouts/constants/head.php'); 
require_once ('../../layouts/navbar/navbar.php');

require_once('../../webapp/database/config.php');
mysqli_set_charset($con, 'utf8mb4');

// Vérification de la présence de l'ID d'archive
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Identifiant d'archive manquant.";
    header("Location: ../annee_scolaire/");
    exit;
}

$archive_id = (int)$_GET['id'];

/* =====================================================
   1. CHARGEMENT DES DONNÉES DE SYNTHÈSE GLOBALE
===================================================== */
$query_info = "
    SELECT s.annee_scolaire, s.commentaire, s.date_archivage, st.* 
    FROM archive_session s 
    LEFT JOIN archive_statistiques st ON s.id = st.archive_id 
    WHERE s.id = ? LIMIT 1
";

$stmt = mysqli_prepare($con, $query_info);

// Vérification si la préparation a échoué
if ($stmt === false) {
    die("<div class='container my-5'><div class='alert alert-danger shadow-sm'>
            <h4 class='fw-bold'><i class='fas fa-exclamation-triangle me-2'></i>Erreur de préparation SQL :</h4>
            <hr>
            <p class='mb-1'>" . mysqli_error($con) . "</p>
            <small class='text-muted'>Vérifiez que les tables <code>archive_session</code> et <code>archive_statistiques</code> existent.</small>
         </div></div>");
}

mysqli_stmt_bind_param($stmt, "i", $archive_id);
mysqli_stmt_execute($stmt);
$info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$info) {
    echo "<div class='container my-5'><div class='alert alert-warning shadow-sm'><i class='fas fa-search me-2'></i>Archive introuvable.</div></div>";
    exit;
}

$annee_label = htmlspecialchars($info['annee_scolaire']);
$total_recettes = (float)$info['encaissement_scolaire'] + (float)$info['encaissement_connexe'];

/* =====================================================
   2. REQUÊTES POUR LES ONGLET COMPOSANTS (SÉCURISÉES)
===================================================== */
function executer_requete_archive($con, $sql) {
    $result = mysqli_query($con, $sql);
    if ($result === false) {
        die("<div class='container my-5'><div class='alert alert-danger shadow-sm'>
                <h4 class='fw-bold'><i class='fas fa-database me-2'></i>Erreur dans la structure de l'archive :</h4>
                <hr>
                <p><b>Requête :</b> <code>" . htmlspecialchars($sql) . "</code></p>
                <p class='mb-0'><b>Message :</b> " . mysqli_error($con) . "</p>
             </div></div>");
    }
    return $result;
}

// Classes & répartition des effectifs
$classes = executer_requete_archive($con, "SELECT * FROM archive_classe WHERE archive_id = $archive_id ORDER BY description ASC");

// Registre des élèves
$eleves = executer_requete_archive($con, "SELECT * FROM archive_eleve WHERE archive_id = $archive_id ORDER BY nom ASC, prenom ASC LIMIT 300");

// Registre des ménages / parents
$menages = executer_requete_archive($con, "SELECT * FROM archive_menage WHERE archive_id = $archive_id ORDER BY noms ASC LIMIT 200");

// Grand livre des dépenses validées
$depenses = executer_requete_archive($con, "SELECT * FROM archive_depenses WHERE archive_id = $archive_id ORDER BY id DESC");

// Journal des paiements scolaires principaux
$paiements = executer_requete_archive($con, "SELECT * FROM archive_paiement WHERE archive_id = $archive_id ORDER BY dateCreated DESC LIMIT 200");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archivage des données - <?= $annee_label ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .card-kpi {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card-kpi:hover {
        transform: translateY(-2px);
    }

    .icon-shape {
        width: 48px;
        height: 48px;
        background-color: rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        border-bottom: 3px solid transparent;
        padding: 12px 20px;
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd !important;
        border-bottom-color: #0d6efd;
        background: transparent;
    }

    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .nav-tabs {
            display: none !important;
        }

        .tab-pane {
            display: block !important;
            opacity: 1 !important;
            page-break-after: always;
        }

        body {
            background-color: #fff !important;
        }
    }
    </style>
</head>

<body class="bg-light">

    <div class="container-fluid py-4 px-md-5">

        <!-- Fil d'Ariane & En-tête -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 pb-3 border-bottom no-print">
            <div>
                <h2 class="fw-bold text-dark m-0 d-flex align-items-center">
                    <i class="fas fa-archive text-warning me-3"></i>Archives Historiques <?= $annee_label ?>
                </h2>
                <p class="text-muted small mb-0 mt-1">
                    <i class="far fa-calendar-alt me-1"></i> Gélifié et sécurisé le
                    <?= date('d/m/Y à H:i', strtotime($info['date_archivage'])) ?>
                </p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <button class="btn btn-primary shadow-sm d-flex align-items-center" onclick="window.print()">
                    <i class="fas fa-print me-2"></i> Imprimer le Bilan
                </button>
                <a href="../annee_scolaire/" class="btn btn-dark d-flex align-items-center">
                    <i class="fas fa-arrow-left me-2"></i> Retour
                </a>
            </div>
        </div>

        <!-- Zone d'impression uniquement -->
        <div class="d-none d-print-block mb-4 text-center border-bottom pb-3">
            <h3>RAPPORT DE CLÔTURE HISTORIQUE</h3>
            <h2>ANNÉE SCOLAIRE <?= $annee_label ?></h2>
            <p class="text-muted">Document officiel de synthèse - Données immuables</p>
        </div>

        <!-- Cartes KPI de synthèse financière et humaine -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card bg-white border-0 shadow-sm p-3 h-100 card-kpi">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small text-uppercase fw-bold">Effectif Global</span>
                        <div class="icon-shape text-primary"><i class="fas fa-user-graduate fs-5"></i></div>
                    </div>
                    <div class="fs-3 fw-bold text-dark"><?= (int)$info['total_eleves'] ?> <span
                            class="fs-6 text-muted fw-normal">élèves</span></div>
                    <small class="text-muted mt-1"><i class="fas fa-users me-1"></i><?= (int)$info['total_menages'] ?>
                        Ménages (Familles)</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card bg-white border-0 shadow-sm p-3 h-100 card-kpi">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small text-uppercase fw-bold">Recettes Totales</span>
                        <div class="icon-shape text-success"><i class="fas fa-wallet fs-5"></i></div>
                    </div>
                    <div class="fs-3 fw-bold text-success"><?= number_format($total_recettes, 2, ',', ' ') ?> $</div>
                    <small class="text-muted text-truncate mt-1">Scolaire:
                        <?= number_format($info['encaissement_scolaire'], 0, ',', ' ') ?>$ | Div:
                        <?= number_format($info['encaissement_connexe'], 0, ',', ' ') ?>$</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card bg-white border-0 shadow-sm p-3 h-100 card-kpi">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small text-uppercase fw-bold">Flux Dépenses</span>
                        <div class="icon-shape text-danger"><i class="fas fa-receipt fs-5"></i></div>
                    </div>
                    <div class="fs-3 fw-bold text-danger"><?= number_format($info['depenses'], 2, ',', ' ') ?> $</div>
                    <small class="text-muted mt-1">Total décaissé sur l'exercice</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card bg-white border-0 shadow-sm p-3 h-100 card-kpi">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small text-uppercase fw-bold">Résultat Net</span>
                        <div class="icon-shape <?= $info['benefice'] >= 0 ? 'text-success' : 'text-danger' ?>"><i
                                class="fas fa-chart-line fs-5"></i></div>
                    </div>
                    <?php $badge_b = $info['benefice'] >= 0 ? 'text-success' : 'text-danger'; ?>
                    <div class="fs-3 fw-bold <?= $badge_b ?>"><?= number_format($info['benefice'], 2, ',', ' ') ?> $
                    </div>
                    <small class="text-muted mt-1">Balance comptable finale</small>
                </div>
            </div>
        </div>

        <!-- Bloc d'affichage par onglets -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-2 px-4 no-print">
                <ul class="nav nav-tabs card-header-tabs" id="archiveTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold" id="classes-tab" data-bs-toggle="tab"
                            data-bs-target="#classes" type="button" role="tab"><i
                                class="fas fa-school me-2 text-primary"></i>Classes</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="eleves-tab" data-bs-toggle="tab"
                            data-bs-target="#eleves" type="button" role="tab"><i
                                class="fas fa-id-card me-2 text-success"></i>Élèves</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="menages-tab" data-bs-toggle="tab"
                            data-bs-target="#menages" type="button" role="tab"><i
                                class="fas fa-home me-2 text-info"></i>Ménages</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="compta-tab" data-bs-toggle="tab"
                            data-bs-target="#compta" type="button" role="tab"><i
                                class="fas fa-file-invoice-dollar me-2 text-warning"></i>Recettes</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="depenses-tab" data-bs-toggle="tab"
                            data-bs-target="#depenses" type="button" role="tab"><i
                                class="fas fa-cash-register me-2 text-danger"></i>Dépenses</button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4 bg-white rounded-bottom">
                <div class="tab-content" id="archiveTabsContent">

                    <!-- 1. Onglet : Classes -->
                    <div class="tab-pane fade show active" id="classes" role="tabpanel">
                        <div class="d-flex align-items-center mb-3">
                            <h5 class="fw-bold text-dark m-0"><i
                                    class="fas fa-table-list me-2 text-primary"></i>Répartition démographique</h5>
                        </div>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
                            <?php if (mysqli_num_rows($classes) === 0): ?>
                            <div class="col-12">
                                <p class="text-muted my-3">Aucune donnée trouvée.</p>
                            </div>
                            <?php else: ?>
                            <?php while ($cl = mysqli_fetch_assoc($classes)): ?>
                            <div class="col">
                                <div
                                    class="p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                                    <span
                                        class="fw-bold text-secondary"><?= htmlspecialchars($cl['description']) ?></span>
                                    <span class="badge bg-primary px-2.5 py-1.5 rounded fs-6"><?= $cl['total_eleves'] ?>
                                        <small class="fw-normal">Élèves</small></span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 2. Onglet : Registre Élèves -->
                    <div class="tab-pane fade" id="eleves" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark m-0"><i
                                    class="fas fa-users-viewfinder me-2 text-success"></i>Répertoire Nominatif</h5>
                            <span class="badge bg-light text-secondary border">Top 300 entrées</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle text-nowrap border">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nom Complet</th>
                                        <th class="text-center">Genre</th>
                                        <th>Lieu & Date Naissance</th>
                                        <th>Classe</th>
                                        <th>Code Unique Tuteur</th>
                                        <th class="text-center">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($el = mysqli_fetch_assoc($eleves)): ?>
                                    <tr>
                                        <td class="fw-bold text-dark">
                                            <?= htmlspecialchars($el['nom'] . ' ' . $el['postnom'] . ' ' . $el['prenom']) ?>
                                        </td>
                                        <td class="text-center"><span
                                                class="badge bg-light text-dark border px-2"><?= htmlspecialchars($el['genre']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($el['lieu']) . ', le ' . date('d/m/Y', strtotime($el['dateDeNaissance'])) ?>
                                        </td>
                                        <td><span
                                                class="badge bg-secondary-subtle text-secondary border px-2 fw-semibold"><?= htmlspecialchars($el['classe']) ?></span>
                                        </td>
                                        <td><code><?= htmlspecialchars($el['menage']) ?></code></td>
                                        <td class="text-center">
                                            <span
                                                class="badge rounded-pill <?= $el['STATUS'] === 'actif' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?> px-3">
                                                <?= htmlspecialchars($el['STATUS']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 3. Onglet : Registre Ménages -->
                    <div class="tab-pane fade" id="menages" role="tabpanel">
                        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-address-book me-2 text-info"></i>Fiches
                            Responsables Famille</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle border">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Responsable de Famille</th>
                                        <th>Téléphone</th>
                                        <th>Adresse Domicile</th>
                                        <th class="text-end">Frais Fixés ($)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($m = mysqli_fetch_assoc($menages)): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= htmlspecialchars($m['noms']) ?></td>
                                        <td><i class="fas fa-phone-alt text-muted me-1 small"></i>
                                            <?= htmlspecialchars($m['telephone']) ?></td>
                                        <td><small
                                                class="text-secondary"><?= htmlspecialchars("Av. " . $m['avenue'] . ", N° " . $m['numero'] . ", Q/ " . $m['quartier'] . ", C/ " . $m['commune']) ?></small>
                                        </td>
                                        <td class="fw-bold text-end text-dark">
                                            <?= number_format($m['montantAPayer'], 2, ',', ' ') ?> $</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 4. Onglet : Recettes (Paiements) -->
                    <div class="tab-pane fade" id="compta" role="tabpanel">
                        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-receipt me-2 text-warning"></i>Journal
                            Général des Encaissements</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle border">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code Ménage</th>
                                        <th class="text-end">Frais Théoriques</th>
                                        <th class="text-end">Frais Réglés</th>
                                        <th class="text-end">Solde / Reste</th>
                                        <th>Date Encaissement</th>
                                        <th>Observations</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($paiements) === 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center p-4 text-muted">Aucun historique de paiement
                                            scellé.</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php while ($p = mysqli_fetch_assoc($paiements)): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($p['menage']) ?></code></td>
                                        <td class="text-end text-muted">
                                            <?= number_format($p['montantAPayer'], 2, ',', ' ') ?> $</td>
                                        <td class="text-end fw-bold text-success">
                                            <?= number_format($p['montantPayer'], 2, ',', ' ') ?> $</td>
                                        <td class="text-end fw-semibold text-danger">
                                            <?= number_format($p['resteAPayer'], 2, ',', ' ') ?> $</td>
                                        <td><small class="text-dark"><i
                                                    class="far fa-clock me-1 text-muted"></i><?= date('d/m/Y H:i', strtotime($p['dateCreated'])) ?></small>
                                        </td>
                                        <td><span
                                                class="text-muted small"><?= htmlspecialchars($p['observation']) ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 5. Onglet : Dépenses -->
                    <div class="tab-pane fade" id="depenses" role="tabpanel">
                        <h5 class="fw-bold text-dark mb-3"><i
                                class="fas fa-hand-holding-dollar me-2 text-danger"></i>Journal de Caisse (Sorties)</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle border">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Bénéficiaire</th>
                                        <th>Réf & N°</th>
                                        <th>Description / Motif</th>
                                        <th class="text-end">Montant Décaissé</th>
                                        <th>Date Sortie</th>
                                        <th>Ordonnateur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($depenses) === 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center p-4 text-muted">Aucune sortie de caisse
                                            enregistrée.</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php while ($dp = mysqli_fetch_assoc($depenses)): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($dp['beneficiaire']) ?></td>
                                        <td><span
                                                class="badge bg-light text-secondary border"><?= htmlspecialchars($dp['reference']) ?>
                                                N°<?= (int)$dp['numero_reference'] ?></span></td>
                                        <td><small class="text-dark"><?= htmlspecialchars($dp['description']) ?></small>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            <?= number_format($dp['montant'], 2, ',', ' ') ?> $</td>
                                        <td><small><?= date('d/m/Y', strtotime($dp['dateCreaty'])) ?></small></td>
                                        <td><code class="text-muted"><?= htmlspecialchars($dp['createdby']) ?></code>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once ('../../layouts/constants/footer.php'); ?>
</body>

</html>