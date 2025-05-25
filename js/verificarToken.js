function mostrarMensagem(texto, tipo) {
  const msgDiv = document.getElementById("mensagem");
  msgDiv.style.display = "block";
  msgDiv.innerText = texto;
  msgDiv.style.backgroundColor = tipo === "success" ? "#d1fae5" : "#fee2e2";
  msgDiv.style.color = tipo === "success" ? "#065f46" : "#991b1b";
  msgDiv.style.border = "1px solid " + (tipo === "success" ? "#10b981" : "#f87171");
}

document.getElementById("tokenForm").addEventListener("submit", async function (e) {
  e.preventDefault();
  const token = this.token.value.trim();

  if (!token) {
    mostrarMensagem("Por favor, insira um token.", "error");
    return;
  }

  try {
    const response = await fetch("../php/verificarToken.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `token=${encodeURIComponent(token)}`
    });

    const data = await response.json();

    if (data.status === "success") {
      window.location.href = `redefinirSenha.html?token=${encodeURIComponent(token)}`;
    } else {
      mostrarMensagem(data.msg, "error");
    }
  } catch (error) {
    mostrarMensagem("Erro ao verificar o token.", "error");
  }
});
