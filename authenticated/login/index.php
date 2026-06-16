<?php
// login.php
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

function looks_hashed(?string $hash): bool {
    if (!$hash) return false;
    $info = password_get_info($hash);
    return !empty($info['algo']);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Veuillez renseigner l'identifiant et le mot de passe.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :u");
        $stmt->execute([':u' => $username]);
        $rows = $stmt->fetchAll();

        $matched = null;
        foreach ($rows as $row) {
            $stored = (string)$row['password'];
            if (looks_hashed($stored)) {
                if (password_verify($password, $stored)) {
                    $matched = $row;
                    if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $upd = $pdo->prepare("UPDATE users SET password = :p, dateModification = CURDATE() WHERE id = :id");
                        $upd->execute([':p' => $newHash, ':id' => $row['id']]);
                    }
                    break;
                }
            } else {
                if (hash_equals($stored, $password)) {
                    $matched = $row;
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $pdo->prepare("UPDATE users SET password = :p, dateModification = CURDATE() WHERE id = :id");
                    $upd->execute([':p' => $newHash, ':id' => $row['id']]);
                    break;
                }
            }
        }

        if ($matched) {
            $_SESSION['user_id'] = (int)$matched['id'];
            $_SESSION['username'] = (string)$matched['username'];
            $_SESSION['role'] = (string)$matched['role'];
            $_SESSION['login_time'] = time();
            session_regenerate_id(true);
            header('Location: ../../view/dashboard/');
            exit;
        } else {
            $error = "Identifiants incorrects.";
        }
    }
}
?>
<!doctype html>
<html lang="fr" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
    :root {
        --brand: #1f6feb;
        --brand-600: #1b5fd0;
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-br: 18px;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: #fff;
        /* Fond simple blanc */
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px 30px;
        /* plus d'espace à gauche/droite */
    }

    .card-glass {
        background: var(--glass-bg);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: var(--glass-br);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        max-width: 750px;
        /* élargi le bloc (gauche/droite) */
        width: 100%;
    }

    .brand-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .brand-logo {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--brand), var(--brand-600));
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(31, 111, 235, 0.35);
    }

    .brand-text {
        font-weight: 700;
        letter-spacing: .3px;
        color: #0f172a;
    }

    .form-title {
        font-weight: 700;
        letter-spacing: .2px;
        color: #0f172a;
    }

    .subtle {
        color: #475569;
    }

    .btn-primary {
        background: var(--brand);
        border-color: var(--brand);
        /*box-shadow: 0 6px 16px rgba(31, 111, 235, 0.35);*/
    }

    .btn-primary:hover {
        background: var(--brand-600);
        border-color: var(--brand-600);
    }

    .form-floating>.form-control {
        padding-top: 1.3rem;
        padding-bottom: .6rem;
        border-radius: 12px;
    }

    .form-floating>label {
        color: #6b7280;
    }

    .input-group .btn-outline-secondary {
        border-color: #e5e7eb;
    }

    .divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 18px 0;
        color: #6b7280;
        font-size: .9rem;
    }

    .divider::before,
    .divider::after {
        content: '';
        height: 1px;
        flex: 1;
        background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
    }

    .error-alert {
        border-radius: 12px;
        border: 1px solid #fecaca;
        background: #fff1f2;
        color: #991b1b;
    }

    .footer-muted {
        color: #6b7280;
        font-size: .85rem;
    }
    </style>
</head>

<body>
    <div class="card-glass p-4 p-md-5">
        <div class="brand-badge">
            <div class="brand-logo">CS</div>
            <div class="brand-text">C.S ELMA SOMBE</div>
        </div>

        <h1 class="h3 form-title mb-1">Connexion</h1>
        <p class="subtle mb-4">Accédez à votre tableau de bord en toute sécurité.</p>

        <?php if ($error): ?>
        <div class="alert error-alert py-2 px-3 mb-4" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="post" autocomplete="off" class="needs-validation" novalidate>
            <div class="form-floating mb-3">
                <input type="text" name="username" id="username" class="form-control" placeholder="Identifiant"
                    required>
                <label for="username">Nom d'utilisateur</label>
                <div class="invalid-feedback">Veuillez saisir votre nom d'utilisateur.</div>
            </div>

            <div class="mb-3">
                <div class="input-group">
                    <div class="form-floating">
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="Mot de passe" required>
                        <label for="password">Mot de passe</label>
                        <div class="invalid-feedback">Veuillez saisir votre mot de passe.</div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary" id="togglePass"
                        aria-label="Afficher/Masquer le mot de passe">
                        <span id="eyeOpen" style="display:none;">👁️</span>
                        <span id="eyeClosed">👁️‍🗨️</span>
                    </button>
                </div>
                <!-- <div class="form-text">Ne partagez jamais vos identifiants.</div> -->
            </div>

            <button class="btn btn-primary w-100 py-2 mb-2">Se connecter</button>

            <!--
            <div class="divider">ou</div>
            <p class="text-center mb-0">
                <a class="link-underline" href="add_user.php">Créer un utilisateur</a>
            </p>
            -->
        </form>

        <hr class="my-4">
        <div class="d-flex justify-content-between align-items-center">
            <span class="footer-muted">© <?= date('Y') ?> • C.S ELMA SOMBE</span>
            <!--<a class="footer-muted link-underline" href="#" onclick="return false;">Besoin d’aide ?</a>-->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const toggle = document.getElementById('togglePass');
    const pwd = document.getElementById('password');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');

    toggle?.addEventListener('click', () => {
        const isText = pwd.type === 'text';
        pwd.type = isText ? 'password' : 'text';
        eyeOpen.style.display = isText ? 'none' : 'inline';
        eyeClosed.style.display = isText ? 'inline' : 'none';
    });

    (() => {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
    </script>
</body>

</html>