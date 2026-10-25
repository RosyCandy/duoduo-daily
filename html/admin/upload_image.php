<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function image_upload_error($message, $status = 400) {
    http_response_code($status);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}
if (empty($_SESSION['admin'])) image_upload_error('登录已过期，请重新登录后上传图片。', 403);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') image_upload_error('请通过上传按钮选择图片。', 405);
$file = $_FILES['image'] ?? null;
if (!$file || !is_array($file) || !isset($file['error']) || is_array($file['error'])) {
    image_upload_error('没有收到图片，请选择 10MB 以内的 JPG、PNG、GIF 或 WebP 图片。');
}
if ($file['error'] !== UPLOAD_ERR_OK) {
    $messages = [
        UPLOAD_ERR_INI_SIZE => '图片过大，请选择 10MB 以内的图片。',
        UPLOAD_ERR_FORM_SIZE => '图片过大，请选择 10MB 以内的图片。',
        UPLOAD_ERR_PARTIAL => '图片未上传完整，请重试。',
        UPLOAD_ERR_NO_FILE => '请先选择一张图片。',
    ];
    image_upload_error($messages[$file['error']] ?? '暂时无法接收图片，请稍后重试。');
}
if ($file['size'] > 10 * 1024 * 1024) image_upload_error('图片过大，请选择 10MB 以内的图片。', 413);
$info = @getimagesize($file['tmp_name']);
$extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
if (!$info || !isset($extensions[$info['mime'] ?? ''])) image_upload_error('请选择有效的 JPG、PNG、GIF 或 WebP 图片。');
if ($info[0] * $info[1] > 40000000) image_upload_error('图片分辨率过大，请缩小至 4000 万像素以内。');
$type = ($_POST['type'] ?? '') === 'avatar' ? 'avatars' : 'posts';
$directory = dirname(__DIR__) . '/assets/images/' . $type;
if ((!is_dir($directory) && !@mkdir($directory, 0755, true)) || !is_writable($directory)) {
    image_upload_error('图片暂时无法保存，请联系管理员检查图片存储目录。', 500);
}
$filename = bin2hex(random_bytes(16)) . '.' . $extensions[$info['mime']];
$destination = $directory . '/' . $filename;
if (!move_uploaded_file($file['tmp_name'], $destination)) image_upload_error('图片保存失败，请重试。', 500);
chmod($destination, 0644);
echo json_encode(['url' => '/assets/images/' . $type . '/' . $filename, 'width' => $info[0], 'height' => $info[1]], JSON_UNESCAPED_UNICODE);
