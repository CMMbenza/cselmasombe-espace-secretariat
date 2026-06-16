<?php
  $response = null;

session_start();
$dbuser="cselmasombe_admin";
$dbpass="na57k,ad-$h#";
$host="localhost";
$db="cselmasombe_admin";

$mysqli = new mysqli($host,$dbuser, $dbpass, $db);

//login 
if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = sha1(md5($_POST['password'])); //double encrypt to increase security
  $stmt = $mysqli->prepare("SELECT username, password, id  FROM users WHERE (username =? AND password =?)"); //sql to log in user
  $stmt->bind_param('ss',  $username, $password); //bind fetched parameters
  $stmt->execute(); //execute bind 
  $stmt->bind_result($username, $password, $id); //bind result
  $rs = $stmt->fetch();
  $_SESSION['id'] = $id; 
  if ($rs) {
    //if its sucessfull
	//Visit codeastro.com for more projects 
    header("location:../../view/dashboard/");
  } else {
    $err = "Incorrect Authentication Credentials ";
  }
}
?>