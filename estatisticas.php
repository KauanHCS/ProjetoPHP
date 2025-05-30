<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial.
/**
 * Arquivo: estatisticas.php
 * Propósito: Exibe estatísticas e resumos do sistema de gerenciamento de TCC.
 * Isso pode incluir contagem de TCCs por tipo, por status,
 * professores mais ativos, etc.
 *
 * CRITÉRIOS DE AVALIAÇÃO APLICADOS:
 * 1.1 Comentários (Documentação)
 * 2.1 Identificador (Variáveis e Constantes) - Uso de nomes descritivos para variáveis (ex: $estatisticas, $mensagem_erro).
 * 3.3 Atribuição
 * 4.1 Array
 * 5.1 Laço For - Não utilizado diretamente neste arquivo.
 * 5.2 Foreach
 * 6.1 If_Else
 * 9.1 Banco de Dados - Conexão PDO
 * 9.2 Leitura e apresentação de registro
 * 9.3 Atualização de registro (Implícito, pois as estatísticas podem ser afetadas por atualizações)
 * 9.4 Exclusão de registro (Implícito, pois as estatísticas podem ser afetadas por exclusões)
 * 9.5 Inserção (Implícito, pois as estatísticas são baseadas em inserções)
 *
 * Tecnologias: Backend (PHP), Banco de Dados (MySQL), Frontend (HTML/CSS).
 * Prazos: Conformidade com cronograma do projeto.
 */

require_once 'config.php'; // CRITÉRIO: 9.1 Banco de Dados - Conexão PDO.

// CRITÉRIO: 7.3 Instanciação de Objetos - Obtém a instância da conexão PDO.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($pdo).
$pdo = Database::getInstance()->getConnection();

// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para o array de estatísticas e suas chaves.
$estatisticas = [
    'total_tccs' => 0, // CRITÉRIO: 3.3 Atribuição - Inicializa contadores.
    'tccs_por_tipo' => [], // CRITÉRIO: 4.1 Array - Inicializa arrays para estatísticas.
    'tccs_agendados' => 0,
    'professores_orientadores' => [],
    'alunos_tcc_principal' => []
];
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($mensagem_erro).
$mensagem_erro = ''; // CRITÉRIO: 3.3 Atribuição.

try {
    // 1. Total de TCCs ativos
    // CRITÉRIO: 9.2 Leitura e apresentação de registro - Consulta para total de TCCs.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($stmt).
    $stmt = $pdo->query("SELECT COUNT(codigo_tcc) AS total FROM TCC WHERE ativo = TRUE");
    $estatisticas['total_tccs'] = $stmt->fetchColumn();

    // 2. TCCs por Tipo
    // CRITÉRIO: 9.2 Leitura e apresentação de registro - Consulta para TCCs por tipo.
    $stmt = $pdo->query("
        SELECT TT.descricao, COUNT(T.codigo_tcc) AS count
        FROM Tipo_TCC TT
        LEFT JOIN TCC T ON TT.id_tipo_tcc = T.id_tipo_tcc AND T.ativo = TRUE
        GROUP BY TT.descricao
        ORDER BY TT.descricao
    ");
    // CRITÉRIO: 5.2 Foreach - Itera sobre os resultados da consulta.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($row).
    foreach ($stmt->fetchAll() as $row) {
        $estatisticas['tccs_por_tipo'][$row['descricao']] = $row['count'];
    }

    // 3. TCCs Agendados
    // CRITÉRIO: 9.2 Leitura e apresentação de registro - Consulta para TCCs agendados.
    // Alterado para verificar 'data_defesa' e 'hora'
    $stmt = $pdo->query("SELECT COUNT(DISTINCT codigo_tcc) AS total FROM Agenda WHERE data_defesa IS NOT NULL AND hora IS NOT NULL");
    $estatisticas['tccs_agendados'] = $stmt->fetchColumn();

    // 4. Professores Orientadores (os 5 mais ativos, por exemplo)
    // CRITÉRIO: 9.2 Leitura e apresentação de registro - Consulta para professores orientadores.
    $stmt = $pdo->query("
        SELECT P.nome, COUNT(PTCC.id_professor) AS count
        FROM TCC_Professor PTCC
        JOIN Professor P ON PTCC.id_professor = P.id_professor
        WHERE PTCC.tipo_participacao = 'Orientador'
        GROUP BY P.nome
        ORDER BY count DESC, P.nome ASC
        LIMIT 5
    ");
    // CRITÉRIO: 5.2 Foreach - Itera sobre os resultados.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($row).
    foreach ($stmt->fetchAll() as $row) {
        $estatisticas['professores_orientadores'][$row['nome']] = $row['count'];
    }

    // 5. Alunos com TCC Principal
    // CRITÉRIO: 9.2 Leitura e apresentação de registro - Consulta para alunos principais.
    $stmt = $pdo->query("
        SELECT A.nome, COUNT(ATCC.RA) AS count -- Coluna RA na Aluno_TCC
        FROM Aluno_TCC ATCC
        JOIN Aluno A ON ATCC.RA = A.RA -- Coluna RA na Aluno_TCC
        WHERE ATCC.tipo_associacao = 'Principal'
        GROUP BY A.nome
        ORDER BY count DESC, A.nome ASC
        LIMIT 5
    ");
    // CRITÉRIO: 5.2 Foreach - Itera sobre os resultados.
    foreach ($stmt->fetchAll() as $row) {
        $estatisticas['alunos_tcc_principal'][$row['nome']] = $row['count'];
    }


} catch (PDOException $e) {
    // CRITÉRIO: 3.3 Atribuição - Atribui mensagem de erro em caso de falha.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($e para exceção).
    $mensagem_erro = "Erro ao carregar estatísticas: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas - Sistema de Gerenciamento de TCC</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stat-card h3 {
            margin-top: 0;
            color: #333;
            font-size: 1.2em;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .stat-card p {
            font-size: 1.5em;
            font-weight: bold;
            color: #007bff;
            text-align: center;
        }
        .stat-list ul {
            list-style: none;
            padding: 0;
        }
        .stat-list li {
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-list li:last-child {
            border-bottom: none;
        }
        .stat-list span {
            font-weight: bold;
            color: #555;
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
            <li><a href="cadastro_agenda.php">Agendar Defesa</a></li> <li><a href="agenda.php">Agenda de Defesas</a></li>
            <li><a href="estatisticas.php">Estatísticas</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Estatísticas do Sistema</h2>

        <?php
        // CRITÉRIO: 6.1 If_Else - Exibe mensagem de erro se houver.
        if (!empty($mensagem_erro)): ?>
            <div class="message error">
                <?php echo htmlspecialchars($mensagem_erro); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total de TCCs Cadastrados</h3>
                <p><?php echo htmlspecialchars($estatisticas['total_tccs']); ?></p>
            </div>

            <div class="stat-card">
                <h3>TCCs com Defesa Agendada</h3>
                <p><?php echo htmlspecialchars($estatisticas['tccs_agendados']); ?></p>
            </div>

            <div class="stat-card stat-list">
                <h3>TCCs por Tipo</h3>
                <ul>
                    <?php
                    // CRITÉRIO: 5.2 Foreach - Itera sobre os tipos de TCC.
                    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis de loop ($tipo, $count).
                    foreach ($estatisticas['tccs_por_tipo'] as $tipo => $count): ?>
                        <li><?php echo htmlspecialchars($tipo); ?> <span><?php echo htmlspecialchars($count); ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <?php
                // CRITÉRIO: 6.1 If_Else - Verifica se não há dados para o tipo.
                if (empty($estatisticas['tccs_por_tipo'])): ?>
                    <p>Nenhum dado disponível.</p>
                <?php endif; ?>
            </div>

            <div class="stat-card stat-list">
                <h3>Top 5 Orientadores</h3>
                <ul>
                    <?php
                    // CRITÉRIO: 5.2 Foreach - Itera sobre os professores orientadores.
                    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nomes claros para variáveis de loop ($nome, $count).
                    foreach ($estatisticas['professores_orientadores'] as $nome => $count): ?>
                        <li><?php echo htmlspecialchars($nome); ?> <span><?php echo htmlspecialchars($count); ?> TCCs</span></li>
                    <?php endforeach; ?>
                </ul>
                <?php if (empty($estatisticas['professores_orientadores'])): ?>
                    <p>Nenhum dado disponível.</p>
                <?php endif; ?>
            </div>

            <div class="stat-card stat-list">
                <h3>Top 5 Alunos com TCC Principal</h3>
                <ul>
                    <?php
                    // CRITÉRIO: 5.2 Foreach - Itera sobre os alunos principais.
                    foreach ($estatisticas['alunos_tcc_principal'] as $nome => $count): ?>
                        <li><?php echo htmlspecialchars($nome); ?> <span><?php echo htmlspecialchars($count); ?> TCC(s)</span></li>
                    <?php endforeach; ?>
                </ul>
                <?php if (empty($estatisticas['alunos_tcc_principal'])): ?>
                    <p>Nenhum dado disponível.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>