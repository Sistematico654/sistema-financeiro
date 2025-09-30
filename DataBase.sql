-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS sistema_financeiro
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

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
    usuario_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    preco_custo DECIMAL(10,2) NOT NULL,
    preco_venda DECIMAL(10,2) NOT NULL,
    qtd INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE
);

-- Tabela de custos/despesas
CREATE TABLE IF NOT EXISTS Custo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    produto_id INT NULL, -- NULL indica custo geral
    tipo ENUM('Fixa','Variavel') NOT NULL DEFAULT 'Fixa',
    descricao VARCHAR(100) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES Produto(id) ON DELETE CASCADE
);

-- Tabela de ponto de equilíbrio
CREATE TABLE IF NOT EXISTS PontoEquilibrio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    produto_id INT NOT NULL,
    receitasTotais DECIMAL(10,2) NOT NULL,
    custosTotais DECIMAL(10,2) NOT NULL,
    ponto DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES Produto(id) ON DELETE CASCADE
);

-- Tabela de relatórios
CREATE TABLE IF NOT EXISTS Relatorio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    dataCriacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    conteudo TEXT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE
);

-- Tabela de histórico de alterações
CREATE TABLE IF NOT EXISTS HistoricoAlteracao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    dataAlteracao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    alteracao TEXT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id) ON DELETE CASCADE
);
