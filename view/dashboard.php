<?php
// Inclui o motor central de base de dados da pasta db (que já cria e valida tudo)
include '../db/conexao.php';

$entradas = 0;
$saidas = 0;
$saldo = 0;
$transacoes = [];
$erro = ''; // Inicializa limpo para evitar faixas indesejadas

try {
    $sql = "SELECT * FROM transacoes ORDER BY data DESC, id DESC";
    $stmt = $pdo->query($sql);
    $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($transacoes as $t) {
        if ($t['tipo'] === 'Entrada') {
            $entradas += $t['valor'];
        } else {
            $saidas += $t['valor'];
        }
    }

    $saldo = $entradas - $saidas;

} catch (Exception $e) {
    $erro = "Erro ao carregar transações: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UaiMoney - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* Azul Tecnológico Profundo & Sofisticado */
        body {
            background-color: #0b132b;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Cartões dos resumos */
        .card-custom {
            border-radius: 14px;
            background-color: #1c2541;
            border: 1px solid #3a506b;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        /* Textos auxiliares */
        .texto-auxiliar {
            color: #94a3b8 !important;
            font-weight: 500;
        }

        /* Tabela com fundo transparente e letras em branco */
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

        /* Cores de destaque personalizadas */
        .texto-verde {
            color: #34d399;
            font-weight: bold;
        }

        .texto-vermelho {
            color: #f87171;
            font-weight: bold;
        }

        .texto-azul {
            color: #38bdf8;
            font-weight: bold;
        }

        /* Botões de Ação */
        .btn-success {
            background-color: #059669;
            border: none;
            font-weight: 600;
        }

        .btn-success:hover {
            background-color: #047857;
        }

        .btn-danger {
            background-color: #e11d48;
            border: none;
            font-weight: 600;
        }

        .btn-danger:hover {
            background-color: #be123c;
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

        <!-- Cabeçalho Limpo -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="navbar-brand fs-3">💙 Uai<span style="color: #38bdf8;">Money</span></h2>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="../entradas/nova_entrada.php" class="btn btn-success me-2 px-3 py-2 shadow-sm">+ Nova Entrada</a>
                <a href="../saidas/nova_saida.php" class="btn btn-danger px-3 py-2 shadow-sm">+ Nova Saída</a>
            </div>
        </div>

        <!-- Exibe a faixa de erro apenas se a variável $erro tiver algum conteúdo real -->
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger bg-dark text-danger border-danger mb-4"><?php echo $erro; ?></div>
        <?php endif; ?>

        <!-- Cartões de Resumo -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card card-custom p-3 text-center border-top border-success border-3">
                    <h6 class="text-uppercase fs-6 texto-auxiliar mb-2">Entradas 📈</h6>
                    <h3 class="texto-verde">R$ <?php echo number_format($entradas, 2, ',', '.'); ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card card-custom p-3 text-center border-top border-danger border-3">
                    <h6 class="text-uppercase fs-6 texto-auxiliar mb-2">Saídas 📉</h6>
                    <h3 class="texto-vermelho">R$ <?php echo number_format($saidas, 2, ',', '.'); ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card card-custom p-3 text-center border-top border-3"
                    style="border-color: #38bdf8 !important;">
                    <h6 class="text-uppercase fs-6 texto-auxiliar mb-2">Saldo Atual 💰</h6>
                    <h3 class="texto-azul">R$ <?php echo number_format($saldo, 2, ',', '.'); ?></h3>
                </div>
            </div>
        </div>

        <!-- Tabela com Fundo Transparente e Letras Brancas -->
        <div class="card card-custom p-4">
            <h4 class="mb-3 fs-5 text-white">Histórico de Transações</h4>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr style="border-bottom: 2px solid #3a506b;">
                            <th class="py-3 text-white">Data</th>
                            <th class="py-3 text-white">Tipo</th>
                            <th class="py-3 text-white">Grupo</th>
                            <th class="py-3 text-white">Subgrupo</th>
                            <th class="py-3 text-white">Descrição</th>
                            <th class="py-3 text-white">Pagamento</th>
                            <th class="py-3 text-white">Valor</th>
                            <th class="text-center py-3 text-white">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transacoes) > 0): ?>
                            <?php foreach ($transacoes as $t): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($t['data'])); ?></td>
                                    <td>
                                        <?php if ($t['tipo'] == 'Entrada'): ?>
                                            <span
                                                class="badge bg-success bg-opacity-25 text-success border border-success px-2 py-1">Entrada</span>
                                        <?php else: ?>
                                            <span
                                                class="badge bg-danger bg-opacity-25 text-danger border border-danger px-2 py-1">Saída</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($t['grupo']); ?></td>
                                    <td><?php echo htmlspecialchars($t['subgrupo']); ?></td>
                                    <td><?php echo htmlspecialchars($t['descricao']); ?></td>
                                    <td>
                                        <?php
                                        echo htmlspecialchars($t['forma_pagamento']);
                                        if (!empty($t['banco_cartao'])) {
                                            echo " (" . htmlspecialchars($t['banco_cartao']) . ")";
                                        }
                                        ?>
                                    </td>
                                    <td class="<?php echo ($t['tipo'] == 'Entrada') ? 'texto-verde' : 'texto-vermelho'; ?>">
                                        R$ <?php echo number_format($t['valor'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="../acoes/editar.php?id=<?php echo $t['id']; ?>"
                                            class="btn btn-sm btn-action-edit me-1" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <a href="../acoes/deletar.php?id=<?php echo $t['id']; ?>"
                                            class="btn btn-sm btn-action-delete" title="Apagar"
                                            onclick="return confirm('Tem a certeza de que quer apagar este registo, Uai?');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center texto-auxiliar p-4">Nenhuma transação registada ainda.
                                    Comece a poupar, Uai!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>

</html>