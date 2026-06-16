<?php
    require_once('../../webapp/database/config.php');

    if (isset($_POST['submit'])) {

        $object = $_POST['object'];
        $message = $_POST['message'];
        
        $sql = "INSERT INTO annonce VALUES (NULL, '$object', '$message', 'Direction Générale', CURRENT_TIMESTAMP)";
        mysqli_query($con, $sql);
    }
?>