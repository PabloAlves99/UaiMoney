<?php
// Inclui o motor central de base de dados e migrações
include '../db/conexao.php';

$mensagem = '';
$transacao = null;
$subgrupos = [];

try {
    // Busca todos os subgrupos para popular a seleção
    $stmtSub = $pdo->query("SELECT tipo, grupo, nome FROM subgrupos ORDER BY tipo ASC, grupo ASC, nome ASC");
    $subgrupos = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

    // Se o formulário foi submetido para atualizar
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];
        $tipo = $_POST['tipo'];
        $data = $_POST['data'];
        $valor = str_replace(',', '.', $_POST['valor']);
        $subgrupo_nome = $_POST['subgrupo'];
        $forma_pagamento = $_POST['forma_pagamento'];
        $banco_cartao = ($forma_pagamento === 'Cartão de Crédito') ? $_POST['banco_cartao'] : null;
        $descricao = $_POST['descricao'];

        // Descobre automaticamente o grupo correto com base no subgrupo escolhido
        $stmtGrp = $pdo->prepare("SELECT grupo FROM subgrupos WHERE tipo = ? AND nome = ?");
        $stmtGrp->execute([$tipo, $subgrupo_nome]);
        $resGrp = $stmtGrp->fetch(PDO::FETCH_ASSOC);
        $grupo = $resGrp ? $resGrp['grupo'] : ($tipo === 'Entrada' ? 'Renda Fixa' : 'Essencial');

        $sql = "UPDATE transacoes SET tipo = :tipo, data = :data, valor = :valor, grupo = :grupo, subgrupo = :subgrupo, forma_pagamento = :forma_pagamento, banco_cartao = :banco_cartao, descricao = :descricao WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tipo' => $tipo,
            ':data' => $data,
            ':valor' => $valor,
            ':grupo' => $grupo,
            ':subgrupo' => $subgrupo_nome,
            ':forma_pagamento' => $forma_pagamento,
            ':banco_cartao' => $banco_cartao,
            ':descricao' => $descricao,
            ':id' => $id
        ]);

        header("Location: ../view/dashboard.php");
        exit();
    }

    // Se veio o ID por GET, carrega os dados para o formulário
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $transacao = $stmt->fetch(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    $mensagem = "Erro: " . $e->getMessage();
}

if (!$transacao) {
    die("Transação não encontrada!");
}
?>

<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UaiMoney - Editar Transação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="../media/icon.png" type="image/x-icon">
    <style>
        body {
            background-color: #0b132b !important;
            color: #ffffff !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card-custom {
            border-radius: 14px;
            background-color: #1c2541 !important;
            border: 1px solid #3a506b;
            border-top: 5px solid #fbbf24;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .form-label {
            color: #94a3b8 !important;
            font-weight: 500;
        }

        .form-control,
        .form-select {
            background-color: #131b2e !important;
            border: 1px solid #3a506b !important;
            color: #ffffff !important;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #131b2e !important;
            border-color: #38bdf8 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25);
        }

        ::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        .btn-warning-custom {
            background-color: #f59e0b !important;
            border: none;
            font-weight: 600;
            color: #0b132b !important;
        }

        .btn-warning-custom:hover {
            background-color: #d97706 !important;
            color: #ffffff !important;
        }

        .btn-outline-secondary {
            color: #94a3b8 !important;
            border-color: #3a506b !important;
        }

        .btn-outline-secondary:hover {
            background-color: #3a506b !important;
            color: #ffffff !important;
            border-color: #3a506b !important;
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
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4 navbar-brand fs-3" style="color: #fbbf24;">✏️ Editar Transação</h2>

                <?php if (!empty($mensagem)): ?>
                    <div class="alert alert-danger bg-dark text-danger border-danger mb-3"><?php echo $mensagem; ?></div>
                <?php endif; ?>

                <div class="card card-custom p-4">
                    <form method="POST" action="editar.php">
                        <input type="hidden" name="id" value="<?php echo $transacao['id']; ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo *</label>
                                <select name="tipo" id="tipoSelect" class="form-select" required
                                    onchange="atualizarSubgrupos()">
                                    <option value="Entrada" <?php if ($transacao['tipo'] == 'Entrada')
                                        echo 'selected'; ?>>
                                        Entrada 📈</option>
                                    <option value="Saída" <?php if ($transacao['tipo'] == 'Saída')
                                        echo 'selected'; ?>>
                                        Saída 📉</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Data *</label>
                                <input type="date" name="data" class="form-control"
                                    value="<?php echo $transacao['data']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Valor (R$) *</label>
                            <input type="number" step="0.01" name="valor" class="form-control"
                                value="<?php echo $transacao['valor']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subgrupo *</label>
                            <select name="subgrupo" id="subgrupoSelect" class="form-select" required>
                                <!-- Preenchido via JavaScript -->
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Forma de Pagamento</label>
                            <select name="forma_pagamento" id="formaPagamento" class="form-select"
                                onchange="alternarBancoCartao()">
                                <option value="Cartão de Crédito" <?php if ($transacao['forma_pagamento'] == 'Cartão de Crédito')
                                    echo 'selected'; ?>>Cartão de Crédito</option>
                                <option value="Cartão de Débito" <?php if ($transacao['forma_pagamento'] == 'Cartão de Débito')
                                    echo 'selected'; ?>>Cartão de Débito</option>
                                <option value="Pix" <?php if ($transacao['forma_pagamento'] == 'Pix')
                                    echo 'selected'; ?>>
                                    Pix</option>
                                <option value="Boleto" <?php if ($transacao['forma_pagamento'] == 'Boleto')
                                    echo 'selected'; ?>>Boleto</option>
                                <option value="Dinheiro" <?php if ($transacao['forma_pagamento'] == 'Dinheiro')
                                    echo 'selected'; ?>>Dinheiro Físico</option>
                                <option value="Transferência" <?php if ($transacao['forma_pagamento'] == 'Transferência')
                                    echo 'selected'; ?>>Transferência Bancária</option>
                            </select>
                        </div>

                        <!-- Bloco inteligente do Banco do Cartão -->
                        <div class="mb-3" id="blocoBanco" style="display: none;">
                            <label class="form-label" style="color: #38bdf8;">💳 Qual é o Banco / Cartão?</label>
                            <select name="banco_cartao" class="form-select border-info">
                                <option value="Santander" <?php if ($transacao['banco_cartao'] == 'Santander')
                                    echo 'selected'; ?>>Santander</option>
                                <option value="Nubank" <?php if ($transacao['banco_cartao'] == 'Nubank')
                                    echo 'selected'; ?>>Nubank</option>
                                <option value="Inter" <?php if ($transacao['banco_cartao'] == 'Inter')
                                    echo 'selected'; ?>>Inter</option>
                                <option value="Caixa" <?php if ($transacao['banco_cartao'] == 'Caixa')
                                    echo 'selected'; ?>>Caixa</option>
                                <option value="Banco do Brasil" <?php if ($transacao['banco_cartao'] == 'Banco do Brasil')
                                    echo 'selected'; ?>>Banco do Brasil</option>
                                <option value="Bradesco" <?php if ($transacao['banco_cartao'] == 'Bradesco')
                                    echo 'selected'; ?>>Bradesco</option>
                                <option value="Outro" <?php if ($transacao['banco_cartao'] == 'Outro')
                                    echo 'selected'; ?>> Outro Banco</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Descrição Adicional</label>
                            <input type="text" name="descricao" class="form-control"
                                value="<?php echo htmlspecialchars($transacao['descricao'] ?? ''); ?>">
                        </div>

                        <button type="submit" class="btn btn-warning-custom w-100 py-2">Salvar Alterações</button>
                        <a href="../view/dashboard.php" class="btn btn-outline-secondary w-100 py-2 mt-2">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const listaSubgrupos = <?php echo json_encode($subgrupos); ?>;
        const subgrupoAtual = "<?php echo htmlspecialchars($transacao['subgrupo']); ?>";

        function atualizarSubgrupos() {
            const tipo = document.getElementById('tipoSelect').value;
            const subgrupoSelect = document.getElementById('subgrupoSelect');

            subgrupoSelect.innerHTML = '';

            const filtrados = listaSubgrupos.filter(s => s.tipo === tipo);
            filtrados.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.nome;
                opt.textContent = `[${s.grupo}] - ${s.nome}`;
                if (s.nome === subgrupoAtual) {
                    opt.selected = true;
                }
                subgrupoSelect.appendChild(opt);
            });
        }

        function alternarBancoCartao() {
            const forma = document.getElementById('formaPagamento').value;
            const blocoBanco = document.getElementById('blocoBanco');

            if (forma === 'Cartão de Crédito') {
                blocoBanco.style.display = 'block';
            } else {
                blocoBanco.style.display = 'none';
            }
        }

        window.onload = function () {
            atualizarSubgrupos();
            alternarBancoCartao();
        };
    </script>
</body>

</html>