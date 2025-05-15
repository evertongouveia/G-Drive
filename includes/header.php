<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SECEX Drive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar bg-light border-end" id="sidebar">
            <div class="p-3">
                <h4 class="text-primary mb-4"><img class="navbar-brand-item" src="https://www.afagu.com.br/wp-content/uploads/2024/08/logo.svg" width="40px"  alt="Afagu - O Melhor Plano Funerário do Brasil Logo"> SECEX Drive</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="file_manager.php"><i class="fas fa-folder me-2"></i>Meus Arquivos</a>
                    </li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_users.php"><i class="fas fa-users me-2"></i>Usuários</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="content flex-grow-1">
            <!-- Barra Superior -->
            <nav class="navbar navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-light d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <form class="d-flex w-50">
                        <input class="form-control me-2" type="search" placeholder="Pesquisar..." aria-label="Search" disabled>
                    </form>
                    <span class="navbar-text">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                </div>
            </nav>
            <div class="container-fluid mt-4">