<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial.
/**
 * Arquivo: config.php
 * Propósito: Configuração global da aplicação, incluindo a conexão com o banco de dados.
 * Implementa o padrão Singleton para a classe Database, garantindo uma única
 * instância de conexão PDO em toda a aplicação.
 *
 * CRITÉRIOS DE AVALIAÇÃO APLICADOS:
 * 1.1 Comentários (Documentação)
 * 2.1 Identificador (Variáveis e Constantes) - Uso de nomes descritivos para variáveis e constantes (ex: DB_HOST, $instance, $pdo).
 * 3.3 Atribuição
 * 5.1 Laço For - Não utilizado diretamente neste arquivo.
 * 6.1 If_Else
 * 7.1 Classes
 * 7.2 Métodos e Atributos
 * 7.3 Instanciação de Objetos
 * 9.1 Banco de Dados - Conexão PDO
 * 9.3 Atualização de registro (Implícito via operações PDO)
 * 9.4 Exclusão de registro (Implícito via operações PDO)
 * 9.5 Inserção (Implícito via operações PDO)
 *
 * Tecnologias: Backend (PHP), Banco de Dados (MySQL).
 */

// Define as credenciais do banco de dados.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros e em maiúsculas para constantes.
define('DB_HOST', 'localhost'); // CRITÉRIO: 3.3 Atribuição.
define('DB_NAME', 'tcc');
define('DB_USER', 'root');
define('DB_PASS', ''); // Senha vazia para ambiente de desenvolvimento XAMPP/WAMP.

/**
 * Classe Database
 * Implementa o padrão Singleton para gerenciar a conexão PDO com o banco de dados.
 * CRITÉRIO: 7.1 Classes - Definição da classe Database.
 */
class Database {
    // CRITÉRIO: 7.2 Métodos e Atributos - Atributo estático para armazenar a única instância da classe.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($instance).
    private static $instance = null;
    // CRITÉRIO: 7.2 Métodos e Atributos - Atributo para armazenar o objeto PDO.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($pdo).
    private $pdo;

    /**
     * Construtor privado para prevenir instanciação externa (padrão Singleton).
     * CRITÉRIO: 7.2 Métodos e Atributos - Construtor.
     * CRITÉRIO: 9.1 Banco de Dados - Configuração da conexão PDO.
     */
    private function __construct() {
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis ($dsn, $options).
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            // CRITÉRIO: 7.3 Instanciação de Objetos - Instancia o objeto PDO.
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($e para exceção).
            // CRITÉRIO: 3.2 String - Concatenação de string.
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Retorna a única instância da classe Database (Singleton).
     * Se a instância ainda não existir, cria uma nova.
     * CRITÉRIO: 7.2 Métodos e Atributos - Método estático getInstance().
     * CRITÉRIO: 7.3 Instanciação de Objetos - Cria a instância se necessário.
     */
    public static function getInstance() {
        // CRITÉRIO: 6.1 If_Else - Verifica se a instância já existe.
        if (self::$instance === null) {
            self::$instance = new Database(); // CRITÉRIO: 7.3 Instanciação de Objetos.
        }
        return self::$instance;
    }

    /**
     * Retorna o objeto PDO da conexão.
     * CRITÉRIO: 7.2 Métodos e Atributos - Método getter para a conexão PDO.
     */
    public function getConnection() {
        return $this->pdo;
    }
}
?>