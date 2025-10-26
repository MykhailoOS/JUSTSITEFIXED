<?php
require_once __DIR__ . '/lib/auth.php';
require_auth();

$pdo = DatabaseConnectionProvider::getConnection();
$uid = current_user_id();

// Get project info if specified
$project = null;
$projectId = $_GET['project'] ?? null;
if ($projectId) {
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ? AND user_id = ?');
    $stmt->execute([$projectId, $uid]);
    $project = $stmt->fetch();
}

// Get user info
$stmt = $pdo->prepare('SELECT id, email, name FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch() ?: ['email' => '', 'name' => ''];
$displayName = $user['name'] ?: $user['email'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика — JustSite</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">JS</span>
                    </div>
                    <span class="ml-3 text-xl font-bold text-gray-900">JustSite</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="profile_simple.php" class="text-gray-600 hover:text-gray-900">← Назад к профилю</a>
                    <a href="logout.php" class="text-gray-600 hover:text-gray-900">Выйти</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($project): ?>
            <!-- Project Stats -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-4">
                    Статистика проекта: <?php echo htmlspecialchars($project['title'] ?: 'Без названия'); ?>
                </h1>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600"><?php echo $project['view_count'] ?: 0; ?></div>
                        <div class="text-gray-600">Просмотров</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold <?php echo $project['privacy'] === 'public' ? 'text-green-600' : 'text-gray-600'; ?>">
                            <?php echo $project['privacy'] === 'public' ? 'Да' : 'Нет'; ?>
                        </div>
                        <div class="text-gray-600">Опубликован</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600">
                            <?php echo date('d.m.Y', strtotime($project['created_at'])); ?>
                        </div>
                        <div class="text-gray-600">Создан</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-orange-600">
                            <?php echo date('d.m.Y', strtotime($project['updated_at'])); ?>
                        </div>
                        <div class="text-gray-600">Обновлен</div>
                    </div>
                </div>
            </div>

            <!-- Project Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Действия</h2>
                <div class="flex flex-wrap gap-4">
                    <a href="index.php?load=<?php echo $project['id']; ?>" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Редактировать проект
                    </a>
                    <?php if ($project['privacy'] === 'public' && $project['slug']): ?>
                        <a href="view.php?slug=<?php echo htmlspecialchars($project['slug']); ?>" 
                           target="_blank"
                           class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            Посмотреть сайт
                        </a>
                    <?php endif; ?>
                    <button onclick="deleteProject(<?php echo $project['id']; ?>)" 
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                        Удалить проект
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- General Stats -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-4">Общая статистика</h1>
                <p class="text-gray-600">Выберите проект для просмотра детальной статистики</p>
                <div class="mt-4">
                    <a href="profile_simple.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Перейти к проектам
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function deleteProject(projectId) {
            if (confirm('Вы уверены, что хотите удалить этот проект? Это действие нельзя отменить.')) {
                // Here you would implement the delete functionality
                alert('Функция удаления будет добавлена позже');
            }
        }
    </script>
</body>
</html>
