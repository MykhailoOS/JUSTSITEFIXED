<?php
/**
 * Feature Section with Hover Effects
 * Adapted from React component
 */

function renderFeatureCard($props = []) {
    $defaultProps = [
        'title' => '',
        'description' => '',
        'icon' => '',
        'index' => 0
    ];
    
    $props = array_merge($defaultProps, $props);
    
    $borderClasses = '';
    if ($props['index'] === 0 || $props['index'] === 4) {
        $borderClasses .= 'lg:border-l border-gray-800';
    }
    if ($props['index'] < 4) {
        $borderClasses .= ' lg:border-b border-gray-800';
    }
    
    $gradientClass = $props['index'] < 4 ? 'from-gray-100 dark:from-gray-800' : 'from-gray-100 dark:from-gray-800';
    $gradientDirection = $props['index'] < 4 ? 'bg-gradient-to-t' : 'bg-gradient-to-b';
    
    return '
    <div class="flex flex-col lg:border-r border-gray-800 py-10 relative group/feature ' . $borderClasses . '">
        <!-- Hover gradient overlay -->
        <div class="opacity-0 group-hover/feature:opacity-100 transition duration-200 absolute inset-0 h-full w-full ' . $gradientDirection . ' ' . $gradientClass . ' to-transparent pointer-events-none"></div>
        
        <!-- Icon -->
        <div class="mb-4 relative z-10 px-10 text-gray-600 dark:text-gray-400">
            ' . $props['icon'] . '
        </div>
        
        <!-- Title with animated bar -->
        <div class="text-lg font-bold mb-2 relative z-10 px-10">
            <div class="absolute left-0 inset-y-0 h-6 group-hover/feature:h-8 w-1 rounded-tr-full rounded-br-full bg-gray-300 dark:bg-gray-700 group-hover/feature:bg-blue-500 transition-all duration-200 origin-center"></div>
            <span class="group-hover/feature:translate-x-2 transition duration-200 inline-block text-gray-800 dark:text-gray-100">
                ' . htmlspecialchars($props['title']) . '
            </span>
        </div>
        
        <!-- Description -->
        <p class="text-sm text-gray-600 dark:text-gray-300 max-w-xs relative z-10 px-10">
            ' . htmlspecialchars($props['description']) . '
        </p>
    </div>';
}

function renderFeaturesSectionWithHoverEffects($props = []) {
    $defaultProps = [
        'features' => []
    ];
    
    $props = array_merge($defaultProps, $props);
    
    $featuresHtml = '';
    foreach ($props['features'] as $index => $feature) {
        $featuresHtml .= renderFeatureCard([
            'title' => $feature['title'],
            'description' => $feature['description'],
            'icon' => $feature['icon'],
            'index' => $index
        ]);
    }
    
    return '
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 relative z-10 py-10 max-w-7xl mx-auto">
        ' . $featuresHtml . '
    </div>';
}

// Icon components (adapted from Tabler icons)
function renderIconTerminal2() {
    return '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
    </svg>';
}

function renderIconEaseInOut() {
    return '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
    </svg>';
}

function renderIconCurrencyDollar() {
    return '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
    </svg>';
}

function renderIconCloud() {
    return '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
    </svg>';
}

function renderIconRouteAltLeft() {
    return '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
    </svg>';
}

function renderIconHelp() {
    return '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>';
}

function renderIconAdjustmentsBolt() {
    return '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
    </svg>';
}

function renderIconHeart() {
    return '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
    </svg>';
}
?>
