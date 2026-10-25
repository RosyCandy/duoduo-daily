<?php
require_once 'includes/db.php';
require_once 'includes/site.php';
$profile = get_site_profile();
$index = blog_post_index();
$tags = blog_tag_counts($index);
$view = in_array($_GET['view'] ?? '', ['categories', 'tags', 'archives'], true) ? $_GET['view'] : 'home';
$cat = in_array($_GET['cat'] ?? '', ['life', 'tech', 'music', 'essay'], true) ? $_GET['cat'] : '';
$search = trim(is_string($_GET['q'] ?? '') ? ($_GET['q'] ?? '') : '');
$tag = trim(is_string($_GET['tag'] ?? '') ? ($_GET['tag'] ?? '') : '');
$ids = $tag !== '' ? array_column(array_filter($index, fn($post) => in_array($tag, blog_topics($post), true)), 'id') : null;
$total = (int)get_post_count($cat, $search, $ids);
$per_page = 6;
$total_pages = max(1, (int)ceil($total / $per_page));
$page = max(1, min($total_pages, (int)($_GET['page'] ?? 1)));
$posts = $view === 'home' ? get_posts($per_page, ($page - 1) * $per_page, $cat, $search, $ids) : [];
$is_home = $view === 'home' && !$cat && $search === '' && $tag === '' && $page === 1;
$titles = ['categories' => '文章分类', 'tags' => '标签漫游', 'archives' => '时光归档', 'home' => '最新文章'];
$title = $cat ? get_cat_label($cat) . '里的小记录' : ($tag ? '关于「' . $tag . '」' : ($search ? '搜索「' . $search . '」' : $titles[$view]));
$category_counts = array_count_values(array_column($index, 'category'));
function blog_page_url($page) {
    $query = array_intersect_key($_GET, array_flip(['cat', 'q', 'tag']));
    $query['page'] = $page;
    return 'index.php?' . http_build_query($query) . '#journal';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head><?php blog_head($is_home ? "DuoDuo's Daily · 记录生活的点点滴滴" : $title . " · DuoDuo's Daily"); ?>
<meta name="description" content="DuoDuo 的日常小站，记录生活、技术学习与音乐感悟。">
<?php if ($is_home): ?><link rel="preload" as="image" href="assets/images/hero-grass.jpg"><?php endif; ?>
</head>
<body class="blog-app <?= $is_home ? 'home-page' : 'inner-page' ?>" id="top">
<?php blog_nav($view); ?>
<?php if ($is_home): ?>
<section class="home-hero" aria-label="首页封面">
    <img class="hero-image" src="assets/images/hero-grass.jpg" alt="少女们躺在开满小花的绿色草地上" width="2731" height="1701" fetchpriority="high">
    <div class="hero-shade"></div>
    <div class="hero-copy"><span class="hero-kicker">LIFE, NOTES & MELODIES</span><h1>DuoDuo’s Daily<span>.</span></h1><p>记录生活的点点滴滴</p><span class="hero-caption">生活的浪漫，藏在每一个平凡的日子里。</span></div>
    <a class="hero-scroll" href="#journal"><span>往下翻，是最近的日常</span><span class="scroll-line"></span></a>
    <svg class="hero-wave wave-back" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 45C280 135 420 0 730 65s470 40 710-20v75H0Z"/></svg>
    <svg class="hero-wave" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true"><path d="M0 42C250 2 410 100 710 65S1180 8 1440 45v55H0Z"/></svg>
</section>
<?php else: ?>
<div class="page-intro"><span class="eyebrow">DUODUO’S JOURNAL</span><h1><?= blog_escape($title) ?></h1><p>把走过的路、听过的歌，和偶然的灵感留在这里。</p></div>
<?php endif; ?>
<div class="search-dock <?= $is_home ? '' : 'compact-search' ?>">
    <form class="journal-search" method="get" action="index.php#journal" role="search">
        <?= blog_icon('search') ?>
        <?php if ($cat): ?><input type="hidden" name="cat" value="<?= blog_escape($cat) ?>"><?php endif; ?>
        <?php if ($tag): ?><input type="hidden" name="tag" value="<?= blog_escape($tag) ?>"><?php endif; ?>
        <input type="search" name="q" value="<?= blog_escape($search) ?>" aria-label="搜索文章" placeholder="寻找一篇文章，一段日常，或一个灵感…">
        <button type="submit">搜索<?= blog_icon('arrow') ?></button>
    </form>
</div>
<div class="journal-layout" id="journal">
    <?php blog_profile($profile, $index, $view); ?>
    <main class="journal-feed">
        <div class="feed-heading"><div><span class="eyebrow"><?= ['home' => 'THE LATEST STORIES', 'categories' => 'EXPLORE BY CATEGORY', 'tags' => 'CONNECTED THOUGHTS', 'archives' => 'A WALK THROUGH TIME'][$view] ?></span><h2><?= blog_escape($title) ?><span><?= $view === 'tags' ? count($tags) . ' 个标签' : ($view === 'categories' ? count($category_counts) . ' 个分类' : $total . ' 篇记录') ?></span></h2></div><?= blog_icon($view === 'home' ? 'leaf' : ($view === 'categories' ? 'grid' : ($view === 'tags' ? 'tag' : 'archive'))) ?></div>
        <?php if ($view === 'home'): ?>
        <nav class="category-tabs" aria-label="按分类阅读"><?php foreach (['' => '全部', 'life' => '生活', 'tech' => '技术', 'essay' => '随笔', 'music' => '音乐'] as $key => $label): ?><a href="index.php<?= $key ? '?cat=' . $key : '' ?>#journal"<?= $cat === $key && !$tag && !$search ? ' aria-current="page"' : '' ?>><?= $label ?><?php if ($key): ?><small><?= $category_counts[$key] ?? 0 ?></small><?php endif; ?></a><?php endforeach; ?></nav>
        <?php if ($tag || $search): ?><div class="filter-note">找到 <?= $total ?> 篇文章<a href="index.php#journal">清除筛选 ×</a></div><?php endif; ?>
        <?php if (!$posts): ?><div class="blank-panel"><?= blog_icon('search') ?><h3>这里暂时安静了一点</h3><p><?= $search || $tag || $cat ? '换一个关键词，或看看全部文章吧。' : '新的日常，正在慢慢发生。' ?></p><a class="text-link" href="index.php#journal">浏览全部文章 →</a></div><?php endif; ?>
        <?php foreach ($posts as $i => $post): $plain = preg_replace('/\s+/u', ' ', strip_tags(preg_replace('/!\[[^\]]*\]\([^)]*\)|[#*>`]/u', '', $post['content']))); ?>
        <article class="story-card <?= !empty($post['cover']) ? 'has-cover' : '' ?>">
            <div class="story-body"><div class="story-meta"><a href="index.php?cat=<?= blog_escape($post['category']) ?>#journal"><?= get_cat_label($post['category']) ?></a><span>·</span><time datetime="<?= date('Y-m-d', strtotime($post['created_at'])) ?>"><?= date('Y.m.d', strtotime($post['created_at'])) ?></time><?php if ($is_home && $i === 0): ?><span class="new-label">最新</span><?php endif; ?></div>
            <h3><a href="post.php?id=<?= (int)$post['id'] ?>"><?= blog_escape($post['title']) ?></a></h3><p class="story-excerpt"><?= blog_escape(mb_substr($plain, 0, 115)) ?><?= mb_strlen($plain) > 115 ? '…' : '' ?></p>
            <div class="story-bottom"><div class="story-tags"><?php foreach (blog_topics($post) as $topic): ?><a href="index.php?tag=<?= rawurlencode($topic) ?>#journal"># <?= blog_escape($topic) ?></a><?php endforeach; ?></div><a class="story-read" href="post.php?id=<?= (int)$post['id'] ?>" aria-label="阅读：<?= blog_escape($post['title']) ?>"><?= blog_icon('arrow') ?></a></div></div>
            <?php if (!empty($post['cover'])): ?><a class="story-cover" href="post.php?id=<?= (int)$post['id'] ?>" aria-label="阅读：<?= blog_escape($post['title']) ?>"><img src="<?= blog_escape($post['cover']) ?>" alt="" loading="lazy" width="240" height="240"></a><?php endif; ?>
        </article>
        <?php endforeach; ?>
        <?php if ($total_pages > 1): ?><nav class="journal-pagination" aria-label="文章分页"><?php if ($page > 1): ?><a href="<?= blog_escape(blog_page_url($page - 1)) ?>">← 上一页</a><?php endif; ?><?php for ($n = 1; $n <= $total_pages; $n++): ?><a href="<?= blog_escape(blog_page_url($n)) ?>"<?= $n === $page ? ' aria-current="page"' : '' ?>><?= $n ?></a><?php endfor; ?><?php if ($page < $total_pages): ?><a href="<?= blog_escape(blog_page_url($page + 1)) ?>">下一页 →</a><?php endif; ?></nav><?php endif; ?>
        <?php elseif ($view === 'archives'): ?>
        <section class="archive-panel"><p class="panel-intro">一共 <?= count($index) ?> 篇记录。时间会走远，文字会留下。</p><?php $year = ''; foreach ($index as $post): $post_year = date('Y', strtotime($post['created_at'])); if ($year !== $post_year): $year = $post_year; ?><h3 class="archive-year"><?= $year ?><span></span></h3><?php endif; ?><a class="archive-entry" href="post.php?id=<?= (int)$post['id'] ?>"><time><?= date('m / d', strtotime($post['created_at'])) ?></time><span class="timeline-dot"></span><span><?= blog_escape($post['title']) ?></span><span class="archive-category"><?= get_cat_label($post['category']) ?></span></a><?php endforeach; ?></section>
        <?php elseif ($view === 'categories'): ?>
        <div class="category-grid"><?php foreach (['life' => '日子里的小美好', 'tech' => '学习、探索与折腾', 'essay' => '把想法写下来', 'music' => '总有一首歌懂你'] as $key => $desc): ?><a class="category-tile category-<?= $key ?>" href="index.php?cat=<?= $key ?>#journal"><?= blog_icon($key === 'tech' ? 'grid' : ($key === 'music' ? 'music' : 'leaf')) ?><h3><?= get_cat_label($key) ?></h3><p><?= $desc ?></p><span><?= $category_counts[$key] ?? 0 ?> 篇文章 <?= blog_icon('arrow') ?></span></a><?php endforeach; ?></div>
        <?php else: ?>
        <section class="tags-panel"><p class="panel-intro">沿着一个标签，遇见相似的想法。</p><div class="tag-cloud large-tags"><?php foreach ($tags as $topic => $count): ?><a href="index.php?tag=<?= rawurlencode($topic) ?>#journal"><?= blog_escape($topic) ?><small><?= $count ?></small></a><?php endforeach; ?></div>
        <?php if ($tags): ?><div class="tag-constellation" aria-label="标签漫游"><div class="constellation-toolbar"><span><i></i> 标签正在漫游</span><button type="button" data-tag-pause aria-pressed="false">暂停漫游</button></div>
        <canvas data-tag-garden role="img" aria-label="标签独立漂移；鼠标靠近时连线，点击圆点可阅读对应文章"></canvas>
        <script type="application/json" id="tag-garden-data"><?= json_encode(array_map(fn($topic) => ['name' => (string)$topic, 'url' => 'index.php?tag=' . rawurlencode($topic) . '#journal'], array_keys($tags)), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
        </div><?php endif; ?></section>
        <?php endif; ?>
    </main>
    <?php blog_rail($index); ?>
</div>
<?php blog_footer(true); ?>
<?php if ($view === 'tags'): ?><script src="assets/js/tag-garden.js?v=20261022-2"></script><?php endif; ?>
</body>
</html>
