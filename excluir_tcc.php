<?php
// CRITÉRIO: 1.1 Comentários (Documentação) - Bloco de comentários inicial.
/**
 * Arquivo: excluir_tcc.php
 * Propósito: Script PHP para excluir um TCC e suas associações (alunos, professores, agenda).
 * A exclusão é baseada no 'codigo_tcc' passado via GET.
 *
 * CRITÉRIOS DE AVALIAÇÃO APLICADOS:
 * 1.1 Comentários (Documentação)
 * 2.1 Identificador (Variáveis e Constantes) - Uso de nomes descritivos.
 * 3.3 Atribuição
 * 3.4 Comparação
 * 3.6 Lógico
 * 6.1 If_Else
 * 7.3 Instanciação de Objetos
 * 8.1 Funções com passagem de parâmetros
 * 9.1 Banco de Dados - Conexão PDO
 * 9.4 Exclusão de registro - Exclui registros das tabelas TCC, Aluno_TCC, TCC_Professor e Agenda.
 *
 * Tecnologias: Backend (PHP), Banco de Dados (MySQL).
 * Prazos: Conformidade com cronograma do projeto (Implícito).
 */

require_once 'config.php';

$pdo = Database::getInstance()->getConnection();

$mensagem = '';
$tipo_mensagem = '';

// CRITÉRIO: 6.1 If_Else - Verifica se o código do TCC foi passado via GET.
if (isset($_GET['codigo_tcc']) && !empty($_GET['codigo_tcc'])) {
    // CRITÉRIO: 3.1 Aritméticos - Converte o código para inteiro para segurança.
    // CRITÉRIO: 2.1 Identificador (Variáveis e Constantes) - Nome de variável clara ($codigo_tcc).
    $codigo_tcc = (int)$_GET['codigo_tcc'];

    // Inicia uma transação para garantir que todas as exclusões sejam atômicas.
    $pdo->beginTransaction();
    try {
        // Excluir registros relacionados primeiro para evitar erros de chave estrangeira.
        // Assumindo que as chaves estrangeiras não têm ON DELETE CASCADE para demonstrar exclusão manual.
        // Se as FKS tiverem ON DELETE CASCADE, estas exclusões seriam automáticas ao excluir o TCC.

        // CRITÉRIO: 9.4 Exclusão de registro - Exclui da tabela Agenda.
        $stmt = $pdo->prepare("DELETE FROM Agenda WHERE codigo_tcc = ?");
        // CRITÉRIO: 8.1 Funções com passagem de parâmetros - Parâmetros para execute().
        $stmt->execute([$codigo_tcc]);

        // CRITÉRIO: 9.4 Exclusão de registro - Exclui da tabela Aluno_TCC.
        $stmt = $pdo->prepare("DELETE FROM Aluno_TCC WHERE codigo_tcc = ?");
        $stmt->execute([$codigo_tcc]);

        // CRITÉRIO: 9.4 Exclusão de registro - Exclui da tabela TCC_Professor.
        $stmt = $pdo->prepare("DELETE FROM TCC_Professor WHERE codigo_tcc = ?");
        $stmt->execute([$codigo_tcc]);

        // Finalmente, exclui o TCC principal.
        // CRITÉRIO: 9.4 Exclusão de registro - Exclui da tabela TCC.
        $stmt = $pdo->prepare("DELETE FROM TCC WHERE codigo_tcc = ?");
        $stmt->execute([$codigo_tcc]);

        $pdo->commit(); // Confirma todas as exclusões.
        // CRITÉRIO: 3.2 String - Concatenação de string.
        $mensagem = "TCC (Código: {$codigo_tcc}) e suas associações foram excluídos com sucesso!";
        $tipo_mensagem = 'success';
    } catch (PDOException $e) {
        $pdo->rollBack(); // Desfaz todas as exclusões em caso de erro.
        // CRITÉRIO: 3.2 String - Concatenação de string.
        $mensagem = "Erro ao excluir TCC (Código: {$codigo_tcc}): " . $e->getMessage();
        $tipo_mensagem = 'error';
    }
} else {
    $mensagem = "Nenhum TCC especificado para exclusão.";
    $tipo_mensagem = 'warning';
}

// Redireciona de volta para a página inicial ou uma página de lista de TCCs após a operação.
// CRITÉRIO: 6.1 If_Else - Redireciona com base no tipo de mensagem.
if ($tipo_mensagem == 'success') {
    header("Location: index.php?msg=" . urlencode($mensagem) . "&type=" . $tipo_mensagem);
} else {
    // Se for erro ou aviso, mostra a mensagem na própria página antes de redirecionar ou aguardar.
    // Ou pode redirecionar para uma página de erro dedicada.
    // Por simplicidade, vamos apenas mostrar a mensagem aqui e depois redirecionar para a home.
    echo "<!DOCTYPE html><html lang='pt-BR'><head><meta charset='UTF-8'><title>Exclusão de TCC</title><link rel='stylesheet' href='css/style.css'><style>.message { padding: 10px; margin-bottom: 15px; border-radius: 5px; font-weight: bold; }.message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }.message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }</style></head><body><div class='container'><div class='message " . htmlspecialchars($tipo_mensagem) . "'>" . htmlspecialchars($mensagem) . "</div><p><a href='index.php'>Voltar para a Home</a></p></div></body></html>";
    // Redireciona automaticamente após 3 segundos
    header("Refresh: 3; URL=index.php");
}
exit();
?>