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
    // O botão será desabilitado se menos de 3 interesses forem selecionados
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

    // Se o botão não estiver disabled, significa que pelo menos 3 interesses foram selecionados
    if (confirmButton.disabled) {
         alert("Por favor, selecione pelo menos 3 interesses.");
         return;
    }

    localStorage.setItem("userLanguage", selectedLanguage.value);
    localStorage.setItem("userInterests", JSON.stringify(selectedInterests));

    alert("Preferências salvas com sucesso!");
    // window.location.href = 'Home.html'; // Remova ou descomente se quiser redirecionar
});


// --- Lógica de Troca de Tema ---

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


// Lógica de Carregar Preferências (Idioma, Interesses e Tema) ao carregar a página
window.addEventListener("DOMContentLoaded", () => {
    // Carregar preferência de tema
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

    // Carregar preferências de idioma e interesses
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

    // Atualiza o estado do botão "Salvar Preferências" após carregar as seleções
    updateButtonState();
});