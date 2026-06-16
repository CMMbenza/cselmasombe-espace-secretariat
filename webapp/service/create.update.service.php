<?php
    require_once('../../webapp/database/config.php');

    if (isset($_POST['submit'])) {

        $idMenage = $_POST['id']; 
        $montantAPayer = $_POST['montantAPayer'];
        $payer = $_POST['payer']; 
        $rPayer = $_POST['rPayer']; 
        // $obs = $_POST['obs']; 

        if ($montantAPayer == 0) {
            $sql = "INSERT INTO paiement VALUES (NULL, '$idMenage', '$montantAPayer', '$payer', '$rPayer', 'Paiement solder', CURRENT_TIMESTAMP, '$annee_scolaire')";
            mysqli_query($con, $sql);
        }else{
            $sql = "INSERT INTO paiement VALUES (NULL, '$idMenage', '$montantAPayer', '$payer', '$rPayer', 'Paiement encours', CURRENT_TIMESTAMP, '$annee_scolaire')";
            mysqli_query($con, $sql);
        }                    
            $sqlBalance = "INSERT INTO balance VALUES (NULL, 'Paiment frais scolaire', '$payer', '0', '0', CURRENT_TIMESTAMP, '$annee_scolaire')";
            mysqli_query($con, $sqlBalance);
               
    }
?>