<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/config.php';

start_session_if_needed();
header('Content-Type: text/html; charset=utf-8');

// Check if user is logged in
$uid = current_user_id();
if (!$uid) {
    header('Location: login.php');
    exit;
}

// Get slug from URL
$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 - Project Not Found</title></head><body><h1>404 - Project Not Found</h1></body></html>';
    exit;
}

try {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    // Get project by slug (must belong to current user)
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE slug = ? AND user_id = ?');
    $stmt->execute([$slug, $uid]);
    $project = $stmt->fetch();
    
    if (!$project) {
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>404 - Project Not Found</title></head><body><h1>404 - Project Not Found</h1></body></html>';
        exit;
    }
    
    // Get view statistics
    $stmt = $pdo->prepare('
        SELECT 
            COUNT(*) as total_views,
            COUNT(DISTINCT visitor_ip) as unique_views,
            DATE(viewed_at) as view_date,
            COUNT(*) as daily_views
        FROM project_views 
        WHERE project_id = ? 
        GROUP BY DATE(viewed_at) 
        ORDER BY view_date DESC 
        LIMIT 30
    ');
    $stmt->execute([$project['id']]);
    $dailyStats = $stmt->fetchAll();
    
    // Get recent views
    $stmt = $pdo->prepare('
        SELECT visitor_ip, user_agent, referer, viewed_at 
        FROM project_views 
        WHERE project_id = ? 
        ORDER BY viewed_at DESC 
        LIMIT 50
    ');
    $stmt->execute([$project['id']]);
    $recentViews = $stmt->fetchAll();
    
    // Calculate total stats
    $dailyViewsArray = array_column($dailyStats, 'daily_views');
    $totalViews = !empty($dailyViewsArray) ? array_sum($dailyViewsArray) : 0;
    $recentIpsArray = array_column($recentViews, 'visitor_ip');
    $uniqueViews = !empty($recentIpsArray) ? count(array_unique($recentIpsArray)) : 0;
    
} catch (Throwable $e) {
    error_log('Stats error: ' . $e->getMessage());
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>500 - Server Error</title></head><body><h1>500 - Server Error</h1></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика: <?php echo htmlspecialchars($project['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
    <base href="<?php echo APP_BASE_URL; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/main.css?v=2">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            background: linear-gradient(135deg, #0E0E10 0%, #1A1A1E 100%);
            color: #EDEDED; 
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            line-height: 1.6;
        }
        
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 20px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #6C5CE7;
            text-decoration: none;
            margin-bottom: 32px;
            font-weight: 500;
            padding: 12px 20px;
            background: rgba(108, 92, 231, 0.1);
            border: 1px solid rgba(108, 92, 231, 0.2);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: rgba(108, 92, 231, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.3);
        }
        
        .header {
            background: linear-gradient(135deg, #16161A 0%, #1E1E24 100%);
            border: 1px solid #2A2A2E;
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6C5CE7, #A29BFE, #6C5CE7);
            background-size: 200% 100%;
            animation: gradientShift 3s ease-in-out infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .header h1 {
            margin: 0 0 12px;
            color: #EDEDED;
            font-size: 32px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header p {
            margin: 0;
            color: #B8B8BF;
            font-size: 16px;
        }
        
        .header p a {
            color: #6C5CE7;
            text-decoration: none;
            font-weight: 500;
        }
        
        .header p a:hover {
            text-decoration: underline;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #16161A 0%, #1E1E24 100%);
            border: 1px solid #2A2A2E;
            border-radius: 16px;
            padding: 28px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: #6C5CE7;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #6C5CE7, #A29BFE);
        }
        
        .stat-card h3 {
            margin: 0 0 16px;
            color: #EDEDED;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stat-card .value {
            font-size: 36px;
            font-weight: 800;
            color: #6C5CE7;
            margin: 0;
            text-shadow: 0 0 20px rgba(108, 92, 231, 0.3);
        }
        
        .chart-container {
            background: linear-gradient(135deg, #16161A 0%, #1E1E24 100%);
            border: 1px solid #2A2A2E;
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6C5CE7, #A29BFE, #6C5CE7);
        }
        
        .chart-container h3 {
            margin: 0 0 24px;
            color: #EDEDED;
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .chart {
            height: 250px;
            display: flex;
            align-items: end;
            gap: 6px;
            padding: 20px 0;
        }
        
        .chart-bar {
            background: linear-gradient(180deg, #6C5CE7 0%, #A29BFE 100%);
            border-radius: 6px 6px 0 0;
            min-height: 6px;
            flex: 1;
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
        }
        
        .chart-bar:hover {
            background: linear-gradient(180deg, #5B4FCF 0%, #9B8AFF 100%);
            transform: scaleY(1.05);
            box-shadow: 0 0 20px rgba(108, 92, 231, 0.4);
        }
        
        .chart-bar::after {
            content: attr(data-value);
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .chart-bar:hover::after {
            opacity: 1;
        }
        
        .recent-views {
            background: linear-gradient(135deg, #16161A 0%, #1E1E24 100%);
            border: 1px solid #2A2A2E;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .recent-views::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6C5CE7, #A29BFE, #6C5CE7);
        }
        
        .recent-views h3 {
            margin: 0 0 24px;
            color: #EDEDED;
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .view-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #2A2A2E;
            transition: all 0.3s ease;
        }
        
        .view-item:hover {
            background: rgba(108, 92, 231, 0.05);
            border-radius: 8px;
            padding-left: 12px;
            padding-right: 12px;
        }
        
        .view-item:last-child {
            border-bottom: none;
        }
        
        .view-info {
            flex: 1;
        }
        
        .view-ip {
            color: #EDEDED;
            font-weight: 600;
            font-size: 14px;
        }
        
        .view-time {
            color: #7A7A80;
            font-size: 12px;
            margin-top: 4px;
        }
        
        .view-referer {
            color: #B8B8BF;
            font-size: 12px;
            margin-top: 4px;
            word-break: break-all;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7A7A80;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .privacy-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .privacy-public {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        
        .privacy-unlisted {
            background: rgba(255, 193, 7, 0.2);
            color: #FFC107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .privacy-private {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 16px;
            }
            
            .header {
                padding: 24px;
                margin-bottom: 24px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-card .value {
                font-size: 28px;
            }
            
            .chart-container, .recent-views {
                padding: 24px;
            }
            
            .chart {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="profile.php" class="back-link">← Назад к профилю</a>
        
        <div class="header">
            <h1>📊 Статистика: <?php echo htmlspecialchars($project['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
            <p>URL: <a href="view.php?slug=<?php echo urlencode($project['slug']); ?>" target="_blank"><?php echo htmlspecialchars($project['slug'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>👁️ Всего просмотров</h3>
                <p class="value"><?php echo number_format($project['view_count']); ?></p>
            </div>
            <div class="stat-card">
                <h3>👤 Уникальные посетители</h3>
                <p class="value"><?php echo number_format($uniqueViews); ?></p>
            </div>
            <div class="stat-card">
                <h3>🔒 Приватность</h3>
                <div class="privacy-badge privacy-<?php echo $project['privacy']; ?>">
                    <?php 
                    $privacyIcons = ['public' => '🌐', 'unlisted' => '🔗', 'private' => '🔒'];
                    echo $privacyIcons[$project['privacy']] ?? '❓';
                    ?>
                    <?php echo ucfirst($project['privacy']); ?>
                </div>
            </div>
        </div>
        
        <div class="chart-container">
            <h3>📈 Просмотры за последние 30 дней</h3>
            <div class="chart">
                <?php if (!empty($dailyStats)): ?>
                    <?php 
                    $viewsArray = array_column($dailyStats, 'daily_views');
                    $maxViews = !empty($viewsArray) ? max($viewsArray) : 1;
                    foreach ($dailyStats as $stat): 
                        $height = $maxViews > 0 ? ($stat['daily_views'] / $maxViews) * 100 : 0;
                    ?>
                        <div class="chart-bar" style="height: <?php echo $height; ?>%" data-value="<?php echo $stat['daily_views']; ?>" title="<?php echo $stat['view_date']; ?>: <?php echo $stat['daily_views']; ?> просмотров"></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; color: #666; padding: 20px;">
                        Нет данных о просмотрах
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="recent-views">
            <h3>🕒 Последние просмотры</h3>
            <?php if (empty($recentViews)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👁️</div>
                    <p>Пока нет просмотров</p>
                </div>
            <?php else: ?>
                <?php foreach ($recentViews as $view): ?>
                    <div class="view-item">
                        <div class="view-info">
                            <div class="view-ip"><?php echo htmlspecialchars($view['visitor_ip'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                            <div class="view-time"><?php echo date('d.m.Y H:i', strtotime($view['viewed_at'])); ?></div>
                            <?php if ($view['referer']): ?>
                                <div class="view-referer">Откуда: <?php echo htmlspecialchars($view['referer'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
