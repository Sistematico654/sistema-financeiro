<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Recuperar Senha - Sistema Financeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background-color: #f8f9fa; }
    .card-recovery { max-width: 450px; margin: 100px auto; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
</style>
</head>
<body>
<div class="card card-recovery">
    <div class="card-body">
        <h3 class="card-title mb-3 text-center">Recuperar Senha</h3>
        <p class="text-muted text-center mb-4">Digite seu email e enviaremos um link para você redefinir sua senha.</p>
        
        <form action="enviar_recuperacao.php" method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enviar Link de Recuperação</button>
        </form>

        <div class="text-center mt-3">
            <a href="login.php">Voltar para o Login</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>