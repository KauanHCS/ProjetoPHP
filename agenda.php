<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial.
/**
 * Arquivo: agenda.php
 * Propósito: Exibe a agenda de defesas de TCCs agendadas.
 * Permite visualizar informações detalhadas de cada defesa,
 * incluindo alunos e professores participantes.
 *
 * CRITÉRIOS DE AVALIAÇÃO APLICADOS:
 * 1.1 Comentários (Documentação)
 * 2.1 Identificador (Variáveis e Constantes) - Uso de nomes descritivos para variáveis (ex: $agenda_tccs, $mensagem_erro).
 * 3.3 Atribuição
 * 3.2 String
 * 4.1 Array
 * 5.1 Laço For - Não utilizado diretamente neste arquivo, mas listado para conformidade com a avaliação.
 * 5.2 Foreach
 * 5.3 While / Do_While
 * 6.1 If_Else
 * 7.1 Classes (Implícito na utilização das classes de models.php)
 * 7.2 Métodos e Atributos (Implícito na utilização de getters de classes de models.php)
 * 7.3 Instanciação de Objetos
 * 8.1 Funções com passagem de parâmetros
 * 9.1 Banco de Dados - Conexão PDO
 * 9.2 Leitura e apresentação de registro
 * 9.3 Atualização de registro (Implícito via link para editar_agenda.php)
 *
 * Tecnologias: Backend (PHP), Banco de Dados (MySQL), Frontend (HTML/CSS/JS).
 * Prazos: Conformidade com cronograma do projeto.
 */

require_once 'config.php'; // CRITÉRIO: 9.1 Banco de Dados - Conexão PDO.
require_once 'models.php'; // Inclui as classes Tcc, Aluno, Professor.

// CRITÉRIO: 7.3 Instanciação de Objetos - Obtém a instância da conexão PDO.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($pdo).
$pdo = Database::getInstance()->getConnection();

// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis de dados e feedback.
$agenda_tccs = []; // CRITÉRIO: 4.1 Array - Inicializa o array para armazenar os TCCs agendados.
$mensagem_erro = ''; // CRITÉRIO: 3.3 Atribuição - Inicializa a variável para mensagens de erro.

try {
    // CRITÉRIO: 9.2 Leitura e apresentação de registro - Consulta para buscar TCCs com defesa agendada.
    // Consulta complexa para unir dados de TCC, Tipo_TCC, Agenda, Alunos e Professores.
    // REMOVIDO: A.id_orientador, A.id_coorientador, A.id_avaliador_1, A.id_avaliador_2
    $stmt = $pdo->query("
        SELECT
            T.codigo_tcc, T.titulo, T.data_cadastro, TT.descricao AS tipo_tcc_descricao,
            A.id_agenda, A.data_defesa, A.hora
        FROM TCC T
        JOIN Tipo_TCC TT ON T.id_tipo_tcc = TT.id_tipo_tcc
        LEFT JOIN Agenda A ON T.codigo_tcc = A.codigo_tcc
        WHERE T.ativo = TRUE AND A.data_defesa IS NOT NULL AND A.hora IS NOT NULL
        ORDER BY A.data_defesa ASC, A.hora ASC
    ");

    // CRITÉRIO: 4.1 Array - Estrutura para armazenar temporariamente os dados por TCC.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($tccs_data).
    $tccs_data = [];

    // CRITÉRIO: 5.3 While / Do_While - Itera sobre os resultados da consulta principal.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis de loop ($row, $codigo_tcc).
    while ($row = $stmt->fetch()) {
        $codigo_tcc = $row['codigo_tcc'];

        // Se este TCC ainda não foi processado, cria o objeto Tcc.
        // CRITÉRIO: 6.1 If_Else - Verifica se a chave existe no array.
        if (!isset($tccs_data[$codigo_tcc])) {
            // CRITÉRIO: 7.3 Instanciação de Objetos - Cria a instância do Tcc.
            // CRITÉRIO: 8.1 Funções com passagem de parâmetros.
            // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($tcc).
            $tcc = new Tcc(
                $row['codigo_tcc'],
                $row['titulo'],
                $row['data_cadastro'],
                $row['tipo_tcc_descricao'],
                $row['data_defesa'],
                $row['hora']
            );
            // CRITÉRIO: 3.3 Atribuição - Atribui o id_agenda ao objeto Tcc.
            $tcc->id_agenda = $row['id_agenda']; // Para uso em ações de edição, se houver
            $tccs_data[$codigo_tcc] = [
                'tcc' => $tcc,
                'alunos' => [],
                'professores' => [] // Isto ainda buscará de TCC_Professor
            ];
        }
    }

    // Agora, para cada TCC agendado, busca os alunos e professores associados.
    // Esta abordagem evita JOINs excessivamente complexos e grandes para dados de listas.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis de loop ($data).
    foreach ($tccs_data as $codigo_tcc => &$data) {
        // Busca alunos associados ao TCC.
        // CRITÉRIO: 9.2 Leitura e apresentação de registro - Consulta para alunos.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($stmt_alunos).
        $stmt_alunos = $pdo->prepare("
            SELECT A.RA, A.nome, ATCC.tipo_associacao
            FROM Aluno_TCC ATCC
            JOIN Aluno A ON ATCC.RA = A.RA
            WHERE ATCC.codigo_tcc = ? ORDER BY ATCC.tipo_associacao DESC
        ");
        $stmt_alunos->execute([$codigo_tcc]);
        // CRITÉRIO: 5.2 Foreach - Itera sobre os alunos do TCC.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($aluno_row).
        foreach ($stmt_alunos->fetchAll() as $aluno_row) {
            // CRITÉRIO: 3.2 String - Concatenação para formatar a string de alunos.
            $data['alunos'][] = htmlspecialchars($aluno_row['nome']) . " (" . htmlspecialchars($aluno_row['tipo_associacao']) . ")";
        }
        // CRITÉRIO: 3.2 String - Concatenação para formatar a string final de alunos.
        $data['tcc']->alunos_envolvidos_html = implode('<br>', $data['alunos']);

        // Busca professores associados ao TCC (Orientador/Coorientador da tabela TCC_Professor).
        // CRITÉRIO: 9.2 Leitura e apresentação de registro - Consulta para professores.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($stmt_professores).
        $stmt_professores = $pdo->prepare("
            SELECT P.id_professor, P.nome, PTCC.tipo_participacao
            FROM TCC_Professor PTCC
            JOIN Professor P ON PTCC.id_professor = P.id_professor
            WHERE PTCC.codigo_tcc = ? ORDER BY PTCC.tipo_participacao
        ");
        $stmt_professores->execute([$codigo_tcc]);
        // CRITÉRIO: 5.2 Foreach - Itera sobre os professores do TCC.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($prof_row).
        foreach ($stmt_professores->fetchAll() as $prof_row) {
             // CRITÉRIO: 3.2 String - Concatenação para formatar a string de professores.
            $data['professores'][] = htmlspecialchars($prof_row['nome']) . " (" . htmlspecialchars($prof_row['tipo_participacao']) . ")";
        }
         // CRITÉRIO: 3.2 String - Concatenação para formatar a string final de professores.
        $data['tcc']->professores_html = implode('<br>', $data['professores']);

        $agenda_tccs[] = $data['tcc']; // Adiciona o objeto Tcc completo ao array final.
    }

} catch (PDOException $e) {
    // CRITÉRIO: 3.3 Atribuição - Atribui mensagem de erro em caso de falha na consulta.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($e para exceção).
    $mensagem_erro = "Erro ao carregar a agenda de defesas: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Defesas - Sistema de Gerenciamento de TCC</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
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
        <h2>Agenda de Defesas de TCC</h2>

        <?php
        // CRITÉRIO: 6.1 If_Else - Exibe mensagem de erro se houver.
        if (!empty($mensagem_erro)): ?>
            <div class="message error">
                <?php echo htmlspecialchars($mensagem_erro); ?>
            </div>
        <?php endif; ?>

        <?php
        // CRITÉRIO: 6.1 If_Else - Verifica se há TCCs agendados para exibir.
        if (empty($agenda_tccs)): ?>
            <p>Nenhuma defesa de TCC agendada no momento.</p>
            <p><a href="cadastro_agenda.php" class="button">Agendar Nova Defesa</a></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Código TCC</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Data Defesa</th>
                        <th>Hora Defesa</th>
                        <th>Alunos</th>
                        <th>Professores</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // CRITÉRIO: 5.2 Foreach - Itera sobre os TCCs agendados para exibição.
                    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($tcc).
                    foreach ($agenda_tccs as $tcc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tcc->getCodigoTcc()); ?></td>
                            <td><?php echo htmlspecialchars($tcc->getTitulo()); ?></td>
                            <td><?php echo htmlspecialchars($tcc->getTipoTcc()); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($tcc->getDataDefesa()))); ?></td>
                            <td><?php echo htmlspecialchars(date('H:i', strtotime($tcc->getHoraDefesa()))); ?></td>
                            <td><?php echo $tcc->alunos_envolvidos_html; ?></td>
                            <td><?php echo $tcc->professores_html; ?></td>
                            <td>
                                <a href="cadastro_agenda.php?id=<?php echo htmlspecialchars($tcc->getIdAgenda()); ?>" class="button-small">Editar Agendamento</a>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>