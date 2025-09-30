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

$dadosGrafico = [];

foreach ($produtos as $p) {
    $id = $p['id'];
    $nome = $p['nome'];
    $quantidade = $p['quantidade'];
    $preco_custo = $p['preco_custo'];
    $preco_venda = $p['preco_venda'];

    $custoFixo = 0;
    foreach ($despesas as $d) {
        if ($d['produto_id'] == $id || is_null($d['produto_id'])) {
            $custoFixo += $d['valor'] / (is_null($d['produto_id']) ? count($produtos) : 1);
        }
    }

    $receitaTotal = $preco_venda * $quantidade;
    $custoTotal = $custoFixo + ($preco_custo * $quantidade);
    $pontoEquilibrio = ($preco_venda - $preco_custo) > 0
        ? ceil($custoFixo / ($preco_venda - $preco_custo))
        : 0;

    $viabilidade = ($receitaTotal - $custoTotal) > 0 ? "Lucro" : "Prejuízo";

    $dadosGrafico[] = [
        'produto' => $nome,
        'receitaTotal' => $receitaTotal,
        'custoTotal' => $custoTotal,
        'pontoEquilibrio' => $pontoEquilibrio,
        'viabilidade' => $viabilidade
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Relatório de Viabilidade - Sistema Financeiro</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Relatório de Viabilidade por Produto</h2>

    <!-- Gráfico -->
    <canvas id="grafico" height="100"></canvas>

    <!-- Tabela -->
    <table class="table table-bordered bg-white mt-4">
        <thead class="table-light">
            <tr>
                <th>Produto</th>
                <th>Receita Total</th>
                <th>Custo Total</th>
                <th>Ponto de Equilíbrio (unidades)</th>
                <th>Viabilidade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dadosGrafico as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['produto']) ?></td>
                <td>R$ <?= number_format($d['receitaTotal'],2,",",".") ?></td>
                <td>R$ <?= number_format($d['custoTotal'],2,",",".") ?></td>
                <td><?= $d['pontoEquilibrio'] ?></td>
                <td><?= $d['viabilidade'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-3">
        <a href="dashboard.php" class="btn btn-primary">Voltar ao Painel</a>
    </div>
</div>

<script>
const ctx = document.getElementById('grafico').getContext('2d');
const labels = <?= json_encode(array_column($dadosGrafico, 'produto')) ?>;
const receitaData = <?= json_encode(array_column($dadosGrafico, 'receitaTotal')) ?>;
const custoData = <?= json_encode(array_column($dadosGrafico, 'custoTotal')) ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Receita Total',
                data: receitaData,
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            },
            {
                label: 'Custo Total',
                data: custoData,
                backgroundColor: 'rgba(255, 99, 132, 0.7)'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
</body>
</html>
