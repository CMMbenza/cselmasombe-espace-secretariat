<?php
    require_once('../../webapp/database/config.php');

    $sql = "SELECT agent.id, agent.nom, agent.postnom, agent.prenom, agent.genre, agent.lieu, agent.dateDeNaissance, agent.niveau_d_etude, gr.description AS grade, fonc.description AS fonction, agent.dateCreated FROM agent JOIN grade gr, fonction fonc WHERE agent.grade = gr.id AND agent.fonction = fonc.id";
    $rst = mysqli_query($con, $sql);
?>