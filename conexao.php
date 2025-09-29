<?php
// Inicia a sessão se ainda não estiver ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuração do banco de dados
$host = "localhost";
$db   = "sistema_financeiro";
$user = "root";
$pass = ""; // coloque a senha do MySQL se tiver

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Função para proteger páginas (somente usuários logados)
function protegerPagina() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Função para limpar sessão e fazer logout
function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Função para buscar dados do usuário logado
function usuarioLogado($conn) {
    if (isset($_SESSION['usuario_id'])) {
        $stmt = $conn->prepare("SELECT id, nome, email FROM Usuario WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['usuario_id']);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}
?>
