<?php
require_once "conexao.php";

// Se já estiver logado, redireciona para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

$erro = "";
$sucesso = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $senha_confirm = $_POST['senha_confirm'];

    // Validação básica
    if (empty($nome) || empty($email) || empty($senha) || empty($senha_confirm)) {
        $erro = "Todos os campos são obrigatórios!";
    } elseif ($senha !== $senha_confirm) {
        $erro = "As senhas não coincidem!";
    } else {
        // Verificar se email já existe
        $stmt = $conn->prepare("SELECT id FROM Usuario WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->fetch()) {
            $erro = "Este email já está cadastrado!";
        } else {
            // Criar usuário
            $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Usuario (nome, email, senha) VALUES (:nome, :email, :senha)");
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $hashSenha);
            $stmt->execute();

            $sucesso = "Cadastro realizado com sucesso! Você já pode fazer login.";
        }
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
.card-cadastro { max-width: 450px; margin: 60px auto; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 12px; background-color: #fff; }
</style>
</head>
<body>
<div class="card-cadastro">
    <h3 class="text-center mb-4">Cadastro de Usuário</h3>
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
        <div class="mb-3">
            <label>Confirmar Senha:</label>
            <input type="password" name="senha_confirm" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Cadastrar</button>
    </form>
    <p class="mt-3 text-center">Já tem conta? <a href="login.php">Faça login</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
