document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('finishButton');

    // Evento de clique no botão
    if (button) {
        button.addEventListener('click', () => {
            window.location.href = '../html/home.html';
        });
    }

    // Verifica se o usuário está logado e pode acessar a página
    fetch('../php/verificar_sessao.php')
        .then(response => response.json())
        .then(data => {
            if (data.sessao_ativa) {
                if (!data.pode_avancar) {
                    if (data.erros.includes('idioma_nao_definido')) {
                        alert("Você precisa definir um idioma antes de continuar.");
                        window.location.href = '../html/SelecaoIdioma.html';
                        return;
                    }
                    if (data.erros.includes('preferencias_nao_definidas')) {
                        alert("Você precisa definir suas preferências antes de continuar.");
                        window.location.href = '../html/SelecioneSuasPreferencias.html';
                        return;
                    }
                }
            } else {
                alert("Você precisa estar logado para acessar esta página.");
                window.location.href = '../html/login.html';
            }
        })
        .catch(error => {
            console.error('Erro ao verificar sessão:', error);
            alert('Ocorreu um erro ao verificar sua sessão. Tente novamente.');
            window.location.href = '../html/login.html';
        });
});
