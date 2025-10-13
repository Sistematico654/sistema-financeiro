<?php
require_once "funcoes.php";
protegerPagina();
require_once "conexao.php";

// Conexão POO
$conn = Database::getInstance()->getConnection();
$usuario_id = $_SESSION['usuario_id'];

// Classe para cálculo do ponto de equilíbrio
class PontoEquilibrio {
    private $conn;
    private $usuario_id;
    private $produtos = [];
    private $despesas = [];
    private $dadosTabela = [];

    public function __construct($conn, $usuario_id) {
        $this->conn = $conn;
        $this->usuario_id = $usuario_id;
        $this->carregarProdutos();
        $this->carregarDespesas();
        $this->calcularDados();
    }

    private function carregarProdutos() {
        $stmt = $this->conn->prepare("SELECT * FROM Produto ORDER BY nome ASC");
        $stmt->execute();
        $this->produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function carregarDespesas() {
        $stmt = $this->conn->prepare("SELECT * FROM Custo ORDER BY descricao ASC");
        $stmt->execute();
        $this->despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function calcularDados() {
        $totalProdutos = count($this->produtos) > 0 ? count($this->produtos) : 1;
        
        foreach ($this->produtos as $p) {
            $id = $p['id'];
            $nome = $p['nome'];
            $quantidade = intval($p['qtd']);
            $preco_custo = floatval($p['preco_custo']);
            $preco_venda = floatval($p['preco_venda']);

            $custoFixo = 0;
            $custoVariavel = 0;

            foreach ($this->despesas as $d) {
                if ($d['produto_id'] == $id || is_null($d['produto_id'])) {
                    $valorDistribuido = $d['valor'] / (is_null($d['produto_id']) ? $totalProdutos : 1);
                    if ($d['tipo'] === 'Fixa') {
                        $custoFixo += $valorDistribuido;
                    } else {
                        $custoVariavel += $valorDistribuido;
                    }
                }
            }
            
            // Custo variável unitário = (soma dos custos variáveis distribuídos / quantidade) + preço de custo do produto
            $custoVariavelUnitario = ($quantidade > 0 ? ($custoVariavel / $quantidade) : 0) + $preco_custo;
            
            // Margem de Contribuição Unitária = Preço de Venda - Custo Variável Unitário
            $margemContribuicao = $preco_venda - $custoVariavelUnitario;

            // Ponto de Equilíbrio (em unidades) = Custos Fixos Totais / Margem de Contribuição Unitária
            $pontoEquilibrio = $margemContribuicao > 0 ? ceil($custoFixo / $margemContribuicao) : 0;

            $this->dadosTabela[] = [
                'produto' => $nome,
                'quantidade' => $quantidade,
                'pontoEquilibrio' => $pontoEquilibrio
            ];
        }
    }

    public function getDadosTabela() {
        return $this->dadosTabela;
    }
}

// Instancia a classe
$ponto = new PontoEquilibrio($conn, $usuario_id);
$dadosTabela = $ponto->getDadosTabela();

$labels = array_map(fn($d) => $d['produto'], $dadosTabela);
$quantidadeData = array_map(fn($d) => $d['quantidade'], $dadosTabela);
$pontoEquilibrioData = array_map(fn($d) => $d['pontoEquilibrio'], $dadosTabela);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Ponto de Equilíbrio - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Ponto de Equilíbrio por Produto</h2>
        <a href="dashboard.php" class="btn btn-primary">Voltar ao Painel</a>
    </div>

    <!-- Gráfico -->
    <div class="bg-white p-3 mb-4 border rounded">
        <canvas id="graficoPE" height="100"></canvas>
    </div>

    <!-- Tabela simplificada -->
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>Produto</th>
                <th>Quantidade em Estoque</th>
                <th>Ponto de Equilíbrio (unidades a vender)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dadosTabela as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['produto']) ?></td>
                <td><?= $d['quantidade'] ?></td>
                <td><?= $d['pontoEquilibrio'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
const ctx = document.getElementById('graficoPE').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            { label: 'Quantidade em Estoque', data: <?= json_encode($quantidadeData) ?>, backgroundColor: 'rgba(54, 162, 235, 0.7)' },
            { label: 'Ponto de Equilíbrio (Unidades)', data: <?= json_encode($pontoEquilibrioData) ?>, backgroundColor: 'rgba(255, 99, 132, 0.7)' }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>