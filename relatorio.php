<?php
require_once "conexao.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// Buscar produtos e custos
$produtosStmt = $conn->prepare("SELECT * FROM Produto WHERE usuario_id = ?");
$produtosStmt->execute([$usuario_id]);
$produtos = $produtosStmt->fetchAll(PDO::FETCH_ASSOC);

$despesasStmt = $conn->prepare("SELECT * FROM Custo WHERE usuario_id = ?");
$despesasStmt->execute([$usuario_id]);
$despesas = $despesasStmt->fetchAll(PDO::FETCH_ASSOC);

$dadosGrafico = [];

foreach ($produtos as $p) {
    $id = $p['id'];
    $nome = $p['nome'];
    $quantidade = $p['quantidade'];
    $preco_custo = $p['preco_custo'];
    $preco_venda = $p['preco_venda'];

    $custoVariavel = $preco_custo * $quantidade;
    $custoFixo = 0;

    foreach ($despesas as $d) {
        if ($d['produto_id'] == $id) {
            if ($d['tipo'] == 'Fixa') {
                $custoFixo += $d['valor'];
            } else {
                $custoVariavel += $d['valor'];
            }
        }

        if (is_null($d['produto_id'])) {
            if ($d['tipo'] == 'Fixa') {
                $custoFixo += $d['valor'] / count($produtos);
            } else {
                $custoVariavel += $d['valor'] / count($produtos);
            }
        }
    }

    $receitaTotal = $preco_venda * $quantidade;
    $custoTotal = $custoFixo + $custoVariavel;

    $dadosGrafico[] = [
        'produto' => $nome,
        'receita' => $receitaTotal,
        'custoTotal' => $custoTotal
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

    <canvas id="grafico" height="100"></canvas>

    <table class="table table-bordered bg-white mt-4">
        <thead class="table-light">
            <tr>
                <th>Produto</th>
                <th>Receita Total</th>
                <th>Custo Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dadosGrafico as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['produto']) ?></td>
                <td>R$ <?= number_format($d['receita'],2,",",".") ?></td>
                <td>R$ <?= number_format($d['custoTotal'],2,",",".") ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-primary mt-3">Voltar ao Painel</a>
</div>

<script>
const ctx = document.getElementById('grafico').getContext('2d');
const labels = <?= json_encode(array_column($dadosGrafico, 'produto')) ?>;
const receitaData = <?= json_encode(array_column($dadosGrafico, 'receita')) ?>;
const custoTotalData = <?= json_encode(array_column($dadosGrafico, 'custoTotal')) ?>;

const grafico = new Chart(ctx, {
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
                data: custoTotalData,
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
