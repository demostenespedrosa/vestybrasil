<?php
require_once 'includes/config.php';
iniciarSessao();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - Vesty Brasil</title>
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
                <h1 style="font-size: 1.2rem; font-weight: 600; margin: 0;">Meu Carrinho</h1>
                <div class="header-actions">
                    <button class="btn-icon" onclick="limparCarrinho()" id="clear-cart-btn" style="display: none;">
                        <i class="material-icons-round">delete_sweep</i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Itens do Carrinho -->
        <div id="carrinho-items" class="cart-items">
            <!-- Carregado via JavaScript -->
        </div>

        <!-- Estado Vazio -->
        <div id="empty-cart" class="empty-state" style="display: none;">
            <div class="empty-icon">🛒</div>
            <h3>Seu carrinho está vazio</h3>
            <p>Adicione produtos ao carrinho para continuar</p>
            <a href="index.php" class="btn btn-primary">Continuar Comprando</a>
        </div>

        <!-- Resumo do Pedido -->
        <div id="cart-summary" class="cart-summary" style="display: none;">
            <div class="summary-card">
                <h3>Resumo do Pedido</h3>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="cart-subtotal">R$ 0,00</span>
                </div>
                <div class="summary-row">
                    <span>Frete:</span>
                    <span>A calcular</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span id="cart-total">R$ 0,00</span>
                </div>
                <button class="btn btn-primary btn-checkout" onclick="prosseguirCheckout()">
                    <i class="material-icons-round">payment</i>
                    Finalizar Pedido
                </button>
                <p class="checkout-note">Calcularemos o frete na próxima etapa</p>
            </div>
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
        <a href="favoritos.php" class="nav-item">
            <i class="material-icons-round nav-icon">favorite</i>
            <span class="nav-label">Favoritos</span>
        </a>
        <a href="carrinho.php" class="nav-item active">
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
        function carregarCarrinho() {
            const container = document.getElementById('carrinho-items');
            const emptyState = document.getElementById('empty-cart');
            const summary = document.getElementById('cart-summary');
            const clearBtn = document.getElementById('clear-cart-btn');
            
            const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
            
            if (carrinho.length === 0) {
                container.style.display = 'none';
                emptyState.style.display = 'block';
                summary.style.display = 'none';
                clearBtn.style.display = 'none';
                return;
            }
            
            container.style.display = 'block';
            emptyState.style.display = 'none';
            summary.style.display = 'block';
            clearBtn.style.display = 'flex';
            
            // Renderizar itens
            container.innerHTML = carrinho.map(item => `
                <div class="cart-item" data-produto-id="${item.id}" data-tamanho="${item.tamanho}">
                    <div class="cart-item-image">
                        ${item.imagem ? 
                            `<img src="assets/images/produtos/${item.imagem}" alt="${item.nome}">` : 
                            '<i class="material-icons-round">image</i>'
                        }
                    </div>
                    <div class="cart-item-info">
                        <h4 class="cart-item-name">${item.nome}</h4>
                        <p class="cart-item-size">Tamanho: ${item.tamanho}</p>
                        <div class="cart-item-price">${formatarPreco(item.preco)}</div>
                    </div>
                    <div class="cart-item-controls">
                        <div class="quantity-controls">
                            <button class="btn-icon quantity-btn" data-produto-id="${item.id}" data-tamanho="${item.tamanho}" data-acao="diminuir">
                                <i class="material-icons-round">remove</i>
                            </button>
                            <span class="quantity">${item.quantidade}</span>
                            <button class="btn-icon quantity-btn" data-produto-id="${item.id}" data-tamanho="${item.tamanho}" data-acao="aumentar">
                                <i class="material-icons-round">add</i>
                            </button>
                        </div>
                        <button class="btn-icon remove-from-cart" data-produto-id="${item.id}" data-tamanho="${item.tamanho}">
                            <i class="material-icons-round">delete</i>
                        </button>
                    </div>
                </div>
            `).join('');
            
            // Calcular total
            const subtotal = carrinho.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
            document.getElementById('cart-subtotal').textContent = formatarPreco(subtotal);
            document.getElementById('cart-total').textContent = formatarPreco(subtotal);
        }
        
        function limparCarrinho() {
            if (confirm('Deseja remover todos os itens do carrinho?')) {
                localStorage.removeItem('carrinho');
                carregarCarrinho();
                window.vestyApp.atualizarContadores();
                window.vestyApp.mostrarNotificacao('Carrinho limpo', 'info');
            }
        }
        
        function prosseguirCheckout() {
            const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
            
            if (carrinho.length === 0) {
                window.vestyApp.mostrarNotificacao('Carrinho vazio', 'warning');
                return;
            }
            
            // Simular finalização (em produção seria um processo completo)
            const modal = document.createElement('div');
            modal.className = 'checkout-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>🎉 Pedido Simulado!</h3>
                        <button class="btn-icon" onclick="fecharModal()">
                            <i class="material-icons-round">close</i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Este é um MVP demonstrativo.</p>
                        <p>Em produção, aqui seria implementado:</p>
                        <ul>
                            <li>Endereço de entrega</li>
                            <li>Formas de pagamento</li>
                            <li>Cálculo de frete</li>
                            <li>Integração com gateway</li>
                        </ul>
                        <div class="total-final">
                            <strong>Total: ${document.getElementById('cart-total').textContent}</strong>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-primary" onclick="simularPedido()">
                            Simular Pedido
                        </button>
                        <button class="btn btn-secondary" onclick="fecharModal()">
                            Continuar Comprando
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            setTimeout(() => modal.classList.add('show'), 100);
        }
        
        function fecharModal() {
            const modal = document.querySelector('.checkout-modal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => document.body.removeChild(modal), 300);
            }
        }
        
        function simularPedido() {
            localStorage.removeItem('carrinho');
            fecharModal();
            window.vestyApp.atualizarContadores();
            carregarCarrinho();
            window.vestyApp.mostrarNotificacao('Pedido simulado com sucesso! 🎉', 'success');
        }
        
        function formatarPreco(preco) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(preco);
        }
        
        // Override do método do app para atualizar carrinho
        document.addEventListener('DOMContentLoaded', () => {
            carregarCarrinho();
            
            // Interceptar mudanças no carrinho
            const originalSalvar = window.vestyApp.salvarCarrinho;
            window.vestyApp.salvarCarrinho = function() {
                originalSalvar.call(this);
                carregarCarrinho();
            };
        });
    </script>

    <style>
        .cart-items {
            padding: var(--space) 0;
        }

        .cart-item {
            display: flex;
            gap: var(--space);
            padding: var(--space);
            background: var(--surface);
            border-radius: var(--radius-md);
            margin-bottom: var(--space);
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-container);
        }

        .cart-item-image {
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

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-image i {
            font-size: 2rem;
            color: var(--on-surface-variant);
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-name {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: var(--space-xs);
            line-height: 1.4;
        }

        .cart-item-size {
            font-size: 0.8rem;
            color: var(--on-surface-variant);
            margin-bottom: var(--space-xs);
        }

        .cart-item-price {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
        }

        .cart-item-controls {
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
            align-items: center;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            background: var(--surface-variant);
            border-radius: var(--radius);
            padding: var(--space-xs);
        }

        .quantity-controls .btn-icon {
            width: 32px;
            height: 32px;
            font-size: 1rem;
        }

        .quantity {
            font-weight: 600;
            min-width: 24px;
            text-align: center;
            font-size: 1rem;
        }

        .cart-summary {
            margin-top: var(--space-lg);
            position: sticky;
            bottom: 90px;
            z-index: 50;
        }

        .summary-card {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: var(--space);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--surface-container);
        }

        .summary-card h3 {
            margin-bottom: var(--space);
            font-size: 1.2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--space-sm);
            font-size: 0.9rem;
        }

        .summary-total {
            border-top: 1px solid var(--surface-container);
            padding-top: var(--space-sm);
            margin-top: var(--space-sm);
            font-weight: 700;
            font-size: 1rem;
        }

        .btn-checkout {
            width: 100%;
            margin-top: var(--space);
            font-size: 1.1rem;
            font-weight: 700;
        }

        .checkout-note {
            text-align: center;
            font-size: 0.8rem;
            color: var(--on-surface-variant);
            margin-top: var(--space-sm);
            margin-bottom: 0;
        }

        /* Modal Checkout */
        .checkout-modal {
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

        .checkout-modal.show {
            opacity: 1;
        }

        .modal-content {
            background: var(--surface);
            border-radius: var(--radius-md);
            max-width: 400px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .checkout-modal.show .modal-content {
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
            font-size: 1.3rem;
        }

        .modal-body {
            padding: var(--space);
        }

        .modal-body ul {
            margin: var(--space) 0;
            padding-left: var(--space-lg);
        }

        .modal-body li {
            margin-bottom: var(--space-xs);
        }

        .total-final {
            background: var(--surface-variant);
            padding: var(--space);
            border-radius: var(--radius);
            text-align: center;
            margin-top: var(--space);
            font-size: 1.2rem;
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
    </style>
</body>
</html>
