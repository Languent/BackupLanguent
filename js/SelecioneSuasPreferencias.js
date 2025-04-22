document.addEventListener('DOMContentLoaded', function() {
  const contentItems = document.querySelectorAll('.content-item');
  const selectedCategoriesInput = document.getElementById('selectedCategoriesInput');
  const nextStepButton = document.getElementById('next-step');
  let selectedCategories = [];

  // Função para manipular a seleção de categorias
  contentItems.forEach(item => {
    item.addEventListener('click', function() {
      const category = this.getAttribute('data-category');

      if (selectedCategories.includes(category)) {
        // Remove categoria se já estiver selecionada
        selectedCategories = selectedCategories.filter(cat => cat !== category);
        this.classList.remove('selected');
      } else {
        // Adiciona nova categoria à lista
        selectedCategories.push(category);
        this.classList.add('selected');
      }

      // Atualiza o valor do campo oculto no formulário
      selectedCategoriesInput.value = selectedCategories.join(',');

      // Habilita o botão apenas se houver pelo menos 3 categorias selecionadas
      if (selectedCategories.length >= 3) {
        nextStepButton.disabled = false;
      } else {
        nextStepButton.disabled = true;
      }
    });
  });
});

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

