<?php
session_start();
// A conexão com o banco não é estritamente necessária aqui, mas é mantida por consistência
require_once "conexao.php"; 

$mensagem_sucesso = '';
$mensagem_erro = '';

// Pega as mensagens de sucesso ou erro vindas de enviar_recuperacao.php
if (isset($_SESSION['recovery_success'])) {
    $mensagem_sucesso = $_SESSION['recovery_success'];
    unset($_SESSION['recovery_success']);
}
if (isset($_SESSION['recovery_error'])) {
    $mensagem_erro = $_SESSION['recovery_error'];
    unset($_SESSION['recovery_error']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Sistema Financeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif; 
        }

        /* ---------------------------------- */
        /* CORPO E BACKGROUND */
        /* ---------------------------------- */
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url("imagem/fundo2.jpg");
            background-size: 150% 150%; 
            background-position: center;
            background-repeat: no-repeat;
            animation: gradientBG 15s ease infinite;
            overflow: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* ---------------------------------- */
        /* CONTAINER DE VIDRO (GLASSMORPHYSM) */
        /* ---------------------------------- */
        .glass-container {
            position: relative;
            width: 420px; 
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(15px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            z-index: 10;
            overflow: hidden;
            text-align: center; /* Centraliza elementos dentro do container */
        }

        .glass-container h2 {
            color: #fff;
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 1px;
            margin-bottom: 20px; 
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }
        
        .description-text {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 30px;
            font-weight: 300;
        }

        /* ---------------------------------- */
        /* INPUTS */
        /* ---------------------------------- */
        .input-group {
            position: relative;
            margin-bottom: 30px; 
            text-align: left; /* Garante que os inputs internos fiquem alinhados */
        }

        .input-group input {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            outline: none;
            border-radius: 35px;
            font-size: 16px;
            color: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5); 
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        /* ---------------------------------- */
        /* LABEL FLUTUANTE */
        /* ---------------------------------- */
        .input-group label {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .input-group input:focus ~ label,
        .input-group input:valid ~ label,
        .input-group input:not([value=""]) ~ label {
            top: 0;
            left: 15px;
            font-size: 12px;
            padding: 2px 8px;
            background: rgba(0, 0, 0, 0.2); 
            border-radius: 8px;
            color: #fff;
        }

        /* ---------------------------------- */
        /* BOTÃO PRINCIPAL */
        /* ---------------------------------- */
        .recovery-btn {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.4);
            border: none;
            outline: none;
            border-radius: 35px;
            color: #333; 
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .recovery-btn:hover {
            background: rgba(255, 255, 255, 0.6);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            color: #000;
        }
        
        /* ---------------------------------- */
        /* LINK VOLTAR */
        /* ---------------------------------- */
        .back-link {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            display: block;
        }

        .back-link a {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
        
        /* ---------------------------------- */
        /* ESTILOS PARA ALERTAS */
        /* ---------------------------------- */
        .glass-container .alert {
            margin-bottom: 25px;
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 14px;
            color: #fff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            border: none;
            text-align: center;
        }
        
        .glass-container .alert-danger {
            background-color: rgba(220, 53, 69, 0.7); 
        }
        
        .glass-container .alert-success {
            background-color: rgba(25, 135, 84, 0.7); 
        }

    </style>
</head>
<body>
    <div class="glass-container">
        <h2>Recuperar Senha</h2>

        <p class="description-text">Digite seu email para que possamos enviar um link de redefinição.</p>

        <?php if ($mensagem_sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensagem_sucesso) ?></div>
        <?php endif; ?>
        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($mensagem_erro) ?></div>
        <?php endif; ?>
        <form action="enviar_recuperacao.php" method="post">
            <div class="input-group">
                <input type="email" name="email" id="email" required>
                <label for="email">Email</label>
            </div>
            
            <button type="submit" class="recovery-btn">
                Enviar Link de Redefinição
            </button>
        </form>

        <div class="back-link">
            <a href="index.php">Voltar para o Login</a>
        </div>
    </div>

<script src="https://cdn.jsdelivr.nptm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>