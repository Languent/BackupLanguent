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
$foto_url = $payload['picture'] ?? null;

$avatar = null;
$avatar_tipo = null;

// Se tiver foto, baixa e converte em binário
if (!empty($foto_url)) {
    $img_data = @file_get_contents($foto_url);
    if ($img_data !== false) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $avatar_tipo = $finfo->buffer($img_data);
        $avatar = $img_data;
    }
}

// Verifica se o usuário já existe
$stmt = $conn->prepare("SELECT id_usuario, avatar FROM tb_usuario WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    // Insere novo usuário com imagem
    $stmt = $conn->prepare("INSERT INTO tb_usuario (nome, email, avatar, avatar_tipo) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $email, $avatar, $avatar_tipo);
    $stmt->execute();
    $id_usuario = $stmt->insert_id;
    $stmt->close();
} else {
    $stmt->bind_result($id_usuario, $avatar_existente);
    $stmt->fetch();
    $stmt->close();

    // Se ainda não tem avatar e conseguiu baixar, atualiza
    if (empty($avatar_existente) && $avatar !== null) {
        $stmt = $conn->prepare("UPDATE tb_usuario SET avatar = ?, avatar_tipo = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssi", $avatar, $avatar_tipo, $id_usuario);
        $stmt->execute();
        $stmt->close();
    }
}

session_start();
$_SESSION['id_usuario'] = $id_usuario;

// Verifica se o usuário já tem idioma e preferências
$stmt = $conn->prepare("SELECT id_lingua FROM tb_usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($id_lingua);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_usuario_preferencia WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($num_preferencias);
$stmt->fetch();
$stmt->close();

$redirect = (!empty($id_lingua) && $num_preferencias > 0) ? 'home.html' : 'boasVindas.html';

echo json_encode(['success' => true, 'redirect' => $redirect]);
?>
