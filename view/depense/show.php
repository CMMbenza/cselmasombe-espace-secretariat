<?php
// /votre/chemin/vers/depenses/show.php
declare(strict_types=1);

require_once ('../../layouts/constants/head.php');
require_once ('../../layouts/navbar/navbar.php');

// Si $con n'a pas été défini par head.php, on tente la config directe
if (!isset($con) || !($con instanceof mysqli)) {
  require_once('../../webapp/database/config.php');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$con->set_charset('utf8mb4');

/* Helpers (protégés contre redeclare) */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('fmt_money')) {
  function fmt_money($n){ return number_format((float)$n, 2, ',', ' '); }
}
if (!function_exists('fmt_date')) {
  function fmt_date($d){ return ($d && $d !== '0000-00-00') ? date('d/m/Y', strtotime($d)) : ''; }
}

/* Récupération & validation de l'id */
$idParam = $_GET['id'] ?? '';
if (!ctype_digit((string)$idParam)) {
  $errorMsg = "Identifiant invalide.";
  $row = null;
} else {
  $id = (int)$idParam;

  // Requête préparée
  $sql = "SELECT id, beneficiaire, reference, description, montant, dateCreaty, dateUpdate, createdby, anneeScolaire
          FROM depenses
          WHERE id = ?
          LIMIT 1";
  $st = $con->prepare($sql);
  $st->bind_param('i', $id);
  $st->execute();
  $res = $st->get_result();
  $row = $res->fetch_assoc();
  $st->close();

  if (!$row) {
    $errorMsg = "Aucune dépense trouvée pour l'identifiant demandé.";
  }
}
?>
<div class="main-panel-copy">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h3 class="card-title d-flex align-items-center gap-2 mb-0">
                <span class="menu-icon"><i class="fa fa-receipt"></i></span>
                Détail de la dépense
              </h3>
              <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-secondary">
                  <i class="fa fa-arrow-left me-1"></i> Retour à la liste
                </a>
                <?php if (!empty($row)): ?>
                  <!-- Si tu as une page d’édition, adapte l’URL ici -->
                  <!-- <a href="edit.php?id=<?php echo e($row['id']); ?>" class="btn btn-primary"><i class="fa fa-pencil me-1"></i> Modifier</a> -->
                <?php endif; ?>
              </div>
            </div>

            <?php if (!empty($errorMsg)): ?>
              <div class="alert alert-warning"><?php echo e($errorMsg); ?></div>
            <?php else: ?>
              <div class="row g-3">
                <div class="col-md-3">
                  <div class="small text-muted">ID</div>
                  <div class="fw-bold"><?php echo e($row['id']); ?></div>
                </div>

                <div class="col-md-3">
                  <div class="small text-muted">Référence</div>
                  <div class="fw-bold"><?php echo e($row['reference'] ?? ''); ?></div>
                </div>

                <div class="col-md-6">
                  <div class="small text-muted">Bénéficiaire</div>
                  <div class="fw-bold"><?php echo e($row['beneficiaire'] ?? ''); ?></div>
                </div>

                <div class="col-md-12">
                  <div class="small text-muted">Motif</div>
                  <div class="fw-bold"><?php echo nl2br(e($row['description'] ?? '')); ?></div>
                </div>

                <div class="col-md-3">
                  <div class="small text-muted">Montant</div>
                  <div class="fw-bold"><?php echo fmt_money($row['montant'] ?? 0); ?> $</div>
                </div>

                <div class="col-md-3">
                  <div class="small text-muted">Date dépense</div>
                  <div class="fw-bold"><?php echo fmt_date($row['dateCreaty'] ?? null); ?></div>
                </div>

                <div class="col-md-3">
                  <div class="small text-muted">Dernière modif.</div>
                  <div class="fw-bold"><?php echo fmt_date($row['dateUpdate'] ?? null); ?></div>
                </div>

                <div class="col-md-3">
                  <div class="small text-muted">Année scolaire</div>
                  <div class="fw-bold"><?php echo e($row['anneeScolaire'] ?? ''); ?></div>
                </div>

                <div class="col-md-4">
                  <div class="small text-muted">Créé par</div>
                  <div class="fw-bold"><?php echo e($row['createdby'] ?? ''); ?></div>
                </div>
              </div>

              <hr class="my-4">

              <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-dark">
                  <i class="fa fa-list me-1"></i> Retour à la liste
                </a>
                <!-- Boutons optionnels si tu implémentes plus tard -->
                <!-- <a href="edit.php?id=<?php echo e($row['id']); ?>" class="btn btn-primary"><i class="fa fa-pencil me-1"></i> Modifier</a> -->
                <!-- <form method="post" action="delete.php" onsubmit="return confirm('Supprimer cette dépense ?');" class="d-inline">
                  <input type="hidden" name="id" value="<?php echo e($row['id']); ?>">
                  <button type="submit" class="btn btn-danger"><i class="fa fa-trash me-1"></i> Supprimer</button>
                </form> -->
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once ('../../layouts/constants/footer.php'); ?>
</div>
