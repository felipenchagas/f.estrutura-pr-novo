// Teste de conexão ao primeiro banco
if ($conexao1->connect_error) {
    die("Conexão ao banco 1 falhou: " . $conexao1->connect_error);
} else {
    echo "Conexão ao banco 1 bem-sucedida!";
}

// Teste de conexão ao segundo banco
if ($conexao2->connect_error) {
    die("Conexão ao banco 2 falhou: " . $conexao2->connect_error);
} else {
    echo "Conexão ao banco 2 bem-sucedida!";
}
