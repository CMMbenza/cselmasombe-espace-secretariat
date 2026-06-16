<?php
require_once ('../../layouts/constants/head.php');  
require_once ('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/dbcongig.php');
require_once('../../webapp/service/annee_scolaire.encours.php');

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// Flash helpers
function set_flash(string $type, string $msg): void { $_SESSION['flash'] = ['type'=>$type,'msg'=>$msg]; }
function get_flash(): ?array { if (!empty($_SESSION['flash'])) { $f=$_SESSION['flash']; unset($_SESSION['flash']); return $f; } return null; }

// CSRF helpers
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
function csrf_token(): string { return $_SESSION['csrf_token'] ?? ''; }
function verify_csrf_token(?string $token): bool {
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
function rotate_csrf_token(): void { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

/* ===== POST (enregistrement — avec PRG) ===== */
if (isset($_POST['submit'])) {
    // 1) CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('warning', 'Session expirée ou requête invalide. Veuillez réessayer.');
    } else {
        // 2) Inputs
        $menage        = isset($_POST['menage']) ? (int)$_POST['menage'] : 0;
        $type_frais    = trim((string)($_POST['type_frais'] ?? ''));
        $montantAPayer = (float)($_POST['montantAPayer'] ?? 0);
        $montantPayer  = (float)($_POST['payer'] ?? 0);
        $resteAPayer   = (float)($_POST['rPayer'] ?? 0);
        $observation   = trim((string)($_POST['observation'] ?? ''));
        $createdby     = "admin";

        if ($menage <= 0 || $type_frais === '' || $montantPayer <= 0) {
            set_flash('warning', "Veuillez sélectionner une famille, un type de frais et saisir un montant payé valide.");
        } else {
            $conn->begin_transaction();
            try {
                // a) paiement_divers
                $stmt = $conn->prepare("INSERT INTO paiement_divers (menage, type_frais, montantAPayer, montantPayer, resteAPayer, observation, createdby, anneeScolaire) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issddsss", $menage, $type_frais, $montantAPayer, $montantPayer, $resteAPayer, $observation, $createdby, $annee_scolaire);
                $stmt->execute();
                $paiementId = (int)$stmt->insert_id;
                $stmt->close();
                if ($paiementId <= 0) { throw new Exception("ID paiement principal invalide."); }

                // b) paiements par élève
                if (isset($_POST['eleves']) && is_array($_POST['eleves'])) {
                    $stmt2 = $conn->prepare("INSERT INTO paiement_eleve_divers (paiement_id, eleve_id, montantFrais, montantPaye, solde) 
                                             VALUES (?, ?, ?, ?, ?)");
                    foreach ($_POST['eleves'] as $eleve) {
                        $eleveId      = (int)($eleve['id'] ?? 0);
                        $montantFrais = (float)($eleve['montant'] ?? 0);
                        $montantPayeE = (float)($eleve['paye'] ?? 0);
                        $solde        = (float)($eleve['solde'] ?? 0);
                        if ($eleveId > 0) {
                            $stmt2->bind_param("iiddd", $paiementId, $eleveId, $montantFrais, $montantPayeE, $solde);
                            $stmt2->execute();
                        }
                    }
                    $stmt2->close();

                    // c) caisse_frais_divers (sommes par cycle)
                    $montantsParCycle = [];
                    $stmtCycle = $conn->prepare("SELECT cl.cycle FROM eleve e JOIN classe cl ON e.classe = cl.id WHERE e.id = ?");
                    foreach ($_POST['eleves'] as $eleve) {
                        $eleveId = (int)($eleve['id'] ?? 0);
                        $montantPayeE = (float)($eleve['paye'] ?? 0);
                        if ($eleveId > 0 && $montantPayeE > 0) {
                            $stmtCycle->bind_param("i", $eleveId);
                            $stmtCycle->execute();
                            $resCycle = $stmtCycle->get_result();
                            if ($rowCycle = $resCycle->fetch_assoc()) {
                                $cycleId = (int)$rowCycle['cycle'];
                                if (!isset($montantsParCycle[$cycleId])) $montantsParCycle[$cycleId] = 0;
                                $montantsParCycle[$cycleId] += $montantPayeE;
                            }
                        }
                    }
                    $stmtCycle->close();

                    if (!empty($montantsParCycle)) {
                        $stmtCaisse = $conn->prepare("INSERT INTO caisse_frais_divers (cycle_id, montant_paye, annee_scolaire) VALUES (?, ?, ?)");
                        foreach ($montantsParCycle as $cycleId => $montantTotal) {
                            $stmtCaisse->bind_param("ids", $cycleId, $montantTotal, $annee_scolaire);
                            $stmtCaisse->execute();
                        }
                        $stmtCaisse->close();
                    }
                }

                $conn->commit();
                rotate_csrf_token();
                set_flash('success', "Paiement enregistré avec succès.");

                // ===== PRG: redirect GET (hard refresh anti-cache) =====
                $redirectPath = strtok($_SERVER['REQUEST_URI'], '?'); // sans query string
                $qs = http_build_query(['saved' => 1, '_r' => time()]);
                header('Location: ' . $redirectPath . '?' . $qs);
                exit;

            } catch (Throwable $e) {
                $conn->rollback();
                set_flash('danger', "Erreur lors de l'enregistrement : " . $e->getMessage());
            }
        }
    }
}

// Requêtes d'affichage (GET)
$sqlFrais = "SELECT MIN(id) AS id, description 
             FROM scolarite 
             WHERE LOWER(description) != 'frais scolaire' 
             GROUP BY description 
             ORDER BY description ASC";
$resultFrais = $conn->query($sqlFrais);

$sql = "SELECT id, noms AS menage FROM menage WHERE status ='actif' ORDER BY noms ASC";
$result = $conn->query($sql);
$products = []; 
while ($row = $result->fetch_assoc()) { $products[] = $row; }

$sqlTotalJour = "SELECT COALESCE(SUM(montantPayer),0) AS total_jour FROM paiement_divers WHERE DATE(dateCreated) = CURDATE()";
$resTotalJour = $conn->query($sqlTotalJour);
$afficheTotalDePaiements = $resTotalJour ? $resTotalJour->fetch_assoc() : ['total_jour' => 0];

$sqlPaiementsJour = "
  SELECT 
      pd.id, m.noms AS menage, COALESCE(s.description, pd.type_frais) AS type_frais,
      pd.montantPayer, pd.resteAPayer, pd.dateCreated
  FROM paiement_divers pd
  JOIN menage m ON m.id = pd.menage
  LEFT JOIN scolarite s ON s.id = CAST(pd.type_frais AS UNSIGNED)
  WHERE DATE(pd.dateCreated) = CURDATE()  AND m.status ='actif'
  ORDER BY pd.dateCreated DESC
";
$resultPaiementsJour = $conn->query($sqlPaiementsJour);

$conn->close();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
#heading,
#paragraph {
    display: none;
}
</style>

<body>
    <div class="main-panel-copy">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12 col-sm-12 grid-margin">
                    <div class="card">
                        <div class="card-body">
                            <!-- Flash -->
                            <?php if ($flash = get_flash()): ?>
                            <div id="flash"
                                class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> alert-dismissible fade show"
                                role="alert">
                                <strong><?php echo $flash['type']==='success'?'Succès':($flash['type']==='danger'?'Erreur':'Info'); ?>
                                    :</strong>
                                <?php echo htmlspecialchars($flash['msg']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                            <script>
                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                            </script>
                            <?php else: ?>
                            <div id="flash" class="alert d-none" role="alert"></div>
                            <?php endif; ?>

                            <h4 class="card-title mb-4 text-uppercase">
                                <button class="btn btn-primary btn-block enter-btn me-2"><span class="menu-icon"><i
                                            class="mdi mdi-speedometer"></i></span></button>
                                paiement supplemataire
                            </h4>

                            <div class="row">
                                <div class="col-lg-9 col-sm-12">
                                    <div class="wrapper-menage">
                                        <form
                                            action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>"
                                            method="post" role="form" id="form-divers">
                                            <!-- CSRF -->
                                            <input type="hidden" name="csrf_token"
                                                value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

                                            <div class="d-none row mb-3">
                                                <div class="col-6">
                                                    <label for="id" class="form-label">ID FAMILLE</label>
                                                    <input type="number" class="form-control" name="id" id="id"
                                                        placeholder="ID famille" readonly>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label for="select_product" class="form-label">Noms famille</label>
                                                    <select class="form-control" name="menage" id="select_product">
                                                        <option value="" disabled selected>Sélectionnez la famille
                                                        </option>
                                                        <?php foreach ($products as $product): ?>
                                                        <option value="<?= $product['id']; ?>"
                                                            data-id="<?= $product['id']; ?>">
                                                            <?= htmlspecialchars($product['menage']); ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <label for="type_frais" class="form-label">Type frais</label>
                                                    <select class="form-control" name="type_frais" id="type_frais">
                                                        <option value="" disabled selected>Sélectionnez le frais
                                                        </option>
                                                        <?php if ($resultFrais && $resultFrais->num_rows > 0): ?>
                                                        <?php while ($frais = $resultFrais->fetch_assoc()): ?>
                                                        <option value="<?= $frais['id']; ?>">
                                                            <?= htmlspecialchars($frais['description']); ?></option>
                                                        <?php endwhile; ?>
                                                        <?php else: ?>
                                                        <option value="">Aucun frais trouvé</option>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <label for="montantAPayer" class="form-label">Montant à payer
                                                        $</label>
                                                    <input type="number" class="form-control" name="montantAPayer"
                                                        id="montantAPayer" placeholder="0.00" readonly>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label for="payer" class="form-label">Montant payé $</label>
                                                    <input type="decimal" class="form-control" name="payer" id="payer"
                                                        placeholder="0.00">
                                                </div>
                                                <div class="col">
                                                    <label for="rPayer" class="form-label">Solde à payer $</label>
                                                    <input type="number" class="form-control" name="rPayer" id="rPayer"
                                                        placeholder="0.00" readonly>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col-12">
                                                    <label for="observation" class="form-label">Obs.</label>
                                                    <textarea name="observation" id="observation" cols="30" rows="6"
                                                        class="form-control"></textarea>
                                                </div>
                                            </div>

                                            <div id="liste_enfants_frais" class="mb-4 mt-4"></div>

                                            <div class="mb-5 d-flex gap-2">
                                                <button class="btn btn-success enter-btn" name="submit"
                                                    id="submit">Sauvegarder</button>
                                                <button type="reset" class="btn btn-dark enter-btn"
                                                    id="btnReset">Annuler</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-sm-12">
                                    <div class="alert alert-dark">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4>Somme reçue aujourd'hui</h4>
                                            <button class="btn btn-dark" id="toggleButton"><i
                                                    class="mdi mdi-eye"></i></button>
                                        </div>
                                        <h1 id="heading" style="display:none;">
                                            <?php echo number_format((float)($afficheTotalDePaiements['total_jour'] ?? 0), 2, '.', ' '); ?>$
                                        </h1>
                                        <p id="paragraph" style="display:none;">Total encaissé ce jour
                                            (<?php echo date('Y-m-d'); ?>).</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-3">Liste des paiements du jour</h5>
                                            <div class="d-flex gap-2">
                                                <a class="btn btn-danger"
                                                    href="liste_paiement_frais_divers.php">History</a>
                                                <button type="button" id="exportDivers"
                                                    class="btn btn-outline-success">Exporter CSV</button>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="tblPaiementsJour">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Famille</th>
                                                        <th>Type de frais</th>
                                                        <th>Payé ($)</th>
                                                        <th>Reste ($)</th>
                                                        <th>Date</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($resultPaiementsJour && $resultPaiementsJour->num_rows > 0): ?>
                                                    <?php while($p = $resultPaiementsJour->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($p['id']); ?></td>
                                                        <td><?php echo htmlspecialchars($p['menage']); ?></td>
                                                        <td><?php echo htmlspecialchars($p['type_frais']); ?></td>
                                                        <td><?php echo number_format((float)$p['montantPayer'], 2, '.', ' '); ?>
                                                        </td>
                                                        <td><?php echo number_format((float)$p['resteAPayer'], 2, '.', ' '); ?>
                                                        </td>
                                                        <td><?php echo date('d/m/Y H:i', strtotime($p['dateCreated'])); ?>
                                                        </td>
                                                        <td><a href="apercu_recu_divers.php?ordre=<?php echo $p['id']; ?>"
                                                                class="btn btn-danger">Imprimer</a></td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                    <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">Aucun paiement aujourd'hui.
                                                        </td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div><!-- card-body -->
                    </div><!-- card -->
                </div>
            </div>
        </div>

        <script>
        $(document).ready(function() {
            // Toggle total du jour
            document.getElementById('toggleButton')?.addEventListener('click', function() {
                const h = document.getElementById('heading');
                const p = document.getElementById('paragraph');
                const show = (h.style.display === 'none');
                h.style.display = show ? 'block' : 'none';
                p.style.display = show ? 'block' : 'none';
            });

            // Recalcul solde famille (depuis lignes enfants)
            function recalculerSoldeFamille() {
                let totalPaye = 0;
                $('.montant-paye-eleve').each(function() {
                    totalPaye += parseFloat($(this).val()) || 0;
                });
                $('#payer').val(totalPaye.toFixed(2));
                const montantTotal = parseFloat($('#montantAPayer').val()) || 0;
                const soldeFamille = montantTotal - totalPaye;
                $('#rPayer').val(soldeFamille.toFixed(2));
            }

            // Recalcul solde élève
            function recalculerSoldesEleves() {
                $('.montant-paye-eleve').each(function(index) {
                    const due = parseFloat($('.montant-eleve').eq(index).val()) || 0;
                    const paye = parseFloat($(this).val()) || 0;
                    $('.solde-eleve').eq(index).val((due - paye).toFixed(2));
                });
                recalculerSoldeFamille();
            }

            // Répartition auto quand on tape le montant global
            $('#payer').on('input', function() {
                const montantGlobal = parseFloat($(this).val()) || 0;
                const enfants = $('.montant-eleve');
                const nb = enfants.length;
                if (nb === 0) return;

                const part = (montantGlobal / nb);
                enfants.each(function(index) {
                    const due = parseFloat($(this).val()) || 0;
                    const paye = Math.min(part, due);
                    $('.montant-paye-eleve').eq(index).val(paye.toFixed(2));
                    $('.solde-eleve').eq(index).val((due - paye).toFixed(2));
                });

                $('#rPayer').val((parseFloat($('#montantAPayer').val()) - montantGlobal).toFixed(2));
            });

            // Recalcul si modif manuelle
            $(document).on('input', '.montant-paye-eleve', function() {
                recalculerSoldesEleves();
            });

            // Charger enfants + montant total
            $('#select_product, #type_frais').on('change', function() {
                const menageId = $('#select_product').val();
                const scolariteId = $('#type_frais').val();

                if (menageId && scolariteId) {
                    // Montant total
                    $.getJSON('api/get_montant_total.php', {
                        menage_id: menageId,
                        scolarite_id: scolariteId
                    }, function(r) {
                        $('#montantAPayer').val(((r && r.montant) ? r.montant : 0).toFixed(2));
                        $('#payer').val('');
                        $('#rPayer').val('');
                    });

                    // Liste enfants
                    const fraisLabel = $('#type_frais option:selected').text().trim();
                    $.getJSON('api/get_enfants_par_menage.php', {
                        menage_id: menageId,
                        scolarite_desc: fraisLabel
                    }, function(children) {
                        let totalMontant = 0;
                        let html =
                            `<table class="table table-bordered">
                    <thead><tr><th>Nom</th><th>Classe</th><th>Montant à payer</th><th>Montant payé</th><th>Solde</th></tr></thead><tbody>`;
                        (children || []).forEach(function(child, i) {
                            const m = parseFloat(child.montant) || 0;
                            totalMontant += m;
                            html += `<tr>
                        <td><input type="hidden" name="eleves[${i}][id]" value="${child.id}">${child.nom_complet}</td>
                        <td>${child.classe ?? ''}</td>
                        <td><input type="number" class="form-control montant-eleve" name="eleves[${i}][montant]" value="${m}" readonly></td>
                        <td><input type="number" class="form-control montant-paye-eleve" name="eleves[${i}][paye]" value="0" min="0" step="0.01"></td>
                        <td><input type="number" class="form-control solde-eleve" name="eleves[${i}][solde]" value="${m}" readonly></td>
                    </tr>`;
                        });
                        html += '</tbody></table>';
                        $('#liste_enfants_frais').html(html);
                        $('#montantAPayer').val(totalMontant.toFixed(2));
                    });
                }
            });

            // Renseigne champ caché ID
            $('#select_product').change(function() {
                const selectedId = $(this).find('option:selected').data('id');
                $('#id').val(selectedId || '');
            });

            // Export CSV utils
            function tableToCSV(tableSelector) {
                const rows = Array.from(document.querySelectorAll(tableSelector + ' tr'));
                return rows.map(row => {
                    const cells = Array.from(row.querySelectorAll('th,td')).map(cell => {
                        let text = cell.innerText.replace(/\r?\n|\r/g, ' ').trim();
                        if (text.includes('"') || text.includes(',')) {
                            text = '"' + text.replace(/"/g, '""') + '"';
                        }
                        return text;
                    });
                    return cells.join(',');
                }).join('\n');
            }

            function downloadCSV(csv, filename) {
                const blob = new Blob([csv], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                a.click();
                URL.revokeObjectURL(url);
            }
            $('#exportDivers').on('click', function() {
                const csv = tableToCSV('#tblPaiementsJour');
                downloadCSV(csv, 'paiements_jour_divers.csv');
            });

            // bouton reset: nettoyage
            $('#btnReset').on('click', function() {
                $('#liste_enfants_frais').empty();
                $('#id').val('');
                $('#montantAPayer, #payer, #rPayer').val('');
                $('#select_product, #type_frais').val('');
            });
        });
        </script>
</body>