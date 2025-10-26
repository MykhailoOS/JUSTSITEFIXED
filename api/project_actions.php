<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user
ini_set('log_errors', 1);

try {
    require_once __DIR__ . '/../lib/auth.php';
    require_once __DIR__ . '/../lib/db.php';
} catch (Exception $e) {
    error_log('Failed to include required files: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server configuration error']);
    exit;
}

// Start session and check if user is logged in
try {
    start_session_if_needed();
    $uid = current_user_id();
    if (!$uid) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
} catch (Exception $e) {
    error_log('Auth error: ' . $e->getMessage());
    http_response_code(401);
    echo json_encode(['error' => 'Authentication failed']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON decode error: ' . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$action = $input['action'] ?? '';
$projectId = $input['project_id'] ?? '';

if (empty($action) || empty($projectId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing action or project_id']);
    exit;
}

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Verify project belongs to user
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ? AND user_id = ?');
    $stmt->execute([$projectId, $uid]);
    $project = $stmt->fetch();
    
    if (!$project) {
        http_response_code(404);
        echo json_encode(['error' => 'Project not found']);
        exit;
    }
    
    switch ($action) {
        case 'toggle_privacy':
            $newPrivacy = $project['privacy'] === 'public' ? 'private' : 'public';
            
            // If making public, ensure slug exists
            if ($newPrivacy === 'public' && empty($project['slug'])) {
                $slug = generateUniqueSlug($pdo, $project['title'] ?: 'untitled');
                $stmt = $pdo->prepare('UPDATE projects SET privacy = ?, slug = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$newPrivacy, $slug, $projectId]);
            } else {
                $stmt = $pdo->prepare('UPDATE projects SET privacy = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$newPrivacy, $projectId]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $newPrivacy === 'public' ? 'Project published' : 'Project made private',
                'new_privacy' => $newPrivacy,
                'slug' => $project['slug'] ?? null
            ]);
            break;
            
        case 'duplicate':
            // Create new project
            $newTitle = $project['title'] ? $project['title'] . ' (copy)' : 'Project copy';
            $newSlug = generateUniqueSlug($pdo, $newTitle);
            
            $stmt = $pdo->prepare('
                INSERT INTO projects (user_id, title, html, privacy, slug, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $uid,
                $newTitle,
                $project['html'],
                'private', // New projects are private by default
                $newSlug
            ]);
            
            $newProjectId = $pdo->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Project duplicated successfully',
                'new_project_id' => $newProjectId,
                'new_title' => $newTitle
            ]);
            break;
            
        case 'delete':
            // Delete project and related data
            $pdo->beginTransaction();
            
            try {
                // Delete project views
                $stmt = $pdo->prepare('DELETE FROM project_views WHERE project_id = ?');
                $stmt->execute([$projectId]);
                
                // Delete project
                $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ? AND user_id = ?');
                $stmt->execute([$projectId, $uid]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Project deleted'
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action']);
            break;
    }
    
} catch (Throwable $e) {
    error_log('Project action error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function generateUniqueSlug($pdo, $title) {
    $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $baseSlug = trim($baseSlug, '-');
    
    if (empty($baseSlug)) {
        $baseSlug = 'project';
    }
    
    $slug = $baseSlug;
    $counter = 1;
    
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM projects WHERE slug = ?');
        $stmt->execute([$slug]);
        
        if (!$stmt->fetch()) {
            return $slug;
        }
        
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
}
?>
