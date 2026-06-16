<?php
// users_list.php — Liste des utilisateurs + recherche + suppression (CSRF) + UI simple
session_start();

$mysqli = new mysqli('localhost', 'cselmasombe_admin', 'na57k,ad-$h#', 'cselmasombe_admin');
if ($mysqli->connect_error) { die("Erreur DB: " . $mysqli->connect_error); }
$mysqli->set_charset('utf8mb4');

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// CSRF token global
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf = $_SESSION['csrf_token'];

// Suppression (POST)
$msg = $_GET['msg'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $msg = 'csrf_error';
    } else {
        $delete_id = (int)($_POST['delete_id'] ?? 0);
        if ($delete_id > 0) {
            // ⚠️ Évite de permettre la suppression de ton propre compte admin si tu veux
            $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $delete_id);
            if ($stmt->execute()) {
                $msg = 'deleted';
            } else {
                $msg = 'delete_fail';
            }
            $stmt->close();
        }
    }
}

// Recherche
$q = trim($_GET['q'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');

$sql = "SELECT id, username, role, dateCreation, dateModification FROM users WHERE 1";
$params = [];
$types  = '';

if ($q !== '') {
    $sql .= " AND username LIKE CONCAT('%', ?, '%')";
    $params[] = $q; $types .= 's';
}
if ($roleFilter !== '') {
    $sql .= " AND role = ?";
    $params[] = $roleFilter; $types .= 's';
}

$sql .= " ORDER BY id DESC";

$stmt = $mysqli->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

$ROLES_ALLOWED = ['','admin','formateur','apprenant','promoteur'];
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Liste des utilisateurs</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- UI -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{ background:#f6f7fb; }
    .card{ border:none; border-radius:1rem; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .table th{ white-space:nowrap; }
    .badge-role{ text-transform:capitalize; }
  </style>
</head>
<body class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">📄 Liste des utilisateurs</h1>
    <div class="d-flex gap-2">
      <a class="btn btn-primary" href="creer_user.php">➕ Créer un utilisateur</a>
    </div>
  </div>

  <?php if ($msg === 'created'): ?>
    <div class="alert alert-success">Utilisateur créé avec succès.</div>
  <?php elseif ($msg === 'deleted'): ?>
    <div class="alert alert-success">Utilisateur supprimé.</div>
  <?php elseif ($msg === 'delete_fail'): ?>
    <div class="alert alert-danger">Échec de suppression.</div>
  <?php elseif ($msg === 'csrf_error'): ?>
    <div class="alert alert-danger">Échec CSRF. Recharge la page et réessaie.</div>
  <?php endif; ?>

  <div class="card p-4 mb-3">
    <form class="row g-2 align-items-end" method="get">
      <div class="col-md-6">
        <label class="form-label">Recherche par username</label>
        <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="ex: jean">
      </div>
      <div class="col-md-3">
        <label class="form-label">Filtrer par rôle</label>
        <select class="form-select" name="role">
          <?php foreach ($ROLES_ALLOWED as $r): ?>
            <option value="<?= e($r) ?>" <?= $roleFilter===$r ? 'selected':'' ?>>
              <?= $r===''?'(Tous)':e($r) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-grid">
        <button class="btn btn-outline-secondary" type="submit">🔎 Rechercher</button>
      </div>
    </form>
  </div>

  <div class="card p-0">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Rôle</th>
            <th>Créé le</th>
            <th>Modifié le</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">Aucun utilisateur trouvé.</td></tr>
          <?php else: foreach ($rows as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?= e($u['username']) ?></td>
              <td>
                <span class="badge bg-secondary badge-role"><?= e($u['role']) ?></span>
              </td>
              <td><?= e($u['dateCreation']) ?></td>
              <td><?= e($u['dateModification']) ?></td>
              <td class="text-end">
                <form method="post" onsubmit="return confirm('Supprimer cet utilisateur ?');" class="d-inline">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="delete_id" value="<?= (int)$u['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">🗑️ Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <p class="text-muted small mt-3">
    Conseil : mets un index <code>UNIQUE</code> sur <code>username</code> pour éviter les doublons,
    et retire les affichages de debug dans <code>login.php</code>.
  </p>
</body>
</html>
