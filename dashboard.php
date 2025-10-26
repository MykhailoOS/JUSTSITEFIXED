<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/language.php';
require_auth();

$pdo = DatabaseConnectionProvider::getConnection();
$uid = current_user_id();

// Get project info
$project = null;
$projectId = $_GET['project'] ?? null;
if (!$projectId) {
    header('Location: profile.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ? AND user_id = ?');
$stmt->execute([$projectId, $uid]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: profile.php');
    exit;
}

// Get view statistics if project exists
$dailyStats = [];
$recentViews = [];
$totalViews = 0;
$uniqueViews = 0;

try {
    // Get daily stats
    $stmt = $pdo->prepare('
        SELECT 
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
        LIMIT 20
    ');
    $stmt->execute([$project['id']]);
    $recentViews = $stmt->fetchAll();
    
    // Calculate totals
    $dailyViewsArray = array_column($dailyStats, 'daily_views');
    $totalViews = !empty($dailyViewsArray) ? array_sum($dailyViewsArray) : 0;
    $recentIpsArray = array_column($recentViews, 'visitor_ip');
    $uniqueViews = !empty($recentIpsArray) ? count(array_unique($recentIpsArray)) : 0;
    
} catch (Exception $e) {
    error_log('Dashboard stats error: ' . $e->getMessage());
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
    <title><?php echo htmlspecialchars($project['title'] ?: LanguageManager::t('dashboard_project')); ?> — <?php echo LanguageManager::t('dashboard_title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Стили для красивых алертов */
        .alert-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .alert-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .alert-box {
            background: white;
            border-radius: 16px;
            padding: 24px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s ease;
        }
        
        .alert-overlay.show .alert-box {
            transform: scale(1) translateY(0);
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            z-index: 1001;
            transform: translateX(400px);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 4px solid #10b981;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.error {
            border-left-color: #ef4444;
        }
        
        .toast.warning {
            border-left-color: #f59e0b;
        }
        
        /* Language selector styles */
        .language-selector {
            position: relative;
            display: inline-block;
        }
        
        .language-current {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            color: #374151;
        }
        
        .language-current:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }
        
        .language-flag-icon {
            width: 20px;
            height: 15px;
            object-fit: cover;
            border-radius: 2px;
        }
        
        .language-flag-text {
            font-weight: 500;
            font-size: 12px;
        }
        
        .language-arrow {
            transition: transform 0.2s ease;
        }
        
        .language-selector.open .language-arrow {
            transform: rotate(180deg);
        }
        
        .language-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            min-width: 160px;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
        }
        
        .language-selector.open .language-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .language-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            color: #374151;
            text-decoration: none;
            transition: background-color 0.2s ease;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .language-option:last-child {
            border-bottom: none;
        }
        
        .language-option:hover {
            background: #f8fafc;
        }
        
        .language-option.active {
            background: #eff6ff;
            color: #1d4ed8;
        }
        
        .language-name {
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/components/loading-screen.php'; ?>
    
    <!-- Header -->
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">JS</span>
                    </div>
                    <span class="ml-3 text-lg sm:text-xl font-bold text-gray-900">JustSite</span>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <a href="profile.php" class="text-gray-600 hover:text-gray-900 text-sm sm:text-base">
                        <span class="hidden sm:inline"><?php echo LanguageManager::t('dashboard_back_to_profile'); ?></span>
                        <span class="sm:hidden"><?php echo LanguageManager::t('dashboard_profile'); ?></span>
                    </a>
                    <a href="logout.php" class="text-gray-600 hover:text-gray-900 text-sm sm:text-base"><?php echo LanguageManager::t('dashboard_logout'); ?></a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Project Header -->
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2 truncate">
                        <?php echo htmlspecialchars($project['title'] ?: LanguageManager::t('dashboard_no_title')); ?>
                    </h1>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-2 sm:space-y-0 text-sm text-gray-600">
                        <span><?php echo LanguageManager::t('dashboard_created'); ?> <?php echo date('d.m.Y', strtotime($project['created_at'])); ?></span>
                        <span><?php echo LanguageManager::t('dashboard_updated'); ?> <?php echo date('d.m.Y', strtotime($project['updated_at'])); ?></span>
                        <span class="px-2 py-1 rounded-full text-xs w-fit <?php echo $project['privacy'] === 'public' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                            <?php echo $project['privacy'] === 'public' ? LanguageManager::t('dashboard_published') : LanguageManager::t('dashboard_draft'); ?>
                        </span>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 lg:flex-shrink-0">
                    <a href="index.php?load=<?php echo $project['id']; ?>" 
                       class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-center font-medium">
                        <?php echo LanguageManager::t('dashboard_edit'); ?>
                    </a>
                    <?php if ($project['privacy'] === 'public' && $project['slug']): ?>
                        <a href="view.php?slug=<?php echo htmlspecialchars($project['slug']); ?>" 
                           target="_blank"
                           class="w-full sm:w-auto bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-center font-medium">
                            <?php echo LanguageManager::t('dashboard_view_site'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 stat-card" data-target="<?php echo $totalViews; ?>">
                <div class="text-3xl font-bold text-blue-600 stat-number">0</div>
                <div class="text-gray-600"><?php echo LanguageManager::t('dashboard_total_views'); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 stat-card" data-target="<?php echo $uniqueViews; ?>">
                <div class="text-3xl font-bold text-green-600 stat-number">0</div>
                <div class="text-gray-600"><?php echo LanguageManager::t('dashboard_unique_visitors'); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 stat-card" data-target="<?php echo count($dailyStats); ?>">
                <div class="text-3xl font-bold text-purple-600 stat-number">0</div>
                <div class="text-gray-600"><?php echo LanguageManager::t('dashboard_active_days'); ?></div>
            </div>
        </div>

        <!-- Charts and Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Views Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo LanguageManager::t('dashboard_views_chart'); ?></h3>
                <?php if (!empty($dailyStats)): ?>
                    <div class="chart-container" style="height: 300px; position: relative;">
                        <canvas id="viewsChart" width="400" height="300"></canvas>
                    </div>
                    <script>
                        // Данные для графика
                        const chartData = {
                            labels: [<?php 
                                $reversedStats = array_reverse($dailyStats);
                                echo implode(',', array_map(function($stat) {
                                    return '"' . date('d.m', strtotime($stat['view_date'])) . '"';
                                }, $reversedStats));
                            ?>],
                            values: [<?php 
                                echo implode(',', array_column($reversedStats, 'daily_views'));
                            ?>]
                        };
                    </script>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            📊
                        </div>
                        <p><?php echo LanguageManager::t('dashboard_no_views_data'); ?></p>
                        <p class="text-sm mt-2"><?php echo LanguageManager::t('dashboard_chart_will_appear'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Views -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo LanguageManager::t('dashboard_recent_views'); ?></h3>
                <?php if (!empty($recentViews)): ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($recentViews, 0, 10) as $view): ?>
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars(substr($view['visitor_ip'], 0, -2) . 'xx'); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php echo date('d.m.Y H:i', strtotime($view['viewed_at'])); ?>
                                    </div>
                                </div>
                                <?php if ($view['referer']): ?>
                                    <div class="text-xs text-blue-600">
                                        <?php echo htmlspecialchars(parse_url($view['referer'], PHP_URL_HOST) ?: LanguageManager::t('dashboard_direct_access')); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <?php echo LanguageManager::t('dashboard_no_views'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 bg-white rounded-lg shadow-sm p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo LanguageManager::t('dashboard_project_actions'); ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <button onclick="togglePrivacy()" class="w-full bg-yellow-600 text-white px-4 py-3 rounded-lg hover:bg-yellow-700 transition-colors font-medium text-sm sm:text-base">
                    <?php if ($project['privacy'] === 'public'): ?>
                        <?php echo LanguageManager::t('dashboard_make_private'); ?>
                    <?php else: ?>
                        <?php echo LanguageManager::t('dashboard_publish'); ?>
                    <?php endif; ?>
                </button>
                <button onclick="duplicateProject()" class="w-full bg-purple-600 text-white px-4 py-3 rounded-lg hover:bg-purple-700 transition-colors font-medium text-sm sm:text-base">
                    <?php echo LanguageManager::t('dashboard_duplicate'); ?>
                </button>
                <button onclick="deleteProject()" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 transition-colors font-medium text-sm sm:text-base sm:col-span-2 lg:col-span-1">
                    <?php echo LanguageManager::t('dashboard_delete'); ?>
                </button>
            </div>
        </div>
    </main>

    <script>
        // Language selector functionality
        document.addEventListener('DOMContentLoaded', function() {
            const languageToggle = document.getElementById('languageToggle');
            const languageDropdown = document.getElementById('languageDropdown');
            
            if (languageToggle && languageDropdown) {
                languageToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const selector = this.closest('.language-selector');
                    selector.classList.toggle('open');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.language-selector')) {
                        const openSelector = document.querySelector('.language-selector.open');
                        if (openSelector) {
                            openSelector.classList.remove('open');
                        }
                    }
                });
            }
        });
        
        // Анимация счетчиков
        document.addEventListener('DOMContentLoaded', function() {
            // Анимация чисел в статистических карточках
            const statCards = document.querySelectorAll('.stat-card');
            
            statCards.forEach((card, index) => {
                const target = parseInt(card.dataset.target) || 0;
                const numberElement = card.querySelector('.stat-number');
                
                // Задержка для каскадной анимации
                setTimeout(() => {
                    animateNumber(numberElement, 0, target, 1500);
                    
                    // Анимация появления карточки
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.6s ease-out';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 200);
            });
        });
        
        function animateNumber(element, start, end, duration) {
            const startTime = Date.now();
            
            function update() {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Easing функция для более плавной анимации
                const easeProgress = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(start + (end - start) * easeProgress);
                
                element.textContent = current.toLocaleString();
                
                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            
            update();
        }

        // Анимированный график
        <?php if (!empty($dailyStats)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('viewsChart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            const data = chartData;
            
            // Настройки графика
            const padding = 40;
            const chartWidth = canvas.width - padding * 2;
            const chartHeight = canvas.height - padding * 2;
            const maxValue = Math.max(...data.values) || 1;
            
            // Цвета
            const gradientFill = ctx.createLinearGradient(0, padding, 0, canvas.height - padding);
            gradientFill.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
            gradientFill.addColorStop(1, 'rgba(59, 130, 246, 0.05)');
            
            const gradientStroke = ctx.createLinearGradient(0, 0, chartWidth, 0);
            gradientStroke.addColorStop(0, '#3b82f6');
            gradientStroke.addColorStop(1, '#8b5cf6');
            
            // Функция для анимации
            let animationProgress = 0;
            const animationDuration = 2000; // 2 секунды
            const startTime = Date.now();
            
            function animate() {
                const elapsed = Date.now() - startTime;
                animationProgress = Math.min(elapsed / animationDuration, 1);
                
                // Easing функция
                const easeProgress = 1 - Math.pow(1 - animationProgress, 3);
                
                // Очистка canvas
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // Рисование сетки
                ctx.strokeStyle = '#f3f4f6';
                ctx.lineWidth = 1;
                
                // Горизонтальные линии сетки
                for (let i = 0; i <= 5; i++) {
                    const y = padding + (chartHeight / 5) * i;
                    ctx.beginPath();
                    ctx.moveTo(padding, y);
                    ctx.lineTo(canvas.width - padding, y);
                    ctx.stroke();
                    
                    // Подписи по Y
                    const value = Math.round(maxValue * (5 - i) / 5);
                    ctx.fillStyle = '#9ca3af';
                    ctx.font = '12px sans-serif';
                    ctx.textAlign = 'right';
                    ctx.fillText(value.toString(), padding - 10, y + 4);
                }
                
                // Рисование данных
                if (data.values.length > 0) {
                    const stepX = chartWidth / (data.values.length - 1 || 1);
                    
                    // Создание пути для линии
                    ctx.beginPath();
                    
                    const points = [];
                    for (let i = 0; i < data.values.length; i++) {
                        const x = padding + i * stepX;
                        const y = padding + chartHeight - (data.values[i] / maxValue) * chartHeight * easeProgress;
                        points.push({x, y, value: data.values[i]});
                        
                        if (i === 0) {
                            ctx.moveTo(x, y);
                        } else {
                            ctx.lineTo(x, y);
                        }
                    }
                    
                    // Рисование линии
                    ctx.strokeStyle = gradientStroke;
                    ctx.lineWidth = 3;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                    ctx.stroke();
                    
                    // Заливка под линией
                    ctx.lineTo(canvas.width - padding, canvas.height - padding);
                    ctx.lineTo(padding, canvas.height - padding);
                    ctx.closePath();
                    ctx.fillStyle = gradientFill;
                    ctx.fill();
                    
                    // Рисование точек
                    points.forEach((point, index) => {
                        // Анимация появления точек
                        const pointProgress = Math.max(0, Math.min(1, (easeProgress * data.values.length - index) / 1));
                        
                        if (pointProgress > 0) {
                            // Внешний круг
                            ctx.beginPath();
                            ctx.arc(point.x, point.y, 6 * pointProgress, 0, Math.PI * 2);
                            ctx.fillStyle = '#ffffff';
                            ctx.fill();
                            ctx.strokeStyle = '#3b82f6';
                            ctx.lineWidth = 2;
                            ctx.stroke();
                            
                            // Внутренний круг
                            ctx.beginPath();
                            ctx.arc(point.x, point.y, 3 * pointProgress, 0, Math.PI * 2);
                            ctx.fillStyle = '#3b82f6';
                            ctx.fill();
                        }
                    });
                    
                    // Подписи по X
                    ctx.fillStyle = '#9ca3af';
                    ctx.font = '12px sans-serif';
                    ctx.textAlign = 'center';
                    for (let i = 0; i < data.labels.length; i++) {
                        const x = padding + i * stepX;
                        ctx.fillText(data.labels[i], x, canvas.height - 10);
                    }
                }
                
                // Продолжение анимации
                if (animationProgress < 1) {
                    requestAnimationFrame(animate);
                }
            }
            
            // Запуск анимации
            animate();
            
            // Интерактивность - показ значений при наведении
            canvas.addEventListener('mousemove', function(e) {
                if (animationProgress < 1) return;
                
                const rect = canvas.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;
                
                // Проверка попадания на точки
                const stepX = chartWidth / (data.values.length - 1 || 1);
                let hoveredPoint = -1;
                
                for (let i = 0; i < data.values.length; i++) {
                    const x = padding + i * stepX;
                    const y = padding + chartHeight - (data.values[i] / maxValue) * chartHeight;
                    
                    const distance = Math.sqrt((mouseX - x) ** 2 + (mouseY - y) ** 2);
                    if (distance < 15) {
                        hoveredPoint = i;
                        break;
                    }
                }
                
                // Изменение курсора
                canvas.style.cursor = hoveredPoint >= 0 ? 'pointer' : 'default';
                
                // Показ тултипа (можно добавить позже)
                if (hoveredPoint >= 0) {
                    canvas.title = `${data.labels[hoveredPoint]}: ${data.values[hoveredPoint]} просмотров`;
                } else {
                    canvas.title = '';
                }
            });
        });
        <?php endif; ?>

        // Функции для красивых алертов
        function showAlert(title, message, type = 'confirm', onConfirm = null) {
            const overlay = document.createElement('div');
            overlay.className = 'alert-overlay';
            
            const iconColors = {
                confirm: 'text-blue-600',
                warning: 'text-yellow-600',
                error: 'text-red-600',
                success: 'text-green-600'
            };
            
            const icons = {
                confirm: '❓',
                warning: '⚠️',
                error: '❌',
                success: '✅'
            };
            
            overlay.innerHTML = `
                <div class="alert-box">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center text-2xl">
                            ${icons[type]}
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">${title}</h3>
                        <p class="text-gray-600">${message}</p>
                    </div>
                    <div class="flex space-x-3">
                        ${type === 'confirm' ? `
                            <button onclick="closeAlert(this)" class="flex-1 bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">
                                <?php echo LanguageManager::t('dashboard_cancel'); ?>
                            </button>
                            <button onclick="confirmAlert(this)" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                                <?php echo LanguageManager::t('dashboard_confirm'); ?>
                            </button>
                        ` : `
                            <button onclick="closeAlert(this)" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                                <?php echo LanguageManager::t('dashboard_ok'); ?>
                            </button>
                        `}
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            
            // Анимация появления
            setTimeout(() => overlay.classList.add('show'), 10);
            
            // Сохраняем callback
            overlay._onConfirm = onConfirm;
            
            return overlay;
        }
        
        function closeAlert(button) {
            const overlay = button.closest('.alert-overlay');
            overlay.classList.remove('show');
            setTimeout(() => overlay.remove(), 300);
        }
        
        function confirmAlert(button) {
            const overlay = button.closest('.alert-overlay');
            if (overlay._onConfirm) {
                overlay._onConfirm();
            }
            closeAlert(button);
        }
        
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            
            toast.innerHTML = `
                <div class="flex items-center">
                    <span class="mr-3 text-lg">${icons[type]}</span>
                    <span class="text-gray-800">${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Анимация появления
            setTimeout(() => toast.classList.add('show'), 10);
            
            // Автоматическое скрытие
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 3000);
        }
        
        // API функции
        async function makeApiRequest(action, projectId) {
            try {
                const response = await fetch('api/project_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: action,
                        project_id: projectId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    return result;
                } else {
                    throw new Error(result.error || 'Неизвестная ошибка');
                }
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        }
        
        function togglePrivacy() {
            const currentPrivacy = '<?php echo $project['privacy']; ?>';
            const newStatus = currentPrivacy === 'public' ? '<?php echo LanguageManager::t('dashboard_make_private_confirm'); ?>' : '<?php echo LanguageManager::t('dashboard_make_public_confirm'); ?>';
            
            showAlert(
                '<?php echo LanguageManager::t('dashboard_change_status'); ?>',
                newStatus,
                'confirm',
                async () => {
                    try {
                        const result = await makeApiRequest('toggle_privacy', <?php echo $project['id']; ?>);
                        showToast(result.message, 'success');
                        
                        // Обновляем страницу через 1 секунду
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } catch (error) {
                        showToast('Ошибка: ' + error.message, 'error');
                    }
                }
            );
        }

        function duplicateProject() {
            showAlert(
                '<?php echo LanguageManager::t('dashboard_duplicate_confirm'); ?>',
                '<?php echo LanguageManager::t('dashboard_duplicate_question'); ?>',
                'confirm',
                async () => {
                    try {
                        const result = await makeApiRequest('duplicate', <?php echo $project['id']; ?>);
                        showToast(result.message, 'success');
                        
                        // Перенаправляем на новый проект через 1 секунду
                        setTimeout(() => {
                            window.location.href = 'dashboard.php?project=' + result.new_project_id;
                        }, 1000);
                    } catch (error) {
                        showToast('Ошибка: ' + error.message, 'error');
                    }
                }
            );
        }

        function deleteProject() {
            showAlert(
                '<?php echo LanguageManager::t('dashboard_delete_confirm'); ?>',
                '<?php echo LanguageManager::t('dashboard_delete_warning'); ?>',
                'warning',
                async () => {
                    try {
                        const result = await makeApiRequest('delete', <?php echo $project['id']; ?>);
                        showToast(result.message, 'success');
                        
                        // Перенаправляем в профиль через 1 секунду
                        setTimeout(() => {
                            window.location.href = 'profile.php';
                        }, 1000);
                    } catch (error) {
                        showToast('Ошибка: ' + error.message, 'error');
                    }
                }
            );
        }
    </script>
</body>
</html>
