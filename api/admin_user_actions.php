<?php
// Admin User Actions API
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action']) || !isset($input['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$action = $input['action'];
$userId = (int)$input['userId'];

// Prevent admin from banning/deleting themselves
if ($userId == $uid) {
    echo json_encode(['success' => false, 'message' => 'Cannot perform action on yourself']);
    exit;
}

try {
    require_once __DIR__ . '/../lib/db.php';
    $pdo = DatabaseConnectionProvider::getConnection();
    
    switch ($action) {
        case 'ban':
            $stmt = $pdo->prepare("UPDATE users SET banned = 1 WHERE id = ?");
            $stmt->execute([$userId]);
            echo json_encode(['success' => true, 'message' => 'User banned successfully']);
            break;
            
        case 'unban':
            $stmt = $pdo->prepare("UPDATE users SET banned = 0 WHERE id = ?");
            $stmt->execute([$userId]);
            echo json_encode(['success' => true, 'message' => 'User unbanned successfully']);
            break;
            
        case 'delete':
            // First delete user's projects
            $stmt = $pdo->prepare("DELETE FROM projects WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Then delete the user
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            break;
            
        case 'edit':
            if (!isset($input['name'])) {
                echo json_encode(['success' => false, 'message' => 'Name is required']);
                exit;
            }
            $name = trim($input['name']);
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->execute([$name, $userId]);
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    error_log('Admin user actions API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
