<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

$folder_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$folder = null;
$subfolders = [];
$images = [];

if ($folder_id) {
    $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ? AND user_id = ?");
    $stmt->execute([$folder_id, $user_id]);
    $folder = $stmt->fetch();

    if (!$folder) {
        header('Location: dashboard.php');
        exit;
    }

    $subfolders = $pdo->prepare("SELECT * FROM folders WHERE parent_id = ?");
    $subfolders->execute([$folder_id]);
    $subfolders = $subfolders->fetchAll();

    $images = $pdo->prepare("SELECT * FROM images WHERE folder_id = ?");
    $images->execute([$folder_id]);
    $images = $images->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasta - SECEX SUPAD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 150px;
            object-fit: cover;
        }
        .action-btn {
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">SECEX SUPAD</a>
            <div class="navbar-nav">
                <a class="nav-link" href="dashboard.php">Drive</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a class="nav-link" href="admin.php">Admin</a>
                <?php endif; ?>
                <a class="nav-link" href="logout.php">Sair</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2>Pasta: <?php echo $folder ? htmlspecialchars($folder['name']) : 'Raiz'; ?></h2>
        <?php if ($folder): ?>
            <div class="mb-3">
                <button class="btn btn-outline-primary action-btn" data-bs-toggle="modal" data-bs-target="#renameModal">Renomear</button>
                <button class="btn btn-outline-danger action-btn" data-bs-toggle="modal" data-bs-target="#deleteModal">Excluir</button>
            </div>
        <?php endif; ?>
        <form method="POST" action="folder_actions.php" class="mb-4">
            <input type="hidden" name="parent_id" value="<?php echo $folder_id; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="input-group">
                <input type="text" class="form-control" name="folder_name" placeholder="Nome da subpasta" required>
                <button type="submit" name="action" value="create" class="btn btn-primary">Criar Subpasta</button>
            </div>
        </form>
        <h4>Subpastas</h4>
        <div class="row">
            <?php foreach ($subfolders as $subfolder): ?>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($subfolder['name']); ?></h5>
                            <a href="folder.php?id=<?php echo $subfolder['id']; ?>" class="btn btn-outline-primary">Abrir</a>
                            <button class="btn btn-outline-primary btn-sm action-btn" data-bs-toggle="modal" data-bs-target="#renameModal" data-folder-id="<?php echo $subfolder['id']; ?>" data-folder-name="<?php echo htmlspecialchars($subfolder['name']); ?>">Renomear</button>
                            <button class="btn btn-outline-danger btn-sm action-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-folder-id="<?php echo $subfolder['id']; ?>" data-folder-name="<?php echo htmlspecialchars($subfolder['name']); ?>">Excluir</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <h4>Imagens</h4>
        <div class="row">
            <?php foreach ($images as $image): ?>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($image['file_path']); ?>" class="card-img-top" alt="Imagem">
                        <div class="card-body">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($image['direct_link']); ?>" readonly>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <h4>Upload de Imagem</h4>
        <form method="POST" action="upload.php" enctype="multipart/form-data">
            <input type="hidden" name="folder_id" value="<?php echo $folder_id; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="mb-3">
                <input type="file" class="form-control" name="image" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Enviar Imagem</button>
        </form>
    </div>

    <!-- Rename Modal -->
    <div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="renameModalLabel">Renomear Pasta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="folder_actions.php">
                    <div class="modal-body">
                        <input type="hidden" name="folder_id" id="rename_folder_id">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="mb-3">
                            <label for="folder_name" class="form-label">Novo Nome</label>
                            <input type="text" class="form-control" name="folder_name" id="rename_folder_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="action" value="rename" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Excluir Pasta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="folder_actions.php">
                    <div class="modal-body">
                        <input type="hidden" name="folder_id" id="delete_folder_id">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <p>Tem certeza que deseja excluir a pasta "<span id="delete_folder_name"></span>"? Isso excluirá todas as subpastas e imagens dentro dela.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="action" value="delete" class="btn btn-danger">Excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const renameModal = document.getElementById('renameModal');
        renameModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const folderId = button.getAttribute('data-folder-id') || '<?php echo $folder_id; ?>';
            const folderName = button.getAttribute('data-folder-name') || '<?php echo $folder ? htmlspecialchars($folder['name']) : ''; ?>';
            document.getElementById('rename_folder_id').value = folderId;
            document.getElementById('rename_folder_name').value = folderName;
        });

        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const folderId = button.getAttribute('data-folder-id') || '<?php echo $folder_id; ?>';
            const folderName = button.getAttribute('data-folder-name') || '<?php echo $folder ? htmlspecialchars($folder['name']) : ''; ?>';
            document.getElementById('delete_folder_id').value = folderId;
            document.getElementById('delete_folder_name').textContent = folderName;
        });
    </script>
</body>
</html>