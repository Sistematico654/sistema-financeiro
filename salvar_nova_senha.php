<?php
require_once 'conexao.php';
require_once 'funcoes.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $nova_senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // 1. Verifica se as senhas digitadas são iguais
    if ($nova_senha !== $confirmar_senha) {
        $_SESSION['mensagem'] = "As senhas não coincidem. Tente novamente.";
        // Redireciona de volta para a página de redefinição com o mesmo token
        header("Location: redefinir_senha.php?token=" . urlencode($token));
        exit();
    }
    
    // (Opcional, mas recomendado) Adicionar uma verificação de força da senha
    if (strlen($nova_senha) < 6) {
        $_SESSION['mensagem'] = "A senha deve ter no mínimo 6 caracteres.";
        header("Location: redefinir_senha.php?token=" . urlencode($token));
        exit();
    }

    // 2. Revalida o token para garantir que não foi adulterado
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

    // 3. Se tudo estiver correto, atualiza a senha
    // Criptografa a nova senha com password_hash()
    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    // Atualiza a senha e LIMPA os tokens de recuperação para que o link não possa ser usado novamente
    $stmt_update = $conn->prepare("
        UPDATE Usuario 
        SET senha = ?, reset_token_hash = NULL, reset_token_expires_at = NULL 
        WHERE id = ?
    ");
    
    if ($stmt_update->execute([$nova_senha_hash, $usuario['id']])) {
        $_SESSION['mensagem_sucesso'] = "Sua senha foi redefinida com sucesso! Você já pode fazer o login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['mensagem'] = "Ocorreu um erro ao atualizar sua senha. Por favor, tente novamente.";
        header("Location: mensagem.php");
        exit();
    }

} else {
    // Se alguém tentar acessar este arquivo diretamente, redireciona
    header('Location: login.php');
    exit();
}
?>