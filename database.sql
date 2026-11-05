-- ============================================
-- SKIN CARE GALLERY
-- Estrutura do banco de dados
-- ============================================

CREATE DATABASE IF NOT EXISTS skin_care_gallery
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE skin_care_gallery;


-- ============================================
-- 1. TABELA: CLIENTES
-- ============================================

CREATE TABLE IF NOT EXISTS clientes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY email (email)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- 2. TABELA: CATEGORIAS
-- ============================================

CREATE TABLE IF NOT EXISTS categorias (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY nome (nome)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- 3. TABELA: MARCAS
-- ============================================

CREATE TABLE IF NOT EXISTS marcas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY nome (nome)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- 4. TABELA: PRODUTOS
-- ============================================

CREATE TABLE IF NOT EXISTS produtos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    imagem VARCHAR(255) NULL,
    preco DECIMAL(10,2) NULL,
    marca_id INT(11) NOT NULL,
    categoria_id INT(11) NOT NULL,
    destaque TINYINT(1) NULL DEFAULT 0,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY marca_id (marca_id),
    KEY categoria_id (categoria_id)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- 5. TABELA: ADMINISTRADORES
-- ============================================

CREATE TABLE IF NOT EXISTS administradores (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usuario VARCHAR(50) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY usuario (usuario)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- 6. TABELA: PEDIDOS
-- ============================================

CREATE TABLE IF NOT EXISTS pedidos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cliente_id INT(11) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NULL DEFAULT 'Pendente',
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY cliente_id (cliente_id)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- 7. TABELA: ITENS_PEDIDO
-- ============================================

CREATE TABLE IF NOT EXISTS itens_pedido (
    id INT(11) NOT NULL AUTO_INCREMENT,
    pedido_id INT(11) NOT NULL,
    produto_id INT(11) NOT NULL,
    quantidade INT(11) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,

    PRIMARY KEY (id),
    KEY pedido_id (pedido_id),
    KEY produto_id (produto_id)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- DADOS INICIAIS: CATEGORIAS
-- ============================================

INSERT IGNORE INTO categorias (id, nome) VALUES
(1, 'Sabonetes faciais'),
(2, 'Hidratantes e loções'),
(3, 'Ácidos'),
(4, 'Protetores solares');


-- ============================================
-- DADOS INICIAIS: MARCAS
-- ============================================

INSERT IGNORE INTO marcas (id, nome) VALUES
(1, 'Principia'),
(2, 'CeraVe'),
(3, 'Creamy');


-- ============================================
-- FIM DO ARQUIVO
-- ============================================