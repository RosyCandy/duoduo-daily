<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/site.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = get_post($id);
if (!$post) { header('Location: index.php'); exit; }

// 删除评论
if (isset($_GET['delete_comment']) && isset($_SESSION['admin'])) {
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id=?");
    $stmt->execute([(int)$_GET['delete_comment']]);
    header("Location: post.php?id=$id");
    exit;
}

// 提交评论
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $author = '匿名';
    $content = trim($_POST['content'] ?? '');
    if ($content) {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, author, content) VALUES (?,?,?)");
        $stmt->execute([$id, $author, $content]);
        header("Location: post.php?id=$id#comments");
        exit;
    }
}

$comments = get_comments($id);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<?php blog_head($post['title'] . " · DuoDuo's Daily"); ?>
<script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>
</head>
<body class="blog-app inner-page" id="top">
<?php blog_nav(); ?>
    <div class="reading-layout" id="journal">
        <main class="reading-main"><nav class="breadcrumbs" aria-label="当前位置"><a href="index.php#journal">首页</a><span>›</span><a href="index.php?cat=<?= blog_escape($post['category']) ?>#journal"><?= get_cat_label($post['category']) ?></a><span>›</span><span>正文</span></nav>
            <article class="post-detail">
                <div class="post-meta">
                    <span class="post-cat <?= $post['category'] ?>"><?= get_cat_label($post['category']) ?></span>
                    <span class="post-date"><?= date('Y年m月d日', strtotime($post['created_at'])) ?></span>
                </div>
                <h1><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-content markdown-body" data-markdown-content>
<?= htmlspecialchars(trim($post['content']), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="post-export-links" aria-label="导出文章">
                    <span>导出：</span>
                    <a href="export.php?id=<?= $id ?>&format=md">Markdown</a>
                    <a href="export.php?id=<?= $id ?>&format=txt">TXT</a>
                </div>
            </article>

            <!-- 评论 -->
            <div class="post-detail comments-section" id="comments" style="margin-top:20px">
                <div class="card-title">评论 (<?= count($comments) ?>)</div>

                <?php foreach($comments as $c): ?>
                    <div class="comment-item">
                        <span class="comment-author">匿名</span>
                        <span class="comment-time"><?= date('Y-m-d H:i', strtotime($c['created_at'])) ?></span>
                        <?php if(isset($_SESSION['admin'])): ?>
                            <a href="?delete_comment=<?= $c['id'] ?>&id=<?= $id ?>"
                               style="float:right;font-size:0.75rem;color:#ff8fab"
                               onclick="return confirm('删除这条评论？')">删除</a>
                        <?php endif; ?>
                        <div class="comment-content"><?= nl2br(htmlspecialchars($c['content'])) ?></div>
                    </div>
                <?php endforeach; ?>

                <div class="comment-form" style="margin-top:20px">
                    <div class="card-title">留下评论</div>
                    <form method="POST">
                        <input type="hidden" name="comment" value="1">
                        <textarea aria-label="评论内容" name="content" placeholder="说说你的想法..." required></textarea>
                        <button type="submit" class="btn">发表评论</button>
                    </form>
                </div>
            </div>
        </main>
        <aside class="toc-sidebar"><div class="toc-heading"><?= blog_icon('archive') ?>文章目录</div><nav class="article-toc" aria-label="文章目录"></nav><a class="toc-back" href="index.php#journal">← 回到日常</a></aside>
    </div>

    <script src="assets/js/markdown.js"></script>
    <?php blog_footer(); ?>
</body>
</html>
