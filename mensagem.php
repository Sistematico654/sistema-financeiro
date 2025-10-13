<?php
session_start();

// Pega a mensagem da sessão ou define uma padrão se não houver
$mensagem = $_SESSION['mensagem'] ?? 'Ocorreu um erro inesperado.';

// Limpa a mensagem da sessão para que ela não seja exibida novamente
unset($_SESSION['mensagem']); 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Aviso - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background-color: #f8f9fa; }
    .card-message { max-width: 500px; margin: 100px auto; }
</style>
</head>
<body>
<div class="card card-message text-center">
    <div class="card-body">
        <h5 class="card-title mb-3">Aviso do Sistema</h5>
        <p class="lead"><?= htmlspecialchars($mensagem) ?></p>
        <a href="index.php" class="btn btn-primary mt-3">Voltar para o Login</a>
    </div>
</div>
</body>
</html>