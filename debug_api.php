<?php
// Simple test to check if API dependencies work
try {
    require_once __DIR__ . '/lib/auth.php';
    require_once __DIR__ . '/lib/db.php';
    
    echo "✅ Files included successfully\n";
    
    start_session_if_needed();
    echo "✅ Session started\n";
    
    $uid = current_user_id();
    echo "✅ User ID: " . ($uid ?: 'Not logged in') . "\n";
    
    $pdo = DatabaseConnectionProvider::getConnection();
    echo "✅ Database connection successful\n";
    
    // Test query
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE user_id = ?');
    $stmt->execute([$uid ?: 0]);
    $count = $stmt->fetchColumn();
    echo "✅ Projects count: " . $count . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
