<?php
session_start();
require_once "conexao.php";

$erro = "";
$sucesso = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Verifica se email já existe
    $stmt = $conn->prepare("SELECT * FROM Usuario WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    if ($stmt->fetch()) {
        $erro = "Email já cadastrado!";
    } else {
        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO Usuario (nome, email, senha) VALUES (:nome, :email, :senha)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $hashSenha);
        $stmt->execute();
        $sucesso = "Cadastro realizado com sucesso! <a href='login.php'>Faça login</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cadastro - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f7f9fc; }
.card-login { max-width: 400px; margin: 80px auto; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 12px; background-color: #fff; }
</style>
</head>
<body>
<div class="card-login">
    <h3 class="text-center mb-4">Cadastro</h3>
    <?php 
    if (!empty($erro)) echo "<div class='alert alert-danger'>$erro</div>";
    if (!empty($sucesso)) echo "<div class='alert alert-success'>$sucesso</div>";
    ?>
    <form method="post">
        <div class="mb-3">
            <label>Nome:</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Senha:</label>
            <input type="password" name="senha" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Cadastrar</button>
    </form>
    <p class="mt-3 text-center">Já tem conta? <a href="login.php">Login</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
