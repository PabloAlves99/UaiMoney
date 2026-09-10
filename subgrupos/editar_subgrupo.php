<?php
// Inclui o motor central de base de dados da pasta db
include '../db/conexao.php';

$mensagem = '';
$subgrupo = null;

// Busca os grupos dinamicamente no banco
$stmtGrupos = $pdo->query("SELECT * FROM grupos ORDER BY nome ASC");
$listaGrupos = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];
        $tipo = $_POST['tipo'];
        $grupo = $_POST['grupo'];
        $nome = $_POST['nome'];
        $descricao = !empty($_POST['descricao']) ? $_POST['descricao'] : null;

        $stmt = $pdo->prepare("UPDATE subgrupos SET tipo = :tipo, grupo = :grupo, nome = :nome, descricao = :descricao WHERE id = :id");
        $stmt->execute([
            ':tipo' => $tipo,
            ':grupo' => $grupo,
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':id' => $id
        ]);

        header("Location: index.php");
        exit();
    }

    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM subgrupos WHERE id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $subgrupo = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $mensagem = "<div class='alert alert-danger bg-dark text-danger border-danger mt-3'>Erro: " . $e->getMessage() . "</div>";
}

if (!$subgrupo) {
    die("Subgrupo não encontrado!");
}
?>

<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UaiMoney - Editar Subgrupo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="../media/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <style>
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

        .btn-info-custom {
            background-color: #0ea5e9;
            border: none;
            font-weight: 600;
            color: white;
        }

        .btn-info-custom:hover {
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
    <div class="container mt-2">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4 fs-3" style="color: #38bdf8; font-weight:bold;">✏️ Editar Subgrupo</h2>
                <?php if (!empty($mensagem)): ?>
                    <?php echo $mensagem; ?>
                <?php endif; ?>
                <div class="card card-custom p-4">
                    <form method="POST" action="editar_subgrupo.php">
                        <input type="hidden" name="id" value="<?php echo $subgrupo['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Tipo de Transação *</label>
                            <select name="tipo" class="form-select" id="tipoSelect" required
                                onchange="atualizarGrupos()">
                                <option value="Entrada" <?php if ($subgrupo['tipo'] == 'Entrada')
                                    echo 'selected'; ?>>
                                    Entrada (Receita) 📈</option>
                                <option value="Saída" <?php if ($subgrupo['tipo'] == 'Saída')
                                    echo 'selected'; ?>>Saída
                                    (Despesa) 📉</option>
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
                                value="<?php echo htmlspecialchars($subgrupo['nome']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Descrição (Opcional)</label>
                            <textarea name="descricao" class="form-control"
                                rows="3"><?php echo htmlspecialchars($subgrupo['descricao'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-info-custom w-100 py-2">Salvar Alterações</button>
                        <a href="index.php" class="btn btn-outline-secondary w-100 py-2 mt-2">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const grupoAtual = "<?php echo htmlspecialchars($subgrupo['grupo']); ?>";
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
                if (g.nome === grupoAtual) {
                    opt.selected = true;
                }
                grupoSelect.appendChild(opt);
            });
        }

        window.onload = atualizarGrupos;
    </script>
</body>

</html>