<?php
require_once __DIR__ . '/lib/db.php';

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Add new columns if they don't exist
    $pdo->exec("ALTER TABLE projects ADD COLUMN IF NOT EXISTS slug VARCHAR(100) NOT NULL DEFAULT ''");
    $pdo->exec("ALTER TABLE projects ADD COLUMN IF NOT EXISTS description TEXT DEFAULT ''");
    $pdo->exec("ALTER TABLE projects ADD COLUMN IF NOT EXISTS privacy ENUM('public', 'unlisted', 'private') NOT NULL DEFAULT 'public'");
    $pdo->exec("ALTER TABLE projects ADD COLUMN IF NOT EXISTS view_count INT UNSIGNED NOT NULL DEFAULT 0");
    
    // Create indexes
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_projects_slug ON projects(slug)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_projects_privacy ON projects(privacy, created_at)");
    
    // Create project_views table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS project_views (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            project_id INT UNSIGNED NOT NULL,
            visitor_ip VARCHAR(45) NOT NULL,
            user_agent TEXT,
            referer TEXT,
            viewed_at DATETIME NOT NULL,
            CONSTRAINT fk_views_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_views_project ON project_views(project_id, viewed_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_views_ip ON project_views(visitor_ip, viewed_at)");
    
    // Generate slugs for existing projects
    $stmt = $pdo->query('SELECT id, title FROM projects WHERE slug = ""');
    $projects = $stmt->fetchAll();
    
    foreach ($projects as $project) {
        $slug = strtolower($project['title']);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        if (empty($slug)) {
            $slug = 'project-' . $project['id'];
        }
        
        // Make sure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (true) {
            $stmt = $pdo->prepare('SELECT id FROM projects WHERE slug = ?');
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        $stmt = $pdo->prepare('UPDATE projects SET slug = ? WHERE id = ?');
        $stmt->execute([$slug, $project['id']]);
        
        echo "Updated project {$project['id']} with slug: {$slug}\n";
    }
    
    echo "Migration completed successfully!\n";
    
} catch (Throwable $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
?>
