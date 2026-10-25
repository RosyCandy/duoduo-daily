<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/db.php';
require_once '../includes/functions.php';

$type = $_GET['type'] ?? 'posts';

switch ($type) {

    case 'profile':
        echo json_encode(['code' => 0, 'data' => [
            'name'         => 'DuoDuo',
            'bio'          => '记录生活的点点滴滴',
            'avatar'       => 'https://duoduo.best/assets/images/avatar.jpg',
            'postCount'    => (int)get_post_count(),
            'commentCount' => (int)get_comment_count()
        ]]);
        break;

    case 'posts':
        $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $cat    = $_GET['cat'] ?? '';
        $limit  = 10;
        $offset = ($page - 1) * $limit;
        $posts  = get_posts($limit, $offset, $cat);
        $result = array_map(function($p) {
            return [
                'id'       => $p['id'],
                'title'    => $p['title'],
                'excerpt'  => mb_substr(strip_tags($p['content']), 0, 100),
                'category' => $p['category'],
                'cover'    => $p['cover'] ?? '',
                'date'     => date('Y-m-d', strtotime($p['created_at']))
            ];
        }, $posts);
        echo json_encode(['code' => 0, 'data' => $result]);
        break;

    case 'post':
        $id   = (int)($_GET['id'] ?? 0);
        $post = get_post($id);
        if (!$post) { echo json_encode(['code' => 404]); break; }
        $comments = get_comments($id);
        require_once '../includes/Parsedown.php';
        $parsedown = new Parsedown();
        $parsedown->setBreaksEnabled(true);
        // rich-text 只认内联样式，这里给图片/代码块/引用注入基础样式
        $html = $parsedown->text(trim($post['content']));
        $html = str_replace('<img ', '<img style="max-width:100%;height:auto;border-radius:8px;" ', $html);
        $html = str_replace('<pre><code', '<pre style="background:rgba(0,0,0,0.06);padding:12px;border-radius:8px;overflow-x:auto;font-size:13px;"><code', $html);
        $html = str_replace('<blockquote>', '<blockquote style="margin:8px 0;padding:6px 12px;border-left:4px solid #7aa7b5;background:rgba(122,167,181,0.08);color:#555;">', $html);
        echo json_encode(['code' => 0, 'data' => [
            'id'       => $post['id'],
            'title'    => $post['title'],
            'content'  => $post['content'],
            'html'     => $html,
            'category' => $post['category'],
            'cover'    => $post['cover'] ?? '',
            'date'     => date('Y-m-d', strtotime($post['created_at'])),
            'comments' => array_map(function($c) {
                return [
                    'id'      => $c['id'],
                    'author'  => $c['author'],
                    'content' => $c['content'],
                    'date'    => date('Y-m-d H:i', strtotime($c['created_at']))
                ];
            }, $comments)
        ]]);
        break;

    case 'comments':
        $stmt = $pdo->query("SELECT c.id, c.author, c.content, c.created_at, p.title as post_title, p.id as post_id
                             FROM comments c LEFT JOIN posts p ON c.post_id = p.id
                             ORDER BY c.created_at DESC LIMIT 5");
        $comments = $stmt->fetchAll();
        echo json_encode(['code' => 0, 'data' => array_map(function($c) {
            return [
                'id'        => $c['id'],
                'author'    => $c['author'],
                'content'   => $c['content'],
                'postTitle' => $c['post_title'],
                'postId'    => $c['post_id'],
                'date'      => date('Y-m-d', strtotime($c['created_at']))
            ];
        }, $comments)]);
        break;

    case 'comment_post':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['code' => 405]); break; }
        $input   = json_decode(file_get_contents('php://input'), true);
        $post_id = (int)($input['post_id'] ?? 0);
        $content = trim($input['content'] ?? '');
        if (!$post_id || !$content) { echo json_encode(['code' => 400, 'msg' => '参数错误']); break; }
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, author, content) VALUES (?, '匿名', ?)");
        $stmt->execute([$post_id, $content]);
        echo json_encode(['code' => 0, 'msg' => '评论成功']);
        break;

    case 'music':
        $list = $pdo->query("SELECT * FROM music WHERE source='mini' ORDER BY sort_order ASC, id ASC")->fetchAll();
        echo json_encode(['code' => 0, 'data' => array_map(function($m) {
            // 路径逐段编码：歌名含中文/空格/括号，不编码小程序音频组件会加载失败
            $encoded = implode('/', array_map('rawurlencode', explode('/', $m['file_path'])));
            $data = [
                'id'       => $m['id'],
                'name'     => $m['name'],
                'artist'   => $m['artist'] ?? '',
                'file_url' => 'https://duoduo.best/' . $encoded
            ];
            // flac/ogg 在部分机型不支持，优先给小程序转码后的 m4a
            if (preg_match('#^assets/music/(.+)\.(flac|ogg)$#i', $m['file_path'], $mm)) {
                $m4aFile = __DIR__ . '/../assets/music_m4a/' . $mm[1] . '.m4a';
                if (file_exists($m4aFile)) {
                    $data['file_url_m4a'] = 'https://duoduo.best/assets/music_m4a/' . rawurlencode($mm[1]) . '.m4a';
                }
            }
            return $data;
        }, $list)]);
        break;

    default:
        echo json_encode(['code' => 404, 'msg' => '未知接口']);
}
