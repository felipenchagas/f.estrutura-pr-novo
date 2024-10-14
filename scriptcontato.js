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
            event.preventDefault();  // Impede o envio padrão do formulário

            const currentTime = Date.now();
            const formLoadedAtValue = parseInt(document.getElementById('form_loaded_at').value, 10);
            const timeDiff = (currentTime - formLoadedAtValue) / 1000;

            if (formLoadedAtValue === 0 || timeDiff < 5) {
                alert("Você está preenchendo o formulário rápido demais! Por favor, tente novamente.");
                return;
            }

            const honeypot = document.getElementById('honeypot');
            if (honeypot && honeypot.value !== "") {
                alert("Erro: Formulário inválido.");
                return;
            }

            // Envia o formulário via AJAX
            $.ajax({
                url: form.action,  // O URL do action do formulário
                method: form.method,  // O método (POST)
                data: $(form).serialize(),
                dataType: 'json', // Especifica que esperamos um JSON de resposta
                success: function(response) {
                    if (response.status === 'success') {
                        // Redireciona para a página de sucesso
                        window.location.href = 'sucesso.html';
                    } else {
                        alert('Erro: ' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Erro no envio do formulário:', textStatus, errorThrown);
                    alert('Ocorreu um erro ao enviar o formulário.');
                }
            });
        });
    }
});
