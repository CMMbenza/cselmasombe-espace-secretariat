<?php
    require_once('../../webapp/database/config.php');

    if (isset($_POST['submit'])) {

        $description = $_POST['description'];
        $cycle = $_POST['cycle'];
        
        $sql = "INSERT INTO classe VALUES (NULL, '$description', '$cycle', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Administrateur(trice)')";
        mysqli_query($con, $sql);
    }
?>