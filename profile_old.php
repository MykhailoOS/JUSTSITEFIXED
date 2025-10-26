<?php
require_once __DIR__ . '/lib/auth.php';
require_auth();

$pdo = DatabaseConnectionProvider::getConnection();
$uid = current_user_id();
$stmt = $pdo->prepare('SELECT id, email, name, created_at FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch() ?: ['email' => '', 'name' => '', 'created_at' => ''];

// Extract user data
$email = $user['email'] ?? '';
$displayName = $user['name'] ?: $email;
$createdAt = $user['created_at'] ?? date('Y-m-d H:i:s');

// Calculate stats
$totalProjects = 0;
$publishedProjects = 0;
$totalViews = 0;

// Projects list
$projStmt = $pdo->prepare('SELECT id, title, slug, privacy, view_count, updated_at FROM projects WHERE user_id = ? ORDER BY updated_at DESC LIMIT 50');
$projStmt->execute([$uid]);
$projects = $projStmt->fetchAll();

// Calculate stats from projects
foreach ($projects as $project) {
    $totalProjects++;
    if ($project['privacy'] === 'public') {
        $publishedProjects++;
    }
    $totalViews += (int)$project['view_count'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль — JustSite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/main.css?v=<?php echo time(); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .dashboard {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 100%);
            color: white;
            padding: 2rem;
            position: relative;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .user-profile {
            position: relative;
            z-index: 1;
            text-align: center;
            margin-bottom: 2rem;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(45deg, #60a5fa, #a78bfa);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            margin: 0 auto 1rem;
            color: white;
        }

        .user-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .user-role {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .nav-menu {
            position: relative;
            z-index: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .logout-btn {
            position: absolute;
            bottom: 2rem;
            left: 2rem;
            right: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.75rem;
            border-radius: 12px;
            text-decoration: none;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .main-content {
            padding: 2rem;
            overflow-y: auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .dashboard-subtitle {
            font-size: 0.9rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .dashboard-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .notification-icon, .settings-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6b7280;
        }

        .notification-icon:hover, .settings-icon:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .projects-section {
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .filters {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6b7280;
        }

        .filter-btn.active {
            background: #1e3a8a;
            color: white;
            border-color: #1e3a8a;
        }

        .filter-btn:hover {
            border-color: #9ca3af;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .project-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .project-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .project-preview {
            width: 100%;
            height: 160px;
            background: #f3f4f6;
            border-radius: 12px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 0.875rem;
            position: relative;
            overflow: hidden;
        }

        .project-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            opacity: 0.1;
        }

        .project-preview::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .project-info {
            margin-bottom: 1rem;
        }

        .project-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 0.5rem 0;
        }

        .project-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .project-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            flex: 1;
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            color: #374151;
        }

        .action-btn.primary {
            background: #1e3a8a;
            color: white;
            border-color: #1e3a8a;
        }

        .action-btn:hover {
            background: #f9fafb;
        }

        .action-btn.primary:hover {
            background: #1e40af;
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-badge.published {
            background: #dcfce7;
            color: #166534;
        }

        .status-badge.draft {
            background: #fef3c7;
            color: #92400e;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

            .main-content {
                padding: 1rem;
            }

            .projects-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .filters {
                flex-wrap: wrap;
            }
        }
        }
        
        .profile-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            position: relative;
            z-index: 2;
        }
        
        .profile-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            color: white;
        }
        
        .profile-logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .profile-actions {
            display: flex;
            gap: 12px;
        }
        
        .profile-btn {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .profile-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }
        
        .profile-btn.primary {
            background: white;
            color: #667eea;
        }
        
        .profile-btn.primary:hover {
            background: #f8fafc;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: -40px auto 0;
            padding: 0 24px 40px;
            position: relative;
            z-index: 3;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 32px;
            align-items: start;
        }
        
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            font-weight: 700;
            margin-bottom: 24px;
        }
        
        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: #1a202c;
            margin: 0 0 8px 0;
            letter-spacing: -0.02em;
        }
        
        .profile-email {
            color: #718096;
            font-size: 16px;
            margin-bottom: 16px;
        }
        
        .profile-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 24px;
            padding: 16px;
            background: #f7fafc;
            border-radius: 12px;
        }
        
        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #4a5568;
        }
        
        .profile-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .profile-button {
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        
        .profile-button.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .profile-button.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        
        .profile-button.secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
        
        .profile-button.secondary:hover {
            background: #edf2f7;
        }
        
        .projects-section {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
        }
        
        .section-subtitle {
            color: #718096;
            font-size: 14px;
            margin: 4px 0 0 0;
        }
        
        .projects-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .project-card {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            transition: all 0.2s ease;
        }
        
        .project-card:hover {
            background: white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        
        .project-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a202c;
            margin: 0 0 4px 0;
        }
        
        .project-meta {
            font-size: 12px;
            color: #718096;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .project-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .project-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
            background: white;
            color: #4a5568;
        }
        
        .project-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .project-btn.primary {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }
        
        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .dashboard {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        
        .sidebar {
            width: 250px;
            background: #2f343a;
            padding: 20px;
            color: white;
            display: flex;
            flex-direction: column;
        }
        
        .user-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            font-weight: 700;
            margin-bottom: 12px;
        }
        
        .user-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .user-role {
            font-size: 14px;
            color: #718096;
        }
        
        .nav-menu {
            margin-bottom: 20px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .nav-item.active {
            background: #4a5568;
            color: white;
        }
        
        .nav-icon {
            font-size: 18px;
            margin-right: 12px;
        }
        
        .logout-btn {
            background: #667eea;
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .dashboard-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
        }
        
        .dashboard-subtitle {
            color: #718096;
            font-size: 14px;
            margin: 4px 0 0 0;
        }
        
        .dashboard-actions {
            display: flex;
            gap: 12px;
        }
        
        .notification-icon {
            font-size: 18px;
            cursor: pointer;
        }
        
        .settings-icon {
            font-size: 18px;
            cursor: pointer;
        }
        
        .projects-section {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
        }
        
        .section-subtitle {
            color: #718096;
            font-size: 14px;
            margin: 4px 0 0 0;
        }
        
        .filters {
            display: flex;
            gap: 12px;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        
        .filter-btn.active {
            background: #667eea;
            color: white;
        }
        
        .project-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a202c;
            margin: 0 0 4px 0;
        }
        
        .project-meta {
            font-size: 12px;
            color: #718096;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .project-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .project-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
            background: white;
            color: #4a5568;
        }
        
        .project-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .project-btn.primary {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }
        
        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        /* Улучшенные стили для превью проектов */
        .project-preview-enhanced {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            border: 1px solid #dee2e6 !important;
            position: relative !important;
            overflow: hidden !important;
        }
        
        .project-preview-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            z-index: 1;
        }
        
        .project-preview-enhanced .project-icon {
            background: rgba(255, 255, 255, 0.9) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            z-index: 2 !important;
            position: relative !important;
        }
        
        .project-preview-enhanced::after {
            content: '';
            position: absolute;
            bottom: 8px;
            left: 8px;
            right: 8px;
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 1px;
            z-index: 2;
        }

        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .profile-card,
            .projects-section {
                padding: 24px;
            }
            
            .profile-nav {
                padding: 0 16px;
            }
            
            .profile-container {
                padding: 0 16px 40px;
            }
            
            .profile-actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .project-actions {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-50 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-gray-900">JustSite</span>
            </div>
            <button id="mobileMenuBtn" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Sidebar -->
        <div id="sidebar" class="hidden lg:flex lg:w-64 bg-white border-r border-gray-200 flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">JustSite</h1>
                        <p class="text-xs text-gray-500">Website Builder</p>
                    </div>
                </div>
            </div>

            <!-- User Profile -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-sm">
                            <?php echo strtoupper(substr($displayName, 0, 2)); ?>
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($displayName); ?></p>
                        <p class="text-xs text-gray-500">Author • Sep 2025</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="profile.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19Z"/>
                            </svg>
                            Проекты
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
        
        <!-- Mobile Menu Dropdown -->
        <div id="mobileMenu" class="lg:hidden fixed top-16 left-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 z-50 hidden transform transition-all duration-200 opacity-0 scale-95">
            <div class="p-4">
                <nav class="space-y-2">
                    <a href="profile.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19Z"/>
                        </svg>
                        Проекты
                    </a>
                    <a href="settings.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"/>
                        </svg>
                        Настройки
                    </a>
                </nav>
                <div class="border-t border-gray-200 mt-4 pt-4">
                    <button id="mobileLogoutBtn" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z"/>
                        </svg>
                        Logout
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden lg:ml-0">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Профиль</h1>
                        <p class="text-sm text-gray-500">Добро пожаловать, <?php echo htmlspecialchars($displayName); ?></p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <input 
                                type="text" 
                                id="searchProjects" 
                                placeholder="Поиск проектов..." 
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm w-64"
                            >
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-auto bg-gray-50 p-4 md:p-6">
                <!-- Simple Stats -->
                <div class="grid grid-cols-3 gap-6 mb-8">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900"><?php echo $totalProjects; ?></div>
                        <div class="text-sm text-gray-500 mt-1">Projects</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900"><?php echo $publishedProjects; ?></div>
                        <div class="text-sm text-gray-500 mt-1">Published</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900"><?php echo $totalViews; ?></div>
                        <div class="text-sm text-gray-500 mt-1">Views</div>
                    </div>
                </div>

                <!-- Quick Action -->
                <div class="mb-8 text-center">
                    <a href="index.php" class="inline-flex items-center gap-3 bg-indigo-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Create New Project
                    </a>
                </div>

                <!-- Projects Section -->
                <div>
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Your Projects</h2>
                        <div class="flex gap-1 bg-gray-100 rounded-lg p-1">
                            <button onclick="filterProjects('all')" class="px-3 py-1 text-sm font-medium rounded-md bg-white text-gray-900 shadow-sm">All</button>
                            <button onclick="filterProjects('published')" class="px-3 py-1 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900">Published</button>
                            <button onclick="filterProjects('draft')" class="px-3 py-1 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900">Drafts</button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="projectsGrid">
                        <!-- Projects will be loaded here via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Применяем улучшенные стили к существующим превью проектов
            setTimeout(function() {
                $('.h-32.w-full.rounded-md.bg-gradient-to-br').each(function() {
                    $(this).removeClass('bg-gradient-to-br from-indigo-50 to-blue-50')
                           .addClass('project-preview-enhanced');
                    
                    // Обновляем иконку
                    const $icon = $(this).find('.w-12.h-12');
                    $icon.addClass('project-icon');
                });
            }, 100);
            
            // Toggle project menu
            window.toggleProjectMenu = function(projectId) {
                const menu = document.getElementById(`menu-${projectId}`);
                const allMenus = document.querySelectorAll('[id^="menu-"]');
                
                // Close all other menus
                allMenus.forEach(m => {
                    if (m !== menu) {
                        m.classList.add('hidden');
                    }
                });
                
                // Toggle current menu
                menu.classList.toggle('hidden');
            };

            // Duplicate project function
            window.duplicateProject = function(projectId) {
                if (confirm('Are you sure you want to duplicate this project?')) {
                    showNotification('Project duplication will be available soon', 'info');
                }
            };

            // Close menus when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('[id^="menu-"]') && !e.target.closest('button[onclick*="toggleProjectMenu"]')) {
                    document.querySelectorAll('[id^="menu-"]').forEach(menu => {
                        menu.classList.add('hidden');
                    });
                }
            });

            // Search functionality
            let allProjects = [];
            let currentFilter = 'all';
            
            window.searchProjects = function(query) {
                const filteredProjects = allProjects.filter(project => {
                    const matchesSearch = !query || 
                        (project.title && project.title.toLowerCase().includes(query.toLowerCase())) ||
                        (project.slug && project.slug.toLowerCase().includes(query.toLowerCase()));
                    
                    const matchesFilter = currentFilter === 'all' || 
                        (currentFilter === 'published' && project.privacy === 'public') ||
                        (currentFilter === 'draft' && project.privacy !== 'public');
                    
                    return matchesSearch && matchesFilter;
                });
                
                displayProjects(filteredProjects);
            };
            
            window.filterProjects = function(filter) {
                currentFilter = filter;
                
                // Update filter buttons
                document.querySelectorAll('[onclick*="filterProjects"]').forEach(btn => {
                    btn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                    btn.classList.add('text-gray-600', 'hover:text-gray-900');
                });
                
                const activeBtn = document.querySelector(`[onclick="filterProjects('${filter}')"]`);
                if (activeBtn) {
                    activeBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                    activeBtn.classList.remove('text-gray-600', 'hover:text-gray-900');
                }
                
                const searchQuery = document.getElementById('searchProjects').value;
                searchProjects(searchQuery);
            };
            
            function displayProjects(projects) {
                const projectsGrid = document.getElementById('projectsGrid');
                
                if (projects && projects.length > 0) {
                    projectsGrid.innerHTML = projects.map(project => `
                        <div class="relative group">
                            <a href="index.php?load=${project.id}" class="block rounded-lg p-4 shadow-xs shadow-indigo-100 hover:shadow-md transition-shadow bg-white">
                                <!-- Project Menu -->
                                <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <div class="relative">
                                        <button onclick="event.preventDefault(); toggleProjectMenu(${project.id})" class="w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-gray-50 transition-colors">
                                            <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                            </svg>
                                        </button>
                                        <div id="menu-${project.id}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                                            <a href="dashboard.php?project=${project.id}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                                                </svg>
                                                Dashboard
                                            </a>
                                            <a href="index.php?load=${project.id}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit Project
                                            </a>
                                            ${project.has_deploy ? `
                                                <a href="view.php?slug=${project.slug}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                    View Live
                                                </a>
                                            ` : ''}
                                            <button onclick="event.preventDefault(); duplicateProject(${project.id})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                Duplicate
                                            </button>
                                            <hr class="my-1">
                                            <button onclick="event.preventDefault(); deleteProject(${project.id})" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="h-32 w-full rounded-md project-preview-enhanced flex items-center justify-center mb-3">
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center project-icon">
                                        <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                        </svg>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <div class="mb-2">
                                        <span class="text-xs px-2 py-1 rounded-full ${project.privacy === 'public' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}">
                                            ${project.privacy === 'public' ? 'Published' : 'Draft'}
                                        </span>
                                    </div>

                                    <div class="mb-3">
                                        <h3 class="font-medium text-gray-900 truncate">${project.title || 'Untitled Project'}</h3>
                                        <p class="text-sm text-gray-500 mt-1">Updated ${project.formatted_date}</p>
                                    </div>

                                    <div class="flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span class="text-gray-600">${project.view_count || 0}</span>
                                        </div>
                                        <div class="text-gray-500">${project.privacy === 'public' ? 'Live' : 'Draft'}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    `).join('');
                } else {
                    projectsGrid.innerHTML = `
                        <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M10,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V8C22,6.89 21.1,6 20,6H12L10,4Z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">No projects found</h3>
                            <p class="text-gray-500 mb-4">Try adjusting your search or filter criteria</p>
                            <a href="index.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Create New Project
                            </a>
                        </div>
                    `;
                }
            }
            
            // Search input event listener
            document.getElementById('searchProjects').addEventListener('input', function(e) {
                searchProjects(e.target.value);
            });

            // Load projects on page load
            loadProjects();
            
            // Logout button handlers for all logout buttons
            $('#logoutBtn, #logoutBtnMain, a[href="logout.php"], a[href*="logout.php"]').on('click', function(e) {
                e.preventDefault();
                
                // Show logout confirmation
                const confirmExit = confirm('Вы уверены, что хотите выйти из системы?');
                if (confirmExit) {
                    // Show loading notification
                    showNotification('Выход из системы...', 'info');
                    
                    // Perform AJAX logout
                    performLogout();
                }
                
                return false;
            });
            
            // AJAX logout function
            async function performLogout() {
                try {
                    const response = await fetch('api/logout.php', {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.ok) {
                        // Show success message
                        showNotification('Вы успешно вышли из системы', 'success');
                        
                        // Redirect after a short delay
                        setTimeout(() => {
                            window.location.href = data.redirect_url || 'login.php?logout=success';
                        }, 1000);
                    } else {
                        // Fallback to regular logout
                        window.location.href = 'logout.php';
                    }
                } catch (error) {
                    console.error('Logout error:', error);
                    // Fallback to regular logout
                    window.location.href = 'logout.php';
                }
            }
            
            // Simple notification function
            function showNotification(message, type = 'info') {
                // Remove existing notifications
                $('.notification').remove();
                
                const notification = $(`
                    <div class="notification notification-${type}" style="
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#F44336' : '#2196F3'};
                        color: white;
                        padding: 12px 20px;
                        border-radius: 8px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                        z-index: 10001;
                        font-size: 14px;
                        font-weight: 500;
                        max-width: 300px;
                        word-wrap: break-word;
                    ">
                        ${message}
                    </div>
                `);
                
                $('body').append(notification);
                
                // Auto remove after 4 seconds
                setTimeout(() => {
                    notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 4000);
            }
            
            // Load projects function
            async function loadProjects() {
                try {
                    const response = await fetch('api/projects_list.php', {
                        method: 'GET',
                        credentials: 'include'
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to load projects');
                    }
                    
                    const data = await response.json();
                    
                    if (data.ok && data.projects && data.projects.length > 0) {
                        allProjects = data.projects;
                        displayProjects(allProjects);
                    } else {
                        projectsGrid.innerHTML = `
                            <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M10,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V8C22,6.89 21.1,6 20,6H12L10,4Z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">No projects yet</h3>
                                <p class="text-gray-600 mb-6">Create your first website with our builder</p>
                                <a href="index.php" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                                    Create Project
                                </a>
                            </div>
                        `;
                    }
                } catch (error) {
                    console.error('Error loading projects:', error);
                    document.getElementById('projectsGrid').innerHTML = `
                        <div class="empty-state">
                            <div class="empty-icon">❌</div>
                            <h3>Ошибка загрузки проектов</h3>
                            <p>${error.message}</p>
                            <button onclick="loadProjects()" class="action-btn primary" style="margin-top: 16px;">Повторить</button>
                        </div>
                    `;
                }
            }
            
            // Delete project function
            window.deleteProject = function(projectId) {
                if (confirm('Вы уверены, что хотите удалить этот проект? Это действие нельзя отменить.')) {
                    // Here you would implement the delete functionality
                    alert('Функция удаления будет добавлена позже');
                }
            }
            
            // Filter projects function
            window.filterProjects = function(filter) {
                // Update button states
                document.querySelectorAll('[onclick^="filterProjects"]').forEach(btn => {
                    btn.className = btn.className.replace('bg-blue-100 text-blue-700', 'bg-gray-100 text-gray-700 hover:bg-gray-200');
                });
                
                // Set active button
                event.target.className = event.target.className.replace('bg-gray-100 text-gray-700 hover:bg-gray-200', 'bg-blue-100 text-blue-700');
                
                // Filter projects based on type
                const projectCards = document.querySelectorAll('.project-card');
                projectCards.forEach(card => {
                    const privacyBadge = card.querySelector('.privacy-badge');
                    const isPublic = privacyBadge && privacyBadge.textContent.includes('Published');
                    
                    let shouldShow = false;
                    switch(filter) {
                        case 'all':
                            shouldShow = true;
                            break;
                        case 'published':
                            shouldShow = isPublic;
                            break;
                        case 'drafts':
                            shouldShow = !isPublic;
                            break;
                    }
                    
                    card.style.display = shouldShow ? 'block' : 'none';
                });
                
                console.log('Filtered projects by:', filter);
            }
        });

        // Logout functionality
        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Вы уверены, что хотите выйти из системы?')) {
                window.location.href = 'logout.php';
            }
        });

        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileLogoutBtn = document.getElementById('mobileLogoutBtn');

        function toggleMobileMenu() {
            if (mobileMenu.classList.contains('hidden')) {
                // Show menu
                mobileMenu.classList.remove('hidden');
                setTimeout(() => {
                    mobileMenu.classList.remove('opacity-0', 'scale-95');
                    mobileMenu.classList.add('opacity-100', 'scale-100');
                }, 10);
            } else {
                // Hide menu
                mobileMenu.classList.remove('opacity-100', 'scale-100');
                mobileMenu.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    mobileMenu.classList.add('hidden');
                }, 200);
            }
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mobileMenuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });

        // Mobile logout functionality
        if (mobileLogoutBtn) {
            mobileLogoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Вы уверены, что хотите выйти из системы?')) {
                    window.location.href = 'logout.php';
                }
            });
        }
    </script>
</body>
</html>


