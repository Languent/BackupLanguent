
  // Recupera o token da URL
  const params = new URLSearchParams(window.location.search);
  const token = params.get('token');

  function mostrarMensagem(texto, tipo) {
    const msgDiv = document.getElementById("mensagem");
    msgDiv.style.display = "block";
    msgDiv.innerText = texto;
    msgDiv.style.backgroundColor = tipo === "success" ? "#d1fae5" : "#fee2e2";
    msgDiv.style.color = tipo === "success" ? "#065f46" : "#991b1b";
    msgDiv.style.border = "1px solid " + (tipo === "success" ? "#10b981" : "#f87171");
  }

  document.getElementById("resetForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    const senha = this.senha.value.trim();
    const confirmar = this.confirmar.value.trim();

    if (!senha || !confirmar) {
      mostrarMensagem("Por favor, preencha todos os campos.", "error");
      return;
    }

    if (senha !== confirmar) {
      mostrarMensagem("As senhas não coincidem!", "error");
      return;
    }

    try {
      const response = await fetch("../php/redefinirSenha.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `token=${encodeURIComponent(token)}&senha=${encodeURIComponent(senha)}`
      });

      const data = await response.json();
      mostrarMensagem(data.msg, data.status);

      if (data.status === "success") {
        setTimeout(() => {
          window.location.href = "login.html";
        }, 2000);
      }
    } catch (error) {
      mostrarMensagem("Erro ao processar a solicitação.", "error");
    }
  });

