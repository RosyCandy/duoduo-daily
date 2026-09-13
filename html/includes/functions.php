<?php
function get_posts($limit = 8, $offset = 0, $cat = '', $search = '') {
    global $pdo;
    $limit = (int)$limit;
    $offset = (int)$offset;
    $search = trim((string)$search);

    $where = [];
    $params = [];

    if ($cat) {
        $where[] = 'category = ?';
        $params[] = $cat;
    }

    if ($search !== '') {
        $where[] = '(title LIKE ? OR content LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $sql = 'SELECT * FROM posts';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';

    $stmt = $pdo->prepare($sql);
    $index = 1;
    foreach ($params as $value) {
        $stmt->bindValue($index++, $value);
    }
    $stmt->bindValue($index++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($index++, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_post($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_post_count($cat = '', $search = '') {
    global $pdo;
    $search = trim((string)$search);
    $where = [];
    $params = [];

    if ($cat) {
        $where[] = 'category = ?';
        $params[] = $cat;
    }

    if ($search !== '') {
        $where[] = '(title LIKE ? OR content LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $sql = 'SELECT COUNT(*) FROM posts';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $stmt = $pdo->prepare($sql);
    $index = 1;
    foreach ($params as $value) {
        $stmt->bindValue($index++, $value);
    }
    $stmt->execute();
    return $stmt->fetchColumn();
}

function get_comment_count() {
    global $pdo;
    return $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
}

function get_comments($post_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id=? ORDER BY created_at ASC");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll();
}

function get_music_list($search = '') {
    global $pdo;
    $search = trim((string)$search);
    if ($search === '') {
        return $pdo->query("SELECT * FROM music ORDER BY sort_order ASC, id ASC")->fetchAll();
    }
    $stmt = $pdo->prepare('SELECT * FROM music WHERE name LIKE ? OR artist LIKE ? ORDER BY sort_order ASC, id ASC');
    $term = '%' . $search . '%';
    $stmt->execute([$term, $term]);
    return $stmt->fetchAll();
}

function get_cat_label($cat) {
    $labels = ['life'=>'生活','tech'=>'技术','music'=>'音乐','essay'=>'随笔'];
    return $labels[$cat] ?? '随笔';
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_admin() {
    return !empty($_SESSION['admin']);
}

function require_user() {
    if (!current_user()) {
        header('Location: /account.php');
        exit;
    }
}

function require_admin() {
    if (!is_admin()) {
        header('Location: /admin/index.php');
        exit;
    }
}

function get_user_posts($user_id) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE user_id=? ORDER BY created_at DESC');
    $stmt->execute([(int)$user_id]);
    return $stmt->fetchAll();
}

function get_site_profile() {
    global $pdo;
    return $pdo->query('SELECT * FROM site_profile WHERE id=1')->fetch();
}
