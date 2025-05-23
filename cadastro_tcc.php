<?php
require_once 'config.php'; // Inclui o arquivo de configuração do banco de dados

$mensagem = '';
$tipo_mensagem = '';

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $conexao->real_escape_string($_POST['titulo']);
    $data_cadastro = $conexao->real_escape_string($_POST['data_cadastro']);
    $id_tipo_tcc = (int)$_POST['id_tipo_tcc'];
    $ra_aluno = $conexao->real_escape_string($_POST['ra_aluno']);
    
    // Professores selecionados e seus tipos de participação
    $professores_selecionados = $_POST['professores'] ?? [];
    $tipos_participacao = $_POST['tipo_participacao'] ?? [];

    // 1. Inserir o TCC
    $sql_tcc = "INSERT INTO TCC (titulo, data_cadastro, id_tipo_tcc) VALUES ('$titulo', '$data_cadastro', $id_tipo_tcc)";

    if ($conexao->query($sql_tcc) === TRUE) {
        $codigo_tcc = $conexao->insert_id; // Pega o ID do TCC recém-inserido
        $sucesso = true;

        // 2. Atualizar o aluno com o código do TCC
        $sql_update_aluno = "UPDATE Aluno SET codigo_tcc = $codigo_tcc WHERE RA = '$ra_aluno'";
        if ($conexao->query($sql_update_aluno) !== TRUE) {
            $sucesso = false;
            $mensagem .= "Erro ao associar TCC ao aluno: " . $conexao->error . "<br>";
        }

        // 3. Inserir os professores associados
        if ($sucesso && !empty($professores_selecionados)) {
            foreach ($professores_selecionados as $indice => $id_professor) {
                $id_professor = (int)$id_professor;
                $tipo_part = $conexao->real_escape_string($tipos_participacao[$indice]);

                $sql_prof_tcc = "INSERT INTO TCC_Professor (codigo_tcc, id_professor, tipo_participacao) VALUES ($codigo_tcc, $id_professor, '$tipo_part')";
                if ($conexao->query($sql_prof_tcc) !== TRUE) {
                    $sucesso = false;
                    $mensagem .= "Erro ao associar professor (ID: $id_professor, Participação: $tipo_part): " . $conexao->error . "<br>";
                }
            }
        }
        
        if ($sucesso) {
            $mensagem = "TCC cadastrado com sucesso! Código: " . $codigo_tcc;
            $tipo_mensagem = 'success';
        } else {
            $tipo_mensagem = 'error';
            // Se houve algum erro em passos secundários, a mensagem já estará populada
            if (empty($mensagem)) {
                $mensagem = "Erro ao cadastrar TCC: " . $conexao->error;
            }
        }

    } else {
        $mensagem = "Erro ao cadastrar TCC: " . $conexao->error;
        $tipo_mensagem = 'error';
    }
}

// Busca os tipos de TCC para preencher o select
$tipos_tcc = [];
$resultado_tipos = $conexao->query("SELECT id_tipo_tcc, descricao FROM Tipo_TCC");
if ($resultado_tipos) {
    while ($linha = $resultado_tipos->fetch_assoc()) {
        $tipos_tcc[] = $linha;
    }
}

// Busca os alunos para preencher o select (apenas alunos sem TCC associado, ou todos, dependendo da regra)
// Vamos buscar todos para simplificar, mas você pode refinar isso.
$alunos = [];
$resultado_alunos = $conexao->query("SELECT RA, nome FROM Aluno ORDER BY nome");
if ($resultado_alunos) {
    while ($linha = $resultado_alunos->fetch_assoc()) {
        $alunos[] = $linha;
    }
}

// Busca os professores para preencher o select
$professores = [];
$resultado_professores = $conexao->query("SELECT id_professor, nome FROM Professor ORDER BY nome");
if ($resultado_professores) {
    while ($linha = $resultado_professores->fetch_assoc()) {
        $professores[] = $linha;
    }
}

// Tipos de participação para os professores
$tipos_participacao_professor = ['Orientador', 'Coorientador', 'Professor Convidado 1', 'Professor Convidado 2'];

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Novo TCC</title>
    <link rel="stylesheet" href="css/style.css">
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
        </ul>
    </nav>
    <div class="container">
        <h2>Cadastrar Novo TCC</h2>

        <?php if (!empty($mensagem)): ?>
            <div class="message <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="cadastro_tcc.php" method="POST">
            <label for="titulo">Título do TCC:</label>
            <input type="text" id="titulo" name="titulo" required>

            <label for="data_cadastro">Data de Cadastro:</label>
            <input type="date" id="data_cadastro" name="data_cadastro" value="<?php echo date('Y-m-d'); ?>" required>

            <label for="id_tipo_tcc">Tipo de TCC:</label>
            <select id="id_tipo_tcc" name="id_tipo_tcc" required>
                <option value="">Selecione o Tipo</option>
                <?php foreach ($tipos_tcc as $tipo): ?>
                    <option value="<?php echo $tipo['id_tipo_tcc']; ?>"><?php echo $tipo['descricao']; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="ra_aluno">Aluno Responsável:</label>
            <select id="ra_aluno" name="ra_aluno" required>
                <option value="">Selecione o Aluno</option>
                <?php foreach ($alunos as $aluno): ?>
                    <option value="<?php echo $aluno['RA']; ?>"><?php echo $aluno['nome'] . ' (' . $aluno['RA'] . ')'; ?></option>
                <?php endforeach; ?>
            </select>

            <h3>Professores Envolvidos:</h3>
            <div id="professores-container">
                <div class="professor-entry">
                    <label>Professor:</label>
                    <select name="professores[]">
                        <option value="">Selecione o Professor</option>
                        <?php foreach ($professores as $prof): ?>
                            <option value="<?php echo $prof['id_professor']; ?>"><?php echo $prof['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Tipo de Participação:</label>
                    <select name="tipo_participacao[]">
                        <?php foreach ($tipos_participacao_professor as $tipo_part): ?>
                            <option value="<?php echo $tipo_part; ?>"><?php echo $tipo_part; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" onclick="removeProfessor(this)">Remover</button>
                </div>
            </div>
            <button type="button" onclick="addProfessor()">Adicionar Mais Professor</button>
            <br><br>

            <input type="submit" value="Cadastrar TCC">
        </form>
    </div>

    <script>
        function addProfessor() {
            const container = document.getElementById('professores-container');
            const originalEntry = container.querySelector('.professor-entry');
            const newEntry = originalEntry.cloneNode(true); // Clona o primeiro elemento

            // Limpa os valores selecionados do novo elemento clonado
            newEntry.querySelector('select[name="professores[]"]').value = "";
            newEntry.querySelector('select[name="tipo_participacao[]"]').value = "Orientador"; // Define um padrão

            // Adiciona o novo elemento ao container
            container.appendChild(newEntry);
        }

        function removeProfessor(button) {
            const container = document.getElementById('professores-container');
            // Só remove se houver mais de um professor
            if (container.children.length > 1) {
                button.closest('.professor-entry').remove();
            } else {
                alert('É necessário ter pelo menos um professor associado.');
            }
        }
    </script>
</body>
</html>