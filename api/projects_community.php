<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';

require_auth();

$pdo = DatabaseConnectionProvider::getConnection();
$uid = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get project by ID for copying
    $projectId = $_GET['id'] ?? null;
    
    if ($projectId) {
        $stmt = $pdo->prepare('
            SELECT p.*, u.name as author_name 
            FROM projects p 
            LEFT JOIN users u ON p.user_id = u.id 
            WHERE p.id = ? AND p.privacy = "public" AND p.community_featured = 1
        ');
        $stmt->execute([$projectId]);
        $project = $stmt->fetch();
        
        if ($project) {
            // Get project elements
            $elementsStmt = $pdo->prepare('SELECT * FROM elements WHERE project_id = ? ORDER BY z_index');
            $elementsStmt->execute([$projectId]);
            $elements = $elementsStmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'project' => [
                    'id' => $project['id'],
                    'title' => $project['title'],
                    'description' => $project['description'] ?: 'No description',
                    'author_name' => $project['author_name'] ?: 'Anonymous',
                    'created_at' => $project['created_at'],
                    'view_count' => $project['view_count']
                ],
                'elements' => $elements
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Project not found or not public']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Project ID required']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save project to community
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'save') {
        $projectId = $input['project_id'] ?? null;
        $description = $input['description'] ?? '';
        $category = $input['category'] ?? 'general';
        
        if (!$projectId) {
            echo json_encode(['success' => false, 'message' => 'Project ID required']);
            exit;
        }
        
        try {
            // Get project data
            $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ? AND user_id = ?');
            $stmt->execute([$projectId, $uid]);
            $project = $stmt->fetch();
            
            if (!$project) {
                echo json_encode(['success' => false, 'message' => 'Project not found']);
                exit;
            }
            
            // Update project to be public and add community info
            $updateStmt = $pdo->prepare('
                UPDATE projects 
                SET privacy = "public", 
                    description = ?, 
                    category = ?,
                    community_featured = 1,
                    updated_at = NOW()
                WHERE id = ?
            ');
            $updateStmt->execute([$description, $category, $projectId]);
            
            echo json_encode(['success' => true, 'message' => 'Project shared to community successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error sharing project: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
