<?php
require_once "conexao.php";
require_once "funcoes.php";
protegerPagina();

$usuario_id = $_SESSION['usuario_id'];

// Classes Custo e Produto
class Custo {
    private $conn;
    private $usuario_id;
    public function __construct($conn, $usuario_id) {
        $this->conn = $conn;
        $this->usuario_id = $usuario_id;
    }
    public function listar() {
        $stmt = $this->conn->prepare("
            SELECT C.*, P.nome AS produto_nome 
            FROM Custo C 
            LEFT JOIN Produto P ON C.produto_id = P.id 
            WHERE C.usuario_id = ? 
            ORDER BY produto_nome ASC, C.id DESC
        ");
        $stmt->execute([$this->usuario_id]);
        return $stmt->fetchAll();
    }
    public function buscar($id) {
        $stmt = $this->conn->prepare("SELECT * FROM Custo WHERE id=? AND usuario_id=?");
        $stmt->execute([$id, $this->usuario_id]);
        return $stmt->fetch();
    }
    public function salvar($id, $descricao, $valor, $tipo, $produto_id) {
        if ($id) {
            $stmt = $this->conn->prepare("
                UPDATE Custo SET descricao=?, valor=?, tipo=?, produto_id=? 
                WHERE id=? AND usuario_id=?
            ");
            return $stmt->execute([$descricao, $valor, $tipo, $produto_id, $id, $this->usuario_id]);
        } else {
            $stmt = $this->conn->prepare("
                INSERT INTO Custo (descricao, valor, tipo, usuario_id, produto_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$descricao, $valor, $tipo, $this->usuario_id, $produto_id]);
        }
    }
    public function deletar($id) {
        $stmt = $this->conn->prepare("DELETE FROM Custo WHERE id=? AND usuario_id=?");
        return $stmt->execute([$id, $this->usuario_id]);
    }
}

class Produto {
    private $conn;
    private $usuario_id;
    public function __construct($conn, $usuario_id) {
        $this->conn = $conn;
        $this->usuario_id = $usuario_id;
    }
    public function listar() {
        $stmt = $this->conn->prepare("SELECT * FROM Produto WHERE usuario_id=? ORDER BY nome ASC");
        $stmt->execute([$this->usuario_id]);
        return $stmt->fetchAll();
    }
}

// Conexão
$conn = Database::getInstance()->getConnection();
$custo = new Custo($conn, $usuario_id);
$produto = new Produto($conn, $usuario_id);

$erro = '';
$editarCusto = null;

// Adicionar / Atualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $id = $id !== '' ? intval($id) : null;
    $descricao = trim($_POST['descricao'] ?? '');
    $valor = floatval($_POST['valor'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $produto_id = $_POST['produto_id'] ?? null;
    $produto_id = $produto_id !== '' ? intval($produto_id) : null;

    if (!in_array($tipo, ['Fixa','Variavel'])) {
        $erro = "Selecione um tipo válido de custo.";
    } elseif ($descricao && $valor && $tipo && $produto_id) {
        $custo->salvar($id, $descricao, $valor, $tipo, $produto_id);
        header("Location: custos.php");
        exit;
    } else {
        $erro = "Preencha todos os campos corretamente.";
    }
}

// Deletar
if (isset($_GET['delete'])) {
    $custo->deletar(intval($_GET['delete']));
    header("Location: custos.php");
    exit;
}

// Editar
if (isset($_GET['edit'])) {
    $editarCusto = $custo->buscar(intval($_GET['edit']));
}

// Listagem
$despesas = $custo->listar();
$produtosLista = $produto->listar();

// Preparar dados gráfico: separar fixo, variável e custo do produto
$totaisFixos = [];
$totaisVariaveis = [];
$totaisProdutos = [];

foreach ($produtosLista as $p) {
    $id = $p['id'];
    $totaisFixos[$id] = 0;
    $totaisVariaveis[$id] = 0;
    $totaisProdutos[$id] = floatval($p['preco_custo']) * intval($p['qtd']); // custo do produto

    foreach ($despesas as $d) {
        if ($d['produto_id'] == $id || is_null($d['produto_id'])) {
            $valorDistribuido = $d['valor'] / (is_null($d['produto_id']) ? count($produtosLista) : 1);
            if ($d['tipo'] === 'Fixa') {
                $totaisFixos[$id] += $valorDistribuido;
            } else {
                $totaisVariaveis[$id] += $valorDistribuido;
            }
        }
    }
}

// Preparar dados resumidos para tabela resumida
$dadosResumidos = [];
foreach ($produtosLista as $p) {
    $id = $p['id'];
    $custoTotal = $totaisFixos[$id] + $totaisVariaveis[$id] + $totaisProdutos[$id];
    $dadosResumidos[] = [
        'produto' => $p['nome'],
        'custoTotal' => $custoTotal
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Custos - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">

    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Custos / Despesas</h2>
        <a href="dashboard.php" class="btn btn-primary">Voltar ao Painel</a>
    </div>

    <?php if($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <!-- Formulário de adicionar/editar -->
    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="id" value="<?= htmlspecialchars($editarCusto['id'] ?? '') ?>">

        <div class="col-md-3">
            <select name="produto_id" class="form-control" required>
                <option value="">Selecione o produto</option>
                <?php foreach($produtosLista as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (isset($editarCusto['produto_id']) && $editarCusto['produto_id'] == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <input type="text" name="descricao" placeholder="Descrição" class="form-control" value="<?= htmlspecialchars($editarCusto['descricao'] ?? '') ?>" required>
        </div>

        <div class="col-md-2">
            <input type="number" step="0.01" name="valor" placeholder="Valor" class="form-control" value="<?= htmlspecialchars($editarCusto['valor'] ?? '') ?>" required>
        </div>

        <div class="col-md-2">
            <select name="tipo" class="form-control" required>
                <option value="">Selecione o tipo</option>
                <option value="Fixa" <?= (isset($editarCusto['tipo']) && $editarCusto['tipo'] === 'Fixa') ? 'selected' : '' ?>>Fixo</option>
                <option value="Variavel" <?= (isset($editarCusto['tipo']) && $editarCusto['tipo'] === 'Variavel') ? 'selected' : '' ?>>Variável</option>
            </select>
        </div>

        <div class="col-md-2 d-flex justify-content-center">
            <button type="submit" class="btn btn-success w-100"><?= $editarCusto ? 'Atualizar' : 'Adicionar' ?></button>
        </div>
    </form>

    <!-- Gráfico -->
    <div class="bg-white p-3 mb-4 border rounded">
        <canvas id="graficoCustos" height="100"></canvas>
    </div>

    <!-- Tabela resumida por produto -->
    <h4>Resumo de Custos por Produto</h4>
    <table class="table table-bordered bg-white mb-4">
        <thead class="table-light">
            <tr>
                <th>Produto</th>
                <th>Custo Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dadosResumidos as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['produto']) ?></td>
                <td>R$ <?= number_format($d['custoTotal'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Tabela detalhada de despesas -->
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($despesas as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['id']) ?></td>
                <td><?= htmlspecialchars($d['produto_nome'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['descricao'] ?? '') ?></td>
                <td>R$ <?= number_format(floatval($d['valor'] ?? 0), 2, ",", ".") ?></td>
                <td><?= htmlspecialchars($d['tipo'] ?? '') ?></td>
                <td>
                    <a href="?edit=<?= $d['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?delete=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente deletar?')">Deletar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<script>
const ctx = document.getElementById('graficoCustos').getContext('2d');
const labels = <?= json_encode(array_map(fn($p) => $p['nome'], $produtosLista)) ?>;

// Custo fixo permanece igual
const fixosData = <?= json_encode(array_values($totaisFixos)) ?>;

// Custo variável agora inclui o custo do produto (preço_custo * qtd)
const variaveisData = <?= json_encode(array_map(fn($id) => $totaisVariaveis[$id] + $totaisProdutos[$id], array_keys($totaisProdutos))) ?>;

// Custo total = fixo + variável (já inclui o custo do produto)
const produtoData = <?= json_encode(array_map(fn($id) => $totaisFixos[$id] + $totaisVariaveis[$id] + $totaisProdutos[$id], array_keys($totaisProdutos))) ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            { label: 'Custo Fixo', data: fixosData, backgroundColor: 'rgba(255, 99, 132, 0.7)' },
            { label: 'Custo Variável', data: variaveisData, backgroundColor: 'rgba(54, 162, 235, 0.7)' },
            { label: 'Custo Total do Produto', data: produtoData, backgroundColor: 'rgba(75, 192, 192, 0.7)' }
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
