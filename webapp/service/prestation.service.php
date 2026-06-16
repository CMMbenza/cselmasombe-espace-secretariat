<?php
    require_once('../../webapp/database/config.php');

    // requette pour lister toutes les prestation today 
    $sql = "SELECT pres.id, ag.nom AS agent, ag.postnom AS postnom, pres.HA AS HA, pres.HD, pres.remarque AS remarque, pres.dateCreated FROM agent ag JOIN prestation pres ON pres.agent = ag.id";
    $rstPrestation = mysqli_query($con, $sql);

    if (isset($_POST['submit'])) {

        $idAgent = $_POST['id']; 
        $time = $_POST['time'];
        $remarque = $_POST['remarque'];

        $sql = "SELECT * FROM prestation WHERE agent = '$idAgent'";
        $row = mysqli_query($con, $sql); 
        $columRows = $row->fetch_assoc();
        
        // $sql = "INSERT INTO prestation VALUES (NULL, '$idAgent', '$time', '$time', '$remarque', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '$annee_scolaire')";
        // mysqli_query($con, $sql);

        if($columRows){
            $sql = "UPDATE prestation SET HD = '$time' WHERE agent = '$idAgent'";
            mysqli_query($con, $sql);
        } else {
            $sql = "INSERT INTO prestation VALUES (NULL, '$idAgent', '$time', '00:00:00', '$remarque', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '$annee_scolaire')";
            mysqli_query($con, $sql); 
        }
    }

        // requette pour lister toutes les prestation today 
        $sql = "SELECT pres.id, ag.nom AS agent, ag.postnom AS postnom, pres.HA AS HA, pres.HD, pres.remarque AS remarque, pres.dateCreated FROM agent ag JOIN prestation pres ON pres.agent = ag.id";
        $rstPrestation = mysqli_query($con, $sql);
?>