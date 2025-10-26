<?php
require_once __DIR__ . '/lib/language.php';

// Initialize language system
LanguageManager::init();
?>
<?php include __DIR__ . '/components/loading-screen.php'; ?>

<!DOCTYPE html>
<html lang="<?php echo LanguageManager::getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JustSite - <?php echo LanguageManager::t('landing_title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Matrix Rain Effect */
        .matrix-rain {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, transparent 0%, rgba(0, 255, 65, 0.03) 50%, transparent 100%);
            overflow: hidden;
        }
        
        .matrix-rain::before {
            content: '';
            position: absolute;
            top: -100%;
            left: 0;
            width: 100%;
            height: 200%;
            background-image: 
                linear-gradient(90deg, transparent 0%, rgba(0, 255, 65, 0.1) 50%, transparent 100%),
                repeating-linear-gradient(0deg, transparent 0px, rgba(0, 255, 65, 0.05) 1px, transparent 2px);
            animation: matrixRain 20s linear infinite;
        }
        
        @keyframes matrixRain {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }
        
        /* Code typing animation */
        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }
        
        @keyframes blink {
            0%, 50% { border-color: transparent; }
            51%, 100% { border-color: #00ff41; }
        }
        
        .typing-effect {
            overflow: hidden;
            border-right: 2px solid #00ff41;
            white-space: nowrap;
            animation: typing 3s steps(40, end), blink 1s step-end infinite;
        }
        
        /* Glitch effect */
        @keyframes glitch {
            0% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
            100% { transform: translate(0); }
        }
        
        .glitch:hover {
            animation: glitch 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 bg-black/80 backdrop-blur-md border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-white font-mono glitch">JustSite</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="login.php" class="text-gray-300 hover:text-white transition-colors font-mono"><?php echo LanguageManager::t('landing_nav_login'); ?></a>
                    <a href="register.php" class="px-4 py-2 bg-gradient-to-r from-green-500 to-blue-600 text-white rounded-lg hover:from-green-600 hover:to-blue-700 transition-all font-mono"><?php echo LanguageManager::t('landing_nav_register'); ?></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="min-h-screen bg-black relative overflow-hidden flex items-center pt-16">
        <!-- Animated Background -->
        <div class="absolute inset-0">
            <!-- Matrix Rain Effect -->
            <div class="matrix-rain"></div>
            
            <!-- Floating Code Elements -->
            <div class="absolute top-20 left-10 opacity-20 animate-pulse">
                <pre class="text-green-400 text-sm font-mono">
function createSite() {
  return magic();
}</pre>
            </div>
            <div class="absolute top-40 right-20 opacity-20 animate-pulse" style="animation-delay: 1s;">
                <pre class="text-blue-400 text-sm font-mono">
&lt;div className="awesome"&gt;
  &lt;Website /&gt;
&lt;/div&gt;</pre>
            </div>
            <div class="absolute bottom-40 left-20 opacity-20 animate-pulse" style="animation-delay: 2s;">
                <pre class="text-purple-400 text-sm font-mono">
const site = new Builder()
  .drag()
  .drop()
  .publish();</pre>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <div class="mb-6">
                        <span class="inline-block px-4 py-2 bg-green-500/20 text-green-400 rounded-full text-sm font-mono border border-green-500/30">
                            &gt; npm install justsite
                        </span>
                    </div>
                    
                    <h1 class="text-5xl lg:text-7xl font-black text-white mb-6 leading-tight">
                        <?php echo LanguageManager::t('landing_hero_title_1'); ?>
                        <span class="block bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 bg-clip-text text-transparent">
                            <?php echo LanguageManager::t('landing_hero_title_2'); ?>
                        </span>
                        <span class="block text-gray-300"><?php echo LanguageManager::t('landing_hero_title_3'); ?></span>
                    </h1>
                    
                    <p class="text-xl text-gray-300 mb-8 leading-relaxed font-mono">
                        <span class="text-green-400">//</span> <?php echo LanguageManager::t('landing_hero_comment_1'); ?><br>
                        <span class="text-green-400">//</span> <?php echo LanguageManager::t('landing_hero_comment_2'); ?>
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="register.php" class="group px-8 py-4 bg-gradient-to-r from-green-500 to-blue-600 text-white rounded-lg font-semibold hover:from-green-600 hover:to-blue-700 transition-all transform hover:scale-105 shadow-xl flex items-center gap-2">
                            <span><?php echo LanguageManager::t('landing_button_start'); ?></span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#features" class="px-8 py-4 border-2 border-gray-600 text-gray-300 rounded-lg font-semibold hover:bg-gray-800 hover:border-gray-500 transition-all font-mono">
                            <?php echo LanguageManager::t('landing_button_demo'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="relative">
                    <!-- Code Editor Mockup -->
                    <div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-2xl">
                        <!-- Editor Header -->
                        <div class="bg-gray-800 px-4 py-3 flex items-center gap-2 border-b border-gray-700">
                            <div class="flex gap-2">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            </div>
                            <div class="flex-1 text-center">
                                <span class="text-gray-400 text-sm font-mono">website.jsx</span>
                            </div>
                        </div>
                        
                        <!-- Code Content -->
                        <div class="p-6 font-mono text-sm">
                            <div class="space-y-2">
                                <div class="flex">
                                    <span class="text-gray-500 w-8">1</span>
                                    <span class="text-purple-400">import</span>
                                    <span class="text-white"> { </span>
                                    <span class="text-blue-400">DragDrop</span>
                                    <span class="text-white"> } </span>
                                    <span class="text-purple-400">from</span>
                                    <span class="text-green-400"> 'justsite'</span>
                                </div>
                                <div class="flex">
                                    <span class="text-gray-500 w-8">2</span>
                                    <span class="text-white"></span>
                                </div>
                                <div class="flex">
                                    <span class="text-gray-500 w-8">3</span>
                                    <span class="text-purple-400">function</span>
                                    <span class="text-yellow-400"> Website</span>
                                    <span class="text-white">() {</span>
                                </div>
                                <div class="flex">
                                    <span class="text-gray-500 w-8">4</span>
                                    <span class="text-white">  </span>
                                    <span class="text-purple-400">return</span>
                                    <span class="text-white"> (</span>
                                </div>
                                <div class="flex">
                                    <span class="text-gray-500 w-8">5</span>
                                    <span class="text-white">    &lt;</span>
                                    <span class="text-red-400">div</span>
                                    <span class="text-blue-400"> className</span>
                                    <span class="text-white">=</span>
                                    <span class="text-green-400">"awesome-site"</span>
                                    <span class="text-white">&gt;</span>
                                </div>
                                <div class="flex">
                                    <span class="text-gray-500 w-8">6</span>
                                    <span class="text-white">      &lt;</span>
                                    <span class="text-red-400">Header</span>
                                    <span class="text-white"> /&gt;</span>
                                </div>
                                <div class="flex">
                                    <span class="text-gray-500 w-8">7</span>
                                    <span class="text-white">      &lt;</span>
                                    <span class="text-red-400">Content</span>
                                    <span class="text-white"> /&gt;</span>
                                </div>
                                <div class="flex">
                                    <span class="text-gray-500 w-8">8</span>
                                    <span class="text-white">    &lt;/</span>
                                    <span class="text-red-400">div</span>
                                    <span class="text-white">&gt;</span>
                                </div>
                                <div class="flex">
                                    <span class="text-gray-500 w-8">9</span>
                                    <span class="text-white">  )</span>
                                </div>
                                <div class="flex">
                                    <span class="text-gray-500 w-8">10</span>
                                    <span class="text-white">}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating Elements -->
                    <div class="absolute -top-4 -right-4 bg-green-500 text-black px-3 py-1 rounded-full text-sm font-bold animate-bounce">
                        <?php echo LanguageManager::t('landing_badge_live'); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4"><?php echo LanguageManager::t('landing_features_heading'); ?></h2>
                <p class="text-xl text-gray-600"><?php echo LanguageManager::t('landing_features_subheading'); ?></p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group p-8 rounded-2xl border border-gray-200 hover:border-blue-300 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-blue-200 transition-colors">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3"><?php echo LanguageManager::t('landing_feature_drag_title'); ?></h3>
                    <p class="text-gray-600"><?php echo LanguageManager::t('landing_feature_drag_desc'); ?></p>
                </div>

                <!-- Feature 2 -->
                <div class="group p-8 rounded-2xl border border-gray-200 hover:border-purple-300 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-purple-200 transition-colors">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3"><?php echo LanguageManager::t('landing_feature_responsive_title'); ?></h3>
                    <p class="text-gray-600"><?php echo LanguageManager::t('landing_feature_responsive_desc'); ?></p>
                </div>

                <!-- Feature 3 -->
                <div class="group p-8 rounded-2xl border border-gray-200 hover:border-green-300 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-green-200 transition-colors">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3"><?php echo LanguageManager::t('landing_feature_publish_title'); ?></h3>
                    <p class="text-gray-600"><?php echo LanguageManager::t('landing_feature_publish_desc'); ?></p>
                </div>

                <!-- Feature 4 -->
                <div class="group p-8 rounded-2xl border border-gray-200 hover:border-orange-300 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-orange-200 transition-colors">
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3"><?php echo LanguageManager::t('landing_feature_blocks_title'); ?></h3>
                    <p class="text-gray-600"><?php echo LanguageManager::t('landing_feature_blocks_desc'); ?></p>
                </div>

                <!-- Feature 5 -->
                <div class="group p-8 rounded-2xl border border-gray-200 hover:border-pink-300 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-pink-200 transition-colors">
                        <svg class="w-6 h-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3"><?php echo LanguageManager::t('landing_feature_ai_title'); ?></h3>
                    <p class="text-gray-600"><?php echo LanguageManager::t('landing_feature_ai_desc'); ?></p>
                </div>

                <!-- Feature 6 -->
                <div class="group p-8 rounded-2xl border border-gray-200 hover:border-indigo-300 hover:shadow-xl transition-all duration-300">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-indigo-200 transition-colors">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3"><?php echo LanguageManager::t('landing_feature_analytics_title'); ?></h3>
                    <p class="text-gray-600"><?php echo LanguageManager::t('landing_feature_analytics_desc'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gray-900">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-bold text-white mb-6"><?php echo LanguageManager::t('landing_cta_heading'); ?></h2>
            <p class="text-xl text-gray-300 mb-8"><?php echo LanguageManager::t('landing_cta_subheading'); ?></p>
            <a href="register.php" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 shadow-xl">
                <?php echo LanguageManager::t('landing_cta_button'); ?>
                <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center gap-3 mb-4 md:mb-0">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">JustSite</span>
                </div>
                <p class="text-gray-600"><?php echo LanguageManager::t('landing_footer_rights'); ?></p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
