<?php
require_once "conexao.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// Buscar produtos do usuário
$produtosStmt = $conn->prepare("SELECT * FROM Produto WHERE usuario_id = ? ORDER BY nome ASC");
$produtosStmt->execute([$usuario_id]);
$produtos = $produtosStmt->fetchAll(PDO::FETCH_ASSOC);

$resultados = [];

foreach ($produtos as $p) {
    $produto_id = $p['id'];
    $preco_custo = floatval($p['preco_custo']);
    $preco_venda = floatval($p['preco_venda']);
    $quantidade = intval($p['qtd']);

    // Custos fixos
    $stmtFixo = $conn->prepare("SELECT SUM(valor) AS total_fixo FROM Custo WHERE usuario_id = ? AND produto_id = ? AND tipo = 'Fixa'");
    $stmtFixo->execute([$usuario_id, $produto_id]);
    $custo_fixo = floatval($stmtFixo->fetchColumn());

    // Custos variáveis cadastrados
    $stmtVar = $conn->prepare("SELECT SUM(valor) AS total_var FROM Custo WHERE usuario_id = ? AND produto_id = ? AND tipo = 'Variavel'");
    $stmtVar->execute([$usuario_id, $produto_id]);
    $custo_variavel = floatval($stmtVar->fetchColumn());

    // Incluir o custo do produto como custo variável
    $totalCustoVariavel = $custo_variavel + ($preco_custo * $quantidade);

    // Receita total
    $receita_total = $preco_venda * $quantidade;

    // Margem por unidade considerando custo variável
    $margem_unitaria = $preco_venda - ($preco_custo + ($custo_variavel / max($quantidade,1)));

    // Ponto de equilíbrio (unidades)
    $ponto_equilibrio = ($margem_unitaria > 0) ? ceil($custo_fixo / $margem_unitaria) : 0;

    $resultados[] = [
        'nome' => $p['nome'],
        'custo_fixo' => $custo_fixo,
        'custo_variavel' => $totalCustoVariavel,
        'receita_total' => $receita_total,
        'ponto_equilibrio' => $ponto_equilibrio
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

    <!-- Gráfico -->
    <div class="bg-white p-3 mb-4 border rounded">
        <canvas id="graficoPonto" height="100"></canvas>
    </div>

    <table class="table table-bordered bg-white mt-3">
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
            <?php foreach ($resultados as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['nome']) ?></td>
                <td>R$ <?= number_format($r['custo_fixo'], 2, ",", ".") ?></td>
                <td>R$ <?= number_format($r['custo_variavel'], 2, ",", ".") ?></td>
                <td>R$ <?= number_format($r['receita_total'], 2, ",", ".") ?></td>
                <td><?= $r['ponto_equilibrio'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Botão para voltar ao painel -->
    <a href="dashboard.php" class="btn btn-primary mt-3">Voltar ao Painel</a>
</div>

<script>
const ctx = document.getElementById('graficoPonto').getContext('2d');

const labels = <?= json_encode(array_column($resultados, 'nome')) ?>;
const custoFixo = <?= json_encode(array_column($resultados, 'custo_fixo')) ?>;
const custoVariavel = <?= json_encode(array_column($resultados, 'custo_variavel')) ?>;
const receitaTotal = <?= json_encode(array_column($resultados, 'receita_total')) ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Custo Fixo',
                data: custoFixo,
                backgroundColor: 'rgba(255, 99, 132, 0.7)'
            },
            {
                label: 'Custo Variável',
                data: custoVariavel,
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            },
            {
                label: 'Receita Total',
                data: receitaTotal,
                backgroundColor: 'rgba(75, 192, 192, 0.7)'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
