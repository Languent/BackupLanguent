<?php
session_start();
header('Content-Type: application/json');

require_once 'conection.php';

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Usuário não autenticado."]);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['language']) || !isset($data['interests']) || !is_array($data['interests'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Dados inválidos."]);
    exit;
}

$idioma = $conn->real_escape_string($data['language']);
$interesses = $data['interests'];

$stmt = $conn->prepare("UPDATE tb_usuario SET id_lingua = ? WHERE id_usuario = ?");
$stmt->bind_param("si", $idioma, $id_usuario);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Erro ao atualizar idioma."]);
    exit;
}
$stmt->close();

$conn->query("DELETE FROM tb_usuario_preferencia WHERE id_usuario = $id_usuario");

$insertOk = true;
foreach ($interesses as $interesse) {
    $interesse = $conn->real_escape_string($interesse);
    $query = "INSERT INTO tb_usuario_preferencia (id_usuario, id_preferencia) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $id_usuario, $interesse);
    if (!$stmt->execute()) {
        $insertOk = false;
        break;
    }
    $stmt->close();
}

echo json_encode([
    "success" => $insertOk,
    "message" => $insertOk ? "Configurações atualizadas com sucesso." : "Erro ao atualizar ."
]);

$conn->close();
?>
