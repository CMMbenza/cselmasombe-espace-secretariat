<?php
require_once('../../webapp/database/config.php');

/*
 * On renvoie :
 * - ID classe, description
 * - cycle (label)
 * - nb_eleves = COUNT(eleve.id) pour cette classe
 */
$sql = "
  SELECT
    cl.id,
    cl.description,
    cy.description AS cycle,
    COALESCE(COUNT(e.id), 0) AS nb_eleves
  FROM classe cl
  JOIN cycle  cy ON cy.id = cl.cycle
  LEFT JOIN eleve e ON e.classe = cl.id
  GROUP BY cl.id, cl.description, cy.description
  ORDER BY cy.id ASC, cl.description ASC
";
$rsts = mysqli_query($con, $sql);
