<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/site.php';
require_once '../includes/functions.php';
require_user();
$user = current_user();
if (isset($_GET['logout'])) { unset($_SESSION['user']); header('Location: ../account.php'); exit; }
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM posts WHERE id=? AND user_id=?');
    $stmt->execute([(int)$_GET['delete'], $user['id']]);
    header('Location: index.php'); exit;
}
$posts = get_user_posts($user['id']);
?>
<!DOCTYPE html><html lang="zh-CN"><head><?php blog_head("个人工作台 · DuoDuo's Daily"); ?>
</head>
<body class="blog-app management-view" id="top"><?php blog_workspace_nav(); ?>
<main class="admin-layout" id="journal"><div class="admin-card workspace-header"><div><span class="eyebrow">PERSONAL WORKSPACE</span><h1><?= htmlspecialchars($user['display_name']) ?> 的工作台</h1><p>管理你的文章和公开内容。</p></div><a class="btn" href="new_post.php">写新文章</a></div><div class="admin-card"><h2>我的文章 <small><?= count($posts) ?></small></h2><?php if (!$posts): ?><p>还没有文章，开始写下第一篇吧。</p><?php else: foreach ($posts as $post): ?><div class="post-list-item"><div><strong><?= htmlspecialchars($post['title']) ?></strong><small><?= date('Y-m-d', strtotime($post['created_at'])) ?></small></div><div class="post-list-actions"><a class="btn btn-sm" href="edit_post.php?id=<?= $post['id'] ?>">编辑</a><a class="btn btn-sm btn-danger" href="?delete=<?= $post['id'] ?>" onclick="return confirm('确定删除这篇文章？')">删除</a></div></div><?php endforeach; endif; ?></div></main><?php blog_footer(); ?>
</body></html>
