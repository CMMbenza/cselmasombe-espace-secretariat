<?php
    require_once('../../webapp/database/config.php');
    require_once('annee_scolaire.encours.php');

    if (isset($_POST['submit'])) {

        $description = $_POST['description'];
        $cycle = $_POST['cycle'];
        
        $sql = "INSERT INTO classe VALUES (NULL, '$description', '$cycle', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Administrateur(trice)', '$annee_scolaire')";
        mysqli_query($con, $sql);
    }
?>