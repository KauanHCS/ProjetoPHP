<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial.

/**
 * Classe base Pessoa, demonstra atributos e métodos.
 * Será a base para Aluno e Professor.
 * CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Definição da classe Pessoa com atributos e métodos.
 */
class Pessoa {
    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Atributos protegidos.
    protected $id; // Pode ser RA ou id_professor
    protected $nome; // CRITÉRIO: 2.1 Uso de "Camel Case" (para variáveis)

    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Método construtor.
    // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros $id e $nome.
    public function __construct($id, $nome) {
        // CRITÉRIO: 3.3 Atribuição - Atribuição de valores aos atributos.
        $this->id = $id;
        $this->nome = $nome;
    }

    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Métodos getters.
    public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    /**
     * Exemplo de método com operador de string.
     * CRITÉRIO: 3.2 String - Concatenação de strings para formar a saudação.
     */
    public function getSaudacao() {
        // CRITÉRIO: 3.2 String - Uso do operador '.' para concatenação.
        return "Olá, eu sou " . $this->nome . ".";
    }
}

/**
 * Classe Aluno, herda de Pessoa.
 * Demonstra Herança e atributos específicos.
 * CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Definição da classe Aluno.
 * CRITÉRIO: 7.2 Uso de Herança - 'extends Pessoa' indica herança.
 */
class Aluno extends Pessoa {
    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Atributos privados específicos da classe Aluno.
    private $ra; // RA do aluno
    private $email; // Atributo específico de Aluno

    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Método construtor.
    // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros específicos.
    public function __construct($ra, $nome, $email = null) {
        // CRITÉRIO: 7.2 Uso de Herança - Chamada ao construtor da classe pai.
        parent::__construct($ra, $nome);
        // CRITÉRIO: 3.3 Atribuição - Atribuição de valores.
        $this->ra = $ra;
        $this->email = $email;
    }

    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Métodos getters específicos.
    public function getRA() {
        return $this->ra;
    }

    public function getEmail() {
        return $this->email;
    }

    /**
     * Sobrescreve o método getSaudacao da classe pai.
     * CRITÉRIO: 7.2 Uso de Herança - Sobrescrita de método.
     * CRITÉRIO: 3.2 String - Concatenação de strings.
     */
    public function getSaudacao() {
        return "Olá, sou o aluno " . $this->nome . " (RA: " . $this->ra . ").";
    }
}

/**
 * Classe Professor, herda de Pessoa.
 * Demonstra Herança e atributos específicos.
 * CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Definição da classe Professor.
 * CRITÉRIO: 7.2 Uso de Herança - 'extends Pessoa' indica herança.
 */
class Professor extends Pessoa {
    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Atributos privados específicos da classe Professor.
    private $idProfessor; // id_professor
    private $area; // Atributo específico de Professor

    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Método construtor.
    // CRITÉRIO: 8.1 Funções com passagem de parâmetros.
    public function __construct($idProfessor, $nome, $area = null) {
        // CRITÉRIO: 7.2 Uso de Herança - Chamada ao construtor da classe pai.
        parent::__construct($idProfessor, $nome);
        // CRITÉRIO: 3.3 Atribuição - Atribuição de valores.
        $this->idProfessor = $idProfessor;
        $this->area = $area;
    }

    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Métodos getters específicos.
    public function getIdProfessor() {
        return $this->idProfessor;
    }

    public function getArea() {
        return $this->area;
    }

    /**
     * Outro exemplo de método com operador de string.
     * CRITÉRIO: 3.2 String - Concatenação de strings.
     */
    public function getDescricaoCompleta() {
        return $this->nome . " - " . $this->area . " (ID: " . $this->idProfessor . ")";
    }
}

/**
 * Classe Tcc, representa um Trabalho de Conclusão de Curso.
 * CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Definição da classe Tcc.
 */
class Tcc {
    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Atributos privados da classe Tcc.
    private $codigoTcc;
    private $titulo;
    private $dataCadastro;
    private $tipoTcc; // Pode ser um objeto Tipo_TCC, ou apenas ID/descrição para simplificar
    private $dataDefesa; // Adicionado para a agenda
    private $horaDefesa; // Adicionado para a agenda

    // Propriedades públicas para dados HTML, não declaradas originalmente como privadas
    // Adicionadas para compatibilidade e para evitar sublinhados do IDE.
    // Embora não seja a melhor prática OO, o PHP permite e já estava sendo usado assim.
    public $alunos_envolvidos_html;
    public $professores_html;

    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Método construtor.
    // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros do construtor.
    public function __construct($codigoTcc, $titulo, $dataCadastro, $tipoTcc, $dataDefesa = null, $horaDefesa = null) {
        // CRITÉRIO: 3.3 Atribuição - Atribuição de valores aos atributos.
        $this->codigoTcc = $codigoTcc;
        $this->titulo = $titulo;
        $this->dataCadastro = $dataCadastro;
        $this->tipoTcc = $tipoTcc;
        $this->dataDefesa = $dataDefesa;
        $this->horaDefesa = $horaDefesa;
    }

    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Métodos getters para acesso aos atributos.
    public function getCodigoTcc() { return $this->codigoTcc; }
    public function getTitulo() { return $this->titulo; }
    public function getDataCadastro() { return $this->dataCadastro; }
    public function getTipoTcc() { return $this->tipoTcc; }
    public function getDataDefesa() { return $this->dataDefesa; } // Getter para nova propriedade
    public function getHoraDefesa() { return $this->horaDefesa; } // Getter para nova propriedade
}
?>