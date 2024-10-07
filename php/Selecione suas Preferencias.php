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
    
    // Recupera as categorias selecionadas e sanitiza
$selectedCategories = filter_var($_POST['selectedCategories'], FILTER_SANITIZE_STRING);

if (empty($selectedCategories)) {
    die("Erro: Nenhuma categoria selecionada.");
}

// Converte a string de categorias em um array de inteiros e valida os valores
$categoriesArray = explode(',', $selectedCategories);
$categoriesArray = array_map('intval', $categoriesArray);

if (!array_reduce($categoriesArray, function($carry, $item) {
    return $carry && is_numeric($item);
}, true)) {
    die("Erro: Os valores das categorias não são válidos.");
}

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

    // Para evitar duplicidade, removemos as preferências anteriores do usuário
    $deleteSql = "DELETE FROM tb_usuario_preferencias WHERE id_usuario = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteSql);
    mysqli_stmt_bind_param($deleteStmt, "i", $id_usuario);
    mysqli_stmt_execute($deleteStmt);
    mysqli_stmt_close($deleteStmt);

  // Insere as novas preferências
  $insertSql = "INSERT INTO tb_usuario_preferencias (id_usuario, id_preferencia) VALUES (?, ?)";

  $insertStmt = mysqli_prepare($conn, $insertSql);

  if ($insertStmt === false) {
    die("Erro: Falha ao preparar a declaração.");
    }

  // Insere cada categoria selecionada (agora usando o id_preferencia diretamente)
  foreach ($categoriesArray as $id_preferencia) {
    mysqli_stmt_bind_param($insertStmt, "ii", $id_usuario, $id_preferencia);
    mysqli_stmt_execute($insertStmt);
 }

 mysqli_stmt_close($insertStmt);
 mysqli_close($conn);

 // Redireciona para a próxima etapa
 header('Location: home.html');
 exit;
}
?>
