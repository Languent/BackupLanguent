document.addEventListener('DOMContentLoaded', () => {
    // --- SELEÇÃO DOS ELEMENTOS DO DOM (PARA O PAINEL) ---
    const notificationsBtn = document.getElementById('notifications-btn');
    const notificationPanel = document.getElementById('notification-panel');
    const notificationCountSpan = document.getElementById('notification-count');
    const notificationList = document.getElementById('notification-list');

    let notificationCounter = 0;
    let notifications = []; // Array para guardar o histórico de notificações da sessão

    // --- LÓGICA PRINCIPAL DAS NOTIFICAÇÕES ---

    function initializeNotifications() {
        // 1. Verifica se o navegador suporta notificações
        if (!('Notification' in window)) {
            console.log("Este navegador não suporta notificações.");
            return;
        }

        // 2. LÓGICA PARA AGENDAR NOTIFICAÇÕES RECORRENTES (DE 8 EM 8 HORAS)
        function scheduleRecurringNotifications() {
            const messages = [
                { title: 'Novas Recomendações!', body: 'Preparamos uma nova lista de vídeos, musicas e podcasts para você. Venha conferir!' },
                { title: 'Conteúdo Fresco te Espera', body: 'Não perca as últimas novidades para acelerar seu aprendizado.' },
                { title: 'Sua Próxima Lição', body: 'Novas recomendações foram adicionadas à sua home. Explore agora!' },
                { title: 'Languent Atualizado', body: 'Adicionamos novos conteúdos que podem te interessar. Não fique de fora!' }
            ];

            const interval = 8 * 60 * 60 * 1000; // 8 horas em milissegundos

            // Inicia o agendamento
            setInterval(() => {
                // Escolhe uma mensagem aleatória do array
                const randomIndex = Math.floor(Math.random() * messages.length);
                const message = messages[randomIndex];
                
                // Cria e exibe a notificação
                const recurringNotification = new Notification(message.title, {
                    body: message.body,
                    icon: '../img/logo.jfif',
                    tag: 'recurring-recommendation' // Tag para evitar acúmulo de notificações iguais
                });
                
                // Adiciona ao painel visual
                addNotificationToPanel(message.title, message.body);

            }, interval);

            console.log(`Notificações recorrentes agendadas para cada 8 horas.`);
        }

        // 3. VERIFICA A PERMISSÃO E INICIA OS PROCESSOS
        // Caso 1: Permissão já foi concedida anteriormente.
        if (Notification.permission === 'granted') {
            console.log("Permissão já concedida. Iniciando notificações recorrentes.");
            scheduleRecurringNotifications();
        } 
        // Caso 2: Permissão ainda não foi solicitada.
        else if (Notification.permission === 'default') {
            document.body.addEventListener('click', () => {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        // Mostra a notificação de boas-vindas (APENAS NA PRIMEIRA VEZ)
                        const welcomeNotification = new Notification('Bem-vindo ao Languent!', {
                            body: 'Suas notificações estão ativadas. Avisaremos sobre novidades!',
                            icon: '../img/logo.jfif',
                            tag: 'welcome-message'
                        });
                        addNotificationToPanel('Bem-vindo ao Languent!', 'Suas notificações estão ativadas.');

                        // Inicia o ciclo de notificações recorrentes
                        scheduleRecurringNotifications();
                    }
                });
            }, { once: true });
        }
    }

    // --- FUNÇÕES DE GERENCIAMENTO DO PAINEL VISUAL ---

    function addNotificationToPanel(title, body) {
        const newNotification = { title, body, timestamp: new Date() };
        notifications.unshift(newNotification);
        notificationCounter++;
        notificationCountSpan.textContent = notificationCounter;
        notificationCountSpan.style.display = 'block';
        renderNotificationList();
    }
    
    function renderNotificationList() {
        notificationList.innerHTML = '';
        if (notifications.length === 0) {
            notificationList.innerHTML = '<li>Nenhuma notificação nova.</li>';
            return;
        }
        notifications.forEach(notif => {
            const item = document.createElement('li');
            item.className = 'notification-item';
            item.innerHTML = `<div class="notification-title">${notif.title}</div><div>${notif.body}</div>`;
            notificationList.appendChild(item);
        });
    }

    notificationsBtn.addEventListener('click', (event) => {
        event.preventDefault();
        notificationPanel.classList.toggle('show');
        if (notificationPanel.classList.contains('show')) {
            notificationCounter = 0;
            notificationCountSpan.style.display = 'none';
        }
    });

    document.addEventListener('click', (event) => {
        if (!notificationsBtn.contains(event.target) && !notificationPanel.contains(event.target)) {
            notificationPanel.classList.remove('show');
        }
    });

    // --- INICIA TODA A LÓGICA ---
    initializeNotifications();
    renderNotificationList();
});