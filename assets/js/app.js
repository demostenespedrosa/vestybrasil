// Vesty Brasil - JavaScript Principal
class VestyApp {
    constructor() {
        this.carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
        this.favoritos = JSON.parse(localStorage.getItem('favoritos')) || [];
        this.tema = localStorage.getItem('tema') || 'light'; // Padrão claro
        this.init();
    }

    init() {
        this.aplicarTema();
        this.atualizarContadores();
        this.bindEvents();
        this.carregarProdutos();
    }

    bindEvents() {
        // Eventos de navegação
        document.addEventListener('click', (e) => {
            if (e.target.matches('.add-to-cart')) {
                e.preventDefault();
                this.adicionarAoCarrinho(e.target);
            }
            
            if (e.target.matches('.add-to-favorites')) {
                e.preventDefault();
                this.adicionarAosFavoritos(e.target);
            }
            
            if (e.target.matches('.remove-from-cart')) {
                e.preventDefault();
                this.removerDoCarrinho(e.target);
            }
            
            if (e.target.matches('.quantity-btn')) {
                e.preventDefault();
                this.alterarQuantidade(e.target);
            }

            // Toggle de tema
            if (e.target.matches('.theme-toggle') || e.target.closest('.theme-toggle')) {
                e.preventDefault();
                this.toggleTema();
            }
        });

        // Busca
        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.buscarProdutos(e.target.value);
            });
        }

        // Filtros de categoria
        document.addEventListener('change', (e) => {
            if (e.target.matches('.category-filter')) {
                this.filtrarPorCategoria(e.target.value);
            }
        });
    }

    // Carrinho
    adicionarAoCarrinho(btn) {
        const produtoId = btn.dataset.produtoId;
        const produtoNome = btn.dataset.produtoNome;
        const produtoPreco = parseFloat(btn.dataset.produtoPreco);
        const produtoImagem = btn.dataset.produtoImagem;
        const tamanho = btn.dataset.tamanho || 'M';

        const itemExistente = this.carrinho.find(item => 
            item.id === produtoId && item.tamanho === tamanho
        );

        if (itemExistente) {
            itemExistente.quantidade++;
        } else {
            this.carrinho.push({
                id: produtoId,
                nome: produtoNome,
                preco: produtoPreco,
                imagem: produtoImagem,
                tamanho: tamanho,
                quantidade: 1
            });
        }

        this.salvarCarrinho();
        this.atualizarContadores();
        this.mostrarNotificacao('Produto adicionado ao carrinho!', 'success');
        
        // Animação do botão
        btn.innerHTML = '<i class="material-icons-round">check</i> Adicionado';
        btn.classList.add('btn-success');
        setTimeout(() => {
            btn.innerHTML = '<i class="material-icons-round">shopping_cart</i> Adicionar';
            btn.classList.remove('btn-success');
        }, 2000);
    }

    removerDoCarrinho(btn) {
        const produtoId = btn.dataset.produtoId;
        const tamanho = btn.dataset.tamanho;
        
        this.carrinho = this.carrinho.filter(item => 
            !(item.id === produtoId && item.tamanho === tamanho)
        );
        
        this.salvarCarrinho();
        this.atualizarContadores();
        this.atualizarCarrinho();
        this.mostrarNotificacao('Produto removido do carrinho', 'info');
    }

    alterarQuantidade(btn) {
        const produtoId = btn.dataset.produtoId;
        const tamanho = btn.dataset.tamanho;
        const acao = btn.dataset.acao;
        
        const item = this.carrinho.find(item => 
            item.id === produtoId && item.tamanho === tamanho
        );
        
        if (item) {
            if (acao === 'aumentar') {
                item.quantidade++;
            } else if (acao === 'diminuir' && item.quantidade > 1) {
                item.quantidade--;
            }
            
            this.salvarCarrinho();
            this.atualizarContadores();
            this.atualizarCarrinho();
        }
    }

    // Favoritos
    adicionarAosFavoritos(btn) {
        const produtoId = btn.dataset.produtoId;
        const produtoNome = btn.dataset.produtoNome;
        const produtoPreco = parseFloat(btn.dataset.produtoPreco);
        const produtoImagem = btn.dataset.produtoImagem;

        const jaFavorito = this.favoritos.find(item => item.id === produtoId);
        
        if (jaFavorito) {
            this.favoritos = this.favoritos.filter(item => item.id !== produtoId);
            btn.innerHTML = '<i class="material-icons-round">favorite_border</i>';
            this.mostrarNotificacao('Removido dos favoritos', 'info');
        } else {
            this.favoritos.push({
                id: produtoId,
                nome: produtoNome,
                preco: produtoPreco,
                imagem: produtoImagem
            });
            btn.innerHTML = '<i class="material-icons-round">favorite</i>';
            this.mostrarNotificacao('Adicionado aos favoritos!', 'success');
        }
        
        this.salvarFavoritos();
        this.atualizarContadores();
    }

    // Busca e filtros
    buscarProdutos(termo) {
        if (termo.length < 2) {
            this.carregarProdutos();
            return;
        }

        fetch('api/buscar_produtos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ termo: termo })
        })
        .then(response => response.json())
        .then(data => {
            this.renderizarProdutos(data.produtos);
        })
        .catch(error => {
            console.error('Erro na busca:', error);
            this.mostrarNotificacao('Erro ao buscar produtos', 'error');
        });
    }

    filtrarPorCategoria(categoriaId) {
        if (!categoriaId) {
            this.carregarProdutos();
            return;
        }

        fetch(`api/produtos_categoria.php?categoria=${categoriaId}`)
        .then(response => response.json())
        .then(data => {
            this.renderizarProdutos(data.produtos);
        })
        .catch(error => {
            console.error('Erro ao filtrar:', error);
        });
    }

    // Carregamento de dados
    carregarProdutos() {
        const container = document.getElementById('produtos-container');
        if (!container) return;

        container.innerHTML = '<div class="loading"><div class="spinner"></div>Carregando produtos...</div>';

        fetch('api/produtos.php')
        .then(response => response.json())
        .then(data => {
            this.renderizarProdutos(data.produtos);
        })
        .catch(error => {
            console.error('Erro ao carregar produtos:', error);
            container.innerHTML = '<div class="empty-state"><div class="empty-icon">😔</div><p>Erro ao carregar produtos</p></div>';
        });
    }

    renderizarProdutos(produtos) {
        const container = document.getElementById('produtos-container');
        if (!container) return;

        if (produtos.length === 0) {
            container.innerHTML = '<div class="empty-state"><div class="empty-icon">🔍</div><p>Nenhum produto encontrado</p></div>';
            return;
        }

        container.innerHTML = produtos.map(produto => `
            <div class="product-card fade-in">
                <a href="produto.php?id=${produto.id}" class="product-link">
                    <div class="product-image">
                        ${produto.imagem ? 
                            `<img src="assets/images/produtos/${produto.imagem}" alt="${produto.nome}">` : 
                            '<i class="material-icons-round">image</i>'
                        }
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">${produto.nome}</h3>
                        <div class="product-price">${this.formatarPreco(produto.preco)}</div>
                    </div>
                </a>
                <div class="product-actions">
                    <button class="btn-icon add-to-favorites" 
                            data-produto-id="${produto.id}"
                            data-produto-nome="${produto.nome}"
                            data-produto-preco="${produto.preco}"
                            data-produto-imagem="${produto.imagem}">
                        <i class="material-icons-round">${this.favoritos.find(f => f.id === produto.id.toString()) ? 'favorite' : 'favorite_border'}</i>
                    </button>
                    <button class="btn btn-primary btn-small add-to-cart"
                            data-produto-id="${produto.id}"
                            data-produto-nome="${produto.nome}"
                            data-produto-preco="${produto.preco}"
                            data-produto-imagem="${produto.imagem}">
                        <i class="material-icons-round">shopping_cart</i> Adicionar
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Atualização da interface
    atualizarContadores() {
        const carrinhoCount = document.getElementById('carrinho-count');
        const favoritosCount = document.getElementById('favoritos-count');
        
        if (carrinhoCount) {
            const totalItens = this.carrinho.reduce((total, item) => total + item.quantidade, 0);
            carrinhoCount.textContent = totalItens;
            carrinhoCount.style.display = totalItens > 0 ? 'flex' : 'none';
        }
        
        if (favoritosCount) {
            favoritosCount.textContent = this.favoritos.length;
            favoritosCount.style.display = this.favoritos.length > 0 ? 'flex' : 'none';
        }
    }

    atualizarCarrinho() {
        const container = document.getElementById('carrinho-items');
        const total = document.getElementById('carrinho-total');
        
        if (!container) return;

        if (this.carrinho.length === 0) {
            container.innerHTML = '<div class="empty-state"><div class="empty-icon">🛒</div><p>Seu carrinho está vazio</p></div>';
            if (total) total.textContent = 'R$ 0,00';
            return;
        }

        container.innerHTML = this.carrinho.map(item => `
            <div class="cart-item">
                <div class="cart-item-image">
                    ${item.imagem ? 
                        `<img src="assets/images/produtos/${item.imagem}" alt="${item.nome}">` : 
                        '<i class="material-icons-round">image</i>'
                    }
                </div>
                <div class="cart-item-info">
                    <h4>${item.nome}</h4>
                    <p>Tamanho: ${item.tamanho}</p>
                    <div class="cart-item-price">${this.formatarPreco(item.preco)}</div>
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

        if (total) {
            const valorTotal = this.carrinho.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
            total.textContent = this.formatarPreco(valorTotal);
        }
    }    // Controle de Tema
    aplicarTema() {
        // Adicionar classe de transição para evitar flicker na inicialização
        document.documentElement.style.transition = 'none';
        document.documentElement.setAttribute('data-theme', this.tema);
        this.atualizarIconeTema();
        
        // Restaurar transições após aplicar o tema
        setTimeout(() => {
            document.documentElement.style.transition = '';
        }, 100);
    }

    toggleTema() {
        this.tema = this.tema === 'light' ? 'dark' : 'light';
        localStorage.setItem('tema', this.tema);
        
        // Adicionar animação de feedback
        const toggles = document.querySelectorAll('.theme-toggle');
        toggles.forEach(toggle => {
            toggle.style.transform = 'scale(0.95)';
            setTimeout(() => {
                toggle.style.transform = '';
            }, 150);
        });
        
        this.aplicarTema();
        this.mostrarNotificacao(
            `Modo ${this.tema === 'dark' ? 'escuro' : 'claro'} ativado!`, 
            'info'
        );
    }

    atualizarIconeTema() {
        const icones = document.querySelectorAll('.theme-toggle-icon');
        icones.forEach(icone => {
            // Animação de fade para mudança de ícone
            icone.style.opacity = '0';
            setTimeout(() => {
                icone.textContent = this.tema === 'dark' ? '🌙' : '☀️';
                icone.style.opacity = '1';
            }, 200);
        });
    }

    // Utilitários
    salvarCarrinho() {
        localStorage.setItem('carrinho', JSON.stringify(this.carrinho));
    }

    salvarFavoritos() {
        localStorage.setItem('favoritos', JSON.stringify(this.favoritos));
    }

    formatarPreco(preco) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(preco);
    }

    mostrarNotificacao(mensagem, tipo = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${tipo}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="material-icons-round">${this.getNotificationIcon(tipo)}</i>
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

    getNotificationIcon(tipo) {
        const icons = {
            success: 'check_circle',
            error: 'error',
            warning: 'warning',
            info: 'info'
        };
        return icons[tipo] || 'info';
    }
}

// CSS para notificações
const notificationStyle = document.createElement('style');
notificationStyle.textContent = `
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: var(--surface);
    border: 1px solid var(--surface-container);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    padding: var(--space);
    z-index: 1000;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    max-width: 300px;
}

.notification.show {
    transform: translateX(0);
}

.notification-content {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.notification-success {
    border-left: 4px solid var(--success);
}

.notification-error {
    border-left: 4px solid var(--error);
}

.notification-warning {
    border-left: 4px solid var(--warning);
}

.notification-info {
    border-left: 4px solid var(--primary);
}

.cart-item {
    display: flex;
    gap: var(--space);
    padding: var(--space);
    border-bottom: 1px solid var(--surface-container);
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 60px;
    height: 60px;
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--surface-container);
    display: flex;
    align-items: center;
    justify-content: center;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-item-info {
    flex: 1;
}

.cart-item-info h4 {
    margin-bottom: var(--space-xs);
    font-size: 0.9rem;
}

.cart-item-info p {
    font-size: 0.8rem;
    color: var(--on-surface-variant);
    margin-bottom: var(--space-xs);
}

.cart-item-price {
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
}

.quantity {
    font-weight: 600;
    min-width: 20px;
    text-align: center;
}

.product-actions {
    padding: var(--space-sm) var(--space);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid var(--surface-container);
}

.btn-success {
    background: var(--success) !important;
    color: white !important;
}
`;
document.head.appendChild(notificationStyle);

// Inicializar aplicação
document.addEventListener('DOMContentLoaded', () => {
    window.vestyApp = new VestyApp();
});
