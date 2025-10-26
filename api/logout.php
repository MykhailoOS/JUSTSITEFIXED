<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Handle CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    http_response_code(204);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
    exit;
}

try {
    // Start session if needed
    start_session_if_needed();
    
    // Log the logout action
    $userId = current_user_id();
    if ($userId) {
        error_log("User $userId logged out via API at " . date('Y-m-d H:i:s'));
    }
    
    // Perform logout
    logout_user();
    
    // Clear any additional session data
    if (isset($_SESSION)) {
        $_SESSION = array();
    }
    
    echo json_encode([
        'ok' => true,
        'message' => 'Successfully logged out',
        'redirect_url' => 'login.php?logout=success'
    ]);
    
} catch (Throwable $e) {
    error_log('Logout API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal server error']);
}
?>
