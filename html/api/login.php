<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => 405, 'message' => '方法不允许']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(['code' => 400, 'message' => '用户名和密码不能为空']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username=?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // 生成 token
    $token = bin2hex(random_bytes(32));
    // 存到数据库（需要建表，见下方）
    $exp = time() + 86400 * 7; // 7天有效
    $stmt = $pdo->prepare("INSERT INTO admin_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $token, date('Y-m-d H:i:s', $exp)]);

    echo json_encode([
        'code' => 200,
        'token' => $token,
        'user' => ['username' => $user['username']]
    ]);
} else {
    echo json_encode(['code' => 401, 'message' => '用户名或密码错误']);
}