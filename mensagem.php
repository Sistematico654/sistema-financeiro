<?php
session_start();

// Pega a mensagem da sessão ou define uma padrão se não houver
// Usando o operador de coalescência (??)
$mensagem = $_SESSION['mensagem'] ?? 'Ocorreu um erro inesperado.';

// Limpa a mensagem da sessão para que ela não seja exibida novamente
unset($_SESSION['mensagem']); 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aviso - Sistema Financeiro</title>
<!-- Links de estilo idênticos aos demais arquivos -->
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
        width: 500px; /* Mantendo o tamanho máximo do seu original */
        padding: 40px;
        border-radius: 20px;
        backdrop-filter: blur(15px);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
        z-index: 10;
        overflow: hidden;
        text-align: center;
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
    
    .message-lead {
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 30px;
        font-weight: 400;
        font-size: 1.2rem;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    /* ---------------------------------- */
    /* BOTÃO PRINCIPAL */
    /* ---------------------------------- */
    .btn-action {
        padding: 12px 30px;
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
        text-decoration: none;
        display: inline-block;
        margin-top: 15px;
    }

    .btn-action:hover {
        background: rgba(255, 255, 255, 0.6);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        color: #000;
    }

</style>
</head>
<body>
    <div class="glass-container">
        <h2>Aviso do Sistema</h2>

        <p class="message-lead"><?= htmlspecialchars($mensagem) ?></p>

        <a href="index.php" class="btn-action">
            Voltar para o Login
        </a>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>