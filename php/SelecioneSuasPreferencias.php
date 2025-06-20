<?php
session_start();

// Verifica se o ID do usuário está armazenado na sessão
if (!isset($_SESSION['id_usuario'])) {
    die("Erro: Usuário não identificado.");
}

// Incluir o arquivo de conexão
require_once 'conection.php';

// Verifica a conexão (opcional, mas recomendado)
if (!$conn) {
    die("Erro: Conexão com o banco de dados falhou! " . mysqli_connect_error());
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica se existem categorias selecionadas
    if (isset($_POST['selectedCategories']) && !empty($_POST['selectedCategories'])) {
        // Pega o ID do usuário da sessão
        $id_usuario = $_SESSION['id_usuario'];

        // Limpar as preferências existentes para o usuário
        $sql_delete = "DELETE FROM tb_usuario_preferencia WHERE id_usuario = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $id_usuario);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);

        // Quebra a string das categorias selecionadas em um array
        $selectedCategories = explode(',', $_POST['selectedCategories']);

        foreach ($selectedCategories as $category) {
            // Insere a nova preferência
            $sql_insert = "INSERT INTO tb_usuario_preferencia (id_usuario, id_preferencia) VALUES (?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "ii", $id_usuario, $category);
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
        }

        // Redirecionar para a página de configuração inicial concluida
        header('Location: ../html/configuracaoConcluida.html');
        exit;
    } else {
        echo "Nenhuma categoria selecionada.";
    }
}
?>