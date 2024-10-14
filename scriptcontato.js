document.addEventListener('DOMContentLoaded', function() {
    // --- Parte 1: Controle do Modal ---
    const modal = document.getElementById('contactModal');
    const btn = document.getElementById('openModalBtn');
    const span = document.querySelector('.close');

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
        const formLoadedAt = Date.now();
        const formLoadedAtInput = document.getElementById('form_loaded_at');
        if (formLoadedAtInput) {
            formLoadedAtInput.value = formLoadedAt;
        }

        form.addEventListener('submit', function(event) {
            const currentTime = Date.now();
            const formLoadedAtValue = parseInt(document.getElementById('form_loaded_at').value, 10);
            const timeDiff = (currentTime - formLoadedAtValue) / 1000;

            if (formLoadedAtValue === 0 || timeDiff < 5) {
                alert("Você está preenchendo o formulário rápido demais! Por favor, tente novamente.");
                event.preventDefault();  // Impede o envio
                return;
            }

            const honeypot = document.getElementById('honeypot');
            if (honeypot && honeypot.value !== "") {
                alert("Erro: Formulário inválido.");
                event.preventDefault();
                return;
            }

            // Se o formulário passou pelas validações, adicionamos o redirecionamento
            event.preventDefault();  // Bloqueia o comportamento padrão de submissão

            // Envia o formulário via AJAX (ou outro método)
            // Exemplo usando AJAX com jQuery, mas pode ser substituído por outro método
            $.ajax({
                url: form.action,  // O URL do action do formulário
                method: form.method,  // O método (POST)
                data: $(form).serialize(),
                success: function(response) {
                    // Redireciona para a página de sucesso
                    window.location.href = 'sucesso.html';
                },
                error: function() {
                    alert('Erro no envio do formulário.');
                }
            });
        });
    }
});
