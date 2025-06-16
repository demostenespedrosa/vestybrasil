<?php
require_once '../includes/config.php';
iniciarSessao();

// Verificar login
if (!verificarLoginAdmin()) {
    header("Location: login.php");
    exit;
}

$pdo = conectar();

// Buscar estatísticas gerais
$stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE ativo = 1");
$totalProdutos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM vendedores WHERE ativo = 1");
$totalVendedores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
$totalPedidos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE ativa = 1");
$totalCategorias = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Buscar categorias
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar subcategorias
$stmt = $pdo->query("
    SELECT s.*, c.nome as categoria_nome 
    FROM subcategorias s 
    LEFT JOIN categorias c ON s.categoria_id = c.id 
    ORDER BY c.nome, s.nome
");
$subcategorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar pedidos recentes
$stmt = $pdo->query("
    SELECT p.*, u.nome as usuario_nome 
    FROM pedidos p 
    LEFT JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 10
");
$pedidosRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Vesty Brasil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="theme-color" content="#dc2626">
</head>
<body>    <!-- Header -->
    <header class="header admin-header">
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">Vesty Brasil Admin</a>
                <div class="admin-info">
                    <div class="theme-toggle">
                        <div class="theme-toggle-slider">
                            <span class="theme-toggle-icon">☀️</span>
                        </div>
                    </div>
                    <span>Olá, <?= htmlspecialchars($_SESSION['admin_nome']) ?></span>
                    <a href="logout.php" class="btn-icon">
                        <i class="material-icons-round">logout</i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="material-icons-round">inventory</i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalProdutos ?></h3>
                    <p>Produtos Ativos</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="material-icons-round">store</i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalVendedores ?></h3>
                    <p>Vendedores</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="material-icons-round">receipt_long</i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalPedidos ?></h3>
                    <p>Pedidos</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="material-icons-round">category</i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalCategorias ?></h3>
                    <p>Categorias</p>
                </div>
            </div>
        </div>

        <!-- Menu de Ações -->
        <div class="action-menu">
            <button class="action-btn" onclick="mostrarCategorias()">
                <i class="material-icons-round">category</i>
                <span>Gerenciar Categorias</span>
            </button>
            
            <button class="action-btn" onclick="mostrarSubcategorias()">
                <i class="material-icons-round">label</i>
                <span>Subcategorias</span>
            </button>
            
            <button class="action-btn" onclick="mostrarPedidos()">
                <i class="material-icons-round">receipt_long</i>
                <span>Todos os Pedidos</span>
            </button>
            
            <button class="action-btn" onclick="mostrarVendedores()">
                <i class="material-icons-round">people</i>
                <span>Vendedores</span>
            </button>
        </div>

        <!-- Pedidos Recentes -->
        <section class="recent-section">
            <div class="section-header">
                <h2 class="section-title">Pedidos Recentes</h2>
                <button class="section-action" onclick="mostrarPedidos()">Ver todos</button>
            </div>
            
            <?php if (empty($pedidosRecentes)): ?>
            <div class="empty-state">
                <p>Nenhum pedido encontrado</p>
            </div>
            <?php else: ?>
            <div class="orders-list">
                <?php foreach ($pedidosRecentes as $pedido): ?>
                <div class="order-item">
                    <div class="order-info">
                        <h4>Pedido #<?= $pedido['id'] ?></h4>
                        <p>Cliente: <?= htmlspecialchars($pedido['usuario_nome'] ?: 'Não informado') ?></p>
                        <p>Total: <?= formatarPreco($pedido['total']) ?></p>
                        <span class="status-badge status-<?= $pedido['status'] ?>">
                            <?= ucfirst($pedido['status']) ?>
                        </span>
                    </div>
                    <div class="order-date">
                        <?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Modal para Categorias -->
    <div id="categorias-modal" class="modal" style="display: none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>Gerenciar Categorias</h3>
                <button class="btn-icon" onclick="fecharModal('categorias-modal')">
                    <i class="material-icons-round">close</i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulário Nova Categoria -->
                <div class="form-section">
                    <h4>Nova Categoria</h4>
                    <form id="categoria-form" class="inline-form">
                        <div class="form-group">
                            <input type="text" name="nome" class="form-input" placeholder="Nome da categoria" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="icone" class="form-input" placeholder="Ícone (Material Icons)">
                        </div>
                        <button type="button" class="btn btn-primary" onclick="salvarCategoria()">
                            <i class="material-icons-round">add</i> Adicionar
                        </button>
                    </form>
                </div>

                <!-- Lista de Categorias -->
                <div class="categorias-lista">
                    <?php foreach ($categorias as $categoria): ?>
                    <div class="categoria-item" data-id="<?= $categoria['id'] ?>">
                        <div class="categoria-info">
                            <i class="material-icons-round"><?= $categoria['icone'] ?: 'category' ?></i>
                            <span><?= htmlspecialchars($categoria['nome']) ?></span>
                            <span class="status-badge <?= $categoria['ativa'] ? 'ativo' : 'inativo' ?>">
                                <?= $categoria['ativa'] ? 'Ativa' : 'Inativa' ?>
                            </span>
                        </div>
                        <div class="categoria-actions">
                            <button class="btn-icon" onclick="editarCategoria(<?= $categoria['id'] ?>, '<?= htmlspecialchars($categoria['nome']) ?>', '<?= $categoria['icone'] ?>')">
                                <i class="material-icons-round">edit</i>
                            </button>
                            <button class="btn-icon" onclick="toggleCategoria(<?= $categoria['id'] ?>, <?= $categoria['ativa'] ? 'false' : 'true' ?>)">
                                <i class="material-icons-round"><?= $categoria['ativa'] ? 'visibility_off' : 'visibility' ?></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mostrarCategorias() {
            document.getElementById('categorias-modal').style.display = 'flex';
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function salvarCategoria() {
            const form = document.getElementById('categoria-form');
            const formData = new FormData(form);
            
            // Simular salvamento
            console.log('Categoria a ser salva:', Object.fromEntries(formData));
            
            mostrarNotificacao('Categoria adicionada com sucesso! (simulado)', 'success');
            form.reset();
            
            // Em produção, aqui faria um POST para salvar no banco
        }

        function editarCategoria(id, nome, icone) {
            const novoNome = prompt('Novo nome da categoria:', nome);
            const novoIcone = prompt('Novo ícone (Material Icons):', icone);
            
            if (novoNome) {
                console.log('Editando categoria:', { id, nome: novoNome, icone: novoIcone });
                mostrarNotificacao('Categoria editada! (simulado)', 'success');
                
                // Atualizar na interface
                const item = document.querySelector(`[data-id="${id}"] .categoria-info span`);
                if (item) item.textContent = novoNome;
            }
        }

        function toggleCategoria(id, ativar) {
            const acao = ativar ? 'ativada' : 'desativada';
            console.log(`Categoria ${id} ${acao}`);
            mostrarNotificacao(`Categoria ${acao}! (simulado)`, 'info');
        }

        function mostrarSubcategorias() {
            mostrarModal('Subcategorias', `
                <div class="subcategorias-content">
                    <div class="form-section">
                        <h4>Nova Subcategoria</h4>
                        <form class="inline-form">
                            <select class="form-input" required>
                                <option value="">Selecione a categoria...</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" class="form-input" placeholder="Nome da subcategoria" required>
                            <button type="button" class="btn btn-primary">Adicionar</button>
                        </form>
                    </div>
                    
                    <div class="subcategorias-lista">
                        <?php foreach ($subcategorias as $sub): ?>
                        <div class="subcategoria-item">
                            <div class="subcategoria-info">
                                <strong><?= htmlspecialchars($sub['nome']) ?></strong>
                                <small><?= htmlspecialchars($sub['categoria_nome']) ?></small>
                            </div>
                            <div class="subcategoria-actions">
                                <button class="btn-icon"><i class="material-icons-round">edit</i></button>
                                <button class="btn-icon"><i class="material-icons-round">delete</i></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <p class="note">Funcionalidade completa será implementada.</p>
            `);
        }

        function mostrarPedidos() {
            mostrarModal('Todos os Pedidos', `
                <div class="pedidos-admin">
                    <?php foreach ($pedidosRecentes as $pedido): ?>
                    <div class="pedido-admin-item">
                        <div class="pedido-header">
                            <h4>Pedido #<?= $pedido['id'] ?></h4>
                            <span class="status-badge status-<?= $pedido['status'] ?>">
                                <?= ucfirst($pedido['status']) ?>
                            </span>
                        </div>
                        <div class="pedido-details">
                            <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['usuario_nome'] ?: 'Não informado') ?></p>
                            <p><strong>Total:</strong> <?= formatarPreco($pedido['total']) ?></p>
                            <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></p>
                        </div>
                        <div class="pedido-actions">
                            <select class="form-input" onchange="alterarStatus(<?= $pedido['id'] ?>, this.value)">
                                <option value="pendente" ${pedido['status'] === 'pendente' ? 'selected' : ''}>Pendente</option>
                                <option value="confirmado" ${pedido['status'] === 'confirmado' ? 'selected' : ''}>Confirmado</option>
                                <option value="enviado" ${pedido['status'] === 'enviado' ? 'selected' : ''}>Enviado</option>
                                <option value="entregue" ${pedido['status'] === 'entregue' ? 'selected' : ''}>Entregue</option>
                                <option value="cancelado" ${pedido['status'] === 'cancelado' ? 'selected' : ''}>Cancelado</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="note">Em produção, os status seriam atualizados no banco de dados.</p>
            `);
        }

        function mostrarVendedores() {
            mostrarModal('Vendedores', `
                <div class="vendedores-lista">
                    <div class="vendedor-item">
                        <div class="vendedor-info">
                            <h4>João Silva</h4>
                            <p>Confecções Silva</p>
                            <p>joao@confeccoes.com</p>
                            <span class="status-badge ativo">Ativo</span>
                        </div>
                        <div class="vendedor-stats">
                            <small>5 produtos cadastrados</small>
                        </div>
                    </div>
                </div>
                <p class="note">Funcionalidade de gestão de vendedores será implementada.</p>
            `);
        }

        function alterarStatus(pedidoId, novoStatus) {
            console.log(`Pedido ${pedidoId} alterado para: ${novoStatus}`);
            mostrarNotificacao(`Status alterado para ${novoStatus}! (simulado)`, 'success');
        }

        function mostrarModal(titulo, conteudo) {
            const modal = document.createElement('div');
            modal.className = 'admin-modal';
            modal.innerHTML = `
                <div class="modal-content modal-large">
                    <div class="modal-header">
                        <h3>${titulo}</h3>
                        <button class="btn-icon" onclick="fecharAdminModal()">
                            <i class="material-icons-round">close</i>
                        </button>
                    </div>
                    <div class="modal-body">
                        ${conteudo}
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-secondary" onclick="fecharAdminModal()">Fechar</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            setTimeout(() => modal.classList.add('show'), 100);
            window.currentAdminModal = modal;
        }

        function fecharAdminModal() {
            const modal = window.currentAdminModal || document.querySelector('.admin-modal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    if (modal.parentNode) {
                        document.body.removeChild(modal);
                    }
                }, 300);
            }
        }

        function mostrarNotificacao(mensagem, tipo) {
            const notification = document.createElement('div');
            notification.className = `notification notification-${tipo}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="material-icons-round">${getNotificationIcon(tipo)}</i>
                    <span>${mensagem}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => notification.classList.add('show'), 100);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => document.body.removeChild(notification), 300);
            }, 3000);
        }

        function getNotificationIcon(tipo) {
            const icons = {
                success: 'check_circle',
                error: 'error',
                warning: 'warning',
                info: 'info'
            };
            return icons[tipo] || 'info';
        }
    </script>

    <style>
        body {
            padding-bottom: 0;
        }

        .admin-header {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
        }

        .admin-header .logo {
            color: white;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: var(--space);
            color: white;
        }

        .admin-info span {
            font-size: 0.9rem;
        }

        .admin-info .btn-icon {
            color: white;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: var(--space);
            margin: var(--space) 0 var(--space-lg);
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: var(--space);
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-container);
            display: flex;
            align-items: center;
            gap: var(--space);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: #dc2626;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .stat-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: var(--space-xs);
            color: #dc2626;
        }

        .stat-info p {
            font-size: 0.9rem;
            color: var(--on-surface-variant);
            margin: 0;
        }

        .action-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: var(--space);
            margin-bottom: var(--space-lg);
        }

        .action-btn {
            background: var(--surface);
            border: 2px solid #dc2626;
            border-radius: var(--radius-md);
            padding: var(--space);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-sm);
            color: #dc2626;
            cursor: pointer;
            transition: var(--transition);
        }

        .action-btn:hover {
            background: #dc2626;
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .recent-section {
            margin-bottom: var(--space-lg);
        }

        .orders-list {
            display: flex;
            flex-direction: column;
            gap: var(--space);
        }

        .order-item {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: var(--space);
            display: flex;
            justify-content: space-between;
            align-items: start;
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-container);
        }

        .order-info h4 {
            margin-bottom: var(--space-xs);
            font-size: 1rem;
        }

        .order-info p {
            font-size: 0.9rem;
            color: var(--on-surface-variant);
            margin-bottom: var(--space-xs);
        }

        .order-date {
            font-size: 0.8rem;
            color: var(--on-surface-variant);
        }

        .status-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .status-pendente { background: #f59e0b; color: white; }
        .status-confirmado { background: #3b82f6; color: white; }
        .status-enviado { background: #8b5cf6; color: white; }
        .status-entregue { background: #22c55e; color: white; }
        .status-cancelado { background: #ef4444; color: white; }

        /* Modal específico do admin */
        .modal-large {
            max-width: 600px;
        }

        .form-section {
            margin-bottom: var(--space-lg);
            padding-bottom: var(--space);
            border-bottom: 1px solid var(--surface-container);
        }

        .form-section h4 {
            margin-bottom: var(--space);
        }

        .inline-form {
            display: flex;
            gap: var(--space);
            align-items: end;
        }

        .inline-form .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .categorias-lista, .subcategorias-lista {
            max-height: 300px;
            overflow-y: auto;
        }

        .categoria-item, .subcategoria-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-sm);
            background: var(--surface-variant);
            border-radius: var(--radius);
            margin-bottom: var(--space-xs);
        }

        .categoria-info {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .categoria-actions, .subcategoria-actions {
            display: flex;
            gap: var(--space-xs);
        }

        .admin-modal {
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

        .admin-modal.show {
            opacity: 1;
        }        .note {
            font-size: 0.8rem;
            color: var(--on-surface-variant);
            text-align: center;
            font-style: italic;
            margin-top: var(--space);
        }
    </style>

    <script src="../assets/js/app.js"></script>
</body>
</html>
