<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json');

// Check if user is logged in
$uid = current_user_id();
if (!$uid) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$pdo = DatabaseConnectionProvider::getConnection();

// Get single template
if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare('SELECT template FROM templates WHERE id = ?');
        $stmt->execute([$_GET['id']]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($template) {
            echo json_encode(['success' => true, 'html' => $template['template']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Template not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

// Get all templates
try {
    $stmt = $pdo->prepare('SELECT id, name, template FROM templates ORDER BY created_at DESC');
    $stmt->execute();
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'templates' => $templates]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
