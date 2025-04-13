<?php

// Arquivo: conection.php

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "if0_37044542_languent";
$dbport = 3312;

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname, $dbport);

if (!$conn) {
    die("Falha na conexão com o banco de dados: " . mysqli_connect_error());
}


?>