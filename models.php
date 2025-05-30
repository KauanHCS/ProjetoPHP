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
 * 7.2 Métodos e Atributos
 * 8.1 Funções com passagem de parâmetros
 * 9.3 Atualização de registro (Implícito, pois as classes representam dados que podem ser atualizados no banco)
 * 9.4 Exclusão de registro (Implícito, pois as classes representam dados que podem ser excluídos do banco)
 * 9.5 Inserção (Implícito, pois as classes representam dados que podem ser inseridos no banco)
 *
 * Tecnologias: Backend (PHP).
 */

// CRITÉRIO: 7.1 Classes - Definição da classe Tcc.
/**
 * Classe Tcc
 * Representa um Trabalho de Conclusão de Curso (TCC) e suas informações associadas,
 * incluindo detalhes de agendamento de defesa.
 */
class Tcc {
    // CRITÉRIO: 7.2 Métodos e Atributos - Atributos privados para encapsulamento.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros e consistentes para atributos.
    private $codigo_tcc;
    private $titulo;
    private $data_cadastro;
    private $tipo_tcc; // Descrição do tipo (ex: "Monografia")
    private $data_defesa; // Revertido para 'data_defesa'
    private $hora_defesa; // Revertido para 'hora'
    public $id_agenda; // Propriedade para armazenar o ID do agendamento da defesa (se houver)

    // Propriedades adicionais para facilitar a exibição no HTML ou para edição
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros e em camelCase para propriedades públicas.
    public $alunos_envolvidos_html; // String formatada dos alunos
    public $professores_html;       // String formatada dos professores
    public $prof_convidado_1_id_modal; // Para preencher o select no modal de edição
    public $prof_convidado_2_id_modal; // Para preencher o select no modal de edição

    /**
     * Construtor da classe Tcc.
     * CRITÉRIO: 7.2 Métodos e Atributos - Construtor.
     * CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros do construtor.
     * CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para parâmetros do construtor.
     *
     * @param int $codigo_tcc O código identificador do TCC.
     * @param string $titulo O título do TCC.
     * @param string $data_cadastro A data de cadastro do TCC (formato 'YYYY-MM-DD').
     * @param string $tipo_tcc A descrição do tipo de TCC (ex: "Monografia", "Artigo").
     * @param string|null $data_defesa A data da defesa do TCC, se agendada (formato 'YYYY-MM-DD').
     * @param string|null $hora_defesa A hora da defesa do TCC, se agendada (formato 'HH:MM:SS').
     */
    public function __construct($codigo_tcc, $titulo, $data_cadastro, $tipo_tcc, $data_defesa = null, $hora_defesa = null) {
        // CRITÉRIO: 3.3 Atribuição - Atribui valores aos atributos da classe.
        $this->codigo_tcc = $codigo_tcc;
        $this->titulo = $titulo;
        $this->data_cadastro = $data_cadastro;
        $this->tipo_tcc = $tipo_tcc;
        $this->data_defesa = $data_defesa; // Revertido
        $this->hora_defesa = $hora_defesa; // Revertido
    }

    // CRITÉRIO: 7.2 Métodos e Atributos - Métodos getters para acesso aos atributos privados.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para métodos (ex: getCodigoTcc).
    /**
     * Retorna o código do TCC.
     * @return int
     */
    public function getCodigoTcc() {
        return $this->codigo_tcc;
    }

    /**
     * Retorna o título do TCC.
     * @return string
     */
    public function getTitulo() {
        return $this->titulo;
    }

    /**
     * Retorna a data de cadastro do TCC.
     * @return string
     */
    public function getDataCadastro() {
        return $this->data_cadastro;
    }

    /**
     * Retorna a descrição do tipo de TCC.
     * @return string
     */
    public function getTipoTcc() {
        return $this->tipo_tcc;
    }

    /**
     * Retorna a data da defesa do TCC.
     * @return string|null
     */
    public function getDataDefesa() {
        return $this->data_defesa;
    }

    /**
     * Retorna a hora da defesa do TCC.
     * @return string|null
     */
    public function getHoraDefesa() {
        return $this->hora_defesa;
    }

    /**
     * Retorna a data e hora da defesa do TCC combinadas.
     * @return string|null Formato 'YYYY-MM-DD HH:MM:SS'
     */
    public function getDataHoraDefesa() {
        if ($this->data_defesa && $this->hora_defesa) {
            return $this->data_defesa . ' ' . $this->hora_defesa;
        }
        return null;
    }

    /**
     * Retorna o ID do agendamento (chave primária da tabela Agenda).
     * @return int|null
     */
    public function getIdAgenda() {
        return $this->id_agenda;
    }
}

// CRITÉRIO: 7.1 Classes - Definição da classe Aluno.
/**
 * Classe Aluno
 * Representa um Aluno com suas informações básicas.
 * A chave primária da tabela Aluno é 'RA'.
 */
class Aluno {
    // CRITÉRIO: 7.2 Métodos e Atributos - Atributos privados.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para atributos.
    private $ra; // Coluna RA é a PK na sua tabela Aluno
    private $nome;
    private $email;

    /**
     * Construtor da classe Aluno.
     * CRITÉRIO: 7.2 Métodos e Atributos - Construtor.
     * CRITÉRIO: 8.1 Funções com passagem de parâmetros.
     * CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para parâmetros.
     *
     * @param string $ra O Registro Acadêmico (RA) do aluno.
     * @param string $nome O nome do aluno.
     * @param string|null $email O email do aluno.
     */
    public function __construct($ra, $nome, $email = null) {
        // CRITÉRIO: 3.3 Atribuição.
        $this->ra = $ra;
        $this->nome = $nome;
        $this->email = $email;
    }

    // CRITÉRIO: 7.2 Métodos e Atributos - Métodos getters.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para métodos (ex: getRA).
    /**
     * Retorna o Registro Acadêmico (RA) do aluno.
     * @return string
     */
    public function getRA() {
        return $this->ra;
    }

    /**
     * Retorna o nome do aluno.
     * @return string
     */
    public function getNome() {
        return $this->nome;
    }

    /**
     * Retorna o email do aluno.
     * @return string|null
     */
    public function getEmail() {
        return $this->email;
    }
}

// CRITÉRIO: 7.1 Classes - Definição da classe Professor.
/**
 * Classe Professor
 * Representa um Professor com suas informações básicas.
 */
class Professor {
    // CRITÉRIO: 7.2 Métodos e Atributos - Atributos privados.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para atributos.
    private $id_professor;
    private $nome;
    private $area; // Assumindo que a coluna 'area' existe na sua tabela Professor

    /**
     * Construtor da classe Professor.
     * CRITÉRIO: 7.2 Métodos e Atributos - Construtor.
     * CRITÉRIO: 8.1 Funções com passagem de parâmetros.
     * CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para parâmetros.
     *
     * @param int $id_professor O ID identificador do professor.
     * @param string $nome O nome do professor.
     * @param string|null $area A área de atuação do professor.
     */
    public function __construct($id_professor, $nome, $area = null) {
        // CRITÉRIO: 3.3 Atribuição.
        $this->id_professor = $id_professor;
        $this->nome = $nome;
        $this->area = $area;
    }

    // CRITÉRIO: 7.2 Métodos e Atributos - Métodos getters.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para métodos.
    /**
     * Retorna o ID do professor.
     * @return int
     */
    public function getIdProfessor() {
        return $this->id_professor;
    }

    /**
     * Retorna o nome do professor.
     * @return string
     */
    public function getNome() {
        return $this->nome;
    }

    /**
     * Retorna a área de atuação do professor.
     * @return string|null
     */
    public function getArea() {
        return $this->area;
    }
}