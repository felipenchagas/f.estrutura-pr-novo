<?php
session_start();

// **⚠️ IMPORTANTE:** Desative a exibição de erros em produção por segurança
ini_set('display_errors', 1); // Altere para 0 em produção
ini_set('display_startup_errors', 1); // Altere para 0 em produção
error_reporting(E_ALL); // Altere para 0 em produção

// Inclui o PHPMailer
require_once("novo/src/PHPMailer.php");
require_once("novo/src/SMTP.php");
require_once("novo/src/Exception.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Conectar ao primeiro banco de dados (informações fornecidas)
$servidor1 = "162.214.145.189";
$usuario1 = "empre028_felipe";
$senha1 = "Iuh86gwt--@Z123";
$banco1 = "empre028_estruturametalicapr";
$conexao1 = new mysqli($servidor1, $usuario1, $senha1, $banco1);

// Conectar ao segundo banco de dados (Locaweb)
$servidor2 = "localhost";
$usuario2 = "primeiro_estrupr";
$senha2 = "uRXA1r9Z7pv~Cw2";
$banco2 = "primeiro_estruturapr";
$conexao2 = new mysqli($servidor2, $usuario2, $senha2, $banco2);

// Verifica se a conexão foi bem-sucedida com ambos os bancos
if ($conexao1->connect_error || $conexao2->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conexao1->connect_error . " / " . $conexao2->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Função para sanitizar os dados de entrada
    function sanitizar($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    // Captura e sanitiza os dados do formulário
    $nome = sanitizar(isset($_POST['nome']) ? $_POST['nome'] : '');
    $email = sanitizar(isset($_POST['email']) ? $_POST['email'] : '');
    $ddd = sanitizar(isset($_POST['ddd']) ? $_POST['ddd'] : '');
    $telefone = sanitizar(isset($_POST['telefone']) ? $_POST['telefone'] : '');
    $cidade = sanitizar(isset($_POST['cidade']) ? $_POST['cidade'] : '');
    $estado = sanitizar(isset($_POST['estado']) ? $_POST['estado'] : '');
    $descricao = sanitizar(isset($_POST['descricao']) ? $_POST['descricao'] : '');
    $honeypot = sanitizar(isset($_POST['honeypot']) ? $_POST['honeypot'] : '');
    $form_loaded_at = isset($_POST['form_loaded_at']) ? intval($_POST['form_loaded_at']) : 0;

    // Verificação do Honeypot
    if (!empty($honeypot)) {
        // Submissão suspeita de bot
        header('Location: sucesso.html'); 
        exit();
    }

    // Verificação do Temporizador de Submissão
    $current_time = round(microtime(true) * 1000); // Tempo atual em milissegundos
    $time_diff = ($current_time - $form_loaded_at) / 1000; // diferença em segundos

    if ($form_loaded_at == 0 || $time_diff < 5) {  // Se o formulário foi enviado muito rapidamente
        // Submissão suspeita de bot
        header('Location: sucesso.html'); 
        exit();
    }

    // Validação básica dos campos (já realizada no frontend, mas reforçada aqui)
    if (empty($nome) || empty($email) || empty($ddd) || empty($telefone) || empty($cidade) || empty($estado) || empty($descricao)) {
        echo "Todos os campos são obrigatórios.";
        exit();
    }

    // Combina 'ddd' e 'telefone'
    $telefone_completo = "({$ddd}) {$telefone}";

    // Prepara a consulta SQL de inserção para o primeiro banco
    $sql1 = "INSERT INTO orcamentos (nome, email, telefone, cidade, estado, descricao, data_envio) 
             VALUES (?, ?, ?, ?, ?, ?, NOW())";

    // Prepara a consulta SQL de inserção para o segundo banco
    $sql2 = "INSERT INTO orcamentos (nome, email, telefone, cidade, estado, descricao, data_envio) 
             VALUES (?, ?, ?, ?, ?, ?, NOW())";

    // Prepara a inserção no primeiro banco
    $stmt1 = $conexao1->prepare($sql1);
    // Prepara a inserção no segundo banco
    $stmt2 = $conexao2->prepare($sql2);

    if ($stmt1 && $stmt2) {
        // Bind os parâmetros para ambos os bancos
        $stmt1->bind_param("ssssss", $nome, $email, $telefone_completo, $cidade, $estado, $descricao);
        $stmt2->bind_param("ssssss", $nome, $email, $telefone_completo, $cidade, $estado, $descricao);

        // Executa as inserções
        if ($stmt1->execute() && $stmt2->execute()) {
            // Envio do e-mail somente se os dados forem inseridos com sucesso nos dois bancos
            $mail = new PHPMailer(true);
            try {
                // Configurações do servidor SMTP
                $mail->isSMTP();
                $mail->Host       = 'mail.embrafer.com';    // Endereço do servidor SMTP
                $mail->SMTPAuth   = true;
                $mail->Username   = 'contato@estruturametalicapr.com.br'; // Seu email no SMTP
                $mail->Password   = 'Futgrass80802!';      // Sua senha no SMTP
                $mail->SMTPSecure = 'tls';                  // Criptografia TLS
                $mail->Port       = 587;                    // Porta SMTP

                // Remetente e destinatário
                $mail->setFrom('contato@estruturametalicapr.com.br', 'Embrafer Contato');
                $mail->addAddress('contato@estruturametalicapr.com.br', 'Embrafer'); // Email que receberá o aviso

                // Conteúdo do e-mail
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = 'Novo Contato - Site Empresarial';
                $mail->Body    = "
                    <html>
                    <body>
                        <h3>Contato recebido pelo site</h3>
                        <p><strong>Nome:</strong> $nome</p>
                        <p><strong>E-mail:</strong> $email</p>
                        <p><strong>Telefone:</strong> $telefone_completo</p>
                        <p><strong>Cidade:</strong> $cidade</p>
                        <p><strong>Estado:</strong> $estado</p>
                        <p><strong>Descrição:</strong> $descricao</p>
                        <p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>
                    </body>
                    </html>
                ";

                // Envia o e-mail
                $mail->send();

                // Redireciona para a página de sucesso
                header('Location: sucesso.html');
                exit();
            } catch (Exception $e) {
                echo "Erro ao enviar e-mail: " . $mail->ErrorInfo;
            }
        } else {
            echo "Erro ao inserir dados no banco 1: " . $stmt1->error;
            echo "Erro ao inserir dados no banco 2: " . $stmt2->error;
            exit();
        }
        $stmt1->close();
        $stmt2->close();
    } else {
        echo "Erro ao preparar a consulta no banco 1: " . $conexao1->error;
        echo "Erro ao preparar a consulta no banco 2: " . $conexao2->error;
        exit();
    }

    // Fecha a conexão com os bancos de dados
    $conexao1->close();
    $conexao2->close();
} else {
    echo "Método de requisição inválido.";
}
?>
