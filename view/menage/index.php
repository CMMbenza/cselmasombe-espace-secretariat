<?php
require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/menage.service.php'); // Doit fournir $rst (mysqli_result) avec: id, noms, nbreEnfant, montantAPayer, telephone, avenue, numero, quartier, commune
require_once ('../../layouts/navbar/navbar.php');

/* Helpers */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
?>

<!-- DataTables CSS + Buttons Bootstrap5 -->
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
</style>

<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">
                            <span class="menu-icon"><i class="fa fa-user-circle"></i></span>Famille
                        </h3>
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                            <div class="d-flex align-items-center gap-2">
                                <a href="create-update.php" class="btn btn-success">Nouvelle famille</a>
                                <a href="../eleve/" class="btn btn-dark">Gest. élève</a>
                                <a href="menage_douteux.php" class="btn btn-primary">Liste des familles douteuses</a>
                                <a href="../controle/" class="btn btn-danger">Situation financière des familles</a>
                                 <a href="../other/extraire_inscription.php" class="btn btn-outline-success">
                                    <i class="mdi mdi-file-export me-1"></i> Gest. de l'inscription
                                </a>
                            </div>
                            <div class="ms-auto" style="min-width:280px;">
                                <input type="text" class="form-control" id="globalSearch"
                                    placeholder="Rechercher (famille, téléphone, commune...)">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label mb-1">Commune</label>
                                <input id="filterCommune" class="form-control" placeholder="ex: Kasa-Vubu">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label mb-1">Quartier</label>
                                <input id="filterQuartier" class="form-control" placeholder="ex: Matonge">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label mb-1">Téléphone</label>
                                <input id="filterTel" class="form-control" placeholder="ex: 099...">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label mb-1">ID Famille</label>
                                <input id="filterId" class="form-control" placeholder="ID exact">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-responsive align-middle" id="menageTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width:100px;">ID Famille</th>
                                        <th>Famille</th>
                                        <th>Nbre d'enfants</th>
                                        <th>Frais <span class="text-primary">scolaire</span>/ <span class="text-success">Connexe</span></th>
                                        <th>Téléphone</th>
                                        <th>Localisation</th>
                                        <th style="width:160px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($rst->num_rows > 0): while ($row = $rst->fetch_assoc()): ?>
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
                                        <td>
                                            <span class="badge badge-primary"><?php echo number_format((float)$row['montantAPayer'], 2, ',', ' '); ?> $</span>
                                            <span class="badge badge-success"><?php echo number_format((float)$row['montantAPayerFC'], 2, ',', ' '); ?> $</span>
                                        </td>
                                        <td><?php echo e($row['telephone']); ?></td>
                                        <td><?php echo e($row['avenue']); ?>, <?php echo e($row['numero']); ?>,
                                            <?php echo e($row['quartier']); ?>, <?php echo e($row['commune']); ?></td>
                                        <td class="">
                                            <a href="create-update.php?id=<?php echo (int)$row['id']; ?>"
                                                class="btn btn-primary btn-sm">Modifier</a>

                                            <a href="detail_menage.php?id=<?php echo (int)$row['id']; ?>"
                                                class="btn btn-secondary btn-sm">Voir</a>

                                            <a href="../../webapp/service/menage.service.create.update.php?id=<?php echo (int)$row['id']; ?>&status=inactif"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Désactiver cette famille ?')">
                                                Désactiver
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div><!-- card-body -->
                </div><!-- card -->
            </div><!-- col -->
        </div><!-- row -->
    </div><!-- content-wrapper -->
</div><!-- main-panel-copy -->

<?php require_once ('../../layouts/constants/footer.php'); ?>

<!-- Dépendances export (ORDRE IMPORTANT) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script> <!-- pour Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- DataTables core -->
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.7/b-3.0.2/r-3.0.3/fh-4.0.1/datatables.min.js"></script>
<!-- Buttons Bootstrap 5 + HTML5 + Print -->
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = new DataTable('#menageTable', {
        responsive: true,
        fixedHeader: true,
        paging: true,
        pageLength: 100,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "Tous"]
        ],
        ordering: true,
        info: true,
        searching: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.7/i18n/fr-FR.json"
        },
        dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-md-end'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [{
                extend: 'csv',
                text: 'Exporter CSV',
                className: 'btn btn-outline-primary btn-sm',
                fieldSeparator: ';',
                bom: true,
                charset: 'utf-8',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                },
                title: 'menage_export_<?php echo date("Ymd_His"); ?>'
            },
            {
                extend: 'excel',
                text: 'Exporter XLSX',
                className: 'btn btn-outline-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                },
                title: 'menage_export_<?php echo date("Ymd_His"); ?>'
            },
            {
                extend: 'pdf',
                text: 'PDF',
                className: 'btn btn-outline-danger btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                },
                title: 'menage_export_<?php echo date("Ymd_His"); ?>'
            },
            {
                extend: 'print',
                text: 'Imprimer',
                className: 'btn btn-outline-secondary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }
        ],
        columnDefs: [{
                targets: [6],
                orderable: false
            } // Actions non triables
        ]
    });

    // Recherche globale
    document.getElementById('globalSearch')?.addEventListener('keyup', function() {
        table.search(this.value).draw();
    });

    // Filtres colonnes
    document.getElementById('filterCommune')?.addEventListener('keyup', function() {
        table.column(5).search(this.value).draw(); // Localisation col 5
    });
    document.getElementById('filterQuartier')?.addEventListener('keyup', function() {
        // Localisation contient "avenue, numero, quartier, commune"
        table.column(5).search(this.value).draw();
    });
    document.getElementById('filterTel')?.addEventListener('keyup', function() {
        table.column(4).search(this.value).draw();
    });
    document.getElementById('filterId')?.addEventListener('keyup', function() {
        const v = this.value.trim();
        if (/^\d+$/.test(v)) table.column(0).search('^' + v + '$', true, false).draw(); // exact
        else table.column(0).search(v).draw();
    });
});
</script>