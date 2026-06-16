<?php 
require_once ('../../layouts/constants/head.php'); 
require_once ('../../webapp/service/paiement.service.php');
require_once ('../../webapp/service/paiement.nouveau.service.php'); 
require_once ('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/dbcongig.php');
require_once('../../webapp/service/annee_scolaire.encours.php');

// Récupérer les produits de la base de données
$sql = "SELECT men.id AS id, men.noms AS menage, MIN(paie.resteAPayer) AS resteAPayer 
        FROM paiement paie 
        JOIN menage men ON paie.menage = men.id 
        WHERE paie.anneeScolaire ='$annee_scolaire' AND men.status ='actif' 
        GROUP BY men.id 
        ORDER BY men.noms ASC";

$result = $conn->query($sql);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// --- Total reçu aujourd'hui (paiement scolaire) ---
$sqlTotalJour = "
  SELECT COALESCE(SUM(p.montantPayer), 0) AS total_jour
  FROM paiement p
  WHERE DATE(p.dateCreated) = CURDATE()
    AND p.anneeScolaire = ?
";
$stmtTotal = $conn->prepare($sqlTotalJour);
$stmtTotal->bind_param("s", $annee_scolaire);
$stmtTotal->execute();
$resTotal = $stmtTotal->get_result();
$afficheTotalDePaiements = $resTotal ? $resTotal->fetch_assoc() : ['total_jour' => 0];
$stmtTotal->close();

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
                            <h4 class="card-title mb-4">
                                <button class="btn btn-primary btn-block enter-btn me-2">
                                    <span class="menu-icon"><i class="mdi mdi-speedometer"></i></span>
                                </button>
                                paiement frais scolaire
                            </h4>

                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="wrapper-menage">
                                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" role="form"
                                            id="form-paie">
                                            <div class="d-none row mb-3">
                                                <div class="col">
                                                    <label for="id" class="form-label">ID FAMILLE</label>
                                                    <input type="number" class="form-control" name="id" id="id"
                                                        readonly>
                                                </div>
                                                <div class="col">
                                                    <div class="mt-4"></div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4 col-sm-12">
                                                    <label for="select_product" class="form-label">Noms famille</label>
                                                    <select class="form-control" name="menage" id="select_product">
                                                        <option value="" disabled selected>Sélectionnez la famille
                                                        </option>
                                                        <?php foreach ($products as $product): ?>
                                                        <option value="<?= $product['id']; ?>"
                                                            data-id="<?= $product['id']; ?>"
                                                            data-price="<?= $product['resteAPayer']; ?>">
                                                            <?= htmlspecialchars($product['menage']) . ' ' . htmlspecialchars($product['id']); ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-lg-2 col-sm-12">
                                                    <label for="montantAPayer" class="form-label">Montant à payer
                                                        $</label>
                                                    <input type="number" class="form-control" name="montantAPayer"
                                                        id="montantAPayer" placeholder="0.00" readonly>
                                                </div>

                                                <div class="col-lg-3 col-sm-12">
                                                    <label for="payer" class="form-label">Montant payé $</label>
                                                    <input type="number" class="form-control" name="payer" id="payer"
                                                        placeholder="0.00">
                                                </div>

                                                <div class="col-lg-3 col-sm-12">
                                                    <label for="rPayer" class="form-label">Solde à payer $</label>
                                                    <input type="number" class="form-control" name="rPayer" id="rPayer"
                                                        placeholder="0.00" readonly>
                                                </div>

                                                <div class="col d-none">
                                                    <label class="form-label">Obs.</label>
                                                    <p class="text-danger">aucun</p>
                                                </div>
                                            </div>

                                            <div class="col-12 mt-4" id="tranches-container">
                                                <!-- Les champs de saisie seront injectés ici via JS -->
                                            </div>

                                            <div class="mb-5 d-flex gap-2">
                                                <button class="btn btn-success enter-btn"
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
                                            <div class="show-hidden">
                                                <button class="btn btn-dark" id="toggleButton" type="button">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <h1 id="heading" style="display:block;">
                                            <?php echo number_format((float)($afficheTotalDePaiements['total_jour'] ?? 0), 2, '.', ' '); ?>$
                                        </h1>
                                        <p id="paragraph" style="display:none;">Total encaissé ce jour
                                            (<?php echo date('Y-m-d'); ?>).</p>
                                    </div>
                                </div>
                            </div>

                            <div class="wrapper-paiement">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row tableau-de-paie">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="card-title mb-3">Paiement du jour</h4>
                                                <div class="d-flex gap-2">
                                                    <a href="history.php" class="btn btn-primary">History</a>
                                                    <a href="nouveau-paiement.php" class="btn btn-danger">Nouveau
                                                        paiement</a>
                                                    <a href="autres_frais_supplementaire.php"
                                                        class="btn btn-secondary">Divers paiement</a>
                                                    <button type="button" id="exportJour"
                                                        class="btn btn-outline-success">Exporter CSV</button>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table" id="myTable">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Famille</th>
                                                            <th>Montant à payer</th>
                                                            <th class="text-primary">Montant payé</th>
                                                            <th>Solde à payer</th>
                                                            <th class="text-danger">Obs.</th>
                                                            <th>Date paiement</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if ($rstCinqPaiement->num_rows <= 0): ?>
                                                        <tr>
                                                            <td colspan="8">
                                                                <div class="alert alert-danger m-0">Liste de paie vide
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php else: while ($row = $rstCinqPaiement->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?= $row['id']; ?></td>
                                                            <td><?= $row['menage']; ?></td>
                                                            <td><?= $row['montantAPayer']; ?>$</td>
                                                            <td class="text-primary"><?= $row['montantPayer']; ?>$</td>
                                                            <td><?= $row['resteAPayer']; ?>$</td>
                                                            <td class="text-danger"><?= $row['observation']; ?></td>
                                                            <td><?= $row['dateCreated']; ?></td>
                                                            <td>
                                                                <a href="apercu_recu.php?ordre=<?= $row['id']; ?>"
                                                                    class="btn btn-danger">Imprimer</a>
                                                                <a href="detail_paiement.php?paiement_id=<?= $row['id']; ?>"
                                                                    class="btn d-none btn-primary btn-sm">Voir</a>
                                                            </td>
                                                        </tr>
                                                        <?php endwhile; endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div> <!-- card-body -->
                    </div> <!-- card -->
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Toggle total du jour
        (function() {
            const btn = document.getElementById('toggleButton');
            const h = document.getElementById('heading');
            const p = document.getElementById('paragraph');
            if (!btn || !h || !p) return;
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const isHidden = (h.style.display === 'none') || (getComputedStyle(h).display ===
                    'none');
                h.style.display = isHidden ? 'block' : 'none';
                p.style.display = isHidden ? 'block' : 'none';
            });
        })();

        // Changement de famille
        $('#select_product').change(function() {
            const selected = $(this).find('option:selected');
            const menageId = selected.val();
            const resteAPayer = parseFloat(selected.data('price')) || 0;

            $('#id').val(selected.data('id') || '');
            $('#montantAPayer').val(resteAPayer);
            $('#payer').trigger('input');

            // Charger les tranches restantes
            $.ajax({
                url: 'get_tranches_restantes.php',
                method: 'GET',
                data: {
                    menage_id: menageId
                },
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    if (!data || data.length === 0) {
                        html =
                            '<p class="text-warning">Aucun enfant trouvé pour cette famille.</p>';
                    } else {
                        html +=
                            `<div class="alert alert-info d-none">Le montant payé sera réparti entre les élèves.</div>`;
                        data.forEach(eleve => {
                            const tranche = eleve.tranches_restantes[0];
                            const montant = parseFloat(tranche.montant).toFixed(2);
                            const trancheId = tranche.tranche_id;
                            const trancheNum = tranche.numero_tranche;

                            html += `
                        <div class="card mb-4 shadow-sm p-3">
                            <h5 class="mb-3">${eleve.nom_eleve}</h5>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label>Montant tranche à payer ($)</label>
                                    <input type="text" class="form-control montant-tranche" readonly value="${montant}"
                                           name="paiements[${eleve.eleve_id}][${trancheId}][montant_tranche]">
                                </div>
                                <div class="col-md-4">
                                    <label>Paiement tranche n°${trancheNum} ($)</label>
                                    <input type="number" step="0.01" min="0" max="${montant}"
                                           class="form-control paiement-eleve"
                                           data-eleve="${eleve.eleve_id}" data-tranche="${trancheId}" data-cycle="${eleve.cycle_id}" data-max="${montant}"
                                           name="paiements[${eleve.eleve_id}][${trancheId}][montant]">
                                    <input type="hidden" name="paiements[${eleve.eleve_id}][${trancheId}][tranche_id]" value="${trancheId}">
                                </div>
                                <div class="col-md-4">
                                    <label>Reste tranche ($)</label>
                                    <input type="text" class="form-control reste-eleve" readonly value="${montant}">
                                </div>
                            </div>
                        </div>`;
                        });
                    }
                    $('#tranches-container').html(html);
                    activerRepartitionEtCalcul();
                },
                error: function() {
                    $('#tranches-container').html(
                        '<p class="text-danger">Erreur lors du chargement des tranches.</p>'
                        );
                }
            });
        });

        // Répartition / recalcul
        function activerRepartitionEtCalcul() {
            $('#payer').on('input', function() {
                const totalPayeFamille = parseFloat($(this).val()) || 0;
                const champsPaiement = $('.paiement-eleve');
                let reste = totalPayeFamille;

                champsPaiement.each(function() {
                    const max = parseFloat($(this).attr('data-max'));
                    let montantApplique = Math.min(reste, max);
                    montantApplique = montantApplique < 0 ? 0 : montantApplique;

                    $(this).val(montantApplique.toFixed(2));
                    const resteTranche = (max - montantApplique).toFixed(2);
                    $(this).closest('.row').find('.reste-eleve').val(resteTranche);

                    reste -= montantApplique;
                });

                const montantAPayer = parseFloat($('#montantAPayer').val()) || 0;
                const resteGlobal = (montantAPayer - totalPayeFamille).toFixed(2);
                $('#rPayer').val(resteGlobal);
            });

            $(document).on('input', '.paiement-eleve', function() {
                const valeur = parseFloat($(this).val()) || 0;
                const max = parseFloat($(this).attr('data-max')) || 0;
                const reste = Math.max(0, max - valeur).toFixed(2);
                $(this).closest('.row').find('.reste-eleve').val(reste);

                let totalPaye = 0;
                $('.paiement-eleve').each(function() {
                    totalPaye += parseFloat($(this).val()) || 0;
                });

                const montantAPayer = parseFloat($('#montantAPayer').val()) || 0;
                const resteGlobal = (montantAPayer - totalPaye).toFixed(2);
                $('#rPayer').val(resteGlobal);
            });
        }

        // --- HARD RELOAD util (bypass cache) ---
        function hardReload() {
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('_r', Date.now().toString());
                // Remplace l'entrée d’historique (évite le back sur la version sans refresh)
                window.location.replace(url.toString());
            } catch (e) {
                // Fallback
                window.location.href = window.location.href + (window.location.href.indexOf('?') === -1 ? '?' :
                    '&') + '_r=' + Date.now();
            }
        }

        // Sauvegarde AJAX — vrai refresh après succès
        $('#submit').on('click', function(e) {
            e.preventDefault();

            const $btn = $(this);
            $btn.prop('disabled', true);

            const menageId = $('#id').val();
            const montantGlobal = parseFloat($('#montantAPayer').val()) || 0;
            const montantPaye = parseFloat($('#payer').val()) || 0;

            if (!menageId || montantPaye <= 0) {
                $btn.prop('disabled', false);
                return;
            }

            let eleves = [];
            $('.paiement-eleve').each(function() {
                const eleveId = $(this).data('eleve');
                const trancheId = $(this).data('tranche');
                const cycleId = $(this).data('cycle') || 0;
                const montantPayeTranche = parseFloat($(this).val()) || 0;

                let eleveIndex = eleves.findIndex(e => e.eleve_id === eleveId);
                if (eleveIndex === -1) {
                    eleves.push({
                        eleve_id: eleveId,
                        cycle_id: cycleId,
                        tranches: []
                    });
                    eleveIndex = eleves.length - 1;
                }
                eleves[eleveIndex].tranches.push({
                    tranche_id: trancheId,
                    montant_paye: montantPayeTranche
                });
            });

            const payload = {
                menage_id: menageId,
                montant_global: montantGlobal,
                montant_paye: montantPaye,
                eleves: eleves
            };

            $.ajax({
                url: '../../webapp/service/save_ancien_paiement.php',
                method: 'POST',
                // Pas de dataType imposé (le script peut renvoyer JSON/texte)
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success: function() {
                    // VRAI REFRESH (anti-cache)
                    hardReload();
                    // Double filet de sécurité au cas où :
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function() {
                    // En cas d'erreur, on réactive juste le bouton
                    $btn.prop('disabled', false);
                }
            });
        });

        // Export CSV util
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
        $('#exportJour').on('click', function() {
            const csv = tableToCSV('#myTable');
            downloadCSV(csv, 'paiements_jour_scolaire.csv');
        });

        // bouton reset: nettoie tranches et montants
        $('#btnReset').on('click', function() {
            $('#tranches-container').empty();
            $('#id').val('');
            $('#montantAPayer, #payer, #rPayer').val('');
            $('#select_product').val('').trigger('change');
        });
    });
    </script>
</body>