<?php
// Set UTF-8 encoding
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/language.php';

// Initialize language system
LanguageManager::init();

// Check if user is logged in
$uid = current_user_id();
?>
<!DOCTYPE html>
<html lang="<?php echo LanguageManager::getCurrentLanguage(); ?>" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo LanguageManager::t('site_title'); ?> — <?php echo LanguageManager::t('tagline'); ?></title>
    <link rel="stylesheet" href="styles/main.css?v=<?php echo time(); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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
        
        /* CSS Variables */
        :root {
            --brand: 27 96% 61%;
            --brand-foreground: 31 97% 72%;
            --background: 0 0% 100%;
            --foreground: 0 0% 3.9%;
            --muted: 0 0% 96.1%;
            --muted-foreground: 0 0% 45.1%;
            --border: 0 0% 89.8%;
            --input: 0 0% 89.8%;
            --primary: 0 0% 9%;
            --primary-foreground: 0 0% 98%;
            --secondary: 0 0% 96.1%;
            --secondary-foreground: 0 0% 9%;
            --accent: 0 0% 96.1%;
            --accent-foreground: 0 0% 9%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 0 0% 98%;
            --ring: 0 0% 3.9%;
        }
        
        .dark {
            --brand: 31 97% 72%;
            --brand-foreground: 27 96% 61%;
            --background: 0 0% 3.9%;
            --foreground: 0 0% 98%;
            --muted: 0 0% 14.9%;
            --muted-foreground: 0 0% 63.9%;
            --border: 0 0% 14.9%;
            --input: 0 0% 14.9%;
            --primary: 0 0% 98%;
            --primary-foreground: 0 0% 9%;
            --secondary: 0 0% 14.9%;
            --secondary-foreground: 0 0% 98%;
            --accent: 0 0% 14.9%;
            --accent-foreground: 0 0% 98%;
            --destructive: 0 62.8% 30.6%;
            --destructive-foreground: 0 0% 98%;
            --ring: 0 0% 83.1%;
        }
        
        /* Animations */
        @keyframes appear {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes appear-zoom {
            0% { opacity: 0; transform: scale(0.95); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        .animate-appear {
            animation: appear 0.5s ease-out forwards;
        }
        
        .animate-appear-zoom {
            animation: appear-zoom 0.5s ease-out forwards;
        }
        
        .delay-100 {
            animation-delay: 100ms;
        }
        
        .delay-300 {
            animation-delay: 300ms;
        }
        
        .delay-700 {
            animation-delay: 700ms;
        }
        
        .delay-1000 {
            animation-delay: 1000ms;
        }
        
        /* Utilities */
        .fade-bottom {
            background: linear-gradient(to bottom, transparent 0%, hsl(var(--background)) 100%);
        }
        
        .max-w-container {
            max-width: 1200px;
        }
        
        /* Dark theme */
        body {
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
        }
        
        /* Brand colors for glow */
        .bg-brand {
            background-color: hsl(var(--brand));
        }
        
        .text-brand {
            color: hsl(var(--brand));
        }
        
        .bg-brand-foreground {
            background-color: hsl(var(--brand-foreground));
        }
        
        .text-brand-foreground {
            color: hsl(var(--brand-foreground));
        }
        
        /* Gradient utilities */
        .bg-gradient-radial {
            background: radial-gradient(ellipse at center, var(--tw-gradient-stops));
        }
        
        /* Ensure glow is visible */
        .glow-effect {
            filter: blur(40px);
            opacity: 0.6;
        }
    </style>
</head>
<body class="min-h-screen">
    <?php include __DIR__ . '/components/loading-screen.php'; ?>

    <!-- Hero Section -->
    <?php
    require_once __DIR__ . '/components/blocks/hero-section.php';
    
    echo renderHeroSection([
        'badge' => [
            'text' => LanguageManager::t('hero_badge_text'),
            'action' => [
                'text' => LanguageManager::t('hero_badge_action'),
                'href' => '#features'
            ]
        ],
        'title' => LanguageManager::t('hero_title'),
        'description' => LanguageManager::t('hero_description'),
        'actions' => [
            [
                'text' => LanguageManager::t('hero_get_started'),
                'href' => $uid ? 'index.php' : 'register.php',
                'variant' => 'default'
            ],
            [
                'text' => LanguageManager::t('hero_github'),
                'href' => 'https://github.com/justsite',
                'variant' => 'glow',
                'icon' => renderIcon('gitHub', ['className' => 'h-5 w-5'])
            ]
        ],
        'image' => [
            'light' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1248&h=765&fit=crop&auto=format',
            'dark' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1248&h=765&fit=crop&auto=format',
            'alt' => 'JustSite UI Components Preview'
        ]
    ]);
    ?>
</body>
</html>