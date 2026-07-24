<?php
require_once ('../../layouts/constants/head.php');  
require_once ('../../layouts/navbar/navbar.php');
require_once ('../../webapp/database/dbcongig.php');
require_once ('../../webapp/service/annee_scolaire.encours.php');

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// Flash helpers
function set_flash(string $type, string $msg): void { $_SESSION['flash'] = ['type'=>$type,'msg'=>$msg]; }
function get_flash(): ?array { if (!empty($_SESSION['flash'])) { $f=$_SESSION['flash']; unset($_SESSION['flash']); return $f; } return null; }

// CSRF helpers
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
function csrf_token(): string { return $_SESSION['csrf_token'] ?? ''; }

// Liste des ménages
$sqlMenages = "SELECT id, noms FROM menage WHERE status ='actif' ORDER BY noms ASC";
$resMenages = $conn->query($sqlMenages);
$menages = [];
while ($m = $resMenages->fetch_assoc()) { $menages[] = $m; }

// Liste unique des frais divers (Hors 'frais scolaire')
$sqlFrais = "SELECT MIN(id) AS id, description FROM scolarite WHERE LOWER(description) != 'frais scolaire' GROUP BY description ORDER BY description ASC";
$resFrais = $conn->query($sqlFrais);

// Total jour Scolaire
$sqlTotalScolaire = "SELECT COALESCE(SUM(montantPayer), 0) AS total FROM paiement WHERE DATE(dateCreated) = CURDATE() AND anneeScolaire = ?";
$stmt = $conn->prepare($sqlTotalScolaire);
$stmt->bind_param("s", $annee_scolaire);
$stmt->execute();
$resScolaire = $stmt->get_result();
$rowScolaire = $resScolaire ? $resScolaire->fetch_assoc() : null;
$totalScolaire = $rowScolaire['total'] ?? 0;
$stmt->close();

// Total jour Divers
$sqlTotalDivers = "SELECT COALESCE(SUM(montantPayer), 0) AS total FROM paiement_divers WHERE DATE(dateCreated) = CURDATE()";
$resDivers = $conn->query($sqlTotalDivers);
$totalDivers = $resDivers ? $resDivers->fetch_assoc()['total'] : 0;

// Historique combiné du jour
$sqlJour = "
    (SELECT 'Scolaire' AS nature, p.id, m.noms AS menage, 'Scolarité' AS motif, p.montantPayer, p.resteAPayer, p.dateCreated 
     FROM paiement p JOIN menage m ON m.id = p.menage WHERE DATE(p.dateCreated) = CURDATE() AND m.status ='actif')
    UNION ALL
    (SELECT 'Divers' AS nature, pd.id, m.noms AS menage, COALESCE(s.description, pd.type_frais) AS motif, pd.montantPayer, pd.resteAPayer, pd.dateCreated 
     FROM paiement_divers pd JOIN menage m ON m.id = pd.menage LEFT JOIN scolarite s ON s.id = CAST(pd.type_frais AS UNSIGNED) 
     WHERE DATE(pd.dateCreated) = CURDATE() AND m.status ='actif')
    ORDER BY dateCreated DESC";
$resPaiementsJour = $conn->query($sqlJour);
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
.badge-scolaire {
    background-color: #0d6efd;
    color: #fff;
}

.badge-divers {
    background-color: #fd7e14;
    color: #fff;
}
</style>

<body>
    <div class="main-panel-copy">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12 col-sm-12 grid-margin">
                    <div class="card">
                        <div class="card-body">

                            <!-- Flash Messages -->
                            <?php if ($flash = get_flash()): ?>
                            <div
                                class="alert alert-<?= htmlspecialchars($flash['type']); ?> alert-dismissible fade show">
                                <strong><?= $flash['type']==='success'?'Succès':'Erreur'; ?> :</strong>
                                <?= htmlspecialchars($flash['msg']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>

                            <!-- Entête -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title text-uppercase mb-0">
                                    <button class="btn btn-primary enter-btn me-2"><i
                                            class="mdi mdi-speedometer"></i></button>
                                    Guichet Unique d'Encaissement
                                </h4>
                                <div class="alert alert-dark m-0 p-2 d-flex align-items-center gap-3">
                                    <div>
                                        <small class="d-block text-muted">Recette du Jour</small>
                                        <span id="heading_stats" class="h5 mb-0 fw-bold">
                                            Scolaire: <?= number_format((float)$totalScolaire, 2); ?>$ | Divers:
                                            <?= number_format((float)$totalDivers, 2); ?>$
                                        </span>
                                    </div>
                                    <button class="btn btn-sm btn-outline-light" id="toggleStatsBtn" type="button"><i
                                            class="mdi mdi-eye"></i></button>
                                </div>
                            </div>

                            <!-- Sélection Famille -->
                            <div class="row bg-light p-3 rounded mb-4 border">
                                <div class="col-md-12">
                                    <label for="select_menage_global" class="form-label fw-bold">Sélectionner la Famille
                                        / Ménage :</label>
                                    <select id="select_menage_global" class="form-control form-control-lg">
                                        <option value="" disabled selected>-- Choisissez une famille --</option>
                                        <?php foreach ($menages as $m): ?>
                                        <option value="<?= $m['id']; ?>"><?= htmlspecialchars($m['noms']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- ZONE PAIEMENT -->
                            <div id="zone_paiement" style="display: none;">
                                <ul class="nav nav-tabs mb-3" id="caisseTab" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active fw-bold" id="tab-scolaire" data-bs-toggle="tab"
                                            data-bs-target="#content-scolaire" type="button">
                                            💳 Frais Scolaires
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link fw-bold text-warning" id="tab-divers"
                                            data-bs-toggle="tab" data-bs-target="#content-divers" type="button">
                                            📦 Frais Divers / Supplémentaires
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="caisseTabContent">

                                    <!-- SCOLARITÉ -->
                                    <div class="tab-pane fade show active p-3 border rounded bg-white"
                                        id="content-scolaire">
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Solde Total Dû ($)</label>
                                                <input type="text" id="scolaire_montantAPayer" class="form-control"
                                                    readonly value="0.00">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Montant Perçu Ce Jour ($)</label>
                                                <input type="number" step="0.01" id="scolaire_payer"
                                                    class="form-control form-control-lg border-primary"
                                                    placeholder="0.00">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Nouveau Solde Restant ($)</label>
                                                <input type="text" id="scolaire_rPayer" class="form-control" readonly
                                                    value="0.00">
                                            </div>
                                        </div>

                                        <button type="button" id="btn_save_scolaire"
                                            class="btn btn-primary btn-lg mt-3">Enregistrer Paiement Scolaire</button>
                                    </div>

                                    <!-- DIVERS -->
                                    <div class="tab-pane fade p-3 border rounded bg-white" id="content-divers">
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Type de Frais Divers</label>
                                                <select class="form-control" id="divers_type_frais">
                                                    <option value="" disabled selected>-- Choisir le frais --</option>
                                                    <?php 
                                                    $resFrais->data_seek(0);
                                                    while ($f = $resFrais->fetch_assoc()): 
                                                    ?>
                                                    <option value="<?= $f['id']; ?>"
                                                        data-description="<?= htmlspecialchars($f['description']); ?>">
                                                        <?= htmlspecialchars($f['description']); ?>
                                                    </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Montant Frais ($)</label>
                                                <input type="text" id="divers_montantAPayer" class="form-control"
                                                    readonly value="0.00">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Montant Perçu ($)</label>
                                                <input type="number" step="0.01" id="divers_payer"
                                                    class="form-control form-control-lg border-warning"
                                                    placeholder="0.00">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Solde Reste ($)</label>
                                                <input type="text" id="divers_rPayer" class="form-control" readonly
                                                    value="0.00">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Observation</label>
                                            <textarea id="divers_observation" class="form-control" rows="2"></textarea>
                                        </div>

                                        <div id="container_enfants_divers" class="mb-4"></div>

                                        <button type="button" id="btn_save_divers"
                                            class="btn btn-warning text-white btn-lg">Enregistrer Paiement
                                            Divers</button>
                                    </div>

                                </div>
                            </div>

                            <!-- HISTORIQUE -->
                            <?php
                                // Initialisation des variables de calcul
                                $totalScolaire = 0;
                                $totalDivers   = 0;
                                $rows = [];

                                if ($resPaiementsJour && $resPaiementsJour->num_rows > 0) {
                                    while ($row = $resPaiementsJour->fetch_assoc()) {
                                        $rows[] = $row;
                                        if ($row['nature'] === 'Scolaire') {
                                            $totalScolaire += (float)$row['montantPayer'];
                                        } else {
                                            $totalDivers += (float)$row['montantPayer'];
                                        }
                                    }
                                }
                                $totalGeneral = $totalScolaire + $totalDivers;
                                ?>

                            <div class="row mt-4">
                                <div class="col-12">

                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                                        <h5 class="card-title mb-0">
                                            <i class="mdi mdi-cash-multiple text-primary me-1"></i>
                                            Tous les paiements enregistrés aujourd'hui
                                        </h5>

                                        <div class="d-flex align-items-center gap-2">

                                            <!-- Historique -->
                                            <div class="dropdown">
                                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button"
                                                    id="historyDropdown" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="mdi mdi-history me-1"></i> Historique
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end"
                                                    aria-labelledby="historyDropdown">
                                                    <li>
                                                        <a class="dropdown-item" href="history.php">
                                                            <i class="mdi mdi-school me-2 text-primary"></i>
                                                            Historique Frais Scolaires
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="liste_paiement_frais_divers.php">
                                                            <i class="mdi mdi-cash-register me-2 text-success"></i>
                                                            Historique Frais Divers
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <!-- Export -->
                                            <button type="button" id="exportJour" class="btn btn-success btn-sm">
                                                <i class="bi bi-file-earmark-excel me-1"></i>
                                                Exporter CSV
                                            </button>
                                        </div>

                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="tblPaiementsJour">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Famille</th>
                                                    <th>Motif / Frais</th>
                                                    <th>Payé ($)</th>
                                                    <th>Reste ($)</th>
                                                    <th>Date/Heure</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($rows)): ?>
                                                <?php foreach($rows as $p): ?>
                                                <tr>
                                                    <td>
                                                        <span
                                                            class="badge <?= $p['nature'] === 'Scolaire' ? 'bg-primary' : 'bg-info'; ?>">
                                                            <?= htmlspecialchars($p['nature']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($p['menage']); ?></td>
                                                    <td><?= htmlspecialchars($p['motif']); ?></td>
                                                    <td class="fw-bold text-success">
                                                        <?= number_format((float)$p['montantPayer'], 2); ?> $</td>
                                                    <td class="text-danger">
                                                        <?= number_format((float)$p['resteAPayer'], 2); ?> $</td>
                                                    <td><?= date('d/m/Y H:i', strtotime($p['dateCreated'])); ?></td>
                                                    <td>
                                                        <a href="<?= $p['nature'] === 'Scolaire' ? 'api/apercu_recu.php' : 'api/apercu_recu_divers.php'; ?>?ordre=<?= $p['id']; ?>"
                                                            class="btn btn-danger btn-sm" target="_blank">Reçu</a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">Aucun versement
                                                        effectué aujourd'hui.</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>

                                            <!-- LIGNES DE TOTAL EN BAS DU TABLEAU -->
                                            <tfoot class="table-light fw-bold">
                                                <tr>
                                                    <td colspan="3" class="text-end text-uppercase">Total Scolaire :
                                                    </td>
                                                    <td class="text-primary"><?= number_format($totalScolaire, 2); ?> $
                                                    </td>
                                                    <td colspan="3"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end text-uppercase">Total Divers :</td>
                                                    <td class="text-info"><?= number_format($totalDivers, 2); ?> $</td>
                                                    <td colspan="3"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end text-uppercase">Recette Totale du
                                                        Jour :</td>
                                                    <td class="text-success"><?= number_format($totalGeneral, 2); ?> $
                                                    </td>
                                                    <td colspan="3"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        let currentMenageId = null;

        $('#toggleStatsBtn').on('click', function() {
            $('#heading_stats').toggle();
        });

        // Sélection de la famille / ménage
        $('#select_menage_global').on('change', function() {
            currentMenageId = $(this).val();
            if (!currentMenageId) return;

            $('#zone_paiement').slideDown();
            chargerSoldesMenage(currentMenageId);
        });

        // ================= DÉTECTION AUTOMATIQUE DES SOLDES =================
        function chargerSoldesMenage(menageId) {
            $.ajax({
                url: 'api/get_solde_menage.php',
                method: 'GET',
                data: {
                    menage_id: menageId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        alert(response.error);
                        return;
                    }

                    // 1. Mise à jour de la Scolarité
                    let soldeScolaire = parseFloat(response.scolarite.solde_du) || 0;
                    $('#scolaire_montantAPayer').val(soldeScolaire.toFixed(2));
                    $('#scolaire_rPayer').val(soldeScolaire.toFixed(2));
                    $('#scolaire_payer').val('');

                    // 2. Pré-remplissage du Solde Frais Divers
                    let soldeDivers = parseFloat(response.divers.solde_du) || 0;
                    $('#divers_montantAPayer').val(soldeDivers.toFixed(2));
                    $('#divers_rPayer').val(soldeDivers.toFixed(2));
                    $('#divers_payer').val('');
                    $('#divers_type_frais').val('');
                    $('#container_enfants_divers').empty();
                },
                error: function() {
                    alert('Erreur lors du chargement des informations du ménage.');
                }
            });
        }

        // ================= CALCUL DYNAMIQUE : SCOLARITÉ =================
        $('#scolaire_payer').on('input', function() {
            let totalSaisie = parseFloat($(this).val()) || 0;
            let globalDue = parseFloat($('#scolaire_montantAPayer').val()) || 0;
            let reste = Math.max(0, globalDue - totalSaisie);

            $('#scolaire_rPayer').val(reste.toFixed(2));
        });

        // ================= CALCUL DYNAMIQUE : FRAIS DIVERS =================
        $('#divers_type_frais').on('change', function() {
            let option = $(this).find('option:selected');
            let fraisDesc = option.data('description');

            if (!currentMenageId || !fraisDesc) return;

            // Charger la répartition par enfant si nécessaire
            $.getJSON('api/get_enfants_par_menage_2.php', {
                menage_id: currentMenageId,
                scolarite_desc: fraisDesc
            }, function(children) {
                let html =
                    `<table class="table table-bordered mt-2">
            <thead><tr><th>Nom</th><th>Classe</th><th>A Payer ($)</th><th>Payé ($)</th><th>Solde ($)</th></tr></thead><tbody>`;

                if (!children || children.length === 0) {
                    html +=
                        '<tr><td colspan="5" class="text-center text-muted">Aucun enfant éligible.</td></tr>';
                } else {
                    children.forEach(function(child) {
                        let m = parseFloat(child.montant) || 0;
                        html += `<tr>
                        <td><input type="hidden" class="divers-eleve-id" value="${child.id}">${child.nom_complet}</td>
                        <td>${child.classe ?? ''}</td>
                        <td><input type="number" class="form-control divers-montant-eleve" value="${m}" readonly></td>
                        <td><input type="number" class="form-control divers-paye-eleve" value="0" step="0.01"></td>
                        <td><input type="number" class="form-control divers-solde-eleve" value="${m}" readonly></td>
                    </tr>`;
                    });
                }
                html += '</tbody></table>';
                $('#container_enfants_divers').html(html);
            });
        });

        $('#divers_payer').on('input', function() {
            let globalPaye = parseFloat($(this).val()) || 0;
            let totalDuce = parseFloat($('#divers_montantAPayer').val()) || 0;

            let elements = $('.divers-montant-eleve');
            let nb = elements.length;
            if (nb > 0) {
                let part = globalPaye / nb;
                elements.each(function(i) {
                    let due = parseFloat($(this).val()) || 0;
                    let p = Math.min(part, due);
                    $('.divers-paye-eleve').eq(i).val(p.toFixed(2));
                    $('.divers-solde-eleve').eq(i).val(Math.max(0, due - p).toFixed(2));
                });
            }

            let reste = Math.max(0, totalDuce - globalPaye);
            $('#divers_rPayer').val(reste.toFixed(2));
        });

        $(document).on('input', '.divers-paye-eleve', function() {
            let totalVerser = 0;
            $('.divers-paye-eleve').each(function(i) {
                let paye = parseFloat($(this).val()) || 0;
                let due = parseFloat($('.divers-montant-eleve').eq(i).val()) || 0;
                $('.divers-solde-eleve').eq(i).val(Math.max(0, due - paye).toFixed(2));
                totalVerser += paye;
            });
            $('#divers_payer').val(totalVerser.toFixed(2));
            let totalDuce = parseFloat($('#divers_montantAPayer').val()) || 0;
            $('#divers_rPayer').val(Math.max(0, totalDuce - totalVerser).toFixed(2));
        });

        // =========================================================================
        // 1. CLIC SUR BTN_SAVE_SCOLAIRE
        // =========================================================================
        $('#btn_save_scolaire').on('click', function(e) {
            e.preventDefault();

            let menageId = $('#select_menage_global').val();
            let montantPayer = parseFloat($('#scolaire_payer').val()) || 0;
            let soldeDu = parseFloat($('#scolaire_montantAPayer').val()) || 0;

            if (!menageId) {
                alert("Veuillez d'abord sélectionner un ménage.");
                return;
            }
            if (montantPayer <= 0) {
                alert("Veuillez saisir un montant perçu valide.");
                return;
            }

            $(this).prop('disabled', true).text('Enregistrement...');

            $.ajax({
                url: 'api/save_paiement.php',
                method: 'POST',
                data: {
                    action: 'save_scolaire',
                    menage_id: menageId,
                    montant_payer: montantPayer,
                    solde_du: soldeDu
                },
                dataType: 'json',
                success: function(res) {
                    $('#btn_save_scolaire').prop('disabled', false).text(
                        'Enregistrer Paiement Scolaire');

                    if (res.status === 'success') {
                        alert(res.message);

                        // Ouvrir le reçu
                        if (res.paiement_id) {
                            window.open('api/apercu_recu.php?ordre=' + res.paiement_id,
                                '_blank');
                        }

                        // Recharge la page pour mettre à jour les statistiques et le tableau
                        location.reload();
                    } else {
                        alert(res.message);
                    }
                },
                error: function(xhr) {
                    $('#btn_save_scolaire').prop('disabled', false).text(
                        'Enregistrer Paiement Scolaire');
                    console.error("Erreur serveur :", xhr.responseText);
                    alert('Erreur serveur (' + xhr.status +
                        '). Vérifiez la console (F12) pour voir le détail PHP.');
                }
            });
        });

        // =========================================================================
        // 2. CLIC SUR BTN_SAVE_DIVERS
        // =========================================================================
        $('#btn_save_divers').on('click', function(e) {
            e.preventDefault();

            let menageId = $('#select_menage_global').val();
            let scolariteId = $('#divers_type_frais').val();
            let montantPayer = parseFloat($('#divers_payer').val()) || 0;
            let soldeDu = parseFloat($('#divers_montantAPayer').val()) || 0;

            if (!menageId) {
                alert("Veuillez d'abord sélectionner un ménage.");
                return;
            }
            if (!scolariteId) {
                alert("Veuillez choisir un type de frais divers.");
                return;
            }
            if (montantPayer <= 0) {
                alert("Veuillez saisir un montant perçu valide.");
                return;
            }

            // Récupération du détail par enfant
            let enfantsData = [];
            $('.divers-eleve-id').each(function(index) {
                let eleveId = $(this).val();
                let payeEleve = parseFloat($('.divers-paye-eleve').eq(index).val()) || 0;
                enfantsData.push({
                    eleve_id: eleveId,
                    montant_paye: payeEleve
                });
            });

            $(this).prop('disabled', true).text('Enregistrement...');

            $.ajax({
                url: 'api/save_paiement.php',
                method: 'POST',
                data: {
                    action: 'save_divers',
                    menage_id: menageId,
                    scolarite_id: scolariteId,
                    montant_payer: montantPayer,
                    solde_du: soldeDu,
                    enfants: JSON.stringify(enfantsData)
                },
                dataType: 'json',
                success: function(res) {
                    $('#btn_save_divers').prop('disabled', false).text(
                        'Enregistrer Frais Divers');

                    if (res.status === 'success') {
                        alert(res.message);

                        // Ouvrir le reçu divers
                        if (res.paiement_id) {
                            window.open('api/apercu_recu_divers.php?ordre=' + res
                                .paiement_id, '_blank');
                        }

                        // Recharge la page pour mettre à jour les statistiques et le tableau
                        location.reload();
                    } else {
                        alert(res.message);
                    }
                },
                error: function(xhr) {
                    $('#btn_save_divers').prop('disabled', false).text(
                        'Enregistrer Frais Divers');
                    console.error("Erreur serveur :", xhr.responseText);
                    alert('Erreur serveur (' + xhr.status +
                        '). Vérifiez la console (F12) pour voir le détail PHP.');
                }
            });
        });

        // Export CSV
        $('#exportJour').on('click', function() {
            let rows = Array.from(document.querySelectorAll('#tblPaiementsJour tr'));
            let csv = rows.map(r => Array.from(r.querySelectorAll('th,td')).map(c => c.innerText.trim())
                .join(',')).join('\n');
            let blob = new Blob([csv], {
                type: 'text/csv;charset=utf-8;'
            });
            let a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'paiements_du_jour.csv';
            a.click();
        });
    });
    </script>
</body>