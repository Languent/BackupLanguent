document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('checkbox');
    const body = document.body;

    // Função para aplicar o tema salvo
    const applySavedTheme = () => {
        const savedTheme = localStorage.getItem('theme');
        // Se o tema salvo for 'dark' ou se não houver tema salvo e o usuário preferir o modo escuro
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            body.classList.add('dark-mode');
            themeToggle.checked = true;
        } else {
            body.classList.remove('dark-mode');
            themeToggle.checked = false;
        }
    };

    // Adiciona o listener para o clique no botão
    themeToggle.addEventListener('change', () => {
        if (themeToggle.checked) {
            body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark'); // Salva a preferência
        } else {
            body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light'); // Salva a preferência
        }
    });

    // Aplica o tema salvo quando a página carrega
    applySavedTheme();
});