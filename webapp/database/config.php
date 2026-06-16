<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cselmasombe_admin');
define('DB_USERNAME','cselmasombe_admin');
define('DB_PASSWORD', 'na57k,ad-$h#');

$con = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if( mysqli_connect_error()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
?>