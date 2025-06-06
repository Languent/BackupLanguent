document.addEventListener('DOMContentLoaded', function () {
            // Verifica se o usuário está logado e se pode acessar a home
            fetch('../php/verificar_sessao.php')
                .then(response => response.json())
                .then(data => {
                    if (data.sessao_ativa) {
                        if (!data.pode_avancar) {
                            // Redireciona apenas uma vez, na primeira pendência encontrada
                            if (data.erros.includes('idioma_nao_definido')) {
                                alert("Você precisa definir um idioma antes de continuar.");
                                window.location.href = '../html/Selecaoidioma.html';
                                return;
                            }
                            if (data.erros.includes('preferencias_nao_definidas')) {
                                alert("Você precisa definir suas preferências antes de continuar.");
                                window.location.href = '../html/SelecioneSuasPreferencias.html';
                                return;
                            }
                        }
                        // Se sessão ativa e pode avançar: não faz nada (usuário segue normalmente)
                    } else {
                        // Se a sessão não estiver ativa, redireciona para o login
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