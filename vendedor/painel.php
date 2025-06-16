<?php
require_once '../includes/config.php';
iniciarSessao();

// Verificar login
if (!verificarLoginVendedor()) {
    header("Location: login.php");
    exit;
}

$pdo = conectar();
$vendedor_id = $_SESSION['vendedor_id'];

// Buscar dados do vendedor
$stmt = $pdo->prepare("SELECT * FROM vendedores WHERE id = ?");
$stmt->execute([$vendedor_id]);
$vendedor = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar estatísticas
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM produtos WHERE vendedor_id = ? AND ativo = 1");
$stmt->execute([$vendedor_id]);
$totalProdutos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM itens_pedido ip 
    JOIN produtos p ON ip.produto_id = p.id 
    WHERE p.vendedor_id = ?
");
$stmt->execute([$vendedor_id]);
$totalVendas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Buscar produtos recentes
$stmt = $pdo->prepare("
    SELECT * FROM produtos 
    WHERE vendedor_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$vendedor_id]);
$produtosRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar categorias para o formulário
$stmt = $pdo->query("SELECT * FROM categorias WHERE ativa = 1 ORDER BY nome");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Vendedor - Vesty Brasil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="theme-color" content="#6366F1">
</head>
<body>    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">Vesty Brasil</a>
                <div class="vendor-info">
                    <div class="theme-toggle">
                        <div class="theme-toggle-slider">
                            <span class="theme-toggle-icon">☀️</span>
                        </div>
                    </div>
                    <span>Olá, <?= htmlspecialchars($vendedor['nome']) ?></span>
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
                    <p>Produtos Cadastrados</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="material-icons-round">shopping_cart</i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalVendas ?></h3>
                    <p>Itens Vendidos</p>
                </div>
            </div>
        </div>

        <!-- Menu de Ações -->
        <div class="action-menu">
            <button class="action-btn" onclick="mostrarFormularioProduto()">
                <i class="material-icons-round">add_box</i>
                <span>Cadastrar Produto</span>
            </button>
            
            <button class="action-btn" onclick="mostrarProdutos()">
                <i class="material-icons-round">inventory_2</i>
                <span>Meus Produtos</span>
            </button>
            
            <button class="action-btn" onclick="mostrarPedidos()">
                <i class="material-icons-round">receipt_long</i>
                <span>Pedidos Recebidos</span>
            </button>
        </div>

        <!-- Produtos Recentes -->
        <?php if (!empty($produtosRecentes)): ?>
        <section class="recent-products">
            <div class="section-header">
                <h2 class="section-title">Produtos Recentes</h2>
                <button class="section-action" onclick="mostrarProdutos()">Ver todos</button>
            </div>
            <div class="products-list">
                <?php foreach ($produtosRecentes as $produto): ?>
                <div class="product-item">
                    <div class="product-image-small">
                        <?php if ($produto['imagem']): ?>
                            <img src="../assets/images/produtos/<?= $produto['imagem'] ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                        <?php else: ?>
                            <i class="material-icons-round">image</i>
                        <?php endif; ?>
                    </div>
                    <div class="product-details">
                        <h4><?= htmlspecialchars($produto['nome']) ?></h4>
                        <p><?= formatarPreco($produto['preco']) ?></p>
                        <span class="product-status <?= $produto['ativo'] ? 'ativo' : 'inativo' ?>">
                            <?= $produto['ativo'] ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </div>
                    <div class="product-actions-small">
                        <button class="btn-icon" onclick="editarProduto(<?= $produto['id'] ?>)">
                            <i class="material-icons-round">edit</i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Modal para Cadastro de Produto -->
    <div id="produto-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cadastrar Produto</h3>
                <button class="btn-icon" onclick="fecharModal()">
                    <i class="material-icons-round">close</i>
                </button>
            </div>
            <form id="produto-form" class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nome do Produto:</label>
                    <input type="text" name="nome" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Descrição:</label>
                    <textarea name="descricao" class="form-input form-textarea" rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Preço (R$):</label>
                        <input type="number" name="preco" class="form-input" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Estoque:</label>
                        <input type="number" name="estoque" class="form-input" min="0" value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Categoria:</label>
                    <select name="categoria_id" class="form-input" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tamanhos (separados por vírgula):</label>
                    <input type="text" name="tamanhos" class="form-input" placeholder="P, M, G, GG">
                </div>

                <div class="form-group">
                    <label class="form-label">Imagem:</label>
                    <input type="file" name="imagem" class="form-input" accept="image/*">
                    <small>Máximo 2MB. Formatos: JPG, PNG, WEBP</small>
                </div>

                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="destaque">
                        <span class="checkmark"></span>
                        Produto em destaque
                    </label>
                </div>
            </form>
            <div class="modal-actions">
                <button type="button" class="btn btn-primary" onclick="salvarProduto()">
                    <i class="material-icons-round">save</i> Salvar
                </button>
                <button type="button" class="btn btn-secondary" onclick="fecharModal()">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        function mostrarFormularioProduto() {
            document.getElementById('produto-modal').style.display = 'flex';
        }

        function fecharModal() {
            document.getElementById('produto-modal').style.display = 'none';
            document.getElementById('produto-form').reset();
        }

        function salvarProduto() {
            const form = document.getElementById('produto-form');
            const formData = new FormData(form);
            
            // Simular salvamento
            console.log('Produto a ser salvo:', Object.fromEntries(formData));
            
            // Mostrar notificação
            mostrarNotificacao('Produto cadastrado com sucesso! (simulado)', 'success');
            fecharModal();
            
            // Em produção, aqui faria um POST para salvar no banco
        }

        function mostrarProdutos() {
            mostrarModal('Meus Produtos', `
                <div class="produtos-lista">
                    <?php foreach ($produtosRecentes as $produto): ?>
                    <div class="produto-item-modal">
                        <div class="produto-info">
                            <h4><?= htmlspecialchars($produto['nome']) ?></h4>
                            <p><?= formatarPreco($produto['preco']) ?> • Estoque: <?= $produto['estoque'] ?></p>
                            <span class="status-badge ${produto['ativo'] ? 'ativo' : 'inativo'}">
                                <?= $produto['ativo'] ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </div>
                        <div class="produto-acoes">
                            <button class="btn btn-small btn-secondary">Editar</button>
                            <button class="btn btn-small ${produto['ativo'] ? 'btn-warning' : 'btn-primary'}">
                                ${produto['ativo'] ? 'Desativar' : 'Ativar'}
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="note">Funcionalidade completa será implementada.</p>
            `);
        }

        function mostrarPedidos() {
            mostrarModal('Pedidos Recebidos', `
                <div class="pedidos-lista">
                    <div class="pedido-item-vendor">
                        <div class="pedido-info">
                            <h4>Pedido #12345</h4>
                            <p>Cliente: Maria Silva</p>
                            <p>2 itens • Total: R$ 159,80</p>
                            <span class="status-badge pendente">Pendente</span>
                        </div>
                        <div class="pedido-data">Hoje, 14:30</div>
                    </div>
                    <div class="pedido-item-vendor">
                        <div class="pedido-info">
                            <h4>Pedido #12344</h4>
                            <p>Cliente: João Santos</p>
                            <p>1 item • Total: R$ 89,90</p>
                            <span class="status-badge confirmado">Confirmado</span>
                        </div>
                        <div class="pedido-data">Ontem, 16:45</div>
                    </div>
                </div>
                <p class="note">Em produção, você poderia alterar status e gerenciar os pedidos.</p>
            `);
        }

        function editarProduto(id) {
            mostrarNotificacao('Funcionalidade de edição será implementada', 'info');
        }

        function mostrarModal(titulo, conteudo) {
            const modal = document.createElement('div');
            modal.className = 'vendor-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>${titulo}</h3>
                        <button class="btn-icon" onclick="fecharVendorModal()">
                            <i class="material-icons-round">close</i>
                        </button>
                    </div>
                    <div class="modal-body">
                        ${conteudo}
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-secondary" onclick="fecharVendorModal()">Fechar</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            setTimeout(() => modal.classList.add('show'), 100);
            window.currentVendorModal = modal;
        }

        function fecharVendorModal() {
            const modal = window.currentVendorModal || document.querySelector('.vendor-modal');
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

            setTimeout(() => {
                notification.classList.add('show');
            }, 100);

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
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

        .vendor-info {
            display: flex;
            align-items: center;
            gap: var(--space);
        }

        .vendor-info span {
            font-size: 0.9rem;
            color: var(--on-surface-variant);
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
            background: var(--primary);
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
            color: var(--primary);
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
            border: 2px solid var(--primary);
            border-radius: var(--radius-md);
            padding: var(--space);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-sm);
            color: var(--primary);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .action-btn:hover {
            background: var(--primary);
            color: var(--on-primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .action-btn i {
            font-size: 2rem;
        }

        .action-btn span {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .recent-products {
            margin-bottom: var(--space-lg);
        }

        .products-list {
            display: flex;
            flex-direction: column;
            gap: var(--space);
        }

        .product-item {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: var(--space);
            display: flex;
            align-items: center;
            gap: var(--space);
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-container);
        }

        .product-image-small {
            width: 60px;
            height: 60px;
            border-radius: var(--radius);
            background: var(--surface-container);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-image-small img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-image-small i {
            color: var(--on-surface-variant);
            font-size: 1.5rem;
        }

        .product-details {
            flex: 1;
        }

        .product-details h4 {
            margin-bottom: var(--space-xs);
            font-size: 1rem;
        }

        .product-details p {
            font-size: 0.9rem;
            color: var(--on-surface-variant);
            margin-bottom: var(--space-xs);
        }

        .product-status {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .product-status.ativo {
            background: var(--success);
            color: white;
        }

        .product-status.inativo {
            background: var(--error);
            color: white;
        }

        .product-actions-small {
            display: flex;
            gap: var(--space-xs);
        }

        /* Modal Styles */
        .modal {
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
            padding: var(--space);
        }

        .modal-content {
            background: var(--surface);
            border-radius: var(--radius-md);
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space);
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            cursor: pointer;
        }

        .vendor-modal {
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

        .vendor-modal.show {
            opacity: 1;
        }

        .vendor-modal .modal-content {
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .vendor-modal.show .modal-content {
            transform: translateY(0);
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
