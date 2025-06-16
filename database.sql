-- Banco de dados Vesty Brasil MVP
CREATE DATABASE IF NOT EXISTS vesty_brasil;
USE vesty_brasil;

-- Tabela de categorias
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    icone VARCHAR(50),
    ativa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de subcategorias
CREATE TABLE subcategorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT,
    nome VARCHAR(100) NOT NULL,
    ativa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Tabela de usuários (clientes)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    endereco TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de vendedores
CREATE TABLE vendedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    empresa VARCHAR(100),
    telefone VARCHAR(20),
    endereco TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de administradores
CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de produtos
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendedor_id INT,
    categoria_id INT,
    subcategoria_id INT,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    imagem VARCHAR(255),
    tamanhos TEXT, -- JSON com tamanhos disponíveis
    estoque INT DEFAULT 0,
    destaque BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (subcategoria_id) REFERENCES subcategorias(id)
);

-- Tabela de favoritos
CREATE TABLE favoritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    produto_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    UNIQUE KEY unique_favorito (usuario_id, produto_id)
);

-- Tabela de carrinho
CREATE TABLE carrinho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    produto_id INT,
    quantidade INT DEFAULT 1,
    tamanho VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Tabela de pedidos
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'confirmado', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente',
    endereco_entrega TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de itens do pedido
CREATE TABLE itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    produto_id INT,
    quantidade INT NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    tamanho VARCHAR(10),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Inserir dados de exemplo
INSERT INTO categorias (nome, icone) VALUES 
('Feminino', 'woman'),
('Masculino', 'man'),
('Infantil', 'child_care'),
('Calçados', 'shopping_bag'),
('Acessórios', 'watch');

INSERT INTO subcategorias (categoria_id, nome) VALUES 
(1, 'Vestidos'),
(1, 'Blusas'),
(1, 'Calças'),
(1, 'Saias'),
(2, 'Camisetas'),
(2, 'Calças'),
(2, 'Bermudas'),
(2, 'Camisas'),
(3, 'Meninas'),
(3, 'Meninos'),
(4, 'Tênis'),
(4, 'Sandálias'),
(4, 'Sapatos'),
(5, 'Bolsas'),
(5, 'Relógios'),
(5, 'Bijuterias');

-- Inserir admin padrão (senha: admin123)
INSERT INTO administradores (nome, email, senha) VALUES 
('Administrador', 'admin@vestybrasil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserir vendedor de exemplo (senha: vendedor123)
INSERT INTO vendedores (nome, email, senha, empresa, telefone) VALUES 
('João Silva', 'joao@confeccoes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Confecções Silva', '(81) 99999-9999');

-- Inserir produtos de exemplo
INSERT INTO produtos (vendedor_id, categoria_id, subcategoria_id, nome, descricao, preco, imagem, tamanhos, estoque, destaque) VALUES 
(1, 1, 1, 'Vestido Floral Lindo', 'Vestido estampado em tecido leve e confortável', 89.90, 'vestido1.jpg', '["P", "M", "G", "GG"]', 15, TRUE),
(1, 1, 2, 'Blusa Básica Algodão', 'Blusa básica em 100% algodão, várias cores', 39.90, 'blusa1.jpg', '["P", "M", "G"]', 25, TRUE),
(1, 2, 5, 'Camiseta Masculina Premium', 'Camiseta em algodão premium, corte moderno', 49.90, 'camiseta1.jpg', '["P", "M", "G", "GG"]', 20, TRUE),
(1, 3, 9, 'Conjunto Infantil Menina', 'Conjunto fofo para meninas, 100% algodão', 59.90, 'infantil1.jpg', '["2", "4", "6", "8"]', 10, FALSE),
(1, 4, 11, 'Tênis Esportivo Unissex', 'Tênis confortável para o dia a dia', 129.90, 'tenis1.jpg', '["35", "36", "37", "38", "39", "40"]', 12, TRUE);
