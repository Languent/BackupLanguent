<?php
header("Content-Type: application/json"); // força retorno JSON

include 'conection.php';

$email = $_POST['email'] ?? '';

if (empty($email)) {
    echo json_encode(["status" => "error", "msg" => "Informe um e-mail válido."]);
    exit;
}

// Verifica se o e-mail existe no banco
$sql = "SELECT id_usuario FROM tb_usuario WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "msg" => "E-mail não encontrado."]);
    exit;
}

$row = $result->fetch_assoc();
$id_usuario = $row['id_usuario'];

// Gera o token
$token = bin2hex(random_bytes(32));
$expira_em = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Salva o token na tabela de reset
$sql = "INSERT INTO tb_reset_senha (id_usuario, token, expira_em) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $id_usuario, $token, $expira_em);
$stmt->execute();

// Envia para o Google Apps Script
$scriptUrl = "https://script.google.com/macros/s/AKfycbxaLMyxxLBVknyxnrb_-MoJzwRA_9wbssxfqQfFZxzFY3Y0q8l-FZz_dyVzVM5GBuWe5Q/exec";

$payload = json_encode([
    "email" => $email,
    "token" => $token
]);

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => $payload,
        'timeout' => 10
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($scriptUrl, false, $context);

// Verifica a resposta do script externo
if ($response === false) {
    echo json_encode(["status" => "error", "msg" => "Falha ao enviar e-mail de recuperação."]);
} else {
    $data = json_decode($response, true);

    if (isset($data["status"]) && $data["status"] === "success") {
        echo json_encode(["status" => "success", "msg" => "E-mail de recuperação enviado com sucesso. Verifique sua caixa de entrada."]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Não foi possível enviar o e-mail de recuperação."]);
    }
}
?>
