<?php
require_once '../includes/config.php';
iniciarSessao();

// Se já está logado, redirecionar
if (verificarLoginVendedor()) {
    header("Location: painel.php");
    exit;
}

$erro = '';

if ($_POST) {
    $email = sanitizar($_POST['email']);
    $senha = $_POST['senha'];
    
    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos';
    } else {
        $pdo = conectar();
        $stmt = $pdo->prepare("SELECT * FROM vendedores WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $vendedor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($vendedor && password_verify($senha, $vendedor['senha'])) {
            $_SESSION['vendedor_id'] = $vendedor['id'];
            $_SESSION['vendedor_nome'] = $vendedor['nome'];
            header("Location: painel.php");
            exit;
        } else {
            $erro = 'Email ou senha inválidos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Vendedor - Vesty Brasil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="theme-color" content="#6366F1">
</head>
<body>    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="btn-icon">
                    <i class="material-icons-round">arrow_back</i>
                </a>
                <a href="../index.php" class="logo">Vesty Brasil</a>
                <div class="theme-toggle">
                    <div class="theme-toggle-slider">
                        <span class="theme-toggle-icon">☀️</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="login-icon">
                        <i class="material-icons-round">store</i>
                    </div>
                    <h1>Área do Vendedor</h1>
                    <p>Faça login para gerenciar sua loja</p>
                </div>

                <?php if ($erro): ?>
                <div class="error-message">
                    <i class="material-icons-round">error</i>
                    <?= htmlspecialchars($erro) ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label class="form-label">Email:</label>
                        <input type="email" name="email" class="form-input" placeholder="seu@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Senha:</label>
                        <input type="password" name="senha" class="form-input" placeholder="Sua senha" required>
                    </div>

                    <button type="submit" class="btn btn-primary login-btn">
                        <i class="material-icons-round">login</i>
                        Entrar
                    </button>
                </form>

                <div class="login-footer">
                    <div class="demo-info">
                        <h3>🎯 Demonstração</h3>
                        <p>Para testar o painel do vendedor, use:</p>
                        <div class="demo-credentials">
                            <strong>Email:</strong> joao@confeccoes.com<br>
                            <strong>Senha:</strong> vendedor123
                        </div>
                    </div>
                    
                    <div class="help-links">
                        <p>Não tem conta? Entre em contato conosco:</p>
                        <a href="tel:+5581999999999" class="help-link">
                            <i class="material-icons-round">phone</i>
                            (81) 99999-9999
                        </a>
                        <a href="mailto:vendedores@vestybrasil.com" class="help-link">
                            <i class="material-icons-round">email</i>
                            vendedores@vestybrasil.com
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        body {
            padding-bottom: 0;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
        }

        .login-container {
            padding: var(--space-lg) 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 160px);
        }

        .login-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--surface-container);
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--space-lg);
        }

        .login-icon {
            width: 80px;
            height: 80px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--space);
            color: white;
            font-size: 2.5rem;
        }

        .login-header h1 {
            font-size: 1.5rem;
            margin-bottom: var(--space-sm);
            color: var(--on-surface);
        }

        .login-header p {
            color: var(--on-surface-variant);
            margin: 0;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: var(--space);
            border-radius: var(--radius);
            margin-bottom: var(--space);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-size: 0.9rem;
        }

        .login-form {
            margin-bottom: var(--space-lg);
        }

        .login-btn {
            width: 100%;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: var(--space);
        }

        .login-footer {
            border-top: 1px solid var(--surface-container);
            padding-top: var(--space);
        }

        .demo-info {
            background: var(--surface-variant);
            padding: var(--space);
            border-radius: var(--radius);
            margin-bottom: var(--space);
            text-align: center;
        }

        .demo-info h3 {
            margin-bottom: var(--space-sm);
            font-size: 1rem;
        }

        .demo-info p {
            font-size: 0.9rem;
            margin-bottom: var(--space-sm);
        }

        .demo-credentials {
            background: var(--surface);
            padding: var(--space-sm);
            border-radius: var(--radius-sm);
            font-family: monospace;
            font-size: 0.8rem;
            line-height: 1.6;
        }

        .help-links {
            text-align: center;
        }

        .help-links p {
            font-size: 0.9rem;
            margin-bottom: var(--space-sm);
            color: var(--on-surface-variant);
        }

        .help-link {
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            margin: 0 var(--space-sm);
        }        .help-link:hover {
            text-decoration: underline;
        }
    </style>

    <script src="../assets/js/app.js"></script>
</body>
</html>
