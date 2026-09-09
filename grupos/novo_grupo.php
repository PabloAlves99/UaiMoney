<?php
include '../db/conexao.php';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $nome = $_POST['nome'];

    try {
        $stmt = $pdo->prepare("INSERT INTO grupos (nome, tipo) VALUES (:nome, :tipo)");
        $stmt->execute([':nome' => $nome, ':tipo' => $tipo]);
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $mensagem = "<div class='alert alert-danger'>Erro ao salvar: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Novo Grupo - UaiMoney</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4 fs-3" style="color: #38bdf8; font-weight:bold;">➕ Novo Grupo</h2>
                <?php echo $mensagem; ?>
                <div class="card card-custom p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Tipo *</label>
                            <select name="tipo" class="form-select" required>
                                <option value="Entrada">Entrada (Receita) 📈</option>
                                <option value="Saída">Saída (Despesa) 📉</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Nome do Grupo *</label>
                            <input type="text" name="nome" class="form-control" placeholder="Ex: Moradia, Transporte..."
                                required>
                        </div>
                        <button type="submit" class="btn btn-info-custom w-100 py-2">Salvar Grupo</button>
                        <button type="button" onclick="history.back()"
                            class="btn btn-outline-secondary w-100 py-2 mt-2">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>