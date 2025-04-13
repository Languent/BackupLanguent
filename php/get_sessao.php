<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir o arquivo de conexão (se necessário para obter dados do usuário)
require_once 'conection.php';

// Verificar se o ID do usuário está na sessão
if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];

    // Consultar o banco de dados para obter o nome do usuário (exemplo)
    $sql = "SELECT nome FROM tb_usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->bind_result($nome);
    $stmt->fetch();
    $stmt->close();

    // Montar o array de dados da sessão para JSON
    $response = ['id_usuario' => $id_usuario, 'nome' => $nome];

    // Definir o cabeçalho para indicar que a resposta é JSON
    header('Content-Type: application/json');

    // Enviar a resposta como JSON
    echo json_encode($response);

} else {
    // Se a sessão não estiver ativa
    header('Content-Type: application/json');
    echo json_encode(['id_usuario' => null, 'nome' => null]);
}

// Não deve haver nenhuma saída HTML ou texto fora das tags PHP
?>