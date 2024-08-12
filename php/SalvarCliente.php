<?php

// Detalhes da conexão com o banco de dados
$dbhost = "localhost";
$dbuser = "id21956605_languent";
$dbpass = "L@nguent123";
$dbname = "id21956605_bdtestelanguent";

// Criar conexão com o banco de dados
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Verificar se a conexão foi bem-sucedida
if ($conn->connect_error) {
  die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Receber os dados do formulário
$nome = filter_input(INPUT_POST, "nome", FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
$senha = filter_input(INPUT_POST, "senha", FILTER_SANITIZE_STRING);


// Preparar consulta SQL para inserir os dados
$sql = "INSERT INTO tb_usuario (nome, email, senha) VALUES (?, ?, ?)";

// Preparar declaração
$stmt = $conn->prepare($sql);

// Vincular os parâmetros à declaração
$stmt->bind_param("sss", $nome, $email, $senha);

// Executar consulta e verificar erros
if ($stmt->execute()) {
  echo "Dados salvos com sucesso!";
} else {
  echo "Erro ao salvar dados: " . $stmt->error;
}

// Fechar declaração e conexão com o banco de dados
$stmt->close();
$conn->close();

?>
