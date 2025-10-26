<?php
/**
 * Badge Component
 * Adapted from React component
 */

function renderBadge($props = []) {
    $defaultProps = [
        'variant' => 'default',
        'className' => ''
    ];
    
    $props = array_merge($defaultProps, $props);
    
    $baseClasses = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2';
    
    $variantClasses = [
        'default' => 'border-transparent bg-primary text-primary-foreground hover:bg-primary/80',
        'secondary' => 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
        'destructive' => 'border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80',
        'outline' => 'text-foreground'
    ];
    
    $variantClass = $variantClasses[$props['variant']] ?? $variantClasses['default'];
    $allClasses = $baseClasses . ' ' . $variantClass . ' ' . $props['className'];
    
    return '
    <div class="' . $allClasses . '">
        ' . $props['children'] . '
    </div>';
}
?>