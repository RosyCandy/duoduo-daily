<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: index.php'); exit; }
require_once '../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("SELECT file_path FROM music WHERE id=?");
    $stmt->execute([$id]);
    $music = $stmt->fetch();
    if ($music && $music['file_path']) {
        $file = '../' . $music['file_path'];
        if (file_exists($file)) unlink($file);
    }
    $stmt = $pdo->prepare("DELETE FROM music WHERE id=?");
    $stmt->execute([$id]);
}
header('Location: index.php');
