<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_user();
$id = (int)($_GET['id'] ?? 0); $user = current_user();
$stmt = $pdo->prepare('SELECT * FROM posts WHERE id=? AND user_id=?'); $stmt->execute([$id, $user['id']]); $post = $stmt->fetch();
if (!$post) { http_response_code(404); exit('文章不存在或无权访问'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? ''); $content = trim($_POST['content'] ?? ''); $category = $_POST['category'] ?? 'life'; $cover = trim($_POST['cover'] ?? '');
    if ($title && $content) { $stmt = $pdo->prepare('UPDATE posts SET title=?, content=?, category=?, cover=? WHERE id=? AND user_id=?'); $stmt->execute([$title, $content, $category, $cover, $id, $user['id']]); header('Location: index.php'); exit; }
    $error = '标题和内容不能为空';
}
?>
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg"><title>编辑文章 - DuoDuo's Daily</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><div class="bg-overlay"></div><nav class="navbar"><div class="nav-inner"><a href="../index.php" class="nav-logo">DuoDuo's Daily</a><ul class="nav-links"><li><a href="index.php">工作台</a></li></ul></div></nav><div class="admin-layout"><div class="admin-card"><span class="eyebrow">EDIT STORY</span><h1>编辑文章</h1><?php if (!empty($error)): ?><p class="form-error"><?= htmlspecialchars($error) ?></p><?php endif; ?><form method="POST"><div class="form-group"><label>标题</label><input name="title" value="<?= htmlspecialchars($post['title']) ?>" required></div><div class="form-group"><label>分类</label><select name="category"><?php foreach (['life'=>'生活','tech'=>'技术','music'=>'音乐','essay'=>'随笔'] as $value => $label): ?><option value="<?= $value ?>" <?= $post['category'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div><div class="form-group"><label>封面图片 URL</label><input name="cover" value="<?= htmlspecialchars($post['cover']) ?>"></div><div class="form-group"><label>内容（支持 Markdown）</label><textarea name="content" class="markdown-editor" required><?= htmlspecialchars($post['content']) ?></textarea></div><button class="btn">保存修改</button></form></div></div></body></html>
