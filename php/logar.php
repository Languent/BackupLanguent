<?php

// Incluir o arquivo de conexão
require_once 'conection.php';

// Receber dados do formulário de login e remover espaços em branco
$email = trim($_POST['email']);
$senha = trim($_POST['senha']);

// Consulta SQL para verificar credenciais
$sql = "SELECT id_usuario FROM tb_usuario WHERE email = '$email' AND senha = '$senha'";

// Executar a consulta e verificar por erros
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Erro na consulta: " . mysqli_error($conn));
}

// Verificar se o usuário foi encontrado
if (mysqli_num_rows($result) == 1) {

    // Usuário encontrado, obter apenas o ID
    $usuario = mysqli_fetch_assoc($result);
    $id_usuario = $usuario['id_usuario'];

    // Iniciar sessão
    session_start();

    // Obter ID do usuário para a sessão
    $_SESSION['id_usuario'] = $id_usuario;

    // Verificar se o usuário já possui id_lingua e preferências
    $sql_verificar_dados = "SELECT id_lingua, (SELECT COUNT(*) FROM tb_usuario_preferencia WHERE id_usuario = ?) AS tem_preferencias
                           FROM tb_usuario
                           WHERE id_usuario = ?";
    $stmt_verificar = $conn->prepare($sql_verificar_dados);
    $stmt_verificar->bind_param("ii", $id_usuario, $id_usuario);
    $stmt_verificar->execute();
    $stmt_verificar->bind_result($id_lingua, $tem_preferencias);
    $stmt_verificar->fetch();
    $stmt_verificar->close();

    // Redirecionar com base na existência de id_lingua e preferências
    if ($id_lingua !== null && $tem_preferencias > 0) {
        header('Location: ../html/home.html');
    } else {
        header('Location: ../html/SelecaoIdioma.html');
    }
    exit;

} else {
    // Usuário não encontrado, exibir mensagem de erro
    echo "Email ou Senha inválidos retorne para a página anterior e tente novamente .";
}

mysqli_close($conn);

?>