<?php
require_once('../../webapp/database/config.php'); // expose $con (mysqli)
require_once('annee_scolaire.encours.php');

/**
 * Helpers simples
 */
function to_int($v){ return (int)$v; }
function to_float($v){ return (float)$v; }

if (isset($_POST['submit'])) {
    // ====== CREATION ======
    $nom = $_POST['nom'] ?? '';
    $postnom = $_POST['postnom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $lieuDeNaissance = $_POST['lieuDeNaissance'] ?? '';
    $dateDeNaissance = $_POST['dateDeNaissance'] ?? '';
    $classe = to_int($_POST['classe'] ?? 0);
    $menage = to_int($_POST['menage'] ?? 0);
    
    // Récupération des deux frais (scolaire et connexe) depuis le formulaire
    $frais_scolaire = to_float($_POST['montant_a_payer'] ?? $_POST['frais_scolaire'] ?? 0.0);
    $frais_connexe  = to_float($_POST['montantAPayerFC'] ?? 0.0);

    // Eleve : Insertion avec montant_a_payer ET montantAPayerFC
    $sql = "INSERT INTO eleve (nom, postnom, prenom, genre, lieu, dateDeNaissance, classe, menage, dateCreated, dateUpdate, anneeScolaire, createdby, montant_a_payer, montantAPayerFC)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, 'Administrateur(trice)', ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssssissdd", $nom, $postnom, $prenom, $genre, $lieuDeNaissance, $dateDeNaissance, $classe, $menage, $annee_scolaire, $frais_scolaire, $frais_connexe);
    $stmt->execute();
    $stmt->close();

    // Menage : addition des deux frais
    $sql2 = "UPDATE menage 
               SET montantAPayer   = IFNULL(montantAPayer, 0) + ?, 
                   montantAPayerFC = IFNULL(montantAPayerFC, 0) + ? 
             WHERE id = ?";
    $stmt2 = $con->prepare($sql2);
    $stmt2->bind_param("ddi", $frais_scolaire, $frais_connexe, $menage);
    $stmt2->execute();
    $stmt2->close();

} elseif (isset($_POST['update'])) {
    // ====== MISE A JOUR ======
    $eleve_id = to_int($_POST['eleve_id'] ?? 0);
    if ($eleve_id <= 0) { return; }

    $nom = $_POST['nom'] ?? '';
    $postnom = $_POST['postnom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $lieuDeNaissance = $_POST['lieuDeNaissance'] ?? '';
    $dateDeNaissance = $_POST['dateDeNaissance'] ?? '';
    $classe_new = to_int($_POST['classe'] ?? 0);
    $menage_new = to_int($_POST['menage'] ?? 0);
    
    $frais_scolaire_new = to_float($_POST['montant_a_payer'] ?? $_POST['frais_scolaire'] ?? 0.0);
    $frais_connexe_new  = to_float($_POST['montantAPayerFC'] ?? 0.0);

    // Récupérer valeurs anciennes (menage, montant_a_payer, montantAPayerFC)
    $sqlOld = "SELECT menage AS menage_old, 
                      montant_a_payer AS frais_scolaire_old, 
                      montantAPayerFC AS frais_connexe_old 
                 FROM eleve 
                WHERE id = ?";
    $stmtOld = $con->prepare($sqlOld);
    $stmtOld->bind_param("i", $eleve_id);
    $stmtOld->execute();
    $resOld = $stmtOld->get_result();
    $rowOld = $resOld->fetch_assoc();
    $stmtOld->close();

    if (!$rowOld) { return; }
    $menage_old        = to_int($rowOld['menage_old']);
    $frais_scolaire_old = to_float($rowOld['frais_scolaire_old']);
    $frais_connexe_old  = to_float($rowOld['frais_connexe_old']);

    // Update eleve avec les deux montants
    $sqlUp = "UPDATE eleve 
                 SET nom=?, postnom=?, prenom=?, genre=?, lieu=?, dateDeNaissance=?, 
                     classe=?, menage=?, dateUpdate=CURRENT_TIMESTAMP, 
                     anneeScolaire=?, createdby='Administrateur(trice)', 
                     montant_a_payer=?, montantAPayerFC=?
               WHERE id=?";
    $stmtUp = $con->prepare($sqlUp);
    $stmtUp->bind_param("ssssssissddi", $nom, $postnom, $prenom, $genre, $lieuDeNaissance, $dateDeNaissance, $classe_new, $menage_new, $annee_scolaire, $frais_scolaire_new, $frais_connexe_new, $eleve_id);
    $stmtUp->execute();
    $stmtUp->close();

    // Ajustement des montants sur les ménages
    if ($menage_old === $menage_new) {
        // Même ménage : appliquer les deltas
        $delta_scolaire = $frais_scolaire_new - $frais_scolaire_old;
        $delta_connexe  = $frais_connexe_new - $frais_connexe_old;

        if (abs($delta_scolaire) > 0.00001 || abs($delta_connexe) > 0.00001) {
            $sqlDelta = "UPDATE menage 
                           SET montantAPayer   = IFNULL(montantAPayer, 0) + ?, 
                               montantAPayerFC = IFNULL(montantAPayerFC, 0) + ? 
                         WHERE id = ?";
            $stmtDelta = $con->prepare($sqlDelta);
            $stmtDelta->bind_param("ddi", $delta_scolaire, $delta_connexe, $menage_new);
            $stmtDelta->execute();
            $stmtDelta->close();
        }
    } else {
        // Ménage changé : déduire l'ancien ménage et créditer le nouveau ménage
        
        // 1. Déduire de l'ancien ménage
        $sqlM1 = "UPDATE menage 
                     SET montantAPayer   = IFNULL(montantAPayer, 0) - ?, 
                         montantAPayerFC = IFNULL(montantAPayerFC, 0) - ? 
                   WHERE id = ?";
        $stmtM1 = $con->prepare($sqlM1);
        $stmtM1->bind_param("ddi", $frais_scolaire_old, $frais_connexe_old, $menage_old);
        $stmtM1->execute();
        $stmtM1->close();

        // 2. Ajouter au nouveau ménage
        $sqlM2 = "UPDATE menage 
                     SET montantAPayer   = IFNULL(montantAPayer, 0) + ?, 
                         montantAPayerFC = IFNULL(montantAPayerFC, 0) + ? 
                   WHERE id = ?";
        $stmtM2 = $con->prepare($sqlM2);
        $stmtM2->bind_param("ddi", $frais_scolaire_new, $frais_connexe_new, $menage_new);
        $stmtM2->execute();
        $stmtM2->close();
    }
}