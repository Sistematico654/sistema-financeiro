<?php
require_once 'conexao.php';
require_once 'funcoes.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['mensagem'] = "Link de recuperação inválido ou ausente.";
    header("Location: mensagem.php");
    exit();
}

$token_hash = hash('sha256', $token);

$conn = Database::getInstance()->getConnection();

$stmt = $conn->prepare("SELECT id FROM Usuario WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()");
$stmt->execute([$token_hash]);
$usuario = $stmt->fetch();

if (!$usuario) {
    $_SESSION['mensagem'] = "Link de recuperação inválido ou expirado. Por favor, solicite a recuperação novamente.";
    header("Location: mensagem.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Redefinir Senha - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background-color: #f8f9fa; }
    .card-reset { max-width: 450px; margin: 100px auto; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
</style>
</head>
<body>
<div class="card card-reset">
    <div class="card-body">
        <h3 class="card-title text-center mb-4">Crie uma Nova Senha</h3>

        <form action="salvar_nova_senha.php" method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="mb-3">
                <label for="senha" class="form-label">Nova Senha</label>
                <input type="password" name="senha" id="senha" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary w-100">Salvar Nova Senha</button>
        </form>
        
        <p class="mt-3 text-center">
            <a href="index.php">Voltar para o login</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>