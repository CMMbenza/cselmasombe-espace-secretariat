<?php
    require_once('../../webapp/database/config.php');

    if (isset($_POST['submit'])) {

        // $idMenage = $_POST['id']; 
        $montantAPayer = $_POST['montantAPayer'];
        $payer = $_POST['payer']; 
        $rPayer = $_POST['rPayer']; 
        
        
        $sqlBalance = "INSERT INTO balance VALUES (NULL, 'paiement frais scolaire', '$payer', '0', '0', CURRENT_TIMESTAMP, '$annee_scolaire')";
        mysqli_query($con, $sqlBalance);
        
    }
?>