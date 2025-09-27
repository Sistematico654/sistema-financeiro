<?php
session_start();
if (!isset($_SESSION['usuario_id'])) header("Location: login.php");
require_once "conexao.php";

// Total receitas
$receitas = $conn->query("SELECT SUM(preco) as total FROM Produto")->fetch(PDO::FETCH_ASSOC);
$receitasTotais = $receitas['total'] ?? 0;

// Total custos
$custos = $conn->query("SELECT SUM(valor) as total FROM Custo")->fetch(PDO::FETCH_ASSOC);
$custosTotais = $custos['total'] ?? 0;

// Ponto de equilíbrio
$ponto = ($custosTotais > 0) ? $custosTotais : 0;
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
        <p><strong>Receitas Totais:</strong> R$ <?= number_format($receitasTotais,2,",",".") ?></p>
        <p><strong>Custos Totais:</strong> R$ <?= number_format($custosTotais,2,",",".") ?></p>
        <p><strong>Ponto de Equilíbrio:</strong> R$ <?= number_format($ponto,2,",",".") ?></p>
    </div>
    <a href="dashboard.php" class="btn btn-primary mt-3">Voltar ao Painel</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
