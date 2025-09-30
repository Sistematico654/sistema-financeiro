<?php
require_once "conexao.php";
protegerPagina();
$nomeUsuario = $_SESSION['usuario_nome'] ?? "Usuário"; // Assumindo que você salvou o nome na sessão
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Dashboard - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f4f6f9;
    }
    .card-dashboard {
        transition: transform 0.2s;
        cursor: pointer;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .card-dashboard:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .card-icon {
        font-size: 2.5rem;
    }
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .logout-btn {
        font-size: 0.9rem;
        padding: 0.3rem 0.8rem;
    }
</style>
</head>
<body>
<div class="container mt-4">

    <!-- Top Bar com Boas-vindas e Logout -->
    <div class="top-bar">
        <h4>Bem-vindo, <?= htmlspecialchars($nomeUsuario) ?>!</h4>
        <a href="logout.php" class="btn btn-danger logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>

    <div class="row g-4">

        <!-- Produtos -->
        <div class="col-md-3">
            <a href="produtos.php" class="text-decoration-none text-dark">
                <div class="card card-dashboard shadow-sm text-center p-3 bg-primary text-white">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <i class="fas fa-box card-icon mb-2"></i>
                        <h5 class="card-title">Produtos</h5>
                        <p class="card-text">Gerencie seus produtos</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Custos -->
        <div class="col-md-3">
            <a href="custos.php" class="text-decoration-none text-dark">
                <div class="card card-dashboard shadow-sm text-center p-3 bg-warning text-dark">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <i class="fas fa-dollar-sign card-icon mb-2"></i>
                        <h5 class="card-title">Custos / Despesas</h5>
                        <p class="card-text">Adicione ou edite custos</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Ponto de Equilíbrio -->
        <div class="col-md-3">
            <a href="ponto.php" class="text-decoration-none text-dark">
                <div class="card card-dashboard shadow-sm text-center p-3 bg-success text-white">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <i class="fas fa-balance-scale card-icon mb-2"></i>
                        <h5 class="card-title">Ponto de Equilíbrio</h5>
                        <p class="card-text">Visualize o equilíbrio de seus produtos</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Relatório -->
        <div class="col-md-3">
            <a href="relatorio.php" class="text-decoration-none text-dark">
                <div class="card card-dashboard shadow-sm text-center p-3 bg-info text-white">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <i class="fas fa-chart-bar card-icon mb-2"></i>
                        <h5 class="card-title">Relatório</h5>
                        <p class="card-text">Confira a viabilidade das vendas</p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
