<?php
// Admin Users API
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$uid = $_SESSION['user_id'];

// Simple admin check
if ($uid != 1) { // First user is admin
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

try {
    require_once __DIR__ . '/../lib/db.php';
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Get all users with their project counts
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.email,
            u.name,
            u.created_at,
            u.banned,
            COUNT(p.id) as projects_count
        FROM users u
        LEFT JOIN projects p ON u.id = p.user_id
        GROUP BY u.id, u.email, u.name, u.created_at, u.banned
        ORDER BY u.created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedUsers = array_map(function($user) {
        return [
            'id' => (int)$user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'created_at' => $user['created_at'],
            'banned' => (bool)$user['banned'],
            'projects_count' => (int)$user['projects_count']
        ];
    }, $users);
    
    echo json_encode([
        'success' => true,
        'users' => $formattedUsers,
        'total' => count($formattedUsers)
    ]);
    
} catch (Exception $e) {
    error_log('Admin users API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
