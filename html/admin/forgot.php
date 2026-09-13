<?php
date_default_timezone_set('Asia/Shanghai');
session_start();
require_once '../includes/db.php';

$msg = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // 生成6位验证码
    $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // 存入数据库
    $pdo->exec("DELETE FROM reset_tokens WHERE email='$email'");
    $stmt = $pdo->prepare("INSERT INTO reset_tokens (email, token, expires_at) VALUES (?,?,?)");
    $stmt->execute([$email, $token, $expires]);

    // 发送邮件
    $sent = send_reset_email($email, $token);
    if ($sent) {
        $success = true;
        $_SESSION['reset_email'] = $email;
    } else {
        $msg = '邮件发送失败，请检查配置';
    }
}

function send_reset_email($to, $token) {
    $host = 'smtp.zoho.com';
    $port = 465;
    $username = 'rosyhazes@zohomail.com';
    $password = 'RnLH7hkkRZZL';
    $from_name = "DuoDuo's Daily";

    $subject = '重置密码验证码';
    $body = "您的验证码是：{$token}\n\n验证码15分钟内有效，请勿泄露给他人。";

    // 使用 SSL SMTP
    $errno = $errstr = '';
    $socket = fsockopen("ssl://{$host}", $port, $errno, $errstr, 10);
    if (!$socket) return false;

    $read = fgets($socket, 512);
    fputs($socket, "EHLO localhost\r\n");
    while ($line = fgets($socket, 512)) { if (substr($line, 3, 1) == ' ') break; }
    fputs($socket, "AUTH LOGIN\r\n"); fgets($socket, 512);
    fputs($socket, base64_encode($username)."\r\n"); fgets($socket, 512);
    fputs($socket, base64_encode($password)."\r\n"); fgets($socket, 512);
    fputs($socket, "MAIL FROM: <{$username}>\r\n"); fgets($socket, 512);
    fputs($socket, "RCPT TO: <{$to}>\r\n"); fgets($socket, 512);
    fputs($socket, "DATA\r\n"); fgets($socket, 512);

    $headers = "From: {$from_name} <{$username}>\r\n";
    $headers .= "To: {$to}\r\n";
    $headers .= "Subject: =?UTF-8?B?".base64_encode($subject)."?=\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    fputs($socket, $headers."\r\n".$body."\r\n.\r\n");
    $res = fgets($socket, 512);
    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return strpos($res, '250') !== false;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg">
    <title>忘记密码 - DuoDuo's Daily</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="bg-overlay"></div>
<nav class="navbar"><div class="nav-inner"><a href="../index.php" class="nav-logo">DuoDuo's Daily</a></div></nav>
<div class="admin-layout">
    <div class="admin-card" style="max-width:400px;margin:0 auto">
        <h2>忘记密码</h2>
        <?php if($success): ?>
            <p style="color:#3ab4c8;margin-bottom:16px">验证码已发送到你的邮箱，15分钟内有效！</p>
            <a href="reset.php" class="btn">输入验证码</a>
        <?php else: ?>
            <?php if($msg): ?><p style="color:#c0392b;margin-bottom:16px"><?= $msg ?></p><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>邮箱地址</label>
                    <input type="email" name="email" placeholder="输入你的邮箱" required>
                </div>
                <button type="submit" class="btn">发送验证码</button>
            </form>
            <p style="margin-top:12px;font-size:0.85rem"><a href="index.php" style="color:var(--accent)">← 返回登录</a></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
