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
        echo json_encode(['code' => 0, 'data' => [
            'id'       => $post['id'],
            'title'    => $post['title'],
            'content'  => $post['content'],
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
            return [
                'id'       => $m['id'],
                'name'     => $m['name'],
                'artist'   => $m['artist'] ?? '',
                'file_url' => 'https://duoduo.best/' . $m['file_path']
            ];
        }, $list)]);
        break;

    default:
        echo json_encode(['code' => 404, 'msg' => '未知接口']);
}
