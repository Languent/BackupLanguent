document.addEventListener('DOMContentLoaded', function () {
    let state = {
        mode: 'recommendations',
        contentType: 'videos',
        currentQuery: '',
        pageToken: '',
        isLoading: false,
        userRatings: {}
    };

    const elements = {
        videoGrid: document.getElementById('video-grid'),
        loadMoreBtn: document.getElementById('load-more-btn'),
        searchButton: document.getElementById('search-button'),
        searchInput: document.getElementById('search-input'),
        mainTitle: document.getElementById('main-title'),
        filterButtons: document.querySelectorAll('.filter-btn'),
    };

    function showSkeletons(isAppending) {
        if (!isAppending) {
            elements.videoGrid.innerHTML = '';
        }
        
        // Controla o estado do botão "Carregar Mais"
        elements.loadMoreBtn.disabled = true;
        elements.loadMoreBtn.textContent = 'Carregando...';

        // Cria os skeletons para feedback visual
        for (let i = 0; i < 8; i++) {
            const skeletonEl = document.createElement('div');
            skeletonEl.classList.add('video-item-skeleton');
            skeletonEl.innerHTML = `<div class="skeleton-thumbnail"></div><div class="skeleton-bar"></div>`;
            elements.videoGrid.appendChild(skeletonEl);
        }
    }

    function renderGrid(data, isAppending) {
        // Remove os skeletons antes de adicionar o conteúdo real
        elements.videoGrid.querySelectorAll('.video-item-skeleton').forEach(el => el.remove());

        const { items, nextPageToken } = data;
        state.pageToken = nextPageToken || '';
        
        if (!items || items.length === 0) {
            if (elements.videoGrid.childElementCount === 0) {
                 elements.videoGrid.innerHTML = `<p class="error-message">Nenhum vídeo encontrado. Tente outros filtros.</p>`;
            }
            elements.loadMoreBtn.style.display = 'none';
            return;
        }

        const fragment = document.createDocumentFragment();
        items.forEach(video => {
            const videoId = video.id;
            const videoEl = document.createElement('div');
            videoEl.classList.add('video-item');
            videoEl.dataset.videoId = videoId; // Adiciona o ID para a correção de repetição
            videoEl.innerHTML = `
                <iframe src="https://www.youtube.com/embed/${videoId}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <div class="avaliacao-container">
                    <button class="btn-like ${state.userRatings[videoId] === 'like' ? 'active' : ''}" data-videoid="${videoId}"><i class="fa-solid fa-thumbs-up"></i></button>
                    <button class="btn-dislike ${state.userRatings[videoId] === 'dislike' ? 'active' : ''}" data-videoid="${videoId}"><i class="fa-solid fa-thumbs-down"></i></button>
                </div>`;
            videoEl.querySelector('.btn-like').addEventListener('click', (e) => rateVideo(e.currentTarget, videoId, 'like'));
            videoEl.querySelector('.btn-dislike').addEventListener('click', (e) => rateVideo(e.currentTarget, videoId, 'dislike'));
            fragment.appendChild(videoEl);
        });

        elements.videoGrid.appendChild(fragment);
        
        elements.loadMoreBtn.style.display = state.pageToken ? 'block' : 'none';
        elements.loadMoreBtn.textContent = 'Carregar Mais';
        elements.loadMoreBtn.disabled = false;
    }

    function fetchContent(isNew = false) {
        if (state.isLoading) return;
        state.isLoading = true;
        
        const pageTokenForRequest = isNew ? '' : state.pageToken;
        if (isNew) {
            state.pageToken = '';
            // Limpa o grid apenas se for uma busca completamente nova (ex: troca de aba ou pesquisa)
            elements.videoGrid.innerHTML = '';
        }
        
        showSkeletons(isNew ? false : true);

        // --- INÍCIO DA CORREÇÃO CONTRA REPETIÇÃO ---
        // Coleta os IDs dos vídeos que já estão na tela para evitar duplicatas
        const existingIds = isNew ? '' : Array.from(elements.videoGrid.querySelectorAll('.video-item'))
                                              .map(el => el.dataset.videoId)
                                              .join(',');
        // --- FIM DA CORREÇÃO ---

        const params = new URLSearchParams({
            mode: state.mode,
            type: state.contentType,
            q: state.currentQuery,
            pageToken: pageTokenForRequest,
            exclude_ids: existingIds // Envia os IDs para o backend
        });

        fetch(`../php/get_content.php?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                // A função de renderização agora sempre anexa (isAppending = true), pois a limpeza é feita antes.
                renderGrid(data, false); 
            })
            .catch(error => {
                console.error('Falha ao buscar conteúdo:', error);
                elements.videoGrid.innerHTML = `<p class="error-message">Ocorreu um erro ao carregar os vídeos.</p>`;
            })
            .finally(() => { 
                state.isLoading = false; 
                elements.loadMoreBtn.disabled = false;
                elements.loadMoreBtn.textContent = 'Carregar Mais';
            });
    }

    function rateVideo(buttonElement, videoId, type) {
        const container = buttonElement.parentElement;
        const likeBtn = container.querySelector('.btn-like');
        const dislikeBtn = container.querySelector('.btn-dislike');
        const isCurrentlyActive = buttonElement.classList.contains('active');

        fetch('../php/avaliar_video.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ videoId: videoId, tipo: type })
        })
        .then(res => res.json())
        .then(data => {
            if (data.sucesso) {
                state.userRatings[videoId] = isCurrentlyActive ? undefined : type;
                likeBtn.classList.remove('active');
                dislikeBtn.classList.remove('active');
                if (!isCurrentlyActive) {
                    buttonElement.classList.add('active');
                }
            }
        });
    }

    elements.loadMoreBtn.addEventListener('click', () => fetchContent(false));

    elements.searchButton.addEventListener('click', () => {
        state.mode = 'search';
        state.currentQuery = elements.searchInput.value.trim();
        if (!state.currentQuery) return;
        elements.mainTitle.textContent = `Resultados para: "${state.currentQuery}"`;
        fetchContent(true);
    });
    elements.searchInput.addEventListener('keypress', e => e.key === 'Enter' && elements.searchButton.click());

    elements.filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('active') && state.mode === 'recommendations') return;
            elements.filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            state.mode = 'recommendations';
            state.contentType = button.dataset.type;
            state.currentQuery = ''; // Limpa a query de busca ao voltar para recomendações
            elements.mainTitle.textContent = 'Recomendações para você:';
            fetchContent(true);
        });
    });

    // Carregamento inicial da página
    Promise.all([
        fetch('../php/get_sessao.php').then(res => res.json()),
        fetch('../php/get_avaliacoes_usuario.php').then(res => res.json())
    ]).then(([sessionData, ratingsData]) => {
        if(document.getElementById('nome')) {
            document.getElementById('nome').textContent = sessionData.nome || 'Usuário';
        }
        state.userRatings = ratingsData.avaliacoes || {};
        fetchContent(true); // Faz a primeira busca de conteúdo
    });
});