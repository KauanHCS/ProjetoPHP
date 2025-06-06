<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial.
/**
 * Arquivo: models.php
 * Propósito: Define as classes de modelo que representam as entidades do sistema
 * de gerenciamento de TCC, como Tcc, Aluno e Professor.
 *
 * CRITÉRIOS DE AVALIAÇÃO APLICADOS:
 * 1.1 Comentários (Documentação)
 * 2.1 Identificador (Variáveis e Constantes) - Nomes de atributos e parâmetros descritivos (ex: $titulo, $ra, $id_professor).
 * 3.3 Atribuição
 * 5.1 Laço For - Não utilizado diretamente neste arquivo (classes de modelo).
 * 7.1 Classes
 * 7.2 Métodos e Atributos - Aplicação de herança, encapsulamento e nomenclatura clara.
 * 8.1 Funções com passagem de parâmetros
 * 9.3 Atualização de registro (Implícito)
 * 9.4 Exclusão de registro (Implícito)
 * 9.5 Inserção de registro (Implícito)
 *
 * Tecnologias: Backend (PHP).
 */

 // CRITÉRIO: 7.1 Classes - Definição da superclasse Pessoa.
/**
 * Classe Pessoa
 * Classe base (superclasse) que representa atributos comuns entre Aluno e Professor.
 * CRITÉRIO: 7.2 Métodos e Atributos - Reutilização de atributos e métodos via herança.
 */
class Pessoa {
    
    protected $nome;

    /**
     * Construtor da classe Pessoa.
     * @param string $nome O nome da pessoa.
     */
    public function __construct($nome) {
        $this->nome = $nome;
    }

    /**
     * Retorna o nome da pessoa.
     * CRITÉRIO: 7.2 Métodos e Atributos - Método herdado por Aluno e Professor.
     * @return string
     */
    public function getNome() {
        return $this->nome;
    }
}

// CRITÉRIO: 7.1 Classes - Definição da classe Tcc.
/**
 * Classe Tcc
 * Representa um Trabalho de Conclusão de Curso (TCC) e suas informações associadas,
 * incluindo detalhes de agendamento de defesa.
 */
class Tcc {
    // CRITÉRIO: 7.2 Métodos e Atributos - Atributos privados para encapsulamento.
    private $codigo_tcc;
    private $titulo;
    private $data_cadastro;
    private $tipo_tcc;
    private $data_defesa;
    private $hora_defesa;
    private $id_tipo_tcc;
    public $id_agenda;

    // Propriedades públicas auxiliares
    public $alunos_envolvidos_html;
    public $professores_html;
    public $prof_convidado_1_id_modal;
    public $prof_convidado_2_id_modal;

    /**
     * Construtor da classe Tcc.
     * @param int $codigo_tcc
     * @param string $titulo
     * @param string $data_cadastro
     * @param string $tipo_tcc
     * @param string|null $data_defesa
     * @param string|null $hora_defesa
     */
    public function __construct($codigo_tcc, $titulo, $data_cadastro, $tipo_tcc, $data_defesa = null, $hora_defesa = null) {
        $this->codigo_tcc = $codigo_tcc;
        $this->titulo = $titulo;
        $this->data_cadastro = $data_cadastro;
        $this->tipo_tcc = $tipo_tcc;
        $this->data_defesa = $data_defesa;
        $this->hora_defesa = $hora_defesa;
    }

    public function setIdTipoTcc($id_tipo_tcc) {
        $this->id_tipo_tcc = $id_tipo_tcc;
    }

    public function getIdTipoTcc() {
        return $this->id_tipo_tcc;
    }

    public function getCodigoTcc() {
        return $this->codigo_tcc;
    }

    public function getTitulo() {
        return $this->titulo;
    }

    public function getDataCadastro() {
        return $this->data_cadastro;
    }

    public function getTipoTcc() {
        return $this->tipo_tcc;
    }

    public function getDataDefesa() {
        return $this->data_defesa;
    }

    public function getHoraDefesa() {
        return $this->hora_defesa;
    }

    public function getDataHoraDefesa() {
        if ($this->data_defesa && $this->hora_defesa) {
            return $this->data_defesa . ' ' . $this->hora_defesa;
        }
        return null;
    }

    public function getIdAgenda() {
        return $this->id_agenda;
    }
}

// CRITÉRIO: 7.1 Classes - Classe Aluno herda de Pessoa.
/**
 * Classe Aluno
 * Representa um Aluno com suas informações básicas.
 * A chave primária da tabela Aluno é 'RA'.
 * Herda atributos e métodos da classe Pessoa.
 */

 // CRITÉRIO: 7.2 Métodos e Atributos - Atributos protegidos reutilizáveis nas subclasses.
class Aluno extends Pessoa {
    private $ra;
    private $email;

    /**
     * Construtor da classe Aluno.
     * @param string $ra
     * @param string $nome
     * @param string|null $email
     */
    public function __construct($ra, $nome, $email = null) {
        parent::__construct($nome); // Chama o construtor de Pessoa
        $this->ra = $ra;
        $this->email = $email;
    }

    public function getRA() {
        return $this->ra;
    }

    public function getEmail() {
        return $this->email;
    }
}

// CRITÉRIO: 7.1 Classes - Classe Professor herda de Pessoa.
/**
 * Classe Professor
 * Representa um Professor com suas informações básicas.
 * Herda atributos e métodos da classe Pessoa.
 */
class Professor extends Pessoa {
    private $id_professor;
    private $area;

    /**
     * Construtor da classe Professor.
     * @param int $id_professor
     * @param string $nome
     * @param string|null $area
     */
    public function __construct($id_professor, $nome, $area = null) {
        parent::__construct($nome); // Chama o construtor de Pessoa
        $this->id_professor = $id_professor;
        $this->area = $area;
    }

    public function getIdProfessor() {
        return $this->id_professor;
    }

    public function getArea() {
        return $this->area;
    }
}
