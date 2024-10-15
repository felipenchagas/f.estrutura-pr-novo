document.addEventListener('DOMContentLoaded', function() {
    // --- Parte 1: Controle do Modal ---
    const modal = document.getElementById('contactModal');
    const btn = document.getElementById('openModalBtn');
    let span = document.querySelector('.close');

    if (btn && modal && span) {
        btn.addEventListener('click', function() {
            modal.style.display = "flex";
        });

        span.addEventListener('click', function() {
            modal.style.display = "none";
        });

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });
    }

    // --- Parte 2: Medidas Anti-Bot ---
    const form = document.getElementById('contact-form');
    if (form) {
        // Popula o campo hidden 'form_loaded_at' com o timestamp atual
        const formLoadedAtInput = document.getElementById('form_loaded_at');
        if (formLoadedAtInput) {
            formLoadedAtInput.value = Date.now();
        }

        // Opcional: Adicionar validações adicionais no lado do cliente
        form.addEventListener('submit', function(event) {
            const formLoadedAtValue = parseInt(formLoadedAtInput.value, 10);
            const currentTime = Date.now();
            const timeDiff = (currentTime - formLoadedAtValue) / 1000; // diferença em segundos

            if (formLoadedAtValue === 0 || timeDiff < 5) {
                alert("Você está preenchendo o formulário rápido demais! Por favor, tente novamente.");
                event.preventDefault(); // Impede o envio do formulário
                return;
            }

            const honeypot = document.getElementById('honeypot');
            if (honeypot && honeypot.value !== "") {
                alert("Erro: Formulário inválido.");
                event.preventDefault(); // Impede o envio do formulário
                return;
            }

            // Caso deseje exibir uma mensagem de carregamento ou desabilitar o botão de envio, pode-se fazer aqui
            // Exemplo:
            // const submitButton = form.querySelector('button[type="submit"]');
            // submitButton.disabled = true;
            // submitButton.textContent = "Enviando...";
        });
    }
});
