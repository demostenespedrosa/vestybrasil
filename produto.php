<?php
require_once 'includes/config.php';
iniciarSessao();

$produto_id = $_GET['id'] ?? null;
if (!$produto_id) {
    header("Location: index.php");
    exit;
}

$pdo = conectar();

// Buscar produto
$stmt = $pdo->prepare("
    SELECT p.*, c.nome as categoria_nome, s.nome as subcategoria_nome, v.nome as vendedor_nome, v.empresa
    FROM produtos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    LEFT JOIN subcategorias s ON p.subcategoria_id = s.id
    LEFT JOIN vendedores v ON p.vendedor_id = v.id
    WHERE p.id = ? AND p.ativo = 1
");
$stmt->execute([$produto_id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header("Location: index.php");
    exit;
}

// Decodificar tamanhos
$tamanhos = json_decode($produto['tamanhos'], true) ?: [];

// Buscar produtos relacionados da mesma categoria
$stmt = $pdo->prepare("
    SELECT p.* FROM produtos p 
    WHERE p.categoria_id = ? AND p.id != ? AND p.ativo = 1 
    ORDER BY RAND() LIMIT 4
");
$stmt->execute([$produto['categoria_id'], $produto_id]);
$produtosRelacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produto['nome']) ?> - Vesty Brasil</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="theme-color" content="#6366F1">
    <meta name="description" content="<?= htmlspecialchars(substr($produto['descricao'], 0, 160)) ?>">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="javascript:history.back()" class="btn-icon">
                    <i class="material-icons-round">arrow_back</i>
                </a>
                <h1 style="font-size: 1.1rem; font-weight: 600; margin: 0; flex: 1; text-align: center;">Produto</h1>                <div class="header-actions">
                    <div class="theme-toggle">
                        <div class="theme-toggle-slider">
                            <span class="theme-toggle-icon">☀️</span>
                        </div>
                    </div>
                    <button class="btn-icon share-btn" onclick="compartilharProduto()">
                        <i class="material-icons-round">share</i>
                    </button>
                    <a href="carrinho.php" class="btn-icon">
                        <i class="material-icons-round">shopping_cart</i>
                        <span class="badge" id="carrinho-count" style="display: none;">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Imagem do Produto -->
        <div class="product-gallery">
            <div class="product-main-image">
                <?php if ($produto['imagem']): ?>
                    <img src="assets/images/produtos/<?= $produto['imagem'] ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" id="main-image">
                <?php else: ?>
                    <div class="no-image">
                        <i class="material-icons-round">image</i>
                        <p>Sem imagem</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Informações do Produto -->
        <div class="product-details">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="categorias.php"><?= htmlspecialchars($produto['categoria_nome']) ?></a>
                <?php if ($produto['subcategoria_nome']): ?>
                <span> > </span>
                <span><?= htmlspecialchars($produto['subcategoria_nome']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Nome e Preço -->
            <h1 class="product-title"><?= htmlspecialchars($produto['nome']) ?></h1>
            <div class="product-price-section">
                <div class="product-price-main"><?= formatarPreco($produto['preco']) ?></div>
                <div class="product-vendor">Por: <?= htmlspecialchars($produto['empresa'] ?: $produto['vendedor_nome']) ?></div>
            </div>

            <!-- Tamanhos -->
            <?php if (!empty($tamanhos)): ?>
            <div class="product-sizes">
                <h3>Tamanho:</h3>
                <div class="sizes-grid" id="sizes-grid">
                    <?php foreach ($tamanhos as $tamanho): ?>
                    <button class="size-btn" data-size="<?= htmlspecialchars($tamanho) ?>" onclick="selecionarTamanho(this)">
                        <?= htmlspecialchars($tamanho) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Descrição -->
            <?php if ($produto['descricao']): ?>
            <div class="product-description">
                <h3>Descrição:</h3>
                <p><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Informações do Vendedor -->
            <div class="vendor-info">
                <h3>Vendedor:</h3>
                <div class="vendor-card">
                    <div class="vendor-details">
                        <h4><?= htmlspecialchars($produto['empresa'] ?: $produto['vendedor_nome']) ?></h4>
                        <p>Fabricante de Pernambuco</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produtos Relacionados -->
        <?php if (!empty($produtosRelacionados)): ?>
        <section class="related-products">
            <div class="section-header">
                <h2 class="section-title">Produtos Relacionados</h2>
            </div>
            <div class="products-grid">
                <?php foreach ($produtosRelacionados as $relacionado): ?>
                <div class="product-card">
                    <a href="produto.php?id=<?= $relacionado['id'] ?>" class="product-link">
                        <div class="product-image">
                            <?php if ($relacionado['imagem']): ?>
                                <img src="assets/images/produtos/<?= $relacionado['imagem'] ?>" alt="<?= htmlspecialchars($relacionado['nome']) ?>">
                            <?php else: ?>
                                <i class="material-icons-round">image</i>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($relacionado['nome']) ?></h3>
                            <div class="product-price"><?= formatarPreco($relacionado['preco']) ?></div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Ações Fixas -->
    <div class="product-actions-fixed">
        <div class="container">
            <div class="actions-row">
                <button class="btn-icon btn-favorite add-to-favorites" 
                        data-produto-id="<?= $produto['id'] ?>"
                        data-produto-nome="<?= htmlspecialchars($produto['nome']) ?>"
                        data-produto-preco="<?= $produto['preco'] ?>"
                        data-produto-imagem="<?= $produto['imagem'] ?>">
                    <i class="material-icons-round">favorite_border</i>
                </button>
                <button class="btn btn-primary btn-add-cart" id="btn-add-cart" onclick="adicionarProdutoCarrinho()">
                    <i class="material-icons-round">shopping_cart</i>
                    Adicionar ao Carrinho
                </button>
            </div>
        </div>
    </div>

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
        <a href="perfil.php" class="nav-item">
            <i class="material-icons-round nav-icon">person</i>
            <span class="nav-label">Perfil</span>
        </a>
    </nav>

    <script src="assets/js/app.js"></script>
    <script>
        let tamanhoSelecionado = null;
        
        function selecionarTamanho(btn) {
            // Remove seleção anterior
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
            
            // Adiciona seleção
            btn.classList.add('selected');
            tamanhoSelecionado = btn.dataset.size;
            
            // Atualiza botão de adicionar
            const addBtn = document.getElementById('btn-add-cart');
            addBtn.classList.remove('disabled');
        }
        
        function adicionarProdutoCarrinho() {
            const addBtn = document.getElementById('btn-add-cart');
            
            // Se tem tamanhos, precisa selecionar um
            const temTamanhos = document.getElementById('sizes-grid');
            if (temTamanhos && !tamanhoSelecionado) {
                window.vestyApp.mostrarNotificacao('Selecione um tamanho', 'warning');
                return;
            }
            
            // Simular click no botão add-to-cart
            addBtn.dataset.produtoId = '<?= $produto['id'] ?>';
            addBtn.dataset.produtoNome = '<?= htmlspecialchars($produto['nome']) ?>';
            addBtn.dataset.produtoPreco = '<?= $produto['preco'] ?>';
            addBtn.dataset.produtoImagem = '<?= $produto['imagem'] ?>';
            addBtn.dataset.tamanho = tamanhoSelecionado || 'Único';
            
            addBtn.classList.add('add-to-cart');
            addBtn.click();
        }
        
        function compartilharProduto() {
            if (navigator.share) {
                navigator.share({
                    title: '<?= htmlspecialchars($produto['nome']) ?>',
                    text: 'Confira este produto na Vesty Brasil!',
                    url: window.location.href
                });
            } else {
                // Fallback: copiar para clipboard
                navigator.clipboard.writeText(window.location.href);
                window.vestyApp.mostrarNotificacao('Link copiado!', 'success');
            }
        }
        
        // Inicializar tamanho se há apenas um
        document.addEventListener('DOMContentLoaded', () => {
            const tamanhos = document.querySelectorAll('.size-btn');
            if (tamanhos.length === 1) {
                tamanhos[0].click();
            }
        });
    </script>

    <style>
        body {
            padding-bottom: 140px; /* Espaço para actions + navbar */
        }

        .product-gallery {
            margin-bottom: var(--space);
        }

        .product-main-image {
            width: 100%;
            height: 300px;
            border-radius: var(--radius-md);
            overflow: hidden;
            background: var(--surface-container);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-image {
            text-align: center;
            color: var(--on-surface-variant);
        }

        .no-image i {
            font-size: 4rem;
            margin-bottom: var(--space-sm);
        }

        .product-details {
            padding: var(--space) 0;
        }

        .breadcrumb {
            font-size: 0.8rem;
            color: var(--on-surface-variant);
            margin-bottom: var(--space);
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        .product-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: var(--space);
            line-height: 1.3;
        }

        .product-price-section {
            margin-bottom: var(--space-lg);
        }

        .product-price-main {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: var(--space-xs);
        }

        .product-vendor {
            font-size: 0.9rem;
            color: var(--on-surface-variant);
        }

        .product-sizes {
            margin-bottom: var(--space-lg);
        }

        .product-sizes h3 {
            margin-bottom: var(--space);
            font-size: 1.1rem;
        }

        .sizes-grid {
            display: flex;
            gap: var(--space-sm);
            flex-wrap: wrap;
        }

        .size-btn {
            padding: var(--space-sm) var(--space);
            border: 2px solid var(--surface-container);
            border-radius: var(--radius);
            background: var(--surface);
            color: var(--on-surface);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            min-width: 48px;
        }

        .size-btn:hover {
            border-color: var(--primary);
        }

        .size-btn.selected {
            border-color: var(--primary);
            background: var(--primary);
            color: var(--on-primary);
        }

        .product-description,
        .vendor-info {
            margin-bottom: var(--space-lg);
        }

        .product-description h3,
        .vendor-info h3 {
            margin-bottom: var(--space);
            font-size: 1.1rem;
        }

        .product-description p {
            line-height: 1.6;
            color: var(--on-surface-variant);
        }

        .vendor-card {
            background: var(--surface-variant);
            padding: var(--space);
            border-radius: var(--radius);
            border: 1px solid var(--surface-container);
        }

        .vendor-details h4 {
            margin-bottom: var(--space-xs);
            font-weight: 600;
        }

        .vendor-details p {
            font-size: 0.9rem;
            color: var(--on-surface-variant);
            margin: 0;
        }

        .related-products {
            margin: var(--space-lg) 0;
            padding-top: var(--space-lg);
            border-top: 1px solid var(--surface-container);
        }

        .product-actions-fixed {
            position: fixed;
            bottom: 80px;
            left: 0;
            right: 0;
            background: var(--surface);
            border-top: 1px solid var(--surface-container);
            padding: var(--space);
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .actions-row {
            display: flex;
            gap: var(--space);
            align-items: center;
        }

        .btn-favorite {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-md);
            border: 2px solid var(--primary);
            background: var(--surface);
            color: var(--primary);
            font-size: 1.5rem;
        }

        .btn-add-cart {
            flex: 1;
            height: 56px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .btn-add-cart.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</body>
</html>
