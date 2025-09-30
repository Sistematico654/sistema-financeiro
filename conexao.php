<?php
session_start();

// Configurações do banco de dados
$host = "localhost";
$db   = "sistema_financeiro";
$user = "root";        // ajuste se seu usuário for diferente
$pass = "";            // ajuste se sua senha for diferente
$charset = "utf8mb4";

// DSN e opções PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Função para proteger páginas (verifica login)
function protegerPagina() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }
}
?>
