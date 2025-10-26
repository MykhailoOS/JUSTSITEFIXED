<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$uid = current_user_id();
if (!$uid) {
    echo json_encode(['ok' => false]);
    exit;
}

$pdo = DatabaseConnectionProvider::getConnection();
$stmt = $pdo->prepare('SELECT id, email, name FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();
if (!$user) {
    echo json_encode(['ok' => false]);
    exit;
}
echo json_encode(['ok' => true, 'user' => $user]);


