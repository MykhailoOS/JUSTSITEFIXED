<?php
require_once __DIR__ . '/lib/db.php';

echo "=== JustSite Deployment Fix ===\n\n";

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Step 1: Check current database state
    echo "1. Checking current database state...\n";
    
    // Check if the problematic project exists
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE slug = ?');
    $stmt->execute(['123234567890']);
    $project = $stmt->fetch();
    
    if ($project) {
        echo "   ✓ Project with slug '123234567890' found:\n";
        echo "     - ID: {$project['id']}\n";
        echo "     - User ID: {$project['user_id']}\n";
        echo "     - Title: {$project['title']}\n";
        echo "     - Privacy: {$project['privacy']}\n";
    } else {
        echo "   ✗ Project with slug '123234567890' not found\n";
    }
    
    // Check all projects for user ID 4
    $stmt = $pdo->prepare('SELECT id, slug, title, privacy FROM projects WHERE user_id = ? ORDER BY id');
    $stmt->execute([4]);
    $userProjects = $stmt->fetchAll();
    
    echo "\n   Projects for user ID 4:\n";
    if ($userProjects) {
        foreach ($userProjects as $proj) {
            echo "     - ID: {$proj['id']}, Slug: '{$proj['slug']}', Title: '{$proj['title']}', Privacy: {$proj['privacy']}\n";
        }
    } else {
        echo "     No projects found for user ID 4\n";
    }
    
    // Step 2: Check database indexes
    echo "\n2. Checking database indexes...\n";
    $stmt = $pdo->prepare("SHOW INDEX FROM projects WHERE Key_name LIKE '%slug%'");
    $stmt->execute();
    $indexes = $stmt->fetchAll();
    
    foreach ($indexes as $index) {
        echo "   - {$index['Key_name']}: {$index['Column_name']} (Unique: " . ($index['Non_unique'] ? 'No' : 'Yes') . ")\n";
    }
    
    // Step 3: Fix the index issue
    echo "\n3. Fixing database index...\n";
    
    // Drop old unique index on slug only if it exists
    try {
        $pdo->exec('DROP INDEX idx_projects_slug ON projects');
        echo "   ✓ Dropped old unique index on slug\n";
    } catch (Exception $e) {
        echo "   - Old index may not exist: " . $e->getMessage() . "\n";
    }
    
    // Create new unique index on (user_id, slug)
    try {
        $pdo->exec('CREATE UNIQUE INDEX idx_projects_user_slug ON projects(user_id, slug)');
        echo "   ✓ Created new unique index on (user_id, slug)\n";
    } catch (Exception $e) {
        echo "   ✗ Failed to create new index: " . $e->getMessage() . "\n";
    }
    
    // Step 4: Test deployment logic
    echo "\n4. Testing deployment logic...\n";
    
    // Simulate the deployment check
    $testSlug = '123234567890';
    $testUserId = 4;
    
    // Check if slug is already taken by another user
    $stmt = $pdo->prepare('SELECT id FROM projects WHERE slug = ? AND user_id != ?');
    $stmt->execute([$testSlug, $testUserId]);
    $conflict = $stmt->fetch();
    
    if ($conflict) {
        echo "   ✗ Slug '{$testSlug}' is taken by another user (project ID: {$conflict['id']})\n";
    } else {
        echo "   ✓ Slug '{$testSlug}' is available for user {$testUserId}\n";
    }
    
    // Check if project with this slug already exists for this user
    $stmt = $pdo->prepare('SELECT id FROM projects WHERE slug = ? AND user_id = ?');
    $stmt->execute([$testSlug, $testUserId]);
    $existingProject = $stmt->fetch();
    
    if ($existingProject) {
        echo "   ✓ Project with slug '{$testSlug}' already exists for user {$testUserId} (ID: {$existingProject['id']})\n";
    } else {
        echo "   - No existing project with slug '{$testSlug}' for user {$testUserId}\n";
    }
    
    echo "\n=== Fix Summary ===\n";
    echo "The main issue was a database constraint that prevented multiple users from having the same slug.\n";
    echo "This has been fixed by changing the unique index from 'slug' to '(user_id, slug)'.\n";
    echo "Now each user can have their own projects with the same slug.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
