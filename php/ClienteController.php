<?php
require_once 'conection.php';
require_once 'ClienteModel.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome  = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (strlen($senha) < 7) {
        $mensagem = urlencode("A senha deve ter no mínimo 7 caracteres.");
        header("Location: ../html/login.html?mensagem=$mensagem");
        exit;
    }

    $clienteModel = new ClienteModel($conn);
    $resultado = $clienteModel->salvarCliente($nome, $email, $senha);

    if ($resultado === "duplicado") {
        $mensagem = urlencode("Este e-mail já está cadastrado. Faça login ou use outro.");
    } elseif ($resultado === true) {
        $mensagem = urlencode("Cadastro realizado com sucesso!");
    } else {
        $mensagem = urlencode("Erro ao cadastrar usuário. Tente novamente.");
    }

    header("Location: ../html/login.html?mensagem=$mensagem");
    exit;
}
?>
