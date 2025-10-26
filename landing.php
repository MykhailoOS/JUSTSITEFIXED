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
    <script src="https://unpkg.com/framer-motion@11.0.0/dist/framer-motion.js"></script>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <style>
        /* Hide header during loading */
        body.loading header {
            opacity: 0;
            visibility: hidden;
        }
        
        /* Hide scrollbar during loading */
        body.loading {
            overflow: hidden;
        }
        
        /* Ensure loading screen is on top */
        #loading-screen {
            z-index: 99999 !important;
        }
        
        /* Custom animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        /* Animation classes */
        .animate-fadeInUp {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .animate-fadeInLeft {
            animation: fadeInLeft 0.8s ease-out forwards;
        }
        
        .animate-fadeInRight {
            animation: fadeInRight 0.8s ease-out forwards;
        }
        
        .animate-pulse-slow {
            animation: pulse 3s ease-in-out infinite;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient 3s ease infinite;
        }
        
        /* Text rotate styles */
        .text-rotate-container {
            display: inline-block;
            overflow: hidden;
            position: relative;
        }
        
        .text-rotate-item {
            display: inline-block;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            transform: translateY(30px) scale(0.7) rotateX(90deg);
            opacity: 0;
            transform-origin: center;
        }
        
        .text-rotate-item.active {
            transform: translateY(0) scale(1) rotateX(0deg);
            opacity: 1;
        }
        
        /* Gradient backgrounds */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .bg-gradient-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .bg-gradient-accent {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        /* Glass morphism */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Dark theme */
        body {
            background: linear-gradient(135deg, #0c0c0c 0%, #1a1a1a 100%);
            color: #ffffff;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #1a1a1a;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #764ba2;
        }
        
        /* Hide scrollbar during loading */
        body.loading ::-webkit-scrollbar {
            display: none;
        }
        
        body.loading {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>
<body class="min-h-screen">
    <?php include __DIR__ . '/components/loading-screen.php'; ?>
    
    <!-- Hero Section -->
    <section class="min-h-screen flex items-center justify-center relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 bg-gradient-primary opacity-10"></div>
        <div class="absolute top-20 left-20 w-72 h-72 bg-gradient-secondary rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float"></div>
        <div class="absolute bottom-20 right-20 w-72 h-72 bg-gradient-accent rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 2s;"></div>
        
        <!-- Main Content -->
        <div class="relative z-10 text-center px-6 max-w-6xl mx-auto">
            <!-- Badge -->
            <div class="animate-fadeInUp mb-8">
                <div class="inline-flex items-center px-4 py-2 rounded-full glass text-sm font-medium">
                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse-slow"></span>
                    Introducing JustSite
                </div>
            </div>
            
            <!-- Main Title with Text Rotate -->
            <div class="animate-fadeInUp mb-8" style="animation-delay: 0.2s;">
                <h1 class="text-4xl sm:text-6xl md:text-8xl font-bold mb-4">
                    <span class="block mb-4">Make it</span>
                    <div class="text-rotate-container inline-block relative">
                        <span class="text-rotate-item active bg-gradient-primary text-white px-6 py-3 rounded-xl mx-1 shadow-lg">work!</span>
                        <span class="text-rotate-item bg-gradient-secondary text-white px-6 py-3 rounded-xl mx-1 shadow-lg opacity-0">fancy ✽</span>
                        <span class="text-rotate-item bg-gradient-accent text-white px-6 py-3 rounded-xl mx-1 shadow-lg opacity-0">right</span>
                        <span class="text-rotate-item bg-gradient-primary text-white px-6 py-3 rounded-xl mx-1 shadow-lg opacity-0">fast</span>
                        <span class="text-rotate-item bg-gradient-secondary text-white px-6 py-3 rounded-xl mx-1 shadow-lg opacity-0">fun</span>
                        <span class="text-rotate-item bg-gradient-accent text-white px-6 py-3 rounded-xl mx-1 shadow-lg opacity-0">rock</span>
                        <span class="text-rotate-item bg-gradient-primary text-white px-6 py-3 rounded-xl mx-1 shadow-lg opacity-0">🕶️🕶️🕶️</span>
                    </div>
                </h1>
            </div>
            
            <!-- Description -->
            <div class="animate-fadeInUp mb-12" style="animation-delay: 0.4s;">
                <p class="text-lg sm:text-xl md:text-2xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
                    Where thoughts take shape and consciousness flows like liquid mercury through infinite dimensions.
                </p>
            </div>
            
            <!-- CTA Buttons -->
            <div class="animate-fadeInUp flex flex-col sm:flex-row gap-4 justify-center items-center" style="animation-delay: 0.6s;">
                <a href="<?php echo $uid ? 'index.php' : 'register.php'; ?>" class="group relative px-8 py-4 bg-gradient-primary text-white font-semibold rounded-full overflow-hidden transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                    <span class="relative z-10">Get Started</span>
                    <div class="absolute inset-0 bg-gradient-secondary opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>
                <a href="https://t.me/justsite" target="_blank" class="group px-8 py-4 glass text-white font-semibold rounded-full transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                        Contact
                    </span>
                </a>
            </div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute top-1/4 left-1/4 w-4 h-4 bg-white rounded-full opacity-20 animate-float"></div>
        <div class="absolute top-3/4 right-1/4 w-6 h-6 bg-gradient-primary rounded-full opacity-30 animate-float" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/6 w-3 h-3 bg-gradient-accent rounded-full opacity-40 animate-float" style="animation-delay: 2s;"></div>
    </section>
    
    <!-- Features Section -->
    <section class="py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16 animate-fadeInUp">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4">Why Choose JustSite?</h2>
                <p class="text-lg text-gray-300 max-w-2xl mx-auto">Powerful features that make website creation effortless</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group animate-fadeInLeft glass p-8 rounded-2xl transition-all duration-500 hover:scale-110 hover:shadow-2xl hover:bg-gradient-to-br hover:from-purple-500/20 hover:to-blue-500/20">
                    <div class="relative mb-6">
                        <div class="w-20 h-20 bg-gradient-primary rounded-full flex items-center justify-center group-hover:animate-float group-hover:scale-110 transition-all duration-300">
                            <svg class="w-10 h-10 text-white group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-green-400 rounded-full animate-pulse-slow"></div>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-primary">Lightning Fast</h3>
                    <p class="text-gray-300 group-hover:text-white transition-colors duration-300">Build and deploy websites in minutes, not hours. Our optimized platform ensures blazing fast performance.</p>
                    <div class="mt-4 h-1 bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-primary rounded-full animate-pulse-slow" style="width: 85%;"></div>
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="group animate-fadeInUp glass p-8 rounded-2xl transition-all duration-500 hover:scale-110 hover:shadow-2xl hover:bg-gradient-to-br hover:from-pink-500/20 hover:to-red-500/20" style="animation-delay: 0.2s;">
                    <div class="relative mb-6">
                        <div class="w-20 h-20 bg-gradient-secondary rounded-full flex items-center justify-center group-hover:animate-float group-hover:scale-110 transition-all duration-300">
                            <svg class="w-10 h-10 text-white group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-pink-400 rounded-full animate-pulse-slow"></div>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-secondary">Beautiful Design</h3>
                    <p class="text-gray-300 group-hover:text-white transition-colors duration-300">Stunning templates and components that make your website stand out from the crowd.</p>
                    <div class="mt-4 h-1 bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-secondary rounded-full animate-pulse-slow" style="width: 92%;"></div>
                    </div>
                </div>
                
                <!-- Feature 3 -->
                <div class="group animate-fadeInRight glass p-8 rounded-2xl transition-all duration-500 hover:scale-110 hover:shadow-2xl hover:bg-gradient-to-br hover:from-blue-500/20 hover:to-cyan-500/20" style="animation-delay: 0.4s;">
                    <div class="relative mb-6">
                        <div class="w-20 h-20 bg-gradient-accent rounded-full flex items-center justify-center group-hover:animate-float group-hover:scale-110 transition-all duration-300">
                            <svg class="w-10 h-10 text-white group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-blue-400 rounded-full animate-pulse-slow"></div>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-accent">Easy to Use</h3>
                    <p class="text-gray-300 group-hover:text-white transition-colors duration-300">No coding required. Drag, drop, and customize your way to a professional website.</p>
                    <div class="mt-4 h-1 bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-accent rounded-full animate-pulse-slow" style="width: 78%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="py-20 px-6 bg-gradient-primary">
        <div class="max-w-4xl mx-auto text-center">
            <div class="animate-fadeInUp">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-6">Ready to Get Started?</h2>
                <p class="text-lg sm:text-xl mb-8 text-white/90">Join thousands of creators who are already building amazing websites with JustSite.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?php echo $uid ? 'index.php' : 'register.php'; ?>" class="px-8 py-4 bg-white text-gray-900 font-semibold rounded-full transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                        Start Building Now
                    </a>
                    <a href="#features" class="px-8 py-4 glass text-white font-semibold rounded-full transition-all duration-300 hover:scale-105">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Text Rotate Animation Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const textRotateContainer = document.querySelector('.text-rotate-container');
        const textRotateItems = document.querySelectorAll('.text-rotate-item');
        let currentIndex = 0;
        
        function rotateText() {
            // Hide current item with more dramatic effect
            textRotateItems[currentIndex].style.opacity = '0';
            textRotateItems[currentIndex].style.transform = 'translateY(30px) scale(0.7) rotateX(90deg)';
            textRotateItems[currentIndex].classList.remove('active');
            
            // Move to next item
            currentIndex = (currentIndex + 1) % textRotateItems.length;
            
            // Show next item with bounce effect
            textRotateItems[currentIndex].style.opacity = '1';
            textRotateItems[currentIndex].style.transform = 'translateY(0) scale(1) rotateX(0deg)';
            textRotateItems[currentIndex].classList.add('active');
            
            // Add temporary scale effect
            setTimeout(() => {
                textRotateItems[currentIndex].style.transform = 'translateY(0) scale(1.05) rotateX(0deg)';
                setTimeout(() => {
                    textRotateItems[currentIndex].style.transform = 'translateY(0) scale(1) rotateX(0deg)';
                }, 100);
            }, 50);
        }
        
        // Start rotation with faster interval
        setInterval(rotateText, 1200);
        
        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe all animated elements
        document.querySelectorAll('.animate-fadeInUp, .animate-fadeInLeft, .animate-fadeInRight').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            observer.observe(el);
        });
        
        // Force hide scrollbar during loading
        document.body.style.overflow = 'hidden';
        
        // Show scrollbar after loading screen is hidden
        setTimeout(() => {
            document.body.style.overflow = 'auto';
        }, 3000); // Adjust timing based on your loading screen duration
    });
    </script>
</body>
</html>