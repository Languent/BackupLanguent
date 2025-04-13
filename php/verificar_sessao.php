<?php
session_start();

// Verifica se a sessão está ativa e o ID do usuário está definido
if (isset($_SESSION["id_usuario"])) {
    $id_usuario = $_SESSION["id_usuario"];

    // Incluir o arquivo de conexão
    require_once 'conection.php';

    // Verificar se a conexão foi bem-sucedida
    if (!$conn) {
        echo json_encode(['erro' => 'Erro na conexão com o banco de dados']);
        exit;
    }

    // Verificar se o usuário possui id_lingua
    $sql_lingua = "SELECT id_lingua FROM tb_usuario WHERE id_usuario = ?";
    $stmt_lingua = $conn->prepare($sql_lingua);
    $stmt_lingua->bind_param("i", $id_usuario);
    $stmt_lingua->execute();
    $stmt_lingua->bind_result($id_lingua);
    $stmt_lingua->fetch();
    $stmt_lingua->close();

    $tem_lingua = ($id_lingua !== null);

    // Verificar se o usuário possui pelo menos uma id_preferencia
    $sql_preferencia = "SELECT COUNT(*) FROM tb_usuario_preferencia WHERE id_usuario = ?";
    $stmt_preferencia = $conn->prepare($sql_preferencia);
    $stmt_preferencia->bind_param("i", $id_usuario);
    $stmt_preferencia->execute();
    $stmt_preferencia->bind_result($count_preferencia);
    $stmt_preferencia->fetch();
    $stmt_preferencia->close();

    $tem_preferencia = ($count_preferencia > 0);

    // Verificar se o usuário pode avançar para home.html
    if ($tem_lingua && $tem_preferencia) {
        echo json_encode(['pode_avancar' => true]);
    } else {
        $erros = [];
        if (!$tem_lingua) {
            $erros[] = 'idioma_nao_definido';
        }
        if (!$tem_preferencia) {
            $erros[] = 'preferencias_nao_definidas';
        }
        echo json_encode(['pode_avancar' => false, 'erros' => $erros]);
    }

    mysqli_close($conn);

} else {
    // Se a sessão não estiver ativa, retorna um JSON indicando isso
    echo json_encode(['sessao_ativa' => false]);
}
?>
