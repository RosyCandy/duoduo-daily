<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once '../includes/db.php';
require_once '../includes/functions.php';

// ── 验证 Token ──────────────────────────────
function check_token($pdo) {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $auth);
    if (!$token) {
        echo json_encode(['code' => 401, 'message' => '未登录']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM admin_tokens WHERE token=? AND expires_at > NOW()");
    $stmt->execute([$token]);
    if (!$stmt->fetch()) {
        echo json_encode(['code' => 401, 'message' => 'token已过期']);
        exit;
    }
}

$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {

    // ── 获取文章列表 ──────────────────────────
    case 'get_posts':
        check_token($pdo);
        $posts = $pdo->query("SELECT id, title, category, created_at FROM posts ORDER BY created_at DESC")->fetchAll();
        echo json_encode(['code' => 200, 'data' => $posts]);
        break;

    case 'get_post':
        check_token($pdo);
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { echo json_encode(['code' => 400, 'message' => '参数错误']); break; }
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id=?");
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        echo json_encode(['code' => 200, 'data' => $post]);
        break;

    // ── 发布 / 编辑文章 ───────────────────────
    case 'save_post':
        check_token($pdo);
        $id       = (int)($input['id'] ?? 0);
        $title    = trim($input['title'] ?? '');
        $category = trim($input['category'] ?? '生活');
        $content  = trim($input['content'] ?? '');
        $cover    = trim($input['cover'] ?? '');
        if (!$title || !$content) {
            echo json_encode(['code' => 400, 'message' => '标题和内容不能为空']); break;
        }
        if ($id) {
            $stmt = $pdo->prepare("UPDATE posts SET title=?, category=?, content=?, cover=? WHERE id=?");
            $stmt->execute([$title, $category, $content, $cover, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO posts (title, category, content, cover) VALUES (?,?,?,?)");
            $stmt->execute([$title, $category, $content, $cover]);
        }
        echo json_encode(['code' => 200, 'message' => '保存成功']);
        break;

    // ── 删除文章 ──────────────────────────────
    case 'delete_post':
        check_token($pdo);
        $id = (int)($input['id'] ?? 0);
        if (!$id) { echo json_encode(['code' => 400, 'message' => '参数错误']); break; }
        $pdo->prepare("DELETE FROM posts WHERE id=?")->execute([$id]);
        echo json_encode(['code' => 200, 'message' => '删除成功']);
        break;

    // ── 获取音乐列表 ──────────────────────────
    case 'get_music':
        check_token($pdo);
        $list = $pdo->query("SELECT id, name, artist, file_path FROM music ORDER BY id DESC")->fetchAll();
        $list = array_map(function($m) {
            $m['file_url'] = 'https://duoduo.best/' . $m['file_path'];
            return $m;
        }, $list);
        echo json_encode(['code' => 200, 'data' => $list]);
        break;

    // ── 添加音乐（URL方式，不上传文件）─────────
    case 'add_music':
        check_token($pdo);
        $name      = trim($input['name'] ?? '');
        $artist    = trim($input['artist'] ?? '');
        $file_path = trim($input['file_path'] ?? '');
        if (!$name || !$file_path) {
            echo json_encode(['code' => 400, 'message' => '歌名和文件路径不能为空']); break;
        }
        $stmt = $pdo->prepare("INSERT INTO music (name, artist, file_path) VALUES (?,?,?)");
        $stmt->execute([$name, $artist, $file_path]);
        echo json_encode(['code' => 200, 'message' => '添加成功']);
        break;

    // ── 删除音乐 ──────────────────────────────
    case 'delete_music':
        check_token($pdo);
        $id = (int)($input['id'] ?? 0);
        if (!$id) { echo json_encode(['code' => 400, 'message' => '参数错误']); break; }
        $pdo->prepare("DELETE FROM music WHERE id=?")->execute([$id]);
        echo json_encode(['code' => 200, 'message' => '删除成功']);
        break;

    default:
        echo json_encode(['code' => 404, 'message' => '未知操作']);
}
