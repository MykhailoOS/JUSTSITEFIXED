<?php
/**
 * JSB Website Builder - Three-Panel Editor (Pines Library Edition)
 * 
 * Architecture:
 * - Library (Left Panel) - Drag-and-drop element library with categories
 * - Canvas (Center Panel) - iframe-based preview with postMessage communication
 * - Inspector (Right Panel) - Property editor with visual box-model controls
 * 
 * Tech Stack:
 * - Tailwind CSS + Alpine.js via Pines UI Library
 * - iframe sandbox for security isolation
 * - CSP headers for clickjacking protection
 */

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/language.php';
start_session_if_needed();

// Initialize language system
LanguageManager::init();

// CSP Headers for security
header('Content-Security-Policy: frame-ancestors \'self\'; default-src \'self\' https://unpkg.com https://cdn.tailwindcss.com https://fonts.googleapis.com https://fonts.gstatic.com; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://unpkg.com https://cdn.tailwindcss.com; style-src \'self\' \'unsafe-inline\' https://cdn.tailwindcss.com https://fonts.googleapis.com; img-src \'self\' data: https:; font-src \'self\' https://fonts.gstatic.com data:; connect-src \'self\';');
header('X-Frame-Options: SAMEORIGIN');
header('Content-Type: text/html; charset=utf-8');

// Determine current user
$uid = current_user_id();

// If not logged in — redirect to landing page
if (!$uid) {
    header('Location: landing.php');
    exit;
}

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\') . '/';

// Fetch user name
$displayName = '';
try {
    $pdo = DatabaseConnectionProvider::getConnection();
    $stmt = $pdo->prepare('SELECT COALESCE(NULLIF(name, ""), email) AS dn FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    $row = $stmt->fetch();
    if ($row && !empty($row['dn'])) { 
        $displayName = htmlspecialchars($row['dn'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); 
    }
} catch (Throwable $e) { 
    /* ignore */ 
}

// Current project ID (if loading existing project)
$projectId = isset($_GET['load']) ? (int)$_GET['load'] : null;
$projectTitle = 'Untitled Project';

if ($projectId) {
    try {
        $pdo = DatabaseConnectionProvider::getConnection();
        $stmt = $pdo->prepare('SELECT title FROM projects WHERE id = ? AND user_id = ?');
        $stmt->execute([$projectId, $uid]);
        $project = $stmt->fetch();
        if ($project) {
            $projectTitle = htmlspecialchars($project['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    } catch (Throwable $e) {
        /* ignore */
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <base href="<?php echo $basePath; ?>">
    <title><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> — Website Builder</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN for prototyping) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js (via Pines recommendation) -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar styling */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }
        
        /* Canvas iframe styles */
        #canvasFrame {
            border: none;
            background: white;
            transition: width 0.3s ease, height 0.3s ease;
        }
        
        /* Selection outline for canvas elements */
        .jsb-element-selected {
            outline: 2px solid #3B82F6 !important;
            outline-offset: 2px;
        }
        
        /* Box model visual control */
        .box-model-cell {
            min-width: 48px;
            text-align: center;
        }
        
        /* Drag and drop styles */
        .dragging {
            opacity: 0.5;
            cursor: grabbing !important;
        }
        
        .drop-zone-active {
            background: rgba(59, 130, 246, 0.1) !important;
            border: 2px dashed #3B82F6 !important;
        }
    </style>
</head>
<body class="bg-gray-50 overflow-hidden">

<!-- Main Editor Layout -->
<div class="flex flex-col h-screen" x-data="editorApp()">
    
    <!-- TOP BAR (Pines menubar pattern) -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg z-50">
        <div class="flex items-center justify-between px-4 h-14">
            
            <!-- Left Section -->
            <div class="flex items-center gap-4">
                <a href="profile.php" class="flex items-center gap-2 hover:opacity-80 transition">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 3h18v18H3V3zm16 16V5H5v14h14zM7 7h10v2H7V7zm0 4h10v2H7v-2zm0 4h7v2H7v-2z"/>
                    </svg>
                    <span class="font-semibold text-lg hidden md:inline"><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
                </a>
                
                <div class="h-6 w-px bg-white/20 hidden md:block"></div>
                
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="font-medium text-sm" x-text="projectTitle">Project Name</span>
                </div>
            </div>
            
            <!-- Center Section -->
            <div class="flex items-center gap-3">
                <!-- Undo/Redo -->
                <div class="flex items-center gap-1 bg-white/10 rounded-lg p-1">
                    <button @click="undo()" :disabled="!canUndo" :class="canUndo ? 'hover:bg-white/10' : 'opacity-40 cursor-not-allowed'" class="p-2 rounded transition" title="Undo (Ctrl+Z)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </button>
                    <button @click="redo()" :disabled="!canRedo" :class="canRedo ? 'hover:bg-white/10' : 'opacity-40 cursor-not-allowed'" class="p-2 rounded transition" title="Redo (Ctrl+Shift+Z)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Viewport Switcher -->
                <div class="flex items-center gap-1 bg-white/10 rounded-lg p-1">
                    <button @click="setViewport('desktop')" :class="viewport === 'desktop' ? 'bg-white/20' : 'hover:bg-white/10'" class="p-2 rounded transition" title="Desktop">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="2" y="3" width="20" height="14" rx="2" stroke-width="2"/>
                            <line x1="8" y1="21" x2="16" y2="21" stroke-width="2"/>
                            <line x1="12" y1="17" x2="12" y2="21" stroke-width="2"/>
                        </svg>
                    </button>
                    <button @click="setViewport('tablet')" :class="viewport === 'tablet' ? 'bg-white/20' : 'hover:bg-white/10'" class="p-2 rounded transition" title="Tablet">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="5" y="2" width="14" height="20" rx="2" stroke-width="2"/>
                            <circle cx="12" cy="18" r="1"/>
                        </svg>
                    </button>
                    <button @click="setViewport('mobile')" :class="viewport === 'mobile' ? 'bg-white/20' : 'hover:bg-white/10'" class="p-2 rounded transition" title="Mobile">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="7" y="2" width="10" height="20" rx="2" stroke-width="2"/>
                            <circle cx="12" cy="18" r="1"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Zoom Controls -->
                <div class="flex items-center gap-1 bg-white/10 rounded-lg p-1">
                    <button @click="zoomOut()" class="p-2 hover:bg-white/10 rounded transition" title="Zoom Out">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" stroke-width="2"/>
                            <path d="M21 21l-4.35-4.35" stroke-width="2" stroke-linecap="round"/>
                            <path d="M8 11h6" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <span class="px-2 text-sm font-medium min-w-[50px] text-center" x-text="Math.round(zoom * 100) + '%'">100%</span>
                    <button @click="zoomIn()" class="p-2 hover:bg-white/10 rounded transition" title="Zoom In">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" stroke-width="2"/>
                            <path d="M21 21l-4.35-4.35" stroke-width="2" stroke-linecap="round"/>
                            <path d="M11 8v6M8 11h6" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Right Section -->
            <div class="flex items-center gap-2">
                <button @click="saveProject()" class="flex items-center gap-2 px-3 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    <span class="text-sm font-medium hidden lg:inline">Save</span>
                </button>
                
                <button @click="previewProject()" class="flex items-center gap-2 px-3 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span class="text-sm font-medium hidden lg:inline">Preview</span>
                </button>
                
                <button @click="deployProject()" class="flex items-center gap-2 px-4 py-2 bg-white text-blue-600 hover:bg-blue-50 rounded-lg transition font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    <span class="text-sm">Deploy</span>
                </button>
                
                <div class="h-6 w-px bg-white/20 hidden md:block"></div>
                
                <!-- User Dropdown (Pines dropdown pattern) -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="p-2 hover:bg-white/10 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" x-cloak x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900"><?php echo $displayName ?: 'User'; ?></p>
                        </div>
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                        <a href="dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- THREE PANEL LAYOUT -->
    <div class="flex flex-1 overflow-hidden">
        
        <!-- LEFT PANEL - LIBRARY (Pines slide-over on mobile) -->
        <aside 
            x-show="!isMobile || showLibrary" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="w-80 bg-white border-r border-gray-200 flex flex-col relative z-30"
            :class="isMobile ? 'fixed inset-y-0 left-0 shadow-xl' : ''"
            style="backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95);">
            
            <!-- Library Header -->
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold text-gray-900">Elements Library</h2>
                    <button v-if="isMobile" @click="showLibrary = false" class="p-1 hover:bg-gray-100 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-500 mb-3">Drag elements to canvas</p>
                
                <!-- Search -->
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="librarySearch"
                        placeholder="Search elements..." 
                        class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            
            <!-- Library Content (Pines accordion pattern) -->
            <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
                <template x-for="category in filteredLibrary" :key="category.name">
                    <div class="mb-4">
                        <button @click="toggleCategory(category.name)" class="w-full flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition">
                            <span class="font-medium text-sm text-gray-900" x-text="category.name"></span>
                            <svg class="w-4 h-4 transition-transform" :class="category.open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="category.open" x-collapse class="mt-2 space-y-2">
                            <template x-for="element in category.elements" :key="element.type">
                                <div 
                                    @click="addElement(element.type)"
                                    draggable="true"
                                    @dragstart="handleDragStart($event, element.type)"
                                    @dragend="handleDragEnd($event)"
                                    class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg cursor-move transition group">
                                    <div class="p-2 bg-white rounded group-hover:bg-blue-100 transition" x-html="element.icon"></div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900" x-text="element.label"></div>
                                        <div class="text-xs text-gray-500" x-text="element.description"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </aside>
        
        <!-- Toggle Library Button (Mobile) -->
        <button 
            x-show="isMobile && !showLibrary"
            @click="showLibrary = true"
            class="fixed left-4 bottom-4 p-3 bg-blue-600 text-white rounded-full shadow-lg z-40 hover:bg-blue-700 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        
        <!-- CENTER PANEL - CANVAS -->
        <main class="flex-1 bg-gray-100 relative overflow-hidden">
            <div class="h-full flex items-center justify-center p-8">
                <div 
                    class="bg-white shadow-2xl rounded-lg overflow-hidden transition-all duration-300"
                    :style="canvasStyle"
                    @dragover.prevent
                    @drop="handleDrop($event)">
                    <iframe 
                        id="canvasFrame"
                        sandbox="allow-scripts allow-same-origin"
                        class="w-full h-full"
                        :style="{ transform: `scale(${zoom})`, transformOrigin: 'top left' }">
                    </iframe>
                </div>
            </div>
            
            <!-- Canvas Overlay Controls -->
            <div class="absolute top-4 right-4 bg-white rounded-lg shadow-lg p-2 flex gap-2">
                <button @click="toggleGrid()" :class="gridEnabled ? 'bg-blue-100 text-blue-600' : 'text-gray-600'" class="p-2 hover:bg-gray-100 rounded transition" title="Toggle Grid">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                    </svg>
                </button>
                <button @click="toggleSnapping()" :class="snappingEnabled ? 'bg-blue-100 text-blue-600' : 'text-gray-600'" class="p-2 hover:bg-gray-100 rounded transition" title="Toggle Snapping">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </button>
            </div>
        </main>
        
        <!-- RIGHT PANEL - INSPECTOR (Pines slide-over on mobile) -->
        <aside 
            x-show="selectedElement && (!isMobile || showInspector)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="w-80 bg-white border-l border-gray-200 flex flex-col relative z-30"
            :class="isMobile ? 'fixed inset-y-0 right-0 shadow-xl' : ''"
            style="backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95);">
            
            <!-- Inspector Header -->
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-semibold text-gray-900">Inspector</h2>
                    <button v-if="isMobile" @click="showInspector = false" class="p-1 hover:bg-gray-100 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-500" x-text="selectedElement ? selectedElement.label : 'Select an element'"></p>
            </div>
            
            <!-- Inspector Tabs (Pines tabs pattern) -->
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <template x-for="tab in inspectorTabs" :key="tab.id">
                        <button 
                            @click="activeTab = tab.id"
                            :class="activeTab === tab.id ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="flex-1 py-3 px-1 text-center border-b-2 font-medium text-xs transition">
                            <span x-text="tab.label"></span>
                        </button>
                    </template>
                </nav>
            </div>
            
            <!-- Inspector Content -->
            <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
                <!-- Content Tab -->
                <div x-show="activeTab === 'content'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Text Content</label>
                        <textarea 
                            x-model="selectedElementProps.text"
                            @input="updateElement()"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tag</label>
                        <select 
                            x-model="selectedElementProps.tag"
                            @change="updateElement()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="div">Div</option>
                            <option value="section">Section</option>
                            <option value="article">Article</option>
                            <option value="header">Header</option>
                            <option value="footer">Footer</option>
                            <option value="main">Main</option>
                        </select>
                    </div>
                </div>
                
                <!-- Layout Tab -->
                <div x-show="activeTab === 'layout'" class="space-y-4">
                    <!-- Box Model Visual Control -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700">Spacing</label>
                            <button @click="toggleLockSpacing()" class="p-1 hover:bg-gray-100 rounded transition">
                                <svg class="w-4 h-4" :class="spacingLocked ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="spacingLocked ? 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' : 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z'"/>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Box Model Diagram -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <!-- Margin -->
                            <div class="bg-orange-100 border border-orange-300 rounded p-2">
                                <div class="text-xs text-orange-700 font-medium mb-2">Margin</div>
                                <div class="grid grid-cols-3 gap-1 mb-2">
                                    <div></div>
                                    <input type="number" x-model="selectedElementProps.marginTop" @input="updateSpacing('margin', 'top')" class="box-model-cell px-2 py-1 text-xs border border-orange-300 rounded bg-white text-center" placeholder="0">
                                    <div></div>
                                    <input type="number" x-model="selectedElementProps.marginLeft" @input="updateSpacing('margin', 'left')" class="box-model-cell px-2 py-1 text-xs border border-orange-300 rounded bg-white text-center" placeholder="0">
                                    
                                    <!-- Padding (nested) -->
                                    <div class="bg-green-100 border border-green-300 rounded p-2">
                                        <div class="text-xs text-green-700 font-medium mb-2">Padding</div>
                                        <div class="grid grid-cols-3 gap-1">
                                            <div></div>
                                            <input type="number" x-model="selectedElementProps.paddingTop" @input="updateSpacing('padding', 'top')" class="box-model-cell px-2 py-1 text-xs border border-green-300 rounded bg-white text-center" placeholder="0">
                                            <div></div>
                                            <input type="number" x-model="selectedElementProps.paddingLeft" @input="updateSpacing('padding', 'left')" class="box-model-cell px-2 py-1 text-xs border border-green-300 rounded bg-white text-center" placeholder="0">
                                            
                                            <!-- Content -->
                                            <div class="bg-blue-200 border border-blue-400 rounded p-4 text-center">
                                                <span class="text-xs text-blue-800 font-medium">Content</span>
                                            </div>
                                            
                                            <input type="number" x-model="selectedElementProps.paddingRight" @input="updateSpacing('padding', 'right')" class="box-model-cell px-2 py-1 text-xs border border-green-300 rounded bg-white text-center" placeholder="0">
                                            <div></div>
                                            <input type="number" x-model="selectedElementProps.paddingBottom" @input="updateSpacing('padding', 'bottom')" class="box-model-cell px-2 py-1 text-xs border border-green-300 rounded bg-white text-center" placeholder="0">
                                            <div></div>
                                        </div>
                                    </div>
                                    
                                    <input type="number" x-model="selectedElementProps.marginRight" @input="updateSpacing('margin', 'right')" class="box-model-cell px-2 py-1 text-xs border border-orange-300 rounded bg-white text-center" placeholder="0">
                                    <div></div>
                                    <input type="number" x-model="selectedElementProps.marginBottom" @input="updateSpacing('margin', 'bottom')" class="box-model-cell px-2 py-1 text-xs border border-orange-300 rounded bg-white text-center" placeholder="0">
                                    <div></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Spacing Unit -->
                        <div class="mt-2">
                            <label class="block text-xs text-gray-600 mb-1">Unit</label>
                            <div class="flex gap-1">
                                <button @click="spacingUnit = 'px'" :class="spacingUnit === 'px' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'" class="flex-1 px-3 py-1 text-xs rounded hover:bg-blue-50 transition">px</button>
                                <button @click="spacingUnit = 'rem'" :class="spacingUnit === 'rem' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'" class="flex-1 px-3 py-1 text-xs rounded hover:bg-blue-50 transition">rem</button>
                                <button @click="spacingUnit = '%'" :class="spacingUnit === '%' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'" class="flex-1 px-3 py-1 text-xs rounded hover:bg-blue-50 transition">%</button>
                            </div>
                        </div>
                        
                        <!-- Spacing Presets -->
                        <div class="mt-2">
                            <label class="block text-xs text-gray-600 mb-1">Presets</label>
                            <div class="flex gap-1">
                                <button @click="applySpacingPreset(0)" class="flex-1 px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition">0</button>
                                <button @click="applySpacingPreset(8)" class="flex-1 px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition">8</button>
                                <button @click="applySpacingPreset(16)" class="flex-1 px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition">16</button>
                                <button @click="applySpacingPreset(24)" class="flex-1 px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition">24</button>
                                <button @click="applySpacingPreset(32)" class="flex-1 px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition">32</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Width & Height -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Width</label>
                            <input type="text" x-model="selectedElementProps.width" @input="updateElement()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="auto">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Height</label>
                            <input type="text" x-model="selectedElementProps.height" @input="updateElement()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="auto">
                        </div>
                    </div>
                </div>
                
                <!-- Typography Tab -->
                <div x-show="activeTab === 'typography'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Font Size</label>
                        <input type="number" x-model="selectedElementProps.fontSize" @input="updateElement()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Font Weight</label>
                        <select x-model="selectedElementProps.fontWeight" @change="updateElement()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="300">Light</option>
                            <option value="400">Normal</option>
                            <option value="500">Medium</option>
                            <option value="600">Semibold</option>
                            <option value="700">Bold</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                        <input type="color" x-model="selectedElementProps.color" @input="updateElement()" class="w-full h-10 border border-gray-300 rounded-lg cursor-pointer">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Text Align</label>
                        <div class="flex gap-1">
                            <button @click="selectedElementProps.textAlign = 'left'; updateElement()" :class="selectedElementProps.textAlign === 'left' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'" class="flex-1 p-2 rounded hover:bg-blue-50 transition">
                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"/>
                                </svg>
                            </button>
                            <button @click="selectedElementProps.textAlign = 'center'; updateElement()" :class="selectedElementProps.textAlign === 'center' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'" class="flex-1 p-2 rounded hover:bg-blue-50 transition">
                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M5 18h14"/>
                                </svg>
                            </button>
                            <button @click="selectedElementProps.textAlign = 'right'; updateElement()" :class="selectedElementProps.textAlign === 'right' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'" class="flex-1 p-2 rounded hover:bg-blue-50 transition">
                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M6 18h14"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Accessibility Tab -->
                <div x-show="activeTab === 'accessibility'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ARIA Label</label>
                        <input type="text" x-model="selectedElementProps.ariaLabel" @input="updateElement()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Descriptive label">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select x-model="selectedElementProps.role" @change="updateElement()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">None</option>
                            <option value="button">Button</option>
                            <option value="link">Link</option>
                            <option value="navigation">Navigation</option>
                            <option value="main">Main</option>
                            <option value="banner">Banner</option>
                            <option value="contentinfo">Content Info</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="selectedElementProps.focusable" @change="updateElement()" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Focusable</span>
                        </label>
                    </div>
                </div>
                
                <!-- Advanced Tab -->
                <div x-show="activeTab === 'advanced'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID</label>
                        <input type="text" x-model="selectedElementProps.id" @input="updateElement()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CSS Classes</label>
                        <input type="text" x-model="selectedElementProps.classes" @input="updateElement()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="class1 class2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Custom CSS</label>
                        <textarea x-model="selectedElementProps.customCSS" @input="updateElement()" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <button @click="deleteElement()" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                        Delete Element
                    </button>
                </div>
            </div>
        </aside>
        
        <!-- Toggle Inspector Button (Mobile) -->
        <button 
            x-show="isMobile && selectedElement && !showInspector"
            @click="showInspector = true"
            class="fixed right-4 bottom-4 p-3 bg-blue-600 text-white rounded-full shadow-lg z-40 hover:bg-blue-700 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
        </button>
    </div>
    
    <!-- Toast Notifications (Pines toast pattern) -->
    <div class="fixed bottom-4 right-4 z-50 space-y-2" x-show="toasts.length > 0">
        <template x-for="toast in toasts" :key="toast.id">
            <div 
                x-show="toast.show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-2 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-2 opacity-0"
                class="bg-white rounded-lg shadow-lg p-4 flex items-center gap-3 min-w-[300px]"
                :class="toast.type === 'success' ? 'border-l-4 border-green-500' : toast.type === 'error' ? 'border-l-4 border-red-500' : 'border-l-4 border-blue-500'">
                <div class="flex-shrink-0">
                    <svg x-show="toast.type === 'success'" class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="toast.type === 'error'" class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <svg x-show="toast.type === 'info'" class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900" x-text="toast.message"></p>
                </div>
                <button @click="removeToast(toast.id)" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>
</div>

<!-- Main Alpine.js Application -->
<script>
function editorApp() {
    return {
        // Project state
        projectId: <?php echo $projectId ? $projectId : 'null'; ?>,
        projectTitle: '<?php echo addslashes($projectTitle); ?>',
        
        // Canvas state
        viewport: 'desktop',
        zoom: 1.0,
        gridEnabled: true,
        snappingEnabled: true,
        
        // Panels state
        isMobile: window.innerWidth < 1024,
        showLibrary: window.innerWidth >= 1024,
        showInspector: window.innerWidth >= 1024,
        
        // History state
        history: [],
        historyIndex: -1,
        canUndo: false,
        canRedo: false,
        
        // Library state
        librarySearch: '',
        library: [
            {
                name: 'Layout',
                open: true,
                elements: [
                    {
                        type: 'section',
                        label: 'Section',
                        description: 'Container section',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/></svg>'
                    },
                    {
                        type: 'container',
                        label: 'Container',
                        description: 'Centered container',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="5" y="5" width="14" height="14" rx="1" stroke-width="2"/></svg>'
                    },
                    {
                        type: 'row',
                        label: 'Row',
                        description: 'Horizontal flex row',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18" stroke-width="2" stroke-linecap="round"/></svg>'
                    },
                    {
                        type: 'column',
                        label: 'Column',
                        description: 'Vertical flex column',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 3v18M6 3v18M18 3v18" stroke-width="2" stroke-linecap="round"/></svg>'
                    }
                ]
            },
            {
                name: 'Content',
                open: true,
                elements: [
                    {
                        type: 'heading',
                        label: 'Heading',
                        description: 'H1-H6 heading',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10" stroke-width="2" stroke-linecap="round"/></svg>'
                    },
                    {
                        type: 'paragraph',
                        label: 'Paragraph',
                        description: 'Text paragraph',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h12M4 18h12" stroke-width="2" stroke-linecap="round"/></svg>'
                    },
                    {
                        type: 'button',
                        label: 'Button',
                        description: 'Call to action',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="8" rx="2" stroke-width="2"/></svg>'
                    },
                    {
                        type: 'link',
                        label: 'Link',
                        description: 'Hyperlink',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71" stroke-width="2" stroke-linecap="round"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71" stroke-width="2" stroke-linecap="round"/></svg>'
                    },
                    {
                        type: 'list',
                        label: 'List',
                        description: 'Bulleted list',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5h12M9 12h12M9 19h12M5 5v.01M5 12v.01M5 19v.01" stroke-width="2" stroke-linecap="round"/></svg>'
                    },
                    {
                        type: 'divider',
                        label: 'Divider',
                        description: 'Horizontal line',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12h18" stroke-width="2" stroke-linecap="round"/></svg>'
                    }
                ]
            },
            {
                name: 'Media',
                open: false,
                elements: [
                    {
                        type: 'image',
                        label: 'Image',
                        description: 'Image element',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21" stroke-width="2" stroke-linecap="round"/></svg>'
                    },
                    {
                        type: 'video',
                        label: 'Video',
                        description: 'Video container',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2" stroke-width="2"/><path d="M10 9l5 3-5 3V9z" fill="currentColor"/></svg>'
                    },
                    {
                        type: 'icon',
                        label: 'Icon',
                        description: 'SVG icon',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                    }
                ]
            },
            {
                name: 'Forms',
                open: false,
                elements: [
                    {
                        type: 'input',
                        label: 'Text Input',
                        description: 'Text field',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2" stroke-width="2"/><path d="M7 10h4M7 14h10" stroke-width="2" stroke-linecap="round"/></svg>'
                    },
                    {
                        type: 'textarea',
                        label: 'Textarea',
                        description: 'Multi-line text',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" stroke-width="2"/><path d="M7 8h10M7 12h10M7 16h6" stroke-width="2" stroke-linecap="round"/></svg>'
                    },
                    {
                        type: 'select',
                        label: 'Select',
                        description: 'Dropdown menu',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2" stroke-width="2"/><path d="M12 11l3 3-3 3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" transform="rotate(90 12 12)"/></svg>'
                    },
                    {
                        type: 'checkbox',
                        label: 'Checkbox',
                        description: 'Checkbox input',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                    },
                    {
                        type: 'radio',
                        label: 'Radio',
                        description: 'Radio button',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="currentColor"/></svg>'
                    }
                ]
            },
            {
                name: 'Components',
                open: false,
                elements: [
                    {
                        type: 'card',
                        label: 'Card',
                        description: 'Card container',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2" stroke-width="2"/><path d="M4 10h16" stroke-width="2"/></svg>'
                    },
                    {
                        type: 'navbar',
                        label: 'Navbar',
                        description: 'Navigation bar',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="4" rx="1" stroke-width="2"/><path d="M7 14h4M7 18h10" stroke-width="2" stroke-linecap="round"/></svg>'
                    },
                    {
                        type: 'tabs',
                        label: 'Tabs',
                        description: 'Tab navigation',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="5" rx="1" stroke-width="2"/><rect x="14" y="3" width="7" height="5" rx="1" stroke-width="2"/><rect x="3" y="12" width="18" height="9" rx="1" stroke-width="2"/></svg>'
                    },
                    {
                        type: 'accordion',
                        label: 'Accordion',
                        description: 'Collapsible sections',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="3" rx="1" stroke-width="2"/><rect x="3" y="11" width="18" height="3" rx="1" stroke-width="2"/><rect x="3" y="17" width="18" height="3" rx="1" stroke-width="2"/></svg>'
                    },
                    {
                        type: 'modal',
                        label: 'Modal',
                        description: 'Dialog overlay',
                        icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="5" y="7" width="14" height="10" rx="2" stroke-width="2"/><path d="M9 7V5a3 3 0 116 0v2" stroke-width="2" stroke-linecap="round"/></svg>'
                    }
                ]
            }
        ],
        
        // Inspector state
        selectedElement: null,
        selectedElementProps: {
            text: '',
            tag: 'div',
            width: 'auto',
            height: 'auto',
            marginTop: 0,
            marginRight: 0,
            marginBottom: 0,
            marginLeft: 0,
            paddingTop: 0,
            paddingRight: 0,
            paddingBottom: 0,
            paddingLeft: 0,
            fontSize: 16,
            fontWeight: 400,
            color: '#000000',
            textAlign: 'left',
            ariaLabel: '',
            role: '',
            focusable: true,
            id: '',
            classes: '',
            customCSS: ''
        },
        activeTab: 'content',
        inspectorTabs: [
            { id: 'content', label: 'Content' },
            { id: 'layout', label: 'Layout' },
            { id: 'typography', label: 'Type' },
            { id: 'accessibility', label: 'A11y' },
            { id: 'advanced', label: 'Advanced' }
        ],
        spacingLocked: false,
        spacingUnit: 'px',
        
        // Toast notifications
        toasts: [],
        toastId: 0,
        
        // Debounce timer
        updateTimer: null,
        
        init() {
            // Initialize iframe
            this.initCanvas();
            
            // Listen for window resize
            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth < 1024;
                if (!this.isMobile) {
                    this.showLibrary = true;
                    this.showInspector = true;
                }
            });
            
            // Listen for iframe messages
            window.addEventListener('message', (e) => {
                if (e.data.type === 'elementSelected') {
                    this.selectElement(e.data.element);
                }
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    this.undo();
                } else if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) {
                    e.preventDefault();
                    this.redo();
                } else if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    this.saveProject();
                }
            });
            
            this.showToast('Editor loaded successfully', 'success');
        },
        
        // Canvas methods
        initCanvas() {
            const iframe = document.getElementById('canvasFrame');
            iframe.srcdoc = this.getCanvasHTML();
        },
        
        getCanvasHTML() {
            return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        .jsb-element { position: relative; min-height: 20px; }
        .jsb-element:hover { outline: 1px dashed #3B82F6; }
        .jsb-element-selected { outline: 2px solid #3B82F6 !important; }
    </style>
</head>
<body>
    <div id="canvas-root" class="min-h-screen">
        <div class="p-8 text-center text-gray-400">
            <p>Drag elements from the library to start building</p>
        </div>
    </div>
    <script>
        document.getElementById('canvas-root').addEventListener('click', function(e) {
            const element = e.target.closest('.jsb-element');
            if (element) {
                window.parent.postMessage({ type: 'elementSelected', element: { id: element.id } }, '*');
            }
        });
    </script>
</body>
</html>
            `;
        },
        
        get canvasStyle() {
            const dimensions = {
                desktop: { width: '1280px', height: '800px' },
                tablet: { width: '768px', height: '1024px' },
                mobile: { width: '375px', height: '667px' }
            };
            return `width: ${dimensions[this.viewport].width}; height: ${dimensions[this.viewport].height};`;
        },
        
        setViewport(viewport) {
            this.viewport = viewport;
        },
        
        zoomIn() {
            if (this.zoom < 2.0) {
                this.zoom = Math.round((this.zoom + 0.1) * 10) / 10;
            }
        },
        
        zoomOut() {
            if (this.zoom > 0.25) {
                this.zoom = Math.round((this.zoom - 0.1) * 10) / 10;
            }
        },
        
        toggleGrid() {
            this.gridEnabled = !this.gridEnabled;
        },
        
        toggleSnapping() {
            this.snappingEnabled = !this.snappingEnabled;
        },
        
        // Library methods
        get filteredLibrary() {
            if (!this.librarySearch) return this.library;
            
            const search = this.librarySearch.toLowerCase();
            return this.library.map(category => ({
                ...category,
                elements: category.elements.filter(el => 
                    el.label.toLowerCase().includes(search) ||
                    el.description.toLowerCase().includes(search)
                )
            })).filter(category => category.elements.length > 0);
        },
        
        toggleCategory(name) {
            const category = this.library.find(c => c.name === name);
            if (category) {
                category.open = !category.open;
            }
        },
        
        handleDragStart(event, type) {
            event.dataTransfer.effectAllowed = 'copy';
            event.dataTransfer.setData('text/plain', type);
            event.target.classList.add('dragging');
        },
        
        handleDragEnd(event) {
            event.target.classList.remove('dragging');
        },
        
        handleDrop(event) {
            event.preventDefault();
            const type = event.dataTransfer.getData('text/plain');
            if (type) {
                this.addElement(type);
            }
        },
        
        addElement(type) {
            const iframe = document.getElementById('canvasFrame');
            const message = { type: 'addElement', elementType: type };
            iframe.contentWindow.postMessage(message, '*');
            this.showToast(`${type} added to canvas`, 'success');
        },
        
        // Inspector methods
        selectElement(element) {
            this.selectedElement = element;
            // Load element properties
            // In a real implementation, fetch from iframe
        },
        
        updateElement() {
            clearTimeout(this.updateTimer);
            this.updateTimer = setTimeout(() => {
                const iframe = document.getElementById('canvasFrame');
                iframe.contentWindow.postMessage({
                    type: 'updateElement',
                    props: this.selectedElementProps
                }, '*');
            }, 200);
        },
        
        updateSpacing(type, side) {
            if (this.spacingLocked) {
                const value = this.selectedElementProps[`${type}${side.charAt(0).toUpperCase() + side.slice(1)}`];
                this.selectedElementProps[`${type}Top`] = value;
                this.selectedElementProps[`${type}Right`] = value;
                this.selectedElementProps[`${type}Bottom`] = value;
                this.selectedElementProps[`${type}Left`] = value;
            }
            this.updateElement();
        },
        
        toggleLockSpacing() {
            this.spacingLocked = !this.spacingLocked;
        },
        
        applySpacingPreset(value) {
            this.selectedElementProps.paddingTop = value;
            this.selectedElementProps.paddingRight = value;
            this.selectedElementProps.paddingBottom = value;
            this.selectedElementProps.paddingLeft = value;
            this.selectedElementProps.marginTop = value;
            this.selectedElementProps.marginRight = value;
            this.selectedElementProps.marginBottom = value;
            this.selectedElementProps.marginLeft = value;
            this.updateElement();
        },
        
        deleteElement() {
            if (confirm('Are you sure you want to delete this element?')) {
                const iframe = document.getElementById('canvasFrame');
                iframe.contentWindow.postMessage({ type: 'deleteElement', id: this.selectedElement.id }, '*');
                this.selectedElement = null;
                this.showToast('Element deleted', 'success');
            }
        },
        
        // History methods
        undo() {
            if (this.canUndo) {
                this.historyIndex--;
                this.restoreState();
                this.updateHistoryButtons();
            }
        },
        
        redo() {
            if (this.canRedo) {
                this.historyIndex++;
                this.restoreState();
                this.updateHistoryButtons();
            }
        },
        
        saveState() {
            // Save current state to history
            // Implementation depends on canvas state management
        },
        
        restoreState() {
            // Restore state from history
        },
        
        updateHistoryButtons() {
            this.canUndo = this.historyIndex > 0;
            this.canRedo = this.historyIndex < this.history.length - 1;
        },
        
        // Project methods
        async saveProject() {
            try {
                const iframe = document.getElementById('canvasFrame');
                const canvasHTML = iframe.contentWindow.document.body.innerHTML;
                
                const formData = new FormData();
                formData.append('title', this.projectTitle);
                formData.append('canvas', canvasHTML);
                if (this.projectId) {
                    formData.append('id', this.projectId);
                }
                
                const response = await fetch('api/project_save.php', {
                    method: 'POST',
                    credentials: 'include',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    this.projectId = data.id;
                    this.showToast('Project saved successfully', 'success');
                } else {
                    throw new Error(data.error || 'Save failed');
                }
            } catch (error) {
                this.showToast('Failed to save project: ' + error.message, 'error');
            }
        },
        
        previewProject() {
            window.open(`preview.php?id=${this.projectId}`, '_blank');
        },
        
        deployProject() {
            window.location.href = `deploy.php?id=${this.projectId}`;
        },
        
        // Toast methods
        showToast(message, type = 'info') {
            const id = ++this.toastId;
            const toast = { id, message, type, show: true };
            this.toasts.push(toast);
            
            setTimeout(() => {
                this.removeToast(id);
            }, 5000);
        },
        
        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index > -1) {
                this.toasts[index].show = false;
                setTimeout(() => {
                    this.toasts.splice(index, 1);
                }, 300);
            }
        }
    }
}
</script>

<!-- Editor JavaScript Modules -->
<script src="assets/js/editor-canvas.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/editor-library.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/editor-inspector.js?v=<?php echo time(); ?>"></script>

</body>
</html>
