var btnSignin = document.querySelector("#signin");
var btnSignup = document.querySelector("#signup");


var body = document.querySelector("body");




btnSignin.addEventListener("click", function () {
  body.className = "sign-in-js";
});


btnSignup.addEventListener("click", function () {
   body.className = "sign-up-js";
})


var btnSignin = document.querySelector("#signin");
var btnSignup = document.querySelector("#signup");


var body = document.querySelector("body");


btnSignin.addEventListener("click", function () {
  body.className = "sign-in-js";
});


btnSignup.addEventListener("click", function () {
   body.className = "sign-up-js";
})

//Validação de e-mail
document.querySelector("form").addEventListener("submit", function (e) {
	const email = document.getElementById("email").value;
	const dominioValido = /^[a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com|outlook\.com)$/;

	if (!dominioValido.test(email)) {
		alert("Por favor, use um e-mail com domínio válido (gmail.com, hotmail.com, outlook.com).");
		e.preventDefault();
	}
});

//Validação de senha com no mínimo 7 caracteres 

document.addEventListener("DOMContentLoaded", function () {
	const forms = document.querySelectorAll("form");

	forms.forEach(function(form) {
		form.addEventListener("submit", function (e) {
			const senhaInput = form.querySelector('input[name="senha"]');
			if (senhaInput && senhaInput.value.length < 7) {
				alert("A senha deve conter pelo menos 7 caracteres.");
				e.preventDefault();
			}
		});
	});
});

//Mensagens de erro

window.addEventListener("DOMContentLoaded", function () {
  const params = new URLSearchParams(window.location.search);
  if (params.has("mensagem")) {
      alert(decodeURIComponent(params.get("mensagem")));
  }
});




