document.addEventListener('DOMContentLoaded', () => {
    const videoGrid = document.getElementById('video-grid');
    let nextPageToken = '';
    let isLoading = false;

    fetch('../php/get_sessao.php')
        .then(response => response.json())
        .then(data => {
            if (data.id_usuario) {
                document.getElementById('nome').textContent = `${data.nome}`;
                loadUserPreferences(data.id_usuario);
            } else {
                document.getElementById('nome').textContent = "Erro: Sessão não encontrada.";
            }
        })
        .catch(error => console.error('Erro ao carregar dados da sessão:', error));

    function loadUserPreferences(id_usuario) {
        fetch(`../php/get_user_preferences.php?id_usuario=${id_usuario}`)
            .then(response => {
                if (!response.ok) throw new Error('Erro ao carregar preferências do usuário, status: ' + response.status);
                return response.json();
            })
            .then(preferences => {
                console.log('Preferências do usuário carregadas:', preferences);
                loadRecommendations(preferences);
            })
            .catch(error => console.error('Erro ao carregar preferências do usuário:', error));
    }

    function loadRecommendations(preferences) {
        if (!preferences || !preferences.preferencias || !Array.isArray(preferences.preferencias)) {
            console.error('Preferências inválidas, não será possível carregar vídeos.');
            return;
        }
        if (isLoading) return;
        isLoading = true;

        const temas = preferences.preferencias.join(',');
        const url = `../php/recomendacoes.php?pageToken=${nextPageToken}&tema=${encodeURIComponent(temas)}&idioma=${encodeURIComponent(preferences.idioma)}`;
        console.log('URL de requisição:', url);

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Erro ao carregar vídeos, status: ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error('Erro retornado pela API:', data.error);
                    return;
                }

                nextPageToken = data.nextPageToken || '';
                if (videoGrid && Array.isArray(data.items)) {
                    data.items.forEach(video => {
                        const videoItem = document.createElement('div');
                        videoItem.classList.add('video-item');
                        videoItem.innerHTML = `
                            <iframe width="100%" height="500" src="https://www.youtube.com/embed/${video.id}" frameborder="0" allowfullscreen></iframe>
                        `;
                        videoGrid.appendChild(videoItem);
                    });
                }
                isLoading = false;
            })
            .catch(error => {
                console.error('Erro ao carregar vídeos:', error);
                isLoading = false;
            });
    }
});
