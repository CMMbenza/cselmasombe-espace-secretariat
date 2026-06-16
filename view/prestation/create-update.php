<?php
declare(strict_types=1);
session_start();
require_once ('../../webapp/database/config.php');

date_default_timezone_set('Africa/Kinshasa');

/* Sécurité (optionnel selon ton système) */
// if (!isset($_SESSION['user'])) {
//     header("Location: ../login.php");
//     exit;
// }

/* Récupération des agents */
$agents = [];
$res = $con->query("SELECT id, nom, postnom, prenom FROM agent ORDER BY nom");
while ($row = $res->fetch_assoc()) {
    $agents[] = $row;
}

$errors = [];
$success = false;

/* Traitement formulaire */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $agent_id = (int)($_POST['agent_id'] ?? 0);
    $date_presence = $_POST['date_presence'] ?? '';
    $statut = $_POST['statut'] ?? '';
    $heure_arrivee = $_POST['heure_arrivee'] ?? null;

    if ($agent_id <= 0) {
        $errors[] = "Veuillez sélectionner un agent.";
    }

    if (!$date_presence) {
        $errors[] = "La date de présence est obligatoire.";
    }

    if (!in_array($statut, ['present', 'retard', 'absent'], true)) {
        $errors[] = "Statut invalide.";
    }

    if ($statut !== 'absent' && !$heure_arrivee) {
        $errors[] = "L'heure d'arrivée est obligatoire pour présent ou retard.";
    }

    if (empty($errors)) {

        $stmt = $con->prepare("
            INSERT INTO presence_agent (agent_id, date_presence, heure_arrivee, statut)
            VALUES (?, ?, ?, ?)
        ");

        $heure_arrivee = ($statut === 'absent') ? null : $heure_arrivee;

        $stmt->bind_param(
            "isss",
            $agent_id,
            $date_presence,
            $heure_arrivee,
            $statut
        );

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Erreur lors de l'enregistrement.";
        }
    }
}

/* Fonction échappement */
function e(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une présence agent</title>
    <link rel="stylesheet" href="../assets/bootstrap.min.css">
</head>
<body>

<div class="container mt-4">
    <h3 class="mb-4">➕ Enregistrer une présence agent</h3>

    <?php if ($success): ?>
        <div class="alert alert-success">
            Présence enregistrée avec succès.
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">

        <div class="mb-3">
            <label class="form-label">Agent</label>
            <select name="agent_id" class="form-select" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($agents as $a): ?>
                    <option value="<?= $a['id'] ?>">
                        <?= e($a['nom'].' '.$a['postnom'].' '.$a['prenom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Date de présence</label>
            <input type="date" name="date_presence"
                   value="<?= date('Y-m-d') ?>"
                   class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Statut</label>
            <select name="statut" id="statut" class="form-select" required>
                <option value="">-- Choisir --</option>
                <option value="present">Présent</option>
                <option value="retard">Retard</option>
                <option value="absent">Absent</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Heure d’arrivée</label>
            <input type="time" name="heure_arrivee" id="heure_arrivee"
                   class="form-control">
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary">Enregistrer</button>
            <a href="index.php" class="btn btn-secondary">Retour</a>
        </div>

    </form>
</div>

<script>
const statut = document.getElementById('statut');
const heure = document.getElementById('heure_arrivee');

statut.addEventListener('change', () => {
    if (statut.value === 'absent') {
        heure.value = '';
        heure.disabled = true;
    } else {
        heure.disabled = false;
    }
});
</script>

</body>
</html>
