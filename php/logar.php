<?php

// Conexão com o banco de dados
$dbhost = "localhost";
$dbuser = "id21956605_languent";
$dbpass = "L@nguent123";
$dbname = "id21956605_bdtestelanguent";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$conn) {
  die("Falha na conexão com o banco de dados: " . mysqli_connect_error());
}

// Receber dados do formulário de login
$email = $_POST['email'];
$senha = $_POST['senha'];

// Consulta SQL para verificar credenciais
$sql = "SELECT * FROM tb_usuario WHERE email = '$email' AND senha = '$senha'";
$result = mysqli_query($conn, $sql);

// Verificar se o usuário foi encontrado
if (mysqli_num_rows($result) == 1) {

  // Usuário encontrado
  $usuario = mysqli_fetch_assoc($result);

  // Iniciar sessão
  session_start();

  // Obter dados do usuário
  $_SESSION['id_usuario'] = $usuario['id_usuario'];
  $_SESSION['nome'] = $usuario['nome'];

  if (isset($_SESSION['primeiro_login']) && $_SESSION['primeiro_login'] === true) {
  // Redirecionar para a página para usuários que já fizeram login
  header('Location: home.html');
  exit;
}

// Marcar o login como concluído
$_SESSION['primeiro_login'] = false;

// Redirecionar para a página para novos usuários
header('Location: SelecaoIdioma.html');
exit;

} else {
  // Usuário não encontrado, exibir mensagem de erro
  echo "Email ou Senha inválidos.";
}

mysqli_close($conn);

?>
