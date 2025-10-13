<?php
// Inclui os arquivos necessários e inicia a sessão
require_once "conexao.php"; // Garanta que este caminho está correto

// Reutiliza a mesma lógica de proteção do seu dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Pega a conexão com o banco de dados (usando o mesmo padrão do seu dashboard)
try {
    $conn = Database::getInstance()->getConnection();

    //  JOIN para pegar o nome do usuário
    $stmt = $conn->prepare("
        SELECT u.nome as usuario, l.acao, l.descricao, l.data_log 
        FROM logs l 
        JOIN Usuario u ON l.usuario_id = u.id 
        ORDER BY l.data_log DESC
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao conectar ou consultar o banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Alterações - Sistema Financeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Histórico de Alterações</h1>
        <a href="dashboard.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (count($logs) > 0): ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Usuário</th>
                            <th>Ação Realizada</th>
                            <th>Descrição</th>
                            <th>Data e Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['usuario']) ?></td>
                                <td><?= htmlspecialchars($log['acao']) ?></td>
                                <td><?= htmlspecialchars($log['descricao']) ?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($log['data_log'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    Nenhum registro de histórico encontrado.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>