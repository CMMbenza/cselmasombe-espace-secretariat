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
    $frais_scolaire = to_float($_POST['frais_scolaire'] ?? 0.0);

    // Eleve
    $sql = "INSERT INTO eleve (nom, postnom, prenom, genre, lieu, dateDeNaissance, classe, menage, dateCreated, dateUpdate, anneeScolaire, createdby, montant_a_payer)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, 'Administrateur(trice)', ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssssissd", $nom, $postnom, $prenom, $genre, $lieuDeNaissance, $dateDeNaissance, $classe, $menage, $annee_scolaire, $frais_scolaire);
    $stmt->execute();
    $stmt->close();

    // Menage : addition du frais
    $sql2 = "UPDATE menage SET montantAPayer = IFNULL(montantAPayer,0) + ? WHERE id = ?";
    $stmt2 = $con->prepare($sql2);
    $stmt2->bind_param("di", $frais_scolaire, $menage);
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
    $frais_scolaire_new = to_float($_POST['frais_scolaire'] ?? 0.0);

    // Récupérer valeurs anciennes (menage, montant_a_payer)
    $sqlOld = "SELECT menage AS menage_old, montant_a_payer AS frais_old FROM eleve WHERE id = ?";
    $stmtOld = $con->prepare($sqlOld);
    $stmtOld->bind_param("i", $eleve_id);
    $stmtOld->execute();
    $resOld = $stmtOld->get_result();
    $rowOld = $resOld->fetch_assoc();
    $stmtOld->close();

    if (!$rowOld) { return; }
    $menage_old = to_int($rowOld['menage_old']);
    $frais_old = to_float($rowOld['frais_old']);

    // Update eleve
    $sqlUp = "UPDATE eleve 
                 SET nom=?, postnom=?, prenom=?, genre=?, lieu=?, dateDeNaissance=?, 
                     classe=?, menage=?, dateUpdate=CURRENT_TIMESTAMP, 
                     anneeScolaire=?, createdby='Administrateur(trice)', 
                     montant_a_payer=?
               WHERE id=?";
    $stmtUp = $con->prepare($sqlUp);
    $stmtUp->bind_param("ssssssissdi", $nom, $postnom, $prenom, $genre, $lieuDeNaissance, $dateDeNaissance, $classe_new, $menage_new, $annee_scolaire, $frais_scolaire_new, $eleve_id);
    $stmtUp->execute();
    $stmtUp->close();

    // Ajustement des montants sur les ménages
    if ($menage_old === $menage_new) {
        // Même ménage : appliquer le delta
        $delta = $frais_scolaire_new - $frais_old;
        if (abs($delta) > 0.00001) {
            $sqlDelta = "UPDATE menage SET montantAPayer = IFNULL(montantAPayer,0) + ? WHERE id = ?";
            $stmtDelta = $con->prepare($sqlDelta);
            $stmtDelta->bind_param("di", $delta, $menage_new);
            $stmtDelta->execute();
            $stmtDelta->close();
        }
    } else {
        // Ménage changé : retirer l'ancien frais de l'ancien ménage, ajouter le nouveau au nouveau ménage
        $sqlM1 = "UPDATE menage SET montantAPayer = IFNULL(montantAPayer,0) - ? WHERE id = ?";
        $stmtM1 = $con->prepare($sqlM1);
        $stmtM1->bind_param("di", $frais_old, $menage_old);
        $stmtM1->execute();
        $stmtM1->close();

        $sqlM2 = "UPDATE menage SET montantAPayer = IFNULL(montantAPayer,0) + ? WHERE id = ?";
        $stmtM2 = $con->prepare($sqlM2);
        $stmtM2->bind_param("di", $frais_scolaire_new, $menage_new);
        $stmtM2->execute();
        $stmtM2->close();
    }
}
