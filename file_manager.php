<?php
session_start();
require_once 'config/db_connect.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Criar nova pasta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_folder'])) {
    try {
        $folder_name = trim($_POST['folder_name']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
        if (empty($folder_name)) {
            throw new Exception('Nome da pasta é obrigatório.');
        }
        $stmt = $pdo->prepare("INSERT INTO folders (name, parent_id, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$folder_name, $parent_id, $_SESSION['user_id']]);
        echo "<script>Swal.fire('Sucesso', 'Pasta criada com sucesso!', 'success').then(() => { window.location.href = 'file_manager.php?folder=$parent_id'; });</script>";
    } catch (Exception $e) {
        echo "<script>Swal.fire('Erro', '" . htmlspecialchars($e->getMessage()) . "', 'error');</script>";
    }
}

// Renomear pasta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_folder'])) {
    try {
        $folder_id = (int)$_POST['folder_id'];
        $new_name = trim($_POST['new_folder_name']);
        if (empty($new_name)) {
            throw new Exception('Novo nome da pasta é obrigatório.');
        }
        $stmt = $pdo->prepare("UPDATE folders SET name = ? WHERE id = ? AND user_id = ?");
        $rows = $stmt->execute([$new_name, $folder_id, $_SESSION['user_id']]);
        if ($rows === 0) {
            throw new Exception('Pasta não encontrada ou sem permissão.');
        }
        echo "<script>Swal.fire('Sucesso', 'Pasta renomeada com sucesso!', 'success').then(() => { window.location.href = 'file_manager.php?folder=" . (int)$_GET['folder'] . "'; });</script>";
    } catch (Exception $e) {
        echo "<script>Swal.fire('Erro', '" . htmlspecialchars($e->getMessage()) . "', 'error');</script>";
    }
}

// Deletar pasta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_folder'])) {
    try {
        $folder_id = (int)$_POST['folder_id'];
        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ? AND user_id = ?");
        $rows = $stmt->execute([$folder_id, $_SESSION['user_id']]);
        if ($rows === 0) {
            throw new Exception('Pasta não encontrada ou sem permissão.');
        }
        echo "<script>Swal.fire('Sucesso', 'Pasta excluída com sucesso!', 'success').then(() => { window.location.href = 'file_manager.php?folder=" . (int)$_GET['folder'] . "'; });</script>";
    } catch (Exception $e) {
        echo "<script>Swal.fire('Erro', '" . htmlspecialchars($e->getMessage()) . "', 'error');</script>";
    }
}

// Upload de arquivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $upload_dir = 'assets/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = uniqid() . '_' . basename($_FILES['file']['name']);
        $target_path = $upload_dir . $file_name;
        $token = md5(uniqid());
        $folder_id = !empty($_POST['folder_id']) ? (int)$_POST['folder_id'] : 0;

        if (!in_array($_FILES['file']['type'], ['image/jpeg', 'image/png', 'image/gif']) || $_FILES['file']['size'] > 5 * 1024 * 1024) {
            throw new Exception('Arquivo inválido ou muito grande (máx. 5MB).');
        }

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
            $stmt = $pdo->prepare("INSERT INTO files (user_id, file_name, file_path, token, folder_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $_FILES['file']['name'], $target_path, $token, $folder_id]);
            echo "<script>Swal.fire('Sucesso', 'Arquivo enviado com sucesso!', 'success').then(() => { window.location.href = 'file_manager.php?folder=$folder_id'; });</script>";
        } else {
            throw new Exception('Falha ao enviar o arquivo.');
        }
    } catch (Exception $e) {
        echo "<script>Swal.fire('Erro', '" . htmlspecialchars($e->getMessage()) . "', 'error');</script>";
    }
}

// Obter pastas e arquivos
$current_folder = isset($_GET['folder']) ? (int)$_GET['folder'] : 0;
$folders = $pdo->prepare("SELECT * FROM folders WHERE parent_id = ? AND user_id = ?");
$folders->execute([$current_folder, $_SESSION['user_id']]);
$folders = $folders->fetchAll(PDO::FETCH_ASSOC);

$files = $pdo->prepare("SELECT id, file_name, file_path, token FROM files WHERE folder_id = ? AND user_id = ?");
$files->execute([$current_folder, $_SESSION['user_id']]);
$files = $files->fetchAll(PDO::FETCH_ASSOC);

// Breadcrumb
$breadcrumb = [];
if ($current_folder != 0) {
    $folder = $pdo->prepare("SELECT id, name, parent_id FROM folders WHERE id = ? AND user_id = ?");
    $folder->execute([$current_folder, $_SESSION['user_id']]);
    $folder = $folder->fetch(PDO::FETCH_ASSOC);
    if ($folder) {
        $breadcrumb[] = $folder;
        while ($folder['parent_id'] != 0) {
            $folder = $pdo->prepare("SELECT id, name, parent_id FROM folders WHERE id = ? AND user_id = ?");
            $folder->execute([$folder['parent_id'], $_SESSION['user_id']]);
            $folder = $folder->fetch(PDO::FETCH_ASSOC);
            if ($folder) {
                $breadcrumb[] = $folder;
            } else {
                break;
            }
        }
    }
}
$breadcrumb = array_reverse($breadcrumb);
?>
<?php require_once 'includes/header.php'; ?>
<div class="card border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Meus Arquivos</h5>
        <div>
            <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#createFolderModal"><i class="fas fa-folder-plus me-1"></i>Nova Pasta</button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="fas fa-upload me-1"></i>Enviar Arquivo</button>
        </div>
    </div>
    <div class="card-body">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-light p-2 rounded">
                <li class="breadcrumb-item"><a href="file_manager.php">Raiz</a></li>
                <?php foreach ($breadcrumb as $crumb): ?>
                    <li class="breadcrumb-item"><a href="file_manager.php?folder=<?php echo $crumb['id']; ?>"><?php echo htmlspecialchars($crumb['name']); ?></a></li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <!-- Pastas -->
        <?php if (!empty($folders)): ?>
            <h6 class="mt-4">Pastas</h6>
            <div class="row mb-4">
                <?php foreach ($folders as $folder): ?>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card folder-card position-relative">
                            <div class="card-body text-center">
                                <a href="file_manager.php?folder=<?php echo $folder['id']; ?>" class="text-decoration-none">
                                    <i class="fas fa-folder fa-3x text-primary"></i>
                                    <p class="mt-2 mb-0"><?php echo htmlspecialchars($folder['name']); ?></p>
                                </a>
                                <div class="position-absolute top-0 end-0 p-2">
                                    <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#renameFolderModal<?php echo $folder['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $folder['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Renomear Pasta -->
                        <div class="modal fade" id="renameFolderModal<?php echo $folder['id']; ?>" tabindex="-1" aria-labelledby="renameFolderLabel<?php echo $folder['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="renameFolderLabel<?php echo $folder['id']; ?>"><i class="fas fa-edit me-2"></i>Renomear Pasta</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="">
                                            <div class="mb-3">
                                                <label for="new_folder_name_<?php echo $folder['id']; ?>" class="form-label">Novo Nome</label>
                                                <input type="text" class="form-control" id="new_folder_name_<?php echo $folder['id']; ?>" name="new_folder_name" value="<?php echo htmlspecialchars($folder['name']); ?>" required>
                                                <input type="hidden" name="folder_id" value="<?php echo $folder['id']; ?>">
                                                <input type="hidden" name="rename_folder" value="1">
                                            </div>
                                            <button type="submit" class="btn btn-primary">Renomear</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <!-- Arquivos -->
        <?php if (!empty($files)): ?>
            <h6>Arquivos</h6>
            <div class="row">
                <?php foreach ($files as $file): ?>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card file-card">
                            <img src="<?php echo htmlspecialchars($file['file_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($file['file_name']); ?>">
                            <div class="card-body text-center">
                                <p class="card-text"><?php echo htmlspecialchars($file['file_name']); ?></p>
                                <a href="file.php?token=<?php echo htmlspecialchars($file['token']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">Link Direto</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Criar Pasta -->
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createFolderLabel"><i class="fas fa-folder-plus me-2"></i>Criar Nova Pasta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="folder_name" class="form-label">Nome da Pasta</label>
                        <input type="text" class="form-control" id="folder_name" name="folder_name" required>
                        <input type="hidden" name="parent_id" value="<?php echo $current_folder; ?>">
                        <input type="hidden" name="create_folder" value="1">
                    </div>
                    <button type="submit" class="btn btn-primary">Criar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Arquivo -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel"><i class="fas fa-upload me-2"></i>Enviar Arquivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" action="">
                    <div class="mb-3">
                        <label for="file" class="form-label">Selecione o arquivo</label>
                        <input type="file" class="form-control" id="file" name="file" accept="image/*" required>
                        <input type="hidden" name="folder_id" value="<?php echo $current_folder; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(folderId) {
    Swal.fire({
        title: 'Tem certeza?',
        text: 'Esta ação excluirá a pasta e todo o seu conteúdo!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            form.innerHTML = `
                <input type="hidden" name="delete_folder" value="1">
                <input type="hidden" name="folder_id" value="${folderId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>