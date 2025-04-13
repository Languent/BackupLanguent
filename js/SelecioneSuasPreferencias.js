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
