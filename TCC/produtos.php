<?php
require_once "conexao.php";
require_once "funcoes.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// Classe Produto
class Produto {
    private $conn;
    private $usuario_id;

    public function __construct($conn, $usuario_id) {
        $this->conn = $conn;
        $this->usuario_id = $usuario_id;
    }

    public function listar() {
        $stmt = $this->conn->prepare("SELECT * FROM Produto WHERE usuario_id=? ORDER BY nome ASC");
        $stmt->execute([$this->usuario_id]);
        return $stmt->fetchAll();
    }

    public function buscar($id) {
        $stmt = $this->conn->prepare("SELECT * FROM Produto WHERE id=? AND usuario_id=?");
        $stmt->execute([$id, $this->usuario_id]);
        return $stmt->fetch();
    }

    public function salvar($id, $nome, $categoria, $preco_custo, $preco_venda, $qtd) {
        if ($id) {
            $stmt = $this->conn->prepare("
                UPDATE Produto 
                SET nome=?, categoria=?, preco_custo=?, preco_venda=?, qtd=? 
                WHERE id=? AND usuario_id=?
            ");
            return $stmt->execute([$nome, $categoria, $preco_custo, $preco_venda, $qtd, $id, $this->usuario_id]);
        } else {
            $stmt = $this->conn->prepare("
                INSERT INTO Produto (nome, categoria, preco_custo, preco_venda, qtd, usuario_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$nome, $categoria, $preco_custo, $preco_venda, $qtd, $this->usuario_id]);
        }
    }

    public function deletar($id) {
        $stmt = $this->conn->prepare("DELETE FROM Produto WHERE id=? AND usuario_id=?");
        return $stmt->execute([$id, $this->usuario_id]);
    }
}

// Conexão
$conn = Database::getInstance()->getConnection();
$produto = new Produto($conn, $usuario_id);

$editarProduto = null;

// Adicionar / Atualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $id = $id !== '' ? intval($id) : null;
    $nome = trim($_POST['nome'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $preco_custo = floatval($_POST['preco_custo'] ?? 0);
    $preco_venda = floatval($_POST['preco_venda'] ?? 0);
    $qtd = intval($_POST['qtd'] ?? 0);

    if ($nome && $categoria) {
        $produto->salvar($id, $nome, $categoria, $preco_custo, $preco_venda, $qtd);
        header("Location: produtos.php");
        exit;
    }
}

// Deletar
if (isset($_GET['delete'])) {
    $produto->deletar(intval($_GET['delete']));
    header("Location: produtos.php");
    exit;
}

// Editar
if (isset($_GET['edit'])) {
    $editarProduto = $produto->buscar(intval($_GET['edit']));
}

// Listagem
$produtosLista = $produto->listar();

// Preparar dados gráfico
$labels = [];
$precoCustoData = [];
$precoVendaData = [];
foreach ($produtosLista as $p) {
    $labels[] = $p['nome'];
    $precoCustoData[] = floatval($p['preco_custo'])*intval($p['qtd']);
    $precoVendaData[] = floatval($p['preco_venda'])*intval($p['qtd']);
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

    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Produtos</h2>
        <a href="dashboard.php" class="btn btn-primary">Voltar ao Painel</a>
    </div>

    <!-- Formulário -->
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

        <div class="col-md-2 d-flex justify-content-center">
            <button type="submit" class="btn btn-success w-100"><?= $editarProduto ? 'Atualizar' : 'Adicionar' ?></button>
        </div>
    </form>

    <!-- Gráfico -->
    <div class="bg-white p-3 mb-4 border rounded">
        <canvas id="graficoProdutos" height="100"></canvas>
    </div>

    <!-- Tabela -->
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
            <?php foreach ($produtosLista as $p): ?>
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
</div>

<script>
const ctx = document.getElementById('graficoProdutos').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            { label: 'Custo Total do Produto', data: <?= json_encode($precoCustoData) ?>, backgroundColor: 'rgba(255, 99, 132, 0.7)' },
            { label: 'Venda Total do Produto', data: <?= json_encode($precoVendaData) ?>, backgroundColor: 'rgba(54, 162, 235, 0.7)' }
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
