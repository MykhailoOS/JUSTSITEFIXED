<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_auth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); echo 'Bad Request'; exit; }

$uid = current_user_id();
$pdo = DatabaseConnectionProvider::getConnection();
$stmt = $pdo->prepare('SELECT html FROM projects WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $uid]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); echo 'Not found'; exit; }

header('Content-Type: text/html; charset=utf-8');
echo $row['html'];


