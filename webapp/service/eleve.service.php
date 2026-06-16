<?php
    require_once('annee_scolaire.encours.php');
    require_once('../../webapp/database/config.php');

    $sql = "SELECT el.id, el.nom, el.postnom, el.prenom, el.genre, el.lieu, el.dateDeNaissance, el.dateCreated, el.classe, el.dateUpdate, el.anneeScolaire, el.createdby, men.id AS id_menage, men.noms AS menage, cla.description AS classe, cy.description AS cycle FROM menage men, classe cla, cycle cy JOIN eleve el WHERE el.menage = men.id AND el.classe = cla.id AND cla.cycle = cy.id AND el.anneeScolaire = '$annee_scolaire' AND el.status = 'actif' ORDER BY `menage` DESC";
    $rst = mysqli_query($con, $sql);
?>