<?php
    require_once('../../webapp/database/config.php');

    $sql = "SELECT frais.id, cy.id AS cycle_id, cy.description AS cycle, frais.description, frais.montant, frais.dateCreated, frais.dateUpdate, frais.createdby FROM scolarite frais JOIN cycle cy ON frais.cycle = cy.id";
    $rst = mysqli_query($con, $sql);
?>