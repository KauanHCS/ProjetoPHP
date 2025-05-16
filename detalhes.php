<?php 
include 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM bancas WHERE id = ?");
$stmt->execute([$id]);
$banca = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$banca) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Banca - <?php echo htmlspecialchars($banca['titulo']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Detalhes da Banca de TCC</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="agenda.php">Agenda</a></li>
                <li><a href="cadastro.php">Cadastrar Banca</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="detalhes-banca">
            <h2><?php echo htmlspecialchars($banca['titulo']); ?></h2>
            
            <div class="info-banca">
                <p><strong>Aluno:</strong> <?php echo htmlspecialchars($banca['aluno']); ?></p>
                <p><strong>Orientador:</strong> <?php echo htmlspecialchars($banca['orientador']); ?></p>
                <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($banca['data_apresentacao'])); ?></p>
                <p><strong>Hora:</strong> <?php echo htmlspecialchars($banca['hora_apresentacao']); ?></p>
                <p><strong>Local:</strong> <?php echo htmlspecialchars($banca['local']); ?></p>
                
                <h3>Membros da Banca:</h3>
                <ul>
                    <?php
                    $membros = explode(',', $banca['membros_banca']);
                    foreach ($membros as $membro) {
                        echo '<li>' . htmlspecialchars(trim($membro)) . '</li>';
                    }
                    ?>
                </ul>
            </div>

            <a href="agenda.php" class="btn">Voltar para Agenda</a>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Sistema de Bancas de TCC</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>