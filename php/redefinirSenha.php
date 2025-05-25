<?php
header("Content-Type: application/json");
include 'conection.php';

$token = $_POST['token'] ?? '';
$novaSenha = $_POST['senha'] ?? '';

if (empty($token) || empty($novaSenha)) {
    echo json_encode(["status" => "error", "msg" => "Token e nova senha são obrigatórios."]);
    exit;
}

$sql = "SELECT id_usuario FROM tb_reset_senha WHERE token = ? AND expira_em > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "msg" => "Token inválido ou expirado."]);
    exit;
}

$dados = $result->fetch_assoc();
$id_usuario = $dados['id_usuario'];

// Consulta a senha atual do usuário
$sqlSenha = "SELECT senha FROM tb_usuario WHERE id_usuario = ?";
$stmtSenha = $conn->prepare($sqlSenha);
$stmtSenha->bind_param("i", $id_usuario);
$stmtSenha->execute();
$resultSenha = $stmtSenha->get_result();
$usuario = $resultSenha->fetch_assoc();

// Gera o hash da nova senha
$senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

// Atualiza a senha apenas se ainda não existir, ou sobrescreve sempre:
if (empty($usuario['senha'])) {
    $msg = "Senha definida com sucesso.";
} else {
    $msg = "Senha atualizada com sucesso.";
}

$sqlUpdate = "UPDATE tb_usuario SET senha = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($sqlUpdate);
$stmt->bind_param("si", $senhaHash, $id_usuario);
$stmt->execute();

// Invalida o token
$sqlDelete = "DELETE FROM tb_reset_senha WHERE token = ?";
$stmt = $conn->prepare($sqlDelete);
$stmt->bind_param("s", $token);
$stmt->execute();

echo json_encode(["status" => "success", "msg" => $msg]);
