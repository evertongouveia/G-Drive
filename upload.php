<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Ação inválida.");
}

if (isset($_FILES['image'])) {
    $folder_id = isset($_POST['folder_id']) && !empty($_POST['folder_id']) ? (int)$_POST['folder_id'] : null;
    $file = $_FILES['image'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types) || $file['size'] > $max_size) {
        die("Arquivo inválido ou muito grande.");
    }

    if ($folder_id) {
        $stmt = $pdo->prepare("SELECT user_id FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);
        if ($stmt->fetchColumn() !== $_SESSION['user_id']) {
            die("Permissão negada.");
        }
    }

    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_name = uniqid() . '-' . basename($file['name']);
    $file_path = $upload_dir . $file_name;
    $direct_link = BASE_URL . $file_path;

    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $stmt = $pdo->prepare("INSERT INTO images (folder_id, file_path, direct_link) VALUES (?, ?, ?)");
        $stmt->execute([$folder_id, $file_path, $direct_link]);
        header('Location: folder.php?id=' . $folder_id);
        exit;
    } else {
        die("Erro ao fazer upload.");
    }
}

header('Location: dashboard.php');
exit;
?>