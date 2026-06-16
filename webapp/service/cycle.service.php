<?php
    require_once('../../webapp/database/config.php');

    $sql = "SELECT * FROM cycle";
    $rst = mysqli_query($con, $sql);
?>