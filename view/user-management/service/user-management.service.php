<?php
    require_once('../../webapp/database/config.php');

    $sql = "SELECT * FROM users";
    $rst = mysqli_query($con, $sql);
?>