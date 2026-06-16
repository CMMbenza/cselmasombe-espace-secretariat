<?php
// add_user.php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) session_start();

/* ===== DB CONFIG ===== */
$DB_HOST = 'localhost';
$DB_NAME = 'cselmasombe_admin';
$DB_USER = 'cselmasombe_admin';
$DB_PASS = 'na57k,ad-$h#';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Throwable $e) {
    http_response_code(500);
    exit('Erreur: connexion BD indisponible.');
}

$ok = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role     = trim((string)($_POST['role'] ?? 'user'));

    if ($username === '' || $password === '') {
        $error = "Username et mot de passe sont requis.";
    } elseif (strlen($password) < 4) {
        $error = "Mot de passe trop court (minimum 4 caractères pour ce test).";
    } else {
        // Pas d'unicité sur username : on autorise les doublons comme demandé
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, role, dateCreation, dateModification)
            VALUES (:u, :p, :r, CURDATE(), CURDATE())
        ");
        try {
            $stmt->execute([':u' => $username, ':p' => $hash, ':r' => $role]);
            $ok = "Utilisateur créé (ID: ".$pdo->lastInsertId().").";
        } catch (Throwable $e) {
            $error = "Insertion impossible.";
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ajouter un utilisateur</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-sm-10 col-md-6 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h1 class="h4 mb-3 text-center">Nouvel utilisateur</h1>

          <?php if ($ok): ?>
            <div class="alert alert-success py-2"><?= htmlspecialchars($ok) ?></div>
          <?php endif; ?>
          <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="post" autocomplete="off">
            <div class="mb-3">
              <label class="form-label">Username (peut être dupliqué)</label>
              <input type="text" name="username" class="form-control" required>
              <div class="form-text">Plusieurs comptes peuvent partager ce même identifiant.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Mot de passe</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Rôle</label>
              <select name="role" class="form-select">
                <option value="Promoteur">Promoteur</option>
                <option value="DGAF">DGAF</option>
                <option value="SEC">SEC</option>
                <option value="ADMIN">ADMIN</option>
                <option value="DIR">DIR</option>
              </select>
            </div>
            <button class="btn btn-success w-100">Créer</button>
          </form>

          <hr>
          <p class="mb-0 text-center">
            <a href="login.php">Aller à la connexion</a>
          </p>
        </div>
      </div>
      <p class="text-center text-muted mt-3 small">&copy; <?= date('Y') ?></p>
    </div>
  </div>
</div>
</body>
</html>
