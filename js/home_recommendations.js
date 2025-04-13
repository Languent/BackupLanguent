document.addEventListener('DOMContentLoaded', function () {
    // Iniciar carregamento da sessão
    console.log('[INIT] DOM carregado, iniciando carregamento de sessão...');

    fetch('../php/get_sessao.php')
        .then(response => response.json())
        .then(data => {
            console.log('[GET_SESSAO] Resposta:', data);
            const id_usuario = data.id_usuario;

            // Carregar preferências do usuário
            console.log('[GET_PREFS] Carregando preferências do usuário:', id_usuario);
            return fetch(`../php/get_user_preferences.php?id_usuario=${id_usuario}`);
        })
        .then(response => response.json())
        .then(data => {
            console.log('[GET_PREFS] Preferências carregadas:', data);

            // Preparar URL com preferências e idioma
            const preferencias = data.preferencias.join(',');
            const idioma = data.idioma;
            const url = `../php/recomendacoes.php?pageToken=&tema=${preferencias}&idioma=${idioma}`;

            // Buscar vídeos
            console.log('[RECOMENDACOES] Buscando vídeos...');
            console.log('[RECOMENDACOES] URL:', url);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const videoGrid = document.getElementById('video-grid');
                    videoGrid.innerHTML = ''; // Limpa o conteúdo anterior
                    console.log('[VIDEOS] Dados recebidos:', data.items);
                    
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(video => {
                            const videoElement = document.createElement('div');
                            videoElement.classList.add('video-item');
                            videoElement.innerHTML = `
                                <iframe width="320" height="180" src="https://www.youtube.com/embed/${video.id}" frameborder="0" allowfullscreen></iframe>
                            `;
                            videoGrid.appendChild(videoElement);
                        });
                    } else {
                        videoGrid.innerHTML = '<p>Nenhum vídeo encontrado.</p>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar vídeos:', error);
                });
        })
        .catch(error => {
            console.error('Erro ao carregar sessão ou preferências:', error);
        });
});
