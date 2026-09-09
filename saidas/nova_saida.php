<?php
// Inclui o motor central de conexão e migrações da pasta db
include_once '../db/conexao.php';

$mensagem = '';
$subgrupos = [];

try {
    // Busca apenas os subgrupos de Saída
    $stmtSub = $pdo->prepare("SELECT nome, grupo FROM subgrupos WHERE tipo = 'Saída' ORDER BY grupo ASC, nome ASC");
    $stmtSub->execute();
    $subgrupos = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipo = 'Saída';
        $data = $_POST['data'];
        $valor = str_replace(',', '.', $_POST['valor']);
        $subgrupo_nome = $_POST['subgrupo'];
        $forma_pagamento = $_POST['forma_pagamento'];
        $banco_cartao = ($forma_pagamento === 'Cartão de Crédito') ? $_POST['banco_cartao'] : null;
        $descricao = $_POST['descricao'];

        // Descobre automaticamente o grupo do subgrupo
        $stmtGrp = $pdo->prepare("SELECT grupo FROM subgrupos WHERE tipo = 'Saída' AND nome = ?");
        $stmtGrp->execute([$subgrupo_nome]);
        $resGrp = $stmtGrp->fetch(PDO::FETCH_ASSOC);
        $grupo = $resGrp ? $resGrp['grupo'] : 'Essencial';

        $sql = "INSERT INTO transacoes (tipo, data, valor, grupo, subgrupo, forma_pagamento, banco_cartao, descricao) 
                VALUES (:tipo, :data, :valor, :grupo, :subgrupo, :forma_pagamento, :banco_cartao, :descricao)";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':tipo' => $tipo,
            ':data' => $data,
            ':valor' => $valor,
            ':grupo' => $grupo,
            ':subgrupo' => $subgrupo_nome,
            ':forma_pagamento' => $forma_pagamento,
            ':banco_cartao' => $banco_cartao,
            ':descricao' => $descricao
        ]);

        $mensagem = "<div class='alert alert-danger bg-dark text-danger border-danger mt-3'>Despesa de R$ {$valor} registrada com sucesso! 💸</div>";
    }
} catch (Exception $e) {
    $mensagem = "<div class='alert alert-warning bg-dark text-warning border-danger mt-3'>Erro: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Nova Saída - UaiMoney</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
            border-top: 5px solid #f87171;
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

        .btn-danger {
            background-color: #e11d48;
            border: none;
            font-weight: 600;
        }

        .btn-danger:hover {
            background-color: #be123c;
        }

        .btn-outline-info {
            border-color: #38bdf8;
            color: #38bdf8;
        }

        .btn-outline-info:hover {
            background-color: #38bdf8;
            color: #0b132b;
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
                <h2 class="text-center mb-4" style="color: #f87171;">📉 Despesa (Saída)</h2>
                <div class="card card-custom p-4">
                    <form method="POST" action="nova_saida.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Data</label>
                                <input type="date" name="data" class="form-control" value="<?php echo date('Y-m-d'); ?>"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valor (R$)</label>
                                <input type="number" step="0.01" name="valor" class="form-control" placeholder="0.00"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subgrupo *</label>
                            <div class="input-group">
                                <select name="subgrupo" class="form-select" required>
                                    <option value="">Selecione o subgrupo...</option>
                                    <?php foreach ($subgrupos as $s): ?>
                                        <option value="<?php echo htmlspecialchars($s['nome']); ?>">
                                            [<?php echo htmlspecialchars($s['grupo']); ?>] -
                                            <?php echo htmlspecialchars($s['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <a href="../subgrupos/index.php" class="btn btn-outline-info"
                                    title="Gerenciar Subgrupos">
                                    <i class="bi bi-gear-fill"></i>
                                </a>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Forma de Pagamento</label>
                            <select name="forma_pagamento" id="formaPagamento" class="form-select"
                                onchange="alternarBancoCartao()">
                                <option value="Cartão de Crédito">Cartão de Crédito</option>
                                <option value="Cartão de Débito">Cartão de Débito</option>
                                <option value="Pix">Pix</option>
                                <option value="Boleto">Boleto</option>
                                <option value="Dinheiro">Dinheiro Físico</option>
                            </select>
                        </div>

                        <div class="mb-3" id="blocoBanco" style="display: none;">
                            <label class="form-label" style="color: #38bdf8;">💳 Qual é o Banco / Cartão?</label>
                            <select name="banco_cartao" class="form-select border-info">
                                <option value="Santander">Santander</option>
                                <option value="Inter">Banco Inter</option>
                                <option value="Bradesco">Bradesco</option>
                                <option value="Itaú">Caixa</option>
                                <option value="C6 Bank">C6 Bank</option>
                                <option value="Itaú">Itaú</option>
                                <option value="Nubank">Nubank</option>
                                <option value="Outro">Outro Banco</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Descrição Adicional</label>
                            <input type="text" name="descricao" class="form-control" placeholder="Detalhes do gasto...">
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-2">Registrar Despesa</button>
                        <a href="../view/dashboard.php" class="btn btn-outline-secondary w-100 py-2 mt-2">Voltar ao
                            Painel</a>
                    </form>
                    <?php echo $mensagem; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function alternarBancoCartao() {
            const forma = document.getElementById('formaPagamento').value;
            const blocoBanco = document.getElementById('blocoBanco');

            if (forma === 'Cartão de Crédito') {
                blocoBanco.style.display = 'block';
            } else {
                blocoBanco.style.display = 'none';
            }
        }

        window.onload = alternarBancoCartao;
    </script>

</body>

</html>