<?php
/**
 * Background Paths Component
 * Adapted from React Framer Motion component
 */

function renderBackgroundPaths($props = []) {
    $defaultProps = [
        'title' => 'Background Paths',
        'buttonText' => 'Discover Excellence',
        'buttonHref' => '#',
        'class' => ''
    ];
    
    $props = array_merge($defaultProps, $props);
    
    return '
    <div class="relative min-h-screen w-full flex items-center justify-center overflow-hidden bg-white dark:bg-gray-950 ' . $props['class'] . '">
        <!-- Animated Background Paths -->
        <div class="absolute inset-0 pointer-events-none">
            <svg class="w-full h-full text-gray-900 dark:text-white" viewBox="0 0 696 316" fill="none">
                <title>Background Paths</title>
                ' . generateAnimatedPaths() . '
            </svg>
        </div>
        
        <!-- Content -->
        <div class="relative z-10 container mx-auto px-4 md:px-6 text-center">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-5xl sm:text-7xl md:text-8xl font-bold mb-8 tracking-tighter">
                    ' . renderAnimatedTitle($props['title']) . '
                </h1>
                
                <div class="inline-block group relative bg-gradient-to-b from-black/10 to-white/10 dark:from-white/10 dark:to-black/10 p-px rounded-2xl backdrop-blur-lg overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <a href="' . htmlspecialchars($props['buttonHref']) . '" class="inline-flex items-center justify-center rounded-[1.15rem] px-8 py-6 text-lg font-semibold backdrop-blur-md bg-white/95 hover:bg-white/100 dark:bg-black/95 dark:hover:bg-black/100 text-black dark:text-white transition-all duration-300 group-hover:-translate-y-0.5 border border-black/10 dark:border-white/10 hover:shadow-md dark:hover:shadow-gray-800/50">
                        <span class="opacity-90 group-hover:opacity-100 transition-opacity">
                            ' . htmlspecialchars($props['buttonText']) . '
                        </span>
                        <span class="ml-3 opacity-70 group-hover:opacity-100 group-hover:translate-x-1.5 transition-all duration-300">
                            →
                        </span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Animation Script -->
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Animate paths
            const paths = document.querySelectorAll(".animated-path");
            paths.forEach((path, index) => {
                const delay = index * 100;
                setTimeout(() => {
                    path.style.animation = "pathDraw 20s linear infinite";
                }, delay);
            });
            
            // Animate title letters
            const letters = document.querySelectorAll(".title-letter");
            letters.forEach((letter, index) => {
                const delay = index * 30;
                setTimeout(() => {
                    letter.style.animation = "letterAppear 0.8s ease-out forwards";
                }, delay);
            });
        });
        </script>
        
        <!-- Animation Styles -->
        <style>
        @keyframes pathDraw {
            0% { stroke-dasharray: 0 1000; opacity: 0.3; }
            50% { opacity: 0.6; }
            100% { stroke-dasharray: 1000 0; opacity: 0.3; }
        }
        
        @keyframes letterAppear {
            0% { transform: translateY(100px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        
        .animated-path {
            stroke-dasharray: 0 1000;
            stroke-dashoffset: 0;
        }
        
        .title-letter {
            transform: translateY(100px);
            opacity: 0;
        }
        </style>
    </div>';
}

function generateAnimatedPaths() {
    $paths = '';
    
    // Generate 36 animated paths
    for ($i = 0; $i < 36; $i++) {
        $position = 1;
        $x1 = 380 - $i * 5 * $position;
        $y1 = 189 + $i * 6;
        $x2 = 312 - $i * 5 * $position;
        $y2 = 216 - $i * 6;
        $x3 = 152 - $i * 5 * $position;
        $y3 = 343 - $i * 6;
        $x4 = 616 - $i * 5 * $position;
        $y4 = 470 - $i * 6;
        $x5 = 684 - $i * 5 * $position;
        $y5 = 875 - $i * 6;
        
        $d = "M-$x1 -$y1 C-$x1 -$y1 -$x2 $y2 $x3 $y3 C$x4 $y4 $x5 $y5 $x5 $y5";
        $opacity = 0.1 + $i * 0.03;
        $width = 0.5 + $i * 0.03;
        
        $paths .= '<path class="animated-path" d="' . $d . '" stroke="currentColor" stroke-width="' . $width . '" stroke-opacity="' . $opacity . '" fill="none"></path>';
    }
    
    return $paths;
}

function renderAnimatedTitle($title) {
    $words = explode(' ', $title);
    $html = '';
    
    foreach ($words as $wordIndex => $word) {
        $html .= '<span class="inline-block mr-4 last:mr-0">';
        $letters = str_split($word);
        
        foreach ($letters as $letterIndex => $letter) {
            $html .= '<span class="title-letter inline-block text-transparent bg-clip-text bg-gradient-to-r from-gray-900 to-gray-700/80 dark:from-white dark:to-white/80" style="animation-delay: ' . (($wordIndex * 0.1 + $letterIndex * 0.03) * 1000) . 'ms;">';
            $html .= htmlspecialchars($letter);
            $html .= '</span>';
        }
        
        $html .= '</span>';
    }
    
    return $html;
}
?>
