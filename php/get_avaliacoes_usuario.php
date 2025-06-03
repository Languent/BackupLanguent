<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

include 'conection.php';

$stmt = $conn->prepare("SELECT video_id, tipo FROM tb_avaliacoes WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

$result = $stmt->get_result();
$avaliacoes = [];

while ($row = $result->fetch_assoc()) {
    $avaliacoes[$row['video_id']] = $row['tipo'];
}

echo json_encode(['avaliacoes' => $avaliacoes]);
$conn->close();
