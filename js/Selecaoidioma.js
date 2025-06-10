// Aguarda o documento HTML ser completamente carregado e analisado.
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Seleciona os elementos essenciais da página que serão manipulados.
    const languageCards = document.querySelectorAll('.language-card');
    const hiddenInput = document.getElementById('idioma');
    const nextButton = document.getElementById('next-step');

    // 2. VERIFICAÇÃO DE SEGURANÇA:
    // Confirma se os elementos essenciais realmente existem na página.
    // Se algum não for encontrado, exibe um erro no console e interrompe o script.
    if (languageCards.length === 0 || !hiddenInput || !nextButton) {
        console.error("Erro de integração: Um ou mais elementos essenciais ('.language-card', '#idioma', '#next-step') não foram encontrados. Verifique os IDs e classes no arquivo HTML.");
        return;
    }

    // 3. CONTROLE INICIAL DO BOTÃO:
    // Desabilita o botão "Avançar" via JavaScript assim que a página é carregada.
    // O usuário não poderá avançar antes de fazer uma seleção.
    nextButton.disabled = true;

    // 4. ADICIONA INTERATIVIDADE AOS CARDS:
    // Itera sobre cada card de idioma para adicionar um "ouvinte" de clique.
    languageCards.forEach(card => {
        card.addEventListener('click', () => {
            // Primeiro, remove a classe 'selected' de todos os outros cards
            // para garantir que apenas um esteja selecionado por vez.
            languageCards.forEach(otherCard => {
                otherCard.classList.remove('selected');
            });

            // Adiciona a classe 'selected' ao card que foi clicado,
            // permitindo um feedback visual (que deve ser estilizado no CSS).
            card.classList.add('selected');

            // Atualiza o valor do campo de formulário oculto 'idioma' com o
            // valor do atributo 'data-value' do card selecionado.
            hiddenInput.value = card.dataset.value;

            // 5. HABILITA O BOTÃO:
            // Uma vez que um idioma foi selecionado, o botão "Avançar" é habilitado.
            nextButton.disabled = false;
        });
    });

    // 6. VERIFICAÇÃO DE SESSÃO DO USUÁRIO:
    // (Esta parte permanece como estava, verificando se o usuário está logado)
    fetch('../php/verificar_sessao.php')
        .then(response => {
            if (!response.ok) {
                // Captura erros de rede ou do servidor (ex: arquivo não encontrado, erro interno)
                throw new Error(`Erro na rede ou no servidor: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && !data.sessao_ativa) {
                alert("Você precisa estar logado para acessar esta página.");
                window.location.href = '../html/login.html';
            }
            // Outras lógicas de sessão podem ser adicionadas aqui
        })
        .catch(error => {
            console.error('Erro ao verificar sessão:', error);
            // Pode-se adicionar um alerta para o usuário se a verificação de sessão falhar
            // alert('Ocorreu um erro ao verificar sua sessão. Tente recarregar a página.');
        });
});