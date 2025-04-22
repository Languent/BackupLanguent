<?php
class ClienteModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function salvarCliente($nome, $email, $senha) {
        // Verificar se o e-mail já está cadastrado
        $checkSql = "SELECT id_usuario FROM tb_usuario WHERE email = ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            return "duplicado"; // E-mail já existe
        }

        $checkStmt->close();

        // Gerar hash da senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Inserir novo usuário
        $sql = "INSERT INTO tb_usuario (nome, email, senha) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $nome, $email, $senhaHash);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
?>

