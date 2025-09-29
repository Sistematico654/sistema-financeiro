<?php
require_once "conexao.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// Buscar produtos do usuário
$produtos = $conn->prepare("SELECT preco_venda, preco_custo, quantidade FROM Produto WHERE usuario_id = ?");
$produtos->execute([$usuario_id]);
$produtos = $produtos->fetchAll(PDO::FETCH_ASSOC);

// Buscar custos/despesas do usuário
$despesasFixas = $conn->prepare("SELECT SUM(valor) as total FROM Custo WHERE tipo='Fixa' AND usuario_id = ?");
$despesasFixas->execute([$usuario_id]);
$despesasFixas = $despesasFixas->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$despesasVariaveis = $conn->prepare("SELECT SUM(valor) as total FROM Custo WHERE tipo='Variavel' AND usuario_id = ?");
$despesasVariaveis->execute([$usuario_id]);
$despesasVariaveis = $despesasVariaveis->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Calcular receita total e custo variável dos produtos
$receitaTotal = 0;
$custoVariavelProdutos = 0;
foreach($produtos as $p){
    $receitaTotal += $p['preco_venda'] * $p['quantidade'];
    $custoVariavelProdutos += $p['preco_custo'] * $p['quantidade'];
}

// Custo total (produtos + despesas variáveis externas + fixas)
$custoTotal = $custoVariavelProdutos + $despesasVariaveis + $despesasFixas;

// Preparar dados para o Chart.js
$labels = json_encode(['Receita Total', 'Custo Total']);
$valores = json_encode([round($receitaTotal,2), round($custoTotal,2)]);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Relatório Financeiro - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Relatório Financeiro</h2>
    <div class="card p-4 bg-white mb-4" id="graficoContainer">
        <canvas id="chartFinanceiro"></canvas>
    </div>
    <button id="btnPdf" class="btn btn-danger mb-4">Exportar PDF</button>
    <a href="dashboard.php" class="btn btn-primary mb-4">Voltar ao Painel</a>
</div>

<script>
// Gráfico Financeiro
const ctx = document.getElementById('chartFinanceiro').getContext('2d');
const chartFinanceiro = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= $labels ?>,
        datasets: [{
            label: 'Valores (R$)',
            data: <?= $valores ?>,
            backgroundColor: ['rgba(40,167,69,0.7)', 'rgba(220,53,69,0.7)']
        }]
    },
    options: {
        scales: { y: { beginAtZero: true } }
    }
});

// Exportar PDF com gráfico
document.getElementById('btnPdf').addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.setFontSize(14);
    doc.text("Relatório Financeiro", 10, 10);
    doc.setFontSize(12);
    doc.text(`Receita Total: R$ ${<?= round($receitaTotal,2) ?>}`, 10, 20);
    doc.text(`Custo Total: R$ ${<?= round($custoTotal,2) ?>}`, 10, 30);

    html2canvas(document.getElementById('graficoContainer')).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        doc.addImage(imgData, 'PNG', 10, 40, 180, 90);
        doc.save("relatorio_financeiro_usuario.pdf");
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
