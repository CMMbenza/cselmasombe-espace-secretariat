<?php
require_once('../../webapp/database/config.php'); // expose $con (mysqli)
require_once('annee_scolaire.encours.php');

/**
 * Helpers simples
 */
function to_int($v){ return (int)$v; }
function to_float($v){ return (float)$v; }

// ====== SUPPRESSION D'UN ELEVE (Et soustraction des frais de la famille) ======
if (isset($_GET['action']) && $_GET['action'] === 'delete_eleve' && isset($_GET['id'])) {
    $eleve_id = to_int($_GET['id']);
    $id_menage = to_int($_GET['id_menage'] ?? 0);

    if ($eleve_id > 0) {
        $sqlInfo = "SELECT menage, montant_a_payer, montantAPayerFC FROM eleve WHERE id = ?";
        $stmtInfo = $con->prepare($sqlInfo);
        $stmtInfo->bind_param("i", $eleve_id);
        $stmtInfo->execute();
        $resInfo = $stmtInfo->get_result();
        
        if ($rowEleve = $resInfo->fetch_assoc()) {
            $menage_id = to_int($rowEleve['menage']);
            $frais_scolaire = to_float($rowEleve['montant_a_payer']);
            $frais_connexe = to_float($rowEleve['montantAPayerFC']);

            $sqlDeduct = "UPDATE menage 
                             SET montantAPayer   = GREATEST(0, IFNULL(montantAPayer, 0) - ?), 
                                 montantAPayerFC = GREATEST(0, IFNULL(montantAPayerFC, 0) - ?) 
                           WHERE id = ?";
            $stmtDeduct = $con->prepare($sqlDeduct);
            $stmtDeduct->bind_param("ddi", $frais_scolaire, $frais_connexe, $menage_id);
            $stmtDeduct->execute();
            $stmtDeduct->close();

            $sqlDel = "DELETE FROM eleve WHERE id = ?";
            $stmtDel = $con->prepare($sqlDel);
            $stmtDel->bind_param("i", $eleve_id);
            $stmtDel->execute();
            $stmtDel->close();
        }
        $stmtInfo->close();
    }

    if ($id_menage > 0) {
        header("Location: ../../view/eleve/create-update.php?id_menage=" . $id_menage);
    } else {
        header("Location: ./../view/eleve/create-update.php");
    }
    exit;
}

if (isset($_POST['submit'])) {
    // ====== CREATION ======
    $nom = trim($_POST['nom'] ?? '');
    $postnom = trim($_POST['postnom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $genre = $_POST['genre'] ?? '';
    $nationalite = $_POST['nationalite'] ?? 'CONGOLAISE';
    $lieuDeNaissance = $_POST['lieuDeNaissance'] ?? '';
    $dateDeNaissance = $_POST['dateDeNaissance'] ?? '';
    $classe = to_int($_POST['classe'] ?? 0);
    $menage = to_int($_POST['menage'] ?? 0);
    
    $frais_scolaire = to_float($_POST['montant_a_payer'] ?? $_POST['frais_scolaire'] ?? 0.0);
    $frais_connexe  = to_float($_POST['montantAPayerFC'] ?? 0.0);

    // 1. Insertion de l'élève (12 paramètres dans la requête -> "sssssssissdd")
    $sql = "INSERT INTO eleve (matricule, nom, postnom, prenom, genre, lieu, dateDeNaissance, nationalite, classe, menage, dateCreated, dateUpdate, anneeScolaire, createdby, montant_a_payer, montantAPayerFC)
            VALUES ('', ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, 'Administrateur(trice)', ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssssssissdd", $nom, $postnom, $prenom, $genre, $lieuDeNaissance, $dateDeNaissance, $nationalite, $classe, $menage, $annee_scolaire, $frais_scolaire, $frais_connexe);
    $stmt->execute();
    
    // 2. Récupération de l'ID généré
    $insertedId = $stmt->insert_id;
    $stmt->close();

    // 3. Extraction des initiales
    $iNom = !empty($nom) ? strtoupper(mb_substr($nom, 0, 1)) : 'X';
    $iPostnom = !empty($postnom) ? strtoupper(mb_substr($postnom, 0, 1)) : 'X';
    $iPrenom = !empty($prenom) ? strtoupper(mb_substr($prenom, 0, 1)) : 'X';
    $anneeCivil = date('y'); // Ex: 26

    // Format : [ID]-[INITIALES]-[26] ex: 754-NMC-26
    $matricule = $insertedId . '-' . $iNom . $iPostnom . $iPrenom . '-' . $anneeCivil;

    // 4. Sauvegarde du matricule généré
    $sqlMat = "UPDATE eleve SET matricule = ? WHERE id = ?";
    $stmtMat = $con->prepare($sqlMat);
    $stmtMat->bind_param("si", $matricule, $insertedId);
    $stmtMat->execute();
    $stmtMat->close();

    // 5. Addition des frais au ménage
    $sql2 = "UPDATE menage 
               SET montantAPayer   = IFNULL(montantAPayer, 0) + ?, 
                   montantAPayerFC = IFNULL(montantAPayerFC, 0) + ? 
             WHERE id = ?";
    $stmt2 = $con->prepare($sql2);
    $stmt2->bind_param("ddi", $frais_scolaire, $frais_connexe, $menage);
    $stmt2->execute();
    $stmt2->close();

    header("Location: create-update.php?id_menage=" . $menage);
    exit;

} elseif (isset($_POST['update'])) {
    // ====== MISE A JOUR ======
    $eleve_id = to_int($_POST['eleve_id'] ?? 0);
    if ($eleve_id <= 0) { return; }

    $nom = trim($_POST['nom'] ?? '');
    $postnom = trim($_POST['postnom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $genre = $_POST['genre'] ?? '';
    $nationalite = $_POST['nationalite'] ?? 'CONGOLAISE';
    $lieuDeNaissance = $_POST['lieuDeNaissance'] ?? '';
    $dateDeNaissance = $_POST['dateDeNaissance'] ?? '';
    $classe_new = to_int($_POST['classe'] ?? 0);
    $menage_new = to_int($_POST['menage'] ?? 0);
    
    $frais_scolaire_new = to_float($_POST['montant_a_payer'] ?? $_POST['frais_scolaire'] ?? 0.0);
    $frais_connexe_new  = to_float($_POST['montantAPayerFC'] ?? 0.0);

    // Récupération de l'ancien état et du matricule actuel
    $sqlOld = "SELECT menage AS menage_old, 
                      montant_a_payer AS frais_scolaire_old, 
                      montantAPayerFC AS frais_connexe_old,
                      matricule AS matricule_old 
                 FROM eleve 
                WHERE id = ?";
    $stmtOld = $con->prepare($sqlOld);
    $stmtOld->bind_param("i", $eleve_id);
    $stmtOld->execute();
    $resOld = $stmtOld->get_result();
    $rowOld = $resOld->fetch_assoc();
    $stmtOld->close();

    if (!$rowOld) { return; }
    $menage_old         = to_int($rowOld['menage_old']);
    $frais_scolaire_old = to_float($rowOld['frais_scolaire_old']);
    $frais_connexe_old  = to_float($rowOld['frais_connexe_old']);
    $matricule_old      = trim($rowOld['matricule_old'] ?? '');

    // Génération automatique du matricule s'il n'existe pas encore
    $matricule = $matricule_old;
    if (empty($matricule_old)) {
        $iNom     = !empty($nom) ? strtoupper(mb_substr($nom, 0, 1)) : 'X';
        $iPostnom = !empty($postnom) ? strtoupper(mb_substr($postnom, 0, 1)) : 'X';
        $iPrenom  = !empty($prenom) ? strtoupper(mb_substr($prenom, 0, 1)) : 'X';
        $anneeCivil = date('y');

        $matricule = $eleve_id . '-' . $iNom . $iPostnom . $iPrenom . '-' . $anneeCivil;
    }

    // 14 marqueurs (?) dans la requête SQL
    $sqlUp = "UPDATE eleve 
                 SET matricule=?, nom=?, postnom=?, prenom=?, genre=?, lieu=?, dateDeNaissance=?, nationalite=?,
                     classe=?, menage=?, dateUpdate=CURRENT_TIMESTAMP, 
                     anneeScolaire=?, createdby='Administrateur(trice)', 
                     montant_a_payer=?, montantAPayerFC=?
               WHERE id=?";
    $stmtUp = $con->prepare($sqlUp);
    
    // "sssssssssissdd" (14 caractères pour 14 variables)
    $stmtUp->bind_param("sssssssssissdd", 
        $matricule,          // s (1)
        $nom,                // s (2)
        $postnom,            // s (3)
        $prenom,             // s (4)
        $genre,              // s (5)
        $lieuDeNaissance,    // s (6)
        $dateDeNaissance,    // s (7)
        $nationalite,        // s (8)
        $classe_new,         // i (9)
        $menage_new,         // i (10)
        $annee_scolaire,     // s (11)
        $frais_scolaire_new, // d (12)
        $frais_connexe_new,  // d (13)
        $eleve_id            // i (14)
    );
    
    $stmtUp->execute();
    $stmtUp->close();

    // Ajustement des montants de la famille/ménage
    if ($menage_old === $menage_new) {
        $delta_scolaire = $frais_scolaire_new - $frais_scolaire_old;
        $delta_connexe  = $frais_connexe_new - $frais_connexe_old;

        if (abs($delta_scolaire) > 0.00001 || abs($delta_connexe) > 0.00001) {
            $sqlDelta = "UPDATE menage 
                           SET montantAPayer   = GREATEST(0, IFNULL(montantAPayer, 0) + ?), 
                               montantAPayerFC = GREATEST(0, IFNULL(montantAPayerFC, 0) + ?) 
                         WHERE id = ?";
            $stmtDelta = $con->prepare($sqlDelta);
            $stmtDelta->bind_param("ddi", $delta_scolaire, $delta_connexe, $menage_new);
            $stmtDelta->execute();
            $stmtDelta->close();
        }
    } else {
        $sqlM1 = "UPDATE menage 
                     SET montantAPayer   = GREATEST(0, IFNULL(montantAPayer, 0) - ?), 
                         montantAPayerFC = GREATEST(0, IFNULL(montantAPayerFC, 0) - ?) 
                   WHERE id = ?";
        $stmtM1 = $con->prepare($sqlM1);
        $stmtM1->bind_param("ddi", $frais_scolaire_old, $frais_connexe_old, $menage_old);
        $stmtM1->execute();
        $stmtM1->close();

        $sqlM2 = "UPDATE menage 
                     SET montantAPayer   = IFNULL(montantAPayer, 0) + ?, 
                         montantAPayerFC = IFNULL(montantAPayerFC, 0) + ? 
                   WHERE id = ?";
        $stmtM2 = $con->prepare($sqlM2);
        $stmtM2->bind_param("ddi", $frais_scolaire_new, $frais_connexe_new, $menage_new);
        $stmtM2->execute();
        $stmtM2->close();
    }

    header("Location: create-update.php?id_menage=" . $menage_new);
    exit;
}