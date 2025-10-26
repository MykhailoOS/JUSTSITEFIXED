<?php
// Simple language change API with database update
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['language'])) {
    echo json_encode(['success' => false, 'message' => 'Language not provided']);
    exit;
}

$language = $_POST['language'];
$validLanguages = ['ru', 'en', 'ua', 'pl'];

if (!in_array($language, $validLanguages)) {
    echo json_encode(['success' => false, 'message' => 'Invalid language']);
    exit;
}

// Set language in session and cookie
$_SESSION['language'] = $language;
setcookie('language', $language, time() + (30 * 24 * 60 * 60), '/');

// Update database
try {
    require_once __DIR__ . '/../lib/db.php';
    $pdo = DatabaseConnectionProvider::getConnection();
    $stmt = $pdo->prepare("UPDATE users SET language = ? WHERE id = ?");
    $stmt->execute([$language, $_SESSION['user_id']]);
} catch (Exception $e) {
    // Log error but don't fail the request
    error_log("Language update DB error: " . $e->getMessage());
}

echo json_encode([
    'success' => true,
    'message' => 'Language changed successfully',
    'language' => $language
]);
?>
