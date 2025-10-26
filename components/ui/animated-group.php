<?php
/**
 * Animated Group Component
 * Adapted from React Framer Motion component
 */

function renderAnimatedGroup($props = []) {
    $defaultProps = [
        'className' => '',
        'preset' => 'fade',
        'variants' => null
    ];
    
    $props = array_merge($defaultProps, $props);
    
    // Preset variants
    $presetVariants = [
        'fade' => [
            'container' => [
                'hidden' => ['opacity' => 0],
                'visible' => ['opacity' => 1, 'transition' => ['staggerChildren' => 0.1]]
            ],
            'item' => [
                'hidden' => ['opacity' => 0],
                'visible' => ['opacity' => 1]
            ]
        ],
        'slide' => [
            'container' => [
                'hidden' => ['opacity' => 0],
                'visible' => ['opacity' => 1, 'transition' => ['staggerChildren' => 0.1]]
            ],
            'item' => [
                'hidden' => ['opacity' => 0, 'y' => 20],
                'visible' => ['opacity' => 1, 'y' => 0]
            ]
        ],
        'scale' => [
            'container' => [
                'hidden' => ['opacity' => 0],
                'visible' => ['opacity' => 1, 'transition' => ['staggerChildren' => 0.1]]
            ],
            'item' => [
                'hidden' => ['opacity' => 0, 'scale' => 0.8],
                'visible' => ['opacity' => 1, 'scale' => 1]
            ]
        ],
        'blur' => [
            'container' => [
                'hidden' => ['opacity' => 0],
                'visible' => ['opacity' => 1, 'transition' => ['staggerChildren' => 0.1]]
            ],
            'item' => [
                'hidden' => ['opacity' => 0, 'filter' => 'blur(4px)'],
                'visible' => ['opacity' => 1, 'filter' => 'blur(0px)']
            ]
        ],
        'blur-slide' => [
            'container' => [
                'hidden' => ['opacity' => 0],
                'visible' => ['opacity' => 1, 'transition' => ['staggerChildren' => 0.1]]
            ],
            'item' => [
                'hidden' => ['opacity' => 0, 'filter' => 'blur(4px)', 'y' => 20],
                'visible' => ['opacity' => 1, 'filter' => 'blur(0px)', 'y' => 0]
            ]
        ]
    ];
    
    $selectedVariants = $presetVariants[$props['preset']] ?? $presetVariants['fade'];
    $containerVariants = $props['variants']['container'] ?? $selectedVariants['container'];
    $itemVariants = $props['variants']['item'] ?? $selectedVariants['item'];
    
    return '
    <div class="animated-group ' . $props['className'] . '" 
         data-preset="' . $props['preset'] . '"
         data-container-variants=\'' . json_encode($containerVariants) . '\'
         data-item-variants=\'' . json_encode($itemVariants) . '\'>
        ' . $props['children'] . '
    </div>
    
    <style>
    .animated-group {
        opacity: 0;
    }
    
    .animated-group.animate {
        opacity: 1;
    }
    
    .animated-group .animated-item {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease;
    }
    
    .animated-group.animate .animated-item {
        opacity: 1;
        transform: translateY(0);
    }
    
    .animated-group[data-preset="blur"] .animated-item {
        filter: blur(4px);
    }
    
    .animated-group[data-preset="blur"].animate .animated-item {
        filter: blur(0px);
    }
    
    .animated-group[data-preset="scale"] .animated-item {
        transform: scale(0.8);
    }
    
    .animated-group[data-preset="scale"].animate .animated-item {
        transform: scale(1);
    }
    </style>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const animatedGroups = document.querySelectorAll(".animated-group");
        
        animatedGroups.forEach(group => {
            const items = group.querySelectorAll(".animated-item");
            const staggerDelay = 0.1;
            
            // Animate group
            setTimeout(() => {
                group.classList.add("animate");
                
                // Animate items with stagger
                items.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.transitionDelay = (index * staggerDelay) + "s";
                    }, index * 100);
                });
            }, 200);
        });
    });
    </script>';
}
?>
