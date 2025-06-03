// settings.js

const checkboxes = document.querySelectorAll('input[name="interests"]');
const radios = document.querySelectorAll('input[name="language"]');
const confirmButton = document.getElementById('confirmButton');
const themeToggle = document.getElementById('themeToggle');
const body = document.body;

function updateButtonState() {
    const selectedInterests = Array.from(checkboxes).filter(cb => cb.checked);
    confirmButton.disabled = selectedInterests.length < 3;
}

checkboxes.forEach(cb => {
    cb.addEventListener('change', updateButtonState);
});

confirmButton.addEventListener('click', async (event) => {
    event.preventDefault();

    const selectedLanguage = document.querySelector('input[name="language"]:checked');
    const selectedInterests = Array.from(checkboxes)
        .filter(cb => cb.checked)
        .map(cb => parseInt(cb.value)); // Garante que são números

    if (!selectedLanguage) {
        showNotification("Por favor, selecione um idioma.", "error");
        return;
    }

    if (confirmButton.disabled) {
        showNotification("Por favor, selecione pelo menos 3 interesses para salvar.", "error");
        return;
    }

    try {
        const response = await fetch('../php/settings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                language: selectedLanguage.value,
                interests: selectedInterests
            })
        });

        const result = await response.json();
        showNotification(result.message, result.success ? 'success' : 'error');

        if (result.success) {
            setTimeout(() => {
                window.location.href = 'home.html';
            }, 1500); // Espera 1.5s para mostrar a mensagem antes de redirecionar
        }

    } catch (error) {
        showNotification("Erro ao salvar as preferências. Tente novamente.", "error");
        console.error(error);
    }
});

function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `notification show ${type}`;

    setTimeout(() => {
        notification.classList.add('hidden');
    }, 3000);

    setTimeout(() => {
        notification.className = `notification hidden`;
    }, 3500);
}

function applyTheme(theme) {
    if (theme === 'dark') {
        body.classList.add('dark-theme');
        themeToggle.checked = true;
    } else {
        body.classList.remove('dark-theme');
        themeToggle.checked = false;
    }
    localStorage.setItem('themePreference', theme);
}

if (themeToggle) {
    themeToggle.addEventListener('change', () => {
        applyTheme(themeToggle.checked ? 'dark' : 'light');
    });
}

window.addEventListener("DOMContentLoaded", () => {
    const savedTheme = localStorage.getItem('themePreference');
    if (savedTheme) {
        applyTheme(savedTheme);
    } else {
        applyTheme(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    }

    const savedLang = localStorage.getItem("userLanguage");
    if (savedLang) {
        const langInput = document.querySelector(`input[name="language"][value="${savedLang}"]`);
        if (langInput) langInput.checked = true;
    }

    const savedInterests = JSON.parse(localStorage.getItem("userInterests")) || [];
    savedInterests.forEach(val => {
        const interestInput = document.querySelector(`input[name="interests"][value="${val}"]`);
        if (interestInput) interestInput.checked = true;
    });

    updateButtonState();
});
