<?php
session_start();
$mysqli = new mysqli('localhost', 'cselmasombe_admin', 'na57k,ad-$h#', 'cselmasombe_admin');

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashedPasswordFromDB);
        $stmt->fetch();

        // 🔍 Affichage temporaire pour debug
        echo "Mot de passe tapé : $password <br>";
        echo "Hash de la DB : $hashedPasswordFromDB <br>";

        if (password_verify($password, $hashedPasswordFromDB)) {
            $_SESSION['id'] = $id;
            header("Location: ../../view/dashboard/");
            exit();
        } else {
            echo "❌ Mot de passe incorrect.";
        }
    } else {
        echo "❌ Utilisateur non trouvé.";
    }

    $stmt->close();
}
?>
