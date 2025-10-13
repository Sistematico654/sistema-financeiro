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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $senha_confirma = trim($_POST['senha_confirma']);

    $usuario = new Usuario($conn);

    if ($nome && $email && $senha && $senha_confirma) {
        if ($senha !== $senha_confirma) {
            $erro = "As senhas não coincidem.";
        } elseif ($usuario->emailExiste($email)) {
            $erro = "O e-mail já está cadastrado.";
        } else {
            if ($usuario->cadastrar($nome, $email, $senha)) {
                header("Location: index.php?cadastro=sucesso");
                exit;
            } else {
                $erro = "Erro ao cadastrar. Tente novamente.";
            }
        }
    } else {
        $erro = "Todos os campos são obrigatórios.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cadastro - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.card-cadastro { max-width: 400px; margin: 100px auto; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
</style>
</head>
<body>
<div class="card card-cadastro">
    <div class="card-body">
        <h4 class="card-title mb-4 text-center">Cadastro</h4>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <input type="text" name="nome" class="form-control" placeholder="Nome" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="E-mail" required>
            </div>
            <div class="mb-3">
                <input type="password" name="senha" class="form-control" placeholder="Senha" required>
            </div>
            <div class="mb-3">
                <input type="password" name="senha_confirma" class="form-control" placeholder="Confirme a senha" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
        </form>

        <p class="mt-3 text-center">
            Já tem uma conta? <a href="index.php">Faça login</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>