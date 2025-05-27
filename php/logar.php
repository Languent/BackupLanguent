<?php

// Incluir o arquivo de conexão
require_once 'conection.php';

// Iniciar sessão
session_start();

// Receber dados do formulário de login e remover espaços em branco (somente para email)
$email = trim($_POST['email']);
$senha = trim($_POST['senha']); 

// Consulta segura para obter o hash da senha e o ID do usuário
$sql_login = "SELECT id_usuario, senha FROM tb_usuario WHERE email = ?";
$stmt_login = $conn->prepare($sql_login);
$stmt_login->bind_param("s", $email);
$stmt_login->execute();
$result = $stmt_login->get_result();

// Verificar se encontrou o usuário
if ($result->num_rows === 1) {
    $usuario = $result->fetch_assoc();

    // Verificar a senha usando password_verify
    if (password_verify($senha, $usuario['senha'])) {
        $id_usuario = $usuario['id_usuario'];
        $_SESSION['id_usuario'] = $id_usuario;

        // Verificar se o usuário já possui id_lingua e preferências
        $sql_verificar_dados = "SELECT id_lingua, 
                                       (SELECT COUNT(*) FROM tb_usuario_preferencia WHERE id_usuario = ?) AS tem_preferencias
                                FROM tb_usuario
                                WHERE id_usuario = ?";
        $stmt_verificar = $conn->prepare($sql_verificar_dados);
        $stmt_verificar->bind_param("ii", $id_usuario, $id_usuario);
        $stmt_verificar->execute();
        $stmt_verificar->bind_result($id_lingua, $tem_preferencias);
        $stmt_verificar->fetch();
        $stmt_verificar->close();

        // Redirecionar com base nos dados do usuário
        if (is_null($id_lingua) || $tem_preferencias == 0) {
            header('Location: ../html/boasVindas.html');
        } else {
            header('Location: ../html/home.html');
        }
        exit;
    } else {
        // Senha incorreta
        header("Location: ../html/login.html?mensagem=" . urlencode("Senha incorreta. Tente novamente."));
        exit;

    }
} else {
    // Usuário não encontrado
    header("Location: ../html/login.html?mensagem=" . urlencode("Email não encontrado. Verifique e tente novamente."));
    exit;

}

// Fechar o statement de login
$stmt_login->close();
$conn->close();

?>
