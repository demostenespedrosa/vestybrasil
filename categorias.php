<?php
require_once 'includes/config.php';
iniciarSessao();

$pdo = conectar();

// Buscar todas as categorias com suas subcategorias
$stmt = $pdo->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM produtos p WHERE p.categoria_id = c.id AND p.ativo = 1) as total_produtos
    FROM categorias c 
    WHERE c.ativa = 1 
    ORDER BY c.nome
");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar subcategorias
$subcategorias = [];
foreach ($categorias as $categoria) {
    $stmt = $pdo->prepare("
        SELECT s.*, 
               (SELECT COUNT(*) FROM produtos p WHERE p.subcategoria_id = s.id AND p.ativo = 1) as total_produtos
        FROM subcategorias s 
        WHERE s.categoria_id = ? AND s.ativa = 1 
        ORDER BY s.nome
    ");
    $stmt->execute([$categoria['id']]);
    $subcategorias[$categoria['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias - Vesty Brasil</title>
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
                <h1 style="font-size: 1.2rem; font-weight: 600; margin: 0;">Categorias</h1>                <div class="header-actions">
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

    <!-- Main Content -->
    <main class="container">
        <div class="categories-list">
            <?php foreach ($categorias as $categoria): ?>
            <div class="category-section">
                <div class="category-header">
                    <a href="categoria.php?id=<?= $categoria['id'] ?>" class="category-main">
                        <div class="category-info">
                            <i class="material-icons-round category-icon"><?= $categoria['icone'] ?: 'category' ?></i>
                            <div>
                                <h3 class="category-title"><?= htmlspecialchars($categoria['nome']) ?></h3>
                                <p class="category-count"><?= $categoria['total_produtos'] ?> produtos</p>
                            </div>
                        </div>
                        <i class="material-icons-round">arrow_forward_ios</i>
                    </a>
                </div>
                
                <?php if (!empty($subcategorias[$categoria['id']])): ?>
                <div class="subcategories">
                    <?php foreach ($subcategorias[$categoria['id']] as $subcategoria): ?>
                    <a href="categoria.php?subcategoria=<?= $subcategoria['id'] ?>" class="subcategory-item">
                        <span><?= htmlspecialchars($subcategoria['nome']) ?></span>
                        <span class="subcategory-count"><?= $subcategoria['total_produtos'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
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

    <style>
        .categories-list {
            padding: var(--space) 0;
        }

        .category-section {
            margin-bottom: var(--space-lg);
        }

        .category-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--space);
            background: var(--surface);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--on-surface);
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-container);
            transition: var(--transition);
        }

        .category-main:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .category-info {
            display: flex;
            align-items: center;
            gap: var(--space);
        }

        .category-icon {
            font-size: 2rem;
            color: var(--primary);
        }

        .category-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: var(--space-xs);
        }

        .category-count {
            font-size: 0.9rem;
            color: var(--on-surface-variant);
            margin: 0;
        }

        .subcategories {
            margin-top: var(--space);
            padding-left: var(--space-lg);
        }

        .subcategory-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--space-sm) var(--space);
            margin-bottom: var(--space-xs);
            background: var(--surface-variant);
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--on-surface);
            transition: var(--transition);
        }

        .subcategory-item:hover {
            background: var(--surface-container);
            color: var(--primary);
        }

        .subcategory-count {
            font-size: 0.8rem;
            color: var(--on-surface-variant);
            background: var(--surface);
            padding: 2px 8px;
            border-radius: 12px;
        }
    </style>
</body>
</html>
