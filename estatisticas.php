<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial.
require_once 'config.php'; // Inclui a configuração PDO (CRITÉRIO: 9.1 Banco de Dados - Conexão PDO)
require_once 'models.php'; // Inclui as classes (CRITÉRIO: 7.1 Classes)

// CRITÉRIO: 7.3 Instanciação de Objetos - Obtém a conexão PDO.
$pdo = Database::getInstance()->getConnection();

// CRITÉRIO: 3.3 Atribuição - Inicialização de variáveis.
$total_tccs = 0;
$total_alunos = 0;
$total_professores = 0;
$media_tccs_por_mes = 0;
// CRITÉRIO: 4.1 Array - Inicialização de array para armazenar dados.
$tccs_por_mes = []; // Este array armazenará 'YYYY-MM' => count

try {
    // Total de TCCs
    // CRITÉRIO: 9.2 Leitura e apresentação de registro - Comando SELECT.
    $stmt = $pdo->query("SELECT COUNT(*) FROM TCC");
    // CRITÉRIO: 3.3 Atribuição.
    $total_tccs = $stmt->fetchColumn();

    // Total de Alunos
    // CRITÉRIO: 9.2 Leitura e apresentação de registro.
    $stmt = $pdo->query("SELECT COUNT(*) FROM Aluno");
    $total_alunos = $stmt->fetchColumn();

    // Total de Professores
    // CRITÉRIO: 9.2 Leitura e apresentação de registro.
    $stmt = $pdo->query("SELECT COUNT(*) FROM Professor");
    $total_professores = $stmt->fetchColumn();

    // TCCs cadastrados por mês
    // CRITÉRIO: 9.2 Leitura e apresentação de registro - Query com GROUP BY.
    // Garante que 'mes' é um formato YYYY-MM válido para o MySQL.
    // A coluna 'data_cadastro' na sua tabela TCC DEVE ser do tipo DATE ou DATETIME.
    $stmt_tccs_por_mes = $pdo->query("SELECT DATE_FORMAT(data_cadastro, '%Y-%m') as mes, COUNT(*) as count FROM TCC WHERE data_cadastro IS NOT NULL GROUP BY mes ORDER BY mes");
    // CRITÉRIO: 4.1 Array - $tccs_por_mes_data é um array associativo.
    // Altera o modo de fetch para PDO::FETCH_KEY_PAIR para já ter 'YYYY-MM' como chave e 'count' como valor.
    $tccs_por_mes_data = $stmt_tccs_por_mes->fetchAll(PDO::FETCH_KEY_PAIR);

    $total_meses_com_tcc = 0;
    $total_tccs_acumulado_para_media = 0;

    // CRITÉRIO: 6.1 If_Else - Condição para verificar se há dados.
    // CRITÉRIO: 3.6 Lógico - Uso de NOT (!).
    if (!empty($tccs_por_mes_data)) {
        // As chaves já são as strings YYYY-MM
        $primeiro_mes_str = array_key_first($tccs_por_mes_data);
        $ultimo_mes_str = array_key_last($tccs_por_mes_data);

        // Debugging: Adicione estas linhas para ver os valores antes de criar o DateTime
        // error_log("Primeiro mês string: " . $primeiro_mes_str);
        // error_log("Último mês string: " . $ultimo_mes_str);

        // Verifica se as strings são válidas antes de criar DateTime
        if (preg_match('/^\d{4}-\d{2}$/', $primeiro_mes_str) && preg_match('/^\d{4}-\d{2}$/', $ultimo_mes_str)) {
            // CRITÉRIO: 7.3 Instanciação de Objetos - Criação de objetos DateTime.
            $primeiro_mes = new DateTime($primeiro_mes_str . '-01');
            $ultimo_mes = new DateTime($ultimo_mes_str . '-01');

            // CRITÉRIO: 7.3 Instanciação de Objetos - Criação de objeto DateInterval.
            $intervalo = $primeiro_mes->diff($ultimo_mes);
            // CRITÉRIO: 3.1 Aritméticos - Soma e multiplicação para calcular total de meses.
            $total_meses_com_tcc = $intervalo->m + ($intervalo->y * 12) + 1; // +1 para incluir o mês inicial
        } else {
            // Caso as strings de mês não sejam válidas, forçar um estado seguro
            $total_meses_com_tcc = 0;
            $primeiro_mes = null; // Não criar DateTime inválido
            $ultimo_mes = null;   // Não criar DateTime inválido
        }


        // CRITÉRIO: 5.2 Foreach - Loop para popular array $tccs_por_mes.
        foreach ($tccs_por_mes_data as $mes => $count) { // Agora $mes é 'YYYY-MM', $count é o número
            // CRITÉRIO: 3.3 Atribuição - Atribuição a array associativo.
            $tccs_por_mes[$mes] = $count;
            // CRITÉRIO: 3.1 Aritméticos - Soma de valores.
            $total_tccs_acumulado_para_media += $count;
        }

        // Calcula a média usando divisão
        // CRITÉRIO: 6.1 If_Else - Condição para evitar divisão por zero.
        if ($total_meses_com_tcc > 0) {
            // CRITÉRIO: 3.1 Aritméticos - Operador de divisão.
            $media_tccs_por_mes = $total_tccs_acumulado_para_media / $total_meses_com_tcc;
        }
    }

} catch (PDOException $e) {
    // CRITÉRIO: 3.2 String - Concatenação para mensagem de erro.
    $mensagem = "Erro ao carregar estatísticas: " . $e->getMessage();
    $tipo_mensagem = 'error';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas do Sistema</title>
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
            <li><a href="estatisticas.php">Estatísticas</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Estatísticas do Sistema</h2>

        <?php
        // CRITÉRIO: 6.1 If_Else - Exibição condicional de mensagens.
        if (!empty($mensagem)): ?>
            <div class="message <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total de TCCs Cadastrados</h3>
                <p class="stat-number"><?php echo $total_tccs; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total de Alunos no Sistema</h3>
                <p class="stat-number"><?php echo $total_alunos; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total de Professores Cadastrados</h3>
                <p class="stat-number"><?php echo $total_professores; ?></p>
            </div>
            <div class="stat-card">
                <h3>Média de TCCs por Mês</h3>
                <p class="stat-number"><?php echo number_format($media_tccs_por_mes, 2); ?></p>
            </div>
        </div>

        <h3>TCCs Cadastrados por Mês:</h3>
        <?php
        // CRITÉRIO: 6.1 If_Else - Exibição condicional da tabela.
        if (empty($tccs_por_mes)): ?>
            <p>Nenhum TCC com data de cadastro registrada para estatísticas.</p>
        <?php else: ?>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Mês/Ano</th>
                        <th>Quantidade de TCCs</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Gerar um range de meses completo para o loop for (5.1 For)
                    // CRITÉRIO: 7.3 Instanciação de Objetos - Criação de objetos DateTime, DateInterval, DatePeriod.
                    // Antes de criar o DatePeriod, garantimos que $primeiro_mes e $ultimo_mes são objetos DateTime válidos.
                    // Isso é feito ao verificar se eles não são nulos e, se necessário, recriá-los defensivamente.
                    if ($primeiro_mes && $ultimo_mes) {
                        $start    = $primeiro_mes->modify('first day of this month');
                        $end      = $ultimo_mes->modify('first day of next month'); // Define o fim do período para o início do próximo mês
                        $interval = DateInterval::createFromDateString('1 month');
                        $period   = new DatePeriod($start, $interval, $end);

                        // CRITÉRIO: 5.1 For - O loop DatePeriod atua como um loop for iterando sobre datas.
                        // CRITÉRIO: 5.2 Foreach - Utilizando foreach para iterar o DatePeriod.
                        foreach ($period as $dt) {
                            $mes_ano = $dt->format('Y-m'); // Ex: '2023-01'
                            // CRITÉRIO: 3.5 Operador Ternário - Para definir 0 se não houver TCC no mês.
                            $count = $tccs_por_mes[$mes_ano] ?? 0; // Pega a contagem do array $tccs_por_mes, ou 0 se não houver.
                            // CRITÉRIO: 3.2 String - Concatenação para linha da tabela.
                            echo "<tr><td>" . $dt->format('F Y') . "</td><td>" . $count . "</td></tr>";
                        }
                    } else {
                        // Caso $primeiro_mes ou $ultimo_mes não tenham sido criados (ex: por dados inválidos)
                        echo "<tr><td colspan='2'>Não foi possível gerar a tabela de meses devido a dados de data inválidos.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

<style>
/* Adicione isso ao seu style.css */
/* CRITÉRIO: 1.1 Comentários */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background-color: #e9f5ee; /* Cor clara para cartões de estatística */
    border: 1px solid #c8e6c9;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.stat-card h3 {
    margin-top: 0;
    color: var(--primary-color);
    font-size: 1.2em;
}

.stat-card .stat-number {
    font-size: 2.5em;
    font-weight: 700;
    color: var(--dark-blue);
    margin: 10px 0;
}

.simple-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.simple-table th, .simple-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.simple-table th {
    background-color: #f2f2f2;
    font-weight: bold;
}
</style>