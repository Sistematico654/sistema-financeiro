<?php
session_start();
if (!isset($_SESSION['usuario_id'])) header("Location: login.php");
require_once "conexao.php";

// Inserir ou atualizar despesa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null; // Para edição
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];

    if ($id) {
        // Atualizar
        $stmt = $conn->prepare("UPDATE Custo SET descricao = :descricao, valor = :valor WHERE id = :id");
        $stmt->bindParam(':id', $id);
    } else {
        // Inserir
        $stmt = $conn->prepare("INSERT INTO Custo (descricao, valor) VALUES (:descricao, :valor)");
    }
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':valor', $valor);
    $stmt->execute();
    header("Location: custos.php");
    exit;
}

// Deletar despesa
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->prepare("DELETE FROM Custo WHERE id = ?")->execute([$id]);
    header("Location: custos.php");
    exit;
}

// Buscar despesa para editar
$editarDespesa = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM Custo WHERE id = ?");
    $stmt->execute([$id]);
    $editarDespesa = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Listar despesas
$despesas = $conn->query("SELECT * FROM Custo")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Despesas - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Custos Fixos e Variáveis</h2>

    <!-- Formulário de Adicionar/Editar -->
    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="id" value="<?= $editarDespesa['id'] ?? '' ?>">
        <div class="col-md-6">
            <input type="text" name="descricao" placeholder="Descrição" class="form-control" value="<?= $editarDespesa['descricao'] ?? '' ?>" required>
        </div>
        <div class="col-md-4">
            <input type="number" step="0.01" name="valor" placeholder="Valor" class="form-control" value="<?= $editarDespesa['valor'] ?? '' ?>" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100"><?= $editarDespesa ? 'Atualizar' : 'Adicionar' ?></button>
        </div>
    </form>

    <!-- Tabela de despesas -->
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Valor (R$)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($despesas as $d): ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= $d['descricao'] ?></td>
                <td><?= number_format($d['valor'], 2, ",", ".") ?></td>
                <td>
                    <a href="?edit=<?= $d['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?delete=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente deletar?')">Deletar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-primary mt-3">Voltar ao Painel</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
