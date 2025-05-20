// settings.js
const checkboxes = document.querySelectorAll('input[name="interests"]');
const radios = document.querySelectorAll('input[name="language"]');
const confirmButton = document.getElementById('confirmButton');

function updateButtonState() {
    const selected = Array.from(checkboxes).filter(cb => cb.checked);
    confirmButton.disabled = selected.length < 3;
}

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

    // Salvar no localStorage
    localStorage.setItem("userLanguage", selectedLanguage.value);
    localStorage.setItem("userInterests", JSON.stringify(selectedInterests));

    alert("Preferências salvas com sucesso!");
    // Redirecionar, se quiser:
    // window.location.href = 'Home.html';
});

// Carregar seleções anteriores
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

    updateButtonState();
});