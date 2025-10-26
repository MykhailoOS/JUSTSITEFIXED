<?php
require_once __DIR__ . '/lib/db.php';

$pdo = DatabaseConnectionProvider::getConnection();

try {
    // Add community fields to projects table
    $pdo->exec("
        ALTER TABLE projects 
        ADD COLUMN category VARCHAR(50) DEFAULT 'general',
        ADD COLUMN community_featured TINYINT(1) DEFAULT 0,
        ADD INDEX idx_category (category),
        ADD INDEX idx_community_featured (community_featured)
    ");
    
    echo "✅ Migration completed successfully!\n";
    echo "Added fields: category, community_featured\n";
    echo "Added indexes for better performance\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
}
?>
