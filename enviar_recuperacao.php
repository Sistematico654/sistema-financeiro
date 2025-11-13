<?php
// ---- CÓDIGO DE DEBUG: REMOVA QUANDO ESTIVER EM PRODUÇÃO ----
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// -----------------------------------------------------------

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define para qual página vamos redirecionar após o processamento
$redirect_url = 'esqueci_senha.php';

// Requerimentos
require_once 'conexao.php';
require_once 'funcoes.php'; // Se este arquivo for necessário
require_once 'config.php'; // Contém as constantes SMTP_USER e SMTP_PASS

// Inclui os arquivos do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ajuste os caminhos abaixo conforme a sua estrutura de pastas local
require 'lib/phpmailer/Exception.php';
require 'lib/phpmailer/PHPMailer.php';
require 'lib/phpmailer/SMTP.php';


// Verifica se o formulário foi enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    // Validação básica
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['recovery_error'] = "Por favor, insira um email válido.";
        header("Location: $redirect_url");
        exit();
    }

    $conn = Database::getInstance()->getConnection();

    // 1. Busca o Usuário
    $stmt = $conn->prepare("SELECT id, nome, email FROM Usuario WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Processa se o Usuário for Encontrado
    if ($usuario) {
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        // Token expira em 1 hora
        $expira_em = date("Y-m-d H:i:s", time() + 3600); 

        // 3. Salva o Token no Banco
        $stmt_update = $conn->prepare("UPDATE Usuario SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?");

        if ($stmt_update->execute([$token_hash, $expira_em, $usuario['id']])) {
            
            $link_recuperacao = "https://sisfinanceiro.infinityfreeapp.com/redefinir_senha.php?token=" . $token;

            $mail = new PHPMailer(true);
            try {
                // Configurações do Servidor
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER; 
                $mail->Password   = SMTP_PASS; // Lembre-se: Usar "Senha de App" do Google
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';
                $mail->setLanguage('br', 'lib/phpmailer/language/phpmailer.lang-br.php');


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
                
                // Define a mensagem de SUCESSO na sessão
                $_SESSION['recovery_success'] = "Se um email correspondente for encontrado, um link de recuperação será enviado.";
                header("Location: $redirect_url");
                exit();

            } catch (Exception $e) {
                // Define a mensagem de ERRO na sessão
                $_SESSION['recovery_error'] = "Erro ao enviar o email. Verifique suas configurações. Erro: {$mail->ErrorInfo}";
                header("Location: $redirect_url");
                exit();
            }
        } else {
            $_SESSION['recovery_error'] = "Erro interno ao gerar o link. Tente novamente.";
            header("Location: $redirect_url");
            exit();
        }
    } else {
        // Mensagem de segurança: não revela se o email existe ou não
        // Usamos SUCESSO aqui para não dar dicas a um atacante
        $_SESSION['recovery_success'] = "Se um email correspondente for encontrado em nosso sistema, um link de recuperação será enviado.";
        header("Location: $redirect_url");
        exit();
    }
} else {
    // Se alguém tentar acessar este arquivo diretamente (via GET)
    $_SESSION['recovery_error'] = "Acesso inválido.";
    header("Location: $redirect_url");
    exit();
}
?>