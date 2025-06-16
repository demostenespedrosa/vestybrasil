<?php
require_once 'includes/config.php';
iniciarSessao();

$pdo = conectar();
$categoria_id = $_GET['id'] ?? null;
$subcategoria_id = $_GET['subcategoria'] ?? null;
$categoria = null;
$subcategoria = null;
$produtos = [];

if ($subcategoria_id) {
    // Buscar subcategoria
    $stmt = $pdo->prepare("SELECT s.*, c.nome as categoria_nome FROM subcategorias s 
                          LEFT JOIN categorias c ON s.categoria_id = c.id 
                          WHERE s.id = ? AND s.ativa = 1");
    $stmt->execute([$subcategoria_id]);
    $subcategoria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($subcategoria) {
        // Buscar produtos da subcategoria
        $stmt = $pdo->prepare("SELECT p.*, c.nome as categoria_nome FROM produtos p 
                              LEFT JOIN categorias c ON p.categoria_id = c.id 
                              WHERE p.subcategoria_id = ? AND p.ativo = 1 
                              ORDER BY p.created_at DESC");
        $stmt->execute([$subcategoria_id]);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} elseif ($categoria_id) {
    // Buscar categoria
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ? AND ativa = 1");
    $stmt->execute([$categoria_id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($categoria) {
        // Buscar produtos da categoria
        $stmt = $pdo->prepare("SELECT p.*, c.nome as categoria_nome, s.nome as subcategoria_nome 
                              FROM produtos p 
                              LEFT JOIN categorias c ON p.categoria_id = c.id 
                              LEFT JOIN subcategorias s ON p.subcategoria_id = s.id
                              WHERE p.categoria_id = ? AND p.ativo = 1 
                              ORDER BY p.created_at DESC");
        $stmt->execute([$categoria_id]);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!$categoria && !$subcategoria) {
    header("Location: categorias.php");
    exit;
}

$titulo = $subcategoria ? $subcategoria['nome'] : $categoria['nome'];
$subtitulo = $subcategoria ? "Categoria: " . $subcategoria['categoria_nome'] : count($produtos) . " produtos encontrados";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> - Vesty Brasil</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="theme-color" content="#6366F1">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="categorias.php" class="btn-icon">
                    <i class="material-icons-round">arrow_back</i>
                </a>
                <div class="header-title">
                    <h1 style="font-size: 1.1rem; font-weight: 600; margin: 0;"><?= htmlspecialchars($titulo) ?></h1>
                    <p style="font-size: 0.8rem; color: var(--on-surface-variant); margin: 0;"><?= $subtitulo ?></p>
                </div>                <div class="header-actions">
                    <div class="theme-toggle">
                        <div class="theme-toggle-slider">
                            <span class="theme-toggle-icon">☀️</span>
                        </div>
                    </div>
                    <a href="favoritos.php" class="btn-icon">
                        <i class="material-icons-round">favorite</i>
                        <span class="badge" id="favoritos-count" style="display: none;">0</span>
                    </a>
                    <a href="carrinho.php" class="btn-icon">
                        <i class="material-icons-round">shopping_cart</i>
                        <span class="badge" id="carrinho-count" style="display: none;">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Filtros -->
    <div class="filters-container">
        <div class="container">
            <div class="filters">
                <select class="filter-select" onchange="ordenarProdutos(this.value)">
                    <option value="recentes">Mais recentes</option>
                    <option value="preco_menor">Menor preço</option>
                    <option value="preco_maior">Maior preço</option>
                    <option value="nome">Nome A-Z</option>
                </select>
                <button class="btn-icon" onclick="toggleGrid()">
                    <i class="material-icons-round" id="grid-icon">grid_view</i>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <?php if (empty($produtos)): ?>
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h3>Nenhum produto encontrado</h3>
            <p>Não há produtos nesta categoria no momento.</p>
            <a href="categorias.php" class="btn btn-primary">Ver outras categorias</a>
        </div>
        <?php else: ?>
        <div class="products-grid" id="produtos-grid">
            <?php foreach ($produtos as $produto): ?>
            <div class="product-card" data-produto='<?= json_encode($produto) ?>'>
                <a href="produto.php?id=<?= $produto['id'] ?>" class="product-link">
                    <div class="product-image">
                        <?php if ($produto['imagem']): ?>
                            <img src="assets/images/produtos/<?= $produto['imagem'] ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" loading="lazy">
                        <?php else: ?>
                            <i class="material-icons-round">image</i>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($produto['nome']) ?></h3>
                        <?php if ($produto['subcategoria_nome']): ?>
                        <p class="product-category"><?= htmlspecialchars($produto['subcategoria_nome']) ?></p>
                        <?php endif; ?>
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
        <?php endif; ?>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="index.php" class="nav-item">
            <i class="material-icons-round nav-icon">home</i>
            <span class="nav-label">Início</span>
        </a>
        <a href="categorias.php" class="nav-item active">
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
        let gridMode = 'grid'; // 'grid' ou 'list'
        
        function ordenarProdutos(criterio) {
            const grid = document.getElementById('produtos-grid');
            const produtos = Array.from(grid.children);
            
            produtos.sort((a, b) => {
                const produtoA = JSON.parse(a.dataset.produto);
                const produtoB = JSON.parse(b.dataset.produto);
                
                switch (criterio) {
                    case 'preco_menor':
                        return parseFloat(produtoA.preco) - parseFloat(produtoB.preco);
                    case 'preco_maior':
                        return parseFloat(produtoB.preco) - parseFloat(produtoA.preco);
                    case 'nome':
                        return produtoA.nome.localeCompare(produtoB.nome);
                    case 'recentes':
                    default:
                        return new Date(produtoB.created_at) - new Date(produtoA.created_at);
                }
            });
            
            // Reordenar elementos
            produtos.forEach(produto => grid.appendChild(produto));
        }
        
        function toggleGrid() {
            const grid = document.getElementById('produtos-grid');
            const icon = document.getElementById('grid-icon');
            
            if (gridMode === 'grid') {
                grid.classList.add('list-view');
                icon.textContent = 'view_list';
                gridMode = 'list';
            } else {
                grid.classList.remove('list-view');
                icon.textContent = 'grid_view';
                gridMode = 'grid';
            }
        }
    </script>

    <style>
        .header-title {
            flex: 1;
            text-align: center;
        }

        .filters-container {
            background: var(--surface);
            border-bottom: 1px solid var(--surface-container);
            padding: var(--space-sm) 0;
        }

        .filters {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space);
        }

        .filter-select {
            padding: var(--space-sm) var(--space);
            border: 1px solid var(--surface-container);
            border-radius: var(--radius);
            background: var(--surface);
            color: var(--on-surface);
            font-size: 0.9rem;
            min-width: 150px;
        }

        .product-category {
            font-size: 0.8rem;
            color: var(--on-surface-variant);
            margin: var(--space-xs) 0;
        }

        .products-grid.list-view {
            display: flex;
            flex-direction: column;
            gap: var(--space);
        }

        .products-grid.list-view .product-card {
            display: flex;
            align-items: center;
            padding: var(--space);
        }

        .products-grid.list-view .product-image {
            width: 80px;
            height: 80px;
            margin-right: var(--space);
            flex-shrink: 0;
        }

        .products-grid.list-view .product-info {
            flex: 1;
            padding: 0;
        }

        .products-grid.list-view .product-actions {
            flex-direction: column;
            gap: var(--space-xs);
            padding: 0;
            border: none;
        }
    </style>
</body>
</html>
