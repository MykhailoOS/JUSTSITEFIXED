<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/html_generator.php';

start_session_if_needed();
header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in
$uid = current_user_id();
if (!$uid) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
    exit;
}

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Get form data
    $projectId = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $privacy = $_POST['privacy'] ?? 'private';
    $canvas = $_POST['canvas'] ?? '';
    
    // Validate required fields
    if ($projectId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Invalid project ID']);
        exit;
    }
    
    if (empty($title)) {
        echo json_encode(['ok' => false, 'error' => 'Title is required']);
        exit;
    }
    
    if (empty($canvas)) {
        echo json_encode(['ok' => false, 'error' => 'Canvas content is required']);
        exit;
    }
    
    // Validate privacy setting
    if (!in_array($privacy, ['public', 'unlisted', 'private'])) {
        $privacy = 'private';
    }
    
    // Check if project exists and belongs to user
    $stmt = $pdo->prepare('SELECT id, slug FROM projects WHERE id = ? AND user_id = ?');
    $stmt->execute([$projectId, $uid]);
    $project = $stmt->fetch();
    
    if (!$project) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Project not found']);
        exit;
    }
    
    // Optimized HTML generation
    $html = generateOptimizedHTML($title, $canvas);
    
    // Update project
    $stmt = $pdo->prepare('UPDATE projects SET title = ?, description = ?, privacy = ?, html = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
    $stmt->execute([$title, $description, $privacy, $html, $projectId, $uid]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Failed to update project']);
        exit;
    }
    
    echo json_encode([
        'ok' => true,
        'id' => $projectId,
        'slug' => $project['slug'],
        'message' => 'Project updated successfully'
    ]);
    
} catch (Throwable $e) {
    error_log('Update project error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal server error']);
}
?>
