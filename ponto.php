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

    // Custo variável total inclui o preço de custo do estoque
    $custoVariavelTotal = $custoVariavel + ($preco_custo * $quantidade);

    // Receita total (para estoque atual)
    $receitaTotal = $preco_venda * $quantidade;

    // Margem por unidade (preço de venda - custo unitário)
    $margemUnidade = $preco_venda - $preco_custo;

    // Ponto de equilíbrio (unidades) considerando apenas despesas fixas + variáveis (sem estoque)
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
        <canvas id="grafico" height="100"></canvas>
    </div>

    <!-- Tabela -->
    <table class="table table-bordered bg-white mt-4">
        <thead class="table-light">
            <tr>
                <th>Produto</th>
                <th>Custo Fixo</th>
                <th>Custo Variável Total</th>
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

    <div class="mt-3">
        <a href="dashboard.php" class="btn btn-primary">Voltar ao Painel</a>
    </div>
</div>

<script>
const ctx = document.getElementById('grafico').getContext('2d');
const labels = <?= json_encode(array_column($dadosTabela, 'produto')) ?>;
const custoFixoData = <?= json_encode(array_column($dadosTabela, 'custoFixo')) ?>;
const custoVariavelData = <?= json_encode(array_column($dadosTabela, 'custoVariavel')) ?>;
const receitaData = <?= json_encode(array_column($dadosTabela, 'receitaTotal')) ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            { label: 'Custo Fixo', data: custoFixoData, backgroundColor: 'rgba(255, 99, 132, 0.7)' },
            { label: 'Custo Variável Total', data: custoVariavelData, backgroundColor: 'rgba(54, 162, 235, 0.7)' },
            { label: 'Receita Total', data: receitaData, backgroundColor: 'rgba(75, 192, 192, 0.7)' }
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
