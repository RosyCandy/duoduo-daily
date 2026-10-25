<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/site.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['admin'])) {
    header('Location: ../account.php');
    exit;
}

// 退出
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// 删除文章
if (isset($_GET['delete']) && isset($_SESSION['admin'])) {
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id=?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: index.php');
    exit;
}

if (isset($_GET['delete_user']) && isset($_SESSION['admin'])) {
    $user_id = (int)$_GET['delete_user'];
    $pdo->prepare('DELETE FROM posts WHERE user_id=?')->execute([$user_id]);
    $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$user_id]);
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $avatar_value = trim($_POST['avatar'] ?? '');
    if ($avatar_value === '') {
        $avatar_value = '/assets/images/avatar.jpeg';
    } elseif (!preg_match('#^(https?://|/)#i', $avatar_value)) {
        $avatar_value = '/' . ltrim($avatar_value, '/');
    }

    $profile_fields = [
        trim($_POST['display_name'] ?? ''), trim($_POST['description'] ?? ''), $avatar_value,
        trim($_POST['qq'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['link_github'] ?? ''),
        trim($_POST['link_weibo'] ?? ''), trim($_POST['link_cnblogs'] ?? ''), trim($_POST['link_csdn'] ?? '')
    ];
    $urls_valid = true;
    foreach ([$profile_fields[2], $profile_fields[5], $profile_fields[6], $profile_fields[7], $profile_fields[8]] as $url) {
        if ($url && !preg_match('#^(https?://|/)#i', $url)) $urls_valid = false;
    }
    if ($profile_fields[0] && $profile_fields[1] && $profile_fields[2] && $urls_valid) {
        $stmt = $pdo->prepare('UPDATE site_profile SET display_name=?, description=?, avatar=?, qq=?, email=?, link_github=?, link_weibo=?, link_cnblogs=?, link_csdn=? WHERE id=1');
        $stmt->execute($profile_fields);
    }
    header('Location: index.php#site-profile');
    exit;
}

$logged_in = true;
$posts = get_posts(100, 0);
$music_list = get_music_list();
$users = $pdo->query('SELECT u.id, u.username, u.display_name, u.created_at, COUNT(p.id) AS post_count FROM users u LEFT JOIN posts p ON p.user_id=u.id GROUP BY u.id ORDER BY u.created_at DESC')->fetchAll();
$profile = get_site_profile();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head><?php blog_head("管理后台 · DuoDuo's Daily"); ?>
</head>
<body class="blog-app management-view" id="top">


    <?php blog_workspace_nav(); ?>

    <main class="admin-layout" id="journal">
        <?php if ($logged_in): ?>
        <div class="admin-shell">
            <aside class="admin-sidebar">
                <div class="admin-sidebar-title">管理后台</div>
                <nav class="admin-menu" aria-label="后台导航">
                    <button type="button" class="admin-nav" data-target="site-profile">个人资料</button>
                    <button type="button" class="admin-nav" data-target="new-post">写新文章</button>
                    <button type="button" class="admin-nav" data-target="new-music">添加音乐</button>
                    <button type="button" class="admin-nav" data-target="post-list">文章管理</button>
                    <button type="button" class="admin-nav" data-target="user-list">用户管理</button>
                </nav>
            </aside>

            <div class="admin-content">
                <div class="admin-empty">请选择左侧菜单开始管理</div>

                <div class="admin-card admin-section" id="site-profile">
                    <h2>个人资料与社交链接</h2>
                    <p class="admin-hint">这里的修改会同步显示在博客首页左侧资料卡。</p>
                    <form method="POST">
                        <input type="hidden" name="save_profile" value="1">
                        <div class="profile-form-grid">
                            <div class="form-group"><label for="field-display_name">显示名称</label><input id="field-display_name" name="display_name" value="<?= htmlspecialchars($profile['display_name']) ?>" required></div>
                            <div class="form-group"><label for="field-description">个人简介</label><input id="field-description" name="description" value="<?= htmlspecialchars($profile['description']) ?>" required></div>
                            <div class="form-group profile-form-wide">
                                <label>头像图片 URL</label>
                                <div class="avatar-upload-row">
                                    <input name="avatar" value="<?= htmlspecialchars($profile['avatar'] ?: '/assets/images/avatar.jpeg') ?>" placeholder="/assets/images/avatar.jpeg" required>
                                    <label class="btn btn-sm avatar-upload-btn">
                                        上传头像
                                        <input type="file" accept="image/*" onchange="uploadAvatar(this)">
                                    </label>
                                </div>
                                <small class="form-help">默认头像文件已在项目中：/assets/images/avatar.jpeg</small>
                            </div>
                            <div class="form-group"><label for="field-qq">QQ</label><input id="field-qq" name="qq" value="<?= htmlspecialchars($profile['qq']) ?>"></div>
                            <div class="form-group"><label for="field-email">邮箱</label><input id="field-email" type="email" name="email" value="<?= htmlspecialchars($profile['email']) ?>"></div>
                            <div class="form-group"><label for="field-link_github">GitHub 链接</label><input id="field-link_github" type="url" name="link_github" value="<?= htmlspecialchars($profile['link_github']) ?>"></div>
                            <div class="form-group"><label for="field-link_weibo">微博链接</label><input id="field-link_weibo" type="url" name="link_weibo" value="<?= htmlspecialchars($profile['link_weibo']) ?>"></div>
                            <div class="form-group"><label for="field-link_cnblogs">博客园链接</label><input id="field-link_cnblogs" type="url" name="link_cnblogs" value="<?= htmlspecialchars($profile['link_cnblogs']) ?>"></div>
                            <div class="form-group"><label for="field-link_csdn">CSDN 链接</label><input id="field-link_csdn" type="url" name="link_csdn" value="<?= htmlspecialchars($profile['link_csdn']) ?>"></div>
                        </div>
                        <button type="submit" class="btn">保存个人资料</button>
                    </form>
                </div>

                <div class="admin-card admin-section" id="new-post">
                    <h2>✍️ 写新文章</h2>
                    <form method="POST" action="save_post.php">
                        <div class="form-group">
                            <label for="field-title">标题</label><input id="field-title" type="text" name="title" placeholder="文章标题" required>
                        </div>
                        <div class="form-group">
                            <label for="field-category">分类</label><select id="field-category" name="category">
                                <option value="life">生活</option>
                                <option value="tech">技术</option>
                                <option value="music">音乐</option>
                                <option value="essay">随笔</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>封面图片（可选）</label>
                            <div style="display:flex;gap:8px;align-items:center">
                                <input type="text" name="cover" id="cover-url" placeholder="https://... 或上传本地图片">
                                <label class="btn btn-sm" style="cursor:pointer;white-space:nowrap">
                                    本地上传
                                    <input type="file" accept="image/*" style="display:none" onchange="uploadCover(this)">
                                </label>
                            </div>
                            <div id="cover-preview" style="margin-top:8px;display:none">
                                <img id="preview-img" style="max-height:120px;border-radius:8px">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>内容</label>
                            <?php blog_editor_toolbar(); ?>
                            <textarea name="content" class="markdown-editor" placeholder="写下正文，在需要插图的位置点击「上传正文图片」…" required></textarea>
                            <div class="markdown-preview" hidden></div>
                        </div>
                        <button type="submit" class="btn">发布文章</button>
                    </form>
                </div>

                <div class="admin-card admin-section" id="new-music">
                    <h2>🎵 添加音乐</h2>
                    <form method="POST" action="save_music.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="field-name">歌曲名</label><input id="field-name" type="text" name="name" placeholder="歌曲名称" required>
                        </div>
                        <div class="form-group">
                            <label for="field-artist">歌手</label><input id="field-artist" type="text" name="artist" placeholder="歌手名（可选）">
                        </div>
                        <div class="form-group">
                            <label for="field-audio_file">上传音频文件（flac/mp3）</label><input id="field-audio_file" type="file" name="audio_file" accept=".mp3,.flac,.ogg,.wav">
                        </div>
                        <button type="submit" class="btn">添加音乐</button>
                    </form>

                    <?php if($music_list): ?>
                    <div class="admin-music-list" style="margin-top:20px">
                        <div class="card-title">音乐列表</div>
                        <?php foreach($music_list as $m): ?>
                        <div class="post-list-item">
                            <span class="post-list-title"><?= htmlspecialchars(($m['artist']?$m['artist'].' - ':'').$m['name']) ?></span>
                            <a href="delete_music.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定删除？')">删除</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="admin-card admin-section" id="post-list">
                    <h2>📄 文章管理</h2>
                    <?php if(empty($posts)): ?>
                    <p style="color:#7a5c3a;font-size:0.9rem">还没有文章</p>
                    <?php else: ?>
                    <?php foreach($posts as $p): ?>
                    <div class="post-list-item">
                        <span class="post-list-title"><?= htmlspecialchars($p['title']) ?></span>
                        <div class="post-list-actions">
                            <a href="edit_post.php?id=<?= $p['id'] ?>" class="btn btn-sm">编辑</a>
                            <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定删除？')">删除</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="admin-card admin-section" id="user-list">
                    <h2>👥 用户管理 <small><?= count($users) ?></small></h2>
                    <?php if(empty($users)): ?><p>还没有普通用户。</p><?php else: foreach($users as $u): ?>
                    <div class="post-list-item"><div><strong><?= htmlspecialchars($u['display_name']) ?></strong><small>@<?= htmlspecialchars($u['username']) ?> · <?= $u['post_count'] ?> 篇文章</small></div><a href="?delete_user=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('删除用户及其全部文章？')">删除账户</a></div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script>
        const adminNavButtons = document.querySelectorAll('.admin-nav');
        const adminSections = document.querySelectorAll('.admin-section');
        const adminEmpty = document.querySelector('.admin-empty');

        function showAdminSection(targetId) {
            adminSections.forEach((section) => {
                const isActive = section.id === targetId;
                section.classList.toggle('active', isActive);
                section.style.display = isActive ? 'block' : 'none';
            });

            adminNavButtons.forEach((button) => {
                button.classList.toggle('active', button.dataset.target === targetId);
            });

            if (adminEmpty) {
                adminEmpty.classList.toggle('hidden', !!targetId);
            }
        }

        adminNavButtons.forEach((button) => {
            button.addEventListener('click', () => showAdminSection(button.dataset.target));
        });

        const initialSection = location.hash.slice(1);
        showAdminSection([...adminSections].some(section => section.id === initialSection) ? initialSection : 'site-profile');

    </script>
    <script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>
    <script src="../assets/js/markdown-editor.js?v=20261021-2"></script>
<?php blog_footer(); ?>
</body>
</html>

