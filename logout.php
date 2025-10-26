<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/config.php';

// Start session if needed
start_session_if_needed();

// Log the logout action
$userId = current_user_id();
if ($userId) {
    error_log("User $userId logged out at " . date('Y-m-d H:i:s'));
}

// Perform logout
logout_user();

// Clear any additional session data
if (isset($_SESSION)) {
    $_SESSION = array();
}

// Set cache headers to prevent back button issues
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect to login page with success message
header('Location: login.php?logout=success');
exit;
