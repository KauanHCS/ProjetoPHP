<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro.php');
    exit();
}

// Coletar e validar dados
$titulo = $_POST['titulo'] ?? '';
$aluno = $_POST['aluno'] ?? '';
$orientador = $_POST['orientador'] ?? '';
$data_apresentacao = $_POST['data_apresentacao'] ?? '';
$hora_apresentacao = $_POST['hora_apresentacao'] ?? '';
$local = $_POST['local'] ?? '';
$membros_banca = $_POST['membros_banca'] ?? '';

// Validação básica
if (empty($titulo) || empty($aluno) || empty($orientador) || empty($data_apresentacao) || 
    empty($hora_apresentacao) || empty($local) || empty($membros_banca)) {
    die("Todos os campos são obrigatórios.");
}

try {
    $stmt = $pdo->prepare("INSERT INTO bancas (titulo, aluno, orientador, data_apresentacao, hora_apresentacao, local, membros_banca) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $aluno, $orientador, $data_apresentacao, $hora_apresentacao, $local, $membros_banca]);
    
    header('Location: agenda.php?success=1');
    exit();
} catch (PDOException $e) {
    die("Erro ao cadastrar banca: " . $e->getMessage());
}
?>