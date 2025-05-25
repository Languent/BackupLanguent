<?php
header("Content-Type: application/json");
include 'conection.php';

$token = $_POST['token'] ?? '';

if (empty($token)) {
    echo json_encode(["status" => "error", "msg" => "Token não informado."]);
    exit;
}

$sql = "SELECT id_usuario FROM tb_reset_senha WHERE token = ? AND expira_em > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "success", "msg" => "Token válido."]);
} else {
    echo json_encode(["status" => "error", "msg" => "Token inválido ou expirado."]);
}
?>
