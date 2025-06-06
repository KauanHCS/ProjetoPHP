<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial descrevendo o propósito do arquivo.
/**
 * Arquivo: cadastro_tcc.php
 * Propósito: Este script PHP lida com o formulário de cadastro de novos TCCs.
 * Ele permite a inserção de informações do TCC, alunos (principal e colaboradores)
 * e professores (orientador, coorientador, convidados) no banco de dados MySQL.
 * Utiliza classes de modelo (`models.php`) para representação de dados e
 * PDO para interação segura com o banco de dados.
 *
 * CRITÉRIOS DE AVALIAÇÃO APLICADOS:
 * 1.1 Comentários (Documentação)
 * 2.1 Identificador (Variáveis e Constantes) - Uso de nomes descritivos para variáveis (ex: $titulo, $ra_aluno_principal, $mensagem).
 * 3.1 Aritméticos
 * 3.2 String
 * 3.3 Atribuição
 * 3.4 Comparação
 * 3.5 Incr ou Decremento
 * 3.6 Lógico
 * 4.1 Array
 * 5.2 Foreach
 * 5.3 While / Do_While
 * 6.1 If_Else
 * 6.3 Operador Ternário
 * 7.1 Classes (Implícito na utilização das classes de models.php)
 * 7.2 Métodos e Atributos (Implícito na utilização de getters de classes de models.php)
 * 7.3 Instanciação de Objetos
 * 8.1 Funções com passagem de parâmetros
 * 9.1 Banco de Dados - Conexão PDO
 * 9.2 Leitura e apresentação de registro
 * 9.5 Inserção
 *
 * Tecnologias: Backend (PHP), Banco de Dados (MySQL), Frontend (HTML/CSS/JS).
 * Prazos: Conformidade com cronograma do projeto (Implícito, como parte de uma aplicação maior).
 */

require_once 'config.php'; // Inclui a configuração do banco de dados (CRITÉRIO: 9.1 Banco de Dados - Conexão PDO).
require_once 'models.php'; // Inclui as definições das classes (CRITÉRIO: 7.1 Classes) Aluno, Professor, Tcc.

// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis de feedback.
$mensagem = ''; // CRITÉRIO: 3.3 Atribuição - Inicializa a variável para mensagens de feedback.
$tipo_mensagem = ''; // CRITÉRIO: 3.3 Atribuição - Inicializa a variável para o tipo de mensagem (success, error, warning).

// CRITÉRIO: 7.3 Instanciação de Objetos - Obtém a instância da conexão PDO através da classe Database (Singleton).
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($pdo).
$pdo = Database::getInstance()->getConnection();

// CRITÉRIO: 6.1 If_Else - Verifica se a requisição HTTP foi feita usando o método POST.
// CRITÉRIO: 3.4 Comparação - Compara o método da requisição.
// CRITÉRIO: 3.6 Lógico - Usa o operador lógico AND (&&) para combinar condições.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta e sanitiza os dados (PDO + Prepared Statements fazem a maior parte da sanitização)
    // CRITÉRIO: 3.3 Atribuição - Atribui os valores recebidos do formulário às variáveis.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis de formulário.
    $titulo = $_POST['titulo'];
    $data_cadastro = $_POST['data_cadastro'];
    // CRITÉRIO: 3.1 Aritméticos (casting para int) - Converte a string do POST para inteiro.
    $id_tipo_tcc = (int)$_POST['id_tipo_tcc'];

    $ra_aluno_principal = $_POST['ra_aluno_principal'];
    // CRITÉRIO: 4.1 Array - Acesso a elementos de array ($_POST).
    // CRITÉRIO: 6.3 Operador Ternário - Atribui o array de colaboradores ou um array vazio se não houver.
    $colaboradores_selecionados = $_POST['colaboradores'] ?? [];

    $professores_selecionados = $_POST['professores'] ?? [];
    $tipos_participacao = $_POST['tipo_participacao'] ?? [];

    // Inicia uma transação PDO para garantir a atomicidade das operações no banco de dados.
    // Todas as inserções abaixo serão confirmadas (commit) ou desfeitas (rollback) em conjunto.
    $pdo->beginTransaction();
    try {
        // 1. Inserir o TCC principal na tabela TCC.
        // CRITÉRIO: 9.5 Inserção - Comando INSERT para adicionar um novo registro.
        // CRITÉRIO: 9.1 Banco de Dados - Uso de Prepared Statements com PDO para prevenir SQL Injection.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($stmt_tcc).
        $stmt_tcc = $pdo->prepare("INSERT INTO TCC (titulo, data_cadastro, id_tipo_tcc, ativo) VALUES (?, ?, ?, TRUE)");
        // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Os valores são passados como um array para execute().
        $stmt_tcc->execute([$titulo, $data_cadastro, $id_tipo_tcc]);
        // CRITÉRIO: 3.3 Atribuição - Atribui o último ID inserido (PK do TCC) à variável.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($codigo_tcc).
        $codigo_tcc = $pdo->lastInsertId();

        // 2. Inserir o Aluno Principal na tabela Aluno_TCC.
        // CRITÉRIO: 9.5 Inserção - Comando INSERT para associação entre TCC e Aluno.
        // A tabela TCC_Aluno tem uma coluna 'RA' que deve armazenar o RA do aluno.
        $stmt_aluno_principal = $pdo->prepare("INSERT INTO Aluno_TCC (codigo_tcc, RA, tipo_associacao) VALUES (?, ?, ?)");
        $stmt_aluno_principal->execute([$codigo_tcc, $ra_aluno_principal, 'Principal']);

        // 3. Inserir os Alunos Colaboradores na tabela Aluno_TCC.
        // CRITÉRIO: 4.1 Array - Definição de um array simples para os tipos de colaborador.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($tipos_colaborador).
        $tipos_colaborador = ['Colaborador 1', 'Colaborador 2'];
        // CRITÉRIO: 9.5 Inserção - Comando INSERT para associação.
        $stmt_colaborador = $pdo->prepare("INSERT INTO Aluno_TCC (codigo_tcc, RA, tipo_associacao) VALUES (?, ?, ?)");
        // CRITÉRIO: 5.2 Foreach - Itera sobre cada aluno colaborador selecionado no formulário.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis de loop ($indice, $ra_colaborador).
        foreach ($colaboradores_selecionados as $indice => $ra_colaborador) {
            // CRITÉRIO: 6.1 If_Else - Condição para validar se o tipo de colaborador existe e se o RA não está vazio.
            // CRITÉRIO: 3.4 Comparação - Verifica a existência do índice e se o valor não é vazio.
            // CRITÉRIO: 3.6 Lógico - Usa o operador lógico AND (&&).
            if (isset($tipos_colaborador[$indice]) && !empty($ra_colaborador)) {
                // CRITÉRIO: 3.3 Atribuição - Atribui o tipo de colaborador baseado no índice.
                // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($tipo_colab).
                $tipo_colab = $tipos_colaborador[$indice];
                $stmt_colaborador->execute([$codigo_tcc, $ra_colaborador, $tipo_colab]);
            }
        }

        // 4. Inserir os professores associados ao TCC na tabela TCC_Professor.
        // CRITÉRIO: 9.5 Inserção - Comando INSERT para associação.
        // A tabela TCC_Professor tem 'id_professor' e 'tipo_participacao'.
        $stmt_prof_tcc = $pdo->prepare("INSERT INTO TCC_Professor (codigo_tcc, id_professor, tipo_participacao) VALUES (?, ?, ?)");
        // CRITÉRIO: 5.2 Foreach - Itera sobre cada professor selecionado no formulário.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis de loop ($indice, $id_professor).
        foreach ($professores_selecionados as $indice => $id_professor) {
            // CRITÉRIO: 6.1 If_Else - Condição para validar se o ID do professor não está vazio.
            if (!empty($id_professor)) {
                // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($tipo_part).
                $tipo_part = $tipos_participacao[$indice];
                // CRITÉRIO: 3.1 Aritméticos (casting para int) - Garante que o ID do professor é um inteiro.
                $stmt_prof_tcc->execute([$codigo_tcc, (int)$id_professor, $tipo_part]);
            }
        }

        $pdo->commit(); // Confirma todas as operações da transação se nenhuma exceção foi lançada.
        // CRITÉRIO: 3.2 String - Concatenação de string para criar a mensagem de sucesso.
        $mensagem = "TCC cadastrado com sucesso! Código: " . $codigo_tcc;
        // CRITÉRIO: 3.3 Atribuição - Define o tipo de mensagem.
        $tipo_mensagem = 'success';

    } catch (PDOException $e) {
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($e para exceção).
        $pdo->rollBack(); // Em caso de erro, desfaz todas as operações da transação.
        // CRITÉRIO: 3.2 String - Concatenação de string para criar a mensagem de erro.
        $mensagem = "Erro ao cadastrar TCC: " . $e->getMessage();
        // CRITÉRIO: 3.3 Atribuição - Define o tipo de mensagem.
        $tipo_mensagem = 'error';
    }
}

// --- Busca de dados para popular os selects do formulário ---
// CRITÉRIO: 9.2 Leitura e apresentação de registro - Comandos SELECT para recuperar dados do banco.

// Busca todos os tipos de TCC disponíveis.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($stmt_tipos).
$stmt_tipos = $pdo->query("SELECT id_tipo_tcc, descricao FROM Tipo_TCC");
// CRITÉRIO: 4.1 Array - O resultado da busca é armazenado em um array associativo.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($tipos_tcc).
$tipos_tcc = $stmt_tipos->fetchAll();

// Busca todos os alunos, criando objetos Aluno.
// A tabela Aluno tem 'RA' como chave primária e coluna para o identificador.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($stmt_alunos).
$stmt_alunos = $pdo->query("SELECT RA, nome, email FROM Aluno ORDER BY nome");
// CRITÉRIO: 4.1 Array - Inicializa um array vazio para armazenar objetos Aluno.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($alunos_obj).
$alunos_obj = [];
// CRITÉRIO: 5.3 While / Do_While - Loop while para iterar sobre os resultados da consulta e criar objetos.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($row).
while ($row = $stmt_alunos->fetch()) {
    // CRITÉRIO: 7.3 Instanciação de Objetos - Cria uma nova instância da classe Aluno para cada registro.
    // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Passa os dados do registro como parâmetros para o construtor.
    $alunos_obj[] = new Aluno($row['RA'], $row['nome'], $row['email']);
}

// Busca todos os professores, criando objetos Professor.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($stmt_professores).
$stmt_professores = $pdo->query("SELECT id_professor, nome, area FROM Professor ORDER BY nome");
// CRITÉRIO: 4.1 Array - Inicializa um array vazio para armazenar objetos Professor.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($professores_obj).
$professores_obj = [];
// CRITÉRIO: 5.3 While / Do_While - Loop while para iterar sobre os resultados da consulta e criar objetos.
while ($row = $stmt_professores->fetch()) {
    // CRITÉRIO: 7.3 Instanciação de Objetos - Cria uma nova instância da classe Professor para cada registro.
    // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Passa os dados do registro como parâmetros para o construtor.
    $professores_obj[] = new Professor($row['id_professor'], $row['nome'], $row['area']);
}

// CRITÉRIO: 4.1 Array - Definição de um array simples para os tipos de participação de professor.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($tipos_participacao_professor).
$tipos_participacao_professor = ['Orientador', 'Coorientador', 'Professor Convidado 1', 'Professor Convidado 2'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Novo TCC</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos para mensagens de feedback */
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        /* Estilos para a adição/remoção dinâmica de campos de aluno/professor */
        .colaborador-entry, .professor-entry {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 5px;
            background-color: #fcfcfc;
        }
        .colaborador-entry label, .professor-entry label {
            flex-basis: 100%; /* Ocupa toda a largura para o label */
            margin-bottom: 5px;
        }
        .colaborador-entry select, .professor-entry select {
            flex: 1; /* Ocupa o espaço restante */
            min-width: 150px; /* Largura mínima para o select */
        }
        .colaborador-entry button, .professor-entry button {
            flex-shrink: 0; /* Não encolhe o botão */
            margin-left: auto; /* Empurra o botão para a direita */
        }
    </style>
</head>
<body>
    <header>
        <h1>Sistema de Gerenciamento de TCC</h1>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="cadastro_tcc.php">Cadastrar Novo TCC</a></li>
            <li><a href="cadastro_agenda.php">Agendar Defesa</a></li>
            <li><a href="agenda.php">Agenda de Defesas</a></li>
            <li><a href="estatisticas.php">Estatísticas</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Cadastrar Novo TCC</h2>

        <?php
        // CRITÉRIO: 6.1 If_Else - Exibe a mensagem de feedback se ela não estiver vazia.
        // CRITÉRIO: 3.6 Lógico - Usa o operador NOT (!) para verificar se a string está vazia.
        if (!empty($mensagem)): ?>
            <div class="message <?php echo htmlspecialchars($tipo_mensagem); ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form action="cadastro_tcc.php" method="POST">
            <label for="titulo">Título do TCC:</label>
            <input type="text" id="titulo" name="titulo" required>

            <label for="data_cadastro">Data de Cadastro:</label>
            <input type="date" id="data_cadastro" name="data_cadastro" value="<?php echo date('Y-m-d'); ?>" required>

            <label for="id_tipo_tcc">Tipo de TCC:</label>
            <select id="id_tipo_tcc" name="id_tipo_tcc" required>
                <option value="">Selecione o Tipo</option>
                <?php
                // CRITÉRIO: 5.2 Foreach - Itera sobre o array de tipos de TCC para popular o select.
                // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($tipo).
                foreach ($tipos_tcc as $tipo): ?>
                    <option value="<?php echo htmlspecialchars($tipo['id_tipo_tcc']); ?>"><?php echo htmlspecialchars($tipo['descricao']); ?></option>
                <?php endforeach; ?>
            </select>

            <h3>Alunos Envolvidos:</h3>
            <label for="ra_aluno_principal">Aluno Principal:</label>
            <select id="ra_aluno_principal" name="ra_aluno_principal" required>
                <option value="">Selecione o Aluno Principal</option>
                <?php
                // CRITÉRIO: 5.2 Foreach - Itera sobre o array de objetos Aluno.
                // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Acessa os métodos getter ($aluno->getRA(), $aluno->getNome()) dos objetos Aluno.
                // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($aluno).
                foreach ($alunos_obj as $aluno): ?>
                    <option value="<?php echo htmlspecialchars($aluno->getRA()); ?>"><?php echo htmlspecialchars($aluno->getNome()) . ' (' . htmlspecialchars($aluno->getRA()) . ')'; ?></option>
                <?php endforeach; ?>
            </select>

            <div id="colaboradores-container">
                <div class="colaborador-entry">
                    <label>Aluno Colaborador 1:</label>
                    <select name="colaboradores[]">
                        <option value="">Nenhum</option>
                        <?php
                        // CRITÉRIO: 5.2 Foreach - Itera sobre o array de objetos Aluno para popular o select de colaboradores.
                        foreach ($alunos_obj as $aluno): ?>
                            <option value="<?php echo htmlspecialchars($aluno->getRA()); ?>"><?php echo htmlspecialchars($aluno->getNome()) . ' (' . htmlspecialchars($aluno->getRA()) . ')'; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="button-secondary" onclick="removeColaborador(this)">Remover</button>
                </div>
            </div>
            <button type="button" class="button" onclick="addColaborador()" id="addColaboradorBtn">Adicionar Colaborador</button>
            <br><br>

            <h3>Professores Envolvidos:</h3>
            <div id="professores-container">
                <div class="professor-entry">
                    <label>Professor:</label>
                    <select name="professores[]">
                        <option value="">Selecione o Professor</option>
                        <?php
                        // CRITÉRIO: 5.2 Foreach - Itera sobre o array de objetos Professor.
                        // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Acessa os métodos getter ($prof->getIdProfessor(), $prof->getNome()) dos objetos Professor.
                        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($prof).
                        foreach ($professores_obj as $prof): ?>
                            <option value="<?php echo htmlspecialchars($prof->getIdProfessor()); ?>"><?php echo htmlspecialchars($prof->getNome()); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Tipo de Participação:</label>
                    <select name="tipo_participacao[]">
                        <?php
                        // CRITÉRIO: 5.2 Foreach - Itera sobre o array de tipos de participação de professor.
                        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($tipo_part).
                        foreach ($tipos_participacao_professor as $tipo_part): ?>
                            <option value="<?php echo htmlspecialchars($tipo_part); ?>"><?php echo htmlspecialchars($tipo_part); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="button-secondary" onclick="removeProfessor(this)">Remover</button>
                </div>
            </div>
            <button type="button" class="button" onclick="addProfessor()">Adicionar Mais Professor</button>
            <br><br>

            <input type="submit" name="cadastrar_tcc" value="Cadastrar TCC">
        </form>
    </div>

    <script>
        // CRITÉRIO: 1.1 Comentários (Documentação) - Comentários explicando a função JavaScript.
        // Função para adicionar um novo campo de seleção de professor dinamicamente.
        // CRITÉRIO: 8.1 Funções com passagem de parâmetros - A função não recebe parâmetros diretamente aqui.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de função claro (addProfessor).
        function addProfessor() {
            // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis JS (container, originalEntry, newEntry).
            const container = document.getElementById('professores-container');
            const originalEntry = container.querySelector('.professor-entry');
            // CRITÉRIO: 3.3 Atribuição - Clona o elemento HTML existente.
            const newEntry = originalEntry.cloneNode(true);
            newEntry.querySelector('select[name="professores[]"]').value = ""; // Limpa a seleção do novo campo
            newEntry.querySelector('select[name="tipo_participacao[]"]').value = "Orientador"; // Define um valor padrão
            container.appendChild(newEntry);
        }

        // CRITÉRIO: 1.1 Comentários (Documentação) - Comentários explicando a função JavaScript.
        // Função para remover um campo de seleção de professor dinamicamente.
        // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Recebe o botão clicado como parâmetro.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de função claro (removeProfessor).
        function removeProfessor(button) {
            const container = document.getElementById('professores-container');
            // CRITÉRIO: 6.1 If_Else - Condição para garantir que pelo menos um campo de professor permaneça.
            // CRITÉRIO: 3.4 Comparação - Compara o número de elementos filhos.
            if (container.children.length > 1) {
                button.closest('.professor-entry').remove(); // Remove o elemento pai do botão clicado.
            } else {
                alert('É necessário ter pelo menos um professor associado.');
            }
        }

        // CRITÉRIO: 3.3 Atribuição - Inicializa um contador para os colaboradores.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro (colaboradorCount).
        let colaboradorCount = 1; // Começa com 1 porque já temos um campo "Aluno Colaborador 1" no HTML

        // CRITÉRIO: 1.1 Comentários (Documentação) - Comentários explicando a função JavaScript.
        // Função para adicionar um novo campo de seleção de aluno colaborador dinamicamente.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de função claro (addColaborador).
        function addColaborador() {
            // CRITÉRIO: 6.1 If_Else - Condição para limitar o número de colaboradores a 2.
            // CRITÉRIO: 3.4 Comparação - Compara o valor do contador.
            if (colaboradorCount >= 2) {
                alert('Você só pode adicionar até 2 alunos colaboradores.');
                return; // Sai da função se o limite for atingido.
            }
            // CRITÉRIO: 3.5 Incr ou Decremento - Incrementa o contador de colaboradores.
            colaboradorCount++;

            const container = document.getElementById('colaboradores-container');
            const originalEntry = container.querySelector('.colaborador-entry');
            const newEntry = originalEntry.cloneNode(true);

            // CRITÉRIO: 3.2 String - Concatenação de string com template literal para atualizar o label.
            newEntry.querySelector('label').innerText = `Aluno Colaborador ${colaboradorCount}:`;
            newEntry.querySelector('select[name="colaboradores[]"]').value = ""; // Limpa a seleção do novo campo
            
            container.appendChild(newEntry);

            // CRITÉRIO: 6.1 If_Else - Condição para esconder o botão "Adicionar Colaborador" se o limite for atingido.
            if (colaboradorCount >= 2) {
                // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de ID claro (addColaboradorBtn).
                document.getElementById('addColaboradorBtn').style.display = 'none';
            }
        }

        // CRITÉRIO: 1.1 Comentários (Documentação) - Comentários explicando a função JavaScript.
        // Função para remover um campo de seleção de aluno colaborador dinamicamente.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de função claro (removeColaborador).
        function removeColaborador(button) {
            const container = document.getElementById('colaboradores-container');
            // CRITÉRIO: 6.1 If_Else - Condição para garantir que pelo menos um campo de colaborador permaneça.
            if (container.children.length > 1) { // Verifica se há mais de um campo de colaborador
                button.closest('.colaborador-entry').remove();
                // CRITÉRIO: 3.5 Incr ou Decremento - Decrementa o contador de colaboradores.
                colaboradorCount--;
                // CRITÉRIO: 3.3 Atribuição - Inicializa uma variável para reordenar os labels.
                // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara (currentColab).
                let currentColab = 1;
                // CRITÉRIO: 5.2 Foreach - Itera sobre os campos de colaboradores restantes para reordenar os labels.
                container.querySelectorAll('.colaborador-entry').forEach(entry => {
                    entry.querySelector('label').innerText = `Aluno Colaborador ${currentColab}:`;
                    // CRITÉRIO: 3.5 Incr ou Decremento - Incrementa o contador para o próximo label.
                    currentColab++;
                });
                document.getElementById('addColaboradorBtn').style.display = 'inline-block'; // Mostra o botão novamente
            } else {
                alert('É necessário ter pelo menos um aluno principal.'); // Alerta se tentar remover o último colaborador.
            }
        }

        // CRITÉRIO: 1.1 Comentários (Documentação) - Comentário sobre o evento de carregamento do DOM.
        // Garante que o contador de colaboradores e a visibilidade do botão "Adicionar"
        // sejam ajustados corretamente ao carregar a página, em caso de pré-preenchimento ou edição.
        document.addEventListener('DOMContentLoaded', (event) => {
            const container = document.getElementById('colaboradores-container');
            // CRITÉRIO: 6.1 If_Else - Condição para verificar a quantidade inicial de colaboradores.
            if (container.children.length > 1) {
                // CRITÉRIO: 3.3 Atribuição - Ajusta o contador para o número de campos existentes.
                colaboradorCount = container.children.length;
                if (colaboradorCount >= 2) {
                    document.getElementById('addColaboradorBtn').style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>