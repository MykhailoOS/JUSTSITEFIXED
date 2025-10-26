<?php
require_once '../config/database.php';
require_once '../lib/language.php';

header('Content-Type: application/json');

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Get current language
    $currentLang = LanguageManager::getCurrentLanguage();
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];
    
    // New users today
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURDATE()");
    $newUsersToday = $stmt->fetch()['total'];
    
    // New users this week
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $newUsersWeek = $stmt->fetch()['total'];
    
    // Total projects
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects");
    $totalProjects = $stmt->fetch()['total'];
    
    // Public projects
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects WHERE privacy = 'public'");
    $publicProjects = $stmt->fetch()['total'];
    
    // Total views
    $stmt = $pdo->query("SELECT SUM(view_count) as total FROM projects");
    $totalViews = $stmt->fetch()['total'] ?: 0;
    
    // Views today
    $stmt = $pdo->query("SELECT SUM(view_count) as total FROM projects WHERE DATE(updated_at) = CURDATE()");
    $viewsToday = $stmt->fetch()['total'] ?: 0;
    
    // Recent users (last 10)
    $stmt = $pdo->query("SELECT id, name, email, banned, created_at FROM users ORDER BY created_at DESC LIMIT 10");
    $recentUsers = $stmt->fetchAll();
    
    // Top projects (by views)
    $stmt = $pdo->query("
        SELECT p.id, p.title, p.view_count, u.name as user_name 
        FROM projects p 
        LEFT JOIN users u ON p.user_id = u.id 
        ORDER BY p.view_count DESC 
        LIMIT 10
    ");
    $topProjects = $stmt->fetchAll();
    
    // Daily registrations for the last 30 days
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as daily_registrations 
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
        GROUP BY DATE(created_at) 
        ORDER BY date ASC
    ");
    $dailyRegistrationsRaw = $stmt->fetchAll();
    
    // Daily views for the last 30 days
    $stmt = $pdo->query("
        SELECT DATE(updated_at) as date, SUM(view_count) as daily_views 
        FROM projects 
        WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
        GROUP BY DATE(updated_at) 
        ORDER BY date ASC
    ");
    $dailyViewsRaw = $stmt->fetchAll();
    
    // Format data for charts with dates
    $dailyRegistrations = [
        'values' => array_column($dailyRegistrationsRaw, 'daily_registrations'),
        'dates' => array_column($dailyRegistrationsRaw, 'date')
    ];
    
    $dailyViews = [
        'values' => array_column($dailyViewsRaw, 'daily_views'),
        'dates' => array_column($dailyViewsRaw, 'date')
    ];
    
    // Calculate circular chart data
    $publicProjectsPercentage = $totalProjects > 0 ? ($publicProjects / $totalProjects) * 100 : 0;
    $viewsTodayPercentage = $totalViews > 0 ? min(($viewsToday / max($totalViews * 0.1, 1)) * 100, 100) : 0;
    $newUsersWeekPercentage = $totalUsers > 0 ? min(($newUsersWeek / $totalUsers) * 100, 100) : 0;
    $projectsPerUserPercentage = $totalUsers > 0 ? min(($totalProjects / $totalUsers) * 100, 100) : 0;
    
    // Prepare response
    $response = [
        'success' => true,
        'stats' => [
            'totalUsers' => $totalUsers,
            'newUsersToday' => $newUsersToday,
            'newUsersWeek' => $newUsersWeek,
            'totalProjects' => $totalProjects,
            'publicProjects' => $publicProjects,
            'totalViews' => $totalViews,
            'viewsToday' => $viewsToday
        ],
        'charts' => [
            'circular' => [
                'publicProjects' => [
                    'percentage' => $publicProjectsPercentage,
                    'value' => $publicProjects,
                    'total' => $totalProjects
                ],
                'viewsToday' => [
                    'percentage' => $viewsTodayPercentage,
                    'value' => $viewsToday
                ],
                'newUsersWeek' => [
                    'percentage' => $newUsersWeekPercentage,
                    'value' => $newUsersWeek
                ],
                'projectsPerUser' => [
                    'percentage' => $projectsPerUserPercentage,
                    'value' => $totalUsers > 0 ? round($totalProjects / $totalUsers, 1) : 0
                ]
            ],
            'line' => [
                'registrations' => $dailyRegistrations,
                'views' => $dailyViews
            ]
        ],
        'activity' => [
            'recentUsers' => $recentUsers,
            'topProjects' => $topProjects
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
