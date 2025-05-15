<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Ação inválida: Token CSRF inválido.");
}

$action = $_POST['action'] ?? '';
$redirect = 'dashboard.php';

try {
    if ($action === 'create') {
        $folder_name = trim($_POST['folder_name']);
        $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        if (empty($folder_name)) {
            die("Nome da pasta é obrigatório.");
        }

        if ($parent_id) {
            $stmt = $pdo->prepare("SELECT user_id FROM folders WHERE id = ?");
            $stmt->execute([$parent_id]);
            if ($stmt->fetchColumn() !== $user_id) {
                die("Permissão negada.");
            }
        }

        $stmt = $pdo->prepare("INSERT INTO folders (name, parent_id, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$folder_name, $parent_id, $user_id]);
        $redirect = $parent_id ? "folder.php?id=$parent_id" : 'dashboard.php';
    }

    elseif ($action === 'rename') {
        $folder_id = (int)$_POST['folder_id'];
        $folder_name = trim($_POST['folder_name']);

        if (empty($folder_name)) {
            die("Novo nome da pasta é obrigatório.");
        }

        $stmt = $pdo->prepare("SELECT user_id FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);
        if ($stmt->fetchColumn() !== $user_id) {
            die("Permissão negada.");
        }

        $stmt = $pdo->prepare("UPDATE folders SET name = ? WHERE id = ?");
        $stmt->execute([$folder_name, $folder_id]);
        $redirect = "dashboard.php";
    }

    elseif ($action === 'delete') {
        $folder_id = (int)$_POST['folder_id'];

        $stmt = $pdo->prepare("SELECT user_id FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);
        if ($stmt->fetchColumn() !== $user_id) {
            die("Permissão negada.");
        }

        // Delete folder and its contents (cascading handled by foreign keys)
        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);

        // Delete associated image files
        $stmt = $pdo->prepare("SELECT file_path FROM images WHERE folder_id = ?");
        $stmt->execute([$folder_id]);
        $files = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
} catch (PDOException $e) {
    die("Erro na ação: " . $e->getMessage());
}

header("Location: $redirect");
exit;
?>