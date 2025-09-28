<?php
require_once "conexao.php";
protegerPagina();

// Obter dados dos produtos e despesas
$produtos = $conn->query("SELECT nome, preco FROM Produto")->fetchAll(PDO::FETCH_ASSOC);
$despesas = $conn->query("SELECT descricao, valor FROM Custo")->fetchAll(PDO::FETCH_ASSOC);

// Preparar dados para Chart.js
$produtosNomes = json_encode(array_column($produtos,'nome'));
$produtosValores = json_encode(array_column($produtos,'preco'));
$despesasDescricoes = json_encode(array_column($despesas,'descricao'));
$despesasValores = json_encode(array_column($despesas,'valor'));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Relatórios - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Relatórios</h2>
    <div class="card p-4 bg-white mb-4">
        <h5>Produtos</h5>
        <canvas id="chartProdutos"></canvas>
    </div>
    <div class="card p-4 bg-white mb-4">
        <h5>Despesas</h5>
        <canvas id="chartDespesas"></canvas>
    </div>
    <button id="btnPdf" class="btn btn-danger mb-4">Exportar PDF</button>
    <a href="dashboard.php" class="btn btn-primary mb-4">Voltar ao Painel</a>
</div>

<script>
const ctxProdutos = document.getElementById('chartProdutos').getContext('2d');
new Chart(ctxProdutos, {
    type: 'bar',
    data: {
        labels: <?= $produtosNomes ?>,
        datasets: [{
            label: 'Preço (R$)',
            data: <?= $produtosValores ?>,
            backgroundColor: 'rgba(13,110,253,0.7)'
        }]
    }
});

const ctxDespesas = document.getElementById('chartDespesas').getContext('2d');
new Chart(ctxDespesas, {
    type: 'bar',
    data: {
        labels: <?= $despesasDescricoes ?>,
        datasets: [{
            label: 'Valor (R$)',
            data: <?= $despesasValores ?>,
            backgroundColor: 'rgba(220,53,69,0.7)'
        }]
    }
});

// Exportar PDF
document.getElementById('btnPdf').addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text("Relatório de Produtos e Despesas", 10, 10);

    doc.text("Produtos:", 10, 20);
    <?= $produtosNomes ?>.forEach((nome, i) => {
        doc.text(`${nome}: R$ ${<?= $produtosValores ?>}[i]`, 10, 30 + i*10);
    });

    let offset = 30 + <?= count($produtos) ?> * 10;
    doc.text("Despesas:", 10, offset);
    <?= $despesasDescricoes ?>.forEach((desc, i) => {
        doc.text(`${desc}: R$ ${<?= $despesasValores ?>}[i]`, 10, offset + 10 + i*10);
    });

    doc.save("relatorio.pdf");
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
