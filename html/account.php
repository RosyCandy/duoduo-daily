<?php
session_start();
require_once 'includes/db.php';

if (isset($_GET['logout'])) {
    unset($_SESSION['user'], $_SESSION['admin']);
    header('Location: account.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $error = '';
    if (!$username || strlen($password) < 6) {
        $error = '请输入用户名和至少 6 位密码';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password FROM admin_users WHERE username=?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            unset($_SESSION['user']);
            $_SESSION['admin'] = true;
            header('Location: admin/index.php');
            exit;
        }
        $stmt = $pdo->prepare('SELECT id, username, display_name, password FROM users WHERE username=?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            unset($user['password'], $_SESSION['admin']);
            $_SESSION['user'] = $user;
            header('Location: user/index.php');
            exit;
        }
        $error = '用户名或密码错误';
    }
}
$logged_in = !empty($_SESSION['user']) || !empty($_SESSION['admin']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg"><title>账户中心 - DuoDuo's Daily</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body><div class="bg-overlay"></div>
<nav class="navbar"><div class="nav-inner"><a href="index.php" class="nav-logo">DuoDuo's Daily</a><ul class="nav-links"><li><a href="index.php">返回博客</a></li></ul></div></nav>
<div class="admin-layout">
<div class="admin-card account-card">
<?php if ($logged_in): ?>
<h2>欢迎回来</h2><p>你的账户已登录。</p><a class="btn" href="<?= !empty($_SESSION['admin']) ? 'admin/index.php' : 'user/index.php' ?>">进入工作台</a> <a class="text-link" href="account.php?logout=1">退出登录</a>
<?php else: ?>
<h2>登录账户</h2>
<?php if (!empty($error)): ?><p class="form-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="POST" class="login-form"><div class="form-group"><label>用户名</label><input name="username" autocomplete="username" required></div><div class="form-group"><label>密码</label><input type="password" name="password" autocomplete="current-password" required></div><button class="btn login-submit">登录</button><div class="account-links"><a href="admin/forgot.php">忘记密码</a><span>·</span><a href="register.php">注册账户</a></div></form>
<?php endif; ?></div></div>
<script src="assets/js/main.js"></script></body></html>
