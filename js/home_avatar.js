document.addEventListener('DOMContentLoaded', () => {
    const avatar = document.getElementById('user_avatar');
    const input = document.getElementById('fileInput');

    avatar.addEventListener('click', () => {
        input.click(); // abre seletor de arquivos
    });

    input.addEventListener('change', () => {
        const file = input.files[0];

        if (!file) {
            alert("Nenhuma imagem selecionada.");
            return;
        }

        const formData = new FormData();
        formData.append('avatar', file);

        fetch('../php/upload_avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload(); // recarrega com nova imagem
            } else {
                console.error(data.error);
                alert("Erro ao enviar imagem: " + data.error);
            }
        })
        .catch(err => {
            console.error("Erro na requisição:", err);
            alert("Erro inesperado no envio.");
        });
    });
});
