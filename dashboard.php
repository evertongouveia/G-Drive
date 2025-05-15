<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Estatísticas
$total_files = $pdo->query("SELECT COUNT(*) FROM files WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();
$total_users = $_SESSION['role'] === 'admin' ? $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() : 0;
$used_space = $pdo->query("SELECT SUM(LENGTH(file_path)) / 1024 / 1024 AS size_mb FROM files WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();
?>
<div class="card">
    <div class="card-header">
        <h5>Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total de Arquivos</h5>
                        <p class="card-text"><?php echo $total_files; ?></p>
                    </div>
                </div>
            </div>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Usuários Ativos</h5>
                            <p class="card-text"><?php echo $total_users; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Espaço Usado</h5>
                        <p class="card-text"><?php echo number_format($used_space, 2); ?> MB</p>
                    </div>
                </div>
            </div>
        </div>
        <a href="file_manager.php" class="btn btn-primary">Gerenciar Arquivos</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin_users.php" class="btn btn-secondary">Gerenciar Usuários</a>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>