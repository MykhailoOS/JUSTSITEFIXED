<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/language.php';
require_auth();

// Initialize language system
LanguageManager::init();

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
<html lang="<?php echo LanguageManager::getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo LanguageManager::t('nav_profile'); ?> — JustSite</title>
    <link rel="stylesheet" href="styles/main.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Hide header during loading */
        body.loading header {
            opacity: 0;
            visibility: hidden;
        }
        
        /* Ensure loading screen is on top */
        #loading-screen {
            z-index: 99999 !important;
        }
        
        /* Language selector z-index fix */
        .language-selector {
            position: relative;
            z-index: 1;
        }
        
        .language-dropdown {
            z-index: 1000;
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
                    <span class="ml-3 text-xl font-bold text-gray-900">JustSite</span>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Language Selector -->
                    <div class="language-selector">
                        <button class="language-btn" id="languageBtn" title="<?php echo LanguageManager::t('header_language'); ?>">
                            <?php 
                            $currentLang = LanguageManager::getCurrentLanguage();
                            $langs = LanguageManager::getAvailableLanguages();
                            $currentLangData = $langs[$currentLang];
                            ?>
                            <?php if ($currentLangData['flag'] === 'text'): ?>
                                <span class="language-flag-text"><?php echo $currentLangData['name']; ?></span>
                            <?php else: ?>
                                <img src="<?php echo $currentLangData['flag']; ?>" alt="<?php echo $currentLangData['name']; ?>" class="language-flag">
                            <?php endif; ?>
                            <span class="language-arrow">▼</span>
                        </button>
                        <div class="language-dropdown" id="languageDropdown">
                            <?php foreach ($langs as $code => $lang): ?>
                                <a href="#" class="language-option <?php echo $code === $currentLang ? 'active' : ''; ?>" data-lang="<?php echo $code; ?>">
                                    <?php if ($lang['flag'] === 'text'): ?>
                                        <span class="language-flag-text"><?php echo $lang['name']; ?></span>
                                    <?php else: ?>
                                        <img src="<?php echo $lang['flag']; ?>" alt="<?php echo $lang['name']; ?>" class="language-flag">
                                    <?php endif; ?>
                                    <span class="language-name"><?php echo $lang['name']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <a href="index.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <?php echo LanguageManager::t('btn_create_project'); ?>
                    </a>
                    <a href="community_projects.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
                        <span class="material-icons text-sm">public</span>
                        <span>Community</span>
                    </a>
                    <?php 
                    // Show admin link for administrators
                    $isAdmin = strpos($user['email'], 'admin') !== false || $user['email'] === 'admin@justsite.com' || $uid == 1;
                    if ($isAdmin): 
                    ?>
                        <a href="admin.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            👑 <?php echo LanguageManager::t('admin_panel'); ?>
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="text-gray-600 hover:text-gray-900"><?php echo LanguageManager::t('dashboard_logout'); ?></a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- User Info -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-bold text-xl">
                        <?php echo strtoupper(substr($displayName, 0, 2)); ?>
                    </span>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($displayName); ?></h1>
                    <p class="text-gray-600"><?php echo htmlspecialchars($email); ?></p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-3xl font-bold text-gray-900"><?php echo $totalProjects; ?></div>
                <div class="text-gray-600"><?php echo LanguageManager::t('profile_total_projects'); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-3xl font-bold text-green-600"><?php echo $publishedProjects; ?></div>
                <div class="text-gray-600"><?php echo LanguageManager::t('profile_published'); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-3xl font-bold text-blue-600"><?php echo $totalViews; ?></div>
                <div class="text-gray-600"><?php echo LanguageManager::t('profile_views'); ?></div>
            </div>
        </div>

        <!-- Projects -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900"><?php echo LanguageManager::t('profile_recent_projects'); ?></h2>
            </div>
            <div class="p-6">
                <?php if (empty($projects)): ?>
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2"><?php echo LanguageManager::t('profile_no_projects'); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo LanguageManager::t('profile_create_first'); ?></p>
                        <a href="index.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <?php echo LanguageManager::t('btn_create_project'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($projects as $project): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <!-- Project Preview -->
                                <div class="h-32 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                    </svg>
                                </div>
                                
                                <!-- Project Info -->
                                <div class="mb-4">
                                    <h3 class="font-medium text-gray-900 mb-1">
                                        <?php echo htmlspecialchars($project['title'] ?: LanguageManager::t('dashboard_no_title')); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        <?php echo LanguageManager::t('dashboard_updated'); ?> <?php echo date('d.m.Y', strtotime($project['updated_at'])); ?>
                                    </p>
                                    <div class="flex items-center mt-2">
                                        <span class="text-xs px-2 py-1 rounded-full <?php echo $project['privacy'] === 'public' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                            <?php echo $project['privacy'] === 'public' ? LanguageManager::t('dashboard_published') : LanguageManager::t('dashboard_draft'); ?>
                                        </span>
                                        <span class="text-xs text-gray-500 ml-2">
                                            👁 <?php echo $project['view_count'] ?: 0; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex space-x-2">
                                    <a href="index.php?load=<?php echo $project['id']; ?>" 
                                       class="flex-1 bg-blue-600 text-white text-center py-2 px-3 rounded text-sm hover:bg-blue-700 transition-colors">
                                        <?php echo LanguageManager::t('dashboard_edit'); ?>
                                    </a>
                                    <a href="dashboard.php?project=<?php echo $project['id']; ?>" 
                                       class="flex-1 bg-gray-100 text-gray-700 text-center py-2 px-3 rounded text-sm hover:bg-gray-200 transition-colors">
                                        <?php echo LanguageManager::t('dashboard_title'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Language Selector Functionality
        $(document).ready(function() {
            const languageBtn = $('#languageBtn');
            const languageDropdown = $('#languageDropdown');
            
            // Toggle dropdown
            languageBtn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                languageDropdown.toggleClass('show');
                languageBtn.toggleClass('active');
            });
            
            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.language-selector').length) {
                    languageDropdown.removeClass('show');
                    languageBtn.removeClass('active');
                }
            });
            
            // Handle language selection
            $('.language-option').on('click', function(e) {
                e.preventDefault();
                
                const selectedLang = $(this).data('lang');
                console.log('Selected language:', selectedLang);
                
                if (selectedLang) {
                    // Show loading state
                    languageBtn.html('<span class="language-flag-text">...</span>');
                    
                    // Send request to change language
                    $.ajax({
                        url: 'api/change_language_simple.php',
                        method: 'POST',
                        data: { language: selectedLang },
                        dataType: 'json',
                        beforeSend: function() {
                            console.log('Sending language change request...');
                        },
                        success: function(response) {
                            console.log('Language change response:', response);
                            
                            // Check if response is valid
                            if (typeof response !== 'object' || response === null) {
                                console.error('Invalid response format:', response);
                                alert('<?php echo LanguageManager::t('error_network'); ?>: Invalid server response');
                                location.reload();
                                return;
                            }
                            
                            if (response.success) {
                                console.log('Language changed successfully, redirecting...');
                                // Redirect to main page to apply new language across the service
                                window.location.href = 'index.php';
                            } else {
                                console.error('Language change failed:', response.message);
                                alert('<?php echo LanguageManager::t('dashboard_error'); ?>: ' + (response.message || 'Unknown error'));
                                // Restore button state
                                location.reload();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Language change error:', error);
                            console.error('Status:', status);
                            console.error('Response text:', xhr.responseText);
                            console.error('Response headers:', xhr.getAllResponseHeaders());
                            
                            // Try to parse response as JSON
                            let errorMessage = '<?php echo LanguageManager::t('error_network'); ?>: ' + error;
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                console.error('Failed to parse response as JSON:', e);
                                errorMessage = '<?php echo LanguageManager::t('error_network'); ?>: Invalid server response';
                            }
                            
                            alert(errorMessage);
                            // Restore button state
                            location.reload();
                        }
                    });
                } else {
                    console.error('No language selected');
                }
            });
            
            // Prevent dropdown from closing when clicking inside
            languageDropdown.on('click', function(e) {
                e.stopPropagation();
            });
        });

        // Simple notification function
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>
