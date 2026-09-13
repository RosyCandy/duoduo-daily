<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$posts = get_posts(20, 0, 'music', $search);
$music_list = get_music_list($search);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/assets/images/cover.jpg">
    <title>音乐 - DuoDuo's Daily</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .music-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 24px;
            align-items: start;
        }
        .music-player-panel {
            position: sticky;
            top: 80px;
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 28px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(8px);
        }
        .now-playing-cover {
            width: 180px; height: 180px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1a3a4a, #2a5a6a);
            display: flex; align-items: center; justify-content: center;
            font-size: 4rem;
            margin: 0 auto 20px;
            box-shadow: 0 8px 32px rgba(0,150,180,0.3);
        }
        .now-playing-cover.spinning { animation: spin 4s linear infinite; }
        .now-playing-title {
            text-align: center;
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 4px;
        }
        .now-playing-artist {
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 20px;
        }
        .music-playlist-panel {
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 28px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(8px);
        }
        .playlist-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--card-border);
        }
        .playlist-header h2 {
            font-size: 1rem;
            color: var(--accent);
        }
        .playlist-count {
            font-size: 0.8rem;
            color: var(--text-light);
        }
        .playlist-full-item {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.15s;
            gap: 14px;
        }
        .playlist-full-item:hover { background: rgba(58,180,200,0.1); }
        .playlist-full-item.active { background: rgba(117,198,245,0.23); box-shadow: inset 0 0 0 1px rgba(117,198,245,0.15); }
        .playlist-num {
            width: 24px;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-light);
            flex-shrink: 0;
        }
        .playlist-full-item.active .playlist-num { color: #2d8ec4; }
        .playlist-item-info { flex: 1; min-width: 0; }
        .playlist-item-name {
            font-size: 0.9rem;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .playlist-full-item.active .playlist-item-name { color: #2d8ec4; font-weight: 500; }
        .playlist-item-artist {
            font-size: 0.78rem;
            color: var(--text-light);
            margin-top: 2px;
        }
        .playlist-item-duration {
            font-size: 0.78rem;
            color: var(--text-light);
            flex-shrink: 0;
        }
        .music-articles { margin-top: 24px; }
        @media (max-width: 768px) {
            .music-layout { grid-template-columns: 1fr; }
            .music-player-panel { position: static; }
        }
    </style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="particles" id="particles"></div>

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
    </div>
</nav>

<div class="content-search-wrap">
    <form class="content-search" method="GET" action="music.php">
        <input type="search" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="搜索音乐名称或歌手">
        <button type="submit">搜索</button>
    </form>
</div>

<div style="position:relative;z-index:10;max-width:1200px;margin:0 auto;padding:20px 20px 40px">
    <div class="music-layout">

        <!-- 左：播放器 -->
        <div class="music-player-panel">
            <div class="now-playing-cover" id="disc" style="background:none;overflow:hidden;border-radius:50%">
                <img class="album-art" src="/assets/images/Apple Music.png" alt="专辑封面">
            </div>
            <div class="now-playing-title" id="song-name">暂无音乐</div>
            <div class="now-playing-artist" id="song-artist">去后台添加</div>

            <div class="player-progress" style="margin-bottom:16px">
                <div class="progress-bar">
                    <div class="progress-fill" id="progress-fill"></div>
                </div>
                <div class="time-display">
                    <span id="current-time">0:00</span>
                    <span id="total-time">0:00</span>
                </div>
            </div>

            <div class="player-controls" style="margin-bottom:0">
                <button onclick="prevSong()">⏮</button>
                <button class="play-btn" onclick="togglePlay()" id="play-btn">▶</button>
                <button onclick="nextSong()">⏭</button>
            </div>
            <audio id="audio" preload="metadata"></audio>
        </div>

        <!-- 右：歌单 + 文章 -->
        <div>
            <div class="music-playlist-panel">
                <div class="playlist-header">
                    <h2>🎵 歌单</h2>
                    <span class="playlist-count"><?= count($music_list) ?> 首</span>
                </div>
                <div id="playlist">
                    <?php if(empty($music_list)): ?>
                        <p style="color:var(--text-light);font-size:0.9rem;text-align:center;padding:20px">去后台添加音乐 🎵</p>
                    <?php else: ?>
                        <?php foreach($music_list as $i => $m): ?>
                            <div class="playlist-full-item" onclick="loadSongByIndex(<?= $i ?>); togglePlay(true);" data-index="<?= $i ?>">
                                <span class="playlist-num"><?= $i+1 ?></span>
                                <div class="playlist-item-info">
                                    <div class="playlist-item-name"><?= htmlspecialchars($m['name']) ?></div>
                                    <div class="playlist-item-artist"><?= htmlspecialchars($m['artist'] ?? '') ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if(!empty($posts)): ?>
                <div class="music-articles">
                    <div style="color:rgba(255,255,255,0.7);font-size:0.85rem;margin-bottom:16px;letter-spacing:0.1em">音乐随笔</div>
                    <?php foreach($posts as $post): ?>
                        <article class="post-card">
                            <div class="post-body">
                                <div class="post-meta">
                                    <span class="post-cat music"><?= get_cat_label('music') ?></span>
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
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© <?= date('Y') ?> DuoDuo's Daily · 用心记录每一天</p>
</footer>
<script src="assets/js/main.js"></script>
<script>
    // 覆盖渲染播放列表函数，让页面内的歌单高亮同步
    function renderPlaylist() {
        document.querySelectorAll('.playlist-full-item').forEach((el, i) => {
            el.classList.toggle('active', i === currentIndex);
        });
    }
    function loadSongByIndex(index) {
        loadSong(index);
    }
</script>
</body>
</html>