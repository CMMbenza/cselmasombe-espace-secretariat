<?php
    require_once('../../webapp/database/config.php');

    if (isset($_POST['submit'])) {
        $description = $_POST['description'];

        $sql = "INSERT INTO cycle VALUES (NULL, '$description', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Administrateur(trice)')";
        mysqli_query($con, $sql);
    }
?>