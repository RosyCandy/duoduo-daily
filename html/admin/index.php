<?php
session_start();
require_once '../includes/db.php';
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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg">
    <title>管理后台 - DuoDuo's Daily</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Ma+Shan+Zheng&family=Noto+Serif+SC:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="bg-overlay"></div>

    <nav class="navbar">
        <div class="nav-inner">
            <a href="../index.php" class="nav-logo">DuoDuo's Daily</a>
            <ul class="nav-links">
                <li><a href="../index.php">← 返回博客</a></li>
                <?php if($logged_in): ?>
                <li><a href="?logout=1">退出登录</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="admin-layout">
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
                            <div class="form-group"><label>显示名称</label><input name="display_name" value="<?= htmlspecialchars($profile['display_name']) ?>" required></div>
                            <div class="form-group"><label>个人简介</label><input name="description" value="<?= htmlspecialchars($profile['description']) ?>" required></div>
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
                            <div class="form-group"><label>QQ</label><input name="qq" value="<?= htmlspecialchars($profile['qq']) ?>"></div>
                            <div class="form-group"><label>邮箱</label><input type="email" name="email" value="<?= htmlspecialchars($profile['email']) ?>"></div>
                            <div class="form-group"><label>GitHub 链接</label><input type="url" name="link_github" value="<?= htmlspecialchars($profile['link_github']) ?>"></div>
                            <div class="form-group"><label>微博链接</label><input type="url" name="link_weibo" value="<?= htmlspecialchars($profile['link_weibo']) ?>"></div>
                            <div class="form-group"><label>博客园链接</label><input type="url" name="link_cnblogs" value="<?= htmlspecialchars($profile['link_cnblogs']) ?>"></div>
                            <div class="form-group"><label>CSDN 链接</label><input type="url" name="link_csdn" value="<?= htmlspecialchars($profile['link_csdn']) ?>"></div>
                        </div>
                        <button type="submit" class="btn">保存个人资料</button>
                    </form>
                </div>

                <div class="admin-card admin-section" id="new-post">
                    <h2>✍️ 写新文章</h2>
                    <form method="POST" action="save_post.php">
                        <div class="form-group">
                            <label>标题</label>
                            <input type="text" name="title" placeholder="文章标题" required>
                        </div>
                        <div class="form-group">
                            <label>分类</label>
                            <select name="category">
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
                            <div class="editor-toolbar">
                                <button type="button" class="btn btn-sm toolbar-btn" onclick="toggleMarkdownPreview(this)">预览</button>
                                <button type="button" class="btn btn-sm toolbar-btn" onclick="wrapSelection('**', '**', '加粗文字')">加粗</button>
                                <button type="button" class="btn btn-sm toolbar-btn" onclick="wrapSelection('> ', '', '引用内容')">引用</button>
                                <button type="button" class="btn btn-sm toolbar-btn" onclick="wrapSelection('\n- ', '', '列表项')">列表</button>
                                <label class="btn btn-sm toolbar-btn upload-content-image">
                                    插入图片
                                    <input type="file" accept="image/*" onchange="insertContentImage(this, false)">
                                </label>
                                <label class="btn btn-sm toolbar-btn upload-content-image">
                                    居中图片
                                    <input type="file" accept="image/*" onchange="insertContentImage(this, true)">
                                </label>
                            </div>
                            <textarea name="content" class="markdown-editor" placeholder="支持 Markdown，可用 ![图片说明](图片地址) 插入图片，或直接用上方按钮插入图片" required></textarea>
                            <div class="markdown-preview" hidden></div>
                        </div>
                        <button type="submit" class="btn">发布文章</button>
                    </form>
                </div>

                <div class="admin-card admin-section" id="new-music">
                    <h2>🎵 添加音乐</h2>
                    <form method="POST" action="save_music.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>歌曲名</label>
                            <input type="text" name="name" placeholder="歌曲名称" required>
                        </div>
                        <div class="form-group">
                            <label>歌手</label>
                            <input type="text" name="artist" placeholder="歌手名（可选）">
                        </div>
                        <div class="form-group">
                            <label>上传音频文件（flac/mp3）</label>
                            <input type="file" name="audio_file" accept=".mp3,.flac,.ogg,.wav">
                        </div>
                        <button type="submit" class="btn">添加音乐</button>
                    </form>

                    <?php if($music_list): ?>
                    <div style="margin-top:20px">
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
    </div>

    <footer class="footer">
        <p>© <?= date('Y') ?> DuoDuo's Daily</p>
    </footer>

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

        function uploadCover(input) {
            const file = input.files[0];
            if (!file) return;
            const form = new FormData();
            form.append('image', file);
            form.append('type', 'post');
            fetch('upload_image.php', { method: 'POST', body: form })
                .then(r => r.json())
                .then(data => {
                    if (data.url) {
                        const normalizedUrl = data.url.startsWith('/') ? data.url : '/' + data.url;
                        document.getElementById('cover-url').value = normalizedUrl;
                        document.getElementById('preview-img').src = normalizedUrl;
                        document.getElementById('cover-preview').style.display = 'block';
                    }
                });
        }

        function uploadAvatar(input) {
            const file = input.files[0];
            if (!file) return;
            const form = new FormData();
            form.append('image', file);
            form.append('type', 'avatar');
            fetch('upload_image.php', { method: 'POST', body: form })
                .then(r => r.json())
                .then(data => {
                    if (data.url) {
                        const avatarInput = document.querySelector('input[name="avatar"]');
                        if (avatarInput) avatarInput.value = data.url.startsWith('/') ? data.url : '/' + data.url;
                    }
                });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>
    <script src="../assets/js/markdown-editor.js"></script>
</body>
</html>

