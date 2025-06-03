document.addEventListener('DOMContentLoaded', function () {
    let preferencias = [];
    let idioma = '';
    let temaIndex = 0;
    let nextPageToken = '';
    let carregando = false;
    let avaliacoesUsuario = {};

    const videoGrid = document.getElementById('video-grid');
    const sentinel = document.createElement('div');
    sentinel.id = 'sentinel';
    sentinel.style.height = '1px';

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
                        const avaliacao = avaliacoesUsuario[video.id];
                        const likeClass = avaliacao === 'like' ? 'active' : '';
                        const dislikeClass = avaliacao === 'dislike' ? 'active' : '';

                        const videoElement = document.createElement('div');
                        videoElement.classList.add('video-item');
                        videoElement.innerHTML = `
                            <iframe width="320" height="180" src="https://www.youtube.com/embed/${video.id}" frameborder="0" allowfullscreen></iframe>
                            <div class="avaliacao-container">
                                <button class="btn-like ${likeClass}" data-videoid="${video.id}"><i class="fa-solid fa-thumbs-up"></i></button>
                                <button class="btn-dislike ${dislikeClass}" data-videoid="${video.id}"><i class="fa-solid fa-thumbs-down"></i></button>
                            </div>
                        `;

                        videoElement.querySelector('.btn-like').addEventListener('click', () => {
                            avaliarVideo(video.id, 'like');
                        });

                        videoElement.querySelector('.btn-dislike').addEventListener('click', () => {
                            avaliarVideo(video.id, 'dislike');
                        });

                        videoGrid.insertBefore(videoElement, sentinel);
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

    // Envia a avaliação para o servidor e atualiza visualmente
    function avaliarVideo(videoId, tipo) {
        fetch('../php/avaliar_video.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ videoId, tipo })
        })
        .then(res => res.json())
        .then(data => {
            console.log('Avaliação salva:', data);
            avaliacoesUsuario[videoId] = tipo;

            const likeBtn = document.querySelector(`.btn-like[data-videoid="${videoId}"]`);
            const dislikeBtn = document.querySelector(`.btn-dislike[data-videoid="${videoId}"]`);

            if (tipo === 'like') {
                likeBtn.classList.add('active');
                dislikeBtn.classList.remove('active');
            } else if (tipo === 'dislike') {
                dislikeBtn.classList.add('active');
                likeBtn.classList.remove('active');
            }
        })
        .catch(err => {
            console.error('Erro ao salvar avaliação:', err);
        });
    }

    // Início: Carrega sessão, preferências e avaliações
    fetch('../php/get_sessao.php')
        .then(response => response.json())
        .then(data => {
            console.log('[GET_SESSAO] Resposta:', data);
            const id_usuario = data.id_usuario;
            const nome_usuario = data.nome;

            const nomeElemento = document.getElementById('nome');
            if (nome_usuario && nomeElemento) {
                nomeElemento.textContent = nome_usuario;
            }

            return Promise.all([
                fetch(`../php/get_user_preferences.php?id_usuario=${id_usuario}`).then(res => res.json()),
                fetch('../php/get_avaliacoes_usuario.php').then(res => res.json())
            ]);
        })
        .then(([prefsData, avaliacoesData]) => {
            preferencias = prefsData.preferencias || [];
            idioma = prefsData.idioma;
            avaliacoesUsuario = avaliacoesData.avaliacoes || {};

            temaIndex = 0;
            nextPageToken = '';

            videoGrid.innerHTML = '';
            videoGrid.appendChild(sentinel);
            carregarMaisVideos();
        })
        .catch(error => {
            console.error('Erro ao carregar dados iniciais:', error);
        });

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
