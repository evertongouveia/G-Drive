<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Estatísticas básicas
$total_files = $pdo->query("SELECT COUNT(*) FROM files WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();
$recent_files = $pdo->prepare("SELECT file_name, token FROM files WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$recent_files->execute([$_SESSION['user_id']]);
$recent_files = $recent_files->fetchAll();
?>
<div class="card border-0">
    <div class="card-header bg-white">
        <h5 class="mb-0">Bem-vindo ao SECEX Drive, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
    </div>
    <div class="card-body">
        <p class="lead">Organize, compartilhe e acesse seus arquivos com facilidade.</p>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-folder me-2 text-primary"></i>Meus Arquivos</h6>
                        <p class="card-text">Você tem <?php echo $total_files; ?> arquivo(s) armazenado(s).</p>
                        <a href="file_manager.php" class="btn btn-primary"><i class="fas fa-folder-open me-1"></i>Acessar Arquivos</a>
                    </div>
                </div>
            </div>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-users me-2 text-success"></i>Gerenciar Usuários</h6>
                            <p class="card-text">Controle os usuários do sistema.</p>
                            <a href="admin_users.php" class="btn btn-outline-primary"><i class="fas fa-user-cog me-1"></i>Gerenciar</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($recent_files)): ?>
            <h6>Arquivos Recentes</h6>
            <div class="row">
                <?php foreach ($recent_files as $file): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card file-card">
                            <div class="card-body">
                                <p class="card-text"><?php echo htmlspecialchars($file['file_name']); ?></p>
                                <a href="file.php?token=<?php echo $file['token']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">Ver</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>