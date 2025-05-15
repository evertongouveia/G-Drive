<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Criar usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $role]);
    echo "<script>Swal.fire('Sucesso', 'Usuário cadastrado com sucesso!', 'success');</script>";
}

// Editar usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    if ($password) {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
        $stmt->execute([$username, $password, $role, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $stmt->execute([$username, $role, $user_id]);
    }
    echo "<script>Swal.fire('Sucesso', 'Usuário atualizado com sucesso!', 'success');</script>";
}

// Excluir usuário
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    if ($user_id !== $_SESSION['user_id']) { // Evitar exclusão do próprio usuário
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        echo "<script>Swal.fire('Sucesso', 'Usuário excluído com sucesso!', 'success');</script>";
    } else {
        echo "<script>Swal.fire('Erro', 'Você não pode excluir seu próprio usuário!', 'error');</script>";
    }
}

$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Gerenciar Usuários</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Adicionar Usuário</button>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Função</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">Editar</button>
                            <a href="admin_users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                        </td>
                    </tr>
                    <!-- Modal Editar Usuário -->
                    <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Editar Usuário</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <div class="mb-3">
                                            <label for="username_<?php echo $user['id']; ?>" class="form-label">Usuário</label>
                                            <input type="text" class="form-control" id="username_<?php echo $user['id']; ?>" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password_<?php echo $user['id']; ?>" class="form-label">Nova Senha (opcional)</label>
                                            <input type="password" class="form-control" id="password_<?php echo $user['id']; ?>" name="password">
                                        </div>
                                        <div class="mb-3">
                                            <label for="role_<?php echo $user['id']; ?>" class="form-label">Função</label>
                                            <select class="form-control" id="role_<?php echo $user['id']; ?>" name="role">
                                                <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Usuário</option>
                                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="edit_user" class="btn btn-primary">Salvar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Adicionar Usuário -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Função</label>
                        <select class="form-control" id="role" name="role">
                            <option value="user">Usuário</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <button type="submit" name="create_user" class="btn btn-primary">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>