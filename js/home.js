document.addEventListener('DOMContentLoaded', function () {
    
    // --- Lógica da Sidebar ---
    const openBtn = document.getElementById('open_btn');
    if (openBtn) {
        openBtn.addEventListener('click', function () {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('open-sidebar');
            }
        });
    }

    // --- Lógica do Tema (Dark Mode) ---
    const checkbox = document.getElementById("checkbox");
    if (checkbox) {
        checkbox.addEventListener("change", () => {
            document.body.classList.toggle("dark-mode");
        });
    }

    // --- Lógica de Verificação de Sessão ---
    // Esta função verifica se o usuário pode estar na página ou se deve ser redirecionado.
    fetch('../php/verificar_sessao.php')
        .then(response => response.json())
        .then(data => {
            if (!data.sessao_ativa) {
                // Se a sessão não estiver ativa, redireciona para o login
                alert("Você precisa estar logado para acessar esta página.");
                window.location.href = '../html/login.html';
                return;
            }

            if (!data.pode_avancar) {
                // Se o usuário não tiver configurado a conta, redireciona para a pendência.
                if (data.erros && data.erros.includes('idioma_nao_definido')) {
                    alert("Você precisa definir um idioma antes de continuar.");
                    window.location.href = '../html/SelecaoIdioma.html';
                } else if (data.erros && data.erros.includes('preferencias_nao_definidas')) {
                    alert("Você precisa definir suas preferências antes de continuar.");
                    window.location.href = '../html/SelecioneSuasPreferencias.html';
                }
            }
        })
        .catch(error => {
            console.error('Erro ao verificar sessão:', error);
            alert('Ocorreu um erro ao verificar sua sessão. Tente novamente.');
            window.location.href = '../html/login.html';
        });
});