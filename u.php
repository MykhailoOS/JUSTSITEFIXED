<?php
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');

// Debug mode - remove in production
$debug = true;

// Get username and slug from URL
$path = $_GET['path'] ?? '';
$pathParts = explode('/', trim($path, '/'));

if ($debug) {
    error_log("Debug u.php - Path: " . $path);
    error_log("Debug u.php - Path parts: " . print_r($pathParts, true));
    error_log("Debug u.php - GET params: " . print_r($_GET, true));
    error_log("Debug u.php - REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set'));
}

if (count($pathParts) < 2) {
    if ($debug) {
        echo '<!DOCTYPE html><html><head><title>Debug - Path Error</title></head><body>';
        echo '<h1>Debug: Path Error</h1>';
        echo '<p>Path: ' . htmlspecialchars($path) . '</p>';
        echo '<p>Path parts count: ' . count($pathParts) . '</p>';
        echo '<p>Path parts: ' . htmlspecialchars(print_r($pathParts, true)) . '</p>';
        echo '</body></html>';
    } else {
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>404 - Project Not Found</title></head><body><h1>404 - Project Not Found</h1></body></html>';
    }
    exit;
}

$usernameSlug = $pathParts[0];
$projectSlug = $pathParts[1];

if ($debug) {
    error_log("Debug u.php - Username slug: " . $usernameSlug);
    error_log("Debug u.php - Project slug: " . $projectSlug);
}

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    if ($debug) {
        error_log("Debug u.php - Database connection successful");
    }
    
    // Find user by username slug - improved approach
    $user = null;
    
    // First try to find by exact match in name or email
    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE LOWER(name) = ? OR LOWER(email) = ?');
    $stmt->execute([strtolower($usernameSlug), strtolower($usernameSlug)]);
    $user = $stmt->fetch();
    
    // If not found, try to find by slugified name/email
    if (!$user) {
        $stmt = $pdo->prepare('SELECT id, name, email FROM users');
        $stmt->execute();
        $allUsers = $stmt->fetchAll();
        
        foreach ($allUsers as $u) {
            $displayName = $u['name'] ?: $u['email'];
            $slugified = strtolower(trim($displayName));
            $slugified = preg_replace('/[^a-z0-9\s-]/', '', $slugified);
            $slugified = preg_replace('/\s+/', '-', $slugified);
            $slugified = trim($slugified, '-');
            
            if ($slugified === $usernameSlug) {
                $user = $u;
                break;
            }
        }
    }
    
    // Special case: if username slug is "user" and no user found, try to get the first user
    if (!$user && $usernameSlug === 'user') {
        $stmt = $pdo->prepare('SELECT id, name, email FROM users ORDER BY id ASC LIMIT 1');
        $stmt->execute();
        $user = $stmt->fetch();
    }
    
    if ($debug) {
        error_log("Debug u.php - User search result: " . print_r($user, true));
    }
    
    if (!$user) {
        if ($debug) {
            echo '<!DOCTYPE html><html><head><title>Debug - User Not Found</title></head><body>';
            echo '<h1>Debug: User Not Found</h1>';
            echo '<p>Looking for username slug: ' . htmlspecialchars($usernameSlug) . '</p>';
            echo '<p>Project slug: ' . htmlspecialchars($projectSlug) . '</p>';
            echo '</body></html>';
        } else {
            http_response_code(404);
            echo '<!DOCTYPE html><html><head><title>404 - User Not Found</title></head><body><h1>404 - User Not Found</h1></body></html>';
        }
        exit;
    }
    
    // Get project by slug and user
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE slug = ? AND user_id = ? AND privacy = "public"');
    $stmt->execute([$projectSlug, $user['id']]);
    $project = $stmt->fetch();
    
    if ($debug) {
        error_log("Debug u.php - Project search for user " . $user['id'] . " and slug " . $projectSlug);
        error_log("Debug u.php - Project result: " . print_r($project, true));
    }
    
    if (!$project) {
        // Check if project exists but is private/unlisted
        $stmt = $pdo->prepare('SELECT id, privacy FROM projects WHERE slug = ? AND user_id = ?');
        $stmt->execute([$projectSlug, $user['id']]);
        $privateProject = $stmt->fetch();
        
        if ($debug) {
            error_log("Debug u.php - Private project check: " . print_r($privateProject, true));
        }
        
        if ($privateProject) {
            if ($debug) {
                echo '<!DOCTYPE html><html><head><title>Debug - Access Denied</title></head><body>';
                echo '<h1>Debug: Access Denied</h1>';
                echo '<p>Project exists but is ' . htmlspecialchars($privateProject['privacy']) . '</p>';
                echo '<p>User ID: ' . $user['id'] . '</p>';
                echo '<p>Project slug: ' . htmlspecialchars($projectSlug) . '</p>';
                echo '</body></html>';
            } else {
                http_response_code(403);
                echo '<!DOCTYPE html><html><head><title>403 - Access Denied</title></head><body><h1>403 - Access Denied</h1><p>This project is private.</p></body></html>';
            }
        } else {
            if ($debug) {
                echo '<!DOCTYPE html><html><head><title>Debug - Project Not Found</title></head><body>';
                echo '<h1>Debug: Project Not Found</h1>';
                echo '<p>User ID: ' . $user['id'] . '</p>';
                echo '<p>Project slug: ' . htmlspecialchars($projectSlug) . '</p>';
                echo '</body></html>';
            } else {
                http_response_code(404);
                echo '<!DOCTYPE html><html><head><title>404 - Project Not Found</title></head><body><h1>404 - Project Not Found</h1></body></html>';
            }
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
    error_log('User view error: ' . $e->getMessage());
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>500 - Server Error</title></head><body><h1>500 - Server Error</h1></body></html>';
}
?>
