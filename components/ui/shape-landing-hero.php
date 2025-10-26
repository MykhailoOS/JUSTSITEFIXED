<?php
/**
 * Shape Landing Hero Component
 * Adapted from React Framer Motion component
 */

function renderElegantShape($props = []) {
    $defaultProps = [
        'className' => '',
        'delay' => 0,
        'width' => 400,
        'height' => 100,
        'rotate' => 0,
        'gradient' => 'from-white/[0.08]'
    ];
    
    $props = array_merge($defaultProps, $props);
    
    return '
    <div class="elegant-shape absolute ' . $props['className'] . '" 
         style="animation-delay: ' . $props['delay'] . 's; transform: rotate(' . $props['rotate'] . 'deg);">
        <div class="floating-shape" style="width: ' . $props['width'] . 'px; height: ' . $props['height'] . 'px;">
            <div class="shape-content absolute inset-0 rounded-full bg-gradient-to-r to-transparent ' . $props['gradient'] . ' backdrop-blur-[2px] border-2 border-white/[0.15] shadow-[0_8px_32px_0_rgba(255,255,255,0.1)] after:absolute after:inset-0 after:rounded-full after:bg-[radial-gradient(circle_at_50%_50%,rgba(255,255,255,0.2),transparent_70%)]"></div>
        </div>
    </div>';
}

function renderHeroGeometric($props = []) {
    $defaultProps = [
        'badge' => 'Design Collective',
        'title1' => 'Elevate Your Digital Vision',
        'title2' => 'Crafting Exceptional Websites',
        'description' => 'Crafting exceptional digital experiences through innovative design and cutting-edge technology.'
    ];
    
    $props = array_merge($defaultProps, $props);
    
    return '
    <div class="relative min-h-screen w-full flex items-center justify-center overflow-hidden bg-[#030303]">
        <!-- Background gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/[0.05] via-transparent to-rose-500/[0.05] blur-3xl"></div>
        
        <!-- Animated shapes -->
        <div class="absolute inset-0 overflow-hidden">
            ' . renderElegantShape([
                'delay' => 0.3,
                'width' => 600,
                'height' => 140,
                'rotate' => 12,
                'gradient' => 'from-indigo-500/[0.15]',
                'className' => 'left-[-10%] md:left-[-5%] top-[15%] md:top-[20%]'
            ]) . '
            
            ' . renderElegantShape([
                'delay' => 0.5,
                'width' => 500,
                'height' => 120,
                'rotate' => -15,
                'gradient' => 'from-rose-500/[0.15]',
                'className' => 'right-[-5%] md:right-[0%] top-[70%] md:top-[75%]'
            ]) . '
            
            ' . renderElegantShape([
                'delay' => 0.4,
                'width' => 300,
                'height' => 80,
                'rotate' => -8,
                'gradient' => 'from-violet-500/[0.15]',
                'className' => 'left-[5%] md:left-[10%] bottom-[5%] md:bottom-[10%]'
            ]) . '
            
            ' . renderElegantShape([
                'delay' => 0.6,
                'width' => 200,
                'height' => 60,
                'rotate' => 20,
                'gradient' => 'from-amber-500/[0.15]',
                'className' => 'right-[15%] md:right-[20%] top-[10%] md:top-[15%]'
            ]) . '
            
            ' . renderElegantShape([
                'delay' => 0.7,
                'width' => 150,
                'height' => 40,
                'rotate' => -25,
                'gradient' => 'from-cyan-500/[0.15]',
                'className' => 'left-[20%] md:left-[25%] top-[5%] md:top-[10%]'
            ]) . '
        </div>
        
        <!-- Content -->
        <div class="relative z-10 container mx-auto px-4 md:px-6">
            <div class="max-w-3xl mx-auto text-center">
                <!-- Badge -->
                <div class="fade-up-0 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/[0.03] border border-white/[0.08] mb-8 md:mb-12">
                    <div class="h-2 w-2 rounded-full bg-rose-500/80"></div>
                    <span class="text-sm text-white/60 tracking-wide">' . htmlspecialchars($props['badge']) . '</span>
                </div>
                
                <!-- Title -->
                <div class="fade-up-1">
                    <h1 class="text-4xl sm:text-6xl md:text-8xl font-bold mb-6 md:mb-8 tracking-tight">
                        <span class="bg-clip-text text-transparent bg-gradient-to-b from-white to-white/80">
                            ' . htmlspecialchars($props['title1']) . '
                        </span>
                        <br />
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-300 via-white/90 to-rose-300">
                            ' . htmlspecialchars($props['title2']) . '
                        </span>
                    </h1>
                </div>
                
                <!-- Description -->
                <div class="fade-up-2">
                    <p class="text-base sm:text-lg md:text-xl text-white/40 mb-8 leading-relaxed font-light tracking-wide max-w-xl mx-auto px-4">
                        ' . htmlspecialchars($props['description']) . '
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Bottom gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#030303] via-transparent to-[#030303]/80 pointer-events-none"></div>
        
        <!-- Animation styles -->
        <style>
        .elegant-shape {
            opacity: 0;
            transform: translateY(-150px) rotate(calc(var(--rotate, 0deg) - 15deg));
            animation: shapeAppear 2.4s ease-out forwards;
        }
        
        .floating-shape {
            animation: float 12s ease-in-out infinite;
        }
        
        .fade-up-0 {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeUp 1s ease-out 0.5s forwards;
        }
        
        .fade-up-1 {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeUp 1s ease-out 0.7s forwards;
        }
        
        .fade-up-2 {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeUp 1s ease-out 0.9s forwards;
        }
        
        @keyframes shapeAppear {
            0% {
                opacity: 0;
                transform: translateY(-150px) rotate(calc(var(--rotate, 0deg) - 15deg));
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 1;
                transform: translateY(0) rotate(var(--rotate, 0deg));
            }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(15px); }
        }
        
        @keyframes fadeUp {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        </style>
    </div>';
}
?>
