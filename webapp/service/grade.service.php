<?php
    require_once('../../webapp/database/config.php');

    $sql = "SELECT * FROM grade";
    $rst = mysqli_query($con, $sql);
?>