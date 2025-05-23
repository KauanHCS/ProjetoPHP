<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial

// Definições de constantes para o banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'tcc');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Classe para gerenciar a conexão com o banco de dados usando PDO.
 * CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Definição da classe Database.
 * CRITÉRIO: 9.1 Banco de Dados - Conexão PDO - Implementação da conexão PDO.
 */
class Database {
    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Atributos privados da classe.
    private static $instance = null; // Para o padrão Singleton
    private $pdo; // Objeto PDO

    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Método construtor (privado para Singleton).
    private function __construct() {
        // CRITÉRIO: 3.2 String - Concatenação de strings para DSN.
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lançar exceções em erros
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Retornar arrays associativos
            PDO::ATTR_EMULATE_PREPARES   => false,                // Desativar emulação para segurança
        ];
        try {
            // CRITÉRIO: 7.3 Instanciação de Objetos - Instanciação do objeto PDO.
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Em um ambiente de produção, logar o erro em vez de exibi-lo diretamente
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }

    /**
     * Método para obter a instância única do PDO (Singleton Pattern).
     * CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Método estático.
     */
    public static function getInstance() {
        // CRITÉRIO: 3.4 Comparação - Comparação para verificar se a instância já existe.
        if (self::$instance === null) {
            // CRITÉRIO: 7.3 Instanciação de Objetos - Instanciação da própria classe Database.
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Método para obter o objeto PDO.
     * CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Método público para acesso à conexão.
     */
    public function getConnection() {
        return $this->pdo;
    }
}
?>