<?php
session_start();
header('Content-Type: application/json');

include 'conection.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$videoId = $data['videoId'] ?? null;
$tipo = $data['tipo'] ?? null;
$id_usuario = $_SESSION['id_usuario'];

if (!$videoId || !in_array($tipo, ['like', 'dislike'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados inválidos']);
    exit;
}

// Verifica se já existe uma avaliação para este vídeo por este usuário
$stmt_check = $conn->prepare("SELECT tipo FROM tb_avaliacoes WHERE id_usuario = ? AND video_id = ?");
$stmt_check->bind_param("is", $id_usuario, $videoId);
$stmt_check->execute();
$result = $stmt_check->get_result();
$existing_rating = $result->fetch_assoc();
$stmt_check->close();

$stmt = null;
if ($existing_rating) {
    // Se a avaliação existente é a mesma que o clique, o usuário está desmarcando.
    if ($existing_rating['tipo'] === $tipo) {
        $stmt = $conn->prepare("DELETE FROM tb_avaliacoes WHERE id_usuario = ? AND video_id = ?");
        $stmt->bind_param("is", $id_usuario, $videoId);
    } else {
        // Se a avaliação é diferente, o usuário está trocando o voto.
        $stmt = $conn->prepare("UPDATE tb_avaliacoes SET tipo = ?, data_avaliacao = CURRENT_TIMESTAMP WHERE id_usuario = ? AND video_id = ?");
        $stmt->bind_param("sis", $tipo, $id_usuario, $videoId);
    }
} else {
    // Se não existe avaliação, insere uma nova.
    $stmt = $conn->prepare("INSERT INTO tb_avaliacoes (id_usuario, video_id, tipo) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_usuario, $videoId, $tipo);
}

if ($stmt && $stmt->execute()) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar avaliação no banco de dados.']);
}

$conn->close();
?>