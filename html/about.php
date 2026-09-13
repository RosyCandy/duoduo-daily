<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg">
    <title>关于 - DuoDuo's Daily</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="particles" id="particles"></div>

    <nav class="navbar">
        <div class="nav-inner">
            <a href="index.php" class="nav-logo">DuoDuo's Daily</a>
            <ul class="nav-links">
                <li><a href="index.php">首页</a></li>
                <li><a href="index.php?cat=life">生活</a></li>
                <li><a href="index.php?cat=essay">随笔</a></li>
                <li><a href="index.php?cat=tech">技术</a></li>
                <li><a href="music.php">音乐</a></li>
                <li><a href="about.php">关于</a></li>
                <li><a href="account.php">账户</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-layout" style="grid-template-columns:1fr;max-width:800px">
        <div class="post-detail">
            <h1>关于我</h1>
            <p>你好，我是 DuoDuo，这里是我记录日常生活、技术学习和音乐感悟的地方。</p>
            <p>喜欢音乐、喜欢折腾代码，偶尔写写生活感悟。</p>
            <p>欢迎来到我的小角落 🌿</p>
        </div>
    </div>

    <footer class="footer">
        <p>© <?= date('Y') ?> DuoDuo's Daily · 用心记录每一天</p>
    </footer>
    <script src="assets/js/main.js"></script>
</body>
</html>