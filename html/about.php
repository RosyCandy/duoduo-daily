<?php
require_once 'includes/db.php';
require_once 'includes/site.php';
$profile = get_site_profile();
?>
<!DOCTYPE html>
<html lang="zh-CN"><head><?php blog_head("关于 · DuoDuo's Daily"); ?></head>
<body class="blog-app inner-page" id="top">
<?php blog_nav('about'); ?>
<div class="page-intro"><span class="eyebrow">BEHIND THE JOURNAL</span><h1>你好，欢迎来到我的小角落。</h1><p>在文字和旋律之间，认识一下彼此。</p></div>
<main class="about-layout" id="journal"><img class="about-banner" src="assets/images/hero-grass.jpg" alt="阳光下的草地与朋友" width="2731" height="1701"><div class="about-copy"><h2>我是 <?= blog_escape($profile['display_name']) ?>。</h2><p><?= blog_escape($profile['description']) ?></p><p>这里是我记录日常生活、技术学习和音乐感悟的地方。</p><p>喜欢音乐，喜欢折腾代码，偶尔写写生活感悟。那些值得记住的小事、终于解决的问题、循环播放的歌，都想在这里留下一点痕迹。</p><p>欢迎你常来坐坐，也欢迎在文章下面留下你的想法。🌿</p><div class="about-links"><a href="index.php#journal">读读我的日常 →</a><a href="music.php">听听我的歌单 ♫</a></div></div></main>
<?php blog_footer(); ?>
</body></html>
