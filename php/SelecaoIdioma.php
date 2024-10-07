<?php
// Inicia uma sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o ID do usuário está armazenado na sessão
if (!isset($_SESSION['id_usuario'])) {
    die("Erro: Usuário não identificado.");
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_SESSION['id_usuario'];
    
    // Captura o valor do idioma e sanitiza
    $id_lingua = filter_var($_POST['idioma'], FILTER_SANITIZE_NUMBER_INT);

    // Conexão com o banco de dados
    $dbhost = "sql205.infinityfree.com";
    $dbuser = "if0_37044542";
    $dbpass = "SenhaLanguent";
    $dbname = "if0_37044542_languent";

    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    // Verifica a conexão
    if (!$conn) {
        die("Erro: Conexão com o banco de dados falhou! " . mysqli_connect_error());
    }

    // Consulta de atualização
    $sql = "UPDATE tb_usuario SET id_lingua = ? WHERE id_usuario = ?";

    // Prepara a declaração para evitar injeção de SQL
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt === false) {
        die("Erro: Falha ao preparar a declaração.");
    }

    // Vincula parâmetros à declaração
    mysqli_stmt_bind_param($stmt, "ii", $id_lingua, $id_usuario);

    // Executa a declaração
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header('Location: Selecione suas Preferencias.html ');
        exit;
    } else {
        echo "Erro: Falha ao atualizar o idioma.";
    }
}
?>
