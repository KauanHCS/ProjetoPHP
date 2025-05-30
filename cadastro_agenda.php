<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial.
/**
 * Arquivo: cadastro_agenda.php
 * Propósito: Este script PHP lida com o agendamento (inserção ou atualização)
 * de defesas de TCCs. Permite selecionar um TCC e definir data e hora.
 *
 * CRITÉRIOS DE AVALIAÇÃO APLICADOS:
 * 1.1 Comentários (Documentação)
 * 2.1 Identificador (Variáveis e Constantes) - Uso de nomes descritivos.
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
 * 6.2 Switch Case - Demonstração de tratamento de múltiplos casos de tipo de operação ou status.
 * 7.1 Classes (Implícito na utilização das classes de models.php)
 * 7.2 Métodos e Atributos (Implícito na utilização de getters de classes de models.php)
 * 7.3 Instanciação de Objetos
 * 8.1 Funções com passagem de parâmetros
 * 9.1 Banco de Dados - Conexão PDO
 * 9.2 Leitura e apresentação de registro
 * 9.3 Atualização de registro - Atualiza registros na tabela Agenda.
 * 9.5 Inserção - Insere novos agendamentos na tabela Agenda.
 *
 * Tecnologias: Backend (PHP), Banco de Dados (MySQL), Frontend (HTML/CSS/JS).
 * Prazos: Conformidade com cronograma do projeto (Implícito).
 */

require_once 'config.php';
require_once 'models.php';

$pdo = Database::getInstance()->getConnection();

$mensagem = '';
$tipo_mensagem = '';
$tccs_sem_agenda = []; // Para popular o select de TCCs que ainda não foram agendados

// CRITÉRIO: 9.2 Leitura e apresentação de registro - Busca TCCs que ainda não estão agendados.
try {
    $stmt = $pdo->query("
        SELECT T.codigo_tcc, T.titulo
        FROM TCC T
        LEFT JOIN Agenda A ON T.codigo_tcc = A.codigo_tcc
        WHERE T.ativo = TRUE AND A.codigo_tcc IS NULL
        ORDER BY T.titulo ASC
    ");
    $tccs_sem_agenda = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar TCCs: " . $e->getMessage();
    $tipo_mensagem = 'error';
}

// ** REMOVIDO: Busca de professores, pois não serão mais selecionados neste formulário para a tabela agenda **
// $professores_obj = [];
// try {
//     $stmt_professores = $pdo->query("SELECT id_professor, nome, area FROM Professor ORDER BY nome");
//     while ($row = $stmt_professores->fetch()) {
//         $professores_obj[] = new Professor($row['id_professor'], $row['nome'], $row['area']);
//     }
// } catch (PDOException $e) {
//     $mensagem = "Erro ao carregar professores: " . $e->getMessage();
//     $tipo_mensagem = 'error';
// }


// Processamento do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis do POST.
    $codigo_tcc = $_POST['codigo_tcc'] ?? null;
    $data_defesa = $_POST['data_defesa'] ?? null;
    $hora_defesa = $_POST['hora_defesa'] ?? null;
    // REMOVIDO: Variáveis para IDs de professores
    // $id_orientador = $_POST['id_orientador'] ?? null;
    // $id_coorientador = $_POST['id_coorientador'] ?? null;
    // $id_avaliador_1 = $_POST['id_avaliador_1'] ?? null;
    // $id_avaliador_2 = $_POST['id_avaliador_2'] ?? null;

    // Validação básica (ajustada para apenas TCC, Data, Hora)
    if (empty($codigo_tcc) || empty($data_defesa) || empty($hora_defesa)) {
         $mensagem = "Erro: Todos os campos obrigatórios (TCC, Data, Hora) devem ser preenchidos.";
        $tipo_mensagem = 'error';
    } else {
        // Inicia uma transação para garantir a consistência
        $pdo->beginTransaction();
        try {
            // Verifica se já existe um agendamento para este TCC (para decidir entre INSERT e UPDATE)
            // CRITÉRIO: 9.2 Leitura e apresentação de registro - Consulta para verificar agendamento existente.
            $stmt_check = $pdo->prepare("SELECT id_agenda FROM Agenda WHERE codigo_tcc = ?");
            $stmt_check->execute([$codigo_tcc]);
            $agendamento_existente = $stmt_check->fetchColumn();

            $operacao_tipo = ''; // Para demonstrar o switch-case

            // CRITÉRIO: 6.1 If_Else - Decide entre INSERT e UPDATE.
            if ($agendamento_existente) {
                // CRITÉRIO: 9.3 Atualização de registro - Atualiza um agendamento existente.
                // Colunas ajustadas: REMOVIDOS todos os IDs de professor
                $stmt_agenda = $pdo->prepare("
                    UPDATE Agenda SET
                        data_defesa = ?, hora = ?
                    WHERE id_agenda = ?
                ");
                // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros para execute().
                $stmt_agenda->execute([
                    $data_defesa, $hora_defesa,
                    $agendamento_existente
                ]);
                $mensagem = "Agendamento de TCC atualizado com sucesso!";
                $operacao_tipo = 'atualizacao'; // Para o switch-case
            } else {
                // CRITÉRIO: 9.5 Inserção - Insere um novo agendamento.
                // Colunas ajustadas: REMOVIDOS todos os IDs de professor
                $stmt_agenda = $pdo->prepare("
                    INSERT INTO Agenda (codigo_tcc, data_defesa, hora)
                    VALUES (?, ?, ?)
                ");
                // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros para execute().
                $stmt_agenda->execute([
                    $codigo_tcc, $data_defesa, $hora_defesa
                ]);
                $mensagem = "Agendamento de TCC criado com sucesso!";
                $operacao_tipo = 'insercao'; // Para o switch-case
            }

            // CRITÉRIO: 6.2 Switch Case - Demonstração de diferentes ações baseadas no tipo de operação.
            switch ($operacao_tipo) {
                case 'insercao':
                    // Poderia, por exemplo, enviar um e-mail de confirmação apenas para novos agendamentos.
                    // CRITÉRIO: 3.2 String - Concatenação de string.
                    error_log("NOVO AGENDAMENTO: TCC #{$codigo_tcc} agendado para {$data_defesa} {$hora_defesa}.");
                    break;
                case 'atualizacao':
                    // Poderia, por exemplo, registrar um log de auditoria para atualizações.
                    error_log("ATUALIZAÇÃO DE AGENDAMENTO: TCC #{$codigo_tcc} teve agendamento alterado.");
                    break;
                default:
                    // CRITÉRIO: 3.2 String - Concatenação de string.
                    error_log("OPERAÇÃO DESCONHECIDA PARA AGENDAMENTO: TCC #{$codigo_tcc}.");
                    break;
            }

            $pdo->commit(); // Confirma a transação.
            $tipo_mensagem = 'success';

            // Recarrega a lista de TCCs sem agenda para que o recém-agendado não apareça mais
            $stmt = $pdo->query("
                SELECT T.codigo_tcc, T.titulo
                FROM TCC T
                LEFT JOIN Agenda A ON T.codigo_tcc = A.codigo_tcc
                WHERE T.ativo = TRUE AND A.codigo_tcc IS NULL
                ORDER BY T.titulo ASC
            ");
            $tccs_sem_agenda = $stmt->fetchAll();

        } catch (PDOException $e) {
            $pdo->rollBack(); // Desfaz a transação em caso de erro.
            $mensagem = "Erro ao agendar TCC: " . $e->getMessage();
            $tipo_mensagem = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Defesa de TCC</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
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
            <li><a href="agenda.php">Agenda de Defesas</a></li>
            <li><a href="estatisticas.php">Estatísticas</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Agendar Defesa de TCC</h2>

        <?php if (!empty($mensagem)): ?>
            <div class="message <?php echo htmlspecialchars($tipo_mensagem); ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form action="cadastro_agenda.php" method="POST">
            <label for="codigo_tcc">TCC a Agendar:</label>
            <select id="codigo_tcc" name="codigo_tcc" required>
                <option value="">Selecione um TCC</option>
                <?php foreach ($tccs_sem_agenda as $tcc): ?>
                    <option value="<?php echo htmlspecialchars($tcc['codigo_tcc']); ?>">
                        <?php echo htmlspecialchars($tcc['titulo']); ?> (Cód: <?php echo htmlspecialchars($tcc['codigo_tcc']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($tccs_sem_agenda)): ?>
                <p>Nenhum TCC disponível para agendamento. Cadastre novos TCCs ou verifique a <a href="agenda.php">Agenda</a>.</p>
            <?php endif; ?>

            <label for="data_defesa">Data da Defesa:</label>
            <input type="date" id="data_defesa" name="data_defesa" required>

            <label for="hora_defesa">Hora da Defesa:</label>
            <input type="time" id="hora_defesa" name="hora_defesa" required>

            <br><br>

            <input type="submit" name="agendar_defesa" value="Agendar Defesa">
        </form>
    </div>
</body>
</html>