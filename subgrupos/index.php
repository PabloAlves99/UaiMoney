<?php
// Inclui o motor central de conexão e migrações da pasta db
include '../db/conexao.php';

$subgrupos = [];
$mensagem = '';

try {
    // Se veio pedido de exclusão por GET
    if (isset($_GET['deletar'])) {
        $id_del = $_GET['deletar'];
        $stmtDel = $pdo->prepare("DELETE FROM subgrupos WHERE id = :id");
        $stmtDel->execute([':id' => $id_del]);
        header("Location: index.php");
        exit();
    }

    // Busca todos os subgrupos ordenados
    $stmt = $pdo->query("SELECT * FROM subgrupos ORDER BY tipo DESC, grupo ASC, nome ASC");
    $subgrupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $mensagem = "<div class='alert alert-danger bg-dark text-danger border-danger mt-3'>Erro: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Subgrupos - UaiMoney</title>
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
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .texto-auxiliar {
            color: #94a3b8 !important;
            font-weight: 500;
        }

        /* Tabela com fundo transparente e letras brancas */
        .table {
            color: #ffffff;
            background-color: transparent !important;
        }

        .table th,
        .table td {
            background-color: transparent !important;
            color: #ffffff;
            border-color: #2b3e55 !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(56, 189, 248, 0.08) !important;
            color: #ffffff;
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

        .btn-action-edit {
            background-color: rgba(255, 255, 255, 0.05);
            color: #fbbf24;
            border: 1px solid #3a506b;
        }

        .btn-action-edit:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #f59e0b;
        }

        .btn-action-delete {
            background-color: rgba(255, 255, 255, 0.05);
            color: #f87171;
            border: 1px solid #3a506b;
        }

        .btn-action-delete:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ef4444;
        }

        .navbar-brand {
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body>

    <div class="container mt-5 mb-5">

        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="navbar-brand fs-3">📌 Gestão de Subgrupos</h2>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="../view/dashboard.php" class="btn btn-outline-secondary me-2 px-3 py-2 shadow-sm">
                    <i class="bi bi-arrow-left"></i> Tela inicial
                </a>
                <a href="novo_subgrupo.php" class="btn btn-info-custom px-3 py-2 shadow-sm">+ Novo Subgrupo</a>
            </div>
        </div>

        <?php echo $mensagem; ?>

        <div class="card card-custom p-4">
            <h4 class="mb-3 fs-5 text-white">Subgrupos Cadastrados</h4>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr style="border-bottom: 2px solid #3a506b;">
                            <th class="py-3 text-white">Tipo</th>
                            <th class="py-3 text-white">Grupo (Classificação)</th>
                            <th class="py-3 text-white">Nome do Subgrupo</th>
                            <th class="py-3 text-white">Descrição</th>
                            <th class="text-center py-3 text-white">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($subgrupos) > 0): ?>
                            <?php foreach ($subgrupos as $s): ?>
                                <tr>
                                    <td>
                                        <?php if ($s['tipo'] == 'Entrada'): ?>
                                            <span
                                                class="badge bg-success bg-opacity-25 text-success border border-success px-2 py-1">Entrada</span>
                                        <?php else: ?>
                                            <span
                                                class="badge bg-danger bg-opacity-25 text-danger border border-danger px-2 py-1">Saída</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span
                                            class="badge bg-secondary bg-opacity-25 text-light px-2 py-1"><?php echo htmlspecialchars($s['grupo'] ?? ''); ?></span>
                                    </td>
                                    <td class="fw-bold text-info"><?php echo htmlspecialchars($s['nome'] ?? ''); ?></td>
                                    <td class="texto-auxiliar"><?php echo htmlspecialchars($s['descricao'] ?? ''); ?></td>
                                    <td class="text-center">
                                        <a href="editar_subgrupo.php?id=<?php echo $s['id']; ?>"
                                            class="btn btn-sm btn-action-edit me-1" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <a href="index.php?deletar=<?php echo $s['id']; ?>" class="btn btn-sm btn-action-delete"
                                            title="Apagar"
                                            onclick="return confirm('Tem a certeza de que quer apagar este subgrupo, Uai?');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center texto-auxiliar p-4">Nenhum subgrupo cadastrado ainda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>

</html>