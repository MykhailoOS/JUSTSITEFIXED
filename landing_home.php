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
            --popover: 0 0% 100%;
            --popover-foreground: 0 0% 3.9%;
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
            --popover: 0 0% 3.9%;
            --popover-foreground: 0 0% 98%;
        }
        
        /* Shine Border Animation */
        @keyframes shine-pulse {
            0% {
                background-position: 0% 0%;
            }
            100% {
                background-position: 300% 300%;
            }
        }
        
        /* TypeWriter Animation */
        @keyframes typewriter {
            0% { width: 0; }
            100% { width: 100%; }
        }
        
        .typewriter {
            overflow: hidden;
            border-right: 2px solid;
            white-space: nowrap;
            animation: typewriter 2s steps(40, end) infinite;
        }
        
        /* Dark theme */
        body {
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
        }
        
        /* Brand colors */
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
        
        /* Popover styles */
        .bg-popover {
            background-color: hsl(var(--popover));
        }
        
        .text-primary {
            color: hsl(var(--primary));
        }
        
        .text-primary-foreground {
            color: hsl(var(--primary-foreground));
        }
        
        /* Text red color */
        .text-text-red-500 {
            color: #ef4444;
        }
        
        /* Hover effects */
        .hover\:text-ali:hover {
            color: #ef4444;
        }
    </style>
</head>
<body class="min-h-screen">
    <?php include __DIR__ . '/components/loading-screen.php'; ?>
    
    <!-- Hero Designali Section -->
    <?php
    require_once __DIR__ . '/components/ui/hero-designali.php';
    
    echo renderHeroDesignali([
        'title' => 'Your complete platform for the',
        'subtitle' => 'Welcome to my creative playground! I\'m',
        'description' => 'I craft enchanting visuals for brands, and conjure design resources to empower others. I am an expert in design like',
        'talkAbout' => [
            LanguageManager::t('hero_word_1') ?: 'Graphic Design',
            LanguageManager::t('hero_word_2') ?: 'Branding', 
            LanguageManager::t('hero_word_3') ?: 'Web Design',
            LanguageManager::t('hero_word_4') ?: 'Web Develop',
            LanguageManager::t('hero_word_5') ?: 'Marketing'
        ],
        'primaryButton' => [
            'text' => LanguageManager::t('hero_get_started') ?: 'Start Posting',
            'href' => $uid ? 'index.php' : 'register.php'
        ],
        'secondaryButton' => [
            'text' => 'Book a call',
            'href' => 'https://cal.com/aliimam/designali'
        ]
    ]);
    ?>
    
    <!-- Canvas Animation Script -->
    <?php echo renderCanvasScript(); ?>
</body>
</html>
