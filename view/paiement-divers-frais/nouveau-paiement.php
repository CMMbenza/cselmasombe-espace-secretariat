<?php 
require_once ('../../layouts/constants/head.php'); 
require_once ('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/config.php');
require_once('../../webapp/service/annee_scolaire.encours.php');

$sql = "SELECT id, noms, montantAPayer FROM menage WHERE anneeScolaire = '$annee_scolaire' AND status ='actif' ORDER BY noms ASC";
$result = $con->query($sql);

$menages = [];
while ($row = $result->fetch_assoc()) {
    $menages[] = $row;
}
$con->close();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<body>
    <div class="main-panel-copy">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12 col-sm-12 grid-margin">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4 text-uppercase">
                                <button class="btn btn-primary btn-block enter-btn me-2">
                                    <span class="menu-icon"><i class="mdi mdi-speedometer"></i></span>
                                </button>
                                nouveul paiement frais scolaire
                            </h4>

                            <div class="row">
                                <div class="col-lg-12 col-sm-12">
                                    <div class="wrapper-menage">
                                        <div class="mb-3">
                                            <label for="select_menage" class="form-label">Nom famille</label>
                                            <select id="select_menage" class="form-control">
                                                <option selected disabled>-- Sélectionnez une famille --</option>
                                                <?php foreach ($menages as $m): ?>
                                                <option value="<?= $m['id'] ?>" data-price="<?= $m['montantAPayer'] ?>">
                                                    <?= htmlspecialchars($m['noms']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="montantGlobal" class="form-label">Montant global à payer $</label>
                                    <input type="text" id="montantGlobal" class="form-control" readonly />
                                </div>
                                <div class="col-md-4">
                                    <label for="montantPaye" class="form-label">Montant à payer maintenant $</label>
                                    <input type="number" min="0" step="0.01" id="montantPaye" class="form-control" />
                                </div>
                                <div class="col-md-4">
                                    <label for="soldeGlobal" class="form-label">Solde restant $</label>
                                    <input type="text" id="soldeGlobal" class="form-control" readonly />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <h4>INFORMATION DE L'ELEVE</h4>
                                <div id="elevesContainer" class="mb-4">
                                    <p class="text-muted">Sélectionnez une famille pour afficher les élèves.</p>
                                </div>

                                <button id="submitBtn" class="btn btn-primary">Valider le paiement</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        let elevesData = [];
        let nbEleves = 0;
        let autoRepartition = false; // 🔁 Pour différencier saisie manuelle ou calcul automatique

        $('#submitBtn').on('click', function() {
            const menageId = $('#select_menage').val();
            if (!menageId) {
                alert('Veuillez sélectionner une famille.');
                return;
            }

            const paiementData = {
                menage_id: menageId,
                montant_global: parseFloat($('#montantGlobal').val()) || 0,
                montant_paye: parseFloat($('#montantPaye').val()) || 0,
                eleves: []
            };

            elevesData.forEach(eleve => {
                let elevePaiement = {
                    eleve_id: eleve.id,
                    cycle_id: eleve.cycle_id,
                    total_paye: eleve.total_paye,
                    tranches: eleve.tranches.map(tranche => ({
                        tranche_id: tranche.id,
                        montant_paye: tranche.montant_paye
                    }))
                };
                paiementData.eleves.push(elevePaiement);
            });

            $.ajax({
                url: '../../webapp/service/save_nouveau_paiement.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(paiementData),
                success: function() {
                    alert('Paiement enregistré avec succès !');
                    window.location.replace('index.php');
                    // Réinitialiser les champs du formulaire
                    $('#select_menage').val('')
                        .change(); // remet le select à vide et déclenche changement
                    $('#montantGlobal').val('');
                    $('#montantPaye').val('');
                    $('#soldeGlobal').val('');
                    $('#elevesContainer').html(
                        '<p class="text-muted">Sélectionnez une famille pour afficher les élèves.</p>'
                    );

                    elevesData = [];
                    nbEleves = 0;
                },
                error: function(xhr) {
                    alert('Erreur : ' + xhr.responseText);
                }
            });
        });

        $('#select_menage').on('change', function() {
            const menageId = $(this).val();
            const montantGlobal = parseFloat($(this).find('option:selected').data('price')) || 0;

            $('#montantGlobal').val(montantGlobal.toFixed(2));
            $('#montantPaye').val('');
            $('#soldeGlobal').val(montantGlobal.toFixed(2));
            $('#elevesContainer').html('<p>Chargement des élèves...</p>');

            $.ajax({
                url: '../../webapp/service/fetch_eleves_by_menage_nouveau_paiement.php',
                method: 'GET',
                data: {
                    menage_id: menageId
                },
                dataType: 'json',
                success: function(eleves) {
                    if (!eleves || eleves.length === 0 || eleves.error) {
                        $('#elevesContainer').html(
                            '<p class="text-danger">Aucun élève trouvé pour cette famille.</p>'
                        );
                        elevesData = [];
                        nbEleves = 0;
                        return;
                    }
                    elevesData = eleves;
                    nbEleves = eleves.length;
                    renderEleves(eleves);
                },
                error: function() {
                    $('#elevesContainer').html(
                        '<p class="text-danger">Erreur lors du chargement des élèves.</p>'
                    );
                    elevesData = [];
                    nbEleves = 0;
                }
            });
        });

        function renderEleves(eleves) {
            let html = `
        <div class="row mb-2 fw-bold-100 text-primary">
            <div class="col-3">Nom complet</div>
            <div class="d-none col-2">Cycle</div>
            <div class="col-2">Montant total cycle ($)</div>
            <div class="col-2">Montant tranche à payer ($)</div>
            <div class="col-2">Paiement première tranche ($)</div>
            <div class="col-2">Reste tranche ($)</div>
        </div>`;

            eleves.forEach(function(eleve, idx) {
                let nomComplet = `${eleve.nom} ${eleve.postnom} ${eleve.prenom}`;
                let tranche = eleve.tranches.length > 0 ? eleve.tranches[0] : {
                    id: null,
                    numero_tranche: 'N/A',
                    montant: 0,
                    montant_paye: 0,
                    reste: 0
                };

                let resteTranche = Math.max(0, tranche.montant - tranche.montant_paye);

                html += `
            <div class="row mb-2 align-items-center">
                <div class="col-3"><input type="text" class="form-control" value="${nomComplet}" readonly></div>
                <div class="d-none col-2"><input type="text" class="form-control" value="${eleve.cycle}" readonly></div>
                <div class="col-2"><input type="number" class="form-control" value="${eleve.montant_total_cycle.toFixed(2)}" readonly></div>
                <div class="col-2"><input type="number" class="form-control" value="${tranche.montant.toFixed(2)}" readonly></div>
                <div class="col-2">
                    <input type="number" min="0" step="0.01" max="${tranche.montant.toFixed(2)}"
                        class="form-control paye_tranche"
                        data-eleve-index="${idx}"
                        data-tranche-id="${tranche.id}"
                        value="${tranche.montant_paye.toFixed(2)}">
                </div>
                <div class="col-2">
                    <input type="number" class="form-control tranche_reste" value="${resteTranche.toFixed(2)}" readonly>
                </div>
            </div>`;
            });

            $('#elevesContainer').html(html);

            $('.paye_tranche').on('input', function() {
                let idx = $(this).data('eleve-index');
                let val = parseFloat($(this).val()) || 0;
                let max = parseFloat($(this).attr('max'));

                if (val > max) val = max;
                if (val < 0) val = 0;
                $(this).val(val.toFixed(2));

                elevesData[idx].tranches[0].montant_paye = val;
                let reste = Math.max(0, elevesData[idx].tranches[0].montant - val);
                $(this).closest('.row').find('.tranche_reste').val(reste.toFixed(2));

                if (!autoRepartition) {
                    syncMontantGlobalFromEleves();
                }
            });
        }

        $('#montantPaye').on('input', function() {
            let montantGlobal = parseFloat($('#montantGlobal').val()) || 0;
            let montantPaye = parseFloat($(this).val()) || 0;

            if (montantPaye > montantGlobal) {
                montantPaye = montantGlobal;
                $(this).val(montantPaye.toFixed(2));
            }

            autoRepartition = true;
            repartitionMontant(montantPaye);
            autoRepartition = false;

            $('#soldeGlobal').val((montantGlobal - montantPaye).toFixed(2));
        });

        function repartitionMontant(total) {
            if (!elevesData || elevesData.length === 0) return;

            let reste = total;

            $('.paye_tranche').each(function(idx) {
                const max = parseFloat($(this).attr('max')) || 0;
                let aPayer = Math.min(reste, max);

                reste -= aPayer;

                $(this).val(aPayer.toFixed(2));
                elevesData[idx].tranches[0].montant_paye = aPayer;
                elevesData[idx].tranches[0].reste = max - aPayer;

                $(this).closest('.row').find('.tranche_reste').val((max - aPayer).toFixed(2));
            });
        }

        function syncMontantGlobalFromEleves() {
            let sum = 0;
            elevesData.forEach(eleve => {
                if (eleve.tranches.length > 0) {
                    sum += eleve.tranches[0].montant_paye || 0;
                }
            });

            let montantGlobal = parseFloat($('#montantGlobal').val()) || 0;
            if (sum > montantGlobal) sum = montantGlobal;

            // ❌ Ne pas écraser la saisie utilisateur
            // $('#montantPaye').val(sum.toFixed(2));
            $('#soldeGlobal').val((montantGlobal - sum).toFixed(2));
        }
    });
    </script>
</body>

</html>