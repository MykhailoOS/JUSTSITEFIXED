<?php
require_once __DIR__ . '/lib/db.php';

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    echo "Starting migration to fix slug index...\n";
    
    // Drop the old unique index on slug only
    echo "Dropping old unique index on slug...\n";
    try {
        $pdo->exec('DROP INDEX idx_projects_slug ON projects');
        echo "Old index dropped successfully.\n";
    } catch (Exception $e) {
        echo "Note: Old index may not exist or already dropped: " . $e->getMessage() . "\n";
    }
    
    // Create new unique index on (user_id, slug)
    echo "Creating new unique index on (user_id, slug)...\n";
    $pdo->exec('CREATE UNIQUE INDEX idx_projects_user_slug ON projects(user_id, slug)');
    echo "New index created successfully.\n";
    
    // Check for any duplicate slugs that might exist
    echo "Checking for duplicate slugs...\n";
    $stmt = $pdo->prepare('SELECT slug, COUNT(*) as count FROM projects GROUP BY slug HAVING COUNT(*) > 1');
    $stmt->execute();
    $duplicates = $stmt->fetchAll();
    
    if ($duplicates) {
        echo "Found duplicate slugs:\n";
        foreach ($duplicates as $dup) {
            echo "- Slug '{$dup['slug']}' appears {$dup['count']} times\n";
        }
        echo "These will need to be resolved manually.\n";
    } else {
        echo "No duplicate slugs found.\n";
    }
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
