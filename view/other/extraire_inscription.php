<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../../webapp/database/config.php');
mysqli_set_charset($con, 'utf8mb4');
session_start();

if (!$con) {
    die("Erreur de connexion à la base de données");
}

/* =====================================================
   1. TRAITEMENT DE L'EXTRACTION / VALIDATION (POST)
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'valider_inscription') {
    
    $inscription_id = (int)$_POST['inscription_id'];
    
    mysqli_begin_transaction($con);

    try {
        // A. Récupération des données de l'inscription et du responsable lié
        $sql_select = "
            SELECT 
                i.*, 
                r.nom_complet AS resp_nom, 
                r.telephone1 AS resp_tel1, 
                r.telephone2 AS resp_tel2, 
                r.email AS resp_email, 
                r.adresse AS resp_adresse
            FROM inscriptions i
            INNER JOIN responsables r ON i.responsable_id = r.id
            WHERE i.id = ? AND i.statut = 'EN_ATTENTE'
            FOR UPDATE
        ";
        
        $stmt = mysqli_prepare($con, $sql_select);
        if (!$stmt) { throw new Exception("Erreur préparation lecture : " . mysqli_error($con)); }
        
        mysqli_stmt_bind_param($stmt, "i", $inscription_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($res) === 0) {
            throw new Exception("Inscription introuvable ou déjà traitée.");
        }

        $row = mysqli_fetch_assoc($res);

        // B. Insertion ou Récupération du Ménage (Responsable)
        $menage_id = null;
        $sql_check_menage = "SELECT id FROM menage WHERE telephone = ? OR (email = ? AND email != '') LIMIT 1";
        $stmt_check = mysqli_prepare($con, $sql_check_menage);
        mysqli_stmt_bind_param($stmt_check, "ss", $row['resp_tel1'], $row['resp_email']);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);

        if ($m = mysqli_fetch_assoc($res_check)) {
            $menage_id = $m['id'];
        } else {
            // Création d'un nouveau ménage
            $sql_ins_menage = "
                INSERT INTO menage (noms, telephone, avenue, email, dateCreated, STATUS) 
                VALUES (?, ?, ?, ?, NOW(), 'actif')
            ";
            $stmt_ins_m = mysqli_prepare($con, $sql_ins_menage);
            if (!$stmt_ins_m) { throw new Exception("Erreur création ménage : " . mysqli_error($con)); }
            
            mysqli_stmt_bind_param($stmt_ins_m, "ssss", 
                $row['resp_nom'], 
                $row['resp_tel1'], 
                $row['resp_adresse'], 
                $row['resp_email']
            );
            
            if (!mysqli_stmt_execute($stmt_ins_m)) {
                throw new Exception("Erreur insertion ménage : " . mysqli_stmt_error($stmt_ins_m));
            }
            $menage_id = mysqli_insert_id($con);
        }

        // C. Insertion dans la table des Élèves actifs
        $sql_ins_eleve = "
            INSERT INTO eleve (
                nom, postnom, prenom, genre, lieu, dateDeNaissance, 
                classe, menage, anneeScolaire, dateCreated, STATUS
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'actif')
        ";
        
        $stmt_eleve = mysqli_prepare($con, $sql_ins_eleve);
        if (!$stmt_eleve) { throw new Exception("Erreur préparation élève : " . mysqli_error($con)); }

        mysqli_stmt_bind_param($stmt_eleve, "sssssssis", 
            $row['nom'], 
            $row['postnom'], 
            $row['prenom'], 
            $row['genre'], 
            $row['lieu_naissance'], 
            $row['date_naissance'], 
            $row['classe'], 
            $menage_id, 
            $row['annee_scolaire']
        );

        if (!mysqli_stmt_execute($stmt_eleve)) {
            throw new Exception("Erreur création fiche élève : " . mysqli_stmt_error($stmt_eleve));
        }

        // D. Mise à jour du statut dans inscriptions (Conservation des données)
        $sql_upd = "UPDATE inscriptions SET statut = 'VALIDE' WHERE id = ?";
        $stmt_upd = mysqli_prepare($con, $sql_upd);
        mysqli_stmt_bind_param($stmt_upd, "i", $inscription_id);
        mysqli_stmt_execute($stmt_upd);

        mysqli_commit($con);
        
        $_SESSION['success'] = "L'élève " . htmlspecialchars($row['nom'] . " " . $row['prenom']) . " a été extrait avec succès !";
        
        // Redirection directe vers la fiche du ménage
        header("Location: ../menage/create-update.php?id=" . $menage_id);
        exit;

    } catch (Exception $e) {
        mysqli_rollback($con);
        $_SESSION['error'] = "Échec de l'extraction : " . $e->getMessage();
        header("Location: extraire_inscription.php");
        exit;
    }
}

/* =====================================================
   2. CHARGEMENT DES INSCRIPTIONS EN ATTENTE
===================================================== */
$sql_attente = "
    SELECT 
        i.*, 
        r.nom_complet AS resp_nom, 
        r.telephone1 AS resp_tel1, 
        r.email AS resp_email
    FROM inscriptions i
    INNER JOIN responsables r ON i.responsable_id = r.id
    WHERE i.statut = 'EN_ATTENTE'
    ORDER BY i.created_at DESC
";
$resultats = mysqli_query($con, $sql_attente);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extraction des Nouvelles Inscriptions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm">
                    <h3 class="mb-0 text-primary"><i class="fas fa-user-plus me-2"></i> Extraire & Valider les
                        Inscriptions</h3>
                    <span class="badge bg-warning text-dark fs-6"><?= mysqli_num_rows($resultats) ?> en attente</span>
                </div>
            </div>
        </div>

        <!-- Messages Flash -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Table des fiches à extraire -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code Famille</th>
                                <th>Élève</th>
                                <th>Genre / Naissance</th>
                                <th>Classe demandée</th>
                                <th>Responsable / Tuteur</th>
                                <th>Provenance</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($resultats) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($resultats)): ?>
                            <tr>
                                <td><span
                                        class="badge bg-secondary"><?= htmlspecialchars($row['code_famille']) ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nom'] . ' ' . $row['postnom']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($row['prenom']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark"><?= $row['genre'] ?></span><br>
                                    <small
                                        class="text-muted"><?= $row['date_naissance'] ? date('d/m/Y', strtotime($row['date_naissance'])) : '-' ?></small>
                                </td>
                                <td>
                                    <strong class="text-dark"><?= htmlspecialchars($row['classe']) ?></strong><br>
                                    <small
                                        class="text-muted"><?= htmlspecialchars($row['option_etude'] ?? '') ?></small>
                                </td>
                                <td>
                                    <i
                                        class="fas fa-user-tie text-muted me-1"></i><?= htmlspecialchars($row['resp_nom']) ?><br>
                                    <small class="text-muted"><i
                                            class="fas fa-phone me-1"></i><?= htmlspecialchars($row['resp_tel1']) ?></small>
                                </td>
                                <td><small><?= htmlspecialchars($row['ecole_provenance'] ?? 'N/A') ?></small></td>
                                <td class="text-center">
                                    <form method="POST"
                                        onsubmit="return confirm('Voulez-vous valider cette inscription et ouvrir la fiche du ménage ?');">
                                        <input type="hidden" name="action" value="valider_inscription">
                                        <input type="hidden" name="inscription_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm px-3 fw-bold">
                                            <i class="fas fa-file-export me-1"></i> Extraire
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Aucune nouvelle demande d'inscription en attente d'extraction.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>