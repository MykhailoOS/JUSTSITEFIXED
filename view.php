<?php
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');

// Get slug from URL
$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 - Project Not Found</title></head><body><h1>404 - Project Not Found</h1></body></html>';
    exit;
}

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Get project by slug
    $stmt = $pdo->prepare('SELECT p.*, u.name as author_name FROM projects p LEFT JOIN users u ON p.user_id = u.id WHERE p.slug = ? AND p.privacy = "public"');
    $stmt->execute([$slug]);
    $project = $stmt->fetch();
    
    if (!$project) {
        // Check if project exists but is private/unlisted
        $stmt = $pdo->prepare('SELECT id FROM projects WHERE slug = ?');
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            http_response_code(403);
            echo '<!DOCTYPE html><html><head><title>403 - Access Denied</title></head><body><h1>403 - Access Denied</h1><p>This project is private.</p></body></html>';
        } else {
            http_response_code(404);
            echo '<!DOCTYPE html><html><head><title>404 - Project Not Found</title></head><body><h1>404 - Project Not Found</h1></body></html>';
        }
        exit;
    }
    
    // Track view (increment view count and log unique view)
    $visitorIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    
    // Check if this is a unique view (same IP in last 24 hours)
    $stmt = $pdo->prepare('SELECT id FROM project_views WHERE project_id = ? AND visitor_ip = ? AND viewed_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)');
    $stmt->execute([$project['id'], $visitorIp]);
    
    if (!$stmt->fetch()) {
        // This is a unique view
        $stmt = $pdo->prepare('INSERT INTO project_views (project_id, visitor_ip, user_agent, referer, viewed_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$project['id'], $visitorIp, $userAgent, $referer]);
        
        // Update view count
        $stmt = $pdo->prepare('UPDATE projects SET view_count = view_count + 1 WHERE id = ?');
        $stmt->execute([$project['id']]);
    }
    
    // Output the project HTML
    echo $project['html'];
    
} catch (Throwable $e) {
    error_log('View error: ' . $e->getMessage());
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>500 - Server Error</title></head><body><h1>500 - Server Error</h1></body></html>';
}
?>
