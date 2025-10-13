<?php
// Inclui os arquivos necessários
require_once 'conexao.php';
require_once 'funcoes.php';

// **** INCLUI O ARQUIVO DE CONFIGURAÇÃO COM AS SENHAS ****
require_once 'config.php';

// Inclui os arquivos do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'lib/phpmailer/Exception.php';
require 'lib/phpmailer/PHPMailer.php';
require 'lib/phpmailer/SMTP.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $conn = Database::getInstance()->getConnection();

    $stmt = $conn->prepare("SELECT id, nome, email FROM Usuario WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $expira_em = date("Y-m-d H:i:s", time() + 3600);
        $stmt_update = $conn->prepare("UPDATE Usuario SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?");

        if ($stmt_update->execute([$token_hash, $expira_em, $usuario['id']])) {
            // LINK CORRIGIDO PARA O SEU DOMÍNIO REAL
            $link_recuperacao = "https://sisfinanceiro.infinityfreeapp.com/redefinir_senha.php?token=" . $token;

            $mail = new PHPMailer(true);
            try {
                // Configurações do Servidor
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                
                // **** USA AS CONSTANTES DO ARQUIVO config.php ****
                $mail->Username   = SMTP_USER; 
                $mail->Password   = SMTP_PASS; 
                
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                // Remetente e Destinatário
                $mail->setFrom(SMTP_USER, 'Sistema Financeiro');
                $mail->addAddress($usuario['email'], $usuario['nome']);

                // Conteúdo do Email
                $mail->isHTML(true);
                $mail->Subject = 'Recuperação de Senha - Sistema Financeiro';
                $mail->Body    = "Olá, {$usuario['nome']}.<br><br>
                                Você solicitou a redefinição de sua senha no Sistema Financeiro.<br><br>
                                Clique no link abaixo para redefinir sua senha:<br>
                                <a href='{$link_recuperacao}'>{$link_recuperacao}</a><br><br>
                                Este link expirará em 1 hora.<br><br>
                                Se você não solicitou esta redefinição, ignore este email.";
                $mail->AltBody = "Olá, {$usuario['nome']}.\n\n
                                Você solicitou a redefinição de sua senha no Sistema Financeiro.\n\n
                                Clique no link abaixo para redefinir sua senha:\n
                                {$link_recuperacao}\n\n
                                Este link expirará em 1 hora.\n\n
                                Se você não solicitou esta redefinição, ignore este email.";

                $mail->send();
                $_SESSION['mensagem'] = "Um link para redefinição de senha foi enviado para o seu email.";
                header("Location: mensagem.php");
                exit();

            } catch (Exception $e) {
                $_SESSION['mensagem'] = "Erro ao enviar o email. Verifique suas configurações. Erro: {$mail->ErrorInfo}";
                header("Location: mensagem.php");
                exit();
            }
        }
    } else {
        $_SESSION['mensagem'] = "Se um email correspondente for encontrado em nosso sistema, um link de recuperação será enviado.";
        header("Location: mensagem.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha - Sistema Financeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-recuperacao { max-width: 400px; margin: 100px auto; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
    </style>
</head>
<body>
    <div class="card card-recuperacao">
        <div class="card-body">
            <h4 class="card-title mb-4 text-center">Recuperar Senha</h4>
            <form method="post">
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Digite seu e-mail" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Enviar Link de Recuperação</button>
            </form>
            <p class="mt-3 text-center">
                <a href="index.php">Voltar para o login</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>