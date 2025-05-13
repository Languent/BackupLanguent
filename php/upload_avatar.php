<?php
session_start();
header('Content-Type: application/json');

require_once 'conection.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Erro no upload']);
    exit;
}

$imageData = file_get_contents($_FILES['avatar']['tmp_name']);
$imageType = mime_content_type($_FILES['avatar']['tmp_name']);

// Apenas imagens permitidas
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($imageType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Tipo de imagem inválido']);
    exit;
}

// Atualiza ou insere imagem na tabela tb_usuario
$stmt = $conn->prepare("UPDATE tb_usuario SET avatar = ?, avatar_tipo = ? WHERE id_usuario = ?");
$stmt->bind_param("bsi", $null, $imageType, $id_usuario);
$null = null;
$stmt->send_long_data(0, $imageData);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erro ao salvar no banco']);
}
?>
