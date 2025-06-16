<?php
require_once 'includes/config.php';
iniciarSessao();

// Verificar se usuário está logado
$usuario_logado = verificarLogin();
$usuario = null;

if ($usuario_logado) {
    $pdo = conectar();
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Vesty Brasil</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="theme-color" content="#6366F1">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="btn-icon">
                    <i class="material-icons-round">arrow_back</i>
                </a>
                <h1 style="font-size: 1.2rem; font-weight: 600; margin: 0;">Meu Perfil</h1>
                <div class="header-actions">
                    <?php if ($usuario_logado): ?>
                    <button class="btn-icon" onclick="logout()">
                        <i class="material-icons-round">logout</i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <?php if ($usuario_logado && $usuario): ?>
        <!-- Usuário Logado -->
        <div class="profile-section">
            <!-- Informações do Usuário -->
            <div class="user-info-card">
                <div class="user-avatar">
                    <i class="material-icons-round">account_circle</i>
                </div>
                <div class="user-details">
                    <h2><?= htmlspecialchars($usuario['nome']) ?></h2>
                    <p><?= htmlspecialchars($usuario['email']) ?></p>
                    <p class="user-since">Membro desde <?= date('M/Y', strtotime($usuario['created_at'])) ?></p>
                </div>
                <button class="btn btn-secondary btn-small" onclick="editarPerfil()">
                    <i class="material-icons-round">edit</i> Editar
                </button>
            </div>

            <!-- Menu de Opções -->
            <div class="profile-menu">
                <a href="#" class="menu-item" onclick="mostrarPedidos()">
                    <div class="menu-icon">
                        <i class="material-icons-round">shopping_bag</i>
                    </div>
                    <div class="menu-content">
                        <h4>Meus Pedidos</h4>
                        <p>Acompanhe seus pedidos</p>
                    </div>
                    <i class="material-icons-round">arrow_forward_ios</i>
                </a>

                <a href="#" class="menu-item" onclick="mostrarEnderecos()">
                    <div class="menu-icon">
                        <i class="material-icons-round">location_on</i>
                    </div>
                    <div class="menu-content">
                        <h4>Endereços</h4>
                        <p>Gerencie seus endereços</p>
                    </div>
                    <i class="material-icons-round">arrow_forward_ios</i>
                </a>

                <a href="#" class="menu-item" onclick="mostrarAjuda()">
                    <div class="menu-icon">
                        <i class="material-icons-round">help</i>
                    </div>
                    <div class="menu-content">
                        <h4>Ajuda</h4>
                        <p>Central de suporte</p>
                    </div>
                    <i class="material-icons-round">arrow_forward_ios</i>
                </a>

                <a href="#" class="menu-item" onclick="mostrarSobre()">
                    <div class="menu-icon">
                        <i class="material-icons-round">info</i>
                    </div>
                    <div class="menu-content">
                        <h4>Sobre</h4>
                        <p>Vesty Brasil v1.0</p>
                    </div>
                    <i class="material-icons-round">arrow_forward_ios</i>
                </a>
            </div>
        </div>

        <?php else: ?>
        <!-- Usuário Não Logado -->
        <div class="login-prompt">
            <div class="login-card">
                <div class="login-icon">
                    <i class="material-icons-round">account_circle</i>
                </div>
                <h2>Entre na sua conta</h2>
                <p>Faça login para acessar seus pedidos, favoritos e muito mais!</p>
                
                <div class="login-buttons">
                    <button class="btn btn-primary" onclick="mostrarLogin()">
                        <i class="material-icons-round">login</i> Entrar
                    </button>
                    <button class="btn btn-secondary" onclick="mostrarCadastro()">
                        <i class="material-icons-round">person_add</i> Cadastrar
                    </button>
                </div>

                <div class="guest-options">
                    <h3>Continuar como visitante</h3>
                    <p>Você pode navegar e comprar sem fazer login, mas perderá algumas funcionalidades.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="index.php" class="nav-item">
            <i class="material-icons-round nav-icon">home</i>
            <span class="nav-label">Início</span>
        </a>
        <a href="categorias.php" class="nav-item">
            <i class="material-icons-round nav-icon">category</i>
            <span class="nav-label">Categorias</span>
        </a>
        <a href="favoritos.php" class="nav-item">
            <i class="material-icons-round nav-icon">favorite</i>
            <span class="nav-label">Favoritos</span>
        </a>
        <a href="carrinho.php" class="nav-item">
            <i class="material-icons-round nav-icon">shopping_cart</i>
            <span class="nav-label">Carrinho</span>
        </a>
        <a href="perfil.php" class="nav-item active">
            <i class="material-icons-round nav-icon">person</i>
            <span class="nav-label">Perfil</span>
        </a>
    </nav>

    <script src="assets/js/app.js"></script>
    <script>
        function logout() {
            if (confirm('Deseja sair da sua conta?')) {
                window.location.href = 'logout.php';
            }
        }

        function editarPerfil() {
            mostrarModal('Editar Perfil', `
                <form id="edit-form">
                    <div class="form-group">
                        <label class="form-label">Nome:</label>
                        <input type="text" class="form-input" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email:</label>
                        <input type="email" class="form-input" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telefone:</label>
                        <input type="tel" class="form-input" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>">
                    </div>
                </form>
            `, 'Salvar', () => {
                window.vestyApp.mostrarNotificacao('Perfil atualizado! (simulado)', 'success');
                fecharModal();
            });
        }

        function mostrarPedidos() {
            mostrarModal('Meus Pedidos', `
                <div class="pedidos-lista">
                    <div class="pedido-item">
                        <div class="pedido-info">
                            <h4>Pedido #12345</h4>
                            <p>2 itens • R$ 159,80</p>
                            <span class="status-badge status-entregue">Entregue</span>
                        </div>
                        <small>15 Nov 2024</small>
                    </div>
                    <div class="pedido-item">
                        <div class="pedido-info">
                            <h4>Pedido #12344</h4>
                            <p>1 item • R$ 89,90</p>
                            <span class="status-badge status-enviado">Em trânsito</span>
                        </div>
                        <small>10 Nov 2024</small>
                    </div>
                </div>
                <p class="note">Esta é uma demonstração. Em produção, os pedidos seriam carregados do banco de dados.</p>
            `);
        }

        function mostrarEnderecos() {
            mostrarModal('Meus Endereços', `
                <div class="enderecos-lista">
                    <div class="endereco-item">
                        <h4>Casa</h4>
                        <p>Rua das Flores, 123<br>Boa Viagem, Recife - PE<br>51020-000</p>
                        <span class="endereco-principal">Principal</span>
                    </div>
                </div>
                <button class="btn btn-secondary" style="margin-top: 16px;">
                    <i class="material-icons-round">add</i> Adicionar Endereço
                </button>
                <p class="note">Funcionalidade de endereços será implementada.</p>
            `);
        }

        function mostrarAjuda() {
            mostrarModal('Central de Ajuda', `
                <div class="ajuda-opcoes">
                    <div class="ajuda-item">
                        <h4>📞 Fale Conosco</h4>
                        <p>WhatsApp: (81) 99999-9999</p>
                    </div>
                    <div class="ajuda-item">
                        <h4>📧 Email</h4>
                        <p>contato@vestybrasil.com</p>
                    </div>
                    <div class="ajuda-item">
                        <h4>❓ FAQ</h4>
                        <p>Perguntas frequentes sobre entregas, trocas e devoluções</p>
                    </div>
                </div>
            `);
        }

        function mostrarSobre() {
            mostrarModal('Sobre a Vesty Brasil', `
                <div class="sobre-content">
                    <h4>Vesty Brasil</h4>
                    <p>Marketplace de moda do polo de confecções de Pernambuco.</p>
                    <p>Conectando fabricantes locais com consumidores de todo o Brasil.</p>
                    <hr>
                    <p><strong>Versão:</strong> 1.0 MVP</p>
                    <p><strong>Desenvolvido:</strong> 2024</p>
                    <p><strong>Tecnologias:</strong> PHP, JavaScript, HTML5, CSS3</p>
                </div>
            `);
        }

        function mostrarLogin() {
            mostrarModal('Entrar', `
                <form id="login-form">
                    <div class="form-group">
                        <label class="form-label">Email:</label>
                        <input type="email" class="form-input" placeholder="seu@email.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Senha:</label>
                        <input type="password" class="form-input" placeholder="Sua senha" required>
                    </div>
                    <p class="note">Funcionalidade de login será implementada na próxima versão.</p>
                </form>
            `, 'Entrar', () => {
                window.vestyApp.mostrarNotificacao('Login será implementado', 'info');
                fecharModal();
            });
        }

        function mostrarCadastro() {
            mostrarModal('Cadastrar', `
                <form id="register-form">
                    <div class="form-group">
                        <label class="form-label">Nome:</label>
                        <input type="text" class="form-input" placeholder="Seu nome completo" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email:</label>
                        <input type="email" class="form-input" placeholder="seu@email.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Senha:</label>
                        <input type="password" class="form-input" placeholder="Mínimo 6 caracteres" required>
                    </div>
                    <p class="note">Funcionalidade de cadastro será implementada na próxima versão.</p>
                </form>
            `, 'Cadastrar', () => {
                window.vestyApp.mostrarNotificacao('Cadastro será implementado', 'info');
                fecharModal();
            });
        }

        function mostrarModal(titulo, conteudo, botaoTexto = 'Fechar', callback = null) {
            const modal = document.createElement('div');
            modal.className = 'profile-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>${titulo}</h3>
                        <button class="btn-icon" onclick="fecharModal()">
                            <i class="material-icons-round">close</i>
                        </button>
                    </div>
                    <div class="modal-body">
                        ${conteudo}
                    </div>
                    <div class="modal-actions">
                        ${callback ? `<button class="btn btn-primary" onclick="(${callback})()">${botaoTexto}</button>` : ''}
                        <button class="btn btn-secondary" onclick="fecharModal()">Fechar</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            setTimeout(() => modal.classList.add('show'), 100);
            window.currentModal = modal;
        }

        function fecharModal() {
            const modal = window.currentModal || document.querySelector('.profile-modal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    if (modal.parentNode) {
                        document.body.removeChild(modal);
                    }
                }, 300);
            }
        }
    </script>

    <style>
        .profile-section {
            padding: var(--space) 0;
        }

        .user-info-card {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: var(--space);
            margin-bottom: var(--space-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-container);
            display: flex;
            align-items: center;
            gap: var(--space);
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            flex-shrink: 0;
        }

        .user-details {
            flex: 1;
        }

        .user-details h2 {
            margin-bottom: var(--space-xs);
            font-size: 1.3rem;
        }

        .user-details p {
            margin-bottom: var(--space-xs);
            color: var(--on-surface-variant);
        }

        .user-since {
            font-size: 0.8rem !important;
        }

        .profile-menu {
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: var(--space);
            padding: var(--space);
            background: var(--surface);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--on-surface);
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-container);
            transition: var(--transition);
        }

        .menu-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .menu-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius);
            background: var(--surface-variant);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.5rem;
        }

        .menu-content {
            flex: 1;
        }

        .menu-content h4 {
            margin-bottom: var(--space-xs);
            font-size: 1rem;
        }

        .menu-content p {
            font-size: 0.9rem;
            color: var(--on-surface-variant);
            margin: 0;
        }

        .login-prompt {
            padding: var(--space-lg) 0;
        }

        .login-card {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            text-align: center;
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-container);
        }

        .login-icon {
            font-size: 5rem;
            color: var(--primary);
            margin-bottom: var(--space);
        }

        .login-card h2 {
            margin-bottom: var(--space);
            font-size: 1.5rem;
        }

        .login-card p {
            margin-bottom: var(--space-lg);
            color: var(--on-surface-variant);
        }

        .login-buttons {
            display: flex;
            gap: var(--space);
            margin-bottom: var(--space-lg);
        }

        .login-buttons .btn {
            flex: 1;
        }

        .guest-options {
            padding-top: var(--space-lg);
            border-top: 1px solid var(--surface-container);
        }

        .guest-options h3 {
            font-size: 1rem;
            margin-bottom: var(--space-sm);
        }

        .guest-options p {
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        /* Modal Styles */
        .profile-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
            padding: var(--space);
        }

        .profile-modal.show {
            opacity: 1;
        }

        .profile-modal .modal-content {
            background: var(--surface);
            border-radius: var(--radius-md);
            max-width: 400px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .profile-modal.show .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space);
            border-bottom: 1px solid var(--surface-container);
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }

        .modal-body {
            padding: var(--space);
        }

        .modal-actions {
            padding: var(--space);
            border-top: 1px solid var(--surface-container);
            display: flex;
            gap: var(--space);
        }

        .modal-actions .btn {
            flex: 1;
        }

        .note {
            font-size: 0.8rem;
            color: var(--on-surface-variant);
            margin-top: var(--space);
            text-align: center;
            font-style: italic;
        }

        .pedido-item, .endereco-item, .ajuda-item {
            padding: var(--space);
            background: var(--surface-variant);
            border-radius: var(--radius);
            margin-bottom: var(--space);
        }

        .pedido-item {
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        .pedido-info h4, .endereco-item h4, .ajuda-item h4 {
            margin-bottom: var(--space-xs);
        }

        .status-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .status-entregue {
            background: var(--success);
            color: white;
        }

        .status-enviado {
            background: var(--warning);
            color: white;
        }

        .endereco-principal {
            background: var(--primary);
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 12px;
        }
    </style>
</body>
</html>
