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

    // --- Parte 2: Medidas Anti-Bot e Envio via AJAX ---
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
            const formLoadedAtValue = parseInt(formLoadedAtInput.value, 10);
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
            const formData = $(form).serialize();

            $.ajax({
                url: form.action,
                method: form.method,
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log(response); // Para depuração

                    if (response.status === 'success') {
                        // Exibe a mensagem de sucesso no modal
                        const modalContent = document.querySelector('.modal-content');
                        modalContent.innerHTML = `
                            <span class="close">&times;</span>
                            <h2>Solicitar Orçamento</h2>
                            <p style="color: #fff; font-size: 18px; text-align: center;">Formulário enviado com sucesso! Entraremos em contato em breve.</p>
                        `;

                        // Reatribui o evento de fechar o modal ao novo botão de fechar
                        span = modalContent.querySelector('.close');
                        if (span) {
                            span.addEventListener('click', function() {
                                modal.style.display = "none";
                            });
                        }
                    } else {
                        // Exibe mensagem de erro
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
