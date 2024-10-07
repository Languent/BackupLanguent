<?php
session_start();

// Verifica se a sessão está ativa
if (isset($_SESSION["id_usuario"])) {
    // Se a sessão estiver ativa, retorna um JSON indicando isso
    echo json_encode(['sessao_ativa' => true]);
} else {
    // Se a sessão não estiver ativa, retorna um JSON indicando isso
    echo json_encode(['sessao_ativa' => false]);
}
?>