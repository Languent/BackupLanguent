<?php 
session_start();
echo json_encode([
    'id_usuario' => $_SESSION['id_usuario'],
    'id_lingua' => $_SESSION['id_lingua'],
    'nome'=> $_SESSION['nome']
]);
?>
