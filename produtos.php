<?php
require_once "conexao.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// Buscar produtos do usuário
$stmt = $conn->prepare("SELECT * FROM Produto WHERE usuario_id = ? ORDER BY nome ASC");
$stmt->execute([$usuario_id]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inserir ou atualizar produto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : null;
    $nome = trim($_POST['nome'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $preco_custo = floatval($_POST['preco_custo'] ?? 0);
    $preco_venda = floatval($_POST['preco_venda'] ?? 0);
    $qtd = intval($_POST['qtd'] ?? 0);

    if ($id) {
        $stmt = $conn->prepare("
            UPDATE Produto 
            SET nome=:nome, categoria=:categoria, preco_custo=:preco_custo, preco_venda=:preco_venda, qtd=:qtd
            WHERE id=:id AND usuario_id=:usuario_id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO Produto (nome, categoria, preco_custo, preco_venda, qtd, usuario_id)
            VALUES (:nome, :categoria, :preco_custo, :preco_venda, :qtd, :usuario_id)
        ");
    }

    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':preco_custo', $preco_custo);
    $stmt->bindParam(':preco_venda', $preco_venda);
    $stmt->bindParam(':qtd', $qtd);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();

    header("Location: produtos.php");
    exit;
}

// Deletar produto
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM Produto WHERE id=:id AND usuario_id=:usuario_id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: produtos.php");
    exit;
}

// Buscar produto para edição
$editarProduto = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM Produto WHERE id=:id AND usuario_id=:usuario_id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $editarProduto = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Preparar dados para gráfico
$labels = [];
$precoCustoData = [];
$precoVendaData = [];

foreach ($produtos as $p) {
    $labels[] = $p['nome'];
    $precoCustoData[] = floatval($p['preco_custo']) * intval($p['qtd']);
    $precoVendaData[] = floatval($p['preco_venda']) * intval($p['qtd']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Produtos - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Produtos</h2>

    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="id" value="<?= $editarProduto['id'] ?? '' ?>">

        <div class="col-md-2">
            <input type="text" name="nome" placeholder="Nome" class="form-control" value="<?= htmlspecialchars($editarProduto['nome'] ?? '') ?>" required>
        </div>

        <div class="col-md-2">
            <input type="text" name="categoria" placeholder="Categoria" class="form-control" value="<?= htmlspecialchars($editarProduto['categoria'] ?? '') ?>" required>
        </div>

        <div class="col-md-2">
            <input type="number" step="0.01" name="preco_custo" placeholder="Preço Custo" class="form-control" value="<?= htmlspecialchars($editarProduto['preco_custo'] ?? '') ?>" required>
        </div>

        <div class="col-md-2">
            <input type="number" step="0.01" name="preco_venda" placeholder="Preço Venda" class="form-control" value="<?= htmlspecialchars($editarProduto['preco_venda'] ?? '') ?>" required>
        </div>

        <div class="col-md-2">
            <input type="number" name="qtd" placeholder="Quantidade" class="form-control" value="<?= htmlspecialchars($editarProduto['qtd'] ?? '') ?>" required>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100"><?= $editarProduto ? 'Atualizar' : 'Adicionar' ?></button>
        </div>
    </form>

    <!-- Gráfico de comparação Custo x Venda -->
    <div class="bg-white p-3 mb-4 border rounded">
        <canvas id="graficoProdutos" height="100"></canvas>
    </div>

    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Preço Custo</th>
                <th>Preço Venda</th>
                <th>Quantidade</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= htmlspecialchars($p['categoria']) ?></td>
                <td>R$ <?= number_format(floatval($p['preco_custo']), 2, ",", ".") ?></td>
                <td>R$ <?= number_format(floatval($p['preco_venda']), 2, ",", ".") ?></td>
                <td><?= intval($p['qtd']) ?></td>
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

<script>
const ctx = document.getElementById('graficoProdutos').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            {
                label: 'Custo Total',
                data: <?= json_encode($precoCustoData) ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.7)'
            },
            {
                label: 'Venda Total',
                data: <?= json_encode($precoVendaData) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
