<?php
require_once "conexao.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// Buscar produtos do usuário
$produtosStmt = $conn->prepare("SELECT * FROM Produto WHERE usuario_id = ? ORDER BY nome ASC");
$produtosStmt->execute([$usuario_id]);
$produtos = $produtosStmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar custos/despesas
$despesasStmt = $conn->prepare("SELECT * FROM Custo WHERE usuario_id = ? ORDER BY descricao ASC");
$despesasStmt->execute([$usuario_id]);
$despesas = $despesasStmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar dados para tabela e gráfico
$dadosGrafico = [];

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

    // Receita total
    $receitaTotal = $preco_venda * $quantidade;

    // Custo total (produto + variáveis + fixos)
    $custoTotal = $custoFixo + $custoVariavel + ($preco_custo * $quantidade);

    // Viabilidade
    $viabilidade = ($receitaTotal - $custoTotal) > 0 ? "Lucro" : "Prejuízo";

    $dadosGrafico[] = [
        'produto' => $nome,
        'receitaTotal' => $receitaTotal,
        'custoTotal' => $custoTotal,
        'viabilidade' => $viabilidade
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Relatório de Viabilidade - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Relatório de Viabilidade por Produto</h2>

    <!-- Gráfico -->
    <div class="bg-white p-3 mb-4 border rounded">
        <canvas id="grafico" height="100"></canvas>
    </div>

    <!-- Tabela -->
    <table class="table table-bordered bg-white mt-4">
        <thead class="table-light">
            <tr>
                <th>Produto</th>
                <th>Receita Total</th>
                <th>Custo Total</th>
                <th>Viabilidade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dadosGrafico as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['produto']) ?></td>
                <td>R$ <?= number_format($d['receitaTotal'],2,",",".") ?></td>
                <td>R$ <?= number_format($d['custoTotal'],2,",",".") ?></td>
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
            { label: 'Receita Total', data: receitaData, backgroundColor: 'rgba(54, 162, 235, 0.7)' },
            { label: 'Custo Total', data: custoData, backgroundColor: 'rgba(255, 99, 132, 0.7)' }
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
