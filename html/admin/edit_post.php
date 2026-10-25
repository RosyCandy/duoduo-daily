<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: index.php'); exit; }
require_once '../includes/db.php';
require_once '../includes/site.php';
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
<head><?php blog_head("编辑文章 · DuoDuo's Daily"); ?>
<script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script></head>
<body class="blog-app management-view" id="top">

    <?php blog_workspace_nav(); ?>
    <main class="admin-layout" id="journal">
        <div class="admin-card">
            <h2>编辑文章</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="field-title">标题</label><input id="field-title" type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="field-category">分类</label><select id="field-category" name="category">
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
                    <?php blog_editor_toolbar(); ?>
                    <textarea name="content" class="markdown-editor" placeholder="写下正文，在需要插图的位置点击「上传正文图片」…" required><?= htmlspecialchars($post['content']) ?></textarea>
                    <div class="markdown-preview" hidden></div>
                </div>
                <button type="submit" class="btn">保存修改</button>
            </form>
        </div>
    </main>
    <script src="../assets/js/markdown-editor.js?v=20261021-2"></script>
<?php blog_footer(); ?>
</body>
</html>
