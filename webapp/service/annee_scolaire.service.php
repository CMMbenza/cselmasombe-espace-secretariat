<?php
require_once('../../webapp/database/config.php');

// Enregistrement
if (isset($_POST['submit'])) {

    $anneeScolaire = mysqli_real_escape_string($con, $_POST['anneeScolaire']);
    $debut = mysqli_real_escape_string($con, $_POST['debut']);
    $fin = mysqli_real_escape_string($con, $_POST['fin']);

    $sql = "INSERT INTO annee_scolaire
            VALUES (NULL, '$anneeScolaire', '$debut', '$fin', 'encours')";

    if (mysqli_query($con, $sql)) {

        // Redirection vers index.php
        header("Location: ../../view/annee_scolaire/");
        exit();

    } else {

        echo "Erreur : " . mysqli_error($con);

    }
}

// Liste des années scolaires
$sql = "SELECT * FROM annee_scolaire
        ORDER BY annee_scolaire DESC";

$rst = mysqli_query($con, $sql);
?>