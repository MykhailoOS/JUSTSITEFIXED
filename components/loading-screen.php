<?php
/**
 * Loading Screen Component
 * Анимированная заставка-загрузка для JustSite
 */

// Определяем текст заставки в зависимости от страницы
$loadingText = 'JustSite - make it simple.';
$loadingSubtext = '';
$currentPage = basename($_SERVER['PHP_SELF']);

switch ($currentPage) {
    case 'index.php':
        $loadingText = 'JustSite - make it simple.';
        $loadingSubtext = 'Building your website...';
        break;
    case 'dashboard.php':
        $loadingText = 'Welcome to dashboard';
        $loadingSubtext = 'Loading your projects...';
        break;
    case 'admin.php':
        $loadingText = 'God Mode On';
        $loadingSubtext = 'Accessing admin panel...';
        break;
    case 'profile.php':
        $loadingText = 'JustSite - make it simple.';
        $loadingSubtext = 'Loading your profile...';
        break;
    case 'landing.php':
        $loadingText = 'JustSite - make it simple.';
        $loadingSubtext = 'Welcome to JustSite...';
        break;
    default:
        $loadingText = 'JustSite - make it simple.';
        $loadingSubtext = 'Loading...';
        break;
}
?>
<link rel="stylesheet" href="components/loading-screen.css">

<div id="loading-screen">
    <div id="loading-text"><?php echo htmlspecialchars($loadingText); ?></div>
    <?php if (!empty($loadingSubtext)): ?>
        <div id="loading-subtext"><?php echo htmlspecialchars($loadingSubtext); ?></div>
    <?php endif; ?>
    <div class="loading-dots"></div>
    <div id="loading-progress">
        <div id="loading-progress-bar"></div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
class LoadingScreen {
    constructor() {
        this.container = document.getElementById('loading-screen');
        this.progressBar = document.getElementById('loading-progress-bar');
        this.scene = null;
        this.renderer = null;
        this.camera = null;
        this.mesh = null;
        this.uniforms = null;
        this.animationId = null;
        this.progress = 0;
        
        // Add loading class to body to hide header
        document.body.classList.add('loading');
        
        // Initialize audio system
        this.audioContext = null;
        this.audioBuffer = null;
        this.audioSource = null;
        this.initAudio();
        
        this.init();
    }

    init() {
        this.setupThreeJS();
        this.animate();
        this.simulateLoading();
    }

    initAudio() {
        try {
            // Create audio context
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            
            // Generate a cool sweep sound
            this.generateSweepSound();
        } catch (error) {
            console.log('Audio not supported:', error);
        }
    }

    generateSweepSound() {
        if (!this.audioContext) return;
        
        const sampleRate = this.audioContext.sampleRate;
        const duration = 2.5; // Match loading duration
        const length = sampleRate * duration;
        const buffer = this.audioContext.createBuffer(1, length, sampleRate);
        const data = buffer.getChannelData(0);
        
        // Create a frequency sweep from low to high
        for (let i = 0; i < length; i++) {
            const t = i / sampleRate;
            const progress = t / duration;
            
            // Frequency sweep from 200Hz to 2000Hz
            const frequency = 200 + (1800 * progress);
            
            // Amplitude envelope (fade in/out)
            const envelope = Math.sin(progress * Math.PI);
            
            // Add some harmonics for richness
            const fundamental = Math.sin(2 * Math.PI * frequency * t);
            const harmonic2 = Math.sin(2 * Math.PI * frequency * 2 * t) * 0.3;
            const harmonic3 = Math.sin(2 * Math.PI * frequency * 3 * t) * 0.1;
            
            // Add some noise for texture
            const noise = (Math.random() - 0.5) * 0.1;
            
            data[i] = (fundamental + harmonic2 + harmonic3 + noise) * envelope * 0.3;
        }
        
        this.audioBuffer = buffer;
    }

    playSweepSound() {
        if (!this.audioContext || !this.audioBuffer) return;
        
        try {
            // Resume audio context if suspended
            if (this.audioContext.state === 'suspended') {
                this.audioContext.resume();
            }
            
            // Create and play the sound
            this.audioSource = this.audioContext.createBufferSource();
            this.audioSource.buffer = this.audioBuffer;
            
            // Add some effects
            const gainNode = this.audioContext.createGain();
            const filterNode = this.audioContext.createBiquadFilter();
            
            // Configure filter for sweep effect
            filterNode.type = 'lowpass';
            filterNode.frequency.setValueAtTime(200, this.audioContext.currentTime);
            filterNode.frequency.linearRampToValueAtTime(2000, this.audioContext.currentTime + 2.5);
            
            // Configure gain
            gainNode.gain.setValueAtTime(0, this.audioContext.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.5, this.audioContext.currentTime + 0.1);
            gainNode.gain.linearRampToValueAtTime(0, this.audioContext.currentTime + 2.5);
            
            // Connect nodes
            this.audioSource.connect(filterNode);
            filterNode.connect(gainNode);
            gainNode.connect(this.audioContext.destination);
            
            // Play the sound
            this.audioSource.start();
            
        } catch (error) {
            console.log('Audio playback error:', error);
        }
    }

    playCompletionSound() {
        if (!this.audioContext) return;
        
        try {
            // Create a short completion sound
            const duration = 0.3;
            const sampleRate = this.audioContext.sampleRate;
            const length = sampleRate * duration;
            const buffer = this.audioContext.createBuffer(1, length, sampleRate);
            const data = buffer.getChannelData(0);
            
            // Create a pleasant completion tone
            for (let i = 0; i < length; i++) {
                const t = i / sampleRate;
                const progress = t / duration;
                
                // Two-tone chord (C major)
                const frequency1 = 523.25; // C5
                const frequency2 = 659.25; // E5
                
                // Envelope
                const envelope = Math.exp(-progress * 8);
                
                const tone1 = Math.sin(2 * Math.PI * frequency1 * t) * 0.3;
                const tone2 = Math.sin(2 * Math.PI * frequency2 * t) * 0.2;
                
                data[i] = (tone1 + tone2) * envelope;
            }
            
            // Play the completion sound
            const source = this.audioContext.createBufferSource();
            source.buffer = buffer;
            
            const gainNode = this.audioContext.createGain();
            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
            
            source.connect(gainNode);
            gainNode.connect(this.audioContext.destination);
            
            source.start();
            
        } catch (error) {
            console.log('Completion sound error:', error);
        }
    }

    setupThreeJS() {
        // Vertex shader
        const vertexShader = `
            void main() {
                gl_Position = vec4( position, 1.0 );
            }
        `;

        // Fragment shader
        const fragmentShader = `
            #define TWO_PI 6.2831853072
            #define PI 3.14159265359

            precision highp float;
            uniform vec2 resolution;
            uniform float time;

            void main(void) {
                vec2 uv = (gl_FragCoord.xy * 2.0 - resolution.xy) / min(resolution.x, resolution.y);
                float t = time*0.05;
                float lineWidth = 0.002;

                vec3 color = vec3(0.0);
                for(int j = 0; j < 3; j++){
                    for(int i=0; i < 5; i++){
                        color[j] += lineWidth*float(i*i) / abs(fract(t - 0.01*float(j)+float(i)*0.01)*5.0 - length(uv) + mod(uv.x+uv.y, 0.2));
                    }
                }
                
                gl_FragColor = vec4(color[0],color[1],color[2],1.0);
            }
        `;

        // Initialize Three.js scene
        this.camera = new THREE.Camera();
        this.camera.position.z = 1;

        this.scene = new THREE.Scene();
        const geometry = new THREE.PlaneGeometry(2, 2);

        this.uniforms = {
            time: { type: "f", value: 1.0 },
            resolution: { type: "v2", value: new THREE.Vector2() },
        };

        const material = new THREE.ShaderMaterial({
            uniforms: this.uniforms,
            vertexShader: vertexShader,
            fragmentShader: fragmentShader,
        });

        this.mesh = new THREE.Mesh(geometry, material);
        this.scene.add(this.mesh);

        this.renderer = new THREE.WebGLRenderer({ antialias: true });
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.renderer.setSize(window.innerWidth, window.innerHeight);
        this.renderer.domElement.style.position = 'absolute';
        this.renderer.domElement.style.top = '0';
        this.renderer.domElement.style.left = '0';
        this.renderer.domElement.style.zIndex = '1';

        this.container.appendChild(this.renderer.domElement);

        // Handle window resize
        window.addEventListener('resize', () => this.onWindowResize(), false);
        this.onWindowResize();
    }

    onWindowResize() {
        const width = window.innerWidth;
        const height = window.innerHeight;
        
        this.renderer.setSize(width, height);
        this.uniforms.resolution.value.x = this.renderer.domElement.width;
        this.uniforms.resolution.value.y = this.renderer.domElement.height;
    }

    animate() {
        this.animationId = requestAnimationFrame(() => this.animate());
        this.uniforms.time.value += 0.05;
        this.renderer.render(this.scene, this.camera);
    }

    simulateLoading() {
        // Play the sweep sound at the start
        this.playSweepSound();
        
        const duration = 2500; // 2.5 seconds
        const interval = 30; // Update every 30ms for smoother animation
        const increment = 100 / (duration / interval);

        const loadingInterval = setInterval(() => {
            this.progress += increment;
            this.progressBar.style.width = Math.min(this.progress, 100) + '%';

            // Add some randomness to make it feel more natural
            if (Math.random() < 0.1 && this.progress < 90) {
                this.progress += increment * 2;
            }

            if (this.progress >= 100) {
                clearInterval(loadingInterval);
                setTimeout(() => {
                    this.hide();
                }, 300);
            }
        }, interval);
    }

    hide() {
        // Play completion sound
        this.playCompletionSound();
        
        // Remove loading class from body to show header
        document.body.classList.remove('loading');
        
        // Add fade out effect with scale
        this.container.style.opacity = '0';
        this.container.style.transform = 'scale(1.1)';
        this.container.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        
        setTimeout(() => {
            this.container.style.display = 'none';
            this.cleanup();
        }, 600);
    }

    cleanup() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
        
        if (this.renderer) {
            this.renderer.dispose();
        }
        
        // Clean up audio resources
        if (this.audioSource) {
            try {
                this.audioSource.stop();
            } catch (e) {
                // Source might already be stopped
            }
        }
        
        if (this.audioContext) {
            this.audioContext.close();
        }
        
        window.removeEventListener('resize', () => this.onWindowResize());
    }
}

// Initialize loading screen when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const loadingScreen = new LoadingScreen();
    
    // Enable audio on first user interaction
    const enableAudio = () => {
        if (loadingScreen.audioContext && loadingScreen.audioContext.state === 'suspended') {
            loadingScreen.audioContext.resume();
        }
        document.removeEventListener('click', enableAudio);
        document.removeEventListener('keydown', enableAudio);
        document.removeEventListener('touchstart', enableAudio);
    };
    
    document.addEventListener('click', enableAudio);
    document.addEventListener('keydown', enableAudio);
    document.addEventListener('touchstart', enableAudio);
});
</script>
