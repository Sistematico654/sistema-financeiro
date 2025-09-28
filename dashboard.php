<?php
require_once "conexao.php";
protegerPagina();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Dashboard - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f7f9fc; }
.navbar { background-color: #0d6efd; }
.navbar-brand, .navbar-nav .nav-link { color: #fff !important; }
.card { margin-top: 20px; border-radius: 12px; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Sistema Financeiro</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link">Olá, <?= $_SESSION['usuario_nome'] ?>!</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Sair</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <div class="row">
        <div class="col-md-6"><div class="card p-4"><h5>Produtos</h5><a href="produtos.php" class="btn btn-primary w-100">Acessar</a></div></div>
        <div class="col-md-6"><div class="card p-4"><h5>Despesas</h5><a href="custos.php" class="btn btn-primary w-100">Acessar</a></div></div>
    </div>
    <div class="row">
        <div class="col-md-6"><div class="card p-4"><h5>Ponto de Equilíbrio</h5><a href="ponto.php" class="btn btn-primary w-100">Calcular</a></div></div>
        <div class="col-md-6"><div class="card p-4"><h5>Relatórios</h5><a href="relatorios.php" class="btn btn-primary w-100">Visualizar</a></div></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
