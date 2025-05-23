<?php
require_once 'config.php'; // Inclui o arquivo de configuração do banco de dados

$mensagem = '';
$tipo_mensagem = '';

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $conexao->real_escape_string($_POST['titulo']);
    $data_cadastro = $conexao->real_escape_string($_POST['data_cadastro']);
    $id_tipo_tcc = (int)$_POST['id_tipo_tcc'];
    
    // Alunos selecionados e seus tipos de associação
    $ra_aluno_principal = $conexao->real_escape_string($_POST['ra_aluno_principal']); // Aluno principal
    $colaboradores_selecionados = $_POST['colaboradores'] ?? []; // Colaboradores

    // Professores selecionados e seus tipos de participação
    $professores_selecionados = $_POST['professores'] ?? [];
    $tipos_participacao = $_POST['tipo_participacao'] ?? [];

    // 1. Inserir o TCC
    $sql_tcc = "INSERT INTO TCC (titulo, data_cadastro, id_tipo_tcc) VALUES ('$titulo', '$data_cadastro', $id_tipo_tcc)";

    if ($conexao->query($sql_tcc) === TRUE) {
        $codigo_tcc = $conexao->insert_id; // Pega o ID do TCC recém-inserido
        $sucesso = true;

        // 2. Inserir o Aluno Principal na Aluno_TCC
        $sql_aluno_principal = "INSERT INTO Aluno_TCC (codigo_tcc, RA, tipo_associacao) VALUES ($codigo_tcc, '$ra_aluno_principal', 'Principal')";
        if ($conexao->query($sql_aluno_principal) !== TRUE) {
            $sucesso = false;
            $mensagem .= "Erro ao associar Aluno Principal: " . $conexao->error . "<br>";
        }

        // 3. Inserir os Alunos Colaboradores na Aluno_TCC
        if ($sucesso && !empty($colaboradores_selecionados)) {
            $tipos_colaborador = ['Colaborador 1', 'Colaborador 2'];
            foreach ($colaboradores_selecionados as $indice => $ra_colaborador) {
                if (isset($tipos_colaborador[$indice])) { // Garante que não exceda os 2 tipos
                    $tipo_colab = $tipos_colaborador[$indice];
                    $sql_colaborador = "INSERT INTO Aluno_TCC (codigo_tcc, RA, tipo_associacao) VALUES ($codigo_tcc, '$ra_colaborador', '$tipo_colab')";
                    if ($conexao->query($sql_colaborador) !== TRUE) {
                        $sucesso = false;
                        $mensagem .= "Erro ao associar Aluno Colaborador $tipo_colab (RA: $ra_colaborador): " . $conexao->error . "<br>";
                    }
                }
            }
        }

        // 4. Inserir os professores associados (essa parte permanece igual)
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
            if (empty($mensagem)) {
                $mensagem = "Erro ao cadastrar TCC: " . $conexao->error;
            }
        }

    } else {
        $mensagem = "Erro ao cadastrar TCC: " . $conexao->error;
        $tipo_mensagem = 'error';
    }
}

// Busca os tipos de TCC, alunos e professores (permanece igual)
$tipos_tcc = [];
$resultado_tipos = $conexao->query("SELECT id_tipo_tcc, descricao FROM Tipo_TCC");
if ($resultado_tipos) {
    while ($linha = $resultado_tipos->fetch_assoc()) {
        $tipos_tcc[] = $linha;
    }
}

$alunos = [];
$resultado_alunos = $conexao->query("SELECT RA, nome FROM Aluno ORDER BY nome");
if ($resultado_alunos) {
    while ($linha = $resultado_alunos->fetch_assoc()) {
        $alunos[] = $linha;
    }
}

$professores = [];
$resultado_professores = $conexao->query("SELECT id_professor, nome FROM Professor ORDER BY nome");
if ($resultado_professores) {
    while ($linha = $resultado_professores->fetch_assoc()) {
        $professores[] = $linha;
    }
}

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

            <h3>Alunos Envolvidos:</h3>
            <label for="ra_aluno_principal">Aluno Principal:</label>
            <select id="ra_aluno_principal" name="ra_aluno_principal" required>
                <option value="">Selecione o Aluno Principal</option>
                <?php foreach ($alunos as $aluno): ?>
                    <option value="<?php echo $aluno['RA']; ?>"><?php echo $aluno['nome'] . ' (' . $aluno['RA'] . ')'; ?></option>
                <?php endforeach; ?>
            </select>

            <div id="colaboradores-container">
                <div class="colaborador-entry">
                    <label>Aluno Colaborador 1:</label>
                    <select name="colaboradores[]">
                        <option value="">Nenhum</option>
                        <?php foreach ($alunos as $aluno): ?>
                            <option value="<?php echo $aluno['RA']; ?>"><?php echo $aluno['nome'] . ' (' . $aluno['RA'] . ')'; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="button-secondary" onclick="removeColaborador(this)">Remover</button>
                </div>
                </div>
            <button type="button" class="button" onclick="addColaborador()" id="addColaboradorBtn">Adicionar Colaborador</button>
            <br><br>

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
                    <button type="button" class="button-secondary" onclick="removeProfessor(this)">Remover</button>
                </div>
            </div>
            <button type="button" class="button" onclick="addProfessor()">Adicionar Mais Professor</button>
            <br><br>

            <input type="submit" value="Cadastrar TCC">
        </form>
    </div>

    <script>
        // Funções para adicionar/remover professores (mantidas)
        function addProfessor() {
            const container = document.getElementById('professores-container');
            const originalEntry = container.querySelector('.professor-entry');
            const newEntry = originalEntry.cloneNode(true);
            newEntry.querySelector('select[name="professores[]"]').value = "";
            newEntry.querySelector('select[name="tipo_participacao[]"]').value = "Orientador";
            container.appendChild(newEntry);
        }

        function removeProfessor(button) {
            const container = document.getElementById('professores-container');
            if (container.children.length > 1) {
                button.closest('.professor-entry').remove();
            } else {
                alert('É necessário ter pelo menos um professor associado.');
            }
        }

        // --- Novas funções para adicionar/remover colaboradores ---
        let colaboradorCount = 1; // Começa em 1 porque já temos um campo para "Colaborador 1"

        // Função para adicionar colaborador
        function addColaborador() {
            if (colaboradorCount >= 2) {
                alert('Você só pode adicionar até 2 alunos colaboradores.');
                return;
            }
            colaboradorCount++;

            const container = document.getElementById('colaboradores-container');
            const originalEntry = container.querySelector('.colaborador-entry');
            const newEntry = originalEntry.cloneNode(true);

            newEntry.querySelector('label').innerText = `Aluno Colaborador ${colaboradorCount}:`;
            newEntry.querySelector('select[name="colaboradores[]"]').value = "";
            
            container.appendChild(newEntry);

            if (colaboradorCount >= 2) {
                document.getElementById('addColaboradorBtn').style.display = 'none'; // Esconde o botão se já tiver 2
            }
        }

        // Função para remover colaborador
        function removeColaborador(button) {
            const container = document.getElementById('colaboradores-container');
            if (container.children.length > 1) { // Garante que o principal não seja removido
                button.closest('.colaborador-entry').remove();
                colaboradorCount--;
                // Reajusta os rótulos e mostra o botão se menos de 2 colaboradores
                let currentColab = 1;
                container.querySelectorAll('.colaborador-entry').forEach(entry => {
                    entry.querySelector('label').innerText = `Aluno Colaborador ${currentColab}:`;
                    currentColab++;
                });
                document.getElementById('addColaboradorBtn').style.display = 'inline-block';
            } else {
                alert('É necessário ter pelo menos um aluno principal.'); // Esta mensagem pode ser ajustada
            }
        }

        // Esconder o botão "Adicionar Colaborador" se já houver 2 ao carregar a página
        document.addEventListener('DOMContentLoaded', (event) => {
            const container = document.getElementById('colaboradores-container');
            if (container.children.length >= 2) {
                colaboradorCount = 2; // Ajusta a contagem inicial se o HTML já tiver 2
                document.getElementById('addColaboradorBtn').style.display = 'none';
            }
        });

    </script>
</body>
</html>