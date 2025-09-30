<?php
require_once "conexao.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// Buscar produtos do usuário para seleção
$produtosStmt = $conn->prepare("SELECT id, nome FROM Produto WHERE usuario_id = ? ORDER BY nome ASC");
$produtosStmt->execute([$usuario_id]);
$produtos = $produtosStmt->fetchAll(PDO::FETCH_ASSOC);

// Inserir ou atualizar custo/despesa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $descricao = trim($_POST['descricao']);
    $valor = floatval($_POST['valor']);
    $produto_id = $_POST['produto_id'] ?: null; // pode ser nulo (custo geral)

    if ($id) {
        $stmt = $conn->prepare("
            UPDATE Custo 
            SET descricao=:descricao, valor=:valor, produto_id=:produto_id 
            WHERE id=:id AND usuario_id=:usuario_id
        ");
        $stmt->bindParam(':id', $id);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO Custo (descricao, valor, usuario_id, produto_id) 
            VALUES (:descricao, :valor, :usuario_id, :produto_id)
        ");
    }

    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':produto_id', $produto_id);
    $stmt->execute();

    header("Location: custos.php");
    exit;
}

// Deletar custo/despesa
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM Custo WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: custos.php");
    exit;
}

// Buscar custo para edição
$editarCusto = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM Custo WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $editarCusto = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Listar todos os custos/despesas do usuário em ordem alfabética por produto
$stmt = $conn->prepare("
    SELECT C.*, P.nome AS produto_nome 
    FROM Custo C 
    LEFT JOIN Produto P ON C.produto_id = P.id 
    WHERE C.usuario_id = ?
    ORDER BY produto_nome ASC, C.id DESC
");
$stmt->execute([$usuario_id]);
$despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar dados para gráfico
$labels = [];
$valores = [];
foreach ($despesas as $d) {
    $labels[] = $d['produto_nome'] ?? 'Todos os produtos';
    $valores[] = $d['valor'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Custos - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Custos / Despesas</h2>

    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="id" value="<?= $editarCusto['id'] ?? '' ?>">

        <div class="col-md-4">
            <input type="text" name="descricao" placeholder="Descrição" class="form-control" value="<?= htmlspecialchars($editarCusto['descricao'] ?? '') ?>" required>
        </div>

        <div class="col-md-2">
            <input type="number" step="0.01" name="valor" placeholder="Valor" class="form-control" value="<?= htmlspecialchars($editarCusto['valor'] ?? '') ?>" required>
        </div>

        <div class="col-md-4">
            <select name="produto_id" class="form-control">
                <option value="">Todos os produtos</option>
                <?php foreach($produtos as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($editarCusto['produto_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100"><?= $editarCusto ? 'Atualizar' : 'Adicionar' ?></button>
        </div>
    </form>

    <!-- Gráfico de custos por produto -->
    <div class="bg-white p-3 mb-4 border rounded">
        <canvas id="graficoCustos" height="100"></canvas>
    </div>

    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($despesas as $d): ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= $d['produto_nome'] ?? 'Todos os produtos' ?></td>
                <td><?= htmlspecialchars($d['descricao']) ?></td>
                <td>R$ <?= number_format($d['valor'], 2, ",", ".") ?></td>
                <td>
                    <a href="?edit=<?= $d['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?delete=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente deletar?')">Deletar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-primary mt-3">Voltar ao Painel</a>
</div>

<script>
const ctx = document.getElementById('graficoCustos').getContext('2d');
const graficoCustos = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Valor do Custo',
            data: <?= json_encode($valores) ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.7)'
        }]
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
