<?php
    require_once('../../webapp/database/config.php');
    require_once('annee_scolaire.encours.php');
    $mois = date('m');
    $annee = date('Y');

    $sql = "SELECT * FROM balance WHERE anneeScolaire = '$annee_scolaire' AND MONTH(dateUpdate) = $mois AND YEAR(dateUpdate) = $annee ";
    $rst = mysqli_query($con, $sql);

    $sql = "SELECT SUM(entre) AS entre, SUM(sorti) AS sorti, SUM(entre) - SUM(sorti) AS reste FROM balance WHERE anneeScolaire = '$annee_scolaire' AND MONTH(dateUpdate) = $mois AND YEAR(dateUpdate) = $annee ";
    $rstSommeDuMoisEncours = mysqli_query($con, $sql);
    $rowSommeDuMoisEncours = $rstSommeDuMoisEncours->fetch_assoc();

    $sql = "SELECT SUM(entre) - SUM(sorti) AS balance FROM balance WHERE anneeScolaire = '$annee_scolaire'";
    $rstSomme = mysqli_query($con, $sql);
    $rowSomme = $rstSomme->fetch_assoc();

    // $sqlBalance = "INSERT INTO balance VALUES (NULL, 'paiement frais scolaire', '$payer', '0', '0', CURRENT_TIMESTAMP, '$annee_scolaire')";
    // mysqli_query($con, $sqlBalance);
?>