<?php include 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Bancas de TCC</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Agenda de Bancas de TCC</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="agenda.php">Agenda</a></li>
                <li><a href="cadastro.php">Cadastrar Banca</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="agenda">
            <h2>Bancas Agendadas</h2>
            
            <div class="filtros">
                <form method="GET" action="agenda.php">
                    <div class="form-group">
                        <label for="data_filtro">Filtrar por data:</label>
                        <input type="date" id="data_filtro" name="data_filtro">
                        <button type="submit" class="btn">Filtrar</button>
                        <a href="agenda.php" class="btn">Limpar</a>
                    </div>
                </form>
            </div>

            <?php
            $where = '';
            if (isset($_GET['data_filtro']) && !empty($_GET['data_filtro'])) {
                $data_filtro = $_GET['data_filtro'];
                $where = " WHERE data_apresentacao = '{$data_filtro}'";
            }

            $stmt = $pdo->query("SELECT * FROM bancas {$where} ORDER BY data_apresentacao, hora_apresentacao");
            $bancas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($bancas) > 0) {
                echo '<div class="bancas-lista">';
                foreach ($bancas as $banca) {
                    echo '<div class="banca-item">';
                    echo '<h3>' . htmlspecialchars($banca['titulo']) . '</h3>';
                    echo '<p><strong>Aluno:</strong> ' . htmlspecialchars($banca['aluno']) . '</p>';
                    echo '<p><strong>Data:</strong> ' . date('d/m/Y', strtotime($banca['data_apresentacao'])) . '</p>';
                    echo '<p><strong>Hora:</strong> ' . htmlspecialchars($banca['hora_apresentacao']) . '</p>';
                    echo '<p><strong>Local:</strong> ' . htmlspecialchars($banca['local']) . '</p>';
                    echo '<a href="detalhes.php?id=' . $banca['id'] . '" class="btn">Detalhes</a>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p>Nenhuma banca encontrada.</p>';
            }
            ?>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Sistema de Bancas de TCC</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>