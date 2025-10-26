<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';

// Check if user is logged in
$uid = current_user_id();
if (!$uid) {
    header('Location: login.php');
    exit;
}

// Simple admin check
$pdo = DatabaseConnectionProvider::getConnection();
$stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();

$isAdmin = $user && (
    strpos($user['email'], 'admin') !== false || 
    $user['email'] === 'admin@justsite.com' ||
    $uid == 1
);

if (!$isAdmin) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><title>403 - Access Denied</title></head><body><h1>403 - Access Denied</h1><p>Admin access required.</p></body></html>';
    exit;
}

// Handle form submission
if ($_POST) {
    $name = $_POST['name'] ?? '';
    $html = $_POST['html'] ?? '';
    
    if ($name && $html) {
        try {
            $stmt = $pdo->prepare('INSERT INTO templates (name, template, user_id) VALUES (?, ?, ?)');
            $stmt->execute([$name, $html, $uid]);
            $success = 'Template added successfully!';
        } catch (Exception $e) {
            $error = 'Error adding template: ' . $e->getMessage();
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM templates WHERE id = ? AND user_id = ?');
        $stmt->execute([$_GET['delete'], $uid]);
        $success = 'Template deleted successfully!';
    } catch (Exception $e) {
        $error = 'Error deleting template: ' . $e->getMessage();
    }
}

// Get templates
try {
    $stmt = $pdo->prepare('SELECT * FROM templates ORDER BY created_at DESC');
    $stmt->execute();
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $templates = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultra Simple Templates</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .templates { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .template-item { padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .template-item:last-child { border-bottom: none; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin: 5px 0; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
        .delete-btn { background: #dc3545; }
        .delete-btn:hover { background: #c82333; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .preview { background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Ultra Simple Templates</h1>
            <p>Простая система шаблонов без заморочек</p>
            <a href="admin.php" style="color: #007cba;">← Back to Admin</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Add Template Form -->
        <div class="form">
            <h2>Add New Template</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Template Name" required>
                <textarea name="html" rows="10" placeholder="HTML Code (use [TITLE], [DESCRIPTION], [BUTTON_TEXT] for variables)" required></textarea>
                <button type="submit">Add Template</button>
            </form>
        </div>

        <!-- Templates List -->
        <div class="templates">
            <div style="padding: 20px; border-bottom: 1px solid #eee;">
                <h2>Templates (<?php echo count($templates); ?>)</h2>
            </div>
            
            <?php if (empty($templates)): ?>
                <div style="padding: 40px; text-align: center; color: #666;">
                    No templates found. Add your first template above.
                </div>
            <?php else: ?>
                <?php foreach ($templates as $template): ?>
                    <div class="template-item">
                        <div>
                            <strong><?php echo htmlspecialchars($template['name']); ?></strong>
                            <div style="font-size: 12px; color: #666; margin-top: 5px;">
                                Created: <?php echo date('Y-m-d H:i', strtotime($template['created_at'])); ?>
                            </div>
                            <div class="preview">
                                <strong>Preview:</strong><br>
                                <?php 
                                $preview = $template['template'];
                                
                                // Replace simple variables
                                $preview = str_replace(['[TITLE]', '[DESCRIPTION]', '[BUTTON_TEXT]', '[IMAGE]', '[PRICE]', '[AUTHOR]', '[DATE]', '[VIEWS]'], 
                                                    ['Sample Title', 'Sample Description', 'Click Me', 'https://via.placeholder.com/300x200', '$99', 'John Doe', date('Y-m-d'), '1,234'], $preview);
                                
                                // Handle Handlebars-like syntax
                                $preview = preg_replace('/\{\{#each\s+(\w+)\}\}(.*?)\{\{\/each\}\}/s', function($matches) {
                                    $arrayName = $matches[1];
                                    if ($arrayName === 'plans') {
                                        return '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;"><div style="border: 1px solid #ddd; padding: 20px; text-align: center;"><h3>Starter Plan</h3><div style="font-size: 2rem; font-weight: bold;">$9/month</div><button style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px;">Get Started</button></div><div style="border: 2px solid #007cba; padding: 20px; text-align: center;"><h3>Professional Plan</h3><div style="font-size: 2rem; font-weight: bold;">$29/month</div><button style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px;">Start Free Trial</button></div></div>';
                                    } else if ($arrayName === 'features') {
                                        return '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;"><div style="text-align: center; padding: 20px; border: 1px solid #eee;"><div style="font-size: 2rem;">🚀</div><h3>Fast Performance</h3><p>Lightning-fast loading times</p></div><div style="text-align: center; padding: 20px; border: 1px solid #eee;"><div style="font-size: 2rem;">🔒</div><h3>Secure</h3><p>Enterprise-grade security</p></div></div>';
                                    }
                                    return $matches[2];
                                }, $preview);
                                
                                // Handle simple variables
                                $preview = preg_replace('/\{\{(\w+)\}\}/', function($matches) {
                                    $variable = $matches[1];
                                    $replacements = [
                                        'title' => 'Welcome to Our Platform',
                                        'subtitle' => 'Build amazing websites',
                                        'buttonText' => 'Get Started',
                                        'excerpt' => 'Learn the fundamentals...',
                                        'author' => 'John Doe',
                                        'date' => date('Y-m-d'),
                                        'views' => '1,234',
                                        'featured' => 'true'
                                    ];
                                    return $replacements[$variable] ?? $variable;
                                }, $preview);
                                
                                // Handle if conditions
                                $preview = preg_replace('/\{\{#if\s+(\w+)\}\}(.*?)\{\{\/if\}\}/s', function($matches) {
                                    $condition = $matches[1];
                                    if ($condition === 'featured') {
                                        return $matches[2];
                                    }
                                    return '';
                                }, $preview);
                                
                                echo htmlspecialchars(substr($preview, 0, 200)) . (strlen($preview) > 200 ? '...' : '');
                                ?>
                            </div>
                        </div>
                        <div>
                            <button onclick="insertTemplate(<?php echo $template['id']; ?>)" style="background: #28a745; margin-right: 10px;">Insert</button>
                            <a href="?delete=<?php echo $template['id']; ?>" onclick="return confirm('Delete this template?')" class="delete-btn" style="color: white; text-decoration: none; padding: 8px 12px; border-radius: 4px;">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Insert template into constructor
        function insertTemplate(templateId) {
            // Get template data
            fetch('api/templates_ultra_simple.php?id=' + templateId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Send to parent window (constructor)
                        if (window.parent && window.parent.insertTemplateIntoConstructor) {
                            window.parent.insertTemplateIntoConstructor(data.html);
                            alert('Template inserted successfully!');
                        } else {
                            // Fallback: copy to clipboard
                            navigator.clipboard.writeText(data.html).then(() => {
                                alert('Template copied to clipboard! Paste it in your constructor.');
                            });
                        }
                    } else {
                        alert('Error loading template');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading template');
                });
        }
    </script>
</body>
</html>
