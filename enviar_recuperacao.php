<?php
// Inclui os arquivos necessários
require_once 'conexao.php';
require_once 'funcoes.php';

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
            
            $link_recuperacao = "http://localhost/financeiro/redefinir_senha.php?token=" . $token;

            $mail = new PHPMailer(true);
            try {
                
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'xxxx'; 
                $mail->Password   = 'xxxx';   
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                // Remetente e Destinatário
                $mail->setFrom('brunowt360@gmail.com', 'Sistema Financeiro');
                $mail->addAddress($usuario['email'], $usuario['nome']);

                // Conteúdo do Email
                $mail->isHTML(true);
                $mail->Subject = 'Recuperação de Senha - Sistema Financeiro';
                $mail->Body    = "Olá, {$usuario['nome']}.<br><br>Recebemos uma solicitação para redefinir sua senha. Clique no link abaixo para criar uma nova senha:<br><br><a href='{$link_recuperacao}'>Redefinir Minha Senha</a><br><br>Se você não solicitou isso, pode ignorar este email.<br><br>Atenciosamente,<br>Equipe do Sistema Financeiro";
                $mail->AltBody = "Olá, {$usuario['nome']}.\n\nPara redefinir sua senha, copie e cole o seguinte link no seu navegador:\n{$link_recuperacao}\n\nSe você não solicitou isso, pode ignorar este email.";

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