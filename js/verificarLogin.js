function verificarSessaoERedirecionar() {
    // Envia uma requisição AJAX para a página PHP que verifica a sessão e os dados do usuário
    fetch('../php/verificar_sessao.php')
        .then(response => response.json())
        .then(data => {
            if (data.sessao_ativa) {
                if (data.pode_avancar) {
                    // Redireciona para a página home.html se a sessão estiver ativa e o usuário tiver idioma e preferências
                    window.location.href = "../html/home.html";
                } else {
                    // Exibe mensagens informando o que falta ao usuário
                    let mensagem = "Para continuar para a página inicial, você precisa:";
                    if (data.erros && data.erros.length > 0) {
                        if (data.erros.includes('idioma_nao_definido')) {
                            mensagem += "\n- Selecionar um idioma.";
                            // Redirecionar para a página de seleção de idioma (opcional)
                            window.location.href = "../html/SelecaoIdioma.html";
                            return; // Interrompe a execução para evitar o alerta genérico
                        }
                        if (data.erros.includes('preferencias_nao_definidas')) {
                            mensagem += "\n- Selecionar suas preferências.";
                            // Redirecionar para a página de seleção de preferências (opcional)
                            window.location.href = "../html/SelecioneSuasPreferencias.html";
                            return; // Interrompe a execução para evitar o alerta genérico
                        }
                        alert(mensagem);
                    } else {
                        alert("Sua conta precisa ser configurada completamente para acessar a página inicial.");
                    }
                }
            } else {
                // Redireciona para a página de login se a sessão não estiver ativa
                alert("Sessão não encontrada. Realize o login para prosseguir.");
                window.location.href = "../html/login.html";
            }
        })
        .catch(error => {
            console.error('Erro ao verificar sessão:', error);
            alert("Ocorreu um erro ao verificar sua sessão. Por favor, tente novamente.");
        });
}

