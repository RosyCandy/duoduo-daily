<?php
require_once 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
$format = $_GET['format'] ?? 'md';
if (!in_array($format, ['md', 'txt'], true)) {
    http_response_code(400);
    exit('不支持的导出格式');
}

$stmt = $pdo->prepare('SELECT title, content, created_at FROM posts WHERE id=?');
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) {
    http_response_code(404);
    exit('文章不存在');
}

$filename = preg_replace('/[^\p{L}\p{N}_-]+/u', '-', $post['title']) ?: 'article';
$filename .= '.' . $format;
$content = $format === 'md'
    ? '# ' . $post['title'] . "\n\n" . $post['content']
    : $post['title'] . "\n\n" . stripMarkdown($post['content']);

header('Content-Type: text/plain; charset=UTF-8');
header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($filename));
echo $content;

function stripMarkdown($content) {
    $content = preg_replace('/!\[([^\]]*)\]\([^)]*\)/', '$1', $content);
    $content = preg_replace('/\[([^\]]+)\]\([^)]*\)/', '$1', $content);
    $content = preg_replace('/^#{1,6}\s*/m', '', $content);
    $content = preg_replace('/^\s*[-*+]\s+/m', '', $content);
    $content = preg_replace('/^\s*`{3}[^\n]*\n?/m', '', $content);
    $content = preg_replace('/`{3}\s*$/m', '', $content);
    return preg_replace('/[*_~>`]/', '', $content);
}
