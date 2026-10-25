<?php
require_once __DIR__ . '/functions.php';

function blog_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function blog_icon($name) {
    $paths = [
        'home' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-8H9v8H4a1 1 0 0 1-1-1z"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'tag' => '<path d="M20 13 13 20a2 2 0 0 1-3 0L3 13V3h10l7 7a2 2 0 0 1 0 3Z"/><circle cx="7.5" cy="7.5" r=".8"/>',
        'archive' => '<rect x="3" y="3" width="18" height="4" rx="1"/><path d="M5 7v14h14V7M10 11h4"/>',
        'music' => '<path d="M9 18V5l12-2v13M9 8l12-2"/><ellipse cx="6" cy="18" rx="3" ry="3"/><ellipse cx="18" cy="16" rx="3" ry="3"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="7.5"/><path d="m16 16 5 5"/>',
        'arrow' => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/>',
        'moon' => '<path d="M20 15.5A9 9 0 0 1 8.5 4 9 9 0 1 0 20 15.5Z"/>',
        'chat' => '<path d="M21 11.5a9 9 0 0 1-9 9 10 10 0 0 1-4-.9L3 21l1.4-5a10 10 0 0 1-.9-4.5 9 9 0 0 1 17.5 0Z"/><path d="M8 12h8M8 8h5"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'leaf' => '<path d="M20 3C7 2 2 8 5 15c3 7 17 4 15-12ZM4 21 15 10"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>',
    ];
    return '<svg class="blog-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['leaf']) . '</svg>';
}

function blog_head($title = "DuoDuo's Daily") { ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f3f8f5">
    <title><?= blog_escape($title) ?></title>
    <link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/blog.css?v=20261022-2">
    <script src="/assets/js/blog.js?v=20261022-2" defer></script>
<?php }

function blog_workspace_nav() {
    $logged_in = !empty($_SESSION['admin']) || !empty($_SESSION['user']);
    $workspace = !empty($_SESSION['admin']) ? '/admin/index.php' : '/user/index.php'; ?>
    <header class="site-header"><div class="header-inner">
        <a class="site-brand" href="/index.php"><?= blog_icon('leaf') ?><span>DuoDuo<span class="brand-mark">’s</span> Daily<span class="brand-dot">.</span></span></a>
        <div class="workspace-header-links"><a href="/index.php">返回博客</a><?php if ($logged_in): ?><a href="<?= $workspace ?>">工作台</a><a href="/account.php?logout=1">退出登录</a><?php else: ?><a href="/account.php">账户中心</a><?php endif; ?><button type="button" class="icon-button theme-toggle" aria-label="切换深色模式" title="切换配色"><?= blog_icon('moon') ?></button></div>
    </div></header>
<?php }

function blog_editor_toolbar() { ?>
    <div class="editor-toolbar">
        <label class="btn btn-sm image-upload-button">上传正文图片<input type="file" accept="image/jpeg,image/png,image/gif,image/webp" onchange="insertContentImage(this)"></label>
        <button type="button" class="btn btn-sm toolbar-btn" data-preview-toggle onclick="toggleMarkdownPreview(this)">预览</button>
        <button type="button" class="btn btn-sm toolbar-btn" onclick="wrapSelection('**', '**', '加粗文字')">加粗</button>
        <button type="button" class="btn btn-sm toolbar-btn" onclick="wrapSelection('> ', '', '引用内容')">引用</button>
        <button type="button" class="btn btn-sm toolbar-btn" onclick="wrapSelection('\n- ', '', '列表项')">列表</button>
    </div>
    <p class="editor-image-help">图片插在当前光标处，单独一行居中；上传后可接着写下一段。支持 JPG、PNG、GIF、WebP，最大 10MB。</p>
<?php }

function blog_nav($active = '') {
    $links = [
        'home' => ['index.php', '首页'], 'categories' => ['index.php?view=categories#journal', '分类'],
        'tags' => ['index.php?view=tags#journal', '标签'], 'archives' => ['index.php?view=archives#journal', '归档'],
        'music' => ['music.php', '音乐'], 'about' => ['about.php', '关于'], 'chat' => ['chat.php', 'AI 对话']
    ]; ?>
    <a class="skip-link" href="#journal">跳到正文</a>
    <header class="site-header">
        <div class="header-inner">
            <a href="index.php" class="site-brand"><?= blog_icon('leaf') ?><span>DuoDuo<span class="brand-mark">’s</span> Daily<span class="brand-dot">.</span></span></a>
            <nav class="site-navigation" id="site-navigation" aria-label="主导航">
                <?php foreach ($links as $key => [$url, $label]): ?><a href="<?= $url ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= $label ?></a><?php endforeach; ?>
                <a class="mobile-account" href="account.php">我的账户</a>
            </nav>
            <div class="header-actions">
                <button type="button" class="icon-button theme-toggle" aria-label="切换深色模式" title="切换配色"><?= blog_icon('moon') ?></button>
                <a class="account-link" href="account.php"><?= blog_icon('user') ?><span>账户</span></a>
                <button class="icon-button menu-toggle" type="button" aria-label="展开导航" aria-expanded="false" aria-controls="site-navigation"><?= blog_icon('menu') ?></button>
            </div>
        </div>
    </header>
<?php }

function blog_topics($post) {
    static $topics;
    if ($topics === null) $topics = json_decode(file_get_contents(__DIR__ . '/../assets/data/post-topics.json'), true) ?: [];
    $key = mb_strtolower(preg_replace('/\s+/u', '', trim($post['title'])));
    return $topics[$key] ?? [get_cat_label($post['category'])];
}

function blog_post_index() {
    global $pdo;
    return $pdo->query('SELECT id, title, category, created_at FROM posts ORDER BY created_at DESC, id DESC')->fetchAll();
}

function blog_tag_counts($index) {
    $tags = [];
    foreach ($index as $post) foreach (blog_topics($post) as $tag) $tags[$tag] = ($tags[$tag] ?? 0) + 1;
    arsort($tags);
    return $tags;
}

function blog_profile($profile, $index, $active = 'home') { ?>
    <aside class="author-sidebar" aria-label="作者与导航">
        <section class="author-card">
            <span class="eyebrow">THE LITTLE CORNER</span>
            <a class="author-avatar" href="about.php"><img src="<?= blog_escape($profile['avatar']) ?>" alt="<?= blog_escape($profile['display_name']) ?>的头像" width="96" height="96"></a>
            <h2><?= blog_escape($profile['display_name']) ?></h2>
            <p class="author-bio"><?= blog_escape($profile['description']) ?></p>
            <div class="author-stats"><a href="index.php?view=archives#journal"><strong><?= count($index) ?></strong><span>篇文章</span></a><a href="index.php?view=tags#journal"><strong><?= count(blog_tag_counts($index)) ?></strong><span>个标签</span></a><div><strong><?= get_comment_count() ?></strong><span>条评论</span></div></div>
            <nav class="sidebar-nav" aria-label="博客导航">
                <?php foreach (['home' => ['首页', 'index.php'], 'categories' => ['文章分类', 'index.php?view=categories#journal'], 'tags' => ['标签漫游', 'index.php?view=tags#journal'], 'archives' => ['时光归档', 'index.php?view=archives#journal'], 'music' => ['听听音乐', 'music.php'], 'about' => ['关于我', 'about.php']] as $key => [$label, $url]): ?>
                <a href="<?= $url ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= blog_icon($key === 'categories' ? 'grid' : ($key === 'tags' ? 'tag' : ($key === 'archives' ? 'archive' : ($key === 'about' ? 'user' : $key)))) ?><span><?= $label ?></span><span class="nav-chevron">›</span></a>
                <?php endforeach; ?>
            </nav>
            <div class="author-socials">
                <?php foreach (['github' => 'GitHub', 'weibo' => '微博', 'cnblogs' => '博客园', 'csdn' => 'CSDN'] as $key => $label): if (!empty($profile['link_' . $key])): ?><a href="<?= blog_escape($profile['link_' . $key]) ?>" target="_blank" rel="noopener noreferrer"><?= $label ?></a><?php endif; endforeach; ?>
            </div>
            <?php if (!empty($profile['email'])): ?><a class="author-email" href="mailto:<?= blog_escape($profile['email']) ?>"><?= blog_escape($profile['email']) ?></a><?php endif; ?>
            <?php if (!empty($profile['qq'])): ?><p class="author-contact">QQ · <?= blog_escape($profile['qq']) ?></p><?php endif; ?>
        </section>
        <p class="sidebar-note"><?= blog_icon('leaf') ?>在这里，收藏平凡日子的微光。</p>
    </aside>
<?php }

function blog_player() { ?>
    <section class="rail-card player-card">
        <div class="section-label"><span><?= blog_icon('music') ?>此刻，听首歌</span><a href="music.php" aria-label="打开完整歌单">↗</a></div>
        <div class="player-info"><div class="disc" id="disc"><img class="album-art" src="/assets/images/Apple Music.png" alt="音乐唱片" width="70" height="70"></div><div class="song-meta"><div class="song-name" id="song-name">正在加载歌单</div><div class="song-artist" id="song-artist">让音乐陪伴阅读</div></div></div>
        <div class="player-progress"><div class="progress-bar" role="slider" tabindex="0" aria-label="播放进度" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-fill" id="progress-fill"></div></div><div class="time-display"><span id="current-time">0:00</span><span id="total-time">0:00</span></div></div>
        <div class="player-controls"><button type="button" onclick="prevSong()" aria-label="上一首">⏮</button><button type="button" class="play-btn" id="play-btn" onclick="togglePlay()" aria-label="播放">▶</button><button type="button" onclick="nextSong()" aria-label="下一首">⏭</button></div>
        <audio id="audio" preload="metadata"></audio>
        <details class="mini-playlist"><summary>展开歌单</summary><div class="playlist" id="playlist"></div></details>
    </section>
<?php }

function blog_rail($index, $widgets = true) { ?>
    <aside class="reading-rail" aria-label="博客小窗">
        <?php if ($widgets): blog_player(); ?>
        <section class="rail-card weather-card">
            <div class="section-label"><span><?= blog_icon('sun') ?>日历与天气</span><div class="weather-city-picker"><span id="weather-city">苏州</span><button type="button" id="change-city" aria-label="更换城市">⌄</button></div></div>
            <div class="weather-top"><div class="weather-left"><div class="weather-icon" id="weather-icon">⛅</div><div class="weather-temp" id="weather-temp">--°</div><div class="weather-desc" id="weather-desc">天气加载中</div></div><div class="weather-right"><div class="calendar-date" id="cal-date"></div><div class="calendar-weekday" id="cal-weekday"></div><div class="calendar-lunar" id="cal-lunar"></div></div></div><div class="weather-forecast" id="weather-forecast"></div>
        </section>
        <?php endif; ?>
        <section class="rail-card recent-card"><div class="section-label"><span><?= blog_icon('archive') ?>最近更新</span></div><?php foreach (array_slice($index, 0, 5) as $i => $post): ?><a class="recent-entry" href="post.php?id=<?= (int)$post['id'] ?>"><span class="entry-number"><?= sprintf('%02d', $i + 1) ?></span><span><?= blog_escape($post['title']) ?><time><?= date('Y.m.d', strtotime($post['created_at'])) ?></time></span></a><?php endforeach; ?></section>
        <section class="rail-card"><div class="section-label"><span><?= blog_icon('tag') ?>热门标签</span><a href="index.php?view=tags#journal" aria-label="全部标签">↗</a></div><div class="tag-cloud"><?php foreach (array_slice(blog_tag_counts($index), 0, 12, true) as $tag => $count): ?><a href="index.php?tag=<?= rawurlencode($tag) ?>#journal"><?= blog_escape($tag) ?><small><?= $count ?></small></a><?php endforeach; ?></div></section>
    </aside>
<?php }

function blog_city_modal() { ?>
    <div class="city-picker-modal" id="city-picker-modal" hidden><div class="city-picker-dialog" role="dialog" aria-modal="true" aria-labelledby="city-picker-title"><div class="city-picker-header"><h3 id="city-picker-title">选择城市</h3><button type="button" id="close-city-picker" aria-label="关闭">×</button></div><input class="city-search" id="city-search" type="search" aria-label="搜索城市" placeholder="搜索省、市或县级市"><div class="city-letters" id="city-letters"></div><div class="city-list" id="city-list"></div></div></div>
<?php }

function blog_footer($weather = false) { ?>
    <footer class="site-footer"><a class="footer-brand" href="/index.php">DuoDuo’s Daily<span>.</span></a><p>记录生活的点点滴滴 · 用心收藏每一天</p><small>© <?= date('Y') ?> DuoDuo’s Daily</small><a class="back-to-top" href="#top" aria-label="返回顶部">↑</a></footer>
    <script src="/assets/js/main.js?v=20261022-2"></script>
    <?php if ($weather): blog_city_modal(); ?><script src="https://cdn.jsdelivr.net/npm/pinyin-pro@3.26.0/dist/index.js"></script><script src="assets/js/weather.js?v=20261021-4"></script><?php endif; ?>
<?php }
