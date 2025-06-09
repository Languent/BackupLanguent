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
        elements.loadMoreBtn.disabled = true;
        elements.loadMoreBtn.textContent = 'Carregando...';

        // Se não for para adicionar, limpa a grade antes
        if (!isAppending) {
            elements.videoGrid.innerHTML = '';
        }

        // Adiciona 4 skeletons para indicar carregamento
        for (let i = 0; i < 4; i++) {
            const skeletonEl = document.createElement('div');
            skeletonEl.classList.add('video-item-skeleton');
            skeletonEl.innerHTML = `<div class="skeleton skeleton-thumbnail"></div>`;
            elements.videoGrid.appendChild(skeletonEl);
        }
    }

    function removeSkeletons() {
        elements.videoGrid.querySelectorAll('.video-item-skeleton').forEach(el => el.remove());
        elements.loadMoreBtn.disabled = false;
    }
    
    function renderVideos(data, isNew) {
        removeSkeletons();
        const { items, nextPageToken } = data;

        // Se for uma nova busca, limpa a grade
        if (isNew) {
            elements.videoGrid.innerHTML = '';
        }
        
        if (!items || items.length === 0) {
            if (isNew) {
                elements.videoGrid.innerHTML = `<p class="error-message">Nenhum vídeo encontrado. Tente outros filtros.</p>`;
            }
            // Se não há mais itens, esconde o botão
            elements.loadMoreBtn.style.display = 'none';
            return;
        }

        const fragment = document.createDocumentFragment();
        items.forEach(video => {
            const videoId = video.id;
            const videoEl = document.createElement('div');
            videoEl.classList.add('video-item');
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
        
        // Atualiza o estado do token e a visibilidade do botão
        state.pageToken = nextPageToken || '';
        elements.loadMoreBtn.style.display = state.pageToken ? 'block' : 'none';
        elements.loadMoreBtn.textContent = 'Carregar Mais Vídeos';
    }

    function fetchContent(isNew = false) {
        if (state.isLoading) return;
        state.isLoading = true;
        
        // Se for uma nova busca, reseta o token. Se for para "carregar mais", usa o token salvo.
        const pageTokenForRequest = isNew ? '' : state.pageToken;
        if (isNew) {
            state.pageToken = '';
        }
        
        showSkeletons(!isNew);

        const params = new URLSearchParams({
            mode: state.mode,
            type: state.contentType,
            q: state.currentQuery,
            pageToken: pageTokenForRequest
        });

        fetch(`../php/get_content.php?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                renderVideos(data, isNew);
            })
            .catch(error => {
                console.error('Falha ao buscar conteúdo:', error);
                removeSkeletons();
                elements.videoGrid.innerHTML = `<p class="error-message">Ocorreu um erro ao carregar os vídeos.</p>`;
            })
            .finally(() => { 
                state.isLoading = false; 
                elements.loadMoreBtn.disabled = false;
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

    // --- LISTENERS ---
    elements.loadMoreBtn.addEventListener('click', () => fetchContent(false)); // Carregar mais sempre adiciona

    elements.searchButton.addEventListener('click', () => {
        state.mode = 'search';
        state.currentQuery = elements.searchInput.value.trim();
        elements.mainTitle.textContent = state.currentQuery ? `Resultados para: "${state.currentQuery}"` : 'Busca';
        fetchContent(true); // Uma nova busca sempre limpa os resultados antigos
    });
    elements.searchInput.addEventListener('keypress', e => e.key === 'Enter' && elements.searchButton.click());

    elements.filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('active')) return;
            elements.filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            state.mode = 'recommendations';
            state.contentType = button.dataset.type;
            elements.mainTitle.textContent = 'Recomendações para você:';
            fetchContent(true); // Trocar de aba sempre gera uma nova lista
        });
    });

    // Carregamento inicial
    Promise.all([
        fetch('../php/get_sessao.php').then(res => res.json()),
        fetch('../php/get_avaliacoes_usuario.php').then(res => res.json())
    ]).then(([sessionData, ratingsData]) => {
        document.getElementById('nome').textContent = sessionData.nome || 'Usuário';
        state.userRatings = ratingsData.avaliacoes || {};
        fetchContent(true);
    });
});