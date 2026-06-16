<?php
    require_once('../../webapp/database/config.php');

    $sql = "SELECT * FROM annee_scolaire ORDER BY annee_scolaire.annee_scolaire DESC";
    $rst = mysqli_query($con, $sql);

    if (isset($_POST['submit'])) {

        $anneeScolaire = $_POST['anneeScolaire'];
        $debut = $_POST['debut'];
        $fin = $_POST['fin'];
        
        $sql = "INSERT INTO annee_scolaire VALUES (NULL, '$anneeScolaire', '$debut', '$fin', 'Encours')";
        mysqli_query($con, $sql);
    }
?>