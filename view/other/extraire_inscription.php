<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../../layouts/constants/head.php'); 
require_once('../../layouts/navbar/navbar.php');

require_once('../../webapp/database/config.php'); // $con (mysqli)
require_once('../../webapp/service/annee_scolaire.encours.php'); // $annee_scolaire

mysqli_set_charset($con, 'utf8mb4');
// session_start();

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
        // A. Récupération des données d'inscription et du responsable
        $sql_select = "
            SELECT 
                i.*, 
                r.nom_complet AS resp_nom, 
                r.nom_pere AS resp_nom_pere,
                r.nom_mere AS resp_nom_mere,
                r.profession AS resp_profession,
                r.telephone1 AS resp_tel1, 
                r.telephone2 AS resp_tel2, 
                r.email AS resp_email, 
                r.adresse AS resp_adresse,
                r.province AS resp_province
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

        // B. Recherche du Ménage existant ou Création
        $menage_id = null;
        $sql_check_menage = "SELECT id FROM menage WHERE telephone = ? OR (email = ? AND email != '') LIMIT 1";
        $stmt_check = mysqli_prepare($con, $sql_check_menage);
        mysqli_stmt_bind_param($stmt_check, "ss", $row['resp_tel1'], $row['resp_email']);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);

        // Tronquer l'adresse pour respecter la taille en BDD si VARCHAR(20)
        $avenue_securisee = mb_substr($row['resp_adresse'] ?? '', 0, 20);

        if ($m = mysqli_fetch_assoc($res_check)) {
            $menage_id = (int)$m['id'];

            // Mise à jour si données manquantes
            $sql_upd_m = "
                UPDATE menage SET 
                    nom_du_pere = IF(nom_du_pere = '' OR nom_du_pere IS NULL, ?, nom_du_pere),
                    nom_de_la_mere = IF(nom_de_la_mere = '' OR nom_de_la_mere IS NULL, ?, nom_de_la_mere),
                    profesion = IF(profesion = '' OR profesion IS NULL, ?, profesion),
                    province = IF(province = '' OR province IS NULL, ?, province),
                    dateUpdate = CURRENT_TIMESTAMP
                WHERE id = ?
            ";
            $stmt_upd_m = mysqli_prepare($con, $sql_upd_m);
            mysqli_stmt_bind_param($stmt_upd_m, "ssssi", 
                $row['resp_nom_pere'], 
                $row['resp_nom_mere'], 
                $row['resp_profession'],
                $row['resp_province'], 
                $menage_id
            );
            mysqli_stmt_execute($stmt_upd_m);

        } else {
            // Création d'un nouveau ménage
            $sql_ins_menage = "
                INSERT INTO menage (
                    noms, nom_du_pere, nom_de_la_mere, profesion, telephone, numero, avenue, 
                    quartier, commune, province, email, dateCreated, dateUpdate, 
                    createdby, anneeScolaire, montantAPayer, montantAPayerFC, STATUS, start_tranche, password
                ) VALUES (?, ?, ?, ?, ?, '', ?, '', '', ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Administrateur(trice)', ?, 0.00, 0.00, 'actif', 1, '')
            ";
            
            $stmt_ins_m = mysqli_prepare($con, $sql_ins_menage);
            if (!$stmt_ins_m) { throw new Exception("Erreur création ménage : " . mysqli_error($con)); }
            
            $nom_p = $row['resp_nom_pere'] ?? '';
            $nom_m = $row['resp_nom_mere'] ?? '';
            $prof  = $row['resp_profession'] ?? '';
            $prov  = $row['resp_province'] ?? '';
            $email = $row['resp_email'] ?? '';
            $annee = !empty($row['annee_scolaire']) ? $row['annee_scolaire'] : ($annee_scolaire ?? date('Y'));

            mysqli_stmt_bind_param($stmt_ins_m, "sssssssss", 
                $row['resp_nom'], 
                $nom_p,
                $nom_m,
                $prof,
                $row['resp_tel1'], 
                $avenue_securisee, 
                $prov,
                $email,
                $annee
            );
            
            if (!mysqli_stmt_execute($stmt_ins_m)) {
                throw new Exception("Erreur insertion ménage : " . mysqli_stmt_error($stmt_ins_m));
            }
            $menage_id = mysqli_insert_id($con);
        }

        // C. Insertion de l'élève
        $matricule_temp = 'MAT' . date('Ym') . str_pad((string)$inscription_id, 4, '0', STR_PAD_LEFT);
        $nationalite = !empty($row['nationalite']) ? strtoupper($row['nationalite']) : 'CONGOLAISE';
        $classe_id = (int)($row['classe'] ?? 0);
        $annee = !empty($row['annee_scolaire']) ? $row['annee_scolaire'] : ($annee_scolaire ?? date('Y'));

        $sql_ins_eleve = "
            INSERT INTO eleve (
                matricule, nom, postnom, prenom, genre, lieu, dateDeNaissance, 
                classe, menage, nationalite, dateCreated, dateUpdate, anneeScolaire, 
                createdby, montant_a_payer, montantAPayerFC, STATUS
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, 'Administrateur(trice)', 0.00, 0.00, 'actif')
        ";
        
        $stmt_eleve = mysqli_prepare($con, $sql_ins_eleve);
        if (!$stmt_eleve) { throw new Exception("Erreur préparation élève : " . mysqli_error($con)); }

        mysqli_stmt_bind_param($stmt_eleve, "sssssssiiss", 
            $matricule_temp,
            $row['nom'], 
            $row['postnom'], 
            $row['prenom'], 
            $row['genre'], 
            $row['lieu_naissance'], 
            $row['date_naissance'], 
            $classe_id, 
            $menage_id, 
            $nationalite,
            $annee
        );

        if (!mysqli_stmt_execute($stmt_eleve)) {
            throw new Exception("Erreur création fiche élève : " . mysqli_stmt_error($stmt_eleve));
        }

        // D. Mise à jour du statut
        $sql_upd = "UPDATE inscriptions SET statut = 'VALIDE' WHERE id = ?";
        $stmt_upd = mysqli_prepare($con, $sql_upd);
        mysqli_stmt_bind_param($stmt_upd, "i", $inscription_id);
        mysqli_stmt_execute($stmt_upd);

        mysqli_commit($con);
        
        $_SESSION['success'] = "L'élève " . htmlspecialchars($row['nom'] . " " . $row['prenom']) . " a été extrait avec succès !";
        
        header("Location: ../menage/create-update.php?id=" . $menage_id);
        exit();

    } catch (Exception $e) {
        mysqli_rollback($con);
        $_SESSION['error'] = "Échec de l'extraction : " . $e->getMessage();
        header("Location: extraire_inscription.php");
        exit();
    }
}

/* =====================================================
   2. CHARGEMENT DES INSCRIPTIONS EN ATTENTE
===================================================== */
$sql_attente = "
    SELECT 
        i.*, 
        r.nom_complet AS resp_nom, 
        r.nom_pere AS resp_nom_pere,
        r.nom_mere AS resp_nom_mere,
        r.profession AS resp_profession,
        r.telephone1 AS resp_tel1, 
        r.email AS resp_email,
        r.province AS resp_province
    FROM inscriptions i
    INNER JOIN responsables r ON i.responsable_id = r.id
    WHERE i.statut = 'EN_ATTENTE'
    ORDER BY i.created_at DESC
";

$resultats = mysqli_query($con, $sql_attente);
$nb_attente = ($resultats instanceof mysqli_result) ? mysqli_num_rows($resultats) : 0;
?>

<div class="main-panel-copy">
    <div class="content-wrapper">

        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm">
                    <div>
                        <h3 class="mb-0 text-primary"><i class="fas fa-user-plus me-2"></i> Extraire & Valider les
                            Inscriptions</h3>
                        <br><span class="badge bg-warning text-dark fs-6"><?= $nb_attente ?> en attente</span>
                    </div>
                    <div><a href="list_nouvelle_famille_inscrite.php" class="btn btn-success">
                            <i class="mdi mdi-file-export me-1"></i> Nouveaux inscrits
                        </a></div>

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
        <div class="table-responsive mt-4">
            <table class="table table-responsive align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Code Famille</th>
                        <th>Élève</th>
                        <th>Genre / Nat. / Naissance</th>
                        <th>Classe demandée</th>
                        <th>Responsable (Père / Mère)</th>
                        <th>Province</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($nb_attente > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($resultats)): ?>
                    <tr>
                        <td>
                            <span class="badge bg-secondary text-dark"><?= htmlspecialchars($row['code_famille'] ?? '') ?></span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars(($row['nom'] ?? '') . ' ' . ($row['postnom'] ?? '')) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($row['prenom'] ?? '') ?></small>
                        </td>
                        <td>
                            <span class="badge bg-info text-white"><?= htmlspecialchars($row['genre'] ?? '') ?></span>
                            <small
                                class="badge bg-primary text-white"><?= htmlspecialchars($row['nationalite'] ?? 'CONGOLAISE') ?></small><br>
                            <small
                                class="text-muted"><?= !empty($row['date_naissance']) ? date('d/m/Y', strtotime($row['date_naissance'])) : '-' ?></small>
                        </td>
                        <td>
                            <strong class="text-dark"><?= htmlspecialchars($row['classe'] ?? '') ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($row['option_etude'] ?? '') ?></small>
                        </td>
                        <td>
                            <i
                                class="fas fa-user-tie text-muted me-1"></i><strong><?= htmlspecialchars($row['resp_nom'] ?? '') ?></strong><br>
                            <small class="text-muted">P: <?= htmlspecialchars($row['resp_nom_pere'] ?? 'N/A') ?>
                                | M: <?= htmlspecialchars($row['resp_nom_mere'] ?? 'N/A') ?></small><br>
                            <small class="text-muted"><i
                                    class="fas fa-phone me-1"></i><?= htmlspecialchars($row['resp_tel1'] ?? '') ?></small>
                        </td>
                        <td>
                            <span class="badge bg-primary text-white border border-primary">
                                <?= htmlspecialchars($row['resp_province'] ?? 'N/A') ?>
                            </span>
                        </td>
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



<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
<?php require_once('../../layouts/constants/footer.php'); ?>
</body>

</html>