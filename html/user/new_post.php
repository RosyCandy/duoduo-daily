<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/site.php';
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
<!DOCTYPE html><html lang="zh-CN"><head><?php blog_head("写新文章 · DuoDuo's Daily"); ?>
</head><body class="blog-app management-view" id="top"><?php blog_workspace_nav(); ?><main class="admin-layout" id="journal"><div class="admin-card"><span class="eyebrow">NEW STORY</span><h1>写新文章</h1><?php if (!empty($error)): ?><p class="form-error"><?= htmlspecialchars($error) ?></p><?php endif; ?><form method="POST"><div class="form-group"><label for="field-title">标题</label><input id="field-title" name="title" required></div><div class="form-group"><label for="field-category">分类</label><select id="field-category" name="category"><option value="life">生活</option><option value="tech">技术</option><option value="music">音乐</option><option value="essay">随笔</option></select></div><div class="form-group"><label for="field-cover">封面图片 URL</label><input id="field-cover" name="cover" placeholder="https://..."></div><div class="form-group"><label for="field-content">内容（支持 Markdown）</label><textarea id="field-content" name="content" class="markdown-editor" required></textarea></div><button class="btn">发布文章</button></form></div></main><?php blog_footer(); ?>
</body></html>
