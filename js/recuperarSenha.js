document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("recuperar-form");
    const msgDiv = document.getElementById("msg");
  
    form.addEventListener("submit", function (e) {
      e.preventDefault();
  
      const email = document.getElementById("email").value;
  
      fetch("../php/EnviarToken.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `email=${encodeURIComponent(email)}`
      })
      .then(response => response.json())
      .then(result => {
        msgDiv.textContent = result.msg;
        msgDiv.style.display = "block";
        msgDiv.className = result.status === "success" ? "msg-success" : "msg-error";
  
        if (result.status === "success") {
          form.reset();
        }
  
        // Remover classe fade-out se houver
        msgDiv.classList.remove("fade-out");
  
        // Aplica o fade-out após 4 segundos e remove a mensagem após 5 segundos
        setTimeout(() => {
          msgDiv.classList.add("fade-out");
        }, 4000);
  
        setTimeout(() => {
          msgDiv.style.display = "none";
          msgDiv.classList.remove("fade-out");
        }, 5000);
      })
      .catch(error => {
        msgDiv.textContent = "Erro ao enviar solicitação. Tente novamente mais tarde.";
        msgDiv.className = "msg-error";
        msgDiv.style.display = "block";
  
        setTimeout(() => {
          msgDiv.classList.add("fade-out");
        }, 4000);
  
        setTimeout(() => {
          msgDiv.style.display = "none";
          msgDiv.classList.remove("fade-out");
        }, 5000);
  
        console.error("Erro:", error);
      });
    });
  });
  