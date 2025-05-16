<?php include 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Banca de TCC</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Cadastrar Nova Banca de TCC</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="agenda.php">Agenda</a></li>
                <li><a href="cadastro.php">Cadastrar Banca</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="form-cadastro">
            <h2>Preencha os dados da banca</h2>
            <form action="processa_cadastro.php" method="POST">
                <div class="form-group">
                    <label for="titulo">Título do TCC:</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>

                <div class="form-group">
                    <label for="aluno">Nome do Aluno:</label>
                    <input type="text" id="aluno" name="aluno" required>
                </div>

                <div class="form-group">
                    <label for="orientador">Orientador:</label>
                    <input type="text" id="orientador" name="orientador" required>
                </div>
                <!-- Adicionar após o campo do orientador -->
                <div class="form-group">
                    <label for="tipo_tcc">Tipo de TCC:</label>
                    <select id="tipo_tcc" name="tipo_tcc" required>
                        <option value="1">Monografia</option>
                        <option value="2">Estudo de Caso</option>
                        <option value="3">Revisão Bibliográfica</option>
                        <option value="4">Projeto Experimental</option>
                        <option value="5">Desenvolvimento de Software</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data">Data da Apresentação:</label>
                    <input type="date" id="data" name="data_apresentacao" required>
                </div>

                <div class="form-group">
                    <label for="hora">Hora da Apresentação:</label>
                    <input type="time" id="hora" name="hora_apresentacao" required>
                </div>

                <div class="form-group">
                    <label for="local">Local:</label>
                    <input type="text" id="local" name="local" required>
                </div>

                <div class="form-group">
                    <label for="membros">Membros da Banca (separados por vírgula):</label>
                    <textarea id="membros" name="membros_banca" required></textarea>
                </div>

                <button type="submit" class="btn">Cadastrar Banca</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Sistema de Bancas de TCC</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>