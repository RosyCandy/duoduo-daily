<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/site.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $token = preg_replace('/\s+/', '', $_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($password !== $confirm) {
        $msg = '两次密码不一致';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM reset_tokens WHERE email=? AND token=? AND used=0 AND expires_at > NOW()");
        $stmt->execute([$email, $token]);
        $row = $stmt->fetch();

        if ($row) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $admin = $pdo->query("SELECT id FROM admin_users LIMIT 1")->fetch();
            $pdo->prepare("UPDATE admin_users SET password=? WHERE id=?")->execute([$hash, $admin['id']]);
            $pdo->prepare("UPDATE reset_tokens SET used=1 WHERE id=?")->execute([$row['id']]);
            $msg = 'success';
        } else {
            $msg = '验证码错误或已过期';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head><?php blog_head("重置密码 · DuoDuo's Daily"); ?>
</head>
<body class="blog-app auth-view" id="top">

<?php blog_workspace_nav(); ?>
<main class="admin-layout" id="journal">
    <div class="admin-card account-card">
        <?php if($msg === 'success'): ?>
            <h2>✅ 密码重置成功！</h2>
            <a href="../account.php" class="btn" style="margin-top:16px;display:inline-block">去登录</a>
        <?php else: ?>
            <h2>重置密码</h2>
            <?php if($msg): ?><p style="color:#c0392b;margin-bottom:16px"><?= $msg ?></p><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="field-email">邮箱地址</label><input id="field-email" type="email" name="email" value="<?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="field-token">验证码</label><input id="field-token" type="text" name="token" placeholder="6位验证码" maxlength="6" required>
                </div>
                <div class="form-group">
                    <label for="field-password">新密码</label><input id="field-password" type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="field-confirm">确认密码</label><input id="field-confirm" type="password" name="confirm" required>
                </div>
                <button type="submit" class="btn">重置密码</button>
            </form>
            <p style="margin-top:12px;font-size:0.85rem"><a href="../account.php" style="color:var(--accent)">← 返回登录</a></p>
        <?php endif; ?>
    </div>
</main>
<?php blog_footer(); ?>
</body>
</html>
