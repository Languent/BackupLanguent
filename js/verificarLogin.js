function verificarSessaoERedirecionar() {
    // Envia uma requisição AJAX para uma página PHP que verifica a sessão
    fetch('verificar_sessao.php')
        .then(response => response.json())
        .then(data => {
            if (data.sessao_ativa) {
                // Redireciona para a página home.html
                window.location.href = "home.html";
            } else {
                // Exibe uma mensagem de erro
                alert("Sessão não encontrada. Faça login novamente.");
            }
        })
        .catch(error => {
            console.error('Erro ao verificar sessão:', error);
            alert("Ocorreu um erro ao verificar sua sessão. Por favor, tente novamente.");
        });
}