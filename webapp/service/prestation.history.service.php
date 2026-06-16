<?php
    require_once('../../webapp/database/config.php');

    // requette pour lister toutes les prestation du mois encours 
    $sql = "SELECT pres.id, ag.nom AS agent, ag.postnom AS postnom, COUNT(agent) AS nbre_prestation, SUM(HA) AS nbre_d_heure_prester, pres.dateCreated FROM agent ag JOIN prestation pres ON pres.agent = ag.id GROUP BY pres.agent";
    $rstPrestation = mysqli_query($con, $sql);
?>