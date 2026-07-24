<?php
session_start();
require_once('../../../webapp/database/config.php');

if (!isset($con) && isset($conn)) { $con = $conn; }
mysqli_set_charset($con, 'utf8mb4');

/* =====================================================
   RÉINSCRIPTION / RESTAURATION DU MÉNAGE ET DES ÉLÈVES
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restaurer_menage') {

    $code_menage = trim($_POST['code_menage'] ?? '');
    $user_login  = $_SESSION['username'] ?? $_SESSION['user_id'] ?? 'System';

    if (empty($code_menage)) {
        $_SESSION['error'] = "Code ménage invalide ou manquant.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // 1. Récupérer l'année scolaire active
    $res_annee = mysqli_query($con, "SELECT annee_scolaire FROM annee_scolaire WHERE status='encours' ORDER BY id DESC LIMIT 1");
    if (!$res_annee || mysqli_num_rows($res_annee) === 0) {
        $_SESSION['error'] = "Impossible d'effectuer la réinscription : Aucune année scolaire active trouvée.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    $annee_active_row = mysqli_fetch_assoc($res_annee);
    $annee_scolaire_active = $annee_active_row['annee_scolaire'];

    // 2. Récupérer les données du ménage archivé
    $stmt_m = mysqli_prepare($con, "SELECT * FROM archive_menage WHERE code_menage = ? AND restaure = 0");
    mysqli_stmt_bind_param($stmt_m, "s", $code_menage);
    mysqli_stmt_execute($stmt_m);
    $res_m = mysqli_stmt_get_result($stmt_m);

    if (!$res_m || mysqli_num_rows($res_m) === 0) {
        $_SESSION['error'] = "Ménage archivé introuvable ou déjà restauré.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    $menage_archive = mysqli_fetch_assoc($res_m);
    mysqli_stmt_close($stmt_m);

    // 3. Récupérer les élèves archivés de ce ménage
    $stmt_e = mysqli_prepare($con, "SELECT * FROM archive_eleve WHERE menage = ? AND (restaure = 0 OR restaure IS NULL)");
    mysqli_stmt_bind_param($stmt_e, "s", $code_menage);
    mysqli_stmt_execute($stmt_e);
    $res_e = mysqli_stmt_get_result($stmt_e);

    $eleves_archives = [];
    while ($row = mysqli_fetch_assoc($res_e)) {
        $eleves_archives[] = $row;
    }
    mysqli_stmt_close($stmt_e);

    // DÉBUT DE LA TRANSACTION SQL
    mysqli_begin_transaction($con);

    try {
        $today = date('Y-m-d');
        
        // Convertir le code_menage en entier pour occuper le champ `id` de la table `menage`
        $menage_id_num = (int)$menage_archive['code_menage'];

        /* ----------------------------------------------------
           A. INSERTION DANS LA TABLE `menage`
        ---------------------------------------------------- */
        $sql_ins_menage = "INSERT INTO menage (
            id, noms, telephone, numero, avenue, quartier, commune, 
            dateCreated, dateUpdate, createdby, anneeScolaire, 
            montantAPayer, montantAPayerFC, email, STATUS, start_tranche, id_original
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";

        $stmt_ins_m = mysqli_prepare($con, $sql_ins_menage);
        
        $montant_usd = (float)($menage_archive['montantAPayer'] ?? 0);
        $montant_fc  = (float)($menage_archive['montantAPayerFC'] ?? 0);
        $status_m    = !empty($menage_archive['STATUS']) ? $menage_archive['STATUS'] : 'actif';
        $id_orig     = !empty($menage_archive['id_original']) ? (int)$menage_archive['id_original'] : $menage_id_num;

        mysqli_stmt_bind_param(
            $stmt_ins_m,
            "issssssssssddssi",
            $menage_id_num,
            $menage_archive['noms'],
            $menage_archive['telephone'],
            $menage_archive['numero'],
            $menage_archive['avenue'],
            $menage_archive['quartier'],
            $menage_archive['commune'],
            $today,
            $today,
            $user_login,
            $annee_scolaire_active,
            $montant_usd,
            $montant_fc,
            $menage_archive['email'],
            $status_m,
            $id_orig
        );

        if (!mysqli_stmt_execute($stmt_ins_m)) {
            throw new Exception("Erreur lors de l'insertion du ménage : " . mysqli_stmt_error($stmt_ins_m));
        }
        mysqli_stmt_close($stmt_ins_m);

        /* ----------------------------------------------------
           B. INSERTION DANS LA TABLE `eleve`
        ---------------------------------------------------- */
        if (!empty($eleves_archives)) {
            $sql_ins_eleve = "INSERT INTO eleve (
                nom, postnom, prenom, genre, lieu, dateDeNaissance, 
                classe, menage, dateCreated, dateUpdate, anneeScolaire, 
                createdby, montant_a_payer, montantAPayerFC, STATUS
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt_ins_e = mysqli_prepare($con, $sql_ins_eleve);

            foreach ($eleves_archives as $e) {
                $e_classe      = (int)$e['classe'];
                $e_montant_usd = (float)($e['montant_a_payer'] ?? 0);
                $e_montant_fc  = (float)($e['montantAPayerFC'] ?? 0);
                $e_status      = !empty($e['STATUS']) ? $e['STATUS'] : 'actif';

                mysqli_stmt_bind_param(
                    $stmt_ins_e,
                    "ssssssiisssddds",
                    $e['nom'],
                    $e['postnom'],
                    $e['prenom'],
                    $e['genre'],
                    $e['lieu'],
                    $e['dateDeNaissance'],
                    $e_classe,
                    $menage_id_num, // Clé étrangère ménage
                    $today,
                    $today,
                    $annee_scolaire_active,
                    $user_login,
                    $e_montant_usd,
                    $e_montant_fc,
                    $e_status
                );

                if (!mysqli_stmt_execute($stmt_ins_e)) {
                    throw new Exception("Erreur lors de l'insertion de l'élève (" . $e['nom'] . ") : " . mysqli_stmt_error($stmt_ins_e));
                }

                // Marquer l'élève comme restauré dans archive_eleve
                $stmt_up_e = mysqli_prepare($con, "UPDATE archive_eleve SET restaure = 1 WHERE id = ?");
                mysqli_stmt_bind_param($stmt_up_e, "i", $e['id']);
                mysqli_stmt_execute($stmt_up_e);
                mysqli_stmt_close($stmt_up_e);
            }
            mysqli_stmt_close($stmt_ins_e);
        }

        /* ----------------------------------------------------
           C. MARQUER LE MÉNAGE COMME RESTAURÉ
        ---------------------------------------------------- */
        $stmt_up_m = mysqli_prepare($con, "UPDATE archive_menage SET restaure = 1 WHERE id = ?");
        mysqli_stmt_bind_param($stmt_up_m, "i", $menage_archive['id']);
        mysqli_stmt_execute($stmt_up_m);
        mysqli_stmt_close($stmt_up_m);

        // Validation finale des données
        mysqli_commit($con);

        // Message de confirmation en session
        $_SESSION['success'] = "Le ménage <strong>" . htmlspecialchars($menage_archive['noms']) . "</strong> (ID: {$menage_id_num}) et ses enfants ont été réinscrits avec succès !";

        // REDIRECTION EN CAS DE SUCCÈS
        header("Location: ../../menage/create-update.php?id=" . $menage_id_num);
        exit();

    } catch (Exception $ex) {
        // En cas d'erreur, on annule tout et on redirige vers la page précédente
        mysqli_rollback($con);
        $_SESSION['error'] = "Échec de la réinscription : " . $ex->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}