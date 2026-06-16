<?php
    require_once('../../webapp/database/config.php');
    require_once('annee_scolaire.encours.php');

    if (isset($_POST['submit'])) {

        $idMenage = $_POST['id']; 
        $montantAPayer = $_POST['montantAPayer'];
        $payer = $_POST['payer']; 
        $rPayer = $_POST['rPayer']; 
        
        if ($rPayer == 0) {
        $sql = "INSERT INTO paiement VALUES (NULL, '$idMenage', '$montantAPayer', '$payer', '$rPayer', 'paiement soldé', CURRENT_TIMESTAMP, '$annee_scolaire')";
        mysqli_query($con, $sql);

        header("Location: ../../view/paiement-divers-frais/"); 
        exit();
        } else {
        $sql = "INSERT INTO paiement VALUES (NULL, '$idMenage', '$montantAPayer', '$payer', '$rPayer', 'paiement encours', CURRENT_TIMESTAMP, '$annee_scolaire')";
        mysqli_query($con, $sql);
        
        header("Location: ../../view/paiement-divers-frais/"); 
        exit();
        }
        
    }
?>