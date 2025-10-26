<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/html_generator.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
// Allow same-origin OPTIONS (some hosts/proxies may probe)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// For API: explicit auth check to avoid HTML redirects
$uid = current_user_id();
if (!$uid) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

// Accept POST (preferred). Some hosts may forward as GET with query parameters; allow as fallback
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
if ($method !== 'POST' && $method !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload) { $payload = $_POST; }
if (!$payload && $method === 'GET') { $payload = $_GET; }

$title = trim($payload['title'] ?? 'Untitled');
$canvas = $payload['canvas'] ?? '';
if ($canvas === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Empty canvas']);
    exit;
}

// Log canvas content for debugging
error_log("Saving project - Canvas length: " . strlen($canvas));
error_log("Canvas preview: " . substr($canvas, 0, 300) . "...");

// Count elements in canvas
$elementCount = substr_count($canvas, 'class="el_');
error_log("Number of elements in canvas: " . $elementCount);

// Optimized HTML generation
$html = generateOptimizedHTML($title ?: APP_NAME, $canvas);
error_log("Generated HTML length: " . strlen($html));

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Generate unique slug for new projects
    $slug = '';
    if (!isset($payload['id']) || (int)$payload['id'] <= 0) {
        // Create slug from title
        $baseSlug = strtolower(trim($title));
        $baseSlug = preg_replace('/[^a-z0-9\s-]/', '', $baseSlug);
        $baseSlug = preg_replace('/\s+/', '-', $baseSlug);
        $baseSlug = trim($baseSlug, '-');
        
        // If empty, use default
        if (empty($baseSlug)) {
            $baseSlug = 'project';
        }
        
        // Make sure slug is unique
        $slug = $baseSlug;
        $counter = 1;
        while (true) {
            $stmt = $pdo->prepare('SELECT id FROM projects WHERE slug = ?');
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) {
                break; // Slug is unique
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }
    
    // Upsert by optional project id
    $projectId = isset($payload['id']) ? (int)$payload['id'] : 0;
    if ($projectId > 0) {
        $stmt = $pdo->prepare('UPDATE projects SET title = ?, html = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$title, $html, $projectId, $uid]);
        if ($stmt->rowCount() === 0) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Forbidden or not found']);
            exit;
        }
    } else {
        $stmt = $pdo->prepare('INSERT INTO projects (user_id, title, slug, html, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([$uid, $title, $slug, $html]);
        $projectId = (int)$pdo->lastInsertId();
    }
    echo json_encode(['ok' => true, 'id' => $projectId, 'slug' => $slug]);
} catch (Throwable $e) {
    error_log('Save project error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal server error']);
}


