<?php
    require_once('../../webapp/database/config.php');
    require_once('../../webapp/service/annee_scolaire.encours.php');
    
    if (isset($_POST['submit'])) {

        $description = $_POST['description'];
        $cycle = $_POST['cycle'];
        $montant = $_POST['montant']; 
        
        $sql = "INSERT INTO scolarite VALUES (NULL, '$description', '$cycle', '$montant', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Administrateur(trice)', '$annee_scolaire')";
        mysqli_query($con, $sql);
    }
?>