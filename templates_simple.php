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
    <title>Simple Templates — JustSite</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900">Simple Templates</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="admin.php" class="text-gray-600 hover:text-gray-900">← Back to Admin</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Add Template Form -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4">Add New Template</h2>
            <form method="POST" action="api/templates_simple.php">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="landing">Landing</option>
                            <option value="blog">Blog</option>
                            <option value="portfolio">Portfolio</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">HTML Code</label>
                    <textarea name="template" rows="8" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Enter HTML template code..." required></textarea>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sample Data (JSON)</label>
                    <textarea name="sample_data" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder='{"title": "Hello", "content": "World"}'></textarea>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Add Template</button>
                </div>
            </form>
        </div>

        <!-- Templates List -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Templates (<?php echo count($templates); ?>)</h2>
            </div>
            <div class="divide-y divide-gray-200">
                <?php if (empty($templates)): ?>
                    <div class="px-6 py-8 text-center text-gray-500">
                        No templates found. Add your first template above.
                    </div>
                <?php else: ?>
                    <?php foreach ($templates as $template): ?>
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($template['name']); ?></h3>
                                    <p class="text-sm text-gray-500">Category: <?php echo htmlspecialchars($template['category']); ?> | Downloads: <?php echo $template['downloads']; ?></p>
                                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($template['description'] ?: 'No description'); ?></p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="?preview=<?php echo $template['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm">Preview</a>
                                    <a href="?edit=<?php echo $template['id']; ?>" class="text-green-600 hover:text-green-800 text-sm">Edit</a>
                                    <a href="api/templates_simple.php?delete=<?php echo $template['id']; ?>" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete this template?')">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Preview Modal -->
    <?php if (isset($_GET['preview'])): ?>
        <?php
        $previewId = $_GET['preview'];
        $previewTemplate = null;
        foreach ($templates as $t) {
            if ($t['id'] == $previewId) {
                $previewTemplate = $t;
                break;
            }
        }
        ?>
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold">Preview: <?php echo htmlspecialchars($previewTemplate['name']); ?></h3>
                    <button onclick="window.location.href='templates_simple.php'" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-4 overflow-auto max-h-[calc(90vh-80px)]">
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <?php echo $previewTemplate['template']; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
