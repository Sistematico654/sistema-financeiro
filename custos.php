<?php
require_once "conexao.php";
protegerPagina();
$usuario_id = $_SESSION['usuario_id'];

// Inserir ou atualizar custo/despesa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];

    if ($id) {
        $stmt = $conn->prepare("UPDATE Custo SET tipo=:tipo, descricao=:descricao, valor=:valor WHERE id=:id AND usuario_id=:usuario_id");
        $stmt->bindParam(':id', $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO Custo (tipo, descricao, valor, usuario_id) VALUES (:tipo, :descricao, :valor, :usuario_id)");
    }
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    header("Location: custos.php");
}

// Deletar custo/despesa
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Custo WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario_id]);
    header("Location: custos.php");
}

// Buscar custo para edição
$editarCusto = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM Custo WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario_id]);
    $editarCusto = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Listar custos/despesas do usuário
$stmt = $conn->prepare("SELECT * FROM Custo WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Custos - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Custos / Despesas</h2>
    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="id" value="<?= $editarCusto['id'] ?? '' ?>">
        <div class="col-md-3">
            <select name="tipo" class="form-control" required>
                <option value="">Tipo</option>
                <option value="Fixa" <?= ($editarCusto['tipo'] ?? '')=='Fixa'?'selected':'' ?>>Fixa</option>
                <option value="Variavel" <?= ($editarCusto['tipo'] ?? '')=='Variavel'?'selected':'' ?>>Variável</option>
            </select>
        </div>
        <div class="col-md-5">
            <input type="text" name="descricao" placeholder="Descrição" class="form-control" value="<?= $editarCusto['descricao'] ?? '' ?>" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="valor" placeholder="Valor" class="form-control" value="<?= $editarCusto['valor'] ?? '' ?>" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100"><?= $editarCusto ? 'Atualizar' : 'Adicionar' ?></button>
        </div>
    </form>

    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th><th>Tipo</th><th>Descrição</th><th>Valor</th><th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($despesas as $d): ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= $d['tipo'] ?></td>
                <td><?= $d['descricao'] ?></td>
                <td>R$ <?= number_format($d['valor'],2,",",".") ?></td>
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
