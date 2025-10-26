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

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($projectId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid project ID']);
    exit;
}

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Get project data with better error handling
    $stmt = $pdo->prepare('SELECT id, title, slug, description, privacy, html, created_at, updated_at FROM projects WHERE id = ? AND user_id = ?');
    
    if (!$stmt->execute([$projectId, $uid])) {
        throw new Exception('Failed to execute project query');
    }
    
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        error_log("Project not found: ID=$projectId, User=$uid");
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Project not found or access denied']);
        exit;
    }
    
    // Validate project data
    if (!isset($project['id']) || !is_numeric($project['id'])) {
        error_log("Invalid project data: " . json_encode($project));
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Invalid project data']);
        exit;
    }
    
    // Extract canvas content from HTML using optimized function
    $html = $project['html'] ?? '';
    $canvasContent = '';
    
    if (!empty($html)) {
        error_log("Project $projectId HTML length: " . strlen($html));
        error_log("Project $projectId HTML preview: " . substr($html, 0, 200) . "...");
        
        $canvasContent = extractCanvasFromHTML($html);
        error_log("Extracted canvas content length: " . strlen($canvasContent));
        error_log("Canvas content preview: " . substr($canvasContent, 0, 200) . "...");
        
        $canvasContent = sanitizeCanvasContent($canvasContent);
        error_log("Sanitized canvas content length: " . strlen($canvasContent));
    }
    
    // If no canvas content found, provide a default structure
    if (empty($canvasContent)) {
        $canvasContent = '<div class="el_text_block" data-id="1"><div class="el_text" style="font-size: 32px; font-weight: 700; color: var(--corp);">Добро пожаловать!</div></div>';
        error_log("No canvas content found for project $projectId, using default");
    } else {
        error_log("Successfully extracted canvas content for project $projectId");
    }
    
    echo json_encode([
        'ok' => true,
        'project' => [
            'id' => (int)$project['id'],
            'title' => $project['title'] ?? 'No title',
            'slug' => $project['slug'] ?? '',
            'description' => $project['description'] ?? '',
            'privacy' => $project['privacy'] ?? 'private',
            'canvas' => $canvasContent,
            'created_at' => $project['created_at'] ?? '',
            'updated_at' => $project['updated_at'] ?? '',
            'has_content' => !empty($html)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in project load: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection error']);
} catch (Throwable $e) {
    error_log('Load project error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal server error']);
}
?>
