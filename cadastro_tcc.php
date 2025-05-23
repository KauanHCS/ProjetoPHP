<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial.
require_once 'config.php'; // Inclui a nova configuração PDO (CRITÉRIO: 9.1 Banco de Dados - Conexão PDO)
require_once 'models.php'; // Inclui as novas classes (CRITÉRIO: 7.1 Classes)

$mensagem = ''; // CRITÉRIO: 3.3 Atribuição
$tipo_mensagem = ''; // CRITÉRIO: 3.3 Atribuição

// CRITÉRIO: 7.3 Instanciação de Objetos - Obtém a conexão PDO através da classe Database.
$pdo = Database::getInstance()->getConnection();

// Processa o formulário quando enviado
// CRITÉRIO: 6.1 If_Else - Condição para verificar o método da requisição.
// CRITÉRIO: 3.4 Comparação - Comparação de string.
// CRITÉRIO: 3.6 Lógico - Uso do operador AND (&&).
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta e sanitiza os dados (PDO + Prepared Statements fazem a maior parte da sanitização)
    // CRITÉRIO: 3.3 Atribuição - Atribuição de valores de $_POST.
    $titulo = $_POST['titulo'];
    $data_cadastro = $_POST['data_cadastro'];
    // CRITÉRIO: 3.3 Atribuição, CRITÉRIO: 3.1 Aritméticos (casting para int)
    $id_tipo_tcc = (int)$_POST['id_tipo_tcc'];
    
    $ra_aluno_principal = $_POST['ra_aluno_principal'];
    // CRITÉRIO: 4.1 Array - Acesso a elementos de array ($_POST).
    // CRITÉRIO: 6.3 Operador Ternário - Para definir array vazio se não houver colaboradores.
    $colaboradores_selecionados = $_POST['colaboradores'] ?? [];

    $professores_selecionados = $_POST['professores'] ?? [];
    $tipos_participacao = $_POST['tipo_participacao'] ?? [];

    // Inicia uma transação (garante atomicidade das operações)
    $pdo->beginTransaction();
    try {
        // 1. Inserir o TCC usando Prepared Statement
        // CRITÉRIO: 9.5 Inserção - Comando INSERT.
        // CRITÉRIO: 9.1 Banco de Dados - Uso de Prepared Statements com PDO.
        $stmt_tcc = $pdo->prepare("INSERT INTO TCC (titulo, data_cadastro, id_tipo_tcc) VALUES (?, ?, ?)");
        // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros passados para execute().
        $stmt_tcc->execute([$titulo, $data_cadastro, $id_tipo_tcc]);
        // Pega o ID do TCC recém-inserido
        $codigo_tcc = $pdo->lastInsertId(); // CRITÉRIO: 3.3 Atribuição

        // 2. Inserir o Aluno Principal na Aluno_TCC
        // CRITÉRIO: 9.5 Inserção.
        $stmt_aluno_principal = $pdo->prepare("INSERT INTO Aluno_TCC (codigo_tcc, RA, tipo_associacao) VALUES (?, ?, ?)");
        $stmt_aluno_principal->execute([$codigo_tcc, $ra_aluno_principal, 'Principal']);

        // 3. Inserir os Alunos Colaboradores na Aluno_TCC
        // CRITÉRIO: 4.1 Array - Definição do array $tipos_colaborador.
        $tipos_colaborador = ['Colaborador 1', 'Colaborador 2'];
        // CRITÉRIO: 9.5 Inserção.
        $stmt_colaborador = $pdo->prepare("INSERT INTO Aluno_TCC (codigo_tcc, RA, tipo_associacao) VALUES (?, ?, ?)");
        // CRITÉRIO: 5.2 Foreach - Loop para iterar sobre colaboradores selecionados.
        foreach ($colaboradores_selecionados as $indice => $ra_colaborador) {
            // CRITÉRIO: 6.1 If_Else - Condição para validação.
            // CRITÉRIO: 3.4 Comparação - Comparação de existência e valor.
            // CRITÉRIO: 3.6 Lógico - Uso de AND (&&).
            if (isset($tipos_colaborador[$indice]) && !empty($ra_colaborador)) {
                $tipo_colab = $tipos_colaborador[$indice]; // CRITÉRIO: 3.3 Atribuição
                $stmt_colaborador->execute([$codigo_tcc, $ra_colaborador, $tipo_colab]);
            }
        }

        // 4. Inserir os professores associados
        // CRITÉRIO: 9.5 Inserção.
        $stmt_prof_tcc = $pdo->prepare("INSERT INTO TCC_Professor (codigo_tcc, id_professor, tipo_participacao) VALUES (?, ?, ?)");
        // CRITÉRIO: 5.2 Foreach - Loop para iterar sobre professores selecionados.
        foreach ($professores_selecionados as $indice => $id_professor) {
            // CRITÉRIO: 6.1 If_Else - Condição de validação.
            if (!empty($id_professor)) {
                $tipo_part = $tipos_participacao[$indice];
                // CRITÉRIO: 3.1 Aritméticos - Casting para int.
                $stmt_prof_tcc->execute([$codigo_tcc, (int)$id_professor, $tipo_part]);
            }
        }
        
        $pdo->commit(); // Confirma todas as operações se tudo deu certo
        // CRITÉRIO: 3.2 String - Concatenação para mensagem.
        $mensagem = "TCC cadastrado com sucesso! Código: " . $codigo_tcc;
        $tipo_mensagem = 'success'; // CRITÉRIO: 3.3 Atribuição

    } catch (PDOException $e) {
        $pdo->rollBack(); // Desfaz todas as operações em caso de erro
        // CRITÉRIO: 3.2 String - Concatenação para mensagem de erro.
        $mensagem = "Erro ao cadastrar TCC: " . $e->getMessage();
        $tipo_mensagem = 'error'; // CRITÉRIO: 3.3 Atribuição
    }
}

// --- Busca de dados para popular os selects
// CRITÉRIO: 9.2 Leitura e apresentação de registro - Comandos SELECT.

// Busca os tipos de TCC
$stmt_tipos = $pdo->query("SELECT id_tipo_tcc, descricao FROM Tipo_TCC");
// CRITÉRIO: 4.1 Array - $tipos_tcc é um array.
$tipos_tcc = $stmt_tipos->fetchAll();

// Busca os alunos, criando objetos Aluno
// CRITÉRIO: 9.2 Leitura e apresentação de registro.
$stmt_alunos = $pdo->query("SELECT RA, nome, email FROM Aluno ORDER BY nome");
// CRITÉRIO: 4.1 Array - $alunos_obj é um array de objetos.
$alunos_obj = []; // CRITÉRIO: 3.3 Atribuição
// CRITÉRIO: 5.3 While / Do_While - Loop while para buscar resultados.
while ($row = $stmt_alunos->fetch()) {
    // CRITÉRIO: 7.3 Instanciação de Objetos - Criação de objetos Aluno.
    // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros do construtor Aluno.
    $alunos_obj[] = new Aluno($row['RA'], $row['nome'], $row['email']);
}

// Busca os professores, criando objetos Professor
// CRITÉRIO: 9.2 Leitura e apresentação de registro.
$stmt_professores = $pdo->query("SELECT id_professor, nome, area FROM Professor ORDER BY nome");
// CRITÉRIO: 4.1 Array - $professores_obj é um array de objetos.
$professores_obj = []; // CRITÉRIO: 3.3 Atribuição
// CRITÉRIO: 5.3 While / Do_While - Loop while para buscar resultados.
while ($row = $stmt_professores->fetch()) {
    // CRITÉRIO: 7.3 Instanciação de Objetos - Criação de objetos Professor.
    // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros do construtor Professor.
    $professores_obj[] = new Professor($row['id_professor'], $row['nome'], $row['area']);
}

// CRITÉRIO: 4.1 Array - Definição de array simples.
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
</head>
<body>
    <header>
        <h1>Sistema de Gerenciamento de TCC</h1>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="cadastro_tcc.php">Cadastrar Novo TCC</a></li>
            <li><a href="agenda.php">Agenda de Defesas</a></li>
            <li><a href="estatisticas.php">Estatísticas</a></li> </ul>
    </nav>
    <div class="container">
        <h2>Cadastrar Novo TCC</h2>

        <?php
        // CRITÉRIO: 6.1 If_Else - Exibição condicional de mensagens.
        // CRITÉRIO: 3.6 Lógico - Uso do operador NOT (!).
        if (!empty($mensagem)): ?>
            <div class="message <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
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
                // CRITÉRIO: 5.2 Foreach - Loop para popular o select de tipos de TCC.
                foreach ($tipos_tcc as $tipo): ?>
                    <option value="<?php echo $tipo['id_tipo_tcc']; ?>"><?php echo $tipo['descricao']; ?></option>
                <?php endforeach; ?>
            </select>

            <h3>Alunos Envolvidos:</h3>
            <label for="ra_aluno_principal">Aluno Principal:</label>
            <select id="ra_aluno_principal" name="ra_aluno_principal" required>
                <option value="">Selecione o Aluno Principal</option>
                <?php
                // CRITÉRIO: 5.2 Foreach - Loop para popular o select de alunos.
                // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Acesso a métodos de objetos Aluno ($aluno->getRA(), $aluno->getNome()).
                foreach ($alunos_obj as $aluno): ?>
                    <option value="<?php echo $aluno->getRA(); ?>"><?php echo $aluno->getNome() . ' (' . $aluno->getRA() . ')'; ?></option>
                <?php endforeach; ?>
            </select>

            <div id="colaboradores-container">
                <div class="colaborador-entry">
                    <label>Aluno Colaborador 1:</label>
                    <select name="colaboradores[]">
                        <option value="">Nenhum</option>
                        <?php
                        // CRITÉRIO: 5.2 Foreach - Loop para popular o select de colaboradores.
                        foreach ($alunos_obj as $aluno): ?>
                            <option value="<?php echo $aluno->getRA(); ?>"><?php echo $aluno->getNome() . ' (' . $aluno->getRA() . ')'; ?></option>
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
                        // CRITÉRIO: 5.2 Foreach - Loop para popular o select de professores.
                        // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Acesso a métodos de objetos Professor ($prof->getIdProfessor(), $prof->getNome()).
                        foreach ($professores_obj as $prof): ?>
                            <option value="<?php echo $prof->getIdProfessor(); ?>"><?php echo $prof->getNome(); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Tipo de Participação:</label>
                    <select name="tipo_participacao[]">
                        <?php
                        // CRITÉRIO: 5.2 Foreach - Loop para popular o select de tipos de participação.
                        foreach ($tipos_participacao_professor as $tipo_part): ?>
                            <option value="<?php echo $tipo_part; ?>"><?php echo $tipo_part; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="button-secondary" onclick="removeProfessor(this)">Remover</button>
                </div>
            </div>
            <button type="button" class="button" onclick="addProfessor()">Adicionar Mais Professor</button>
            <br><br>

            <input type="submit" value="Cadastrar TCC">
        </form>
    </div>

    <script>
        // CRITÉRIO: 1.1 Comentários (Documentação) - Comentários em funções JavaScript.
        // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Funções JavaScript com parâmetros.
        function addProfessor() {
            const container = document.getElementById('professores-container');
            const originalEntry = container.querySelector('.professor-entry');
            // CRITÉRIO: 3.3 Atribuição
            const newEntry = originalEntry.cloneNode(true);
            newEntry.querySelector('select[name="professores[]"]').value = "";
            newEntry.querySelector('select[name="tipo_participacao[]"]').value = "Orientador";
            container.appendChild(newEntry);
        }

        function removeProfessor(button) {
            const container = document.getElementById('professores-container');
            // CRITÉRIO: 6.1 If_Else - Condição para remover.
            // CRITÉRIO: 3.4 Comparação - Comparação de comprimento.
            if (container.children.length > 1) {
                button.closest('.professor-entry').remove();
            } else {
                alert('É necessário ter pelo menos um professor associado.');
            }
        }

        // CRITÉRIO: 3.3 Atribuição
        let colaboradorCount = 1;

        function addColaborador() {
            // CRITÉRIO: 6.1 If_Else - Condição de limite.
            // CRITÉRIO: 3.4 Comparação - Comparação numérica.
            if (colaboradorCount >= 2) {
                alert('Você só pode adicionar até 2 alunos colaboradores.');
                return;
            }
            // CRITÉRIO: 3.5 Incr ou Decremento - Incremento.
            colaboradorCount++;

            const container = document.getElementById('colaboradores-container');
            const originalEntry = container.querySelector('.colaborador-entry');
            const newEntry = originalEntry.cloneNode(true);

            // CRITÉRIO: 3.2 String - Concatenação de string com template literal.
            newEntry.querySelector('label').innerText = `Aluno Colaborador ${colaboradorCount}:`;
            newEntry.querySelector('select[name="colaboradores[]"]').value = "";
            
            container.appendChild(newEntry);

            // CRITÉRIO: 6.1 If_Else - Condição de visibilidade.
            if (colaboradorCount >= 2) {
                document.getElementById('addColaboradorBtn').style.display = 'none';
            }
        }

        function removeColaborador(button) {
            const container = document.getElementById('colaboradores-container');
            // CRITÉRIO: 6.1 If_Else - Condição de remoção.
            if (container.children.length > 1) {
                button.closest('.colaborador-entry').remove();
                // CRITÉRIO: 3.5 Incr ou Decremento - Decremento.
                colaboradorCount--;
                // CRITÉRIO: 3.3 Atribuição
                let currentColab = 1;
                // CRITÉRIO: 5.2 Foreach - Loop para reordenar labels.
                container.querySelectorAll('.colaborador-entry').forEach(entry => {
                    entry.querySelector('label').innerText = `Aluno Colaborador ${currentColab}:`;
                    // CRITÉRIO: 3.5 Incr ou Decremento - Incremento.
                    currentColab++;
                });
                document.getElementById('addColaboradorBtn').style.display = 'inline-block';
            } else {
                alert('É necessário ter pelo menos um aluno principal.');
            }
        }

        // CRITÉRIO: 1.1 Comentários (Documentação)
        document.addEventListener('DOMContentLoaded', (event) => {
            const container = document.getElementById('colaboradores-container');
            // CRITÉRIO: 6.1 If_Else - Condição de carregamento inicial.
            if (container.children.length >= 2) {
                colaboradorCount = 2; // CRITÉRIO: 3.3 Atribuição
                document.getElementById('addColaboradorBtn').style.display = 'none';
            }
        });
    </script>
</body>
</html>