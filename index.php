<?php
/**
 * JSB Website Builder - Main Editor
 * Completely restructured modern website builder with:
 * - Webflow-style layout (library left, canvas center, inspector right)
 * - No jQuery dependency (pure vanilla JS)
 * - No Bootstrap/Tailwind in editor UI
 * - Mobile responsive
 * - Comprehensive element library
 * - Visual box model controls
 */

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/language.php';
start_session_if_needed();

// Initialize language system
LanguageManager::init();
header('Content-Type: text/html; charset=utf-8');

// Determine current user
$uid = current_user_id();

// If not logged in — redirect to landing page
if (!$uid) {
    header('Location: landing.php');
    exit;
}

// User is logged in: render the builder UI directly
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
<?php include __DIR__ . '/components/loading-screen.php'; ?>
<!DOCTYPE html>
<html lang="en" class="jsb-editor">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="robots" content="noindex, nofollow">
    <base href="<?php echo $basePath; ?>">
    <title><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> — Website Builder</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Editor Styles (NO Bootstrap, NO Tailwind CDN) -->
    <link rel="stylesheet" href="assets/css/editor.css?v=<?php echo time(); ?>">
    
    <!-- Minimal legacy support for existing modal styles -->
    <style>
        /* Modal Styles (from old system, will be refactored) */
        .modal {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #757575;
            min-width: 44px;
            min-height: 44px;
        }
        .modal-body {
            margin-bottom: 16px;
        }
        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        .button {
            padding: 12px 24px;
            min-height: 44px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
        }
        .button.primary {
            background: #3B82F6;
            color: white;
        }
        .button.primary:hover {
            background: #2563EB;
        }
        .button.stroke {
            background: white;
            border: 1px solid #E0E0E0;
            color: #424242;
        }
        .button.stroke:hover {
            background: #F5F5F5;
        }
        input[type="text"], input[type="email"], input[type="url"], select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #E0E0E0;
            border-radius: 6px;
            font-size: 14px;
            margin: 8px 0;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #3B82F6;
        }
    </style>
</head>
<body>

<!-- Main Editor Layout -->
<div class="jsb-editor__layout">
    
    <!-- TOPBAR -->
    <div class="jsb-editor__topbar">
        <div class="jsb-editor__topbar-left">
            <a href="profile.php" class="jsb-editor__logo" title="Home">
                <img src="images/icons/logo.svg" alt="Logo" class="jsb-editor__logo-icon">
                <span class="jsb-editor__logo-text"><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
            </a>
            <span class="jsb-editor__project-name" id="projectName"><?php echo $projectTitle; ?></span>
        </div>
        
        <div class="jsb-editor__topbar-center">
            <!-- Viewport Switcher -->
            <div style="display: flex; gap: 4px; background: rgba(255,255,255,0.1); padding: 4px; border-radius: 6px;">
                <button type="button" class="jsb-btn jsb-btn--icon jsb-btn--ghost" id="viewportDesktop" title="Desktop view" aria-label="Desktop view" style="color: white;">
                    <svg class="jsb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </button>
                <button type="button" class="jsb-btn jsb-btn--icon jsb-btn--ghost" id="viewportTablet" title="Tablet view" aria-label="Tablet view" style="color: rgba(255,255,255,0.7);">
                    <svg class="jsb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2"/>
                        <line x1="12" y1="18" x2="12" y2="18"/>
                    </svg>
                </button>
                <button type="button" class="jsb-btn jsb-btn--icon jsb-btn--ghost" id="viewportMobile" title="Mobile view" aria-label="Mobile view" style="color: rgba(255,255,255,0.7);">
                    <svg class="jsb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="7" y="2" width="10" height="20" rx="2"/>
                        <line x1="12" y1="18" x2="12" y2="18"/>
                    </svg>
                </button>
            </div>
            
            <!-- Zoom Controls -->
            <div style="display: flex; gap: 4px; align-items: center; background: rgba(255,255,255,0.1); padding: 4px; border-radius: 6px;">
                <button type="button" class="jsb-btn jsb-btn--icon jsb-btn--sm jsb-btn--ghost" id="zoomOut" title="Zoom out" aria-label="Zoom out" style="color: white;">−</button>
                <span style="color: white; font-size: 12px; min-width: 40px; text-align: center;" id="zoomLevel">100%</span>
                <button type="button" class="jsb-btn jsb-btn--icon jsb-btn--sm jsb-btn--ghost" id="zoomIn" title="Zoom in" aria-label="Zoom in" style="color: white;">+</button>
            </div>
            
            <!-- Undo/Redo (placeholder for future) -->
            <div style="display: flex; gap: 4px;">
                <button type="button" class="jsb-btn jsb-btn--icon jsb-btn--ghost" id="undoBtn" title="Undo (Ctrl+Z)" aria-label="Undo" style="color: rgba(255,255,255,0.5);" disabled>
                    <svg class="jsb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 7v6h6"/><path d="M21 17a9 9 0 00-9-9 9 9 0 00-6 2.3L3 13"/>
                    </svg>
                </button>
                <button type="button" class="jsb-btn jsb-btn--icon jsb-btn--ghost" id="redoBtn" title="Redo (Ctrl+Shift+Z)" aria-label="Redo" style="color: rgba(255,255,255,0.5);" disabled>
                    <svg class="jsb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 7v6h-6"/><path d="M3 17a9 9 0 019-9 9 9 0 016 2.3l3 2.7"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="jsb-editor__topbar-right">
            <button type="button" class="jsb-btn jsb-btn--sm jsb-btn--ghost" id="saveBtn" title="Save project" style="color: white;">
                <svg class="jsb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                <span>Save</span>
            </button>
            <button type="button" class="jsb-btn jsb-btn--sm jsb-btn--secondary" id="previewBtn" title="Preview" style="background: rgba(255,255,255,0.15); color: white; border: none;">
                <svg class="jsb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <span>Preview</span>
            </button>
            <button type="button" class="jsb-btn jsb-btn--sm jsb-btn--primary" id="deployBtn" title="Deploy project">
                <svg class="jsb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                <span>Deploy</span>
            </button>
            
            <!-- User Menu -->
            <a href="profile.php" class="jsb-btn jsb-btn--icon jsb-btn--ghost" title="<?php echo $displayName ?: 'Profile'; ?>" style="color: white;">
                <svg class="jsb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </a>
        </div>
    </div>
    
    <!-- LEFT SIDEBAR (Library) -->
    <div class="jsb-editor__sidebar jsb-editor__library" id="libraryPanel">
        <div class="jsb-editor__sidebar-header">
            <h2 class="jsb-editor__sidebar-title">Elements</h2>
            <p class="jsb-editor__sidebar-subtitle">Drag or click to add</p>
        </div>
        <div class="jsb-editor__sidebar-body">
            <!-- Library content will be injected by JS -->
        </div>
    </div>
    
    <!-- CANVAS (Center) -->
    <div class="jsb-editor__canvas-wrapper">
        <div class="jsb-editor__canvas" id="editorCanvas">
            <div class="jsb-editor__canvas-content" data-canvas-content data-viewport="desktop" id="canvasContent">
                <!-- User elements will be added here -->
                <div style="text-align: center; padding: 64px 24px; color: #9E9E9E;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin: 0 auto 16px;">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M9 21V9"/>
                    </svg>
                    <p>Add elements from the library on the left</p>
                    <p style="font-size: 14px; margin-top: 8px;">Or press <kbd style="background: #F5F5F5; padding: 2px 6px; border-radius: 4px; border: 1px solid #E0E0E0;">A</kbd> to toggle library</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- RIGHT SIDEBAR (Inspector) -->
    <div class="jsb-editor__sidebar jsb-editor__sidebar--right jsb-editor__inspector" id="inspectorPanel">
        <div class="jsb-editor__sidebar-header">
            <h2 class="jsb-editor__sidebar-title">Inspector</h2>
            <p class="jsb-editor__sidebar-subtitle">Edit selected element</p>
        </div>
        <div class="jsb-editor__sidebar-body">
            <!-- Inspector content will be injected by JS -->
        </div>
    </div>
    
</div>

<!-- MODALS (Legacy system, to be refactored) -->

<!-- Save Project Modal -->
<div id="saveModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Save Project</h3>
            <button type="button" class="modal-close" id="saveModalClose">&times;</button>
        </div>
        <div class="modal-body">
            <p>Enter a name for your project:</p>
            <input type="text" id="projectNameInput" placeholder="My Awesome Website" maxlength="100" autofocus>
        </div>
        <div class="modal-actions">
            <button type="button" class="button stroke" id="saveModalCancel">Cancel</button>
            <button type="button" class="button primary" id="saveModalConfirm">Save</button>
        </div>
    </div>
</div>

<!-- Deploy Modal -->
<div id="deployModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Deploy Project</h3>
            <button type="button" class="modal-close" id="deployModalClose">&times;</button>
        </div>
        <div class="modal-body">
            <p>Deploy your project and make it live:</p>
            <label>Project Name:</label>
            <input type="text" id="deployProjectName" placeholder="My Website" value="<?php echo $projectTitle; ?>">
            
            <label>URL Slug:</label>
            <input type="text" id="deployProjectSlug" placeholder="my-website" pattern="[a-z0-9-]+">
            <small style="color: #757575; font-size: 12px;">Only lowercase letters, numbers, and hyphens</small>
            
            <label>Privacy:</label>
            <select id="deployPrivacy">
                <option value="public">Public (visible to everyone)</option>
                <option value="unlisted">Unlisted (only with link)</option>
                <option value="private">Private (only you)</option>
            </select>
        </div>
        <div class="modal-actions">
            <button type="button" class="button stroke" id="deployModalCancel">Cancel</button>
            <button type="button" class="button primary" id="deployModalConfirm">Deploy</button>
        </div>
    </div>
</div>

<!-- JavaScript Modules (NO jQuery) -->
<script src="assets/js/editor-canvas.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/editor-library.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/editor-inspector.js?v=<?php echo time(); ?>"></script>

<script>
// Main Editor Application (Pure Vanilla JS)
(function() {
    'use strict';
    
    // Initialize modules
    const canvas = new EditorCanvas(document.getElementById('editorCanvas'));
    const library = new EditorLibrary(document.getElementById('libraryPanel'));
    const inspector = new EditorInspector(document.getElementById('inspectorPanel'));
    
    let currentProjectId = <?php echo $projectId ? $projectId : 'null'; ?>;
    let currentProjectTitle = '<?php echo addslashes($projectTitle); ?>';
    let hasUnsavedChanges = false;
    
    // Viewport switching
    document.getElementById('viewportDesktop').addEventListener('click', () => {
        setViewport('desktop');
    });
    
    document.getElementById('viewportTablet').addEventListener('click', () => {
        setViewport('tablet');
    });
    
    document.getElementById('viewportMobile').addEventListener('click', () => {
        setViewport('mobile');
    });
    
    function setViewport(viewport) {
        canvas.setViewport(viewport);
        
        // Update button states
        document.getElementById('viewportDesktop').style.color = viewport === 'desktop' ? 'white' : 'rgba(255,255,255,0.7)';
        document.getElementById('viewportTablet').style.color = viewport === 'tablet' ? 'white' : 'rgba(255,255,255,0.7)';
        document.getElementById('viewportMobile').style.color = viewport === 'mobile' ? 'white' : 'rgba(255,255,255,0.7)';
    }
    
    // Zoom controls
    document.getElementById('zoomIn').addEventListener('click', () => {
        const newZoom = canvas.zoom + 0.1;
        canvas.setZoom(newZoom);
        updateZoomDisplay();
    });
    
    document.getElementById('zoomOut').addEventListener('click', () => {
        const newZoom = canvas.zoom - 0.1;
        canvas.setZoom(newZoom);
        updateZoomDisplay();
    });
    
    function updateZoomDisplay() {
        document.getElementById('zoomLevel').textContent = Math.round(canvas.zoom * 100) + '%';
    }
    
    // Listen for element addition from library
    document.addEventListener('library:elementAdd', (e) => {
        const type = e.detail.type;
        canvas.addElement(type);
        hasUnsavedChanges = true;
    });
    
    // Listen for template addition from library
    document.addEventListener('library:templateAdd', (e) => {
        const templateId = e.detail.templateId;
        loadTemplate(templateId);
        hasUnsavedChanges = true;
    });
    
    // Track changes
    document.addEventListener('canvas:elementAdded', () => {
        hasUnsavedChanges = true;
    });
    
    document.addEventListener('inspector:propertyChanged', () => {
        hasUnsavedChanges = true;
    });
    
    // Save button
    document.getElementById('saveBtn').addEventListener('click', () => {
        showSaveModal();
    });
    
    function showSaveModal() {
        const modal = document.getElementById('saveModal');
        const input = document.getElementById('projectNameInput');
        input.value = currentProjectTitle;
        modal.classList.add('active');
        input.focus();
    }
    
    document.getElementById('saveModalClose').addEventListener('click', () => {
        document.getElementById('saveModal').classList.remove('active');
    });
    
    document.getElementById('saveModalCancel').addEventListener('click', () => {
        document.getElementById('saveModal').classList.remove('active');
    });
    
    document.getElementById('saveModalConfirm').addEventListener('click', () => {
        const title = document.getElementById('projectNameInput').value.trim();
        if (!title) {
            alert('Please enter a project name');
            return;
        }
        saveProject(title);
    });
    
    async function saveProject(title) {
        try {
            const canvasHTML = document.getElementById('canvasContent').innerHTML;
            
            const formData = new FormData();
            formData.append('title', title);
            formData.append('canvas', canvasHTML);
            if (currentProjectId) {
                formData.append('id', currentProjectId);
            }
            
            const response = await fetch('api/project_save.php', {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('Save failed: ' + response.status);
            }
            
            const data = await response.json();
            
            if (data.ok) {
                currentProjectId = data.id;
                currentProjectTitle = title;
                hasUnsavedChanges = false;
                document.getElementById('projectName').textContent = title;
                document.getElementById('saveModal').classList.remove('active');
                showNotification('Project saved successfully!', 'success');
            } else {
                throw new Error(data.error || 'Save failed');
            }
        } catch (error) {
            console.error('Save error:', error);
            showNotification('Failed to save project: ' + error.message, 'error');
        }
    }
    
    // Deploy button
    document.getElementById('deployBtn').addEventListener('click', () => {
        if (!currentProjectId) {
            showNotification('Please save your project first', 'warning');
            return;
        }
        showDeployModal();
    });
    
    function showDeployModal() {
        const modal = document.getElementById('deployModal');
        document.getElementById('deployProjectName').value = currentProjectTitle;
        modal.classList.add('active');
    }
    
    document.getElementById('deployModalClose').addEventListener('click', () => {
        document.getElementById('deployModal').classList.remove('active');
    });
    
    document.getElementById('deployModalCancel').addEventListener('click', () => {
        document.getElementById('deployModal').classList.remove('active');
    });
    
    document.getElementById('deployModalConfirm').addEventListener('click', async () => {
        const name = document.getElementById('deployProjectName').value.trim();
        const slug = document.getElementById('deployProjectSlug').value.trim();
        const privacy = document.getElementById('deployPrivacy').value;
        
        if (!name || !slug) {
            alert('Please fill in all fields');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('id', currentProjectId);
            formData.append('title', name);
            formData.append('slug', slug);
            formData.append('privacy', privacy);
            
            const response = await fetch('api/project_deploy.php', {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.ok || data.success) {
                document.getElementById('deployModal').classList.remove('active');
                showNotification('Project deployed successfully!', 'success');
            } else {
                throw new Error(data.error || data.message || 'Deploy failed');
            }
        } catch (error) {
            console.error('Deploy error:', error);
            showNotification('Failed to deploy: ' + error.message, 'error');
        }
    });
    
    // Notification system
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        document.querySelectorAll('.jsb-notification').forEach(n => n.remove());
        
        const colors = {
            success: '#10B981',
            error: '#EF4444',
            warning: '#F59E0B',
            info: '#3B82F6'
        };
        
        const notification = document.createElement('div');
        notification.className = 'jsb-notification';
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 24px;
            background: ${colors[type] || colors.info};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 10001;
            font-size: 14px;
            font-weight: 500;
            max-width: 300px;
            word-wrap: break-word;
            animation: slideIn 0.3s ease-out;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
    
    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + S = Save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            showSaveModal();
        }
        
        // A = Toggle library (when no input focused)
        if (e.key === 'a' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            document.getElementById('libraryPanel').classList.toggle('jsb-editor__library--open');
        }
        
        // I = Toggle inspector (when no input focused)
        if (e.key === 'i' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            document.getElementById('inspectorPanel').classList.toggle('jsb-editor__inspector--open');
        }
    });
    
    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', (e) => {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
    
    // Load existing project if projectId is set
    if (currentProjectId) {
        loadProject(currentProjectId);
    }
    
    async function loadProject(id) {
        try {
            const response = await fetch('api/project_load_editor.php?id=' + id, {
                credentials: 'include'
            });
            
            if (!response.ok) {
                throw new Error('Load failed');
            }
            
            const data = await response.json();
            
            if (data.ok && data.project && data.project.canvas) {
                document.getElementById('canvasContent').innerHTML = data.project.canvas;
                hasUnsavedChanges = false;
                showNotification('Project loaded successfully!', 'success');
            } else {
                throw new Error(data.error || 'No canvas content');
            }
        } catch (error) {
            console.error('Load error:', error);
            showNotification('Failed to load project: ' + error.message, 'error');
        }
    }
    
    function loadTemplate(templateId) {
        const templates = {
            'hero-landing': `
                <div data-element-id="hero_${Date.now()}" data-element-type="container" style="padding: 80px 24px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h1 style="font-size: 48px; margin-bottom: 24px; font-weight: 700;">Welcome to Our Service</h1>
                    <p style="font-size: 20px; margin-bottom: 32px; max-width: 600px; margin-left: auto; margin-right: auto;">Build amazing websites with our powerful drag-and-drop builder</p>
                    <button style="padding: 16px 32px; background: white; color: #667eea; border: none; border-radius: 8px; font-size: 18px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">Get Started</button>
                </div>
            `,
            'service-page': `
                <div data-element-id="header_${Date.now()}" data-element-type="container" style="padding: 48px 24px; text-align: center; background: #f8f9fa;">
                    <h1 style="font-size: 36px; margin-bottom: 16px; font-weight: 700; color: #1a1a1a;">Our Services</h1>
                    <p style="font-size: 18px; color: #757575;">We provide comprehensive solutions for your business</p>
                </div>
                <div data-element-id="services_${Date.now()}" data-element-type="row" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; padding: 48px 24px; max-width: 1200px; margin: 0 auto;">
                    <div style="padding: 32px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                        <div style="width: 64px; height: 64px; background: #3B82F6; border-radius: 50%; margin: 0 auto 16px;"></div>
                        <h3 style="font-size: 20px; margin-bottom: 12px; font-weight: 600;">Web Design</h3>
                        <p style="color: #757575; font-size: 14px;">Beautiful and responsive websites</p>
                    </div>
                    <div style="padding: 32px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                        <div style="width: 64px; height: 64px; background: #10B981; border-radius: 50%; margin: 0 auto 16px;"></div>
                        <h3 style="font-size: 20px; margin-bottom: 12px; font-weight: 600;">Development</h3>
                        <p style="color: #757575; font-size: 14px;">Robust and scalable solutions</p>
                    </div>
                    <div style="padding: 32px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                        <div style="width: 64px; height: 64px; background: #F59E0B; border-radius: 50%; margin: 0 auto 16px;"></div>
                        <h3 style="font-size: 20px; margin-bottom: 12px; font-weight: 600;">Marketing</h3>
                        <p style="color: #757575; font-size: 14px;">Grow your online presence</p>
                    </div>
                </div>
            `,
            'contact-form': `
                <div data-element-id="contact_header_${Date.now()}" data-element-type="container" style="padding: 48px 24px; text-align: center; background: #f8f9fa;">
                    <h1 style="font-size: 36px; margin-bottom: 16px; font-weight: 700; color: #1a1a1a;">Contact Us</h1>
                    <p style="font-size: 18px; color: #757575;">We'd love to hear from you</p>
                </div>
                <div data-element-id="contact_form_${Date.now()}" data-element-type="form" style="max-width: 600px; margin: 48px auto; padding: 0 24px;">
                    <form style="display: flex; flex-direction: column; gap: 16px;">
                        <input type="text" placeholder="Your Name" style="padding: 12px 16px; border: 1px solid #E0E0E0; border-radius: 6px; font-size: 16px;">
                        <input type="email" placeholder="Your Email" style="padding: 12px 16px; border: 1px solid #E0E0E0; border-radius: 6px; font-size: 16px;">
                        <textarea placeholder="Your Message" rows="5" style="padding: 12px 16px; border: 1px solid #E0E0E0; border-radius: 6px; font-size: 16px; resize: vertical;"></textarea>
                        <button type="submit" style="padding: 16px 32px; background: #3B82F6; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer;">Send Message</button>
                    </form>
                </div>
            `
        };
        
        if (templates[templateId]) {
            const canvasContent = document.getElementById('canvasContent');
            canvasContent.innerHTML = templates[templateId];
            showNotification('Template loaded successfully!', 'success');
        }
    }
    
    // Preview button
    document.getElementById('previewBtn').addEventListener('click', () => {
        const canvasContent = document.getElementById('canvasContent').innerHTML;
        const previewWindow = window.open('', '_blank');
        previewWindow.document.write(`
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Preview - ${currentProjectTitle}</title>
                <style>
                    body { margin: 0; padding: 24px; font-family: Inter, system-ui, -apple-system, sans-serif; }
                    * { box-sizing: border-box; }
                </style>
            </head>
            <body>
                ${canvasContent}
            </body>
            </html>
        `);
        previewWindow.document.close();
    });
    
    console.log('✅ JSB Website Builder initialized');
    console.log('📦 Modules loaded: Canvas, Library, Inspector');
    console.log('⌨️ Shortcuts: Ctrl+S (save), A (library), I (inspector)');
    
})();
</script>

</body>
</html>
