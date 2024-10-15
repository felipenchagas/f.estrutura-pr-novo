<?php
session_start();

// **⚠️ IMPORTANTE:** Desative a exibição de erros em produção por segurança
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Inicia o buffer de saída
ob_start();

// Inclui o PHPMailer
require_once("novo/src/PHPMailer.php");
require_once("novo/src/SMTP.php");
require_once("novo/src/Exception.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Conectar ao primeiro banco de dados
$servidor1 = "162.214.145.189";
$usuario1 = "empre028_felipe";
$senha1 = "Iuh86gwt--@Z123"; // Sua senha
$banco1 = "empre028_estruturapr";
$conexao1 = new mysqli($servidor1, $usuario1, $senha1, $banco1);

// Conectar ao segundo banco de dados (Locaweb)
$servidor2 = "localhost";
$usuario2 = "primeiro_estrupr";
$senha2 = "uRXA1r9Z7pv~Cw2";
$banco2 = "primeiro_estruturapr";
$conexao2 = new mysqli($servidor2, $usuario2, $senha2, $banco2);

// Verifica se a conexão foi bem-sucedida com ambos os bancos
if ($conexao1->connect_error || $conexao2->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Erro na conexão com o banco de dados.']);
    exit();
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
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']); // Retorna sucesso para evitar feedback aos bots
        exit();
    }

    // Verificação do Temporizador de Submissão
    $current_time = round(microtime(true) * 1000); // Tempo atual em milissegundos
    $time_diff = ($current_time - $form_loaded_at) / 1000; // diferença em segundos

    if ($form_loaded_at == 0 || $time_diff < 5) {
        // Submissão suspeita de bot
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']); // Retorna sucesso para evitar feedback aos bots
        exit();
    }

    // Validação básica dos campos
    if (empty($nome) || empty($email) || empty($ddd) || empty($telefone) || empty($cidade) || empty($estado) || empty($descricao)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Todos os campos são obrigatórios.']);
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
                $mail->Host       = 'mail.embrafer.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'contato@estruturametalicapr.com.br';
                $mail->Password   = 'Futgrass80802!';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                // Remetente e destinatário
                $mail->setFrom('contato@estruturametalicapr.com.br', 'Embrafer Contato');
                $mail->addAddress('contato@estruturametalicapr.com.br', 'Embrafer');

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

                // Retorna resposta de sucesso em JSON
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success']);
                exit();
            } catch (Exception $e) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Erro ao enviar e-mail.']);
                exit();
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Erro ao inserir dados no banco de dados.']);
            exit();
        }
        $stmt1->close();
        $stmt2->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Erro ao preparar a consulta no banco de dados.']);
        exit();
    }

    // Fecha a conexão com os bancos de dados
    $conexao1->close();
    $conexao2->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    exit();
}

// Limpa o buffer de saída e encerra
ob_end_clean();
