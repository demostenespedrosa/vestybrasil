# Vesty Brasil - MVP

Marketplace de moda multi-vendedores focado nos fabricantes do polo de confecções de Pernambuco.

## 🚀 Características

- **Design Mobile-First**: Interface otimizada para smartphones com aparência de app nativo
- **Material Design Expressive 3**: Design moderno seguindo as diretrizes do Android 16
- **Navegação por Bottom Nav**: Experiência similar a aplicativos nativos
- **Responsivo**: Funciona perfeitamente em todos os dispositivos
- **PWA Ready**: Configurado para funcionar como Progressive Web App

## 📱 Áreas do Sistema

### Cliente
- **Home**: Banner, categorias e produtos em destaque
- **Categorias**: Lista organizada por categorias e subcategorias
- **Produto**: Página detalhada com galeria, descrição e opções
- **Favoritos**: Lista de produtos salvos
- **Carrinho**: Gestão de itens e checkout simulado
- **Perfil**: Dados do usuário e configurações

### Vendedor
- **Login**: Acesso restrito aos vendedores
- **Painel**: Dashboard com estatísticas e gestão
- **Produtos**: Cadastro e gerenciamento de produtos
- **Pedidos**: Visualização de vendas

### Administrador
- **Login**: Acesso administrativo
- **Painel**: Visão geral do sistema
- **Categorias**: Gestão de categorias e subcategorias
- **Pedidos**: Controle global de pedidos

## 🛠️ Tecnologias

- **Frontend**: HTML5, CSS3 (Material Design), JavaScript (ES6+)
- **Backend**: PHP 8+ com PDO
- **Banco**: MySQL 8+
- **Servidor**: Apache (XAMPP)
- **PWA**: Service Worker, Manifest

## 📦 Instalação

### Pré-requisitos
- XAMPP (Apache + MySQL + PHP)
- Navegador moderno

### Passos

1. **Clone/Baixe os arquivos** para `C:\xampp\htdocs\vestybrasil\`

2. **Inicie o XAMPP**
   - Inicie Apache
   - Inicie MySQL

3. **Crie o banco de dados**
   - Acesse http://localhost/phpmyadmin
   - Crie um banco chamado `vesty_brasil`
   - Importe o arquivo `database.sql`

4. **Configure o sistema**
   - Edite `includes/config.php` se necessário
   - Ajuste as configurações do banco

5. **Acesse o sistema**
   - http://localhost/vestybrasil/

## 👤 Usuários de Demonstração

### Vendedor
- **Email**: joao@confeccoes.com
- **Senha**: vendedor123

### Administrador
- **Email**: admin@vestybrasil.com
- **Senha**: admin123

## 🎯 Funcionalidades Implementadas

### ✅ Prontas para Uso
- [x] Design responsivo e moderno
- [x] Navegação fluida entre páginas
- [x] Carrinho de compras (localStorage)
- [x] Lista de favoritos (localStorage)
- [x] Busca de produtos
- [x] Filtros por categoria
- [x] Painel do vendedor
- [x] Painel administrativo
- [x] Sistema de login básico

### 🚧 Para Implementar (Próximas Versões)
- [ ] Sistema completo de autenticação
- [ ] Upload de imagens
- [ ] Gateway de pagamento
- [ ] Sistema de frete
- [ ] Chat entre cliente e vendedor
- [ ] Notificações push
- [ ] Sistema de avaliações
- [ ] Relatórios detalhados

## 📱 Como Testar

1. **Acesse pelo celular** para melhor experiência
2. **Navegue pelas categorias** e produtos
3. **Adicione itens** ao carrinho e favoritos
4. **Teste o checkout** simulado
5. **Acesse as áreas** de vendedor e admin

## 🎨 Customização

### Cores
Edite as variáveis CSS em `assets/css/style.css`:
```css
:root {
  --primary: #6366F1;
  --secondary: #EC4899;
  /* ... outras cores ... */
}
```

### Layout
- Ajuste o `max-width` do container para diferentes resoluções
- Modifique os breakpoints responsivos
- Customize os componentes

## 📂 Estrutura de Arquivos

```
vestybrasil/
├── assets/
│   ├── css/style.css       # Estilos principais
│   ├── js/app.js          # JavaScript principal
│   └── images/            # Imagens do sistema
├── includes/
│   └── config.php         # Configurações
├── api/                   # APIs REST simples
├── admin/                 # Área administrativa
├── vendedor/             # Área do vendedor
├── index.php             # Página inicial
├── categorias.php        # Lista de categorias
├── categoria.php         # Produtos por categoria
├── produto.php           # Página do produto
├── favoritos.php         # Lista de favoritos
├── carrinho.php          # Carrinho de compras
├── perfil.php            # Perfil do usuário
├── database.sql          # Script do banco
└── manifest.json         # Configuração PWA
```

## 🔧 Desenvolvimento

### Adicionando Novas Páginas
1. Crie o arquivo PHP na raiz
2. Inclua o header padrão
3. Adicione à navegação bottom-nav
4. Mantenha o padrão visual

### Modificando o Banco
1. Edite `database.sql`
2. Atualize `includes/config.php` se necessário
3. Reimporte o banco

### Adicionando Funcionalidades JavaScript
1. Edite `assets/js/app.js`
2. Mantenha o padrão da classe `VestyApp`
3. Use as funções utilitárias existentes

## 🎯 Próximos Passos

1. **Implementar autenticação** completa
2. **Sistema de upload** de imagens
3. **Integração com pagamento** (PagSeguro, Mercado Pago)
4. **Cálculo de frete** (Correios API)
5. **Sistema de notificações**
6. **Dashboard com métricas** reais
7. **Sistema de reviews** e avaliações
8. **Chat em tempo real**

## 📞 Suporte

Este é um MVP demonstrativo. Para implementação completa em produção, considere:

- Validação de segurança
- Otimização de performance
- Testes automatizados
- Deploy em servidor profissional
- Monitoramento e logs
- Backup automatizado

## 🌓 Sistema de Temas

O Vesty Brasil inclui um sistema completo de alternância entre modo claro e escuro:

### Características dos Temas:
- **Modo Padrão**: Claro (Light Mode)
- **Alternância**: Toggle disponível em todas as páginas
- **Persistência**: Tema salvo automaticamente no localStorage
- **Transições**: Animações suaves entre mudanças de tema
- **Material Design**: Cores otimizadas para ambos os modos
- **Acessibilidade**: Contraste adequado em ambos os temas

### Como Usar:
1. **Toggle de Tema**: Clique no ícone ☀️/🌙 no header
2. **Persistência**: O tema escolhido é mantido entre sessões
3. **Disponibilidade**: Funciona em todas as áreas (Cliente, Vendedor, Admin)

### Demonstração:
Acesse `demo-temas.html` para ver todos os componentes em ambos os temas.

---

**Vesty Brasil** - Conectando a moda pernambucana ao Brasil! 🇧🇷👗
