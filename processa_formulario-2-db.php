<?php
session_start();

// Para depuração, ative a exibição de erros temporariamente
// Remova ou comente estas linhas em produção
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("novo/src/PHPMailer.php");
require_once("novo/src/SMTP.php");
require_once("novo/src/Exception.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Função para detectar requisição AJAX
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Conectar ao primeiro banco de dados
$servidor1 = "162.214.145.189";
$usuario1 = "empre028_felipe";
$senha1 = "Iuh86gwt--@Z123";
$banco1 = "empre028_estruturapr";
$conexao1 = new mysqli($servidor1, $usuario1, $senha1, $banco1);

// Conectar ao segundo banco de dados (Locaweb)
$servidor2 = "localhost";
$usuario2 = "primeiro_estrupr";
$senha2 = "uRXA1r9Z7pv~Cw2";
$banco2 = "primeiro_estruturapr";
$conexao2 = new mysqli($servidor2, $usuario2, $senha2, $banco2);

// Verifica se a conexão foi bem-sucedida com ambos os bancos
if ($conexao1->connect_error) {
    file_put_contents('error_log.txt', "[" . date('Y-m-d H:i:s') . "] Erro de conexão banco 1: " . $conexao1->connect_error . "\n", FILE_APPEND);
}
if ($conexao2->connect_error) {
    file_put_contents('error_log.txt', "[" . date('Y-m-d H:i:s') . "] Erro de conexão banco 2: " . $conexao2->connect_error . "\n", FILE_APPEND);
}

if ($conexao1->connect_error || $conexao2->connect_error) {
    if (is_ajax_request()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Erro na conexão com o banco de dados.']);
    } else {
        header('Location: error.html');
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    function sanitizar($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    $nome = sanitizar($_POST['nome'] ?? '');
    $email = sanitizar($_POST['email'] ?? '');
    $ddd = sanitizar($_POST['ddd'] ?? '');
    $telefone = sanitizar($_POST['telefone'] ?? '');
    $cidade = sanitizar($_POST['cidade'] ?? '');
    $estado = sanitizar($_POST['estado'] ?? '');
    $descricao = sanitizar($_POST['descricao'] ?? '');
    $honeypot = sanitizar($_POST['honeypot'] ?? '');
    $form_loaded_at = intval($_POST['form_loaded_at'] ?? 0);

    if (!empty($honeypot)) {
        header('Location: successo.html');
        exit();
    }

    $current_time = round(microtime(true) * 1000);
    $time_diff = ($current_time - $form_loaded_at) / 1000;

    if ($form_loaded_at == 0 || $time_diff < 5) {
        header('Location: successo.html');
        exit();
    }

    if (empty($nome) || empty($email) || empty($ddd) || empty($telefone) || empty($cidade) || empty($estado) || empty($descricao)) {
        file_put_contents('error_log.txt', "[" . date('Y-m-d H:i:s') . "] Erro: Campos obrigatórios não preenchidos.\n", FILE_APPEND);
        if (is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Todos os campos são obrigatórios.']);
        } else {
            header('Location: error.html');
        }
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        file_put_contents('error_log.txt', "[" . date('Y-m-d H:i:s') . "] Erro: E-mail inválido.\n", FILE_APPEND);
        if (is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Endereço de e-mail inválido.']);
        } else {
            header('Location: error.html');
        }
        exit();
    }

    $telefone_completo = "({$ddd}) {$telefone}";

    $sql1 = "INSERT INTO orcamentos (nome, email, telefone, cidade, estado, descricao, data_envio) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $sql2 = "INSERT INTO orcamentos (nome, email, telefone, cidade, estado, descricao, data_envio) VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt1 = $conexao1->prepare($sql1);
    $stmt2 = $conexao2->prepare($sql2);

    if (!$stmt1 || !$stmt2) {
        file_put_contents('error_log.txt', "[" . date('Y-m-d H:i:s') . "] Erro ao preparar consulta SQL.\n", FILE_APPEND);
        if (is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Erro ao preparar consulta no banco de dados.']);
        } else {
            header('Location: error.html');
        }
        exit();
    }

    $stmt1->bind_param("ssssss", $nome, $email, $telefone_completo, $cidade, $estado, $descricao);
    $stmt2->bind_param("ssssss", $nome, $email, $telefone_completo, $cidade, $estado, $descricao);

    if (!$stmt1->execute()) {
        file_put_contents('error_log.txt', "[" . date('Y-m-d H:i:s') . "] Erro ao executar consulta banco 1: " . $stmt1->error . "\n", FILE_APPEND);
    }
    if (!$stmt2->execute()) {
        file_put_contents('error_log.txt', "[" . date('Y-m-d H:i:s') . "] Erro ao executar consulta banco 2: " . $stmt2->error . "\n", FILE_APPEND);
    }

    if ($stmt1->error || $stmt2->error) {
        if (is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar no banco de dados.']);
        } else {
            header('Location: error.html');
        }
        exit();
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.embrafer.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'contato@estruturametalicapr.com.br';
        $mail->Password = 'Futgrass80802!';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('contato@estruturametalicapr.com.br', 'Embrafer Contato');
        $mail->addAddress('contato@estruturametalicapr.com.br', 'Embrafer');

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Novo Contato - Site Empresarial';
        $mail->Body = "
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

        $mail->send();
        header('Location: sucesso.html');
        exit();
    } catch (Exception $e) {
        file_put_contents('error_log.txt', "[" . date('Y-m-d H:i:s') . "] Erro ao enviar e-mail: " . $mail->ErrorInfo . "\n", FILE_APPEND);
        if (is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Erro ao enviar e-mail.']);
        } else {
            header('Location: error.html');
        }
        exit();
    }
} else {
    if (is_ajax_request()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    } else {
        header('Location: error.html');
    }
    exit();
}
?>