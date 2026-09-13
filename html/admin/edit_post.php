<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: index.php'); exit; }
require_once '../includes/db.php';
require_once '../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$post = get_post($id);
if (!$post) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'life';
    $cover = trim($_POST['cover'] ?? '');
    if ($title && $content) {
        $stmt = $pdo->prepare("UPDATE posts SET title=?, content=?, category=?, cover=? WHERE id=?");
        $stmt->execute([$title, $content, $category, $cover, $id]);
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg">
    <title>编辑文章 - DuoDuo's Daily</title>
    <link href="https://fonts.googleapis.com/css2?family=Ma+Shan+Zheng&family=Noto+Serif+SC:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>
</head>
<body>
    <div class="bg-overlay"></div>
    <nav class="navbar">
        <div class="nav-inner">
            <a href="../index.php" class="nav-logo">DuoDuo's Daily</a>
            <ul class="nav-links"><li><a href="index.php">← 返回管理</a></li></ul>
        </div>
    </nav>
    <div class="admin-layout">
        <div class="admin-card">
            <h2>编辑文章</h2>
            <form method="POST">
                <div class="form-group">
                    <label>标题</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label>分类</label>
                    <select name="category">
                        <?php foreach(['life'=>'生活','tech'=>'技术','music'=>'音乐','essay'=>'随笔'] as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= $post['category']===$v?'selected':'' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>封面图片URL</label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="text" name="cover" id="cover-url" value="<?= htmlspecialchars($post['cover']) ?>" placeholder="https://... 或上传本地图片">
                        <label class="btn btn-sm" style="cursor:pointer;white-space:nowrap">
                            本地上传
                            <input type="file" accept="image/*" style="display:none" onchange="uploadCover(this)">
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>内容</label>
                    <div class="editor-toolbar">
                        <button type="button" class="btn btn-sm" onclick="toggleMarkdownPreview(this)">预览</button>
                        <label class="btn btn-sm upload-content-image">
                            插入图片
                            <input type="file" accept="image/*" onchange="insertContentImage(this)">
                        </label>
                    </div>
                    <textarea name="content" class="markdown-editor" placeholder="支持 Markdown，可用 ![图片说明](图片地址) 插入图片" required><?= htmlspecialchars($post['content']) ?></textarea>
                    <div class="markdown-preview" hidden></div>
                </div>
                <button type="submit" class="btn">保存修改</button>
            </form>
        </div>
    </div>
    <script src="../assets/js/markdown-editor.js"></script>
</body>
</html>
