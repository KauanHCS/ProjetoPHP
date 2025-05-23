<?php
require_once 'config.php'; // Inclui o arquivo de configuração do banco de dados

// Processa o agendamento de defesa quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agendar_defesa'])) {
    $codigo_tcc_agendar = (int)$_POST['codigo_tcc'];
    $data_defesa = $conexao->real_escape_string($_POST['data_defesa']);
    $hora_defesa = $conexao->real_escape_string($_POST['hora_defesa']);

    // Verifica se já existe um agendamento para este TCC (UNIQUE constraint)
    $sql_check_agenda = "SELECT COUNT(*) FROM Agenda WHERE codigo_tcc = $codigo_tcc_agendar";
    $result_check = $conexao->query($sql_check_agenda);
    $row_check = $result_check->fetch_row();

    if ($row_check[0] > 0) {
        // Atualiza a agenda existente
        $sql_agenda = "UPDATE Agenda SET data_defesa = '$data_defesa', hora = '$hora_defesa' WHERE codigo_tcc = $codigo_tcc_agendar";
        $mensagem_agenda = "Agendamento atualizado com sucesso!";
        $tipo_mensagem_agenda = 'success';
    } else {
        // Insere um novo agendamento
        $sql_agenda = "INSERT INTO Agenda (codigo_tcc, data_defesa, hora) VALUES ($codigo_tcc_agendar, '$data_defesa', '$hora_defesa')";
        $mensagem_agenda = "Agendamento realizado com sucesso!";
        $tipo_mensagem_agenda = 'success';
    }
    
    if ($conexao->query($sql_agenda) !== TRUE) {
        $mensagem_agenda = "Erro ao agendar/atualizar defesa: " . $conexao->error;
        $tipo_mensagem_agenda = 'error';
    }
}


// Busca os dados para a agenda
$agenda_tccs = [];
$sql_agenda = "
    SELECT
        T.codigo_tcc,
        T.titulo,
        TT.descricao AS tipo_tcc,
        A.nome AS nome_aluno,
        AG.data_defesa,
        AG.hora
    FROM
        TCC AS T
    INNER JOIN
        Tipo_TCC AS TT ON T.id_tipo_tcc = TT.id_tipo_tcc
    LEFT JOIN
        Aluno AS A ON T.codigo_tcc = A.codigo_tcc -- Assume 1 aluno por TCC, se não, precisaria de JOINs diferentes
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

// Busca os professores para cada TCC
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
            $professores_tcc[] = $prof_linha['tipo_participacao'] . ': ' . $prof_linha['nome'];
        }
    }
    $agenda_tccs[$key]['professores'] = implode('<br>', $professores_tcc);
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Defesas de TCC</title>
    <link rel="stylesheet" href="css/style.css">
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

        <?php if (!empty($mensagem_agenda)): ?>
            <div class="message <?php echo $tipo_mensagem_agenda; ?>">
                <?php echo $mensagem_agenda; ?>
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
                        <th>Aluno</th>
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
                            <td><?php echo htmlspecialchars($tcc['nome_aluno'] ?? 'Não associado'); ?></td>
                            <td><?php echo $tcc['professores']; ?></td>
                            <td><?php echo $tcc['data_defesa'] ? date('d/m/Y', strtotime($tcc['data_defesa'])) : 'Não agendado'; ?></td>
                            <td><?php echo $tcc['hora'] ?? 'Não agendado'; ?></td>
                            <td>
                                <button onclick="openAgendaModal(
                                    <?php echo $tcc['codigo_tcc']; ?>,
                                    '<?php echo htmlspecialchars($tcc['titulo'], ENT_QUOTES); ?>',
                                    '<?php echo $tcc['data_defesa'] ?? ''; ?>',
                                    '<?php echo $tcc['hora'] ?? ''; ?>'
                                )">Agendar/Editar Defesa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div id="agendaModal" style="display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
        <div style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%; border-radius: 8px;">
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
                <button type="button" onclick="closeAgendaModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        function openAgendaModal(codigo_tcc, titulo, data_defesa, hora_defesa) {
            document.getElementById('modal_codigo_tcc').value = codigo_tcc;
            document.getElementById('modal_titulo_tcc').value = titulo;
            document.getElementById('modal_data_defesa').value = data_defesa;
            document.getElementById('modal_hora_defesa').value = hora_defesa;
            document.getElementById('agendaModal').style.display = 'block';
        }

        function closeAgendaModal() {
            document.getElementById('agendaModal').style.display = 'none';
        }
    </script>
</body>
</html>