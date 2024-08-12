<?php

  // Destruir a sessão
  session_destroy();

  // Redirecionar para a página de login ou página inicial
  header('Location: login.html'); // ou paginaInicial.php
?>