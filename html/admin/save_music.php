<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: index.php'); exit; }
require_once '../includes/db.php';

$name = trim($_POST['name'] ?? '');
$artist = trim($_POST['artist'] ?? '');

if (!$name) { header('Location: index.php'); exit; }

$file_path = '';

if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === 0) {
    $upload_dir = '../assets/music/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    
    $ext = strtolower(pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION));
    $allowed = ['mp3', 'flac', 'ogg', 'wav'];
    
    if (in_array($ext, $allowed)) {
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '', $_FILES['audio_file']['name']);
        $dest = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $dest)) {
            $file_path = 'assets/music/' . $filename;
        }
    }
}

if ($name && $file_path) {
    $stmt = $pdo->prepare("INSERT INTO music (name, artist, file_path) VALUES (?,?,?)");
    $stmt->execute([$name, $artist, $file_path]);
}

header('Location: index.php');
