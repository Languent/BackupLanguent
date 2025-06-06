document.addEventListener('DOMContentLoaded', function () {
    let state = {
        mode: 'recommendations',
        contentType: 'videos',
        currentQuery: '',
        nextPageToken: '',
        isLoading: false,
        userRatings: {}
    };

    const videoGrid = document.getElementById('video-grid');
    const sentinel = document.createElement('div');
    sentinel.id = 'sentinel';
    const searchButton = document.getElementById('search-button');
    const searchInput = document.getElementById('search-input');
    const recommendationsTitle = document.querySelector('main h3');
    const filterButtons = document.querySelectorAll('.filter-btn');

    function showSkeletons(count = 8) {
        videoGrid.innerHTML = '';
        for (let i = 0; i < count; i++) {
            const skeletonElement = document.createElement('div');
            skeletonElement.classList.add('video-item-skeleton');
            skeletonElement.innerHTML = `<div class="skeleton skeleton-thumbnail"></div><div class="skeleton skeleton-bar"></div>`;
            videoGrid.appendChild(skeletonElement);
        }
    }

    function hideSkeletons() {
        videoGrid.querySelectorAll('.video-item-skeleton').forEach(el => el.remove());
    }

    function renderVideos(videos) {
        if (!videos || videos.length === 0) {
            if (videoGrid.childElementCount === 0) {
                recommendationsTitle.textContent = `Nenhum ${state.contentType.slice(0, -1)} encontrado.`;
            }
            return;
        }
        const fragment = document.createDocumentFragment();
        videos.forEach(video => {
            const videoId = video.id;
            const rating = state.userRatings[videoId] || '';
            const videoElement = document.createElement('div');
            videoElement.classList.add('video-item');
            videoElement.innerHTML = `
                <iframe width="320" height="400" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <div class="avaliacao-container">
                    <button class="btn-like ${rating === 'like' ? 'active' : ''}" data-videoid="${videoId}"><i class="fa-solid fa-thumbs-up"></i></button>
                    <button class="btn-dislike ${rating === 'dislike' ? 'active' : ''}" data-videoid="${videoId}"><i class="fa-solid fa-thumbs-down"></i></button>
                </div>
            `;
            videoElement.querySelector('.btn-like').addEventListener('click', () => rateVideo(videoId, 'like'));
            videoElement.querySelector('.btn-dislike').addEventListener('click', () => rateVideo(videoId, 'dislike'));
            fragment.appendChild(videoElement);
        });
        videoGrid.appendChild(fragment);
    }
    
    function rateVideo(videoId, clickedType) {
        // (A função rateVideo da resposta anterior pode ser mantida aqui, sem alterações)
    }

    function fetchContent() {
        if (state.isLoading || state.nextPageToken === null) return;
        state.isLoading = true;
        videoGrid.appendChild(sentinel);

        const params = new URLSearchParams({
            mode: state.mode,
            type: state.contentType,
            q: state.currentQuery,
            pageToken: state.nextPageToken
        });

        fetch(`../php/get_content.php?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (state.nextPageToken === '') hideSkeletons();
                renderVideos(data.items);
                state.nextPageToken = data.nextPageToken || null;
            })
            .catch(console.error)
            .finally(() => { state.isLoading = false; });
    }

    function resetAndLoad(newState) {
        Object.assign(state, {
            nextPageToken: '',
            isLoading: false,
            ...newState
        });
        
        if (state.mode === 'search' && state.currentQuery) {
            recommendationsTitle.textContent = `Resultados para: "${state.currentQuery}"`;
        } else {
            state.mode = 'recommendations';
            state.currentQuery = '';
            recommendationsTitle.textContent = 'Recomendações para você:';
        }
        showSkeletons();
        fetchContent();
    }

    // --- INICIALIZAÇÃO E LISTENERS ---
    searchButton.addEventListener('click', () => {
        const query = searchInput.value.trim();
        resetAndLoad({ 
            mode: query ? 'search' : 'recommendations', 
            currentQuery: query 
        });
    });
    searchInput.addEventListener('keypress', e => e.key === 'Enter' && searchButton.click());

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('active')) return;
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // O modo (busca ou recomendação) é mantido, apenas o tipo de conteúdo é alterado
            resetAndLoad({ contentType: button.dataset.type });
        });
    });

    const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting && !state.isLoading) {
            fetchContent();
        }
    }, { rootMargin: '500px' });
    observer.observe(sentinel);

    // Carregamento inicial da página
    Promise.all([
        fetch('../php/get_sessao.php').then(res => res.json()),
        fetch('../php/get_avaliacoes_usuario.php').then(res => res.json())
    ]).then(([sessionData, ratingsData]) => {
        document.getElementById('nome').textContent = sessionData.nome || 'Usuário';
        state.userRatings = ratingsData.avaliacoes || {};
        fetchContent(); // Carrega o lote inicial de recomendações
    }).catch(error => {
        hideSkeletons();
        recommendationsTitle.textContent = 'Erro ao carregar dados iniciais.';
        console.error(error);
    });
});