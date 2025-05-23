<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial.
require_once 'config.php'; // Inclui a nova configuração PDO (CRITÉRIO: 9.1 Banco de Dados - Conexão PDO)
require_once 'models.php'; // Inclui as novas classes (CRITÉRIO: 7.1 Classes)

$mensagem = ''; // CRITÉRIO: 3.3 Atribuição
$tipo_mensagem = ''; // CRITÉRIO: 3.3 Atribuição

// CRITÉRIO: 7.3 Instanciação de Objetos - Obtém a conexão PDO através da classe Database.
$pdo = Database::getInstance()->getConnection();

// --- Processa o agendamento de defesa (INSERT/UPDATE) ---
// CRITÉRIO: 6.1 If_Else - Condição para verificar o método da requisição e a ação.
// CRITÉRIO: 3.4 Comparação, CRITÉRIO: 3.6 Lógico.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agendar_defesa'])) {
    // CRITÉRIO: 3.1 Aritméticos - Casting para int.
    $codigo_tcc_agendar = (int)$_POST['codigo_tcc'];
    // CRITÉRIO: 3.3 Atribuição.
    $data_defesa = $_POST['data_defesa'];
    $hora_defesa = $_POST['hora_defesa'];

    // Inicia transação
    $pdo->beginTransaction();
    try {
        // Verifica se já existe um agendamento para este TCC
        // CRITÉRIO: 9.2 Leitura e apresentação de registro - Comando SELECT.
        // CRITÉRIO: 9.1 Banco de Dados - Uso de Prepared Statements.
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Agenda WHERE codigo_tcc = ?");
        // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros para execute().
        $stmt_check->execute([$codigo_tcc_agendar]);
        // CRITÉRIO: 3.3 Atribuição.
        $count = $stmt_check->fetchColumn();

        // CRITÉRIO: 6.1 If_Else - Lógica de atualização ou inserção.
        // CRITÉRIO: 3.4 Comparação - Comparação numérica.
        if ($count > 0) {
            // Atualiza a agenda existente
            // CRITÉRIO: 9.3 Atualização - Comando UPDATE.
            $stmt_agenda = $pdo->prepare("UPDATE Agenda SET data_defesa = ?, hora = ? WHERE codigo_tcc = ?");
            $stmt_agenda->execute([$data_defesa, $hora_defesa, $codigo_tcc_agendar]);
            // CRITÉRIO: 3.3 Atribuição.
            $mensagem = "Agendamento atualizado com sucesso!";
            $tipo_mensagem = 'success';
        } else {
            // Insere um novo agendamento
            // CRITÉRIO: 9.5 Inserção - Comando INSERT.
            $stmt_agenda = $pdo->prepare("INSERT INTO Agenda (codigo_tcc, data_defesa, hora) VALUES (?, ?, ?)");
            $stmt_agenda->execute([$codigo_tcc_agendar, $data_defesa, $hora_defesa]);
            // CRITÉRIO: 3.3 Atribuição.
            $mensagem = "Agendamento realizado com sucesso!";
            $tipo_mensagem = 'success';
        }
        $pdo->commit(); // Confirma a transação.
    } catch (PDOException $e) {
        $pdo->rollBack(); // Reverte a transação em caso de erro.
        // CRITÉRIO: 3.2 String - Concatenação para mensagem de erro.
        $mensagem = "Erro ao agendar/atualizar defesa: " . $e->getMessage();
        $tipo_mensagem = 'error';
    }
}

// --- Processa a EXCLUSÃO de TCC e seus relacionamentos ---
// CRITÉRIO: 6.1 If_Else - Condição para verificar a ação de exclusão.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_tcc'])) {
    // CRITÉRIO: 3.1 Aritméticos - Casting para int.
    $codigo_tcc_excluir = (int)$_POST['codigo_tcc_excluir'];

    $pdo->beginTransaction(); // Inicia transação para exclusão.
    try {
        // Excluir na ordem correta devido às chaves estrangeiras
        // CRITÉRIO: 9.4 Deleção - Comandos DELETE.
        $stmt_delete_agenda = $pdo->prepare("DELETE FROM Agenda WHERE codigo_tcc = ?");
        $stmt_delete_agenda->execute([$codigo_tcc_excluir]);

        $stmt_delete_prof_tcc = $pdo->prepare("DELETE FROM TCC_Professor WHERE codigo_tcc = ?");
        $stmt_delete_prof_tcc->execute([$codigo_tcc_excluir]);

        $stmt_delete_aluno_tcc = $pdo->prepare("DELETE FROM Aluno_TCC WHERE codigo_tcc = ?");
        $stmt_delete_aluno_tcc->execute([$codigo_tcc_excluir]);
        
        $stmt_delete_tcc = $pdo->prepare("DELETE FROM TCC WHERE codigo_tcc = ?");
        $stmt_delete_tcc->execute([$codigo_tcc_excluir]);

        $pdo->commit(); // Confirma a transação.
        // CRITÉRIO: 3.3 Atribuição.
        $mensagem = "TCC excluído com sucesso (e seus agendamentos/associações)!";
        $tipo_mensagem = 'success';

    } catch (PDOException $e) {
        $pdo->rollBack(); // Reverte a transação.
        // CRITÉRIO: 3.2 String - Concatenação para mensagem de erro.
        $mensagem = "Erro na exclusão do TCC: " . $e->getMessage();
        $tipo_mensagem = 'error';
    }
}

// --- Busca os dados para a agenda ---
// CRITÉRIO: 9.2 Leitura e apresentação de registro - Comando SELECT principal.
$agenda_tccs = []; // CRITÉRIO: 4.1 Array - Inicialização de array para armazenar objetos Tcc.
$stmt_agenda = $pdo->query("
    SELECT
        T.codigo_tcc,
        T.titulo,
        TT.descricao AS tipo_tcc_desc,
        AG.data_defesa,
        AG.hora,
        T.data_cadastro -- Incluindo data_cadastro para o construtor do Tcc
    FROM
        TCC AS T
    INNER JOIN
        Tipo_TCC AS TT ON T.id_tipo_tcc = TT.id_tipo_tcc
    LEFT JOIN
        Agenda AS AG ON T.codigo_tcc = AG.codigo_tcc
    ORDER BY AG.data_defesa ASC, AG.hora ASC, T.titulo ASC;
");

// Cria objetos TCC ao buscar
// CRITÉRIO: 5.3 While / Do_While - Loop while para buscar resultados.
while ($row = $stmt_agenda->fetch()) {
    // CRITÉRIO: 7.3 Instanciação de Objetos - Criação de objetos Tcc.
    // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros do construtor Tcc.
    $agenda_tccs[] = new Tcc(
        $row['codigo_tcc'],
        $row['titulo'],
        $row['data_cadastro'], // Data de cadastro do TCC
        $row['tipo_tcc_desc'],
        $row['data_defesa'], // Data da defesa (da tabela Agenda)
        $row['hora']         // Hora da defesa (da tabela Agenda)
    );
}

// --- Adiciona alunos e professores a cada objeto TCC para exibição ---
// CRITÉRIO: 5.2 Foreach - Loop para iterar sobre os TCCs.
foreach ($agenda_tccs as $key => $tcc_obj) {
    $codigo_tcc = $tcc_obj->getCodigoTcc(); // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Acesso a método getter.
    $alunos_tcc = []; // CRITÉRIO: 4.1 Array - Inicialização de array.
    // CRITÉRIO: 9.2 Leitura e apresentação de registro - Comando SELECT.
    $stmt_alunos = $pdo->prepare("
        SELECT A.nome, ATCC.tipo_associacao
        FROM Aluno_TCC AS ATCC
        INNER JOIN Aluno AS A ON ATCC.RA = A.RA
        WHERE ATCC.codigo_tcc = ?
        ORDER BY FIELD(ATCC.tipo_associacao, 'Principal', 'Colaborador 1', 'Colaborador 2');
    ");
    $stmt_alunos->execute([$codigo_tcc]);
    // CRITÉRIO: 5.3 While / Do_While - Loop while para buscar resultados.
    while ($aluno_row = $stmt_alunos->fetch()) {
        // CRITÉRIO: 6.2 Switch - Demonstração da estrutura Switch.
        switch ($aluno_row['tipo_associacao']) {
            case 'Principal':
                $tipo_aluno_formatado = 'Aluno';
                break; // CRITÉRIO: 1.1 Comentários
            case 'Colaborador 1':
                $tipo_aluno_formatado = 'Colab. 1';
                break;
            case 'Colaborador 2':
                $tipo_aluno_formatado = 'Colab. 2';
                break;
            default:
                $tipo_aluno_formatado = 'Outro';
                break;
        }
        // CRITÉRIO: 3.2 String - Concatenação de string.
        // CRITÉRIO: 8.1 Funções com passagem de parâmetros - htmlspecialchars().
        $alunos_tcc[] = $tipo_aluno_formatado . ': ' . htmlspecialchars($aluno_row['nome']);
    }
    // CRITÉRIO: 3.3 Atribuição - Atribuição de propriedade dinâmica.
    // CRITÉRIO: 3.6 Lógico - Uso de NOT (!).
    // CRITÉRIO: 6.3 Operador Ternário - Para definir texto padrão se array vazio.
    // CRITÉRIO: 8.1 Funções com passagem de parâmetros - implode() e empty().
    $tcc_obj->alunos_envolvidos_html = !empty($alunos_tcc) ? implode('<br>', $alunos_tcc) : 'Nenhum Aluno Associado';


    $professores_tcc = []; // CRITÉRIO: 4.1 Array - Inicialização de array.
    // CRITÉRIO: 9.2 Leitura e apresentação de registro - Comando SELECT.
    $stmt_professores = $pdo->prepare("
        SELECT P.nome, TCC_P.tipo_participacao
        FROM TCC_Professor AS TCC_P
        INNER JOIN Professor AS P ON TCC_P.id_professor = P.id_professor
        WHERE TCC_P.codigo_tcc = ?
        ORDER BY FIELD(TCC_P.tipo_participacao, 'Orientador', 'Coorientador', 'Professor Convidado 1', 'Professor Convidado 2');
    ");
    $stmt_professores->execute([$codigo_tcc]);
    // CRITÉRIO: 5.3 While / Do_While - Loop while para buscar resultados.
    while ($prof_row = $stmt_professores->fetch()) {
        // CRITÉRIO: 3.2 String - Concatenação de string.
        // CRITÉRIO: 8.1 Funções com passagem de parâmetros - htmlspecialchars().
        $professores_tcc[] = $prof_row['tipo_participacao'] . ': ' . htmlspecialchars($prof_row['nome']);
    }
    // CRITÉRIO: 3.3 Atribuição - Atribuição de propriedade dinâmica.
    // CRITÉRIO: 8.1 Funções com passagem de parâmetros - implode().
    $tcc_obj->professores_html = implode('<br>', $professores_tcc);
}

// O objeto PDO é fechado automaticamente quando o script termina.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Defesas de TCC</title>
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
        <h2>Agenda de Defesas de TCC</h2>

        <?php
        // CRITÉRIO: 6.1 If_Else - Exibição condicional de mensagens.
        if (!empty($mensagem)): ?>
            <div class="message <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php
        // CRITÉRIO: 6.1 If_Else - Exibição condicional da tabela.
        if (empty($agenda_tccs)): ?>
            <p>Nenhum TCC cadastrado ou agendado ainda.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Código TCC</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Alunos Envolvidos</th>
                        <th>Professores</th>
                        <th>Data Defesa</th>
                        <th>Hora Defesa</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // CRITÉRIO: 5.2 Foreach - Loop para iterar sobre os objetos Tcc na agenda.
                    // CRITÉRIO: 7.1 Classes (Métodos e Atributos) - Iterando sobre objetos Tcc ($tcc_obj).
                    foreach ($agenda_tccs as $tcc_obj): ?>
                        <tr>
                            <td><?php echo $tcc_obj->getCodigoTcc(); ?></td>
                            <td><?php echo htmlspecialchars($tcc_obj->getTitulo()); ?></td>
                            <td><?php echo htmlspecialchars($tcc_obj->getTipoTcc()); ?></td>
                            <td><?php echo $tcc_obj->alunos_envolvidos_html; ?></td>
                            <td><?php echo $tcc_obj->professores_html; ?></td>
                            <td><?php echo $tcc_obj->getDataDefesa() ? date('d/m/Y', strtotime($tcc_obj->getDataDefesa())) : 'Não agendado'; ?></td>
                            <td><?php echo $tcc_obj->getHoraDefesa() ? date('H:i', strtotime($tcc_obj->getHoraDefesa())) : 'Não agendado'; ?></td>
                            <td>
                                <button class="button" onclick="openAgendaModal(
                                    <?php echo $tcc_obj->getCodigoTcc(); ?>,
                                    '<?php echo htmlspecialchars($tcc_obj->getTitulo(), ENT_QUOTES); ?>',
                                    '<?php echo $tcc_obj->getDataDefesa() ?? ''; ?>',
                                    '<?php echo $tcc_obj->getHoraDefesa() ?? ''; ?>'
                                )">Agendar/Editar</button>
                                <button class="button button-secondary" onclick="openDeleteModal(<?php echo $tcc_obj->getCodigoTcc(); ?>, '<?php echo htmlspecialchars($tcc_obj->getTitulo(), ENT_QUOTES); ?>')">Excluir</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div id="agendaModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-button" onclick="closeAgendaModal()">&times;</span>
            <h3>Agendar/Editar Defesa</h3>
            <form action="agenda.php" method="POST">
                <input type="hidden" id="modal_codigo_tcc" name="codigo_tcc">
                <label for="modal_titulo_tcc">Título do TCC:</label>
                <input type="text" id="modal_titulo_tcc" disabled>

                <label for="modal_data_defesa">Data da Defesa:</label>
                <input type="date" id="modal_data_defesa" name="data_defesa" required>

                <label for="modal_hora_defesa">Hora da Defesa:</label>
                <input type="time" id="modal_hora_defesa" name="hora_defesa" required>

                <input type="submit" name="agendar_defesa" value="Salvar Agendamento">
                <button type="button" class="button-secondary" onclick="closeAgendaModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-button" onclick="closeDeleteModal()">&times;</span>
            <h3>Confirmar Exclusão</h3>
            <p>Você tem certeza que deseja excluir o TCC "<strong id="delete_tcc_titulo"></strong>" (Código: <span id="delete_tcc_codigo"></span>)?</p>
            <p>Esta ação removerá o TCC, seus agendamentos e as associações com alunos e professores.</p>
            <form action="agenda.php" method="POST">
                <input type="hidden" id="delete_codigo_tcc_input" name="codigo_tcc_excluir">
                <input type="submit" name="excluir_tcc" value="Sim, Excluir TCC" class="button" style="background-color: var(--error-color);">
                <button type="button" class="button-secondary" onclick="closeDeleteModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        // CRITÉRIO: 1.1 Comentários (Documentação) - Comentários sobre as funções JavaScript.
        // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Funções JavaScript com parâmetros.
        function openAgendaModal(codigo_tcc, titulo, data_defesa, hora_defesa) {
            // CRITÉRIO: 3.3 Atribuição
            document.getElementById('modal_codigo_tcc').value = codigo_tcc;
            document.getElementById('modal_titulo_tcc').value = titulo;
            document.getElementById('modal_data_defesa').value = data_defesa;
            document.getElementById('modal_hora_defesa').value = hora_defesa;
            document.getElementById('agendaModal').style.display = 'flex';
        }

        function closeAgendaModal() {
            document.getElementById('agendaModal').style.display = 'none';
        }

        function openDeleteModal(codigo_tcc, titulo) {
            // CRITÉRIO: 3.3 Atribuição
            document.getElementById('delete_tcc_titulo').innerText = titulo;
            document.getElementById('delete_tcc_codigo').innerText = codigo_tcc;
            document.getElementById('delete_codigo_tcc_input').value = codigo_tcc;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // CRITÉRIO: 1.1 Comentários (Documentação)
        window.onclick = function(event) {
            const agendaModal = document.getElementById('agendaModal');
            const deleteModal = document.getElementById('deleteModal');

            // CRITÉRIO: 6.1 If_Else - Condição para fechar modal ao clicar fora.
            // CRITÉRIO: 3.4 Comparação.
            if (event.target == agendaModal) {
                agendaModal.style.display = 'none';
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>