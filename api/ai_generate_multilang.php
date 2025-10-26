<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user
ini_set('log_errors', 1); // Log errors to server log

try {
    require_once __DIR__ . '/../lib/language.php';
    require_once __DIR__ . '/ai_content_generator.php';
    
    session_start();
    LanguageManager::init();
    header('Content-Type: application/json; charset=utf-8');

    // Simple auth check - just check if session exists
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
        exit;
    }

    // Get current user language
    $currentLanguage = LanguageManager::getCurrentLanguage();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
        exit;
    }

    // Get input parameters
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $description = trim($input['description'] ?? '');
    $style = trim($input['style'] ?? 'modern');

    if (empty($description)) {
        echo json_encode(['ok' => false, 'error' => 'Description is required']);
        exit;
    }

    // Generate content using multilingual generator
    $elements = AIContentGenerator::generateContent($description, $style, $currentLanguage);
    
    // Format response
    $response = [
        'ok' => true,
        'elements' => $elements,
        'language' => $currentLanguage,
        'style' => $style,
        'message' => 'Content generated successfully'
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("AI Generate Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    http_response_code(500);
    echo json_encode([
        'ok' => false, 
        'error' => 'Internal server error',
        'debug' => $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("AI Generate Fatal Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    http_response_code(500);
    echo json_encode([
        'ok' => false, 
        'error' => 'Fatal error',
        'debug' => $e->getMessage()
    ]);
}
?>
