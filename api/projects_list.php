<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../config.php';

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

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Get user's projects with better error handling
    $stmt = $pdo->prepare('
        SELECT id, title, slug, description, privacy, view_count, created_at, updated_at 
        FROM projects 
        WHERE user_id = ? 
        ORDER BY updated_at DESC 
        LIMIT 50
    ');
    
    if (!$stmt->execute([$uid])) {
        throw new Exception('Failed to execute projects query');
    }
    
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Projects list for user $uid: " . count($projects) . " projects found");
    
    // Format projects for display with better validation
    $formattedProjects = [];
    foreach ($projects as $project) {
        // Validate required fields
        if (!isset($project['id']) || !is_numeric($project['id'])) {
            error_log("Invalid project ID found: " . json_encode($project));
            continue;
        }
        
        $formattedProjects[] = [
            'id' => (int)$project['id'],
            'title' => !empty($project['title']) ? $project['title'] : 'No title',
            'slug' => $project['slug'] ?? '',
            'description' => $project['description'] ?? '',
            'privacy' => $project['privacy'] ?? 'private',
            'view_count' => (int)($project['view_count'] ?? 0),
            'created_at' => $project['created_at'] ?? '',
            'updated_at' => $project['updated_at'] ?? '',
            'has_deploy' => !empty($project['slug']),
            'privacy_icon' => [
                'public' => '🌐',
                'unlisted' => '🔗', 
                'private' => '🔒'
            ][$project['privacy'] ?? 'private'] ?? '🔒',
            'formatted_date' => !empty($project['updated_at']) ? 
                date('d.m.Y H:i', strtotime($project['updated_at'])) : 
                'Unknown'
        ];
    }
    
    echo json_encode([
        'ok' => true,
        'projects' => $formattedProjects,
        'total' => count($formattedProjects)
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in projects list: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection error']);
} catch (Throwable $e) {
    error_log('Projects list error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal server error']);
}
?>
