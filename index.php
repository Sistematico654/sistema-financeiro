<?php
session_start();
require_once "conexao.php";

// Conexão POO 
$conn = Database::getInstance()->getConnection();

// Classe de autenticação
class Auth {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function login($email, $senha) {
        // Usa prepared statements para segurança
        $stmt = $this->conn->prepare("SELECT * FROM Usuario WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Verifica a senha
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            return true;
        }
        return false;
    }
}

// Redireciona se já estiver logado
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    $auth = new Auth($conn);
    if ($auth->login($email, $senha)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Email ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Financeiro</title>
    <!-- Links originais mantidos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            /* Usando uma fonte mais acessível globalmente */
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
            /* Importando Poppins, se não estiver no body */
            font-family: 'Poppins', sans-serif; 
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
            width: 380px;
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
            margin-bottom: 40px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        /* ---------------------------------- */
        /* INPUTS */
        /* ---------------------------------- */
        .input-group {
            position: relative;
            margin-bottom: 30px;
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
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5); /* Anel de foco branco */
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
        .input-group input:valid ~ label {
            top: 0;
            left: 15px;
            font-size: 12px;
            padding: 2px 8px;
            background: rgba(0, 0, 0, 0.2); /* Fundo escuro para destacar na cor de vidro */
            border-radius: 8px;
            color: #fff;
        }

        /* ---------------------------------- */
        /* LEMBRAR E ESQUECI A SENHA */
        /* ---------------------------------- */
        .remember-forgot {
            display: flex;
            justify-content: flex-end; /* Alinhado à direita para o link de esqueci a senha */
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }

        .remember-forgot a {
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .remember-forgot a:hover {
            text-decoration: underline;
        }

        /* ---------------------------------- */
        /* BOTÃO PRINCIPAL */
        /* ---------------------------------- */
        .login-btn {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.4);
            border: none;
            outline: none;
            border-radius: 35px;
            color: #333; /* Cor escura para contraste */
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .login-btn:hover {
            background: rgba(255, 255, 255, 0.6);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            color: #000;
        }

        /* ---------------------------------- */
        /* LINK DE CADASTRO */
        /* ---------------------------------- */
        .register-link {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            display: block;
        }

        .register-link a {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
        
        /* ---------------------------------- */
        /* ESTILOS PARA ALERTAS (MENSAGENS PHP) */
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
            background-color: rgba(220, 53, 69, 0.7); /* Vermelho translúcido forte */
        }
        
        .glass-container .alert-success {
            background-color: rgba(25, 135, 84, 0.7); /* Verde translúcido forte */
        }
    </style>
</head>
<body>
    <div class="glass-container">
        <h2>Login</h2>

        <!-- Exibição de Mensagens PHP -->
        <?php
        // Mensagem de Sucesso (Ex: após cadastro)
        if (isset($_SESSION['mensagem_sucesso'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['mensagem_sucesso']) . '</div>';
            unset($_SESSION['mensagem_sucesso']);
        }
        ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <!-- Fim da Exibição de Mensagens -->

        <form method="POST">
            <div class="input-group">
                <!-- Alterado name para 'email' e type para 'email' -->
                <input type="email" name="email" required>
                <label>Email</label>
            </div>

            <div class="input-group">
                <!-- Alterado name para 'senha' -->
                <input type="password" name="senha" required>
                <label>Senha</label>
            </div>

            <div class="remember-forgot">
                <!-- O checkbox de "Lembrar" foi removido, mantendo apenas "Esqueceu a Senha?" -->
                <a href="esqueci_senha.php">Esqueceu a Senha?</a>
            </div>

            <button type="submit" class="login-btn">
                ENTRAR
            </button>
        </form>

        <div class="register-link">
            Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
        </div>
    </div>

    <!-- Script do Bootstrap (mantido para compatibilidade, embora o JS não seja muito usado aqui) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>