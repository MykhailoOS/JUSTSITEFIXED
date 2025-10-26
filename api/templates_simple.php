<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';

// Check if user is logged in
$uid = current_user_id();
if (!$uid) {
    header('Location: ../login.php');
    exit;
}

// Simple admin check
$pdo = DatabaseConnectionProvider::getConnection();
$stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();

$isAdmin = $user && (
    strpos($user['email'], 'admin') !== false || 
    $user['email'] === 'admin@justsite.com' ||
    $uid == 1
);

if (!$isAdmin) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new template
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? 'landing';
    $template = $_POST['template'] ?? '';
    $sample_data = $_POST['sample_data'] ?? '';
    
    if ($name && $template) {
        try {
            $stmt = $pdo->prepare('INSERT INTO templates (name, category, template, sample_data, user_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $category, $template, $sample_data, $uid]);
            header('Location: ../templates_simple.php?success=1');
            exit;
        } catch (Exception $e) {
            header('Location: ../templates_simple.php?error=1');
            exit;
        }
    }
}

if (isset($_GET['delete'])) {
    // Delete template
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare('DELETE FROM templates WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        header('Location: ../templates_simple.php?deleted=1');
        exit;
    } catch (Exception $e) {
        header('Location: ../templates_simple.php?error=1');
        exit;
    }
}

// Get templates for constructor
if (isset($_GET['action']) && $_GET['action'] === 'list') {
    header('Content-Type: application/json');
    
    try {
        $stmt = $pdo->prepare('SELECT id, name, category, template, sample_data FROM templates ORDER BY created_at DESC');
        $stmt->execute();
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process templates
        foreach ($templates as &$template) {
            if ($template['sample_data']) {
                $template['sample_data'] = json_decode($template['sample_data'], true);
            }
        }
        
        echo json_encode(['success' => true, 'templates' => $templates]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading templates']);
    }
    exit;
}

// Get single template
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    
    try {
        $stmt = $pdo->prepare('SELECT id, name, category, template, sample_data FROM templates WHERE id = ?');
        $stmt->execute([$_GET['id']]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($template) {
            if ($template['sample_data']) {
                $template['sample_data'] = json_decode($template['sample_data'], true);
            }
            echo json_encode(['success' => true, 'template' => $template]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Template not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading template']);
    }
    exit;
}
?>
