<?php
/**
 * Animated Hero Component
 * Adapted from React Framer Motion component
 */

function renderAnimatedHero($props = []) {
    $defaultProps = [
        'badgeText' => 'Read our launch article',
        'badgeHref' => '#article',
        'titlePrefix' => 'This is something',
        'animatedWords' => ['amazing', 'new', 'wonderful', 'beautiful', 'smart'],
        'description' => 'Managing a small business today is already tough. Avoid further complications by ditching outdated, tedious trade methods. Our goal is to streamline SMB trade, making it easier and faster than ever.',
        'button1Text' => 'Jump on a call',
        'button1Href' => '#call',
        'button2Text' => 'Sign up here',
        'button2Href' => 'register.php'
    ];
    
    $props = array_merge($defaultProps, $props);
    
    return '
    <div class="w-full">
        <div class="container mx-auto">
            <div class="flex gap-8 py-20 lg:py-40 items-center justify-center flex-col">
                <!-- Badge -->
                <div>
                    <a href="' . $props['badgeHref'] . '" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-gray-100 text-gray-900 hover:bg-gray-200 h-9 rounded-md px-3 gap-4">
                        ' . htmlspecialchars($props['badgeText']) . '
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                
                <!-- Title with animated words -->
                <div class="flex gap-4 flex-col">
                    <h1 class="text-5xl md:text-7xl max-w-2xl tracking-tighter text-center font-regular">
                        <span class="text-white">' . htmlspecialchars($props['titlePrefix']) . '</span>
                        <span class="relative flex w-full justify-center overflow-hidden text-center md:pb-4 md:pt-1">
                            &nbsp;
                            <div class="animated-words-container">
                                ' . implode('', array_map(function($word, $index) {
                                    return '<span class="animated-word absolute font-semibold" data-index="' . $index . '">' . htmlspecialchars($word) . '</span>';
                                }, $props['animatedWords'], array_keys($props['animatedWords']))) . '
                            </div>
                        </span>
                    </h1>
                    
                    <!-- Description -->
                    <p class="text-lg md:text-xl leading-relaxed tracking-tight text-gray-400 max-w-2xl text-center">
                        ' . htmlspecialchars($props['description']) . '
                    </p>
                </div>
                
                <!-- Buttons -->
                <div class="flex flex-row gap-3">
                    <a href="' . $props['button1Href'] . '" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-600 bg-transparent hover:bg-gray-800 text-white h-11 rounded-md px-8 gap-4">
                        ' . htmlspecialchars($props['button1Text']) . '
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </a>
                    <a href="' . $props['button2Href'] . '" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-white text-black hover:bg-gray-200 h-11 rounded-md px-8 gap-4">
                        ' . htmlspecialchars($props['button2Text']) . '
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .animated-words-container {
        position: relative;
        height: 1.2em;
        overflow: hidden;
    }
    
    .animated-word {
        opacity: 0;
        transform: translateY(-100px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 600;
    }
    
    .animated-word.active {
        opacity: 1;
        transform: translateY(0);
    }
    
    .animated-word.prev {
        opacity: 0;
        transform: translateY(-150px);
    }
    
    .animated-word.next {
        opacity: 0;
        transform: translateY(150px);
    }
    </style>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const words = document.querySelectorAll(".animated-word");
        let currentIndex = 0;
        
        function animateWords() {
            // Hide all words
            words.forEach((word, index) => {
                word.classList.remove("active", "prev", "next");
                
                if (index === currentIndex) {
                    word.classList.add("active");
                } else if (index < currentIndex) {
                    word.classList.add("prev");
                } else {
                    word.classList.add("next");
                }
            });
            
            // Move to next word
            currentIndex = (currentIndex + 1) % words.length;
        }
        
        // Start animation
        animateWords();
        
        // Set interval for word changes
        setInterval(animateWords, 2000);
    });
    </script>';
}
?>
