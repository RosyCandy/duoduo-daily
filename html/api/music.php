<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');
echo json_encode(get_music_list());
