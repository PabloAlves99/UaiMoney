<?php
// Inclui o motor central de conexão e migrações da pasta db
include '../db/conexao.php';

$mensagem = '';
$voltar = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';

// Busca os grupos dinamicamente no banco
$stmtGrupos = $pdo->query("SELECT * FROM grupos ORDER BY nome ASC");
$listaGrupos = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $grupo = $_POST['grupo'];
    $nome = $_POST['nome'];
    $descricao = !empty($_POST['descricao']) ? $_POST['descricao'] : null;
    $url_retorno = $_POST['url_retorno'] ?? '../dashboard.php';

    try {
        $sql = "INSERT INTO subgrupos (tipo, grupo, nome, descricao) VALUES (:tipo, :grupo, :nome, :descricao)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tipo' => $tipo,
            ':grupo' => $grupo,
            ':nome' => $nome,
            ':descricao' => $descricao
        ]);

        header("Location: " . $url_retorno);
        exit();
    } catch (Exception $e) {
        $mensagem = "<div class='alert alert-danger bg-dark text-danger border-danger mt-3'>Erro: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Subgrupo - UaiMoney</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="../media/icon.png" type="image/x-icon">
    <style>
        body {
            background-color: #0b132b;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card-custom {
            border-radius: 14px;
            background-color: #1c2541;
            border: 1px solid #3a506b;
            border-top: 5px solid #38bdf8;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .form-label {
            color: #94a3b8;
            font-weight: 500;
        }

        .form-control,
        .form-select {
            background-color: #131b2e;
            border: 1px solid #3a506b;
            color: #ffffff;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #131b2e;
            border-color: #38bdf8;
            color: #ffffff;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25);
        }

        ::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        .btn-info {
            background-color: #0ea5e9;
            border: none;
            font-weight: 600;
            color: white;
        }

        .btn-info:hover {
            background-color: #0284c7;
            color: white;
        }

        .btn-outline-secondary {
            color: #94a3b8;
            border-color: #3a506b;
        }

        .btn-outline-secondary:hover {
            background-color: #3a506b;
            color: #ffffff;
            border-color: #3a506b;
        }
    </style>
</head>

<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4" style="color: #38bdf8; font-weight:bold;">📌 Novo Subgrupo</h2>
                <div class="card card-custom p-4">
                    <form method="POST" action="novo_subgrupo.php">
                        <input type="hidden" name="url_retorno" value="<?php echo htmlspecialchars($voltar); ?>">

                        <div class="mb-3">
                            <label class="form-label">Tipo de Transação *</label>
                            <select name="tipo" class="form-select" id="tipoSelect" required
                                onchange="atualizarGrupos()">
                                <option value="Entrada">Entrada (Receita) 📈</option>
                                <option value="Saída" selected>Saída (Despesa) 📉</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Grupo *</label>
                            <div class="input-group">
                                <select name="grupo" class="form-select" id="grupoSelect" required></select>
                                <a href="../grupos/index.php" class="btn btn-outline-secondary"
                                    title="Gerenciar Grupos">
                                    <i class="bi bi-gear-fill"></i>
                                </a>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nome do Subgrupo *</label>
                            <input type="text" name="nome" class="form-control"
                                placeholder="Ex: Supermercado, Aluguel..." required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Descrição (Opcional)</label>
                            <textarea name="descricao" class="form-control" rows="3"
                                placeholder="Detalhes sobre este subgrupo..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-info w-100 py-2">Salvar Subgrupo</button>
                        <a href="<?php echo htmlspecialchars($voltar); ?>"
                            class="btn btn-outline-secondary w-100 py-2 mt-2">Voltar</a>
                    </form>
                    <?php echo $mensagem; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Recebe os dados do PHP dinamicamente
        const gruposCadastrados = <?php echo json_encode($listaGrupos); ?>;

        function atualizarGrupos() {
            const tipo = document.getElementById('tipoSelect').value;
            const grupoSelect = document.getElementById('grupoSelect');
            grupoSelect.innerHTML = '';

            const gruposFiltrados = gruposCadastrados.filter(g => g.tipo === tipo);

            if (gruposFiltrados.length === 0) {
                const opt = document.createElement('option');
                opt.value = "";
                opt.textContent = "Nenhum grupo cadastrado!";
                grupoSelect.appendChild(opt);
                return;
            }

            gruposFiltrados.forEach(g => {
                const opt = document.createElement('option');
                opt.value = g.nome;
                opt.textContent = g.nome;

                if (tipo === 'Saída' && g.nome === 'Gastos') {
                    opt.selected = true;
                }

                grupoSelect.appendChild(opt);
            });
        }

        window.onload = atualizarGrupos;
    </script>
</body>

</html>