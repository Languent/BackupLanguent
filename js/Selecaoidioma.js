document.addEventListener('DOMContentLoaded', function () {
  const languageCards = document.querySelectorAll('.language-card');
  const hiddenInput = document.getElementById('idioma');
  const nextButton = document.getElementById('nextButton');

  // Adiciona evento de clique a cada card de idioma
  languageCards.forEach(card => {
    card.addEventListener('click', () => {
      // Remove a classe 'selected' de todos os cards
      languageCards.forEach(otherCard => {
        otherCard.classList.remove('selected');
      });

      // Adiciona a classe 'selected' ao card clicado
      card.classList.add('selected');

      // Atualiza o valor do input hidden com o data-value do card
      hiddenInput.value = card.dataset.value;

      // Habilita o botão "Avançar"
      nextButton.disabled = false;
    });
  });

  // Lógica de verificação de sessão (mantida como estava)
  fetch('../php/verificar_sessao.php')
    .then(response => response.json())
    .then(data => {
      if (data.sessao_ativa) {
        if (!data.pode_avancar) {
          if (data.erros.includes('idioma_nao_definido')) {
            alert("Você precisa definir um idioma antes de continuar.");
            // Não redirecionamos aqui imediatamente, permitindo que o usuário selecione
            // O botão estará desabilitado até que uma seleção seja feita
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