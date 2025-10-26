<?php
/**
 * Mockup Components
 * Adapted from React components
 */

function renderMockup($props = []) {
    $defaultProps = [
        'type' => 'responsive',
        'className' => ''
    ];
    
    $props = array_merge($defaultProps, $props);
    
    $typeClasses = [
        'mobile' => 'rounded-[48px] max-w-[350px]',
        'responsive' => 'rounded-md'
    ];
    
    $typeClass = $typeClasses[$props['type']] ?? $typeClasses['responsive'];
    
    return '
    <div class="flex relative z-10 overflow-hidden shadow-2xl border border-gray-200 dark:border-gray-800 border-t-gray-300 dark:border-t-gray-700 ' . $typeClass . ' ' . $props['className'] . '">
        ' . $props['children'] . '
    </div>';
}

function renderMockupFrame($props = []) {
    $defaultProps = [
        'size' => 'small',
        'className' => ''
    ];
    
    $props = array_merge($defaultProps, $props);
    
    $sizeClasses = [
        'small' => 'p-2',
        'large' => 'p-4'
    ];
    
    $sizeClass = $sizeClasses[$props['size']] ?? $sizeClasses['small'];
    
    return '
    <div class="bg-gray-100 dark:bg-gray-900 flex relative z-10 overflow-hidden rounded-2xl ' . $sizeClass . ' ' . $props['className'] . '">
        ' . $props['children'] . '
    </div>';
}
?>