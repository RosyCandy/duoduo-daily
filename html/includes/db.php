<?php
define('DB_HOST', 'mysql');
define('DB_USER', 'DuoDuo');
define('DB_PASS', 'DuoDuo1103-mysql');
define('DB_NAME', 'blog_db');

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch(PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}
$pdo->exec("CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    category VARCHAR(50) DEFAULT 'life',
    cover VARCHAR(500) DEFAULT '',
    user_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    author VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS music (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    artist VARCHAR(255) DEFAULT '',
    file_path VARCHAR(500) NOT NULL,
    sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS site_profile (
    id TINYINT PRIMARY KEY,
    display_name VARCHAR(100) NOT NULL DEFAULT 'DuoDuo',
    description VARCHAR(255) NOT NULL DEFAULT '记录生活的点点滴滴',
    avatar VARCHAR(500) NOT NULL DEFAULT '/assets/images/avatar.jpeg',
    qq VARCHAR(100) NOT NULL DEFAULT '204575829',
    email VARCHAR(255) NOT NULL DEFAULT 'rosyhazes@zohomail.com',
    link_github VARCHAR(500) NOT NULL DEFAULT 'https://github.com/RosyCandy',
    link_weibo VARCHAR(500) NOT NULL DEFAULT 'https://weibo.com/u/8218386428',
    link_cnblogs VARCHAR(500) NOT NULL DEFAULT 'https://www.cnblogs.com/erieanna',
    link_csdn VARCHAR(500) NOT NULL DEFAULT 'https://blog.csdn.net/2301_80939691?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("INSERT IGNORE INTO site_profile (id) VALUES (1)");

// Keep installations created before multi-user support compatible.
$post_columns = $pdo->query("SHOW COLUMNS FROM posts LIKE 'user_id'")->fetch();
if (!$post_columns) $pdo->exec("ALTER TABLE posts ADD COLUMN user_id INT DEFAULT NULL AFTER cover");
$music_columns = $pdo->query("SHOW COLUMNS FROM music LIKE 'user_id'")->fetch();
if (!$music_columns) $pdo->exec("ALTER TABLE music ADD COLUMN user_id INT DEFAULT NULL AFTER sort_order");

$pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$check = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
if($check == 0) {
    $pdo->exec("INSERT INTO admin_users (username, password) VALUES ('DuoDuo', '".password_hash('DuoDuo1103-blog', PASSWORD_DEFAULT)."')");
}
