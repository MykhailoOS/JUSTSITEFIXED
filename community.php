<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/language.php';

// Initialize language system
LanguageManager::init();

start_session_if_needed();
header('Content-Type: text/html; charset=utf-8');

// Check if user is logged in
$uid = current_user_id();
if (!$uid) {
    header('Location: login.php');
    exit;
}

$pdo = DatabaseConnectionProvider::getConnection();

// Handle search and filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build query
$sql = 'SELECT t.*, u.name as author_name, u.email as author_email FROM templates t 
        LEFT JOIN users u ON t.user_id = u.id 
        WHERE t.status = "active"';
$params = [];

if ($search) {
    $sql .= ' AND (t.name LIKE ? OR t.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= ' AND t.category = ?';
    $params[] = $category;
}

switch ($sort) {
    case 'popular':
        $sql .= ' ORDER BY t.downloads DESC, t.created_at DESC';
        break;
    case 'oldest':
        $sql .= ' ORDER BY t.created_at ASC';
        break;
    default:
        $sql .= ' ORDER BY t.created_at DESC';
}

$sql .= ' LIMIT 50';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $templates = [];
}

// Get categories
try {
    $stmt = $pdo->prepare('SELECT category, COUNT(*) as count FROM templates WHERE status = "active" GROUP BY category ORDER BY count DESC');
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Templates — JustSite</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .template-card {
            transition: all 0.3s ease;
        }
        .template-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .template-preview {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 16px;
            margin: 12px 0;
            font-size: 12px;
            color: #6c757d;
            max-height: 100px;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900">Community Templates</h1>
                    <span class="ml-4 text-sm text-gray-500"><?php echo count($templates); ?> templates</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-gray-900 text-sm px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                        ← Back to Constructor
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-64">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search templates..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($cat['category'])); ?> (<?php echo $cat['count']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="sort" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Search
                </button>
            </form>
        </div>

        <!-- Templates Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($templates)): ?>
                <div class="col-span-full text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Templates Found</h3>
                    <p class="text-gray-500">Try adjusting your search or filters</p>
                </div>
            <?php else: ?>
                <?php foreach ($templates as $template): ?>
                    <div class="template-card bg-white rounded-lg shadow-sm hover:shadow-lg transition-all duration-300">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($template['name']); ?></h3>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                        <?php echo htmlspecialchars(ucfirst($template['category'])); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($template['description'] ?: 'No description'); ?></p>
                            
                            <div class="template-preview">
                                <?php 
                                $preview = $template['template'];
                                $preview = str_replace(['[TITLE]', '[DESCRIPTION]', '[BUTTON_TEXT]'], 
                                                    ['Sample Title', 'Sample Description', 'Click Me'], $preview);
                                echo htmlspecialchars(substr($preview, 0, 200)) . (strlen($preview) > 200 ? '...' : '');
                                ?>
                            </div>
                            
                            <div class="flex items-center justify-between mt-4">
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span>👤 <?php echo htmlspecialchars($template['author_name'] ?: 'Anonymous'); ?></span>
                                    <span>📅 <?php echo date('M j, Y', strtotime($template['created_at'])); ?></span>
                                    <span>⬇️ <?php echo $template['downloads']; ?></span>
                                </div>
                                <button onclick="insertTemplate(<?php echo $template['id']; ?>)" 
                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm flex items-center space-x-2">
                                    <span class="material-icons text-sm">add</span>
                                    <span>Use Template</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Insert template into constructor
        function insertTemplate(templateId) {
            fetch('api/templates_community.php?id=' + templateId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Send to parent window (constructor)
                        if (window.opener && window.opener.insertTemplateIntoConstructor) {
                            window.opener.insertTemplateIntoConstructor(data.html);
                            alert('Template inserted successfully!');
                            window.close();
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
