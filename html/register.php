<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $error = '';
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username) || strlen($password) < 6) {
        $error = '用户名需为 3-30 位字母、数字或下划线，密码至少 6 位';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username=?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = '用户名已存在';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (username, password, display_name) VALUES (?,?,?)');
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $username]);
            $_SESSION['user'] = ['id' => $pdo->lastInsertId(), 'username' => $username, 'display_name' => $username];
            header('Location: user/index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg"><title>注册账户 - DuoDuo's Daily</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body><div class="bg-overlay"></div><nav class="navbar"><div class="nav-inner"><a href="index.php" class="nav-logo">DuoDuo's Daily</a><ul class="nav-links"><li><a href="account.php">返回登录</a></li></ul></div></nav><div class="admin-layout"><div class="admin-card account-card account-single"><h2>创建账户</h2><p class="account-intro">加入 DuoDuo's Daily，开始记录你的故事。</p><?php if (!empty($error)): ?><p class="form-error"><?= htmlspecialchars($error) ?></p><?php endif; ?><form method="POST" class="login-form"><div class="form-group"><label>用户名</label><input name="username" autocomplete="username" required></div><div class="form-group"><label>密码</label><input type="password" name="password" autocomplete="new-password" minlength="6" required></div><button class="btn login-submit">注册并登录</button><div class="account-links"><a href="account.php">已有账户，直接登录</a></div></form></div></div></body></html>
