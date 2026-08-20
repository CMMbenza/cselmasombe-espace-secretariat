<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../../webapp/database/config.php');
mysqli_set_charset($con, 'utf8mb4');
session_start();

if (!$con) {
    die("Erreur connexion base de données");
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Aucune année scolaire sélectionnée.";
    header("Location:../annee_scolaire/");
    exit;
}

$annee_id = (int) $_GET['id'];

/* =====================================================
   INTERCEPTION POUR AFFICHAGE CONFIRMATION DÉTAILLÉE
===================================================== */
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'true') {
    $stmt_check = mysqli_prepare($con, "SELECT annee_scolaire FROM annee_scolaire WHERE id = ? AND status = 'encours'");
    mysqli_stmt_bind_param($stmt_check, "i", $annee_id);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($res_check) === 0) {
        $_SESSION['error'] = "Cette année scolaire n'existe pas ou est déjà clôturée.";
        header("Location:../annee_scolaire/");
        exit;
    }
    
    $annee_info = mysqli_fetch_assoc($res_check);
    $nom_annee = htmlspecialchars($annee_info['annee_scolaire']);
    ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Confirmation d'archivage et clôture</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-danger text-white p-3">
                        <h4 class="card-title mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Confirmation
                            requise : Clôture définitive</h4>
                    </div>
                    <div class="card-body p-4">
                        <p class="lead">Vous êtes sur le point de clôturer définitivement l'année scolaire
                            <strong><?= $nom_annee ?></strong>.
                        </p>

                        <div
                            class="alert alert-warning border-start border-warning border-3 bg-white text-dark shadow-sm">
                            <h5 class="text-warning"><i class="fas fa-box-archive me-2"></i> 1. Données qui seront
                                sauvegardées en archive :</h5>
                            <ul class="mb-0">
                                <li><strong>Ménages & Parents :</strong> Sauvegarde complète des fiches d'informations.
                                </li>
                                <li><strong>Élèves :</strong> Conservation de l'ensemble des profils et statuts
                                    d'inscription.</li>
                                <li><strong>Comptabilité :</strong> Archivage de l'historique complet de tous les
                                    paiements (Réguliers et Divers).</li>
                                <li><strong>Dépenses :</strong> Enregistrement du grand livre des sorties de fonds et
                                    dépenses globales.</li>
                                <li><strong>Classes :</strong> Capture de la répartition statistique des effectifs
                                    totaux par classe.</li>
                                <li><strong>Rapport financier :</strong> Fixation du bilan annuel (Recettes perçues,
                                    attendues et restes).</li>
                            </ul>
                        </div>

                        <div
                            class="alert alert-danger border-start border-danger border-3 bg-white text-dark shadow-sm mt-3">
                            <h5 class="text-danger"><i class="fas fa-trash-can me-2"></i> 2. Remise à zéro du système
                                actif :</h5>
                            <p class="mb-0 text-muted">Après la copie conforme dans les archives, les tables d'origine
                                <em>(Élèves, Ménages, Paiements et Dépenses)</em> seront entièrement vidées pour libérer
                                le système et le préparer proprement à accueillir les futures fiches de la nouvelle
                                rentrée.
                            </p>
                        </div>

                        <div
                            class="alert alert-secondary border-start border-secondary border-3 bg-white text-dark shadow-sm mt-3">
                            <h5><i class="fas fa-toggle-off me-2"></i> 3. Statut de la session :</h5>
                            <p class="mb-0 text-muted">Le statut de l'année <strong><?= $nom_annee ?></strong> passera
                                de <code>'encours'</code> à <code>'fin'</code>. Cette action est irréversible.</p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="../annee_scolaire/" class="btn btn-secondary px-4 py-2"><i
                                    class="fas fa-arrow-left me-2"></i> Annuler l'opération</a>
                            <a href="cloturer_annee.php?id=<?= $annee_id ?>&confirm=true"
                                class="btn btn-danger px-4 py-2 text-white fw-bold"><i
                                    class="fas fa-check-circle me-2"></i> Lancer le grand archivage</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<?php
    exit;
}

/* =====================================================
   EXECUTION SUITE DU SCRIPT SI CONFIRMATION OK
===================================================== */
mysqli_begin_transaction($con);

try {

    /* =====================================================
       1. RECUPERATION ANNEE SCOLAIRE
    ===================================================== */
    $sql = "SELECT * FROM annee_scolaire WHERE id = ? AND status = 'encours' LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) { throw new Exception("Erreur préparation année : " . mysqli_error($con)); }
    mysqli_stmt_bind_param($stmt, "i", $annee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result || mysqli_num_rows($result) == 0) {
        throw new Exception("Cette année scolaire n'existe pas ou est déjà clôturée.");
    }

    $annee = mysqli_fetch_assoc($result);
    $annee_label = $annee['annee_scolaire'];

    /* =====================================================
       2. CREATION SESSION ARCHIVE
    ===================================================== */
    $commentaire = "Clôture automatique année scolaire " . $annee_label;
    $sql = "INSERT INTO archive_session (annee_scolaire_id, annee_scolaire, commentaire, statut) VALUES (?, ?, ?, 'termine')";
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) { throw new Exception("Erreur session archive : " . mysqli_error($con)); }
    mysqli_stmt_bind_param($stmt, "iss", $annee_id, $annee_label, $commentaire);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception("Erreur insertion session archive : " . mysqli_stmt_error($stmt)); }
    
    $archive_id = mysqli_insert_id($con);

    /* =====================================================
       3. COLLECTE DES STATISTIQUES GLOBALES
    ===================================================== */
    $total_menages = 0;
    $total_eleves = 0;
    $encaissement_scolaire = 0.0;
    $encaissement_connexe = 0.0;
    $total_depenses = 0.0;

    // Effectifs Ménages
    $r = mysqli_query($con, "SELECT COUNT(*) total FROM menage");
    if ($r) { $total_menages = (int)mysqli_fetch_assoc($r)['total']; }
    
    // Effectifs Élèves
    $r = mysqli_query($con, "SELECT COUNT(*) total FROM eleve");
    if ($r) { $total_eleves = (int)mysqli_fetch_assoc($r)['total']; }

    // Comptabilité Scolaire Principale (Paiements perçus)
    $r = mysqli_query($con, "SELECT SUM(montantPayer) as p FROM paiement");
    if ($r) {
        $res = mysqli_fetch_assoc($r);
        $encaissement_scolaire = (float)$res['p'];
    }

    // Comptabilité Connexe (Paiements divers perçus)
    $r = mysqli_query($con, "SELECT SUM(montantPayer) as p FROM paiement_divers");
    if ($r) {
        $res = mysqli_fetch_assoc($r);
        $encaissement_connexe = (float)$res['p'];
    }

    // Sorties (Dépenses)
    $r = mysqli_query($con, "SELECT SUM(montant) as total FROM depenses");
    if ($r) { $total_depenses = (float)mysqli_fetch_assoc($r)['total']; }

    // Calcul du bénéfice net (Entrées totales - Sorties)
    $benefice = ($encaissement_scolaire + $encaissement_connexe) - $total_depenses;

    /* =====================================================
       4. INSERTION DANS ARCHIVE_STATISTIQUES (Champs corrigés)
    ===================================================== */
    $sql_stat = "
        INSERT INTO archive_statistiques (
            archive_id, annee_archive, total_menages, total_eleves, 
            encaissement_scolaire, encaissement_connexe, depenses, benefice
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt_stat = mysqli_prepare($con, $sql_stat);
    if (!$stmt_stat) { throw new Exception("Erreur préparation statistiques : " . mysqli_error($con)); }
    
    mysqli_stmt_bind_param($stmt_stat, "isiidddd", 
        $archive_id, $annee_label, $total_menages, $total_eleves, 
        $encaissement_scolaire, $encaissement_connexe, $total_depenses, $benefice
    );
    
    if (!mysqli_stmt_execute($stmt_stat)) { 
        throw new Exception("Erreur écriture statistiques globales : " . mysqli_stmt_error($stmt_stat)); 
    }

    /* =====================================================
       5. ARCHIVAGE PAR CLASSE (archive_classe)
    ===================================================== */
    $sql_classe = "
        INSERT INTO archive_classe (archive_id, annee_archive, description, total_eleves)
        SELECT ?, ?, classe, COUNT(*) 
        FROM eleve 
        GROUP BY classe
    ";
    $stmt_classe = mysqli_prepare($con, $sql_classe);
    if (!$stmt_classe) { throw new Exception("Erreur préparation archive classe : " . mysqli_error($con)); }
    mysqli_stmt_bind_param($stmt_classe, "is", $archive_id, $annee_label);
    if (!mysqli_stmt_execute($stmt_classe)) { throw new Exception("Erreur archivage des classes : " . mysqli_stmt_error($stmt_classe)); }

    /* =====================================================
       6. ARCHIVAGE DES BASES OPERATIONNELLES (Ménages & Elèves)
    ===================================================== */
    // Ménages (Ajout du champ code_menage qui récupère l'id de la table menage)
    $sql = "INSERT INTO archive_menage (archive_id, annee_archive, code_menage, noms, telephone, numero, avenue, quartier, commune, dateCreated, dateUpdate, createdby, anneeScolaire, montantAPayer, email, STATUS, password)
            SELECT ?, ?, id, noms, telephone, numero, avenue, quartier, commune, dateCreated, dateUpdate, createdby, anneeScolaire, montantAPayer, email, STATUS, password FROM menage";
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) { throw new Exception("Erreur préparation archive ménages : " . mysqli_error($con)); }
    mysqli_stmt_bind_param($stmt, "is", $archive_id, $annee_label);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception("Erreur archivage ménages : " . mysqli_stmt_error($stmt)); }

    // Élèves
    $sql = "INSERT INTO archive_eleve (archive_id, annee_archive, nom, postnom, prenom, genre, lieu, dateDeNaissance, classe, menage, dateCreated, dateUpdate, anneeScolaire, createdby, montant_a_payer, STATUS)
            SELECT ?, ?, nom, postnom, prenom, genre, lieu, dateDeNaissance, classe, menage, dateCreated, dateUpdate, anneeScolaire, createdby, montant_a_payer, STATUS FROM eleve";
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) { throw new Exception("Erreur préparation archive élèves : " . mysqli_error($con)); }
    mysqli_stmt_bind_param($stmt, "is", $archive_id, $annee_label);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception("Erreur archivage élèves : " . mysqli_stmt_error($stmt)); }

    /* =====================================================
       7. ARCHIVAGE DES PAIEMENTS & DES DEPENSES
    ===================================================== */
    // Paiements principaux
    mysqli_query($con, "INSERT INTO archive_paiement (archive_id, annee_archive, menage, montantAPayer, montantPayer, resteAPayer, observation, dateCreated, anneeScolaire) SELECT $archive_id, '$annee_label', menage, montantAPayer, montantPayer, resteAPayer, observation, dateCreated, anneeScolaire FROM paiement");
    
    // Paiements divers
    mysqli_query($con, "INSERT INTO archive_paiement_divers (archive_id, annee_archive, menage, type_frais, montantAPayer, montantPayer, resteAPayer, observation, createdby, anneeScolaire, dateCreated) SELECT $archive_id, '$annee_label', menage, type_frais, montantAPayer, montantPayer, resteAPayer, observation, createdby, anneeScolaire, dateCreated FROM paiement_divers");

    // Dépenses
    $sql_dep = "
        INSERT INTO archive_depenses (
            archive_id, annee_archive, beneficiaire, reference, numero_reference, 
            description, montant, dateCreaty, dateUpdate, createdby, anneeScolaire
        )
        SELECT 
            ?, ?, beneficiaire, reference, numero_reference, 
            description, montant, dateCreaty, dateUpdate, createdby, anneeScolaire
        FROM depenses
    ";
    
    $stmt_dep = mysqli_prepare($con, $sql_dep);
    if ($stmt_dep) {
        mysqli_stmt_bind_param($stmt_dep, "is", $archive_id, $annee_label);
        if (!mysqli_stmt_execute($stmt_dep)) { 
            throw new Exception("Erreur archivage des dépenses : " . mysqli_stmt_error($stmt_dep)); 
        }
    } else {
        throw new Exception("Erreur préparation requête archive_depenses : " . mysqli_error($con));
    }

    /* =====================================================
       8. NETTOYAGE ABSOLU (Remise à zéro pour la nouvelle rentrée)
    ===================================================== */
    mysqli_query($con, "DELETE FROM depenses");
    mysqli_query($con, "DELETE FROM paiement_eleve_divers");
    mysqli_query($con, "DELETE FROM paiement_divers");
    mysqli_query($con, "DELETE FROM paiement");
    mysqli_query($con, "DELETE FROM eleve");
    mysqli_query($con, "DELETE FROM menage");

    /* =====================================================
       9. VALIDATION & PASSAGE DU STATUT A 'FIN'
    ===================================================== */
    $stmt = mysqli_prepare($con, "UPDATE archive_session SET total_menages = ?, total_eleves = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "iii", $total_menages, $total_eleves, $archive_id);
    mysqli_stmt_execute($stmt);

    $stmt = mysqli_prepare($con, "UPDATE annee_scolaire SET status = 'fin' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $annee_id);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception("Erreur clôture année scolaire."); }

    mysqli_commit($con);

    $_SESSION['success'] = "L'année scolaire " . $annee_label . " a été fermée, toutes les statistiques, classes, dépenses et comptabilités ont été scellées en archive.";
    header("Location:../annee_scolaire/");
    exit;

} catch (Exception $e) {
    mysqli_rollback($con);

    echo "<div style='padding: 20px; background: #ffdee2; color: #8a1f2c; border: 1px solid #de9da4; font-family: Arial, sans-serif; border-radius: 5px; margin: 30px auto; max-width: 700px;'>";
    echo "<h2 style='margin-top:0;'>❌ Erreur de structure ou de clé lors du grand archivage :</h2>";
    echo "<p><strong>Détails :</strong> <code>" . htmlspecialchars($e->getMessage()) . "</code></p>";
    echo "<hr style='border:0; border-top:1px solid #de9da4;'>";
    echo "<p style='font-size:12px; color:#666;'>Annulation effectuée. Aucune donnée n'a été perdue.</p>";
    echo "</div>";
    exit;
}
?>