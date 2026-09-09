<?php
// Inclui o motor central de base de dados da pasta db (que já cria e valida tudo)
include '../db/conexao.php';

$entradas = 0;
$saidas = 0;
$saldo = 0;
$transacoes = [];
$erro = '';

// 1. Lógica do Filtro de Datas
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-t');

try {
    // 2. SQL Mágico
    $sql = "SELECT * FROM transacoes 
            WHERE data >= :data_inicio AND data <= :data_fim 
            AND IFNULL(tipo_registro, 'unico') != 'pai' 
            ORDER BY data DESC, id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Soma apenas o que está dentro do filtro
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
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UaiMoney - Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="../media/logoUaiMoney.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <div class="container container-dashboard mt-4 mb-5">

        <!-- CABEÇALHO + AÇÕES + FILTROS -->
        <section class="dashboard-toolbar mb-4">
            <div class="toolbar-top">
                <div class="brand-area">
                    <img src="../media/logoUaiMoney.png" alt="Logo UaiMoney" class="brand-logo" style="background-color: #fff; border-radius: 14px;">
                    <div class="brand-separator"></div>
                    <div class="brand-copy">
                        <h1 class="dashboard-title">Visão financeira</h1>
                        <p class="dashboard-subtitle">Acompanhe receitas, despesas e saldo em um só lugar.</p>
                    </div>
                </div>

                <div class="toolbar-actions">
                    <a href="../entradas/nova_entrada.php" class="btn-modern btn-success-modern">
                        <i class="bi bi-plus-lg"></i> Nova Receita
                    </a>
                    <a href="../saidas/nova_saida.php" class="btn-modern btn-danger-modern">
                        <i class="bi bi-dash-lg"></i> Nova Despesa
                    </a>
                </div>
            </div>

            <form method="GET" class="filter-bar">
                <div class="filter-intro">
                    <div class="filter-intro-label"><i class="bi bi-sliders2"></i> Filtros</div>
                    <h2 class="filter-intro-title">Período das transações</h2>
                    <p class="filter-intro-text">Escolha o intervalo que deseja analisar.</p>
                </div>

                <div class="form-group-custom">
                    <label class="filter-label" for="data_inicio"><i class="bi bi-calendar3"></i> Data inicial</label>
                    <input type="date" id="data_inicio" name="data_inicio" class="input-filtro" value="<?php echo htmlspecialchars($data_inicio); ?>" required>
                </div>

                <div class="form-group-custom">
                    <label class="filter-label" for="data_fim"><i class="bi bi-calendar-check"></i> Data final</label>
                    <input type="date" id="data_fim" name="data_fim" class="input-filtro" value="<?php echo htmlspecialchars($data_fim); ?>" required>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn-info-custom" title="Aplicar período">
                        <i class="bi bi-funnel-fill"></i> Filtrar
                    </button>
                </div>
            </form>
        </section>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger bg-dark text-danger border-danger mb-4">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <!-- CARDS DE RESUMO (Com IDs para o JavaScript de Filtro) -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card-custom summary-card card-clicavel texto-verde" id="card-entradas" onclick="filtrarTabela('Entrada')" title="Clique para ver apenas Entradas">
                    <div class="summary-top">
                        <span class="texto-auxiliar text-uppercase">Entradas</span>
                        <span class="summary-icon summary-icon-success"><i class="bi bi-arrow-up-right"></i></span>
                    </div>
                    <h3 class="summary-value texto-verde">R$ <?php echo number_format($entradas, 2, ',', '.'); ?></h3>
                    <div class="summary-help">Clique no card para filtrar</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-custom summary-card card-clicavel texto-vermelho" id="card-saidas" onclick="filtrarTabela('Saída')" title="Clique para ver apenas Saídas">
                    <div class="summary-top">
                        <span class="texto-auxiliar text-uppercase">Saídas</span>
                        <span class="summary-icon summary-icon-danger"><i class="bi bi-arrow-down-right"></i></span>
                    </div>
                    <h3 class="summary-value texto-vermelho">R$ <?php echo number_format($saidas, 2, ',', '.'); ?></h3>
                    <div class="summary-help">Clique no card para filtrar</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-custom summary-card texto-azul">
                    <div class="summary-top">
                        <span class="texto-auxiliar text-uppercase">Saldo do Período</span>
                        <span class="summary-icon summary-icon-primary"><i class="bi bi-wallet2"></i></span>
                    </div>
                    <h3 class="summary-value texto-azul">R$ <?php echo number_format($saldo, 2, ',', '.'); ?></h3>
                    <div class="summary-help">Entradas menos saídas</div>
                </div>
            </div>
        </div>

        <!-- TABELA -->
        <div class="card-custom transaction-card">
            <div class="transaction-header">
                <h4 class="transaction-title" id="titulo-tabela">
                    <span class="transaction-title-icon"><i class="bi bi-receipt"></i></span>
                    <span>Transações de <?php echo date('d/m/Y', strtotime($data_inicio)); ?> a <?php echo date('d/m/Y', strtotime($data_fim)); ?></span>
                </h4>
                <span class="transaction-count">
                    <?php echo count($transacoes); ?> <?php echo count($transacoes) === 1 ? 'registro' : 'registros'; ?>
                </span>
            </div>

            <div class="table-wrapper">
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Grupo</th>
                                <th>Subgrupo</th>
                                <th>Descrição</th>
                                <th>Pagamento</th>
                                <th>Valor</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($transacoes) > 0): ?>
                                <?php foreach ($transacoes as $t): ?>
                                    <!-- A MÁGICA: Colocamos a classe e o data-tipo na linha -->
                                    <tr class="linha-transacao" data-tipo="<?php echo $t['tipo']; ?>">
                                        <td><?php echo date('d/m/Y', strtotime($t['data'])); ?></td>
                                        <td>
                                            <?php if ($t['tipo'] == 'Entrada'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Entrada</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">Saída</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($t['grupo']); ?></td>
                                        <td><?php echo htmlspecialchars($t['subgrupo']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($t['descricao']); ?>
                                            <?php if (isset($t['tipo_registro']) && $t['tipo_registro'] == 'parcela'): ?>
                                                <br>
                                                <small class="text-info" style="font-size: .72rem;">
                                                    <i class="bi bi-arrow-repeat"></i> Recorrente
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            echo htmlspecialchars($t['forma_pagamento'] ?? '');
                                            if (!empty($t['banco_cartao'])) {
                                                echo " (" . htmlspecialchars($t['banco_cartao']) . ")";
                                            }
                                            ?>
                                        </td>
                                        <td class="<?php echo ($t['tipo'] == 'Entrada') ? 'texto-verde' : 'texto-vermelho'; ?>">
                                            R$ <?php echo number_format($t['valor'], 2, ',', '.'); ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="../acoes/editar.php?id=<?php echo $t['id']; ?>" class="btn-action-edit me-1" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <a href="../acoes/deletar.php?id=<?php echo $t['id']; ?>" class="btn-action-delete" title="Apagar" onclick="return confirm('Tem certeza de que quer apagar este registro, Uai?');">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center texto-auxiliar p-5">
                                        <i class="bi bi-inbox d-block fs-4 mb-2 text-info"></i>
                                        Nenhuma transação registrada neste período. Comece a poupar, Uai!
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/script.js"></script>

</body>

</html>