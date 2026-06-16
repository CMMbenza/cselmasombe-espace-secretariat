<?php
require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/menage.service.php'); // ⚠️ nouveau service
require_once ('../../layouts/navbar/navbar.php');

/* Helpers */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.0.7/b-3.0.2/r-3.0.3/fh-4.0.1/datatables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.min.css" />

<style>
.dt-buttons .btn {
    margin-right: .5rem;
}

table.dataTable thead th {
    position: sticky;
    top: 0;
    background: #fff;
    z-index: 2;
}

.dataTables_wrapper .dataTables_length select {
    min-width: 70px;
}

.card-title .menu-icon {
    margin-right: .5rem;
}

.badge-inactif {
    background-color: #dc3545;
}
</style>

<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">
                            <button class="btn btn-dark" onclick="history.back()">Retour</button> <span
                                class="menu-icon"><i class="fa fa-user-times"></i></span>Familles douteuses <span
                                class="badge badge-inactif">Inactif</span>
                        </h3>

                        <div class="table-responsive">
                            <table class="table align-middle" id="menageTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Famille</th>
                                        <th>Enfants</th>
                                        <th>Montant</th>
                                        <th>Frais connexe</th>
                                        <th>Montant payé</th>
                                        <th>Téléphone</th>
                                        <th>Localisation</th>
                                        <th>Montant à payé/payé <span class="text-danger">(Solde)</span></th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($rst_inactif->num_rows > 0): while ($row = $rst_inactif->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <a href="detail_menage.php?id=<?php echo (int)$row['id']; ?>">
                                                <?php echo (int)$row['id']; ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="detail_menage.php?id=<?php echo (int)$row['id']; ?>">
                                                <?php echo e($row['noms']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo (int)$row['nbreEnfant']; ?></td>
                                        <td><?php echo number_format((float)$row['montantAPayer'], 2, ',', ' '); ?>$
                                        </td>
                                        <td>
                                            <?php echo number_format((float)$row['frais_connexe'], 2, ',', ' '); ?>$
                                        </td>
                                        <td>
                                            <?php echo number_format((float)$row['montant_paye'], 2, ',', ' '); ?>$
                                        </td>
                                        <td><?php echo e($row['telephone']); ?></td>
                                        <td>
                                            <?php echo e($row['avenue']); ?>,
                                            <?php echo e($row['numero']); ?>,
                                            <?php echo e($row['quartier']); ?>,
                                            <?php echo e($row['commune']); ?>
                                        </td>

                                        <?php 
                                            $montant_paye = (float)$row['montant_paye'];
                                            $totalAnnel = (float)$row['frais_connexe'] + (float)$row['montantAPayer'];
                                            $reste = $totalAnnel - (float)$row['montant_paye'];
                                            $reste = max(0, $reste);
                                          ?>

                                        <td>
                                            <?php echo number_format($totalAnnel, 2, ',', ' '); ?>$/<?php echo number_format($montant_paye, 2, ',', ' '); ?>$
                                            <span
                                                class="text-danger">(<?php echo number_format($reste, 2, ',', ' '); ?>$)</span>
                                        </td>
                                        <td>
                                            <a href="detail_menage.php?id=<?php echo (int)$row['id']; ?>"
                                                class="btn btn-secondary btn-sm">Voir</a>

                                            <!-- Bouton activer -->
                                            <a href="../../webapp/service/menage.service.create.update.php?id=<?php echo (int)$row['id']; ?>&status=actif"
                                                class="btn btn-success btn-sm"
                                                onclick="return confirm('Activer ce ménage ?')">
                                                Activer
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end">Totaux :</th>
                                        <th id="totalEnfants"></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th colspan="2"></th>
                                        <th>
                                            <span id="totalAnnuel"></span> /
                                            <span id="totalPaye"></span>
                                            (<span class="text-danger" id="totalSolde"></span>)
                                        </th>
                                        <th></th>
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

<?php require_once ('../../layouts/constants/footer.php'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script src="https://cdn.datatables.net/v/bs5/dt-2.0.7/b-3.0.2/r-3.0.3/fh-4.0.1/datatables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const table = new DataTable('#menageTable', {
        responsive: true,
        fixedHeader: true,
        pageLength: 100,
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.7/i18n/fr-FR.json"
        },

        footerCallback: function(row, data, start, end, display) {

            let api = this.api();

            let intVal = function(i) {
                if (typeof i === 'string') {
                    return parseFloat(
                        i.replace(/\s/g, '')
                        .replace(',', '.')
                        .replace(/[^\d.]/g, '')
                    ) || 0;
                }
                return typeof i === 'number' ? i : 0;
            };

            let totalMontant = api.column(3, {
                    page: 'current'
                }).data()
                .reduce((a, b) => intVal(a) + intVal(b), 0);

            let totalFrais = api.column(4, {
                    page: 'current'
                }).data()
                .reduce((a, b) => intVal(a) + intVal(b), 0);

            let totalPaye = api.column(5, {
                    page: 'current'
                }).data()
                .reduce((a, b) => intVal(a) + intVal(b), 0);

            let totalAnnuel = totalMontant + totalFrais;
            let totalSolde = totalAnnuel - totalPaye;

            document.getElementById('totalAnnuel').innerHTML =
                totalAnnuel.toLocaleString('fr-FR') + ' $';

            document.getElementById('totalPaye').innerHTML =
                totalPaye.toLocaleString('fr-FR') + ' $';

            document.getElementById('totalSolde').innerHTML =
                totalSolde.toLocaleString('fr-FR') + ' $';
        }
    });

    // 🔥 FORCER recalcul après 500ms (IMPORTANT)
    setTimeout(() => {
        table.draw();
    }, 500);

});

console.log("FOOTER CALLBACK OK");
</script>