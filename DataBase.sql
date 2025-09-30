-- =========================
-- Criar banco de dados
-- =========================
CREATE DATABASE IF NOT EXISTS sistema_financeiro
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE sistema_financeiro;

-- =========================
-- Tabela de Usuários
-- =========================
CREATE TABLE IF NOT EXISTS Usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- =========================
-- Tabela de Produtos
-- =========================
CREATE TABLE IF NOT EXISTS Produto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(100),
    preco_custo DECIMAL(10,2) NOT NULL,
    preco_venda DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE
);

-- =========================
-- Tabela de Custos / Despesas
-- =========================
CREATE TABLE IF NOT EXISTS Custo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    produto_id INT DEFAULT NULL, -- nulo indica custo geral para todos os produtos
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES Produto(id) ON DELETE SET NULL
);