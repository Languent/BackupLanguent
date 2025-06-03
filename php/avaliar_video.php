<?php
session_start();
header('Content-Type: application/json');

// Conexão com o banco
include 'conection.php';

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$videoId = $data['videoId'] ?? null;
$tipo = $data['tipo'] ?? null;
$id_usuario = $_SESSION['id_usuario'];

if (!$videoId || !in_array($tipo, ['like', 'dislike'])) {
    echo json_encode(['erro' => 'Dados inválidos']);
    exit;
}



// Verifica se já existe uma avaliação para o vídeo
$stmt = $conn->prepare("SELECT * FROM tb_avaliacoes WHERE id_usuario = ? AND video_id = ?");
$stmt->bind_param("is", $id_usuario, $videoId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Atualiza a avaliação
    $stmt = $conn->prepare("UPDATE tb_avaliacoes SET tipo = ?, data_avaliacao = CURRENT_TIMESTAMP WHERE id_usuario = ? AND video_id = ?");
    $stmt->bind_param("sis", $tipo, $id_usuario, $videoId);
} else {
    // Insere nova avaliação
    $stmt = $conn->prepare("INSERT INTO tb_avaliacoes (id_usuario, video_id, tipo) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_usuario, $videoId, $tipo);
}

if ($stmt->execute()) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['erro' => 'Erro ao salvar avaliação']);
}

$conn->close();
