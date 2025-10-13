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
        header("Location: index.php");
        exit;
    }
}

/**
 * Registra uma ação no log do sistema.
 * @param string $acao A descrição da ação a ser registrada.
 * @param string $descricao Descrição detalhada da ação (opcional)
 */
function registrar_log(string $acao, string $descricao = ""): void {
    try {
        // Pega a instância da conexão do banco de dados
        $conn = Database::getInstance()->getConnection();

        // Obtém o ID do usuário da sessão (não o nome)
        $usuario_id = $_SESSION['usuario_id'] ?? 0;

        // Prepara a inserção usando a estrutura CORRETA da tabela logs
        $stmt = $conn->prepare("INSERT INTO logs (usuario_id, acao, descricao) VALUES (?, ?, ?)");
        
        // Executa a inserção com os valores
        $stmt->execute([$usuario_id, $acao, $descricao]);

    } catch (PDOException $e) {
        // Registra o erro em um log de erros do servidor
        error_log("Falha ao registrar log de auditoria: " . $e->getMessage());
    }
}

/**
 * Função auxiliar para formatar valores em Reais
 */
function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Função para validar e sanitizar dados de entrada
 */
function sanitizar($dados) {
    if (is_array($dados)) {
        return array_map('sanitizar', $dados);
    }
    return htmlspecialchars(trim($dados), ENT_QUOTES, 'UTF-8');
}