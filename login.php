<?php
require_once "conexao.php";

$erro = "";

if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT * FROM Usuario WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nome'] = $user['nome'];
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Login inválido!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Login - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f7f9fc; }
.card-login { max-width: 400px; margin: 80px auto; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 12px; background-color: #fff; }
</style>
</head>
<body>
<div class="card-login">
    <h3 class="text-center mb-4">Sistema Financeiro</h3>
    <?php if (!empty($erro)) echo "<div class='alert alert-danger'>$erro</div>"; ?>
    <form method="post">
        <div class="mb-3">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Senha:</label>
            <input type="password" name="senha" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
    <p class="mt-3 text-center">Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
