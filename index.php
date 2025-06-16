<?php
require_once 'includes/config.php';
iniciarSessao();

// Buscar categorias
$pdo = conectar();
$stmt = $pdo->query("SELECT * FROM categorias WHERE ativa = 1 ORDER BY nome");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar produtos em destaque
$stmt = $pdo->query("SELECT p.*, c.nome as categoria_nome FROM produtos p 
                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                     WHERE p.destaque = 1 AND p.ativo = 1 
                     ORDER BY p.created_at DESC LIMIT 8");
$produtosDestaque = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar produtos recentes
$stmt = $pdo->query("SELECT p.*, c.nome as categoria_nome FROM produtos p 
                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                     WHERE p.ativo = 1 
                     ORDER BY p.created_at DESC LIMIT 6");
$produtosRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vesty Brasil - Moda Pernambucana</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#6366F1">
    <meta name="description" content="Marketplace de moda do polo de confecções de Pernambuco">
    <link rel="apple-touch-icon" href="assets/images/icon-192.png">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">Vesty Brasil</a>                <div class="header-actions">
                    <button class="btn-icon" onclick="toggleSearch()">
                        <i class="material-icons-round">search</i>
                    </button>
                    <div class="theme-toggle">
                        <div class="theme-toggle-slider">
                            <span class="theme-toggle-icon">☀️</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Search Bar -->
    <div id="search-container" class="search-container" style="display: none;">
        <div class="container">
            <div class="search-box">
                <input type="text" id="search" placeholder="Buscar produtos..." class="form-input">
                <button onclick="toggleSearch()" class="btn-icon">
                    <i class="material-icons-round">close</i>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <!-- Banner Promocional -->
        <section class="banner">
            <div class="banner-content">
                <h2>🔥 Moda Pernambucana</h2>
                <p>Descubra as melhores peças direto dos fabricantes</p>
                <a href="categorias.php" class="btn btn-secondary">Explorar Agora</a>
            </div>
        </section>

        <!-- Categorias -->
        <section class="categories">
            <div class="section-header">
                <h2 class="section-title">Categorias</h2>
                <a href="categorias.php" class="section-action">Ver todas</a>
            </div>
            <div class="categories-grid">
                <?php foreach ($categorias as $categoria): ?>
                <a href="categoria.php?id=<?= $categoria['id'] ?>" class="category-item">
                    <i class="material-icons-round category-icon"><?= $categoria['icone'] ?: 'category' ?></i>
                    <span class="category-name"><?= htmlspecialchars($categoria['nome']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Produtos em Destaque -->
        <?php if (!empty($produtosDestaque)): ?>
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">⭐ Produtos em Destaque</h2>
                <a href="produtos.php?destaque=1" class="section-action">Ver todos</a>
            </div>
            <div class="products-grid">
                <?php foreach ($produtosDestaque as $produto): ?>
                <div class="product-card">
                    <a href="produto.php?id=<?= $produto['id'] ?>" class="product-link">
                        <div class="product-image">
                            <?php if ($produto['imagem']): ?>
                                <img src="assets/images/produtos/<?= $produto['imagem'] ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                            <?php else: ?>
                                <i class="material-icons-round">image</i>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($produto['nome']) ?></h3>
                            <div class="product-price"><?= formatarPreco($produto['preco']) ?></div>
                        </div>
                    </a>
                    <div class="product-actions">
                        <button class="btn-icon add-to-favorites" 
                                data-produto-id="<?= $produto['id'] ?>"
                                data-produto-nome="<?= htmlspecialchars($produto['nome']) ?>"
                                data-produto-preco="<?= $produto['preco'] ?>"
                                data-produto-imagem="<?= $produto['imagem'] ?>">
                            <i class="material-icons-round">favorite_border</i>
                        </button>
                        <button class="btn btn-primary btn-small add-to-cart"
                                data-produto-id="<?= $produto['id'] ?>"
                                data-produto-nome="<?= htmlspecialchars($produto['nome']) ?>"
                                data-produto-preco="<?= $produto['preco'] ?>"
                                data-produto-imagem="<?= $produto['imagem'] ?>">
                            <i class="material-icons-round">shopping_cart</i> Adicionar
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Produtos Recentes -->
        <?php if (!empty($produtosRecentes)): ?>
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">🆕 Novidades</h2>
                <a href="produtos.php" class="section-action">Ver todos</a>
            </div>
            <div class="products-grid">
                <?php foreach ($produtosRecentes as $produto): ?>
                <div class="product-card">
                    <a href="produto.php?id=<?= $produto['id'] ?>" class="product-link">
                        <div class="product-image">
                            <?php if ($produto['imagem']): ?>
                                <img src="assets/images/produtos/<?= $produto['imagem'] ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                            <?php else: ?>
                                <i class="material-icons-round">image</i>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($produto['nome']) ?></h3>
                            <div class="product-price"><?= formatarPreco($produto['preco']) ?></div>
                        </div>
                    </a>
                    <div class="product-actions">
                        <button class="btn-icon add-to-favorites" 
                                data-produto-id="<?= $produto['id'] ?>"
                                data-produto-nome="<?= htmlspecialchars($produto['nome']) ?>"
                                data-produto-preco="<?= $produto['preco'] ?>"
                                data-produto-imagem="<?= $produto['imagem'] ?>">
                            <i class="material-icons-round">favorite_border</i>
                        </button>
                        <button class="btn btn-primary btn-small add-to-cart"
                                data-produto-id="<?= $produto['id'] ?>"
                                data-produto-nome="<?= htmlspecialchars($produto['nome']) ?>"
                                data-produto-preco="<?= $produto['preco'] ?>"
                                data-produto-imagem="<?= $produto['imagem'] ?>">
                            <i class="material-icons-round">shopping_cart</i> Adicionar
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="index.php" class="nav-item active">
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
        function toggleSearch() {
            const searchContainer = document.getElementById('search-container');
            const searchInput = document.getElementById('search');
            
            if (searchContainer.style.display === 'none') {
                searchContainer.style.display = 'block';
                searchInput.focus();
            } else {
                searchContainer.style.display = 'none';
                searchInput.value = '';
                // Recarregar produtos se estava filtrando
                if (window.vestyApp) {
                    window.vestyApp.carregarProdutos();
                }
            }
        }
    </script>

    <style>
        .search-container {
            background: var(--surface);
            border-bottom: 1px solid var(--surface-container);
            padding: var(--space);
            position: sticky;
            top: 72px;
            z-index: 99;
        }

        .search-box {
            display: flex;
            gap: var(--space-sm);
            align-items: center;
        }

        .search-box input {
            flex: 1;
        }
    </style>
</body>
</html>
