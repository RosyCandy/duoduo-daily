<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/site.php';
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
<!DOCTYPE html><html lang="zh-CN"><head><?php blog_head("编辑文章 · DuoDuo's Daily"); ?>
</head><body class="blog-app management-view" id="top"><?php blog_workspace_nav(); ?><main class="admin-layout" id="journal"><div class="admin-card"><span class="eyebrow">EDIT STORY</span><h1>编辑文章</h1><?php if (!empty($error)): ?><p class="form-error"><?= htmlspecialchars($error) ?></p><?php endif; ?><form method="POST"><div class="form-group"><label for="field-title">标题</label><input id="field-title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required></div><div class="form-group"><label for="field-category">分类</label><select id="field-category" name="category"><?php foreach (['life'=>'生活','tech'=>'技术','music'=>'音乐','essay'=>'随笔'] as $value => $label): ?><option value="<?= $value ?>" <?= $post['category'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div><div class="form-group"><label for="field-cover">封面图片 URL</label><input id="field-cover" name="cover" value="<?= htmlspecialchars($post['cover']) ?>"></div><div class="form-group"><label for="field-content">内容（支持 Markdown）</label><textarea id="field-content" name="content" class="markdown-editor" required><?= htmlspecialchars($post['content']) ?></textarea></div><button class="btn">保存修改</button></form></div></main><?php blog_footer(); ?>
</body></html>
