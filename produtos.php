<?php
require_once "conexao.php";
protegerPagina();

// Inserir ou atualizar produto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $nome = $_POST['nome'];
    $categoria = $_POST['categoria'];
    $preco_custo = $_POST['preco_custo'];
    $preco_venda = $_POST['preco_venda'];
    $quantidade = $_POST['quantidade'];

    if ($id) {
        // Atualizar
        $stmt = $conn->prepare("UPDATE Produto SET nome=:nome, categoria=:categoria, preco_custo=:preco_custo, preco_venda=:preco_venda, quantidade=:quantidade WHERE id=:id");
        $stmt->bindParam(':id', $id);
    } else {
        // Inserir
        $stmt = $conn->prepare("INSERT INTO Produto (nome, categoria, preco_custo, preco_venda, quantidade) VALUES (:nome, :categoria, :preco_custo, :preco_venda, :quantidade)");
    }
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':preco_custo', $preco_custo);
    $stmt->bindParam(':preco_venda', $preco_venda);
    $stmt->bindParam(':quantidade', $quantidade);
    $stmt->execute();
    header("Location: produtos.php");
}

// Deletar produto
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->prepare("DELETE FROM Produto WHERE id = ?")->execute([$id]);
    header("Location: produtos.php");
}

// Buscar produto para edição
$editarProduto = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM Produto WHERE id = ?");
    $stmt->execute([$id]);
    $editarProduto = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Listar produtos
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
    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="id" value="<?= $editarProduto['id'] ?? '' ?>">
        <div class="col-md-3">
            <input type="text" name="nome" placeholder="Nome" class="form-control" value="<?= $editarProduto['nome'] ?? '' ?>" required>
        </div>
        <div class="col-md-2">
            <input type="text" name="categoria" placeholder="Categoria" class="form-control" value="<?= $editarProduto['categoria'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="preco_custo" placeholder="Preço Custo" class="form-control" value="<?= $editarProduto['preco_custo'] ?? '' ?>" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="preco_venda" placeholder="Preço Venda" class="form-control" value="<?= $editarProduto['preco_venda'] ?? '' ?>" required>
        </div>
        <div class="col-md-1">
            <input type="number" name="quantidade" placeholder="Qtd" class="form-control" value="<?= $editarProduto['quantidade'] ?? '' ?>" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100"><?= $editarProduto ? 'Atualizar' : 'Adicionar' ?></button>
        </div>
    </form>

    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th><th>Nome</th><th>Categoria</th><th>Preço Custo</th><th>Preço Venda</th><th>Qtd</th><th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($produtos as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= $p['nome'] ?></td>
                <td><?= $p['categoria'] ?></td>
                <td>R$ <?= number_format($p['preco_custo'],2,",",".") ?></td>
                <td>R$ <?= number_format($p['preco_venda'],2,",",".") ?></td>
                <td><?= $p['quantidade'] ?></td>
                <td>
                    <a href="?edit=<?= $p['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente deletar?')">Deletar</a>
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
