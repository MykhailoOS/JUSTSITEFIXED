<?php
/**
 * Hero Designali Component
 * Adapted from React component with animated canvas lines
 */

function renderHeroDesignali($props = []) {
    $defaultProps = [
        'title' => 'Your complete platform for the Design.',
        'subtitle' => 'Welcome to my creative playground!',
        'description' => 'I craft enchanting visuals for brands, and conjure design resources to empower others.',
        'talkAbout' => ['Graphic Design', 'Branding', 'Web Design', 'Web Develop', 'Marketing', 'UI UX', 'Social Media'],
        'primaryButton' => [
            'text' => 'Start Posting',
            'href' => '/graphic'
        ],
        'secondaryButton' => [
            'text' => 'Book a call',
            'href' => 'https://cal.com/aliimam/designali'
        ]
    ];
    
    $props = array_merge($defaultProps, $props);
    
    return '
    <main class="overflow-hidden">
        <section id="home">
            <!-- Grid Background -->
            <div class="absolute inset-0 max-md:hidden top-[400px] -z-10 h-[400px] w-full bg-transparent bg-[linear-gradient(to_right,#57534e_1px,transparent_1px),linear-gradient(to_bottom,#57534e_1px,transparent_1px)] bg-[size:3rem_3rem] opacity-20 [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)] dark:bg-[linear-gradient(to_right,#a8a29e_1px,transparent_1px),linear-gradient(to_bottom,#a8a29e_1px,transparent_1px)]"></div>
            
            <div class="flex flex-col items-center justify-center px-6 text-center">
                <!-- Badge -->
                <div class="mb-6 mt-10 sm:justify-center md:mb-4 md:mt-40">
                    <div class="relative flex items-center rounded-full border bg-popover px-3 py-1 text-xs text-primary/60">
                        Introducing JustSite.
                        <a href="/products/justsite" rel="noreferrer" class="ml-1 flex items-center font-semibold">
                            <div class="absolute inset-0 hover:font-semibold hover:text-ali flex" aria-hidden="true"></div>
                            Explore <span aria-hidden="true"></span>
                        </a>
                    </div>
                </div>

                <!-- Main Title -->
                <div class="mx-auto max-w-5xl">
                    <div class="border-text-red-500 relative mx-auto h-full bg-background border py-12 p-6 [mask-image:radial-gradient(800rem_96rem_at_center,white,transparent)]">
                        <h1 class="flex flex-col text-center text-5xl font-semibold leading-none tracking-tight md:flex-col md:text-8xl lg:flex-row lg:text-8xl">
                            <!-- Plus Icons -->
                            <svg stroke-width="4" class="text-text-red-500 absolute -left-5 -top-5 h-10 w-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                            </svg>
                            <svg stroke-width="4" class="text-text-red-500 absolute -bottom-5 -left-5 h-10 w-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                            </svg>
                            <svg stroke-width="4" class="text-text-red-500 absolute -right-5 -top-5 h-10 w-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                            </svg>
                            <svg stroke-width="4" class="text-text-red-500 absolute -bottom-5 -right-5 h-10 w-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                            </svg>
                            
                            <span>
                                ' . htmlspecialchars($props['title']) . '
                                <span class="text-red-500">Design.</span>
                            </span>
                        </h1>
                        
                        <!-- Status Indicator -->
                        <div class="flex items-center mt-4 justify-center gap-1">
                            <span class="relative flex h-3 w-3 items-center justify-center">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-500 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                            </span>
                            <p class="text-xs text-green-500">Available Now</p>
                        </div>
                    </div>

                    <!-- Subtitle -->
                    <h1 class="mt-8 text-2xl md:text-2xl">
                        ' . htmlspecialchars($props['subtitle']) . '
                        <span class="text-red-500 font-bold">JustSite </span>
                    </h1>

                    <!-- Description with TypeWriter -->
                    <p class="text-primary/60 py-4">
                        ' . htmlspecialchars($props['description']) . '
                        <span class="text-blue-500 font-semibold">
                            <span id="typewriter-text">' . implode(', ', $props['talkAbout']) . '</span>
                        </span>.
                    </p>
                    
                    <!-- Buttons -->
                    <div class="flex items-center justify-center gap-2">
                        <a href="' . $props['primaryButton']['href'] . '">
                            ' . renderShineBorder([
                                'borderWidth' => 3,
                                'className' => 'border cursor-pointer h-auto w-auto p-2 bg-white/5 backdrop-blur-md dark:bg-black/5',
                                'color' => ['#FF007F', '#39FF14', '#00FFFF'],
                                'children' => '
                                    <button class="w-full rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2">
                                        ' . htmlspecialchars($props['primaryButton']['text']) . '
                                    </button>
                                '
                            ]) . '
                        </a>
                        <a href="' . $props['secondaryButton']['href'] . '" target="_blank">
                            <button class="rounded-xl border border-input bg-background hover:bg-accent hover:text-accent-foreground inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2">
                                ' . htmlspecialchars($props['secondaryButton']['text']) . '
                            </button>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Animated Canvas -->
            <canvas class="pointer-events-none absolute inset-0 mx-auto" id="canvas"></canvas>
        </section>
        
        <!-- Background Image -->
        <img width="1512" height="550" class="absolute left-1/2 top-0 -z-10 -translate-x-1/2" src="https://images.unsplash.com/photo-1557683316-973673baf926?w=1512&h=550&fit=crop&auto=format" alt="" role="presentation" />
    </main>';
}

function renderShineBorder($props = []) {
    $defaultProps = [
        'borderRadius' => 8,
        'borderWidth' => 1,
        'duration' => 14,
        'color' => '#000000',
        'className' => '',
        'children' => ''
    ];
    
    $props = array_merge($defaultProps, $props);
    
    $colors = is_array($props['color']) ? implode(',', $props['color']) : $props['color'];
    
    return '
    <div style="--border-radius: ' . $props['borderRadius'] . 'px;" class="relative grid h-full w-full place-items-center rounded-3xl bg-white p-3 text-black dark:bg-black dark:text-white ' . $props['className'] . '">
        <div style="
            --border-width: ' . $props['borderWidth'] . 'px;
            --border-radius: ' . $props['borderRadius'] . 'px;
            --shine-pulse-duration: ' . $props['duration'] . 's;
            --mask-linear-gradient: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            --background-radial-gradient: radial-gradient(transparent,transparent, ' . $colors . ',transparent,transparent);
        " class="before:bg-shine-size before:absolute before:inset-0 before:aspect-square before:size-full before:rounded-3xl before:p-[--border-width] before:will-change-[background-position] before:content-[\'\'] before:![-webkit-mask-composite:xor] before:[background-image:--background-radial-gradient] before:[background-size:300%_300%] before:![mask-composite:exclude] before:[mask:--mask-linear-gradient] motion-safe:before:animate-[shine-pulse_var(--shine-pulse-duration)_infinite_linear]">
        </div>
        ' . $props['children'] . '
    </div>';
}

function renderCanvasScript() {
    return '
    <script>
    // Animated Canvas Lines Script
    var ctx, f, e = 0, pos = {}, lines = [], E = {
        debug: true,
        friction: 0.5,
        trails: 80,
        size: 50,
        dampening: 0.025,
        tension: 0.99,
    };
    
    function Node() {
        this.x = 0;
        this.y = 0;
        this.vy = 0;
        this.vx = 0;
    }
    
    function n(e) {
        this.init(e || {});
    }
    n.prototype = {
        init: function (e) {
            this.phase = e.phase || 0;
            this.offset = e.offset || 0;
            this.frequency = e.frequency || 0.001;
            this.amplitude = e.amplitude || 1;
        },
        update: function () {
            this.phase += this.frequency;
            e = this.offset + Math.sin(this.phase) * this.amplitude;
        },
        value: function () {
            return e;
        },
    };
    
    function Line(e) {
        this.init(e || {});
    }
    Line.prototype = {
        init: function (e) {
            this.spring = e.spring + 0.1 * Math.random() - 0.05;
            this.friction = E.friction + 0.01 * Math.random() - 0.005;
            this.nodes = [];
            for (var t, n = 0; n < E.size; n++) {
                t = new Node();
                t.x = pos.x;
                t.y = pos.y;
                this.nodes.push(t);
            }
        },
        update: function () {
            let e = this.spring, t = this.nodes[0];
            t.vx += (pos.x - t.x) * e;
            t.vy += (pos.y - t.y) * e;
            for (var n, i = 0, a = this.nodes.length; i < a; i++) {
                t = this.nodes[i];
                if (0 < i) {
                    n = this.nodes[i - 1];
                    t.vx += (n.x - t.x) * e;
                    t.vy += (n.y - t.y) * e;
                    t.vx += n.vx * E.dampening;
                    t.vy += n.vy * E.dampening;
                }
                t.vx *= this.friction;
                t.vy *= this.friction;
                t.x += t.vx;
                t.y += t.vy;
                e *= E.tension;
            }
        },
        draw: function () {
            let e, t, n = this.nodes[0].x, i = this.nodes[0].y;
            ctx.beginPath();
            ctx.moveTo(n, i);
            for (var a = 1, o = this.nodes.length - 2; a < o; a++) {
                e = this.nodes[a];
                t = this.nodes[a + 1];
                n = 0.5 * (e.x + t.x);
                i = 0.5 * (e.y + t.y);
                ctx.quadraticCurveTo(e.x, e.y, n, i);
            }
            e = this.nodes[a];
            t = this.nodes[a + 1];
            ctx.quadraticCurveTo(e.x, e.y, t.x, t.y);
            ctx.stroke();
            ctx.closePath();
        },
    };
    
    function onMousemove(e) {
        function o() {
            lines = [];
            for (let e = 0; e < E.trails; e++)
                lines.push(new Line({ spring: 0.45 + (e / E.trails) * 0.025 }));
        }
        function c(e) {
            if (e.touches) {
                pos.x = e.touches[0].pageX;
                pos.y = e.touches[0].pageY;
            } else {
                pos.x = e.clientX;
                pos.y = e.clientY;
            }
            e.preventDefault();
        }
        function l(e) {
            if (1 == e.touches.length) {
                pos.x = e.touches[0].pageX;
                pos.y = e.touches[0].pageY;
            }
        }
        document.removeEventListener("mousemove", onMousemove);
        document.removeEventListener("touchstart", onMousemove);
        document.addEventListener("mousemove", c);
        document.addEventListener("touchmove", c);
        document.addEventListener("touchstart", l);
        c(e);
        o();
        render();
    }
    
    function render() {
        if (ctx.running) {
            ctx.globalCompositeOperation = "source-over";
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            ctx.globalCompositeOperation = "lighter";
            ctx.strokeStyle = "hsla(" + Math.round(f.update()) + ",100%,50%,0.025)";
            ctx.lineWidth = 10;
            for (var e, t = 0; t < E.trails; t++) {
                e = lines[t];
                e.update();
                e.draw();
            }
            ctx.frame++;
            window.requestAnimationFrame(render);
        }
    }
    
    function resizeCanvas() {
        ctx.canvas.width = window.innerWidth - 20;
        ctx.canvas.height = window.innerHeight;
    }
    
    function renderCanvas() {
        ctx = document.getElementById("canvas").getContext("2d");
        ctx.running = true;
        ctx.frame = 1;
        f = new n({
            phase: Math.random() * 2 * Math.PI,
            amplitude: 85,
            frequency: 0.0015,
            offset: 285,
        });
        document.addEventListener("mousemove", onMousemove);
        document.addEventListener("touchstart", onMousemove);
        document.body.addEventListener("orientationchange", resizeCanvas);
        window.addEventListener("resize", resizeCanvas);
        window.addEventListener("focus", () => {
            if (!ctx.running) {
                ctx.running = true;
                render();
            }
        });
        window.addEventListener("blur", () => {
            ctx.running = true;
        });
        resizeCanvas();
    }
    
    // Initialize when DOM is ready
    document.addEventListener("DOMContentLoaded", function() {
        renderCanvas();
    });
    </script>';
}
?>
