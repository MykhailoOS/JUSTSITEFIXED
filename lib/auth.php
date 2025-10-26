<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

function start_session_if_needed(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function current_user_id(): ?int {
    start_session_if_needed();
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function require_auth(): void {
    if (!current_user_id()) {
        header('Location: login.php');
        exit;
    }
}

function find_user_by_email(PDO $pdo, string $email): ?array {
    $stmt = $pdo->prepare('SELECT id, email, password_hash, name, created_at FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function create_user(PDO $pdo, string $email, string $password, string $name = ''): int {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, name, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$email, $hash, $name]);
    return (int)$pdo->lastInsertId();
}

function verify_user_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function login_user(int $userId): void {
    start_session_if_needed();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void {
    start_session_if_needed();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}


