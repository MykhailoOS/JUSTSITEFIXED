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
        $stmt = $pdo->prepare('SELECT template FROM templates WHERE id = ? AND status = "active"');
        $stmt->execute([$_GET['id']]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($template) {
            // Increment download count
            $stmt = $pdo->prepare('UPDATE templates SET downloads = downloads + 1 WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            
            echo json_encode(['success' => true, 'html' => $template['template']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Template not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

// Save template to library
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    $name = $input['name'] ?? '';
    $html = $input['html'] ?? '';
    $category = $input['category'] ?? 'general';
    
    if (!$name || !$html) {
        echo json_encode(['success' => false, 'message' => 'Name and HTML are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare('INSERT INTO templates (name, template, category, user_id, status) VALUES (?, ?, ?, ?, "active")');
        $stmt->execute([$name, $html, $category, $uid]);
        
        echo json_encode(['success' => true, 'message' => 'Template saved to library']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error saving template: ' . $e->getMessage()]);
    }
    exit;
}
?>
