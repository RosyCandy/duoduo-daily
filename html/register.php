<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/site.php';

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
<!DOCTYPE html><html lang="zh-CN"><head><?php blog_head("注册账户 · DuoDuo's Daily"); ?>
</head>
<body class="blog-app auth-view" id="top"><?php blog_workspace_nav(); ?><main class="admin-layout" id="journal"><div class="admin-card account-card account-single"><h2>创建账户</h2><p class="account-intro">加入 DuoDuo's Daily，开始记录你的故事。</p><?php if (!empty($error)): ?><p class="form-error"><?= htmlspecialchars($error) ?></p><?php endif; ?><form method="POST" class="login-form"><div class="form-group"><label for="field-username">用户名</label><input id="field-username" name="username" autocomplete="username" required></div><div class="form-group"><label for="field-password">密码</label><input id="field-password" type="password" name="password" autocomplete="new-password" minlength="6" required></div><button class="btn login-submit">注册并登录</button><div class="account-links"><a href="account.php">已有账户，直接登录</a></div></form></div></main><?php blog_footer(); ?>
</body></html>
