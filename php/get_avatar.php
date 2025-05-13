<?php
session_start();
require_once 'conection.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    header('Content-Type: image/jpeg');
    readfile('../img/avatarVazio.jpg'); // fallback padrão
    exit;
}

$stmt = $conn->prepare("SELECT avatar, avatar_tipo FROM tb_usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($avatar, $avatar_tipo);
    $stmt->fetch();

    if ($avatar) {
        header("Content-Type: $avatar_tipo");
        echo $avatar;
        exit;
    }
}

// fallback
header('Content-Type: image/jpeg');
readfile('../img/avatarVazio.jpg');
?>
