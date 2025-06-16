<?php
require_once 'includes/config.php';
iniciarSessao();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favoritos - Vesty Brasil</title>
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
                <h1 style="font-size: 1.2rem; font-weight: 600; margin: 0;">Meus Favoritos</h1>
                <div class="header-actions">
                    <button class="btn-icon" onclick="limparFavoritos()" id="clear-btn" style="display: none;">
                        <i class="material-icons-round">clear_all</i>
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
        <!-- Lista de Favoritos -->
        <div id="favoritos-container" class="favorites-container">
            <!-- Carregado via JavaScript -->
        </div>

        <!-- Estado Vazio -->
        <div id="empty-favorites" class="empty-state" style="display: none;">
            <div class="empty-icon">💝</div>
            <h3>Nenhum favorito ainda</h3>
            <p>Adicione produtos aos seus favoritos tocando no ❤️</p>
            <a href="index.php" class="btn btn-primary">Explorar Produtos</a>
        </div>
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
        <a href="favoritos.php" class="nav-item active">
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
        function carregarFavoritos() {
            const container = document.getElementById('favoritos-container');
            const emptyState = document.getElementById('empty-favorites');
            const clearBtn = document.getElementById('clear-btn');
            
            const favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];
            
            if (favoritos.length === 0) {
                container.style.display = 'none';
                emptyState.style.display = 'block';
                clearBtn.style.display = 'none';
                return;
            }
            
            container.style.display = 'block';
            emptyState.style.display = 'none';
            clearBtn.style.display = 'flex';
            
            container.innerHTML = favoritos.map(item => `
                <div class="favorite-item" data-produto-id="${item.id}">
                    <a href="produto.php?id=${item.id}" class="favorite-link">
                        <div class="favorite-image">
                            ${item.imagem ? 
                                `<img src="assets/images/produtos/${item.imagem}" alt="${item.nome}">` : 
                                '<i class="material-icons-round">image</i>'
                            }
                        </div>
                        <div class="favorite-info">
                            <h3 class="favorite-name">${item.nome}</h3>
                            <div class="favorite-price">${formatarPreco(item.preco)}</div>
                        </div>
                    </a>
                    <div class="favorite-actions">
                        <button class="btn-icon remove-favorite" onclick="removerFavorito('${item.id}')">
                            <i class="material-icons-round">favorite</i>
                        </button>
                        <button class="btn btn-primary btn-small add-to-cart"
                                data-produto-id="${item.id}"
                                data-produto-nome="${item.nome}"
                                data-produto-preco="${item.preco}"
                                data-produto-imagem="${item.imagem}">
                            <i class="material-icons-round">shopping_cart</i> Adicionar
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        function removerFavorito(produtoId) {
            let favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];
            favoritos = favoritos.filter(item => item.id !== produtoId);
            localStorage.setItem('favoritos', JSON.stringify(favoritos));
            
            // Atualizar interface
            carregarFavoritos();
            window.vestyApp.atualizarContadores();
            window.vestyApp.mostrarNotificacao('Removido dos favoritos', 'info');
        }
        
        function limparFavoritos() {
            if (confirm('Deseja remover todos os favoritos?')) {
                localStorage.removeItem('favoritos');
                carregarFavoritos();
                window.vestyApp.atualizarContadores();
                window.vestyApp.mostrarNotificacao('Favoritos limpos', 'info');
            }
        }
        
        function formatarPreco(preco) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(preco);
        }
        
        // Carregar favoritos quando a página carregar
        document.addEventListener('DOMContentLoaded', carregarFavoritos);
    </script>

    <style>
        .favorites-container {
            padding: var(--space) 0;
        }

        .favorite-item {
            display: flex;
            align-items: center;
            gap: var(--space);
            padding: var(--space);
            background: var(--surface);
            border-radius: var(--radius-md);
            margin-bottom: var(--space);
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-container);
            transition: var(--transition);
        }

        .favorite-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .favorite-link {
            display: flex;
            align-items: center;
            gap: var(--space);
            flex: 1;
            text-decoration: none;
            color: var(--on-surface);
        }

        .favorite-image {
            width: 80px;
            height: 80px;
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--surface-container);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .favorite-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .favorite-image i {
            font-size: 2rem;
            color: var(--on-surface-variant);
        }

        .favorite-info {
            flex: 1;
        }

        .favorite-name {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: var(--space-xs);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .favorite-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }

        .favorite-actions {
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
            align-items: center;
        }

        .remove-favorite {
            color: var(--secondary);
            border: 2px solid var(--secondary);
            background: transparent;
        }

        .remove-favorite:hover {
            background: var(--secondary);
            color: var(--on-secondary);
        }

        @media (max-width: 480px) {
            .favorite-item {
                flex-direction: column;
                text-align: center;
            }

            .favorite-link {
                flex-direction: column;
                text-align: center;
                width: 100%;
            }

            .favorite-actions {
                flex-direction: row;
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</body>
</html>
