<?php
require_once('../../webapp/database/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    $username = trim($_POST['username']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    // Vérification simple des champs
    if (!empty($username) && !empty($role) && !empty($password)) {

        // Hash du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Préparation de la requête
        $sql = "INSERT INTO users (username, password, role, dateCreation, dateModification)
                VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";

        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $username, $hashedPassword, $role);
            if (mysqli_stmt_execute($stmt)) {
                echo "✅ Utilisateur ajouté avec succès.";
            } else {
                echo "❌ Erreur d'insertion : " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "❌ Erreur de préparation : " . mysqli_error($con);
        }

    } else {
        echo "❌ Tous les champs sont requis.";
    }
}
?>
