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

// Preparar dados para tabela e gráfico
$dadosTabela = [];

foreach ($produtos as $p) {
    $id = $p['id'];
    $nome = $p['nome'];
    $quantidade = intval($p['qtd']);
    $preco_custo = floatval($p['preco_custo']);
    $preco_venda = floatval($p['preco_venda']);

    $custoFixo = 0;
    $custoVariavel = 0;

    foreach ($despesas as $d) {
        if ($d['produto_id'] == $id || is_null($d['produto_id'])) {
            $valorDistribuido = $d['valor'] / (is_null($d['produto_id']) ? count($produtos) : 1);
            if ($d['tipo'] === 'Fixa') {
                $custoFixo += $valorDistribuido;
            } else {
                $custoVariavel += $valorDistribuido;
            }
        }
    }

    $custoVariavelTotal = $custoVariavel + ($preco_custo * $quantidade);
    $receitaTotal = $preco_venda * $quantidade;
    $margemUnidade = $preco_venda - $preco_custo;
    $despesasTotaisParaPE = $custoFixo + $custoVariavel; 
    $pontoEquilibrio = $margemUnidade > 0 ? ceil($despesasTotaisParaPE / $margemUnidade) : 0;

    $dadosTabela[] = [
        'produto' => $nome,
        'custoFixo' => $custoFixo,
        'custoVariavel' => $custoVariavelTotal,
        'receitaTotal' => $receitaTotal,
        'pontoEquilibrio' => $pontoEquilibrio
    ];
}

$labels = array_map(fn($d) => $d['produto'], $dadosTabela);
$custoFixoData = array_map(fn($d) => $d['custoFixo'], $dadosTabela);
$custoVariavelData = array_map(fn($d) => $d['custoVariavel'], $dadosTabela);
$receitaTotalData = array_map(fn($d) => $d['receitaTotal'], $dadosTabela);
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Ponto de Equilíbrio por Produto</h2>
        <a href="dashboard.php" class="btn btn-primary">Voltar ao Painel</a>
    </div>

    <!-- Gráfico -->
    <div class="bg-white p-3 mb-4 border rounded">
        <canvas id="graficoPE" height="100"></canvas>
    </div>

    <!-- Tabela -->
    <table class="table table-bordered bg-white">
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
            <?php foreach($dadosTabela as $d): ?>
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
</div>

<script>
const ctx = document.getElementById('graficoPE').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            { label: 'Custo Fixo', data: <?= json_encode($custoFixoData) ?>, backgroundColor: 'rgba(255, 99, 132, 0.7)' },
            { label: 'Custo Variável total', data: <?= json_encode($custoVariavelData) ?>, backgroundColor: 'rgba(54, 162, 235, 0.7)' },
            { label: 'Receita Total', data: <?= json_encode($receitaTotalData) ?>, backgroundColor: 'rgba(75, 192, 192, 0.7)' }
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
