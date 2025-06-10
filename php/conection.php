<?php

// Arquivo: conection.php

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "if0_37044542_languent";
$dbport = 3307;

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname, $dbport);

// CORREÇÃO: Garante que a resposta seja JSON mesmo em caso de falha de conexão.
if (!$conn) {
    http_response_code(500); // Internal Server Error
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Falha na conexão com o banco de dados.',
        'details' => mysqli_connect_error()
    ]);
    exit; // Interrompe a execução de forma controlada.
}

// Chave da API centralizada
$apiKey = "AIzaSyDjSYNA3VDKSPUikwbuJqt4-kUwhd-7vG8";

?>