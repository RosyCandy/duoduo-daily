<?php
session_start();
if (!isset($_SESSION['admin'])) { http_response_code(403); exit; }

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $type = $_POST['type'] ?? 'post';
    $upload_dir = ($type === 'avatar') ? '../assets/images/avatars/' : '../assets/images/posts/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $allowed)) {
        $filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
        $dest = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $rootPath = ($type === 'avatar') ? '/assets/images/avatars/' . $filename : '/assets/images/posts/' . $filename;
            echo json_encode(['url' => $rootPath]);
            exit;
        }
    }
}
echo json_encode(['error' => '上传失败']);
