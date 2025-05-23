<?php
require_once 'config.php'; // Inclui o arquivo de configuração do banco de dados

$mensagem = '';
$tipo_mensagem = '';

// --- Processa o agendamento de defesa (INSERT/UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agendar_defesa'])) {
    $codigo_tcc_agendar = (int)$_POST['codigo_tcc'];
    $data_defesa = $conexao->real_escape_string($_POST['data_defesa']);
    $hora_defesa = $conexao->real_escape_string($_POST['hora_defesa']);

    // Verifica se já existe um agendamento para este TCC
    $sql_check_agenda = "SELECT COUNT(*) FROM Agenda WHERE codigo_tcc = $codigo_tcc_agendar";
    $result_check = $conexao->query($sql_check_agenda);
    $row_check = $result_check->fetch_row();

    if ($row_check[0] > 0) {
        // Atualiza a agenda existente
        $sql_agenda = "UPDATE Agenda SET data_defesa = '$data_defesa', hora = '$hora_defesa' WHERE codigo_tcc = $codigo_tcc_agendar";
        $mensagem = "Agendamento atualizado com sucesso!";
        $tipo_mensagem = 'success';
    } else {
        // Insere um novo agendamento
        $sql_agenda = "INSERT INTO Agenda (codigo_tcc, data_defesa, hora) VALUES ($codigo_tcc_agendar, '$data_defesa', '$hora_defesa')";
        $mensagem = "Agendamento realizado com sucesso!";
        $tipo_mensagem = 'success';
    }
    
    if ($conexao->query($sql_agenda) !== TRUE) {
        $mensagem = "Erro ao agendar/atualizar defesa: " . $conexao->error;
        $tipo_mensagem = 'error';
    }
}

// --- Processa a EXCLUSÃO de TCC e seus relacionamentos ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_tcc'])) {
    $codigo_tcc_excluir = (int)$_POST['codigo_tcc_excluir'];

    // Inicia uma transação para garantir que todas as exclusões ocorram ou nenhuma ocorra
    $conexao->begin_transaction();
    $sucesso_exclusao = true;

    try {
        // 1. Remover TCC da tabela Agenda (se existir)
        $sql_delete_agenda = "DELETE FROM Agenda WHERE codigo_tcc = $codigo_tcc_excluir";
        if ($conexao->query($sql_delete_agenda) === FALSE) {
            throw new Exception("Erro ao excluir agendamento: " . $conexao->error);
        }

        // 2. Remover associações de professores (TCC_Professor)
        $sql_delete_prof_tcc = "DELETE FROM TCC_Professor WHERE codigo_tcc = $codigo_tcc_excluir";
        if ($conexao->query($sql_delete_prof_tcc) === FALSE) {
            throw new Exception("Erro ao excluir professores associados: " . $conexao->error);
        }

        // 3. Remover associações de alunos (Aluno_TCC) -- ESTE É UM PASSO CRÍTICO
        $sql_delete_aluno_tcc = "DELETE FROM Aluno_TCC WHERE codigo_tcc = $codigo_tcc_excluir";
        if ($conexao->query($sql_delete_aluno_tcc) === FALSE) {
            throw new Exception("Erro ao excluir alunos associados: " . $conexao->error);
        }
        
        // 4. Excluir o TCC da tabela TCC (último, devido às chaves estrangeiras)
        $sql_delete_tcc = "DELETE FROM TCC WHERE codigo_tcc = $codigo_tcc_excluir";
        if ($conexao->query($sql_delete_tcc) === FALSE) {
            throw new Exception("Erro ao excluir TCC principal: " . $conexao->error);
        }

        $conexao->commit(); // Confirma todas as operações
        $mensagem = "TCC excluído com sucesso (e seus agendamentos/associações)!";
        $tipo_mensagem = 'success';

    } catch (Exception $e) {
        $conexao->rollback(); // Desfaz todas as operações em caso de erro
        $mensagem = "Erro na exclusão do TCC: " . $e->getMessage();
        $tipo_mensagem = 'error';
    }
}


// --- Busca os dados para a agenda (executa sempre que a página é carregada) ---
$agenda_tccs = [];
$sql_agenda = "
    SELECT
        T.codigo_tcc,
        T.titulo,
        TT.descricao AS tipo_tcc,
        AG.data_defesa,
        AG.hora
    FROM
        TCC AS T
    INNER JOIN
        Tipo_TCC AS TT ON T.id_tipo_tcc = TT.id_tipo_tcc
    LEFT JOIN
        Agenda AS AG ON T.codigo_tcc = AG.codigo_tcc
    ORDER BY AG.data_defesa ASC, AG.hora ASC, T.titulo ASC;
";
$resultado_agenda = $conexao->query($sql_agenda);

if ($resultado_agenda) {
    while ($linha = $resultado_agenda->fetch_assoc()) {
        $agenda_tccs[] = $linha;
    }
}

// --- Busca os alunos (principal e colaboradores) para cada TCC ---
foreach ($agenda_tccs as $key => $tcc) {
    $codigo_tcc = $tcc['codigo_tcc'];
    $alunos_tcc = [];
    $sql_alunos = "
        SELECT A.nome, ATCC.tipo_associacao
        FROM Aluno_TCC AS ATCC
        INNER JOIN Aluno AS A ON ATCC.RA = A.RA
        WHERE ATCC.codigo_tcc = $codigo_tcc
        ORDER BY FIELD(ATCC.tipo_associacao, 'Principal', 'Colaborador 1', 'Colaborador 2');
    ";
    $resultado_alunos_tcc = $conexao->query($sql_alunos);
    if ($resultado_alunos_tcc) {
        while ($aluno_linha = $resultado_alunos_tcc->fetch_assoc()) {
            $tipo_aluno = '';
            // Formata o tipo de associação para exibição
            if ($aluno_linha['tipo_associacao'] == 'Principal') {
                $tipo_aluno = 'Aluno'; // Exibe "Aluno" para o principal
            } else if ($aluno_linha['tipo_associacao'] == 'Colaborador 1') {
                $tipo_aluno = 'Colab. 1';
            } else if ($aluno_linha['tipo_associacao'] == 'Colaborador 2') {
                $tipo_aluno = 'Colab. 2';
            }
            $alunos_tcc[] = $tipo_aluno . ': ' . htmlspecialchars($aluno_linha['nome']); // Sanitiza o nome do aluno
        }
    }
    // Garante que a coluna 'alunos_envolvidos' exista, mesmo que vazia
    $agenda_tccs[$key]['alunos_envolvidos'] = !empty($alunos_tcc) ? implode('<br>', $alunos_tcc) : 'Nenhum Aluno Associado';
}


// --- Busca os professores para cada TCC ---
foreach ($agenda_tccs as $key => $tcc) {
    $codigo_tcc = $tcc['codigo_tcc'];
    $professores_tcc = [];
    $sql_professores = "
        SELECT P.nome, TCC_P.tipo_participacao
        FROM TCC_Professor AS TCC_P
        INNER JOIN Professor AS P ON TCC_P.id_professor = P.id_professor
        WHERE TCC_P.codigo_tcc = $codigo_tcc
        ORDER BY FIELD(TCC_P.tipo_participacao, 'Orientador', 'Coorientador', 'Professor Convidado 1', 'Professor Convidado 2');
    ";
    $resultado_professores = $conexao->query($sql_professores);
    if ($resultado_professores) {
        while ($prof_linha = $resultado_professores->fetch_assoc()) {
            $professores_tcc[] = $prof_linha['tipo_participacao'] . ': ' . htmlspecialchars($prof_linha['nome']); // Sanitiza o nome do professor
        }
    }
    $agenda_tccs[$key]['professores'] = implode('<br>', $professores_tcc);
}

$conexao->close(); // Fecha a conexão
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
        </ul>
    </nav>
    <div class="container">
        <h2>Agenda de Defesas de TCC</h2>

        <?php if (!empty($mensagem)): ?>
            <div class="message <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($agenda_tccs)): ?>
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
                    <?php foreach ($agenda_tccs as $tcc): ?>
                        <tr>
                            <td><?php echo $tcc['codigo_tcc']; ?></td>
                            <td><?php echo htmlspecialchars($tcc['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($tcc['tipo_tcc']); ?></td>
                            <td><?php echo $tcc['alunos_envolvidos']; ?></td> <td><?php echo $tcc['professores']; ?></td>
                            <td><?php echo $tcc['data_defesa'] ? date('d/m/Y', strtotime($tcc['data_defesa'])) : 'Não agendado'; ?></td>
                            <td><?php echo $tcc['hora'] ?? 'Não agendado'; ?></td>
                            <td>
                                <button class="button" onclick="openAgendaModal(
                                    <?php echo $tcc['codigo_tcc']; ?>,
                                    '<?php echo htmlspecialchars($tcc['titulo'], ENT_QUOTES); ?>',
                                    '<?php echo $tcc['data_defesa'] ?? ''; ?>',
                                    '<?php echo $tcc['hora'] ?? ''; ?>'
                                )">Agendar/Editar</button>
                                <button class="button button-secondary" onclick="openDeleteModal(<?php echo $tcc['codigo_tcc']; ?>, '<?php echo htmlspecialchars($tcc['titulo'], ENT_QUOTES); ?>')">Excluir</button>
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
        // Funções JavaScript para modais (permanecem as mesmas e devem funcionar)
        function openAgendaModal(codigo_tcc, titulo, data_defesa, hora_defesa) {
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
            document.getElementById('delete_tcc_titulo').innerText = titulo;
            document.getElementById('delete_tcc_codigo').innerText = codigo_tcc;
            document.getElementById('delete_codigo_tcc_input').value = codigo_tcc;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const agendaModal = document.getElementById('agendaModal');
            const deleteModal = document.getElementById('deleteModal');

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