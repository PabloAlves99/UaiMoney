<?php
require_once __DIR__ . '/../auth.php';
exigirAdmin(); // Bloqueia se o tipo for 'usuario'

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $login = trim($_POST['login'] ?? ''); // Novo campo capturado
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo  = $_POST['tipo'] ?? 'usuario';

    if (empty($nome) || empty($login) || empty($email) || empty($senha)) {
        $mensagem = "<div class='alert alert-danger py-2' style='font-size:0.85rem;'>Preencha todos os campos obrigatórios.</div>";
    } else {
        try {
            $pdo = new PDO('sqlite:' . __DIR__ . '/../banco.sqlite');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Verifica se o e-mail ou login já existem
            $checkUser = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email OR login = :login");
            $checkUser->execute([
                ':email' => $email,
                ':login' => $login
            ]);

            if ($checkUser->fetchColumn() > 0) {
                $mensagem = "<div class='alert alert-warning py-2' style='font-size:0.85rem;'>Este e-mail ou login já está cadastrado.</div>";
            } else {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $dtcad = date('Y-m-d H:i:s');

                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nome, login, email, senha, dtcad, tipo) 
                    VALUES (:nome, :login, :email, :senha, :dtcad, :tipo)
                ");
                $stmt->execute([
                    ':nome'  => $nome,
                    ':login' => $login,
                    ':email' => $email,
                    ':senha' => $senhaHash,
                    ':dtcad' => $dtcad,
                    ':tipo'  => $tipo
                ]);

                $mensagem = "<div class='alert alert-success py-2' style='font-size:0.85rem;'>Usuário cadastrado com sucesso!</div>";
            }
        } catch (Exception $e) {
            $mensagem = "<div class='alert alert-danger py-2' style='font-size:0.85rem;'>Erro ao salvar: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Usuário - UaiMoney</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="../media/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
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
            font-size: 0.85rem;
            font-weight: 500;
        }

        .form-control,
        .form-select {
            background-color: #131b2e !important;
            border: 1px solid #3a506b;
            color: #ffffff !important;
            height: 38px;
            font-size: 0.85rem;
            border-radius: 8px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25);
        }

        /* O PLACEHOLDER BRANCO QUE ADICIONAMOS */
        .form-control::placeholder {
            color: #ffffff !important;
            opacity: 0.85;
        }

        .btn-info-custom {
            background-color: #0ea5e9;
            border: none;
            font-weight: 600;
            color: #ffffff;
            height: 38px;
            font-size: 0.85rem;
            border-radius: 8px;
        }

        .btn-info-custom:hover {
            background-color: #0284c7;
            color: #ffffff;
        }

        .btn-outline-secondary {
            color: #94a3b8;
            border-color: #3a506b;
            height: 38px;
            font-size: 0.85rem;
            border-radius: 8px;
        }

        .btn-outline-secondary:hover {
            background-color: #3a506b;
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <h3 class="text-center mb-4" style="color: #38bdf8; font-weight: 700;"><i class="bi bi-person-add"></i> Novo Usuário</h3>

                <?php echo $mensagem; ?>

                <div class="card card-custom p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" name="nome" class="form-control text-white" placeholder="Ex: Pablo Alves" required>
                        </div>

                        <!-- Novo campo de Login -->
                        <div class="mb-3">
                            <label class="form-label">Login (Nome de Acesso) *</label>
                            <input type="text" name="login" class="form-control text-white" placeholder="Ex: PabloAlves" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">E-mail *</label>
                            <input type="email" name="email" class="form-control text-white" placeholder="usuario@uaimoney.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Senha de Acesso *</label>
                            <input type="password" name="senha" class="form-control text-white" placeholder="Defina a senha..." required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Tipo de Permissão *</label>
                            <select name="tipo" class="form-select" required>
                                <option value="Usuario" selected>Usuário Padrão</option>
                                <option value="Adm">Administrador (Acesso Total)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-info-custom w-100 mb-2">Cadastrar Usuário</button>
                        <a href="../view/dashboard.php" class="btn btn-outline-secondary w-100">Voltar ao Painel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>