<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: index.php'); exit; }
require_once '../includes/db.php';

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$category = $_POST['category'] ?? 'life';
$cover = trim($_POST['cover'] ?? '');

if ($cover !== '' && !preg_match('#^(https?://|/)#i', $cover)) {
    $cover = '/' . ltrim($cover, '/');
}

if ($title && $content) {
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, category, cover) VALUES (?,?,?,?)");
    $stmt->execute([$title, $content, $category, $cover]);
}
header('Location: index.php');
