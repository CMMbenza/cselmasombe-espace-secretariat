<?php
require_once ('../../layouts/constants/head.php'); 
require_once ('../../webapp/service/eleve.service.php'); // Doit fournir $rst (mysqli_result)
require_once ('../../layouts/navbar/navbar.php');

/* ===== Helpers ===== */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
?>

<!-- DataTables CSS (bundle + Buttons Bootstrap 5) -->
<link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.0.7/b-3.0.2/r-3.0.3/fh-4.0.1/datatables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.min.css" />

<style>
.dt-buttons .btn { margin-right: .5rem; }
.filters-row th { background: #f8f9fa; position: sticky; top: 56px; z-index: 1; }
table.dataTable thead th { position: sticky; top: 0; background: #fff; z-index: 2; }
.dataTables_wrapper .dataTables_length select { min-width: 70px; }
.card-title .menu-icon { margin-right: .5rem; }
</style>

<div class="main-panel-copy">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h3 class="card-title">
              <span class="menu-icon"><i class="fa fa-user-circle"></i></span>Elève
            </h3>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <div class="d-flex align-items-center gap-2">
                <a href="create-update.php" class="btn btn-success">Nouveau élève</a>
                <a class="btn btn-dark" href="../menage/">Ouvrir dossier famille</a>
              </div>

              <div class="ms-auto" style="min-width:280px;">
                <input type="text" class="form-control" id="globalSearch" placeholder="Rechercher (nom, lieu, etc.)">
              </div>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6 col-md-3">
                <label class="form-label mb-1">Filtrer par Genre</label>
                <select id="filterGenre" class="form-control">
                  <option value="">Tous</option>
                  <option value="M">M</option>
                  <option value="F">F</option>
                </select>
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label mb-1">Filtrer par Classe</label>
                <input id="filterClasse" class="form-control" placeholder="ex: 6ème, 2ème...">
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label mb-1">Filtrer par Ménage</label>
                <input id="filterMenage" class="form-control" placeholder="Entrez le nom ménage">
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-responsive align-middle" id="elevesTable" style="width:100%">
                <thead>
                  <tr>
                    <th style="width: 90px;">ID</th>
                    <th>Noms</th>
                    <th style="width: 80px;">Genre</th>
                    <th>Lieu / Date de naissance</th>
                    <th>Classe</th>
                    <th style="width: 110px;">Ménage</th>
                    <th style="width: 220px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($rst->num_rows > 0): while ($row = $rst->fetch_assoc()): ?>
                    <tr>
                      <td><a href="detail_eleve.php?id=<?php echo (int)$row['id']; ?>"><?php echo (int)$row['id']; ?></a></td>
                      <td><?php echo e(trim($row['nom'].' '.$row['postnom'].' '.$row['prenom'])); ?></td>
                      <td><?php echo e($row['genre']); ?></td>
                      <td>à <?php echo e($row['lieu']); ?>, le <?php echo e($row['dateDeNaissance']); ?></td>
                      <td><?php echo e($row['classe']); ?> <?php echo e($row['cycle']); ?></td>
                      <td> <a href="../menage/detail_menage.php?id=<?php echo (int)$row['id_menage']; ?>"><?php echo e($row['menage']); ?></a> </td>
                      <td>
                        <a href="create-update.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <a href="detail_eleve.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-secondary btn-sm">Voir</a>
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
<!-- 1) JSZip pour Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<!-- 2) pdfmake (facultatif pour PDF) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- 3) DataTables core (bundle) -->
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.7/b-3.0.2/r-3.0.3/fh-4.0.1/datatables.min.js"></script>
<!-- 4) Buttons Bootstrap 5 (intégration visuelle) -->
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.min.js"></script>
<!-- 5) Boutons HTML5 & Print -->
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>

<script>
// (Optionnel) suppression
function confirmDelete(id) {
  if (confirm("Supprimer l'élève #" + id + " ?")) {
    alert("Action de suppression à implémenter (service serveur).");
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const table = new DataTable('#elevesTable', {
    responsive: true,
    fixedHeader: true,
    paging: true,
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100, -1],[10, 25, 50, 100, "Tous"]],
    ordering: true,
    info: true,
    searching: true,
    language: { url: "https://cdn.datatables.net/plug-ins/2.0.7/i18n/fr-FR.json" },
    dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-md-end'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    buttons: [
      {
        extend: 'csv',
        text: 'Exporter CSV',
        className: 'btn btn-outline-primary btn-sm',
        fieldSeparator: ';',
        bom: true,
        charset: 'utf-8',
        exportOptions: { columns: [0,1,2,3,4,5] },
        title: 'eleves_export_<?php echo date("Ymd_His"); ?>'
      },
      {
        extend: 'excel',
        text: 'Exporter XLSX',
        className: 'btn btn-outline-success btn-sm',
        exportOptions: { columns: [0,1,2,3,4,5] },
        title: 'eleves_export_<?php echo date("Ymd_His"); ?>'
      },
      {
        extend: 'pdf',
        text: 'PDF',
        className: 'btn btn-outline-danger btn-sm',
        exportOptions: { columns: [0,1,2,3,4,5] },
        title: 'eleves_export_<?php echo date("Ymd_His"); ?>'
      },
      {
        extend: 'print',
        text: 'Imprimer',
        className: 'btn btn-outline-secondary btn-sm',
        exportOptions: { columns: [0,1,2,3,4,5] }
      }
    ],
    columnDefs: [{ targets: [6], orderable: false }]
  });

  // Recherche globale
  const globalSearch = document.getElementById('globalSearch');
  if (globalSearch) globalSearch.addEventListener('keyup', () => table.search(globalSearch.value).draw());

  // Filtres
  document.getElementById('filterGenre')?.addEventListener('change', function(){ table.column(2).search(this.value).draw(); });
  document.getElementById('filterClasse')?.addEventListener('keyup', function(){ table.column(4).search(this.value).draw(); });
  document.getElementById('filterMenage')?.addEventListener('keyup', function(){
    const v = this.value.trim();
    if (/^\d+$/.test(v)) table.column(5).search('^' + v + '$', true, false).draw();
    else table.column(5).search(v).draw();
  });
});
</script>
