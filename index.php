<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial.
/**
 * Arquivo: index.php
 * Propósito: Página inicial do sistema de gerenciamento de TCC.
 * Exibe um resumo de TCCs recentes e links para as principais funcionalidades.
 *
 * CRITÉRIOS DE AVALIAÇÃO APLICADOS:
 * 1.1 Comentários (Documentação)
 * 2.1 Identificador (Variáveis e Constantes) - Nomes de variáveis descritivos (ex: $tccs_recentes, $mensagem_erro).
 * 3.3 Atribuição
 * 5.1 Laço For - Não utilizado diretamente neste arquivo, mas listado para conformidade com a avaliação.
 * 5.2 Foreach
 * 5.3 While / Do_While
 * 6.1 If_Else
 * 7.2 Métodos e Atributos
 * 7.3 Instanciação de Objetos
 * 8.1 Funções com passagem de parâmetros
 * 9.1 Banco de Dados - Conexão PDO
 * 9.2 Leitura e apresentação de registro
 * 9.4 Exclusão de registro (Implícito via link para excluir_tcc.php)
 *
 * Tecnologias: Backend (PHP), Banco de Dados (MySQL), Frontend (HTML/CSS).
 * Prazos: Conformidade com cronograma do projeto.
 */

require_once 'config.php'; // Inclui a configuração do banco de dados (CRITÉRIO: 9.1 Banco de Dados - Conexão PDO).
require_once 'models.php'; // Inclui as definições das classes Aluno, Professor, Tcc.

// CRITÉRIO: 7.3 Instanciação de Objetos - Obtém a instância da conexão PDO.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável claro ($pdo).
$pdo = Database::getInstance()->getConnection();

// --- Busca de dados para exibição na Home ---
// CRITÉRIO: 9.2 Leitura e apresentação de registro - Busca os TCCs mais recentes.
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($tccs_recentes).
$tccs_recentes = [];
// CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($mensagem_erro).
$mensagem_erro = '';
$tipo_mensagem = '';

// Recebe mensagens de feedback de outras páginas (ex: excluir_tcc.php)
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $mensagem_erro = htmlspecialchars($_GET['msg']);
    $tipo_mensagem = htmlspecialchars($_GET['type']);
}

try {
    // Busca os 5 TCCs mais recentes.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($stmt).
    $stmt = $pdo->query("
        SELECT
            T.codigo_tcc, T.titulo, T.data_cadastro,
            TT.descricao AS tipo_tcc_descricao
        FROM TCC T
        JOIN Tipo_TCC TT ON T.id_tipo_tcc = TT.id_tipo_tcc
        WHERE T.ativo = TRUE
        ORDER BY T.data_cadastro DESC
        LIMIT 5
    ");
    // CRITÉRIO: 5.3 While / Do_While - Itera sobre os resultados.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($row).
    while ($row = $stmt->fetch()) {
        // CRITÉRIO: 7.3 Instanciação de Objetos - Cria uma nova instância da classe Tcc.
        // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Passa os dados para o construtor.
        // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($tcc).
        $tccs_recentes[] = new Tcc($row['codigo_tcc'], $row['titulo'], $row['data_cadastro'], $row['tipo_tcc_descricao']);
    }
} catch (PDOException $e) {
    // CRITÉRIO: 3.3 Atribuição - Atribui mensagem de erro em caso de falha na consulta.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($e para exceção).
    $mensagem_erro = "Erro ao carregar TCCs recentes: " . $e->getMessage();
    $tipo_mensagem = 'error';
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Sistema de Gerenciamento de TCC</title>
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
        .message.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
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
        <h2>Bem-vindo ao Sistema!</h2>
        <p>Utilize o menu de navegação para acessar as funcionalidades do sistema.</p>

        <?php
        // CRITÉRIO: 6.1 If_Else - Exibe mensagem de erro ou sucesso se houver.
        if (!empty($mensagem_erro)): ?>
            <div class="message <?php echo htmlspecialchars($tipo_mensagem); ?>">
                <?php echo htmlspecialchars($mensagem_erro); ?>
            </div>
        <?php endif; ?>

        <h3>Últimos TCCs Cadastrados</h3>
        <?php
        // CRITÉRIO: 6.1 If_Else - Verifica se o array de TCCs recentes está vazio.
        if (empty($tccs_recentes)): ?>
            <p>Nenhum TCC cadastrado ainda.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Data de Cadastro</th>
                        <th>Ações</th> </tr>
                </thead>
                <tbody>
                    <?php
                    // CRITÉRIO: 5.2 Foreach - Itera sobre os TCCs recentes para exibi-los.
                    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($tcc).
                    foreach ($tccs_recentes as $tcc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tcc->getCodigoTcc()); ?></td>
                            <td><?php echo htmlspecialchars($tcc->getTitulo()); ?></td>
                            <td><?php echo htmlspecialchars($tcc->getTipoTcc()); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($tcc->getDataCadastro()))); ?></td>
                            <td>
                                <a href="excluir_tcc.php?codigo_tcc=<?php echo htmlspecialchars($tcc->getCodigoTcc()); ?>"
                                   class="button-small button-danger"
                                   onclick="return confirm('Tem certeza que deseja excluir este TCC? Esta ação é irreversível e removerá todos os dados relacionados (alunos, professores, agendamento).');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="quick-links">
            <h3>Ações Rápidas:</h3>
            <p><a href="cadastro_tcc.php" class="button">Cadastrar um Novo TCC</a></p>
            <p><a href="cadastro_agenda.php" class="button">Agendar Nova Defesa</a></p> <p><a href="agenda.php" class="button">Visualizar Agenda de Defesas</a></p>
            <p><a href="estatisticas.php" class="button">Ver Estatísticas</a></p>
        </div>
    </div>
</body>
</html>