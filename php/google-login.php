<?php
header('Content-Type: application/json');
require_once 'conection.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Erro na conexão com o banco: ' . $conn->connect_error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['credential'] ?? '';

if (!$token) {
    echo json_encode(['success' => false, 'error' => 'Token ausente.']);
    exit;
}

$clientId = '683123440343-21kie9quc4ge1cgbg854oi6ogbpl91ft.apps.googleusercontent.com';
$payload = json_decode(file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=" . $token), true);

if (!$payload || $payload['aud'] !== $clientId) {
    echo json_encode(['success' => false, 'error' => 'Token inválido.']);
    exit;
}

$email = $payload['email'];
$nome = $payload['name'];

// Verifica se o usuário já existe
$stmt = $conn->prepare("SELECT id_usuario FROM tb_usuario WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO tb_usuario (nome, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $nome, $email);
    $stmt->execute();
    $id_usuario = $stmt->insert_id;
} else {
    $stmt->bind_result($id_usuario);
    $stmt->fetch();
}

session_start();
$_SESSION['id_usuario'] = $id_usuario;

// Verifica se o usuário possui id_lingua e pelo menos uma preferência
$stmt = $conn->prepare("
    SELECT id_lingua FROM tb_usuario WHERE id_usuario = ?
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($id_lingua);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("
    SELECT COUNT(*) FROM tb_usuario_preferencia WHERE id_usuario = ?
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($num_preferencias);
$stmt->fetch();
$stmt->close();

// Define para onde redirecionar
if (!empty($id_lingua) && $num_preferencias > 0) {
    $redirect = 'home.html';
} else {
    $redirect = 'SelecaoIdioma.html';
}

echo json_encode(['success' => true, 'redirect' => $redirect]);
?>
