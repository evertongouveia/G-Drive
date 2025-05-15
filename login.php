<?php
session_start();
require_once 'config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    error_log("Tentativa de login - Usuário: $username, Senha: $password");

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        error_log("Usuário encontrado: " . $user['username']);
        error_log("Hash armazenado: " . $user['password']);
        error_log("Senha fornecida: $password");
        error_log("Verificação de senha: " . (password_verify($password, $user['password']) ? 'Válida' : 'Inválida'));
    } else {
        error_log("Usuário não encontrado: $username");
    }

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        error_log("Login bem-sucedido: " . $user['username']);
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Usuário ou senha inválidos.";
        error_log("Login falhou para: $username");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SECEX Drive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #007bff, #6610f2);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            background: white;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <center><img class="navbar-brand-item" src="https://www.afagu.com.br/wp-content/uploads/2024/08/logo.svg" width="120px" alt="Afagu - O Melhor Plano Funerário do Brasil Logo"> </center>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Usuário</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>