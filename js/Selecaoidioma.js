document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('idioma');
    
    // Função para atualizar a imagem de fundo do select
    function updateSelectBackground() {
      const selectedOption = select.options[select.selectedIndex];
      const imgSrc = selectedOption.getAttribute('data-img');
      select.style.backgroundImage = `url('${imgSrc}')`;
      select.style.backgroundSize = '20px 20px'; // Ajuste o tamanho da imagem da bandeira
      select.style.backgroundPosition = 'left center'; // Posição da imagem
      select.style.paddingLeft = '40px'; // Espaço para a imagem
    }
  
    // Atualiza a imagem de fundo ao mudar a seleção
    select.addEventListener('change', updateSelectBackground);
  
    // Inicializa a imagem de fundo
    updateSelectBackground();
  });
  