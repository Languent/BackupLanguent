<?php
require_once 'SalvarCliente.php'; 


$db = new mysqli('sql205.infinityfree.com', 'if0_37044542', 'SenhaLanguent', 'if0_37044542_languent');

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

