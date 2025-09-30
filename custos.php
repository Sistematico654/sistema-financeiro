<?php
require_once "conexao.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// Buscar produtos do usuário
$produtosStmt = $conn->prepare("SELECT * FROM Produto WHERE usuario_id = ? ORDER BY nome ASC");
$produtosStmt->execute([$usuario_id]);
$produtos = $produtosStmt->fetchAll(PDO::FETCH_ASSOC);

// Inserir ou atualizar custo/despesa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : null;
    $descricao = trim($_POST['descricao'] ?? '');
    $valor = floatval($_POST['valor'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $produto_id = isset($_POST['produto_id']) && $_POST['produto_id'] !== '' ? intval($_POST['produto_id']) : null;

    if (empty($tipo) || !in_array($tipo, ['Fixa','Variavel'])) {
        header("Location: custos.php?erro=tipo_invalido");
        exit;
    }

    if ($id) {
        $stmt = $conn->prepare("UPDATE Custo SET descricao = :descricao, valor = :valor, tipo = :tipo, produto_id = :produto_id WHERE id = :id AND usuario_id = :usuario_id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    } else {
        $stmt = $conn->prepare("INSERT INTO Custo (descricao, valor, tipo, usuario_id, produto_id) VALUES (:descricao, :valor, :tipo, :usuario_id, :produto_id)");
    }

    $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
    $stmt->bindValue(':valor', $valor);
    $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
    $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->bindValue(':produto_id', $produto_id, PDO::PARAM_INT);
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

// Listar todos os custos/despesas do usuário
$stmt = $conn->prepare("SELECT C.*, P.nome AS produto_nome, P.preco_custo, P.qtd FROM Custo C LEFT JOIN Produto P ON C.produto_id = P.id WHERE C.usuario_id = ? ORDER BY produto_nome ASC, C.id DESC");
$stmt->execute([$usuario_id]);
$despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar dados para gráfico (custo total por produto)
$totaisPorProduto = [];
foreach ($produtos as $p) {
    $produto_id = $p['id'];
    $totaisPorProduto[$produto_id] = floatval($p['preco_custo']) * intval($p['qtd']);
}
foreach ($despesas as $d) {
    if ($d['produto_id']) {
        $totaisPorProduto[$d['produto_id']] += floatval($d['valor']);
    }
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

    <!-- Cabeçalho com botão no topo direito -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Custos / Despesas</h2>
        <a href="dashboard.php" class="btn btn-primary">Voltar ao Painel</a>
    </div>

    <?php if(isset($_GET['erro']) && $_GET['erro'] === 'tipo_invalido'): ?>
        <div class="alert alert-warning">Selecione um tipo válido de custo.</div>
    <?php endif; ?>

    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="id" value="<?= htmlspecialchars($editarCusto['id'] ?? '') ?>">

        <div class="col-md-3">
            <select name="produto_id" class="form-control" required>
                <option value="">Selecione o produto</option>
                <?php foreach($produtos as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (isset($editarCusto['produto_id']) && $editarCusto['produto_id'] == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <input type="text" name="descricao" placeholder="Descrição" class="form-control" value="<?= htmlspecialchars($editarCusto['descricao'] ?? '') ?>" required>
        </div>

        <div class="col-md-2">
            <input type="number" step="0.01" name="valor" placeholder="Valor" class="form-control" value="<?= htmlspecialchars($editarCusto['valor'] ?? '') ?>" required>
        </div>

        <div class="col-md-2">
            <select name="tipo" class="form-control" required>
                <option value="">Selecione o tipo</option>
                <option value="Fixa" <?= (isset($editarCusto['tipo']) && $editarCusto['tipo'] === 'Fixa') ? 'selected' : '' ?>>Fixo</option>
                <option value="Variavel" <?= (isset($editarCusto['tipo']) && $editarCusto['tipo'] === 'Variavel') ? 'selected' : '' ?>>Variável</option>
            </select>
        </div>

        <div class="col-md-2 d-flex justify-content-center">
            <button type="submit" class="btn btn-success w-100"><?= $editarCusto ? 'Atualizar' : 'Adicionar' ?></button>
        </div>
    </form>

    <!-- Gráfico -->
    <div class="bg-white p-3 mb-4 border rounded">
        <canvas id="graficoCustos" height="100"></canvas>
    </div>

    <!-- Tabela -->
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($despesas as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['id']) ?></td>
                <td><?= htmlspecialchars($d['produto_nome'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['descricao'] ?? '') ?></td>
                <td>R$ <?= number_format(floatval($d['valor'] ?? 0), 2, ",", ".") ?></td>
                <td><?= htmlspecialchars($d['tipo'] ?? '') ?></td>
                <td>
                    <a href="?edit=<?= $d['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?delete=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente deletar?')">Deletar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
const ctx = document.getElementById('graficoCustos').getContext('2d');
const labels = <?= json_encode(array_map(fn($p) => $p['nome'], $produtos)) ?>;
const dataTotais = <?= json_encode(array_values($totaisPorProduto)) ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Custo Total (Produto + Custos)',
            data: dataTotais,
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
