document.addEventListener('DOMContentLoaded', function () {

    let preferencias = [];
    let idioma = '';
    let temaIndex = 0;
    let nextPageToken = '';
    let carregando = false;

    const videoGrid = document.getElementById('video-grid');

    function carregarMaisVideos() {
        if (carregando || preferencias.length === 0) return;

        carregando = true;

        const tema = preferencias[temaIndex];
        const url = `../php/recomendacoes.php?pageToken=${nextPageToken}&tema=${tema}&idioma=${idioma}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log('[VIDEOS] Dados recebidos:', data);

                if (data.items && data.items.length > 0) {
                    data.items.forEach(video => {
                        const videoElement = document.createElement('div');
                        videoElement.classList.add('video-item');
                        videoElement.innerHTML = `
                            <iframe width="320" height="180" src="https://www.youtube.com/embed/${video.id}" frameborder="0" allowfullscreen></iframe>
                        `;
                        videoGrid.insertBefore(videoElement, sentinel); // Adiciona antes do sentinel
                    });
                }

                nextPageToken = data.nextPageToken || '';
                if (!nextPageToken) {
                    temaIndex = (temaIndex + 1) % preferencias.length;
                    nextPageToken = '';
                }

                carregando = false;
            })
            .catch(error => {
                console.error('Erro ao carregar vídeos:', error);
                carregando = false;
            });
    }

    // Início: Carregar sessão e preferências
    fetch('../php/get_sessao.php')
        .then(response => response.json())
        .then(data => {
            console.log('[GET_SESSAO] Resposta:', data);
            const id_usuario = data.id_usuario;
            const nome_usuario = data.nome;

            if (nome_usuario) {
                const nomeElemento = document.getElementById('nome');
                if (nomeElemento) {
                    nomeElemento.textContent = nome_usuario;
                }
            }

            return fetch(`../php/get_user_preferences.php?id_usuario=${id_usuario}`);
        })
        .then(response => response.json())
        .then(data => {

            preferencias = data.preferencias;
            idioma = data.idioma;
            temaIndex = 0;
            nextPageToken = '';

            videoGrid.innerHTML = ''; // Limpa os vídeos
            videoGrid.appendChild(sentinel); // Garante que o sentinel está no final
            carregarMaisVideos(); // Primeira carga
        })
        .catch(error => {
            console.error('Erro ao carregar sessão ou preferências:', error);
        });

    // Criar e observar o sentinel para scroll infinito
    const sentinel = document.createElement('div');
    sentinel.id = 'sentinel';
    sentinel.style.height = '1px';

    const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting && !carregando) {
            carregarMaisVideos();
        }
    }, {
        root: null,
        rootMargin: '0px',
        threshold: 1.0
    });

    observer.observe(sentinel);
});
