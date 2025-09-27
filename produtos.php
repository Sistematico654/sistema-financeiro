<?php
session_start();
if (!isset($_SESSION['usuario_id'])) header("Location: login.php");
require_once "conexao.php";

// =======================
// Inserir produto
// =======================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adicionar'])) {
    $nome = $_POST['nome'];
    $preco_custo = $_POST['preco_custo'];
    $preco_venda = $_POST['preco_venda'];
    $quantidade = $_POST['quantidade'];
    $categoria = $_POST['categoria'];

    $stmt = $conn->prepare("INSERT INTO Produto (nome, preco_custo, preco_venda, quantidade, categoria) VALUES (:nome, :preco_custo, :preco_venda, :quantidade, :categoria)");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':preco_custo', $preco_custo);
    $stmt->bindParam(':preco_venda', $preco_venda);
    $stmt->bindParam(':quantidade', $quantidade);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->execute();
}

// =======================
// Editar produto
// =======================
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmt = $conn->prepare("SELECT * FROM Produto WHERE id = :id");
    $stmt->bindParam(':id', $id_editar);
    $stmt->execute();
    $produto_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// =======================
// Atualizar produto
// =======================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['atualizar'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $preco_custo = $_POST['preco_custo'];
    $preco_venda = $_POST['preco_venda'];
    $quantidade = $_POST['quantidade'];
    $categoria = $_POST['categoria'];

    $stmt = $conn->prepare("UPDATE Produto SET nome = :nome, preco_custo = :preco_custo, preco_venda = :preco_venda, quantidade = :quantidade, categoria = :categoria WHERE id = :id");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':preco_custo', $preco_custo);
    $stmt->bindParam(':preco_venda', $preco_venda);
    $stmt->bindParam(':quantidade', $quantidade);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: produtos.php");
    exit;
}

// =======================
// Deletar produto
// =======================
if (isset($_GET['deletar'])) {
    $id_deletar = $_GET['deletar'];
    $stmt = $conn->prepare("DELETE FROM Produto WHERE id = :id");
    $stmt->bindParam(':id', $id_deletar);
    $stmt->execute();
    header("Location: produtos.php");
    exit;
}

// =======================
// Listar produtos
// =======================
$produtos = $conn->query("SELECT * FROM Produto")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Produtos - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Produtos</h2>

    <!-- Formulário adicionar / editar -->
    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="id" value="<?= $produto_editar['id'] ?? '' ?>">
        <div class="col-md-2">
            <input type="text" name="nome" placeholder="Nome do produto" class="form-control" required value="<?= $produto_editar['nome'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="preco_custo" placeholder="Preço Custo" class="form-control" required value="<?= $produto_editar['preco_custo'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="preco_venda" placeholder="Preço Venda" class="form-control" required value="<?= $produto_editar['preco_venda'] ?? '' ?>">
        </div>
        <div class="col-md-1">
            <input type="number" name="quantidade" placeholder="Qtd" class="form-control" required value="<?= $produto_editar['quantidade'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="categoria" placeholder="Categoria" class="form-control" value="<?= $produto_editar['categoria'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" name="<?= isset($produto_editar) ? 'atualizar' : 'adicionar' ?>" class="btn btn-success w-100">
                <?= isset($produto_editar) ? 'Atualizar' : 'Adicionar' ?>
            </button>
        </div>
    </form>

    <!-- Tabela de produtos -->
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Preço Custo</th>
                <th>Preço Venda</th>
                <th>Quantidade</th>
                <th>Categoria</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($produtos as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= $p['nome'] ?></td>
                <td>R$ <?= number_format($p['preco_custo'],2,",",".") ?></td>
                <td>R$ <?= number_format($p['preco_venda'],2,",",".") ?></td>
                <td><?= $p['quantidade'] ?></td>
                <td><?= $p['categoria'] ?></td>
                <td>
                    <a href="produtos.php?editar=<?= $p['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="produtos.php?deletar=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente deletar?')">Deletar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-primary mt-3">Voltar ao Painel</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
