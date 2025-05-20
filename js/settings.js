// settings.js

// Elementos HTML
const checkboxes = document.querySelectorAll('input[name="interests"]');
const radios = document.querySelectorAll('input[name="language"]');
const confirmButton = document.getElementById('confirmButton');
const themeToggle = document.getElementById('themeToggle'); // O novo toggle switch
const body = document.body; // Referência ao body para adicionar/remover classes

// Função para atualizar o estado do botão Confirmar
function updateButtonState() {
    const selected = Array.from(checkboxes).filter(cb => cb.checked);
    confirmButton.disabled = selected.length < 3;
}

// Lógica de Salvar Preferências de Idioma e Interesses
checkboxes.forEach(cb => cb.addEventListener('change', updateButtonState));

confirmButton.addEventListener('click', () => {
    const selectedLanguage = document.querySelector('input[name="language"]:checked');
    const selectedInterests = Array.from(checkboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);

    if (!selectedLanguage) {
        alert("Por favor, selecione um idioma.");
        return;
    }

    localStorage.setItem("userLanguage", selectedLanguage.value);
    localStorage.setItem("userInterests", JSON.stringify(selectedInterests));

    alert("Preferências salvas com sucesso!");
    // window.location.href = 'Home.html'; // Remova ou descomente se quiser redirecionar
});

// Lógica de Carregar Preferências (Idioma e Interesses)
window.addEventListener("DOMContentLoaded", () => {
    const savedLang = localStorage.getItem("userLanguage");
    const savedInterests = JSON.parse(localStorage.getItem("userInterests")) || [];

    if (savedLang) {
        const langInput = document.querySelector(`input[name="language"][value="${savedLang}"]`);
        if (langInput) langInput.checked = true;
    }

    savedInterests.forEach(val => {
        const interestInput = document.querySelector(`input[name="interests"][value="${val}"]`);
        if (interestInput) interestInput.checked = true;
    });

    updateButtonState(); // Atualiza o estado do botão ao carregar
});

// --- Lógica de Troca de Tema (NOVA SEÇÃO) ---

// Função para aplicar o tema
function applyTheme(theme) {
    if (theme === 'dark') {
        body.classList.add('dark-theme');
        themeToggle.checked = true; // Garante que o toggle esteja na posição "escuro"
    } else {
        body.classList.remove('dark-theme');
        themeToggle.checked = false; // Garante que o toggle esteja na posição "claro"
    }
    localStorage.setItem('themePreference', theme); // Salva a preferência
}

// Event Listener para o toggle de tema
themeToggle.addEventListener('change', () => {
    if (themeToggle.checked) {
        applyTheme('dark');
    } else {
        applyTheme('light');
    }
});

// Carregar preferência de tema ao carregar a página
window.addEventListener("DOMContentLoaded", () => {
    const savedTheme = localStorage.getItem('themePreference');
    if (savedTheme) {
        applyTheme(savedTheme);
    } else {
        // Se não houver preferência salva, verifica a preferência do sistema
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            applyTheme('dark');
        } else {
            applyTheme('light');
        }
    }
    // Restante da lógica de carregar idioma e interesses (já existente)
    const savedLang = localStorage.getItem("userLanguage");
    const savedInterests = JSON.parse(localStorage.getItem("userInterests")) || [];

    if (savedLang) {
        const langInput = document.querySelector(`input[name="language"][value="${savedLang}"]`);
        if (langInput) langInput.checked = true;
    }

    savedInterests.forEach(val => {
        const interestInput = document.querySelector(`input[name="interests"][value="${val}"]`);
        if (interestInput) interestInput.checked = true;
    });

    updateButtonState();
});