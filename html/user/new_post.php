<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? ''); $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'life'; $cover = trim($_POST['cover'] ?? '');
    if ($title && $content) {
        $stmt = $pdo->prepare('INSERT INTO posts (title, content, category, cover, user_id) VALUES (?,?,?,?,?)');
        $stmt->execute([$title, $content, $category, $cover, current_user()['id']]);
        header('Location: index.php'); exit;
    }
    $error = '标题和内容不能为空';
}
?>
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg"><title>写新文章 - DuoDuo's Daily</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><div class="bg-overlay"></div><nav class="navbar"><div class="nav-inner"><a href="../index.php" class="nav-logo">DuoDuo's Daily</a><ul class="nav-links"><li><a href="index.php">工作台</a></li></ul></div></nav><div class="admin-layout"><div class="admin-card"><span class="eyebrow">NEW STORY</span><h1>写新文章</h1><?php if (!empty($error)): ?><p class="form-error"><?= htmlspecialchars($error) ?></p><?php endif; ?><form method="POST"><div class="form-group"><label>标题</label><input name="title" required></div><div class="form-group"><label>分类</label><select name="category"><option value="life">生活</option><option value="tech">技术</option><option value="music">音乐</option><option value="essay">随笔</option></select></div><div class="form-group"><label>封面图片 URL</label><input name="cover" placeholder="https://..."></div><div class="form-group"><label>内容（支持 Markdown）</label><textarea name="content" class="markdown-editor" required></textarea></div><button class="btn">发布文章</button></form></div></div></body></html>
