<?php
// Simplified AI generate without external API calls
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    require_once __DIR__ . '/../lib/auth.php';
    require_once __DIR__ . '/../lib/db.php';

    start_session_if_needed();
    header('Content-Type: application/json; charset=utf-8');

    // Check if user is logged in
    $uid = current_user_id();
    if (!$uid) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid JSON input']);
        exit;
    }

    $description = trim($input['description'] ?? '');
    $style = trim($input['style'] ?? 'modern');

    if (empty($description)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Description is required']);
        exit;
    }

    // Generate elements based on description keywords
    $elements = [];
    
    // Analyze description for keywords
    $description_lower = strtolower($description);
    
    // Header element
    if (strpos($description_lower, 'лендинг') !== false || strpos($description_lower, 'сайт') !== false) {
        $elements[] = ['type' => 'text', 'content' => 'Добро пожаловать на наш сайт', 'style' => 'header'];
    } elseif (strpos($description_lower, 'магазин') !== false || strpos($description_lower, 'товар') !== false) {
        $elements[] = ['type' => 'text', 'content' => 'Наш интернет-магазин', 'style' => 'header'];
    } else {
        $elements[] = ['type' => 'text', 'content' => 'Заголовок страницы', 'style' => 'header'];
    }
    
    // Description element
    if (strpos($description_lower, 'качеств') !== false) {
        $elements[] = ['type' => 'text', 'content' => 'Мы предлагаем качественные товары и услуги', 'style' => 'subtitle'];
    } elseif (strpos($description_lower, 'доступн') !== false) {
        $elements[] = ['type' => 'text', 'content' => 'Доступные цены для всех', 'style' => 'subtitle'];
    } else {
        $elements[] = ['type' => 'text', 'content' => 'Описание вашего проекта', 'style' => 'subtitle'];
    }
    
    // Button element
    if (strpos($description_lower, 'заказ') !== false || strpos($description_lower, 'купить') !== false) {
        $elements[] = ['type' => 'button', 'content' => 'Заказать сейчас', 'style' => 'button-primary'];
    } elseif (strpos($description_lower, 'каталог') !== false) {
        $elements[] = ['type' => 'button', 'content' => 'Перейти к каталогу', 'style' => 'button-primary'];
    } else {
        $elements[] = ['type' => 'button', 'content' => 'Узнать больше', 'style' => 'button-primary'];
    }
    
    // Additional elements based on style
    if ($style === 'modern') {
        $elements[] = ['type' => 'separator', 'content' => '', 'style' => 'default'];
        $elements[] = ['type' => 'text', 'content' => 'Современный дизайн и удобство использования', 'style' => 'subtitle'];
    } elseif ($style === 'business') {
        $elements[] = ['type' => 'separator', 'content' => '', 'style' => 'default'];
        $elements[] = ['type' => 'text', 'content' => 'Профессиональный подход к каждому клиенту', 'style' => 'subtitle'];
    }

    echo json_encode([
        'ok' => true,
        'elements' => $elements,
        'description' => $description,
        'style' => $style,
        'mode' => 'simple'
    ]);

} catch (Exception $e) {
    error_log("AI Generate Simple Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal server error: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("AI Generate Simple Fatal Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal server error: ' . $e->getMessage()]);
}
?>
