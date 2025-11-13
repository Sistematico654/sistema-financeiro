<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "conexao.php";

// Conexão POO
$conn = Database::getInstance()->getConnection();

// Classe de Usuário
class Usuario {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function emailExiste($email) {
        $stmt = $this->conn->prepare("SELECT id FROM Usuario WHERE email = ?");
        $stmt->execute([$email]);
        // Retorna true se houver alguma linha (email existe)
        return $stmt->rowCount() > 0; 
    }

    public function cadastrar($nome, $email, $senha) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO Usuario (nome, email, senha) VALUES (?, ?, ?)");
        return $stmt->execute([$nome, $email, $senhaHash]);
    }
}

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

$erro = '';
$nome = $email = ''; // Variáveis para manter os dados no formulário em caso de erro

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta dados, usando o operador de coalescência para evitar warnings
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $senha_confirma = $_POST['senha_confirma'] ?? '';

    $usuario = new Usuario($conn);

    // --- 1. Validações Unificadas e Robustas ---
    if (empty($nome) || empty($email) || empty($senha) || empty($senha_confirma)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Formato de email inválido.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } elseif ($senha !== $senha_confirma) {
        $erro = "As senhas não coincidem.";
    } elseif ($usuario->emailExiste($email)) {
        $erro = "O e-mail já está cadastrado.";
    } else {
        // --- 2. Cadastro ---
        if ($usuario->cadastrar($nome, $email, $senha)) {
            // Configura mensagem de sucesso e redireciona para o login
            $_SESSION['mensagem_sucesso'] = "Cadastro realizado com sucesso! Faça login abaixo.";
            header("Location: index.php"); // Redirecionando para a página de login
            exit;
        } else {
            $erro = "Erro ao cadastrar. Tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sistema Financeiro</title>
    <!-- Links de estilo idênticos ao index.php -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif; 
        }

        /* ---------------------------------- */
        /* CORPO E BACKGROUND (Copiado de index.php) */
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
            font-family: 'Poppins', sans-serif; 
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* ---------------------------------- */
        /* CONTAINER DE VIDRO (GLASSMORPHYSM) */
        /* Ajustado para ser um pouco mais alto para o formulário maior */
        /* ---------------------------------- */
        .glass-container {
            position: relative;
            width: 420px; /* Levemente mais largo para mais campos */
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(15px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            z-index: 10;
            overflow: hidden;
        }

        .glass-container h2 {
            color: #fff;
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 1px;
            margin-bottom: 30px; /* Diminuído um pouco por ter mais campos */
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        /* ---------------------------------- */
        /* INPUTS (Copiado de index.php) */
        /* ---------------------------------- */
        .input-group {
            position: relative;
            margin-bottom: 25px; /* Diminuído o margin-bottom para caber mais campos */
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
        /* LABEL FLUTUANTE (Copiado de index.php) */
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

        /* Adicionando :not([value=""]) para fixar o label quando tem valor, mesmo sem foco */
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
        /* BOTÃO PRINCIPAL (Copiado de index.php) */
        /* ---------------------------------- */
        .register-btn {
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
            margin-top: 15px; /* Mais espaço acima */
        }

        .register-btn:hover {
            background: rgba(255, 255, 255, 0.6);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            color: #000;
        }

        /* ---------------------------------- */
        /* LINK DE LOGIN */
        /* ---------------------------------- */
        .login-link {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            display: block;
        }

        .login-link a {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
        
        /* ---------------------------------- */
        /* ESTILOS PARA ALERTAS (Copiado de index.php) */
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
        
    </style>
</head>
<body>
    <div class="glass-container">
        <h2>Criar Conta</h2>

        <!-- Exibição de Mensagens PHP -->
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <!-- Fim da Exibição de Mensagens -->

        <form method="POST">
            <div class="input-group">
                <!-- Mantém o valor em caso de erro -->
                <input type="text" name="nome" value="<?= htmlspecialchars($nome) ?>" required>
                <label>Nome Completo</label>
            </div>

            <div class="input-group">
                <!-- Mantém o valor em caso de erro -->
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                <label>Email</label>
            </div>

            <div class="input-group">
                <input type="password" name="senha" required>
                <label>Senha</label>
            </div>
            
            <div class="input-group">
                <!-- Nome do input ajustado para 'senha_confirma' conforme sua lógica PHP -->
                <input type="password" name="senha_confirma" required>
                <label>Confirme a Senha</label>
            </div>

            <button type="submit" class="register-btn">
                CADASTRAR
            </button>
        </form>

        <div class="login-link">
            Já tem uma conta? <a href="index.php">Fazer Login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>