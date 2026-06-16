<?php
    require_once('../../webapp/database/config.php');

    $sql = "SELECT * FROM annonce ORDER BY datePubliee ASC";
    $rst = mysqli_query($con, $sql);
?>