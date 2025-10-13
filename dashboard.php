<?php
require_once "conexao.php";

// Classe para proteger páginas que exigem login
class PaginaProtegida {
    public static function proteger() {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: login.php");
            exit;
        }
    }
}

// Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Protege a página
PaginaProtegida::proteger();

// Classe para manipular o usuário logado
class Usuario {
    private $conn;
    private $id;
    private $nome;

    public function __construct($conn, $id) {
        $this->conn = $conn;
        $this->id = $id;
        $this->carregarDados();
    }

    private function carregarDados() {
        $stmt = $this->conn->prepare("SELECT nome FROM Usuario WHERE id = ?");
        $stmt->execute([$this->id]);
        $usuario = $stmt->fetch();
        if ($usuario) {
            $this->nome = $usuario['nome'];
        }
    }

    public function getNome() {
        return $this->nome ?? "Usuário";
    }
}

// Instancia o usuário logado
$conn = Database::getInstance()->getConnection();
$usuario = new Usuario($conn, $_SESSION['usuario_id']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Dashboard - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card-dashboard { transition: transform 0.2s; cursor: pointer; min-height: 220px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; }
    .card-dashboard:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
    .card-icon { font-size: 2.5rem; }
    .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .logout-btn { font-size: 0.9rem; padding: 0.3rem 0.8rem; }
    .card-body p { margin: 0; }
</style>
</head>
<body>
<div class="container mt-4">

    <div class="top-bar">
        <h4>Bem-vindo, <?= htmlspecialchars($usuario->getNome()) ?>!</h4>
        <a href="logout.php" class="btn btn-danger logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>

    <div class="row g-4">

        <div class="col-md-3">
            <a href="produtos.php" class="text-decoration-none text-dark h-100">
                <div class="card card-dashboard shadow-sm bg-primary text-white w-100 h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-box card-icon mb-2"></i>
                        <h5 class="card-title">Produtos</h5>
                        <p class="card-text">Gerencie seus produtos</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="custos.php" class="text-decoration-none text-dark h-100">
                <div class="card card-dashboard shadow-sm bg-warning text-dark w-100 h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-dollar-sign card-icon mb-2"></i>
                        <h5 class="card-title">Custos / Despesas</h5>
                        <p class="card-text">Adicione ou edite custos</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="ponto.php" class="text-decoration-none text-dark h-100">
                <div class="card card-dashboard shadow-sm bg-success text-white w-100 h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-balance-scale card-icon mb-2"></i>
                        <h5 class="card-title">Ponto de Equilíbrio</h5>
                        <p class="card-text">Visualize o equilíbrio de seus produtos</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="relatorio.php" class="text-decoration-none text-dark h-100">
                <div class="card card-dashboard shadow-sm bg-info text-white w-100 h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-chart-bar card-icon mb-2"></i>
                        <h5 class="card-title">Relatório</h5>
                        <p class="card-text">Confira a viabilidade das vendas</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="historico.php" class="text-decoration-none text-dark h-100">
                <div class="card card-dashboard shadow-sm bg-secondary text-white w-100 h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-history card-icon mb-2"></i>
                        <h5 class="card-title">Histórico</h5>
                        <p class="card-text">Veja as ações no sistema</p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>