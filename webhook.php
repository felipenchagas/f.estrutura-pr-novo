<?php

$secret = "@embra8080z"; // Substitua pelo seu secret
$header = "X-Hub-Signature";

// Obtém a assinatura do GitHub do cabeçalho da solicitação
$githubSignature = isset($_SERVER['HTTP_X_HUB_SIGNATURE']) ? $_SERVER['HTTP_X_HUB_SIGNATURE'] : '';

if (!empty($githubSignature)) {
    $payload = file_get_contents('php://input');
    $signature = 'sha1=' . hash_hmac('sha1', $payload, $secret, false);

    // Verifica se a assinatura é válida
    if (hash_equals($signature, $githubSignature)) {
        // Executa o comando para atualizar o repositório
        $output = shell_exec('cd /home/primeiro/public_html && git pull 2>&1');
        echo "Repositório atualizado com sucesso\n";
        echo "Saída do comando:\n$output";
    } else {
        // Assinatura inválida
        header('HTTP/1.0 403 Forbidden');
        echo "Assinatura inválida";
    }
} else {
    // Cabeçalho não encontrado
    header('HTTP/1.0 400 Bad Request');
    echo "Cabeçalho $header não está definido.";
}

?>
