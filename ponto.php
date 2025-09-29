<?php
require_once "conexao.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// Buscar produtos do usuário
$produtos = $conn->prepare("SELECT preco_venda, preco_custo, quantidade FROM Produto WHERE usuario_id = ?");
$produtos->execute([$usuario_id]);
$produtos = $produtos->fetchAll(PDO::FETCH_ASSOC);

// Buscar despesas fixas e variáveis do usuário
$despesasFixas = $conn->prepare("SELECT SUM(valor) as total FROM Custo WHERE tipo='Fixa' AND usuario_id = ?");
$despesasFixas->execute([$usuario_id]);
$despesasFixas = $despesasFixas->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$despesasVariaveisExternas = $conn->prepare("SELECT SUM(valor) as total FROM Custo WHERE tipo='Variavel' AND usuario_id = ?");
$despesasVariaveisExternas->execute([$usuario_id]);
$despesasVariaveisExternas = $despesasVariaveisExternas->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Calcular receita total e custo variável dos produtos
$receitaTotal = 0;
$custoVariavelProdutos = 0;
foreach($produtos as $p){
    $receitaTotal += $p['preco_venda'] * $p['quantidade'];
    $custoVariavelProdutos += $p['preco_custo'] * $p['quantidade'];
}

// Custo total (produtos + despesas variáveis externas + fixas)
$custoTotal = $custoVariavelProdutos + $despesasVariaveisExternas + $despesasFixas;

// Ponto de equilíbrio em quantidade
$precoVendaMedio = count($produtos) ? array_sum(array_column($produtos,'preco_venda'))/count($produtos) : 0;
$custoVariavelMedio = count($produtos) ? array_sum(array_column($produtos,'preco_custo'))/count($produtos) : 0;
$ponto = 0;
if(($precoVendaMedio - $custoVariavelMedio) > 0){
    $ponto = ceil(($despesasFixas + $despesasVariaveisExternas) / ($precoVendaMedio - $custoVariavelMedio));
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Ponto de Equilíbrio - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Ponto de Equilíbrio</h2>
    <div class="card p-4 bg-white">
        <p><strong>Receita Total:</strong> R$ <?= number_format($receitaTotal,2,",",".") ?></p>
        <p><strong>Custo Fixo:</strong> R$ <?= number_format($despesasFixas,2,",",".") ?></p>
        <p><strong>Custo Variável (produtos + despesas variáveis):</strong> R$ <?= number_format($custoVariavelProdutos + $despesasVariaveisExternas,2,",",".") ?></p>
        <p><strong>Ponto de Equilíbrio (quantidade de produtos):</strong> <?= $ponto ?> unidades</p>
    </div>
    <a href="dashboard.php" class="btn btn-primary mt-3">Voltar ao Painel</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
