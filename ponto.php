<?php
session_start();
require_once "conexao.php";

// Protege página
if (!isset($_SESSION['usuario_id'])) header("Location: login.php");

// Receitas e custos
$produtos = $conn->query("SELECT preco_venda, preco_custo, quantidade FROM Produto")->fetchAll(PDO::FETCH_ASSOC);
$despesasFixas = $conn->query("SELECT SUM(valor) as total FROM Custo WHERE tipo='Fixa'")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$despesasVariaveis = $conn->query("SELECT SUM(valor) as total FROM Custo WHERE tipo='Variável'")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Soma receita total (preco_venda * quantidade)
$receitaTotal = 0;
$custoVariavelTotal = 0;
foreach($produtos as $p){
    $receitaTotal += $p['preco_venda'] * $p['quantidade'];
    $custoVariavelTotal += $p['preco_custo'] * $p['quantidade'];
}

// Ponto de equilíbrio em quantidade
$ponto = 0;
$precoVendaMedio = count($produtos) ? array_sum(array_column($produtos,'preco_venda'))/count($produtos) : 0;
$custoVariavelMedio = count($produtos) ? array_sum(array_column($produtos,'preco_custo'))/count($produtos) : 0;
if(($precoVendaMedio - $custoVariavelMedio) > 0){
    $ponto = $despesasFixas / ($precoVendaMedio - $custoVariavelMedio);
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
        <p><strong>Custo Variável:</strong> R$ <?= number_format($custoVariavelTotal + $despesasVariaveis,2,",",".") ?></p>
        <p><strong>Ponto de Equilíbrio (quantidade):</strong> <?= ceil($ponto) ?> produtos</p>
    </div>
    <a href="dashboard.php" class="btn btn-primary mt-3">Voltar ao Painel</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
