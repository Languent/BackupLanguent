<?php
require_once 'SalvarCliente.php';
require_once 'conection.php'; // Incluir o arquivo de conexão

$db = $conn; // Utiliza a conexão já estabelecida em conection.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $clienteModel = new ClienteModel($db);

    if ($clienteModel->salvarCliente($nome, $email, $senha)) {
        echo "Dados Salvos com Sucesso !!! Retorne para a página de login para prosseguir. ";
    } else {
        echo "Erro ao salvar os dados. Tente novamente.";
    }
}
?>

