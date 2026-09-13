<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 8;
$offset = ($page - 1) * $per_page;

$cat = isset($_GET['cat']) ? $_GET['cat'] : '';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$posts = get_posts($per_page, $offset, $cat, $search);
$total = get_post_count($cat, $search);
$total_pages = ceil($total / $per_page);
$profile = get_site_profile();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg">
    <title>DuoDuo's Daily</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Ma+Shan+Zheng&family=Noto+Serif+SC:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://at.alicdn.com/t/c/font_5144765_f9fez7wbd2f.css">
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="particles" id="particles"></div>

    <!-- 导航 -->
    <nav class="navbar">
        <div class="nav-inner">
            <a href="index.php" class="nav-logo">DuoDuo's Daily</a>
            <ul class="nav-links">
                <li><a href="index.php">首页</a></li>
                <li><a href="index.php?cat=life">生活</a></li>
                <li><a href="index.php?cat=essay">随笔</a></li>
                <li><a href="index.php?cat=tech">技术</a></li>
                <li><a href="music.php">音乐</a></li>
                <li><a href="about.php">关于</a></li>
                <li><a href="chat.php">AI 对话</a></li>
                <li><a href="account.php">账户</a></li>
            </ul>
            <button class="nav-toggle" onclick="toggleNav()">☰</button>
        </div>
    </nav>

    <div class="content-search-wrap">
        <form class="content-search" method="GET" action="index.php">
            <?php if ($cat): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($cat) ?>"><?php endif; ?>
            <input type="search" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="搜索文章标题或内容">
            <button type="submit">搜索</button>
        </form>
    </div>

    <div class="main-layout home-layout">
        <!-- 左侧边栏 -->
        <aside class="sidebar">
            <div class="sidebar-card profile-card">
                <div class="avatar" style="background-image:url('<?= htmlspecialchars($profile['avatar']) ?>')"></div>
                <h2 class="blog-title"><?= htmlspecialchars($profile['display_name']) ?></h2>
                <p class="blog-desc"><?= htmlspecialchars($profile['description']) ?></p>
                <div class="stats">
                    <div class="stat"><span><?= $total ?></span><small>文章</small></div>
                    <div class="stat"><span><?= get_comment_count() ?></span><small>评论</small></div>
                </div>
                <div class="contact-info">
                    <div><small>QQ</small><span><?= htmlspecialchars($profile['qq']) ?></span></div>
                    <div><small>邮箱</small><span><?= htmlspecialchars($profile['email']) ?></span></div>
                </div>
                <div class="social-links">
                    <a href="<?= htmlspecialchars($profile['link_github']) ?>" target="_blank" rel="noopener" title="GitHub"><i class="fab fa-github"></i></a>
                    <a href="<?= htmlspecialchars($profile['link_weibo']) ?>" target="_blank" rel="noopener" title="微博"><i class="fab fa-weibo"></i></a>
                    <a href="<?= htmlspecialchars($profile['link_cnblogs']) ?>" target="_blank" rel="noopener" title="博客园"><i class="fas fa-blog"></i></a>
                    <a href="<?= htmlspecialchars($profile['link_csdn']) ?>" target="_blank" rel="noopener" title="CSDN"><i class="iconfont icon-csdn"></i></a>
                </div>
            </div>

            <!-- 天气日历卡片 -->
            <div class="sidebar-card weather-card">
                <div class="weather-top">
                    <div class="weather-left">
                        <div class="weather-icon" id="weather-icon">⛅</div>
                        <div class="weather-temp" id="weather-temp">--°</div>
                        <div class="weather-desc" id="weather-desc">加载中...</div>
                        <div class="weather-city-picker">
                            <span id="weather-city">苏州</span>
                            <button type="button" id="change-city" title="更换城市" aria-label="更换城市">⌄</button>
                        </div>
                    </div>
                    <div class="weather-right">
                        <div class="calendar-date" id="cal-date"></div>
                        <div class="calendar-weekday" id="cal-weekday"></div>
                        <div class="calendar-lunar" id="cal-lunar"></div>
                    </div>
                </div>
                <div class="weather-forecast" id="weather-forecast"></div>
            </div>

            <!-- 音乐播放器 -->
            <div class="sidebar-card player-card">
                <div class="player-title">🎵 音乐</div>
                <div class="player-info">
                    <div class="disc" id="disc" style="background:none;overflow:hidden;border-radius:50%;padding:0">
                        <img class="album-art" src="/assets/images/Apple Music.png" alt="专辑封面">
                    </div>
                    <div class="song-meta">
                        <div class="song-name" id="song-name">韩子曦 - 病变</div>
                        <div class="song-artist" id="song-artist">点击播放</div>
                    </div>
                </div>
                <div class="player-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill"></div>
                    </div>
                    <div class="time-display">
                        <span id="current-time">0:00</span>
                        <span id="total-time">0:00</span>
                    </div>
                </div>
                <div class="player-controls">
                    <button onclick="prevSong()">⏮</button>
                    <button class="play-btn" onclick="togglePlay()" id="play-btn">▶</button>
                    <button onclick="nextSong()">⏭</button>
                </div>
                <audio id="audio" preload="metadata"></audio>
                <div class="playlist" id="playlist"></div>
            </div>

            <!-- 最新文章 -->
            <div class="sidebar-card">
                <div class="card-title">最新文章</div>
                <?php $recent = get_posts(5, 0); foreach($recent as $p): ?>
                <div class="recent-item">
                    <a href="post.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></a>
                    <span><?= date('m-d', strtotime($p['created_at'])) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </aside>

        <!-- 主内容 -->
        <main class="content">
            <?php if(empty($posts)): ?>
            <div class="empty-state">
                <p>还没有文章，<a href="account.php">登录后去写第一篇</a> ✍️</p>
            </div>
            <?php else: ?>
            <?php foreach($posts as $post): ?>
            <article class="post-card">
                <?php if($post['cover']): ?>
                <div class="post-cover" style="background-image:url('<?= htmlspecialchars($post['cover']) ?>')"></div>
                <?php endif; ?>
                <div class="post-body">
                    <div class="post-meta">
                        <span class="post-cat <?= $post['category'] ?>"><?= get_cat_label($post['category']) ?></span>
                        <span class="post-date"><?= date('Y年m月d日', strtotime($post['created_at'])) ?></span>
                    </div>
                    <h2 class="post-title">
                        <a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                    </h2>
                    <p class="post-excerpt"><?= mb_substr(strip_tags($post['content']), 0, 120) ?>...</p>
                    <a href="post.php?id=<?= $post['id'] ?>" class="read-more">阅读全文 →</a>
                </div>
            </article>
            <?php endforeach; ?>

            <!-- 分页 -->
            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                <a href="?page=<?= $page-1 ?>" class="page-btn">← 上一页</a>
                <?php endif; ?>
                <span class="page-info"><?= $page ?> / <?= $total_pages ?></span>
                <?php if($page < $total_pages): ?>
                <a href="?page=<?= $page+1 ?>" class="page-btn">下一页 →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>

    <div class="city-picker-modal" id="city-picker-modal" hidden>
        <div class="city-picker-dialog" role="dialog" aria-modal="true" aria-labelledby="city-picker-title">
            <div class="city-picker-header"><h3 id="city-picker-title">选择城市</h3><button type="button" id="close-city-picker" aria-label="关闭">×</button></div>
            <input class="city-search" id="city-search" type="search" placeholder="搜索省、市或县级市">
            <div class="city-letters" id="city-letters"></div>
            <div class="city-list" id="city-list"></div>
        </div>
    </div>

    <footer class="footer">
        <p>© <?= date('Y') ?> DuoDuo's Daily · 用心记录每一天</p>
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pinyin-pro@3.26.0/dist/index.js"></script>
    <script src="assets/js/weather.js"></script>
</body>
</html>
