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

-- Tabela de produtos (ligada ao usuário)
CREATE TABLE IF NOT EXISTS Produto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    preco_custo DECIMAL(10,2) NOT NULL,
    preco_venda DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL,
    categoria VARCHAR(100),
    usuario_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE
);

-- Tabela de custos/despesas (ligada ao usuário)
CREATE TABLE IF NOT EXISTS Custo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('Fixa','Variavel') NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    usuario_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE
);