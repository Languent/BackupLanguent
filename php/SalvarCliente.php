<?php
class ClienteModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function salvarCliente($nome, $email, $senha) {
        $sql = "INSERT INTO tb_usuario (nome, email, senha) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $nome, $email, $senha);
        return $stmt->execute();
    }
}
?>
