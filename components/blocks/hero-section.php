<?php
/**
 * Hero Section Component
 * Adapted from React component
 */

require_once __DIR__ . '/../ui/badge.php';
require_once __DIR__ . '/../ui/button.php';
require_once __DIR__ . '/../ui/mockup.php';
require_once __DIR__ . '/../ui/glow.php';
require_once __DIR__ . '/../ui/icons.php';

function renderHeroSection($props = []) {
    $defaultProps = [
        'badge' => null,
        'title' => '',
        'description' => '',
        'actions' => [],
        'image' => [
            'light' => '',
            'dark' => '',
            'alt' => ''
        ]
    ];
    
    $props = array_merge($defaultProps, $props);
    
    // Determine which image to use (default to dark for dark theme)
    $imageSrc = $props['image']['dark'] ?: $props['image']['light'];
    
    return '
    <section class="bg-background text-foreground py-12 sm:py-24 md:py-32 px-4 fade-bottom overflow-hidden pb-0">
        <div class="mx-auto flex max-w-container flex-col gap-12 pt-16 sm:gap-24">
            <div class="flex flex-col items-center gap-6 text-center sm:gap-12">
                <!-- Badge -->
                ' . ($props['badge'] ? '
                <div class="animate-appear gap-2">
                    ' . renderBadge([
                        'variant' => 'outline',
                        'className' => 'animate-appear gap-2',
                        'children' => '
                            <span class="text-muted-foreground">' . htmlspecialchars($props['badge']['text']) . '</span>
                            <a href="' . $props['badge']['action']['href'] . '" class="flex items-center gap-1">
                                ' . htmlspecialchars($props['badge']['action']['text']) . '
                                ' . renderIcon('arrowRight', ['className' => 'h-3 w-3']) . '
                            </a>
                        '
                    ]) . '
                </div>
                ' : '') . '
                
                <!-- Title -->
                <h1 class="relative z-10 inline-block animate-appear bg-gradient-to-r from-foreground to-muted-foreground bg-clip-text text-4xl font-semibold leading-tight text-transparent drop-shadow-2xl sm:text-6xl sm:leading-tight md:text-8xl md:leading-tight">
                    ' . htmlspecialchars($props['title']) . '
                </h1>
                
                <!-- Description -->
                <p class="text-md relative z-10 max-w-[550px] animate-appear font-medium text-muted-foreground opacity-0 delay-100 sm:text-xl">
                    ' . htmlspecialchars($props['description']) . '
                </p>
                
                <!-- Actions -->
                <div class="relative z-10 flex animate-appear justify-center gap-4 opacity-0 delay-300">
                    ' . implode('', array_map(function($action, $index) {
                        $variant = $action['variant'] ?? 'default';
                        $icon = isset($action['icon']) ? $action['icon'] : '';
                        
                        return '
                        <a href="' . $action['href'] . '" class="' . getButtonClasses($variant, 'lg') . ' flex items-center gap-2">
                            ' . $icon . '
                            ' . htmlspecialchars($action['text']) . '
                        </a>';
                    }, $props['actions'], array_keys($props['actions']))) . '
                </div>
                
                <!-- Image with Glow -->
                <div class="relative pt-12">
                    ' . renderMockupFrame([
                        'className' => 'animate-appear opacity-0 delay-700',
                        'size' => 'small',
                        'children' => renderMockup([
                            'type' => 'responsive',
                            'children' => '
                                <img src="' . $imageSrc . '" alt="' . htmlspecialchars($props['image']['alt']) . '" width="1248" height="765" class="w-full h-auto" />
                            '
                        ])
                    ]) . '
                    ' . renderGlow([
                        'variant' => 'top',
                        'className' => 'animate-appear-zoom opacity-0 delay-1000'
                    ]) . '
                </div>
            </div>
        </div>
    </section>';
}

function getButtonClasses($variant, $size) {
    $baseClasses = 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50';
    
    $variantClasses = [
        'default' => 'bg-primary text-primary-foreground hover:bg-primary/90',
        'destructive' => 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
        'outline' => 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
        'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
        'ghost' => 'hover:bg-accent hover:text-accent-foreground',
        'link' => 'text-primary underline-offset-4 hover:underline',
        'glow' => 'bg-white text-black hover:bg-gray-200'
    ];
    
    $sizeClasses = [
        'default' => 'h-10 px-4 py-2',
        'sm' => 'h-9 rounded-md px-3',
        'lg' => 'h-11 rounded-md px-8',
        'icon' => 'h-10 w-10'
    ];
    
    $variantClass = $variantClasses[$variant] ?? $variantClasses['default'];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['default'];
    
    return $baseClasses . ' ' . $variantClass . ' ' . $sizeClass;
}
?>