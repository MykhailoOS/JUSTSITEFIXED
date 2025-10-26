<?php
/**
 * Glow Component
 * Adapted from React component
 */

function renderGlow($props = []) {
    $defaultProps = [
        'variant' => 'top',
        'className' => ''
    ];
    
    $props = array_merge($defaultProps, $props);
    
    $variantClasses = [
        'top' => 'top-0',
        'above' => '-top-[128px]',
        'bottom' => 'bottom-0',
        'below' => '-bottom-[128px]',
        'center' => 'top-[50%]'
    ];
    
    $variantClass = $variantClasses[$props['variant']] ?? $variantClasses['top'];
    $centerClass = $props['variant'] === 'center' ? '-translate-y-1/2' : '';
    
    return '
    <div class="absolute w-full ' . $variantClass . ' ' . $props['className'] . '">
        <div class="absolute left-1/2 h-[256px] w-[60%] -translate-x-1/2 scale-[2.5] rounded-[50%] bg-gradient-to-r from-orange-400/50 via-yellow-400/30 to-transparent sm:h-[512px] glow-effect ' . $centerClass . '"></div>
        <div class="absolute left-1/2 h-[128px] w-[40%] -translate-x-1/2 scale-[2] rounded-[50%] bg-gradient-to-r from-yellow-400/40 via-orange-300/20 to-transparent sm:h-[256px] glow-effect ' . $centerClass . '"></div>
    </div>';
}
?>