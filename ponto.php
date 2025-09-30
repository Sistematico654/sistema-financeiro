<?php
require_once "conexao.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// Buscar produtos e custos
$produtosStmt = $conn->prepare("SELECT * FROM Produto WHERE usuario_id = ? ORDER BY nome ASC");
$produtosStmt->execute([$usuario_id]);
$produtos = $produtosStmt->fetchAll(PDO::FETCH_ASSOC);

$despesasStmt = $conn->prepare("SELECT * FROM Custo WHERE usuario_id = ? ORDER BY descricao ASC");
$despesasStmt->execute([$usuario_id]);
$despesas = $despesasStmt->fetchAll(PDO::FETCH_ASSOC);

$dadosEquilibrio = [];

foreach ($produtos as $p) {
    $id = $p['id'];
    $nome = $p['nome'];
    $quantidade = $p['quantidade'];
    $preco_custo = $p['preco_custo'];
    $preco_venda = $p['preco_venda'];

    // Custo Fixo
    $custoFixo = 0;
    foreach ($despesas as $d) {
        if ($d['produto_id'] == $id || is_null($d['produto_id'])) {
            $custoFixo += $d['valor'] / (is_null($d['produto_id']) ? count($produtos) : 1);
        }
    }

    // Custo Variável
    $custoVariavel = $preco_custo * $quantidade;

    // Receita Total
    $receitaTotal = $preco_venda * $quantidade;

    // Ponto de Equilíbrio
    $pontoEquilibrio = ($preco_venda - $preco_custo) > 0 ? ceil($custoFixo / ($preco_venda - $preco_custo)) : 0;

    $dadosEquilibrio[] = [
        'produto' => $nome,
        'custoFixo' => $custoFixo,
        'custoVariavel' => $custoVariavel,
        'receitaTotal' => $receitaTotal,
        'pontoEquilibrio' => $pontoEquilibrio
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Ponto de Equilíbrio - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Ponto de Equilíbrio por Produto</h2>

    <canvas id="grafico" height="100"></canvas>

    <table class="table table-bordered bg-white mt-4">
        <thead class="table-light">
            <tr>
                <th>Produto</th>
                <th>Custo Fixo</th>
                <th>Custo Variável</th>
                <th>Receita Total</th>
                <th>Ponto de Equilíbrio (unidades)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dadosEquilibrio as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['produto']) ?></td>
                <td>R$ <?= number_format($d['custoFixo'],2,",",".") ?></td>
                <td>R$ <?= number_format($d['custoVariavel'],2,",",".") ?></td>
                <td>R$ <?= number_format($d['receitaTotal'],2,",",".") ?></td>
                <td><?= $d['pontoEquilibrio'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-primary mt-3">Voltar ao Painel</a>
</div>

<script>
const ctx = document.getElementById('grafico').getContext('2d');
const labels = <?= json_encode(array_column($dadosEquilibrio, 'produto')) ?>;
const receitaData = <?= json_encode(array_column($dadosEquilibrio, 'receitaTotal')) ?>;
const custoFixoData = <?= json_encode(array_column($dadosEquilibrio, 'custoFixo')) ?>;
const custoVariavelData = <?= json_encode(array_column($dadosEquilibrio, 'custoVariavel')) ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            { label: 'Custo Fixo', data: custoFixoData, backgroundColor: 'rgba(255, 99, 132, 0.7)' },
            { label: 'Custo Variável', data: custoVariavelData, backgroundColor: 'rgba(54, 162, 235, 0.7)' },
            { label: 'Receita Total', data: receitaData, backgroundColor: 'rgba(75, 192, 192, 0.7)' }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
