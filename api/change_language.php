<?php
/**
 * Change Language API
 * Handles language switching
 */

// Completely disable all error reporting and output
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);
ini_set('html_errors', 0);

// Start output buffering immediately
ob_start();

// Check for any output before we start
$preOutput = ob_get_contents();
if (!empty($preOutput)) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server configuration error']);
    exit;
}

// Set JSON headers first
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Include required files
    require_once __DIR__ . '/../lib/auth.php';
    require_once __DIR__ . '/../lib/language.php';
    
    // Start session
    start_session_if_needed();
    
    // Clear any output
    ob_clean();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    // Check if language is provided
    if (!isset($_POST['language']) || empty($_POST['language'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Language not provided']);
        exit;
    }
    
    $language = $_POST['language'];
    
    // Validate language
    $availableLanguages = LanguageManager::getAvailableLanguages();
    if (!array_key_exists($language, $availableLanguages)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid language']);
        exit;
    }
    
    // Set language in session
    $_SESSION['language'] = $language;
    
    // Set cookie
    setcookie('language', $language, time() + (30 * 24 * 60 * 60), '/');
    
    // Update database (optional, don't fail if this doesn't work)
    if (isset($_SESSION['user_id'])) {
        try {
            require_once __DIR__ . '/../lib/db.php';
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("UPDATE users SET language = ? WHERE id = ?");
            $stmt->execute([$language, $_SESSION['user_id']]);
        } catch (Exception $e) {
            // Ignore database errors
        }
    }
    
    // Send success response
    $response = [
        'success' => true, 
        'message' => 'Language changed successfully',
        'language' => $language,
        'language_name' => $availableLanguages[$language]['name']
    ];
    
    // Clear any output before sending JSON
    ob_clean();
    echo json_encode($response);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
} catch (Error $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

// Final check - ensure only JSON is output
$finalOutput = ob_get_contents();
ob_end_clean();

// Check if there's any non-JSON output
if (!empty($finalOutput)) {
    // Try to find JSON in the output
    $jsonStart = strpos($finalOutput, '{');
    if ($jsonStart !== false) {
        $jsonPart = substr($finalOutput, $jsonStart);
        echo $jsonPart;
    } else {
        // No JSON found, send error
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server configuration error']);
    }
} else {
    // No output at all, send error
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No response generated']);
}
?>