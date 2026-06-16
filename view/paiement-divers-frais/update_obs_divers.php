<?php
// /view/paiement_divers/update.php
declare(strict_types=1);
if (session_status()===PHP_SESSION_NONE) session_start();

require_once('../../layouts/constants/head.php');
require_once('../../layouts/navbar/navbar.php');

// Helpers
if (!function_exists('e'))  { function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('nf')) { function nf($n){ return number_format((float)$n, 2, ',', ' '); } }

// DB
if (!isset($con) || !($con instanceof mysqli)) {
  require_once('../../webapp/database/config.php'); // $con
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$con->set_charset('utf8mb4');

// CSRF
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION['csrf_token'];

// ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo '<div class="alert alert-danger m-3">ID invalide.</div>';
  exit;
}

// Charger la ligne (avec info ménage pour affichage)
$stmt = $con->prepare("
  SELECT pd.*, m.noms AS menage_nom, m.telephone
  FROM paiement_divers pd
  JOIN menage m ON m.id = pd.menage
  WHERE pd.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
  http_response_code(404);
  echo '<div class="alert alert-warning m-3">Paiement introuvable.</div>';
  exit;
}

// POST: mise à jour de l'observation + dateCreated
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
      throw new RuntimeException('Jeton CSRF invalide.');
    }
    $newObs = trim((string)($_POST['observation'] ?? ''));

    // Conserver ancienne dateCreated dans l'observation
    $oldDate = $row['dateCreated']; // ancien timestamp
    $stamp   = date('d/m/Y H:i');

    // Construction observation finale :
    // - on ajoute une ligne d’historique avec l’ancienne dateCreated
    // - on garde la saisie de l’utilisateur
    $historique = "— Ancienne dateCreated: ".($oldDate ?: 'n/a');
    $finalObs   = $newObs !== '' ? ($newObs."\n".$historique) : $historique;

    // Transaction
    $con->begin_transaction();
    $stmt = $con->prepare("
      UPDATE paiement_divers
      SET observation = ?
      WHERE id = ?
    ");
    $stmt->bind_param('si', $finalObs, $id);
    $stmt->execute();
    $stmt->close();
    $con->commit();

    // Reload pour refléter la nouvelle valeur
    header('Location: liste_paiement_frais_divers.php?updated=1');
    exit;

  } catch (Throwable $t) {
    if ($con->errno) { $con->rollback(); }
    $error = $t->getMessage();
  }
}
?>
<div class="main-panel-copy">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">

                        <h3 class="card-title d-flex align-items-center gap-2">
                            <span class="menu-icon"><i class="fa fa-pencil"></i></span>
                            Modifier l'observation (Paiement #<?php echo e($row['id']); ?>)
                        </h3>

                        <div class="mb-2">
                            <a class="btn btn-dark" href="javascript:history.back()"><i
                                    class="fa fa-arrow-left me-1"></i> Retour</a>
                            <a class="btn btn-secondary" href="liste_paiement_frais_divers.php"><i
                                    class="fa fa-list-ul me-1"></i> Liste</a>
                        </div>

                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo e($error); ?></div>
                        <?php endif; ?>

                        <!-- Infos en lecture seule -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Ménage</label>
                                <input class="form-control" value="<?php echo e($row['menage_nom'] ?? ''); ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Téléphone</label>
                                <input class="form-control" value="<?php echo e($row['telephone'] ?? ''); ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type de frais</label>
                                <input class="form-control" value="<?php echo e($row['type_frais'] ?? ''); ?>" disabled>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">À payer</label>
                                <input class="form-control text-end" value="<?php echo nf($row['montantAPayer']); ?> $"
                                    disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Payé</label>
                                <input class="form-control text-end" value="<?php echo nf($row['montantPayer']); ?> $"
                                    disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reste</label>
                                <input class="form-control text-end" value="<?php echo nf($row['resteAPayer']); ?> $"
                                    disabled>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Année scolaire</label>
                                <input class="form-control" value="<?php echo e($row['anneeScolaire'] ?? ''); ?>"
                                    disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date actuelle (sera remplacée)</label>
                                <input class="form-control" value="<?php echo e($row['dateCreated'] ?? ''); ?>"
                                    disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Créé par</label>
                                <input class="form-control" value="<?php echo e($row['createdby'] ?? ''); ?>" disabled>
                            </div>
                        </div>

                        <hr>

                        <!-- Formulaire: seule l’observation est modifiable -->
                        <form method="post" class="mt-3">
                            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                            <div class="mb-3">
                                <label class="form-label">Observation (éditable)</label>
                                <textarea name="observation" rows="6" class="form-control"
                                    placeholder="Saisissez la nouvelle observation..."><?php
                    echo e((string)$row['observation']);
                  ?></textarea>
                                <div class="form-text">
                                    À la sauvegarde : l’ancienne <code>dateCreated</code> sera ajoutée automatiquement
                                    en bas de l’observation,
                                    et <code>dateCreated</code> sera mise à aujourd’hui.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Enregistrer
                            </button>
                            <a href="liste_paiement_frais_divers.php" class="btn btn-outline-secondary">Annuler</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once('../../layouts/constants/footer.php'); ?>
</div>