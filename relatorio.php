<?php
require_once "funcoes.php";
protegerPagina();

require_once "conexao.php";
$conn = Database::getInstance()->getConnection();
$usuario_id = $_SESSION['usuario_id'];

// Classe para gerar relatório simplificado
class RelatorioSimples {
    private $conn;
    private $usuario_id;
    private $produtos = [];
    private $despesas = [];
    private $dados = [];

    public function __construct($conn, $usuario_id) {
        $this->conn = $conn;
        $this->usuario_id = $usuario_id;
        $this->carregarProdutos();
        $this->carregarDespesas();
        $this->gerarDados();
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

    private function gerarDados() {
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

            $receitaTotal = $preco_venda * $quantidade;
            $custoTotal = $custoFixo + $custoVariavel + ($preco_custo * $quantidade);
            $lucro = $receitaTotal - $custoTotal;
            $viabilidade = $lucro > 0 ? "Lucro" : ($lucro < 0 ? "Prejuízo" : "Equilíbrio");

            $this->dados[] = [
                'produto' => $nome,
                'receitaTotal' => $receitaTotal,
                'custoTotal' => $custoTotal,
                'lucro' => $lucro,
                'viabilidade' => $viabilidade
            ];
        }
    }

    public function getDados() {
        return $this->dados;
    }
}

$relatorio = new RelatorioSimples($conn, $usuario_id);
$dadosGrafico = $relatorio->getDados();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Relatório de Viabilidade - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Relatório de Viabilidade por Produto</h2>
        <div>
            <button id="exportar-pdf-btn" class="btn btn-success">
                <i class="fas fa-file-pdf"></i> Exportar para PDF
            </button>
            <a href="dashboard.php" class="btn btn-primary">Voltar ao Painel</a>
        </div>
    </div>

    <div class="bg-white p-3 mb-4 border rounded">
        <canvas id="grafico" height="100"></canvas>
    </div>

    <table id="tabela-relatorio" class="table table-bordered bg-white mt-4">
        <thead class="table-light">
            <tr>
                <th>Produto</th>
                <th>Receita Total</th>
                <th>Custo Total</th>
                <th>Resultado (Lucro/Prejuízo)</th>
                <th>Viabilidade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dadosGrafico as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['produto']) ?></td>
                <td>R$ <?= number_format($d['receitaTotal'], 2, ",", ".") ?></td>
                <td>R$ <?= number_format($d['custoTotal'], 2, ",", ".") ?></td>
                <td>R$ <?= number_format($d['lucro'], 2, ",", ".") ?></td>
                <td><?= htmlspecialchars($d['viabilidade']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // --- LÓGICA DO GRÁFICO ---
    const ctx = document.getElementById('grafico').getContext('2d');
    const labels = <?= json_encode(array_column($dadosGrafico, 'produto')) ?>;
    const receitaData = <?= json_encode(array_column($dadosGrafico, 'receitaTotal')) ?>;
    const custoData = <?= json_encode(array_column($dadosGrafico, 'custoTotal')) ?>;

    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Receita Total', data: receitaData, backgroundColor: 'rgba(54, 162, 235, 0.7)' },
                { label: 'Custo Total', data: custoData, backgroundColor: 'rgba(255, 99, 132, 0.7)' }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // --- LÓGICA DO BOTÃO EXPORTAR PDF ---
    const exportarBtn = document.getElementById('exportar-pdf-btn');

    exportarBtn.addEventListener('click', function () {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');

        let yPos = 20;

        doc.text("Relatório de Viabilidade", 14, yPos);
        yPos += 5;
        doc.setFontSize(10);
        doc.text("Gerado em: " + new Date().toLocaleString('pt-BR'), 14, yPos);
        yPos += 15;

        // Adiciona o Gráfico
        const canvas = document.getElementById('grafico');
        const imgData = canvas.toDataURL('image/png');
        const imgWidth = 180;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        doc.addImage(imgData, 'PNG', 14, yPos, imgWidth, imgHeight);
        yPos += imgHeight + 15;

        // Adiciona a Tabela
        doc.setFontSize(12);
        doc.text("Detalhes por Produto", 14, yPos);
        yPos += 5;
        
        
        doc.autoTable({
            html: '#tabela-relatorio',
            startY: yPos,
            headStyles: { fillColor: [40, 40, 40] },
        });

        doc.save('relatorio_viabilidade_<?= date("Y-m-d") ?>.pdf');
    });

});
</script>

</body>
</html>