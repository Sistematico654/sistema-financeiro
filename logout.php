<?php
require_once "conexao.php";

// Destrói todas as sessões
session_unset();
session_destroy();

// Redireciona para a página de login
header("Location: login.php");
exit;
?>

