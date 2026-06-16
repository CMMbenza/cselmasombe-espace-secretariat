<?php
// session_start();

// define('BASE_PATH', 'https://n-gestionnaire.com/');

//Database credentails
// Connexion à la base de données

$servername = 'localhost';
$username = 'cselmasombe_admin'; 
$password = 'na57k,ad-$h#';  
$dbname = 'cselmasombe_admin';
 
// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
