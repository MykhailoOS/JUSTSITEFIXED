<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/html_generator.php';

start_session_if_needed();
header('Content-Type: application/json; charset=utf-8');

// Handle CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    http_response_code(204);
    exit;
}

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
    header('Allow: POST, OPTIONS');
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
    exit;
}

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $privacy = $_POST['privacy'] ?? 'public';
    $description = trim($_POST['description'] ?? '');
    $canvas = $_POST['canvas'] ?? '';
    
    // Validate required fields
    if (empty($title) || empty($slug) || empty($canvas)) {
        echo json_encode(['ok' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    // Clean and validate slug format
    $slug = strtolower(trim(preg_replace('/[^a-z0-9\-]/', '-', $slug)));
    $slug = preg_replace('/-+/', '-', $slug); // Remove multiple dashes
    $slug = trim($slug, '-'); // Remove leading/trailing dashes
    
    if (empty($slug) || !preg_match('/^[a-z0-9\-]+$/', $slug)) {
        echo json_encode(['ok' => false, 'error' => 'Invalid slug format. Only lowercase letters, numbers and dashes allowed.']);
        exit;
    }
    
    // Validate privacy setting
    if (!in_array($privacy, ['public', 'unlisted', 'private'])) {
        $privacy = 'public';
    }
    
    // Check if slug is already taken
    $stmt = $pdo->prepare('SELECT id FROM projects WHERE slug = ? AND user_id != ?');
    $stmt->execute([$slug, $uid]);
    if ($stmt->fetch()) {
        echo json_encode(['ok' => false, 'error' => 'URL already taken']);
        exit;
    }
    
    // Check if project with this slug already exists for this user
    $stmt = $pdo->prepare('SELECT id FROM projects WHERE slug = ? AND user_id = ?');
    $stmt->execute([$slug, $uid]);
    $existingProject = $stmt->fetch();
    
    // Optimized HTML generation with caching
    $html = generateOptimizedHTML($title, $canvas);
    
    if ($existingProject) {
        // Update existing project
        $stmt = $pdo->prepare('UPDATE projects SET title = ?, description = ?, privacy = ?, html = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$title, $description, $privacy, $html, $existingProject['id'], $uid]);
        $projectId = $existingProject['id'];
    } else {
        // Create new project
        $stmt = $pdo->prepare('INSERT INTO projects (user_id, title, slug, description, privacy, html, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([$uid, $title, $slug, $description, $privacy, $html]);
        $projectId = $pdo->lastInsertId();
    }
    
    // Get user info for beautiful URL
    $stmt = $pdo->prepare('SELECT COALESCE(NULLIF(name, ""), email) AS display_name FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
    $username = $user['display_name'] ?? 'user';
    
    // Debug logging
    error_log("Deploy API - Original username: " . $username);
    
    // Create username slug (clean for URL)
    $usernameSlug = strtolower(trim($username));
    $usernameSlug = preg_replace('/[^a-z0-9\s-]/', '', $usernameSlug);
    $usernameSlug = preg_replace('/\s+/', '-', $usernameSlug);
    $usernameSlug = trim($usernameSlug, '-');
    
    // Debug logging
    error_log("Deploy API - Username slug: " . $usernameSlug);
    
    // Fallback if username is empty
    if (empty($usernameSlug)) {
        $usernameSlug = 'user';
        error_log("Deploy API - Using fallback username slug: " . $usernameSlug);
    }
    
    // Generate beautiful URLs
    $baseUrl = rtrim(APP_BASE_URL, '/');
    $publicUrl = $baseUrl . '/u/' . $usernameSlug . '/' . $slug;
    $statsUrl = $baseUrl . '/stats/' . $slug;
    
    echo json_encode([
        'ok' => true,
        'id' => $projectId,
        'slug' => $slug,
        'public_url' => $publicUrl,
        'stats_url' => $statsUrl,
        'privacy' => $privacy
    ]);
    
} catch (Throwable $e) {
    error_log('Deploy error: ' . $e->getMessage());
    error_log('Deploy error trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal server error', 'debug' => $e->getMessage()]);
}
?>
