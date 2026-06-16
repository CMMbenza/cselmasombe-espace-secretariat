<?php
    require_once('../../webapp/database/config.php');

    // requette pour lister toutes les prestation today 
    $sql = "SELECT pres.id, ag.nom AS agent, ag.postnom AS postnom, ag.prenom AS prenom, ag.salaire AS salaire, SUM(pres.HA) AS HA, COUNT(agent) AS nbre_prestation, COUNT(agent) * SUM(salaire) AS versement FROM agent ag JOIN prestation pres ON pres.agent = ag.id GROUP BY pres.agent";
    $rstPrestation = mysqli_query($con, $sql);
?>