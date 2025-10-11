<?php
require_once 'conexao.php';
require_once 'funcoes.php';

// Inicia a sessão para podermos mostrar mensagens
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Pega o token da URL
$token = $_GET['token'] ?? '';

// Se não houver token, o link é inválido
if (empty($token)) {
    $_SESSION['mensagem'] = "Link de recuperação inválido ou ausente.";
    header("Location: mensagem.php");
    exit();
}

// 2. Transforma o token em hash para comparar com o banco de dados
$token_hash = hash('sha256', $token);

$conn = Database::getInstance()->getConnection();

// 3. Verifica se o token hash existe e se não expirou
$stmt = $conn->prepare("SELECT id FROM Usuario WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()");
$stmt->execute([$token_hash]);
$usuario = $stmt->fetch();

// Se não encontrou um usuário, o token é inválido ou expirou
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
    .card-reset { max-width: 450px; margin: 100px auto; }
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
                <input type="password" name="senha" id="senha" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Salvar Nova Senha</button>
        </form>
    </div>
</div>
</body>
</html>