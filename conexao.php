<?php
$host = "localhost";
$db   = "sistema_financeiro";
$user = "root";
$pass = ""; // se tiver senha, coloque aqui

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>

