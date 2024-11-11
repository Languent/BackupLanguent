<?php
session_start(); 

// Verifica se o usuário está autenticado e possui um id_usuario na sessão
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

// Obtém o id_usuario da sessão
$id_usuario = $_SESSION['id_usuario'];

// Conexão ao banco de dados
$host = 'sql205.infinityfree.com';
$user = 'if0_37044542'; // Substitua pelo usuário correto do banco
$password = 'SenhaLanguent'; 
$dbname = 'if0_37044542_languent';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Obtém o idioma preferido do usuário
$sql_lingua = "SELECT l.lingua FROM tb_usuario u 
               JOIN tb_lingua l ON u.id_lingua = l.id_lingua 
               WHERE u.id_usuario = ?";
$stmt = $conn->prepare($sql_lingua);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($lingua);
$stmt->fetch();
$stmt->close();

// Obtém as preferências do usuário
$sql_preferencias = "SELECT p.preferencia FROM tb_usuario_preferencia up 
                     JOIN tb_preferencias p ON up.id_preferencia = p.id_preferencia 
                     WHERE up.id_usuario = ?";
$stmt = $conn->prepare($sql_preferencias);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$preferencias = [];
while ($row = $result->fetch_assoc()) {
    $preferencias[] = $row['preferencia'];
}
$stmt->close();
$conn->close();

// Retorna as preferências e o idioma em JSON
echo json_encode([
    'idioma' => trim($lingua), 
    'preferencias' => $preferencias
]);
