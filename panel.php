<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'admin') {
    header('Location: profile.php');
    exit();
}

$displayName = $user['display_name'] ?: $user['username'];

// Get statistics
$stats = [];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$stats['total_users'] = $stmt->fetchColumn();

// Total projects
$stmt = $pdo->query("SELECT COUNT(*) FROM projects");
$stats['total_projects'] = $stmt->fetchColumn();

// Published projects
$stmt = $pdo->query("SELECT COUNT(*) FROM projects WHERE privacy = 'public'");
$stats['published_projects'] = $stmt->fetchColumn();

// Total views
$stmt = $pdo->query("SELECT SUM(view_count) FROM projects");
$stats['total_views'] = $stmt->fetchColumn() ?: 0;

// Recent users
$stmt = $pdo->query("SELECT id, username, display_name, email, created_at, role FROM users ORDER BY created_at DESC LIMIT 10");
$recent_users = $stmt->fetchAll();

// Recent projects
$stmt = $pdo->query("
    SELECT p.*, u.username, u.display_name 
    FROM projects p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 10
");
$recent_projects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - JustSite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-orange-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19Z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Admin Panel</h1>
                        <p class="text-xs text-gray-500">JustSite Management</p>
                    </div>
                </div>
            </div>

            <!-- User Profile -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-orange-500 rounded-full flex items-center justify-center overflow-hidden">
                        <?php if ($user['avatar'] && file_exists($user['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-white font-semibold text-sm">
                                <?php echo strtoupper(substr($displayName, 0, 2)); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($displayName); ?></p>
                        <p class="text-xs text-red-600 font-medium">Administrator</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="panel.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19Z"/>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="profile.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 rounded-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"/>
                            </svg>
                            Мой профиль
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 rounded-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"/>
                            </svg>
                            Настройки
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Bottom Actions -->
            <div class="p-4 border-t border-gray-200">
                <button id="logoutBtn" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z"/>
                    </svg>
                    Logout
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
                        <p class="text-sm text-gray-500">Управление сервисом JustSite</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Admin Access</span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-auto bg-gray-50 p-4 md:p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <!-- Users Card -->
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                        </div>
                        <div class="flex items-end justify-between">
                            <div>
                                <span class="text-sm text-gray-500">Total Users</span>
                                <h4 class="mt-2 text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_users']); ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- Projects Card -->
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="flex items-end justify-between">
                            <div>
                                <span class="text-sm text-gray-500">Total Projects</span>
                                <h4 class="mt-2 text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_projects']); ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- Published Card -->
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex items-end justify-between">
                            <div>
                                <span class="text-sm text-gray-500">Published</span>
                                <h4 class="mt-2 text-2xl font-bold text-gray-800"><?php echo number_format($stats['published_projects']); ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- Views Card -->
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-100 mb-4">
                            <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <div class="flex items-end justify-between">
                            <div>
                                <span class="text-sm text-gray-500">Total Views</span>
                                <h4 class="mt-2 text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_views']); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Users -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <?php foreach ($recent_users as $recent_user): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                                            <span class="text-white font-semibold text-xs">
                                                <?php echo strtoupper(substr($recent_user['display_name'] ?: $recent_user['username'], 0, 2)); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($recent_user['display_name'] ?: $recent_user['username']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                <?php echo htmlspecialchars($recent_user['email'] ?? 'No email'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs px-2 py-1 rounded-full <?php echo $recent_user['role'] === 'admin' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'; ?>">
                                            <?php echo ucfirst($recent_user['role']); ?>
                                        </span>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <?php echo date('M j, Y', strtotime($recent_user['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Projects -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Projects</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <?php foreach ($recent_projects as $project): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($project['title'] ?: 'Untitled Project'); ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                by <?php echo htmlspecialchars($project['display_name'] ?: $project['username']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs px-2 py-1 rounded-full <?php echo $project['privacy'] === 'public' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                            <?php echo $project['privacy'] === 'public' ? 'Published' : 'Draft'; ?>
                                        </span>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <?php echo $project['view_count']; ?> views
                                        </p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Logout functionality
        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Вы уверены, что хотите выйти из системы?')) {
                window.location.href = 'logout.php';
            }
        });
    </script>
</body>
</html>
