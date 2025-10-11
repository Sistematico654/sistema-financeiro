<?php
// Inicia a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Protege uma página, redirecionando para o login se o usuário não estiver logado.
 */
function protegerPagina(): void {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Registra uma ação no log do sistema.
 * @param string $acao A descrição da ação a ser registrada.
 */
function registrar_log(string $acao): void {
    try {
        // Pega a instância da conexão do banco de dados
        $conn = Database::getInstance()->getConnection();

       
        // Agora usa a chave 'usuario_nome', que é a mesma definida no login.php
        $usuario = $_SESSION['usuario_nome'] ?? 'Sistema';

        // Prepara a inserção de forma segura
        $stmt = $conn->prepare("INSERT INTO logs (usuario, acao) VALUES (?, ?)");
        
        // Executa a inserção com os valores
        $stmt->execute([$usuario, $acao]);

    } catch (PDOException $e) {
        // Registra o erro em um log de erros do servidor
        error_log("Falha ao registrar log de auditoria: " . $e->getMessage());
    }
}