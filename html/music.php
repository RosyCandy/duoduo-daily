<?php
require_once 'includes/db.php';
require_once 'includes/site.php';
$search = trim(is_string($_GET['q'] ?? '') ? ($_GET['q'] ?? '') : '');
$music_list = get_music_list($search);
$posts = get_posts(20, 0, 'music', $search);
?>
<!DOCTYPE html>
<html lang="zh-CN"><head><?php blog_head("音乐 · DuoDuo's Daily"); ?></head>
<body class="blog-app inner-page music-view" id="top">
<?php blog_nav('music'); ?>
<div class="page-intro"><span class="eyebrow">A SOUNDTRACK FOR EVERY DAY</span><h1>让日常，有一点旋律。</h1><p>听喜欢的歌，也把心情留在这里。</p></div>
<div class="search-dock compact-search"><form class="journal-search" action="music.php#journal" method="get" role="search"><?= blog_icon('search') ?><input type="search" name="q" value="<?= blog_escape($search) ?>" placeholder="搜索音乐名称或歌手" aria-label="搜索音乐"><button type="submit">搜索<?= blog_icon('arrow') ?></button></form></div>
<main class="music-page" id="journal"><div class="music-grid">
<?php blog_player(); ?>
<section class="music-library" aria-label="音乐歌单"><div class="music-heading"><h2><?= $search ? '搜索「' . blog_escape($search) . '」' : '我的歌单' ?></h2><span><?= count($music_list) ?> 首音乐<?php if ($search): ?> · <a href="music.php#journal">全部歌单</a><?php endif; ?></span></div>
<div class="music-track-list" data-full-playlist tabindex="0" role="region" aria-label="可滚动的歌单"><?php if (!$music_list): ?><div class="blank-panel"><p>没有找到这首歌，换一个关键词试试。</p></div><?php else: foreach ($music_list as $i => $track): ?><button type="button" class="playlist-item" onclick="loadSong(<?= $i ?>);togglePlay(true)"><span class="track-number"><?= sprintf('%02d', $i + 1) ?></span><span class="track-info"><span><?= blog_escape($track['name']) ?></span><small><?= blog_escape($track['artist']) ?></small></span><span class="track-play">▶</span></button><?php endforeach; endif; ?></div>
</section></div>
<?php if ($posts): ?><div class="music-heading" style="margin-top:30px"><h2>音乐随笔</h2></div><?php foreach ($posts as $post): ?><article class="story-card"><div class="story-body"><div class="story-meta"><time><?= date('Y.m.d', strtotime($post['created_at'])) ?></time></div><h3><a href="post.php?id=<?= (int)$post['id'] ?>"><?= blog_escape($post['title']) ?></a></h3><p class="story-excerpt"><?= blog_escape(mb_substr(strip_tags($post['content']), 0, 100)) ?></p><a class="text-link" href="post.php?id=<?= (int)$post['id'] ?>">阅读全文 →</a></div></article><?php endforeach; endif; ?>
</main>
<script id="music-data" type="application/json"><?= json_encode($music_list, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
<?php blog_footer(); ?>
</body></html>
