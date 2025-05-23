<?php
// Configurações do Banco de Dados
define('DB_SERVER', 'localhost'); // Geralmente 'localhost'
define('DB_USERNAME', 'root');   // Seu usuário do MySQL
define('DB_PASSWORD', '');       // Sua senha do MySQL (deixe em branco se não tiver)
define('DB_NAME', 'tcc'); // O nome do seu banco de dados

// Conexão com o banco de dados MySQL
$conexao = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verifica a conexão
if ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
}

// Opcional: Define o charset para UTF-8 para evitar problemas com acentuação
$conexao->set_charset("utf8mb4");
?>