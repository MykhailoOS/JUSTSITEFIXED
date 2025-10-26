<?php
/**
 * Hero Section 1 Component
 * Adapted from React component
 */

require_once __DIR__ . '/../ui/animated-group.php';

function renderHeroHeader($props = []) {
    $defaultProps = [
        'menuItems' => [
            ['name' => 'Features', 'href' => '#features'],
            ['name' => 'Solution', 'href' => '#solution'],
            ['name' => 'Pricing', 'href' => '#pricing'],
            ['name' => 'About', 'href' => '#about']
        ]
    ];
    
    $props = array_merge($defaultProps, $props);
    
    return '
    <header>
        <nav class="fixed z-20 w-full px-2 group" data-state="false">
            <div class="mx-auto mt-2 max-w-6xl px-6 transition-all duration-300 lg:px-12 scrolled-nav">
                <div class="relative flex flex-wrap items-center justify-between gap-6 py-3 lg:gap-0 lg:py-4">
                    <div class="flex w-full justify-between lg:w-auto">
                        <a href="/" aria-label="home" class="flex items-center space-x-2">
                            ' . renderLogo() . '
                        </a>
                        
                        <button class="mobile-menu-btn relative z-20 -m-2.5 -mr-4 block cursor-pointer p-2.5 lg:hidden" aria-label="Open Menu">
                            <svg class="menu-icon m-auto size-6 duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <svg class="close-icon absolute inset-0 m-auto size-6 -rotate-180 scale-0 opacity-0 duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Desktop Menu -->
                    <div class="absolute inset-0 m-auto hidden size-fit lg:block">
                        <ul class="flex gap-8 text-sm">
                            ' . implode('', array_map(function($item) {
                                return '<li><a href="' . $item['href'] . '" class="text-gray-400 hover:text-white block duration-150"><span>' . $item['name'] . '</span></a></li>';
                            }, $props['menuItems'])) . '
                        </ul>
                    </div>
                    
                    <!-- Mobile Menu -->
                    <div class="mobile-menu bg-white dark:bg-gray-900 group-data-[state=active]:block lg:group-data-[state=active]:flex mb-6 hidden w-full flex-wrap items-center justify-end space-y-8 rounded-3xl border p-6 shadow-2xl shadow-gray-300/20 md:flex-nowrap lg:m-0 lg:flex lg:w-fit lg:gap-6 lg:space-y-0 lg:border-transparent lg:bg-transparent lg:p-0 lg:shadow-none dark:shadow-none dark:lg:bg-transparent">
                        <div class="lg:hidden">
                            <ul class="space-y-6 text-base">
                                ' . implode('', array_map(function($item) {
                                    return '<li><a href="' . $item['href'] . '" class="text-gray-400 hover:text-white block duration-150"><span>' . $item['name'] . '</span></a></li>';
                                }, $props['menuItems'])) . '
                            </ul>
                        </div>
                        <div class="flex w-full flex-col space-y-3 sm:flex-row sm:gap-3 sm:space-y-0 md:w-fit">
                            <a href="login.php" class="login-btn inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">
                                <span>Login</span>
                            </a>
                            <a href="register.php" class="signup-btn inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 h-9 rounded-md px-3">
                                <span>Sign Up</span>
                            </a>
                            <a href="index.php" class="get-started-btn hidden inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 h-9 rounded-md px-3">
                                <span>Get Started</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const mobileMenuBtn = document.querySelector(".mobile-menu-btn");
        const mobileMenu = document.querySelector(".mobile-menu");
        const nav = document.querySelector("nav");
        const scrolledNav = document.querySelector(".scrolled-nav");
        
        // Mobile menu toggle
        mobileMenuBtn.addEventListener("click", function() {
            const isActive = nav.getAttribute("data-state") === "active";
            nav.setAttribute("data-state", isActive ? "false" : "active");
        });
        
        // Scroll effect
        let isScrolled = false;
        window.addEventListener("scroll", function() {
            const shouldBeScrolled = window.scrollY > 50;
            if (shouldBeScrolled !== isScrolled) {
                isScrolled = shouldBeScrolled;
                if (isScrolled) {
                    scrolledNav.classList.add("bg-white/50", "max-w-4xl", "rounded-2xl", "border", "backdrop-blur-lg", "lg:px-5");
                    document.querySelector(".login-btn").classList.add("lg:hidden");
                    document.querySelector(".signup-btn").classList.add("lg:hidden");
                    document.querySelector(".get-started-btn").classList.remove("hidden");
                } else {
                    scrolledNav.classList.remove("bg-white/50", "max-w-4xl", "rounded-2xl", "border", "backdrop-blur-lg", "lg:px-5");
                    document.querySelector(".login-btn").classList.remove("lg:hidden");
                    document.querySelector(".signup-btn").classList.remove("lg:hidden");
                    document.querySelector(".get-started-btn").classList.add("hidden");
                }
            }
        });
    });
    </script>';
}

function renderLogo() {
    return '
    <svg viewBox="0 0 78 18" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-auto">
        <path d="M3 0H5V18H3V0ZM13 0H15V18H13V0ZM18 3V5H0V3H18ZM0 15V13H18V15H0Z" fill="url(#logo-gradient)" />
        <path d="M27.06 7.054V12.239C27.06 12.5903 27.1393 12.8453 27.298 13.004C27.468 13.1513 27.7513 13.225 28.148 13.225H29.338V14.84H27.808C26.9353 14.84 26.2667 14.636 25.802 14.228C25.3373 13.82 25.105 13.157 25.105 12.239V7.054H24V5.473H25.105V3.144H27.06V5.473H29.338V7.054H27.06ZM30.4782 10.114C30.4782 9.17333 30.6709 8.34033 31.0562 7.615C31.4529 6.88967 31.9855 6.32867 32.6542 5.932C33.3342 5.524 34.0822 5.32 34.8982 5.32C35.6349 5.32 36.2752 5.46733 36.8192 5.762C37.3745 6.04533 37.8165 6.40233 38.1452 6.833V5.473H40.1002V14.84H38.1452V13.446C37.8165 13.888 37.3689 14.2563 36.8022 14.551C36.2355 14.8457 35.5895 14.993 34.8642 14.993C34.0595 14.993 33.3229 14.789 32.6542 14.381C31.9855 13.9617 31.4529 13.3837 31.0562 12.647C30.6709 11.899 30.4782 11.0547 30.4782 10.114ZM38.1452 10.148C38.1452 9.502 38.0092 8.941 37.7372 8.465C37.4765 7.989 37.1309 7.62633 36.7002 7.377C36.2695 7.12767 35.8049 7.003 35.3062 7.003C34.8075 7.003 34.3429 7.12767 33.9122 7.377C33.4815 7.615 33.1302 7.972 32.8582 8.448C32.5975 8.91267 32.4672 9.468 32.4672 10.114C32.4672 10.76 32.5975 11.3267 32.8582 11.814C33.1302 12.3013 33.4815 12.6753 33.9122 12.936C34.3542 13.1853 34.8189 13.31 35.3062 13.31C35.8049 13.31 36.2695 13.1853 36.7002 12.936C37.1309 12.6867 37.4765 12.324 37.7372 11.848C38.0092 11.3607 38.1452 10.794 38.1452 10.148ZM43.6317 4.232C43.2803 4.232 42.9857 4.113 42.7477 3.875C42.5097 3.637 42.3907 3.34233 42.3907 2.991C42.3907 2.63967 42.5097 2.345 42.7477 2.107C42.9857 1.869 43.2803 1.75 43.6317 1.75C43.9717 1.75 44.2607 1.869 44.4987 2.107C44.7367 2.345 44.8557 2.63967 44.8557 2.991C44.8557 3.34233 44.7367 3.637 44.4987 3.875C44.2607 4.113 43.9717 4.232 43.6317 4.232ZM44.5837 5.473V14.84H42.6457V5.473H44.5837ZM49.0661 2.26V14.84H47.1281V2.26H49.0661ZM50.9645 10.114C50.9645 9.17333 51.1572 8.34033 51.5425 7.615C51.9392 6.88967 52.4719 6.32867 53.1405 5.932C53.8205 5.524 54.5685 5.32 55.3845 5.32C56.1212 5.32 56.7615 5.46733 57.3055 5.762C57.8609 6.04533 58.3029 6.40233 58.6315 6.833V5.473H60.5865V14.84H58.6315V13.446C58.3029 13.888 57.8552 14.2563 57.2885 14.551C56.7219 14.8457 56.0759 14.993 55.3505 14.993C54.5459 14.993 53.8092 14.789 53.1405 14.381C52.4719 13.9617 51.9392 13.3837 51.5425 12.647C51.1572 11.899 50.9645 11.0547 50.9645 10.114ZM58.6315 10.148C58.6315 9.502 58.4955 8.941 58.2235 8.465C57.9629 7.989 57.6172 7.62633 57.1865 7.377C56.7559 7.12767 56.2912 7.003 55.7925 7.003C55.2939 7.003 54.8292 7.12767 54.3985 7.377C53.9679 7.615 53.6165 7.972 53.3445 8.448C53.0839 8.91267 52.9535 9.468 52.9535 10.114C52.9535 10.76 53.0839 11.3267 53.3445 11.814C53.6165 12.3013 53.9679 12.6753 54.3985 12.936C54.8405 13.1853 55.3052 13.31 55.7925 13.31C56.2912 13.31 56.7559 13.1853 57.1865 12.936C57.6172 12.6867 57.9629 12.324 58.2235 11.848C58.4955 11.3607 58.6315 10.794 58.6315 10.148ZM65.07 6.833C65.3533 6.357 65.7273 5.98867 66.192 5.728C66.668 5.456 67.229 5.32 67.875 5.32V7.326H67.382C66.6227 7.326 66.0447 7.51867 65.648 7.904C65.2627 8.28933 65.07 8.958 65.07 9.91V14.84H63.132V5.473H65.07V6.833ZM73.3624 10.165L77.6804 14.84H75.0624L71.5944 10.811V14.84H69.6564V2.26H71.5944V9.57L74.9944 5.473H77.6804L73.3624 10.165Z" fill="currentColor" />
        <defs>
            <linearGradient id="logo-gradient" x1="10" y1="0" x2="10" y2="20" gradientUnits="userSpaceOnUse">
                <stop stop-color="#9B99FE" />
                <stop offset="1" stop-color="#2BC8B7" />
            </linearGradient>
        </defs>
    </svg>';
}

function renderHeroSection($props = []) {
    $defaultProps = [
        'badgeText' => 'Introducing Support for AI Models',
        'badgeHref' => '#link',
        'title' => 'Modern Solutions for Customer Engagement',
        'description' => 'Highly customizable components for building modern websites and applications that look and feel the way you mean it.',
        'button1Text' => 'Start Building',
        'button1Href' => 'index.php',
        'button2Text' => 'Request a demo',
        'button2Href' => '#demo',
        'imageLight' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=2700&h=1440&fit=crop',
        'imageDark' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=2700&h=1440&fit=crop',
        'customers' => [
            ['name' => 'Nvidia', 'logo' => 'https://html.tailus.io/blocks/customers/nvidia.svg'],
            ['name' => 'Column', 'logo' => 'https://html.tailus.io/blocks/customers/column.svg'],
            ['name' => 'GitHub', 'logo' => 'https://html.tailus.io/blocks/customers/github.svg'],
            ['name' => 'Nike', 'logo' => 'https://html.tailus.io/blocks/customers/nike.svg'],
            ['name' => 'Lemon Squeezy', 'logo' => 'https://html.tailus.io/blocks/customers/lemonsqueezy.svg'],
            ['name' => 'Laravel', 'logo' => 'https://html.tailus.io/blocks/customers/laravel.svg'],
            ['name' => 'Lilly', 'logo' => 'https://html.tailus.io/blocks/customers/lilly.svg'],
            ['name' => 'OpenAI', 'logo' => 'https://html.tailus.io/blocks/customers/openai.svg']
        ]
    ];
    
    $props = array_merge($defaultProps, $props);
    
    return '
    <main class="overflow-hidden">
        <!-- Background decorations -->
        <div aria-hidden class="z-[2] absolute inset-0 pointer-events-none isolate opacity-50 contain-strict hidden lg:block">
            <div class="w-[35rem] h-[80rem] -translate-y-[350px] absolute left-0 top-0 -rotate-45 rounded-full bg-[radial-gradient(68.54%_68.72%_at_55.02%_31.46%,hsla(0,0%,85%,.08)_0,hsla(0,0%,55%,.02)_50%,hsla(0,0%,45%,0)_80%)]"></div>
            <div class="h-[80rem] absolute left-0 top-0 w-56 -rotate-45 rounded-full bg-[radial-gradient(50%_50%_at_50%_50%,hsla(0,0%,85%,.06)_0,hsla(0,0%,45%,.02)_80%,transparent_100%)] [translate:5%_-50%]"></div>
            <div class="h-[80rem] -translate-y-[350px] absolute left-0 top-0 w-56 -rotate-45 bg-[radial-gradient(50%_50%_at_50%_50%,hsla(0,0%,85%,.04)_0,hsla(0,0%,45%,.02)_80%,transparent_100%)]"></div>
        </div>
        
        <section>
            <div class="relative pt-24 md:pt-36">
                <!-- Background image -->
                ' . renderAnimatedGroup([
                    'preset' => 'fade',
                    'className' => 'absolute inset-0 -z-20',
                    'children' => '
                        <img src="' . $props['imageDark'] . '" alt="background" class="absolute inset-x-0 top-56 -z-20 hidden lg:top-32 dark:block" width="3276" height="4095" />
                    '
                ]) . '
                
                <div aria-hidden class="absolute inset-0 -z-10 size-full [background:radial-gradient(125%_125%_at_50%_100%,transparent_0%,var(--background)_75%)]"></div>
                
                <div class="mx-auto max-w-7xl px-6">
                    <div class="text-center sm:mx-auto lg:mr-auto lg:mt-0">
                        ' . renderAnimatedGroup([
                            'preset' => 'blur-slide',
                            'children' => '
                                <!-- Badge -->
                                <a href="' . $props['badgeHref'] . '" class="hover:bg-background dark:hover:border-t-border bg-muted group mx-auto flex w-fit items-center gap-4 rounded-full border p-1 pl-4 shadow-md shadow-black/5 transition-all duration-300 dark:border-t-white/5 dark:shadow-zinc-950">
                                    <span class="text-foreground text-sm">' . htmlspecialchars($props['badgeText']) . '</span>
                                    <span class="dark:border-background block h-4 w-0.5 border-l bg-white dark:bg-zinc-700"></span>
                                    <div class="bg-background group-hover:bg-muted size-6 overflow-hidden rounded-full duration-500">
                                        <div class="flex w-12 -translate-x-1/2 duration-500 ease-in-out group-hover:translate-x-0">
                                            <span class="flex size-6">
                                                <svg class="m-auto size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </span>
                                            <span class="flex size-6">
                                                <svg class="m-auto size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                                
                                <!-- Title -->
                                <h1 class="mt-8 max-w-4xl mx-auto text-balance text-6xl md:text-7xl lg:mt-16 xl:text-[5.25rem]">
                                    ' . htmlspecialchars($props['title']) . '
                                </h1>
                                
                                <!-- Description -->
                                <p class="mx-auto mt-8 max-w-2xl text-balance text-lg">
                                    ' . htmlspecialchars($props['description']) . '
                                </p>
                            '
                        ]) . '
                        
                        <!-- Buttons -->
                        ' . renderAnimatedGroup([
                            'preset' => 'slide',
                            'className' => 'mt-12 flex flex-col items-center justify-center gap-2 md:flex-row',
                            'children' => '
                                <div class="bg-foreground/10 rounded-[14px] border p-0.5">
                                    <a href="' . $props['button1Href'] . '" class="bg-blue-600 text-white hover:bg-blue-700 inline-flex items-center justify-center whitespace-nowrap rounded-xl px-5 text-base h-11 rounded-xl px-5">
                                        <span class="text-nowrap">' . htmlspecialchars($props['button1Text']) . '</span>
                                    </a>
                                </div>
                                <a href="' . $props['button2Href'] . '" class="hover:bg-accent hover:text-accent-foreground h-10.5 rounded-xl px-5 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                                    <span class="text-nowrap">' . htmlspecialchars($props['button2Text']) . '</span>
                                </a>
                            '
                        ]) . '
                    </div>
                </div>
                
                <!-- Hero Image -->
                ' . renderAnimatedGroup([
                    'preset' => 'scale',
                    'children' => '
                        <div class="relative -mr-56 mt-8 overflow-hidden px-2 sm:mr-0 sm:mt-12 md:mt-20">
                            <div aria-hidden class="bg-gradient-to-b to-background absolute inset-0 z-10 from-transparent from-35%"></div>
                            <div class="inset-shadow-2xs ring-background dark:inset-shadow-white/20 bg-background relative mx-auto max-w-6xl overflow-hidden rounded-2xl border p-4 shadow-lg shadow-zinc-950/15 ring-1">
                                <img class="bg-background aspect-15/8 relative hidden rounded-2xl dark:block" src="' . $props['imageDark'] . '" alt="app screen" width="2700" height="1440" />
                                <img class="z-2 border-border/25 aspect-15/8 relative rounded-2xl border dark:hidden" src="' . $props['imageLight'] . '" alt="app screen" width="2700" height="1440" />
                            </div>
                        </div>
                    '
                ]) . '
            </div>
        </section>
        
        <!-- Customers Section -->
        <section class="bg-background pb-16 pt-16 md:pb-32">
            <div class="group relative m-auto max-w-5xl px-6">
                <div class="absolute inset-0 z-10 flex scale-95 items-center justify-center opacity-0 duration-500 group-hover:scale-100 group-hover:opacity-100">
                    <a href="/" class="block text-sm duration-150 hover:opacity-75">
                        <span>Meet Our Customers</span>
                        <svg class="ml-1 inline-block size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="group-hover:blur-xs mx-auto mt-12 grid max-w-2xl grid-cols-4 gap-x-12 gap-y-8 transition-all duration-500 group-hover:opacity-50 sm:gap-x-16 sm:gap-y-14">
                    ' . implode('', array_map(function($customer) {
                        return '
                        <div class="flex">
                            <img class="mx-auto h-5 w-fit dark:invert" src="' . $customer['logo'] . '" alt="' . $customer['name'] . ' Logo" height="20" width="auto" />
                        </div>';
                    }, $props['customers'])) . '
                </div>
            </div>
        </section>
    </main>';
}
?>
