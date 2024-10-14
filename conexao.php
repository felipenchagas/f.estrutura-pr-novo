<?php
session_start();

// **⚠️ IMPORTANTE:** Desative a exibição de erros em produção por segurança
ini_set('display_errors', 1); // Altere para 0 em produção
ini_set('display_startup_errors', 1); // Altere para 0 em produção
error_reporting(E_ALL); // Altere para 0 em produção

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

// Teste de conexão ao primeiro banco
if ($conexao1->connect_error) {
    die("Conexão ao banco 1 falhou: " . $conexao1->connect_error);
} else {
    echo "Conexão ao banco 1 bem-sucedida!<br>";
}

// Teste de conexão ao segundo banco
if ($conexao2->connect_error) {
    die("Conexão ao banco 2 falhou: " . $conexao2->connect_error);
} else {
    echo "Conexão ao banco 2 bem-sucedida!<br>";
}

// A partir daqui você continua com o resto do seu código
