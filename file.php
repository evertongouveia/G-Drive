<?php
require_once 'config/db_connect.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    header('HTTP/1.1 400 Bad Request');
    exit('Token inválido.');
}

$stmt = $pdo->prepare("SELECT file_path FROM files WHERE token = ?");
$stmt->execute([$token]);
$file = $stmt->fetch();

if ($file && file_exists($file['file_path'])) {
    // Registrar acesso (opcional, para logs)
    $stmt = $pdo->prepare("INSERT INTO access_logs (file_id, action) VALUES ((SELECT id FROM files WHERE token = ?), 'view')");
    $stmt->execute([$token]);

    header('Content-Type: image/jpeg'); // Ajustar conforme o tipo de arquivo
    readfile($file['file_path']);
    exit;
} else {
    header('HTTP/1.1 404 Not Found');
    exit('Arquivo não encontrado.');
}
?>