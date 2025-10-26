<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/language.php';

// Initialize language system
LanguageManager::init();

start_session_if_needed();
header('Content-Type: text/html; charset=utf-8');

// Check if user is logged in and is admin
$uid = current_user_id();
if (!$uid) {
    header('Location: login.php');
    exit;
}

// Simple admin check - you can modify this logic
$pdo = DatabaseConnectionProvider::getConnection();
$stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();

// Check if user is admin (you can modify this condition)
$isAdmin = $user && (
    strpos($user['email'], 'admin') !== false || 
    $user['email'] === 'admin@justsite.com' ||
    $uid == 1 // First user is admin
);

if (!$isAdmin) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><title>403 - Access Denied</title></head><body><h1>403 - Access Denied</h1><p>Admin access required.</p></body></html>';
    exit;
}

try {
    // Get statistics
    // Total users
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM users');
    $stmt->execute();
    $totalUsers = $stmt->fetchColumn();
    
    // New users today
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURDATE()');
    $stmt->execute();
    $newUsersToday = $stmt->fetchColumn();
    
    // New users this week
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
    $stmt->execute();
    $newUsersWeek = $stmt->fetchColumn();
    
    // Total projects
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM projects');
    $stmt->execute();
    $totalProjects = $stmt->fetchColumn();
    
    // Public projects
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM projects WHERE privacy = "public"');
    $stmt->execute();
    $publicProjects = $stmt->fetchColumn();
    
    // Total views
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM project_views');
    $stmt->execute();
    $totalViews = $stmt->fetchColumn();
    
    // Views today
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM project_views WHERE DATE(viewed_at) = CURDATE()');
    $stmt->execute();
    $viewsToday = $stmt->fetchColumn();
    
    // Daily registrations for last 30 days
    $stmt = $pdo->prepare('
        SELECT 
            DATE(created_at) as reg_date,
            COUNT(*) as daily_registrations
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at) 
        ORDER BY reg_date DESC 
        LIMIT 30
    ');
    $stmt->execute();
    $dailyRegistrations = $stmt->fetchAll();
    
    // Daily views for last 30 days
    $stmt = $pdo->prepare('
        SELECT 
            DATE(viewed_at) as view_date,
            COUNT(*) as daily_views
        FROM project_views 
        WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(viewed_at) 
        ORDER BY view_date DESC 
        LIMIT 30
    ');
    $stmt->execute();
    $dailyViews = $stmt->fetchAll();
    
    // Recent users
    $stmt = $pdo->prepare('
        SELECT id, email, name, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 20
    ');
    $stmt->execute();
    $recentUsers = $stmt->fetchAll();
    
    // Most active projects
    $stmt = $pdo->prepare('
        SELECT p.id, p.title, p.slug, p.privacy, u.email as author, COUNT(pv.id) as view_count
        FROM projects p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN project_views pv ON p.id = pv.project_id
        GROUP BY p.id
        ORDER BY view_count DESC
        LIMIT 10
    ');
    $stmt->execute();
    $topProjects = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log('Admin panel error: ' . $e->getMessage());
    $totalUsers = $newUsersToday = $newUsersWeek = $totalProjects = $publicProjects = $totalViews = $viewsToday = 0;
    $dailyRegistrations = $dailyViews = $recentUsers = $topProjects = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo LanguageManager::t('admin_panel'); ?> — JustSite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Стили для анимированных диаграмм */
        .chart-container {
            position: relative;
        }
        
        .circular-chart {
            display: block;
            margin: 10px auto;
            max-width: 80%;
            max-height: 250px;
        }
        
        /* Mobile adaptation for circular charts */
        @media (max-width: 768px) {
            .circular-chart {
                max-width: 100%;
                max-height: 200px;
            }
            
            .percentage {
                font-size: 0.4em;
            }
        }
        
        /* Mobile header improvements */
        @media (max-width: 768px) {
            .mobile-menu-enter {
                animation: slideDown 0.2s ease-out;
            }
            
            .mobile-menu-exit {
                animation: slideUp 0.2s ease-out;
            }
            
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes slideUp {
                from {
                    opacity: 1;
                    transform: translateY(0);
                }
                to {
                    opacity: 0;
                    transform: translateY(-10px);
                }
            }
            
            /* Sticky header for mobile */
            header {
                position: sticky;
                top: 0;
                z-index: 50;
                backdrop-filter: blur(10px);
                background-color: rgba(255, 255, 255, 0.95);
            }
            
            /* Mobile menu button animation */
            #mobile-menu-toggle {
                transition: transform 0.2s ease;
            }
            
            #mobile-menu-toggle:hover {
                transform: scale(1.05);
            }
            
            /* Mobile menu items */
            #mobile-menu a,
            #mobile-menu button {
                transition: all 0.2s ease;
            }
            
            #mobile-menu a:hover,
            #mobile-menu button:hover {
                transform: translateX(4px);
            }
        }
        
        /* Context Menu Fixes */
        .user-context-menu {
            position: absolute !important;
            z-index: 9999 !important;
            background: white !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
            min-width: 140px !important;
        }
        
        .activity-item {
            position: relative;
            z-index: 1;
        }
        
        .activity-item:hover {
            z-index: 10;
        }
        
        /* Ensure context menu appears above everything */
        .user-context-menu.show {
            display: block !important;
            z-index: 9999 !important;
        }
        
        /* Prevent overlap with other elements */
        .recent-users-container {
            position: relative;
            z-index: 1;
        }
        
        .activity-item {
            position: relative;
            z-index: 1;
        }
        
        .activity-item:hover,
        .activity-item:focus-within {
            z-index: 10;
        }
        
        /* Context menu positioning */
        .user-context-menu {
            position: absolute !important;
            z-index: 9999 !important;
            transform: translateZ(0);
            will-change: transform;
        }
        
        @media (max-width: 480px) {
            .circular-chart {
                max-width: 100%;
                max-height: 150px;
            }
            
            .percentage {
                font-size: 0.35em;
            }
            
            .chart-container {
                height: 150px !important;
            }
            
            .chart-container canvas {
                max-width: 100%;
                height: auto;
            }
        }
        
        .circular-chart .circle-bg {
            fill: none;
            stroke: #eee;
            stroke-width: 3.8;
        }
        
        .circular-chart .circle {
            fill: none;
            stroke-width: 2.8;
            stroke-linecap: round;
            stroke-dasharray: 0 100;
            transition: stroke-dasharray 2s ease-in-out;
        }
        
        .circular-chart.orange .circle {
            stroke: #ff9500;
        }
        
        .circular-chart.green .circle {
            stroke: #4CC790;
        }
        
        .circular-chart.blue .circle {
            stroke: #3b82f6;
        }
        
        .circular-chart.purple .circle {
            stroke: #8b5cf6;
        }
        
        .percentage {
            fill: #666;
            font-family: sans-serif;
            font-size: 0.5em;
            text-anchor: middle;
        }
        
        @keyframes progress {
            0% {
                stroke-dasharray: 0 100;
            }
        }
        
        .stat-card {
            transition: all 0.6s ease;
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        
        .stat-card.show {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        
        .chart-bar {
            transition: all 0.6s ease;
            transform-origin: bottom;
        }
        
        .chart-bar:hover {
            opacity: 0.8;
        }
        
        .activity-item {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateX(-20px);
        }
        
        .activity-item.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .activity-item:hover {
            background-color: #f8fafc;
            transform: translateX(4px);
        }
        
        /* Анимация для графиков */
        .chart-container {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease;
        }
        
        .chart-container.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/components/loading-screen.php'; ?>
    
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8">
            <!-- Desktop Header -->
            <div class="hidden md:flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">A</span>
                    </div>
                    <span class="ml-3 text-lg sm:text-xl font-bold text-gray-900">JustSite Admin</span>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="refresh-data" class="text-gray-600 hover:text-gray-900 text-sm flex items-center space-x-1 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors" title="<?php echo LanguageManager::t('refresh'); ?>">
                        <span>🔄</span>
                        <span><?php echo LanguageManager::t('refresh'); ?></span>
                    </button>
                    <a href="templates_ultra_simple.php" class="text-gray-600 hover:text-gray-900 text-sm px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                        📄 Templates
                    </a>
                    <a href="profile.php" class="text-gray-600 hover:text-gray-900 text-sm px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                        ← <?php echo LanguageManager::t('back_to_site'); ?>
                    </a>
                    <a href="logout.php" class="text-gray-600 hover:text-gray-900 text-sm px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors"><?php echo LanguageManager::t('logout'); ?></a>
                </div>
            </div>
            
            <!-- Mobile Header -->
            <div class="md:hidden">
                <div class="flex justify-between items-center h-14 px-2">
                    <!-- Logo and Title -->
                    <div class="flex items-center min-w-0 flex-1">
                        <div class="w-7 h-7 bg-red-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold text-xs">A</span>
                        </div>
                        <span class="ml-2 text-sm font-bold text-gray-900 truncate">Admin</span>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-toggle" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Mobile Menu Dropdown -->
                <div id="mobile-menu" class="hidden bg-white border-t border-gray-200 shadow-lg">
                    <div class="px-4 py-3 space-y-2">
                        <button id="refresh-data-mobile" class="w-full text-left text-gray-600 hover:text-gray-900 text-sm flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <span>🔄</span>
                            <span><?php echo LanguageManager::t('refresh'); ?></span>
                        </button>
                        <a href="templates_ultra_simple.php" class="block text-gray-600 hover:text-gray-900 text-sm px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            📄 Templates
                        </a>
                        <a href="profile.php" class="block text-gray-600 hover:text-gray-900 text-sm px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            ← <?php echo LanguageManager::t('back_to_site'); ?>
                        </a>
                        <a href="logout.php" class="block text-gray-600 hover:text-gray-900 text-sm px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <?php echo LanguageManager::t('logout'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <!-- Overview Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 stat-card" data-target="<?php echo $totalUsers; ?>">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">👥</span>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-blue-600 stat-number" data-stat="total-users">0</div>
                        <div class="text-gray-600 text-sm"><?php echo LanguageManager::t('total_users'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 stat-card" data-target="<?php echo $totalProjects; ?>">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">📁</span>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-green-600 stat-number" data-stat="total-projects">0</div>
                        <div class="text-gray-600 text-sm"><?php echo LanguageManager::t('total_projects'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 stat-card" data-target="<?php echo $totalViews; ?>">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">👁️</span>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-purple-600 stat-number" data-stat="total-views">0</div>
                        <div class="text-gray-600 text-sm"><?php echo LanguageManager::t('total_views'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 stat-card" data-target="<?php echo $newUsersToday; ?>">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">🆕</span>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-orange-600 stat-number">0</div>
                        <div class="text-gray-600 text-sm"><?php echo LanguageManager::t('new_today'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Circular Charts -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">📊 <?php echo LanguageManager::t('public_projects'); ?></h3>
                <div class="flex justify-center">
                    <svg viewBox="0 0 36 36" class="circular-chart green">
                        <path class="circle-bg" d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="circle" data-percentage="<?php echo $totalProjects > 0 ? ($publicProjects / $totalProjects) * 100 : 0; ?>" stroke-dasharray="0, 100" d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <text x="18" y="20.35" class="percentage"><?php echo $totalProjects > 0 ? round(($publicProjects / $totalProjects) * 100) : 0; ?>%</text>
                    </svg>
                </div>
                <div class="text-center mt-2">
                    <div class="text-xs text-gray-600"><?php echo LanguageManager::t('public'); ?></div>
                    <div class="text-xs sm:text-sm font-semibold"><?php echo $publicProjects; ?> / <?php echo $totalProjects; ?></div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">📈 <?php echo LanguageManager::t('activity_today'); ?></h3>
                <div class="flex justify-center">
                    <svg viewBox="0 0 36 36" class="circular-chart blue">
                        <path class="circle-bg" d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="circle" data-percentage="<?php echo $totalViews > 0 ? min(($viewsToday / max($totalViews * 0.1, 1)) * 100, 100) : 0; ?>" stroke-dasharray="0, 100" d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <text x="18" y="20.35" class="percentage"><?php echo $viewsToday; ?></text>
                    </svg>
                </div>
                <div class="text-center mt-2">
                    <div class="text-xs text-gray-600"><?php echo LanguageManager::t('views_today'); ?></div>
                    <div class="text-xs sm:text-sm font-semibold"><?php echo LanguageManager::t('of_total', ['total' => $totalViews]); ?></div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">👥 <?php echo LanguageManager::t('growth_week'); ?></h3>
                <div class="flex justify-center">
                    <svg viewBox="0 0 36 36" class="circular-chart purple">
                        <path class="circle-bg" d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="circle" data-percentage="<?php echo $totalUsers > 0 ? min(($newUsersWeek / max($totalUsers * 0.2, 1)) * 100, 100) : 0; ?>" stroke-dasharray="0, 100" d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <text x="18" y="20.35" class="percentage"><?php echo $newUsersWeek; ?></text>
                    </svg>
                </div>
                <div class="text-center mt-2">
                    <div class="text-xs text-gray-600"><?php echo LanguageManager::t('new_week'); ?></div>
                    <div class="text-xs sm:text-sm font-semibold"><?php echo $totalUsers > 0 ? round(($newUsersWeek / $totalUsers) * 100) : 0; ?>% <?php echo LanguageManager::t('growth'); ?></div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">🔥 <?php echo LanguageManager::t('conversion'); ?></h3>
                <div class="flex justify-center">
                    <svg viewBox="0 0 36 36" class="circular-chart orange">
                        <path class="circle-bg" d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="circle" data-percentage="<?php echo $totalUsers > 0 ? min((($totalUsers > 0 ? $totalProjects / $totalUsers : 0) * 100), 100) : 0; ?>" stroke-dasharray="0, 100" d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <text x="18" y="20.35" class="percentage"><?php echo $totalUsers > 0 ? round(($totalProjects / $totalUsers) * 100) : 0; ?>%</text>
                    </svg>
                </div>
                <div class="text-center mt-2">
                    <div class="text-xs text-gray-600"><?php echo LanguageManager::t('projects_per_user'); ?></div>
                    <div class="text-xs sm:text-sm font-semibold"><?php echo $totalUsers > 0 ? round($totalProjects / $totalUsers, 1) : 0; ?> <?php echo LanguageManager::t('projects'); ?></div>
                </div>
            </div>
        </div>

        <!-- Charts and Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8 mb-8">
            <!-- Registrations Chart -->
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">📝 <?php echo LanguageManager::t('registrations_month'); ?></h3>
                <?php if (!empty($dailyRegistrations)): ?>
                    <div class="chart-container" style="height: 200px; position: relative;">
                        <canvas id="registrationsChart" width="400" height="200"></canvas>
                    </div>
                    <script>
                        const registrationsData = {
                            labels: [<?php 
                                $reversedRegs = array_reverse($dailyRegistrations);
                                echo implode(',', array_map(function($stat) {
                                    return '"' . date('d.m', strtotime($stat['reg_date'])) . '"';
                                }, $reversedRegs));
                            ?>],
                            values: [<?php 
                                echo implode(',', array_column($reversedRegs, 'daily_registrations'));
                            ?>]
                        };
                    </script>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            📊
                        </div>
                        <p><?php echo LanguageManager::t('no_registration_data'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Views Chart -->
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">👁️ <?php echo LanguageManager::t('views_month'); ?></h3>
                <?php if (!empty($dailyViews)): ?>
                    <div class="chart-container" style="height: 200px; position: relative;">
                        <canvas id="viewsChart" width="400" height="200"></canvas>
                    </div>
                    <script>
                        const viewsData = {
                            labels: [<?php 
                                $reversedViews = array_reverse($dailyViews);
                                echo implode(',', array_map(function($stat) {
                                    return '"' . date('d.m', strtotime($stat['view_date'])) . '"';
                                }, $reversedViews));
                            ?>],
                            values: [<?php 
                                echo implode(',', array_column($reversedViews, 'daily_views'));
                            ?>]
                        };
                    </script>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            📊
                        </div>
                        <p><?php echo LanguageManager::t('no_views_data'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8">
            <!-- Recent Users -->
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">👤 <?php echo LanguageManager::t('recent_users'); ?></h3>
                <?php if (!empty($recentUsers)): ?>
                    <div class="space-y-3 recent-users-container">
                        <?php foreach (array_slice($recentUsers, 0, 10) as $user): ?>
                            <div class="activity-item flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 cursor-pointer relative" data-user-id="<?php echo $user['id']; ?>" data-user-name="<?php echo htmlspecialchars($user['name'] ?: LanguageManager::t('no_name')); ?>" data-user-email="<?php echo htmlspecialchars($user['email']); ?>" data-user-banned="<?php echo isset($user['banned']) && $user['banned'] ? 'true' : 'false'; ?>">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 truncate flex items-center space-x-2">
                                        <span><?php echo htmlspecialchars($user['name'] ?: LanguageManager::t('no_name')); ?></span>
                                        <?php if (isset($user['banned']) && $user['banned']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                🚫 <?php echo LanguageManager::t('banned'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ✅ <?php echo LanguageManager::t('active'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs sm:text-sm text-gray-500 truncate">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="text-xs text-gray-400">
                                        <?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?>
                                    </div>
                                    <button class="user-menu-trigger p-1 hover:bg-gray-200 rounded" title="<?php echo LanguageManager::t('user_actions'); ?>">
                                        ⋮
                                    </button>
                                </div>
                                
                                <!-- Context Menu -->
                                <div class="user-context-menu absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl z-[9999] hidden min-w-[140px] sm:min-w-[160px]">
                                    <div class="py-1">
                                        <button class="user-action-btn w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100" data-action="view">
                                            👁️ <?php echo LanguageManager::t('view_details'); ?>
                                        </button>
                                        <button class="user-action-btn w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100" data-action="edit">
                                            ✏️ <?php echo LanguageManager::t('edit_user'); ?>
                                        </button>
                                        <button class="user-action-btn w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100" data-action="toggle-ban">
                                            <span class="ban-text">🚫 <?php echo LanguageManager::t('ban_user'); ?></span>
                                            <span class="unban-text hidden">✅ <?php echo LanguageManager::t('unban_user'); ?></span>
                                        </button>
                                        <button class="user-action-btn w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-red-600 hover:bg-red-50" data-action="delete">
                                            🗑️ <?php echo LanguageManager::t('delete_user'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <?php echo LanguageManager::t('no_users'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Top Projects -->
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">🔥 <?php echo LanguageManager::t('popular_projects'); ?></h3>
                <?php if (!empty($topProjects)): ?>
                    <div class="space-y-3 top-projects-container">
                        <?php foreach (array_slice($topProjects, 0, 10) as $project): ?>
                            <div class="activity-item flex items-center justify-between py-2 px-3 rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 truncate">
                                        <?php echo htmlspecialchars($project['title'] ?: LanguageManager::t('no_title')); ?>
                                    </div>
                                    <div class="text-sm text-gray-500 truncate">
                                        <?php echo htmlspecialchars($project['author']); ?>
                                        <?php if ($project['privacy'] === 'public' && $project['slug']): ?>
                                            • <a href="view.php?slug=<?php echo htmlspecialchars($project['slug']); ?>" target="_blank" class="text-blue-600 hover:underline"><?php echo LanguageManager::t('view'); ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-sm font-medium text-purple-600">
                                    <?php echo $project['view_count']; ?> 👁️
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <?php echo LanguageManager::t('no_projects'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Анимация счетчиков
        document.addEventListener('DOMContentLoaded', function() {
            // Ждем скрытия заставки загрузки
            setTimeout(() => {
                // Анимация появления карточек
                const statCards = document.querySelectorAll('.stat-card');
                
                statCards.forEach((card, index) => {
                    const target = parseInt(card.dataset.target) || 0;
                    const numberElement = card.querySelector('.stat-number');
                    
                    setTimeout(() => {
                        card.classList.add('show');
                        animateNumber(numberElement, 0, target, 1500);
                    }, index * 200);
                });
                
                // Анимация графиков
                <?php if (!empty($dailyRegistrations)): ?>
                setTimeout(() => {
                    const chartContainer = document.querySelector('#registrationsChart').closest('.chart-container');
                    if (chartContainer) {
                        chartContainer.classList.add('show');
                    }
                    drawChart('registrationsChart', registrationsData, '#10b981');
                }, 800);
                <?php endif; ?>
                
                <?php if (!empty($dailyViews)): ?>
                setTimeout(() => {
                    const chartContainer = document.querySelector('#viewsChart').closest('.chart-container');
                    if (chartContainer) {
                        chartContainer.classList.add('show');
                    }
                    drawChart('viewsChart', viewsData, '#3b82f6');
                }, 1000);
                <?php endif; ?>
                
                // Анимация круговых графиков
                setTimeout(() => {
                    animateCircularCharts();
                }, 600);
                
                // Анимация элементов активности
                setTimeout(() => {
                    const activityItems = document.querySelectorAll('.activity-item');
                    activityItems.forEach((item, index) => {
                        setTimeout(() => {
                            item.classList.add('show');
                        }, index * 100);
                    });
                }, 1200);
            }, 3000); // Ждем 3 секунды после загрузки страницы
        });
        
        function animateNumber(element, start, end, duration) {
            const startTime = Date.now();
            
            function update() {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easeProgress = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(start + (end - start) * easeProgress);
                
                element.textContent = current.toLocaleString();
                
                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            
            update();
        }

        function animateCircularCharts() {
            const circles = document.querySelectorAll('.circular-chart .circle');
            
            circles.forEach((circle, index) => {
                setTimeout(() => {
                    const percentage = parseFloat(circle.getAttribute('data-percentage')) || 0;
                    circle.style.strokeDasharray = `${percentage}, 100`;
                }, index * 200);
            });
        }
        
        function drawChart(canvasId, data, color) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            const padding = 30;
            const chartWidth = canvas.width - padding * 2;
            const chartHeight = canvas.height - padding * 2;
            const maxValue = Math.max(...data.values) || 1;
            
            // Градиенты
            const gradientFill = ctx.createLinearGradient(0, padding, 0, canvas.height - padding);
            gradientFill.addColorStop(0, color + '30');
            gradientFill.addColorStop(1, color + '05');
            
            // Анимация
            let animationProgress = 0;
            const animationDuration = 2000;
            const startTime = Date.now();
            
            function animate() {
                const elapsed = Date.now() - startTime;
                animationProgress = Math.min(elapsed / animationDuration, 1);
                const easeProgress = 1 - Math.pow(1 - animationProgress, 3);
                
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // Сетка
                ctx.strokeStyle = '#f3f4f6';
                ctx.lineWidth = 1;
                
                for (let i = 0; i <= 4; i++) {
                    const y = padding + (chartHeight / 4) * i;
                    ctx.beginPath();
                    ctx.moveTo(padding, y);
                    ctx.lineTo(canvas.width - padding, y);
                    ctx.stroke();
                }
                
                // Подписи осей Y
                ctx.fillStyle = '#6b7280';
                ctx.font = '12px Arial';
                ctx.textAlign = 'right';
                for (let i = 0; i <= 4; i++) {
                    const y = padding + (chartHeight / 4) * i;
                    const value = Math.round((maxValue / 4) * (4 - i));
                    ctx.fillText(value.toString(), padding - 10, y + 4);
                }
                
                // Подписи осей X (даты)
                ctx.textAlign = 'center';
                ctx.fillStyle = '#6b7280';
                ctx.font = '11px Arial';
                if (data.dates && data.dates.length > 0) {
                    for (let i = 0; i < data.dates.length; i++) {
                        const x = padding + (chartWidth / (data.dates.length - 1 || 1)) * i;
                        const date = new Date(data.dates[i]);
                        const dateStr = date.toLocaleDateString('uk-UA', { month: 'short', day: 'numeric' });
                        ctx.fillText(dateStr, x, canvas.height - 5);
                    }
                }
                
                // Данные
                if (data.values.length > 0) {
                    const stepX = chartWidth / (data.values.length - 1 || 1);
                    
                    ctx.beginPath();
                    const points = [];
                    
                    for (let i = 0; i < data.values.length; i++) {
                        const x = padding + i * stepX;
                        const y = padding + chartHeight - (data.values[i] / maxValue) * chartHeight * easeProgress;
                        points.push({x, y});
                        
                        if (i === 0) {
                            ctx.moveTo(x, y);
                        } else {
                            ctx.lineTo(x, y);
                        }
                    }
                    
                    // Линия
                    ctx.strokeStyle = color;
                    ctx.lineWidth = 3;
                    ctx.lineCap = 'round';
                    ctx.stroke();
                    
                    // Заливка
                    ctx.lineTo(canvas.width - padding, canvas.height - padding);
                    ctx.lineTo(padding, canvas.height - padding);
                    ctx.closePath();
                    ctx.fillStyle = gradientFill;
                    ctx.fill();
                    
                    // Точки с информацией
                    points.forEach((point, index) => {
                        ctx.beginPath();
                        ctx.arc(point.x, point.y, 4, 0, Math.PI * 2);
                        ctx.fillStyle = '#ffffff';
                        ctx.fill();
                        ctx.strokeStyle = color;
                        ctx.lineWidth = 2;
                        ctx.stroke();
                        
                        // Информационная подпись при наведении
                        if (data.values && data.values[index] !== undefined) {
                            const value = data.values[index];
                            const date = data.dates && data.dates[index] ? new Date(data.dates[index]).toLocaleDateString('uk-UA') : '';
                            
                            // Показываем информацию рядом с точкой
                            ctx.fillStyle = '#374151';
                            ctx.font = '10px Arial';
                            ctx.textAlign = 'center';
                            const infoText = `${value}${date ? ' (' + date + ')' : ''}`;
                            ctx.fillText(infoText, point.x, point.y - 10);
                        }
                    });
                }
                
                if (animationProgress < 1) {
                    requestAnimationFrame(animate);
                }
            }
            
            animate();
        }
    </script>


    <script>
        // Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuToggle && mobileMenu) {
                mobileMenuToggle.addEventListener('click', function() {
                    if (mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.remove('hidden');
                        mobileMenu.classList.add('mobile-menu-enter');
                        mobileMenu.classList.remove('mobile-menu-exit');
                    } else {
                        mobileMenu.classList.add('mobile-menu-exit');
                        mobileMenu.classList.remove('mobile-menu-enter');
                        setTimeout(() => {
                            mobileMenu.classList.add('hidden');
                        }, 200);
                    }
                });
                
                // Close menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!mobileMenuToggle.contains(e.target) && !mobileMenu.contains(e.target)) {
                        if (!mobileMenu.classList.contains('hidden')) {
                            mobileMenu.classList.add('mobile-menu-exit');
                            mobileMenu.classList.remove('mobile-menu-enter');
                            setTimeout(() => {
                                mobileMenu.classList.add('hidden');
                            }, 200);
                        }
                    }
                });
            }
            
            // Mobile refresh button
            const refreshDataMobile = document.getElementById('refresh-data-mobile');
            if (refreshDataMobile) {
                refreshDataMobile.addEventListener('click', function() {
                    const button = document.getElementById('refresh-data');
                    if (button) {
                        button.click();
                    }
                    mobileMenu.classList.add('hidden');
                });
            }
        });

        // Context Menu for User Management
        document.addEventListener('DOMContentLoaded', function() {
            // Handle menu trigger clicks
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('user-menu-trigger')) {
                    e.stopPropagation();
                    const userItem = e.target.closest('.activity-item');
                    const menu = userItem.querySelector('.user-context-menu');
                    
                    // Close all other menus
                    document.querySelectorAll('.user-context-menu').forEach(m => {
                        if (m !== menu) {
                            m.classList.add('hidden');
                            m.classList.remove('show');
                        }
                    });
                    
                    // Toggle current menu with proper positioning
                    if (menu.classList.contains('hidden')) {
                        menu.classList.remove('hidden');
                        menu.classList.add('show');
                        
                        // Ensure menu is positioned correctly
                        const rect = userItem.getBoundingClientRect();
                        const menuRect = menu.getBoundingClientRect();
                        const viewportHeight = window.innerHeight;
                        const viewportWidth = window.innerWidth;
                        
                        // Check if menu would go off screen
                        if (rect.bottom + menuRect.height > viewportHeight) {
                            menu.style.top = 'auto';
                            menu.style.bottom = '100%';
                            menu.style.marginTop = '0';
                            menu.style.marginBottom = '4px';
                        } else {
                            menu.style.top = '100%';
                            menu.style.bottom = 'auto';
                            menu.style.marginTop = '4px';
                            menu.style.marginBottom = '0';
                        }
                        
                        // Check if menu would go off right edge
                        if (rect.right + menuRect.width > viewportWidth) {
                            menu.style.left = 'auto';
                            menu.style.right = '0';
                        } else {
                            menu.style.left = 'auto';
                            menu.style.right = '0';
                        }
                    } else {
                        menu.classList.add('hidden');
                        menu.classList.remove('show');
                    }
                    
                    // Update ban/unban text based on user status
                    const isBanned = userItem.dataset.userBanned === 'true';
                    const banText = menu.querySelector('.ban-text');
                    const unbanText = menu.querySelector('.unban-text');
                    
                    if (isBanned) {
                        banText.classList.add('hidden');
                        unbanText.classList.remove('hidden');
                    } else {
                        banText.classList.remove('hidden');
                        unbanText.classList.add('hidden');
                    }
                } else {
                    // Close all menus when clicking outside
                    document.querySelectorAll('.user-context-menu').forEach(menu => {
                        menu.classList.add('hidden');
                        menu.classList.remove('show');
                    });
                }
            });
            
            // Handle action button clicks
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('user-action-btn')) {
                    const action = e.target.dataset.action;
                    const userItem = e.target.closest('.activity-item');
                    const userId = userItem.dataset.userId;
                    const userName = userItem.dataset.userName;
                    const userEmail = userItem.dataset.userEmail;
                    const isBanned = userItem.dataset.userBanned === 'true';
                    
                    // Close menu
                    const menu = userItem.querySelector('.user-context-menu');
                    menu.classList.add('hidden');
                    menu.classList.remove('show');
                    
                    // Handle different actions
                    switch(action) {
                        case 'view':
                            showUserDetails(userName, userEmail, isBanned, userItem);
                            break;
                        case 'edit':
                            editUser(userId, userName);
                            break;
                        case 'toggle-ban':
                            toggleUserBan(userId, userName, isBanned);
                            break;
                        case 'delete':
                            deleteUser(userId, userName);
                            break;
                    }
                }
            });
        });
        
        function showUserDetails(name, email, isBanned, userItem) {
            const createdDate = userItem.querySelector('.text-xs').textContent;
            alert(`<?php echo LanguageManager::t('user_details'); ?>:\n\n<?php echo LanguageManager::t('name'); ?>: ${name}\n<?php echo LanguageManager::t('email'); ?>: ${email}\n<?php echo LanguageManager::t('status'); ?>: ${isBanned ? '<?php echo LanguageManager::t('banned'); ?>' : '<?php echo LanguageManager::t('active'); ?>'}\n<?php echo LanguageManager::t('created'); ?>: ${createdDate}`);
        }
        
        function editUser(userId, currentName) {
            const newName = prompt('<?php echo LanguageManager::t('enter_new_name'); ?>:', currentName);
            if (newName !== null && newName.trim() !== '') {
                performUserAction('edit', userId, { name: newName.trim() });
            }
        }
        
        function toggleUserBan(userId, userName, isBanned) {
            const action = isBanned ? 'unban' : 'ban';
            const actionText = isBanned ? '<?php echo LanguageManager::t('unban_user'); ?>' : '<?php echo LanguageManager::t('ban_user'); ?>';
            const confirmText = isBanned ? '<?php echo LanguageManager::t('confirm_unban_user'); ?>' : '<?php echo LanguageManager::t('confirm_ban_user'); ?>';
            
            if (confirm(confirmText)) {
                performUserAction(action, userId);
            }
        }
        
        function deleteUser(userId, userName) {
            if (confirm(`<?php echo LanguageManager::t('confirm_delete_user'); ?>: ${userName}?`)) {
                performUserAction('delete', userId);
            }
        }
        
        async function performUserAction(action, userId, data = {}) {
            try {
                const response = await fetch('api/admin_user_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action, userId, ...data })
                });
                
                const result = await response.json();
                if (result.success) {
                    showNotification('✅ ' + result.message, 'success');
                    // Update user status in UI if it's a ban/unban action
                    if (action === 'ban' || action === 'unban') {
                        updateUserStatusInUI(userId, action === 'ban');
                    }
                    // Reload page to update data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification('❌ ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('❌ <?php echo LanguageManager::t('error_performing_action'); ?>', 'error');
            }
        }
        
        function updateUserStatusInUI(userId, isBanned) {
            const userItem = document.querySelector(`[data-user-id="${userId}"]`);
            if (!userItem) return;
            
            // Update data attribute
            userItem.dataset.userBanned = isBanned.toString();
            
            // Update status badge
            const statusContainer = userItem.querySelector('.font-medium');
            if (statusContainer) {
                const existingBadge = statusContainer.querySelector('.inline-flex');
                if (existingBadge) {
                    existingBadge.remove();
                }
                
                const newBadge = document.createElement('span');
                newBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium';
                
                if (isBanned) {
                    newBadge.className += ' bg-red-100 text-red-800';
                    newBadge.innerHTML = '🚫 <?php echo LanguageManager::t('banned'); ?>';
                } else {
                    newBadge.className += ' bg-green-100 text-green-800';
                    newBadge.innerHTML = '✅ <?php echo LanguageManager::t('active'); ?>';
                }
                
                statusContainer.appendChild(newBadge);
            }
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                type === 'info' ? 'bg-blue-500 text-white' :
                'bg-gray-500 text-white'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // Animate number changes
        function animateNumber(element, start, end, duration) {
            const startTime = performance.now();
            const difference = end - start;
            
            function updateNumber(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Easing function for smooth animation
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(start + (difference * easeOut));
                
                element.textContent = current.toLocaleString();
                
                if (progress < 1) {
                    requestAnimationFrame(updateNumber);
                }
            }
            
            requestAnimationFrame(updateNumber);
        }

        // Dynamic data updates
        let updateInterval;
        
        function startDataUpdates() {
            // Update data every 30 seconds
            updateInterval = setInterval(updateDashboardData, 30000);
        }
        
        function stopDataUpdates() {
            if (updateInterval) {
                clearInterval(updateInterval);
            }
        }
        
        async function updateDashboardData() {
            try {
                const response = await fetch('api/admin_stats.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    updateStatsCards(data.stats);
                    updateCharts(data.charts);
                    updateActivity(data.activity);
                } else {
                    throw new Error(data.error || 'Unknown error');
                }
            } catch (error) {
                console.error('Data update failed:', error);
                throw error; // Re-throw to handle in button click
            }
        }
        
        function updateStatsCards(stats) {
            // Update total users
            const totalUsersElement = document.querySelector('[data-stat="total-users"]');
            if (totalUsersElement) {
                animateNumber(totalUsersElement, parseInt(totalUsersElement.textContent), stats.totalUsers, 1000);
            }
            
            // Update new users today
            const newUsersTodayElement = document.querySelector('[data-stat="new-users-today"]');
            if (newUsersTodayElement) {
                animateNumber(newUsersTodayElement, parseInt(newUsersTodayElement.textContent), stats.newUsersToday, 1000);
            }
            
            // Update total projects
            const totalProjectsElement = document.querySelector('[data-stat="total-projects"]');
            if (totalProjectsElement) {
                animateNumber(totalProjectsElement, parseInt(totalProjectsElement.textContent), stats.totalProjects, 1000);
            }
            
            // Update public projects
            const publicProjectsElement = document.querySelector('[data-stat="public-projects"]');
            if (publicProjectsElement) {
                animateNumber(publicProjectsElement, parseInt(publicProjectsElement.textContent), stats.publicProjects, 1000);
            }
            
            // Update total views
            const totalViewsElement = document.querySelector('[data-stat="total-views"]');
            if (totalViewsElement) {
                animateNumber(totalViewsElement, parseInt(totalViewsElement.textContent), stats.totalViews, 1000);
            }
            
            // Update views today
            const viewsTodayElement = document.querySelector('[data-stat="views-today"]');
            if (viewsTodayElement) {
                animateNumber(viewsTodayElement, parseInt(viewsTodayElement.textContent), stats.viewsToday, 1000);
            }
        }
        
        function updateCharts(charts) {
            // Update circular charts
            if (charts.circular) {
                updateCircularCharts(charts.circular);
            }
            
            // Update line charts
            if (charts.line) {
                updateLineCharts(charts.line);
            }
        }
        
        function updateCircularCharts(circularData) {
            // Update public projects percentage
            if (circularData.publicProjects) {
                const circle = document.querySelector('.circular-chart.green .circle');
                if (circle) {
                    const percentage = circularData.publicProjects.percentage;
                    circle.style.strokeDasharray = `${percentage}, 100`;
                    const textElement = circle.parentElement.querySelector('.percentage');
                    if (textElement) {
                        textElement.textContent = `${Math.round(percentage)}%`;
                    }
                }
            }
            
            // Update views today
            if (circularData.viewsToday) {
                const circle = document.querySelector('.circular-chart.blue .circle');
                if (circle) {
                    const percentage = circularData.viewsToday.percentage;
                    circle.style.strokeDasharray = `${percentage}, 100`;
                    const textElement = circle.parentElement.querySelector('.percentage');
                    if (textElement) {
                        textElement.textContent = circularData.viewsToday.value;
                    }
                }
            }
            
            // Update new users week
            if (circularData.newUsersWeek) {
                const circle = document.querySelector('.circular-chart.purple .circle');
                if (circle) {
                    const percentage = circularData.newUsersWeek.percentage;
                    circle.style.strokeDasharray = `${percentage}, 100`;
                    const textElement = circle.parentElement.querySelector('.percentage');
                    if (textElement) {
                        textElement.textContent = circularData.newUsersWeek.value;
                    }
                }
            }
            
            // Update projects per user
            if (circularData.projectsPerUser) {
                const circle = document.querySelector('.circular-chart.orange .circle');
                if (circle) {
                    const percentage = circularData.projectsPerUser.percentage;
                    circle.style.strokeDasharray = `${percentage}, 100`;
                    const textElement = circle.parentElement.querySelector('.percentage');
                    if (textElement) {
                        textElement.textContent = `${Math.round(percentage)}%`;
                    }
                }
            }
        }
        
        function updateLineCharts(lineData) {
            // Update registrations chart
            if (lineData.registrations && window.registrationsData) {
                window.registrationsData = lineData.registrations;
                const canvas = document.getElementById('registrationsChart');
                if (canvas) {
                    drawChart('registrationsChart', lineData.registrations, '#10b981');
                }
            }
            
            // Update views chart
            if (lineData.views && window.viewsData) {
                window.viewsData = lineData.views;
                const canvas = document.getElementById('viewsChart');
                if (canvas) {
                    drawChart('viewsChart', lineData.views, '#3b82f6');
                }
            }
        }
        
        function updateActivity(activity) {
            // Update recent users
            if (activity.recentUsers) {
                updateRecentUsers(activity.recentUsers);
            }
            
            // Update top projects
            if (activity.topProjects) {
                updateTopProjects(activity.topProjects);
            }
        }
        
        function updateRecentUsers(users) {
            const container = document.querySelector('.recent-users-container');
            if (!container) return;
            
            container.innerHTML = users.map(user => `
                <div class="activity-item flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 cursor-pointer relative" data-user-id="${user.id}" data-user-name="${user.name || '<?php echo LanguageManager::t('no_name'); ?>'}" data-user-email="${user.email}" data-user-banned="${user.banned}">
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate flex items-center space-x-2">
                            <span>${user.name || '<?php echo LanguageManager::t('no_name'); ?>'}</span>
                            ${user.banned ? 
                                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">🚫 <?php echo LanguageManager::t('banned'); ?></span>' :
                                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">✅ <?php echo LanguageManager::t('active'); ?></span>'
                            }
                        </div>
                        <div class="text-xs sm:text-sm text-gray-500 truncate">
                            ${user.email}
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="text-xs text-gray-400">
                            ${new Date(user.created_at).toLocaleDateString('ru-RU')} ${new Date(user.created_at).toLocaleTimeString('ru-RU', {hour: '2-digit', minute: '2-digit'})}
                        </div>
                        <button class="user-menu-trigger p-1 hover:bg-gray-200 rounded" title="<?php echo LanguageManager::t('user_actions'); ?>">
                            ⋮
                        </button>
                    </div>
                    
                    <!-- Context Menu -->
                    <div class="user-context-menu absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl z-[9999] hidden min-w-[140px] sm:min-w-[160px]">
                        <div class="py-1">
                            <button class="user-action-btn w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100" data-action="view">
                                👁️ <?php echo LanguageManager::t('view_details'); ?>
                            </button>
                            <button class="user-action-btn w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100" data-action="edit">
                                ✏️ <?php echo LanguageManager::t('edit_user'); ?>
                            </button>
                            <button class="user-action-btn w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100" data-action="toggle-ban">
                                <span class="ban-text">🚫 <?php echo LanguageManager::t('ban_user'); ?></span>
                                <span class="unban-text hidden">✅ <?php echo LanguageManager::t('unban_user'); ?></span>
                            </button>
                            <button class="user-action-btn w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-red-600 hover:bg-red-50" data-action="delete">
                                🗑️ <?php echo LanguageManager::t('delete_user'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function updateTopProjects(projects) {
            const container = document.querySelector('.top-projects-container');
            if (!container) return;
            
            container.innerHTML = projects.map(project => `
                <div class="activity-item flex items-center justify-between py-2 px-3 rounded-lg">
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">
                            ${project.title || '<?php echo LanguageManager::t('no_title'); ?>'}
                        </div>
                        <div class="text-xs sm:text-sm text-gray-500 truncate">
                            ${project.user_name || '<?php echo LanguageManager::t('no_name'); ?>'}
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="text-xs text-gray-400">
                            ${project.view_count} <?php echo LanguageManager::t('views'); ?>
                        </div>
                        <a href="dashboard.php?project=${project.id}" class="text-blue-600 hover:text-blue-900 text-xs">
                            <?php echo LanguageManager::t('view'); ?>
                        </a>
                    </div>
                </div>
            `).join('');
        }
        
        // Start data updates when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for initial animations to complete
            setTimeout(() => {
                startDataUpdates();
            }, 5000);
            
            // Manual refresh button
            document.getElementById('refresh-data').addEventListener('click', function() {
                const button = this;
                button.style.transform = 'rotate(360deg)';
                button.style.transition = 'transform 0.5s ease';
                button.disabled = true;
                
                updateDashboardData().then(() => {
                    setTimeout(() => {
                        button.style.transform = 'rotate(0deg)';
                        button.disabled = false;
                    }, 500);
                }).catch(error => {
                    console.error('Refresh failed:', error);
                    button.style.transform = 'rotate(0deg)';
                    button.disabled = false;
                    alert('Ошибка обновления данных. Попробуйте еще раз.');
                });
            });
        });
        
        // Stop updates when page is hidden
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopDataUpdates();
            } else {
                startDataUpdates();
            }
        });
    </script>
</body>
</html>
