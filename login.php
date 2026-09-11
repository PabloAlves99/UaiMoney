<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, redireciona para o painel
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: ./view/dashboard.php");
    exit();
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Coleta os dados enviados pelo formulário
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha o e-mail e a senha, uai!';
    } else {
        try {
            // 2. Conexão PDO com o SQLite na raiz
            $pdo = new PDO('sqlite:banco.sqlite');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 3. Consulta o usuário pelo email
            $stmt = $pdo->prepare("SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // 4. Validação da senha criptografada
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                $_SESSION['logado']        = true;
                $_SESSION['usuario_id']    = $usuario['id'];
                $_SESSION['usuario_nome']  = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_tipo']  = $usuario['tipo']; // 'adm' ou 'usuario'

                header("Location: ./view/dashboard.php");
                exit();
            } else {
                $erro = 'E-mail ou senha incorretos, uai!';
            }
        } catch (Exception $e) {
            $erro = 'Erro de banco de dados: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UaiMoney</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="media/logoUaiMoney.png" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .card-login {
            width: 100%;
            max-width: 380px;
            background-color: #1c2541;
            border: 1px solid #3a506b;
            border-top: 5px solid #38bdf8;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            padding: 28px 24px;
        }

        .form-label {
            color: #94a3b8;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .input-login {
            background-color: #131b2e !important;
            border: 1px solid #3a506b;
            color: #ffffff !important;
            height: 38px;
            font-size: 0.85rem;
            border-radius: 8px;
            -webkit-appearance: none;
            appearance: none;
        }

        .input-login:focus {
            background-color: #131b2e !important;
            border-color: #38bdf8;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25);
            color: #ffffff;
        }

        .btn-entrar {
            height: 38px;
            background-color: #0ea5e9;
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.2s ease;
        }

        .btn-entrar:hover {
            background-color: #0284c7;
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="card-login text-center">
        <img src="media/logoDark.png" alt="UaiMoney" class="img-fluid mb-3" style="max-width: 170px;" onerror="this.style.display='none'">
        <h5 class="mb-4" style="color: #f8fafc; font-weight: 700;">Acesso Restrito</h5>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger py-2 text-start" style="font-size: 0.82rem; background: rgba(248, 113, 113, 0.15); border-color: #f87171; color: #f87171;">
                <i class="bi bi-exclamation-triangle me-1"></i> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="text-start">
            <div class="mb-3">
                <label class="form-label">E-mail de Acesso</label>
                <input type="email" name="email" class="form-control input-login" placeholder="seu@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Senha</label>
                <div class="input-group">
                    <input type="password" name="senha" id="senhaInput" class="form-control input-login" placeholder="Digite sua senha..." required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleSenha" style="border-color: #3a506b; background: #131b2e; color: #94a3b8; height: 38px;">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-entrar w-100 mt-4">
                <i class="bi bi-box-arrow-in-right"></i> Entrar
            </button>
        </form>
    </div>

    <script>
        const toggleSenha = document.getElementById('toggleSenha');
        const senhaInput = document.getElementById('senhaInput');
        toggleSenha.addEventListener('click', () => {
            const tipo = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            senhaInput.setAttribute('type', tipo);
            toggleSenha.innerHTML = tipo === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        });
    </script>
</body>

</html>