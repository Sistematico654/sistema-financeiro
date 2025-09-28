-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS sistema_financeiro;
USE sistema_financeiro;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS Usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- Tabela de produtos
CREATE TABLE IF NOT EXISTS Produto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    preco_custo DECIMAL(10,2) NOT NULL,
    preco_venda DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL,
    categoria VARCHAR(100)
);

-- Tabela de custos/despesas
CREATE TABLE IF NOT EXISTS Custo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('Fixa','Variavel') NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL
);

-- Inserção de usuário de teste (senha: 123456)
INSERT INTO Usuario (nome, email, senha) VALUES
('Admin', 'teste@sistema.com', '$2y$10$fo5yaxkenj7w0RsqhLMMqe3xGK78vX9imT6uZsvETSW0X6Rc4Qmru');

-- Inserção de produtos de exemplo
INSERT INTO Produto (nome, preco_custo, preco_venda, quantidade, categoria) VALUES
('Produto A', 10.00, 20.00, 50, 'Categoria 1'),
('Produto B', 15.00, 25.00, 30, 'Categoria 2');

-- Inserção de custos de exemplo
INSERT INTO Custo (tipo, descricao, valor) VALUES
('Fixa', 'Aluguel', 1000.00),
('Variavel', 'Energia', 300.00);
