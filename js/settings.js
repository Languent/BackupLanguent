// settings.js

// Elementos HTML
const checkboxes = document.querySelectorAll('input[name="interests"]');
const radios = document.querySelectorAll('input[name="language"]');
const confirmButton = document.getElementById('confirmButton');
const themeToggle = document.getElementById('themeToggle');
const body = document.body;

// DEBBUGING: Verificar se os elementos foram encontrados
console.log("Confirm Button:", confirmButton);
console.log("Checkboxes:", checkboxes);
console.log("Radios:", radios);
console.log("Theme Toggle:", themeToggle);


// Função para atualizar o estado do botão Confirmar
function updateButtonState() {
    const selectedInterests = Array.from(checkboxes).filter(cb => cb.checked);
    console.log("Interesses selecionados:", selectedInterests.length); // DEBUGGING
    
    // O botão será desabilitado se menos de 3 interesses forem selecionados
    confirmButton.disabled = selectedInterests.length < 3;
    console.log("Botão Salvar está desabilitado?", confirmButton.disabled); // DEBUGGING
}

// Lógica de Salvar Preferências de Idioma e Interesses
checkboxes.forEach(cb => {
    cb.addEventListener('change', updateButtonState);
    // DEBUGGING: Adicionar listener para cada checkbox
    console.log(`Listener 'change' adicionado ao checkbox: ${cb.value}`);
});

// Opcional: Para garantir que o botão seja habilitado ao selecionar um rádio de idioma (se a regra mudar no futuro)
// radios.forEach(radio => radio.addEventListener('change', updateButtonState));


confirmButton.addEventListener('click', (event) => {
    // PREVINE O COMPORTAMENTO PADRÃO DO BOTÃO (enviar formulário, recarregar página)
    event.preventDefault(); 

    const selectedLanguage = document.querySelector('input[name="language"]:checked');
    const selectedInterests = Array.from(checkboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);

    if (!selectedLanguage) {
        alert("Por favor, selecione um idioma.");
        return; // Impede a continuação se nenhum idioma for selecionado
    }

    // A verificação de disabled já é feita pela função updateButtonState().
    // Se o botão não estiver disabled, significa que selectedInterests.length >= 3.
    if (confirmButton.disabled) {
        // Isso não deveria acontecer se o botão estiver habilitado para ser clicado,
        // mas é uma salvaguarda.
        alert("Por favor, selecione pelo menos 3 interesses para salvar.");
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
        themeToggle.checked = true;
    } else {
        body.classList.remove('dark-theme');
        themeToggle.checked = false;
    }
    localStorage.setItem('themePreference', theme);
    console.log(`Tema aplicado: ${theme}`); // DEBUGGING
}

// Event Listener para o toggle de tema
if (themeToggle) { // Garante que o themeToggle existe antes de adicionar o listener
    themeToggle.addEventListener('change', () => {
        if (themeToggle.checked) {
            applyTheme('dark');
        } else {
            applyTheme('light');
        }
    });
    console.log("Listener 'change' adicionado ao themeToggle."); // DEBUGGING
} else {
    console.error("Erro: Elemento #themeToggle não encontrado."); // DEBUGGING
}


// Lógica de Carregar Preferências (Idioma, Interesses e Tema) ao carregar a página
window.addEventListener("DOMContentLoaded", () => {
    console.log("DOM totalmente carregado. Iniciando carregamento de preferências."); // DEBUGGING

    // Carregar preferência de tema
    const savedTheme = localStorage.getItem('themePreference');
    if (savedTheme) {
        applyTheme(savedTheme);
    } else {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            applyTheme('dark');
        } else {
            applyTheme('light');
        }
    }

    // Carregar preferências de idioma
    const savedLang = localStorage.getItem("userLanguage");
    if (savedLang) {
        const langInput = document.querySelector(`input[name="language"][value="${savedLang}"]`);
        if (langInput) {
            langInput.checked = true;
            console.log(`Idioma carregado: ${savedLang}`); // DEBUGGING
        }
    }

    // Carregar preferências de interesses
    const savedInterests = JSON.parse(localStorage.getItem("userInterests")) || [];
    savedInterests.forEach(val => {
        const interestInput = document.querySelector(`input[name="interests"][value="${val}"]`);
        if (interestInput) {
            interestInput.checked = true;
            console.log(`Interesse carregado: ${val}`); // DEBUGGING
        }
    });

    // MUITO IMPORTANTE: Chamar updateButtonState() APÓS carregar as seleções
    updateButtonState(); 
    console.log("Função updateButtonState() chamada após carregar preferências."); // DEBUGGING
});