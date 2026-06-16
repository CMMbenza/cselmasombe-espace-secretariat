<?php
    require_once('../../webapp/database/config.php');

    $sql = "SELECT * FROM fonction";
    $rst = mysqli_query($con, $sql);
    $rstFonction = mysqli_query($con, $sql);
?>