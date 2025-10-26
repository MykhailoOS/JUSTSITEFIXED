<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/language.php';

// Initialize language system
LanguageManager::init();

start_session_if_needed();
header('Content-Type: text/html; charset=utf-8');

// Check if user is logged in
$uid = current_user_id();
if (!$uid) {
    header('Location: login.php');
    exit;
}

// Simple admin check
$pdo = DatabaseConnectionProvider::getConnection();
$stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();

$isAdmin = $user && (
    strpos($user['email'], 'admin') !== false || 
    $user['email'] === 'admin@justsite.com' ||
    $uid == 1
);

if (!$isAdmin) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><title>403 - Access Denied</title></head><body><h1>403 - Access Denied</h1><p>Admin access required.</p></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Templates — JustSite</title>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- DaisyUI -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.19/dist/full.min.css" rel="stylesheet" type="text/css" />
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Handlebars -->
    <script src="https://cdn.jsdelivr.net/npm/handlebars@latest/dist/handlebars.min.js"></script>
    
    <!-- Material Design Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        /* Custom animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        
        /* Template preview styles */
        .template-preview {
            border: 2px dashed #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .template-preview:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }
        
        /* Code editor styles */
        .code-editor {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        
        /* Handlebars syntax highlighting */
        .handlebars-syntax {
            color: #e11d48;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="templatesApp()">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="bi bi-layout-text-window-reverse text-white text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Templates</h1>
                        <p class="text-sm text-gray-500">Handlebars Template Engine</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button @click="refreshTemplates()" class="btn btn-ghost btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                        Refresh
                    </button>
                    <a href="admin.php" class="btn btn-ghost btn-sm">
                        <i class="bi bi-arrow-left"></i>
                        Back to Admin
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <i class="bi bi-file-earmark-code text-2xl"></i>
                    </div>
                    <div class="stat-title">Total Templates</div>
                    <div class="stat-value text-primary" x-text="templates.length">0</div>
                </div>
            </div>
            
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <i class="bi bi-play-circle text-2xl"></i>
                    </div>
                    <div class="stat-title">Active Templates</div>
                    <div class="stat-value text-secondary" x-text="activeTemplates">0</div>
                </div>
            </div>
            
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-accent">
                        <i class="bi bi-download text-2xl"></i>
                    </div>
                    <div class="stat-title">Downloads</div>
                    <div class="stat-value text-accent" x-text="totalDownloads">0</div>
                </div>
            </div>
            
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-info">
                        <i class="bi bi-people text-2xl"></i>
                    </div>
                    <div class="stat-title">Users</div>
                    <div class="stat-value text-info" x-text="totalUsers">0</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-4 mb-8">
            <button @click="showCreateModal = true" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Create New Template
            </button>
            <button @click="showImportModal = true" class="btn btn-secondary">
                <i class="bi bi-upload"></i>
                Import Template
            </button>
            <button @click="exportTemplates()" class="btn btn-outline">
                <i class="bi bi-download"></i>
                Export All
            </button>
        </div>

        <!-- Templates Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-show="!loading">
            <template x-for="template in templates" :key="template.id">
                <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300">
                    <div class="card-body">
                        <div class="flex items-start justify-between mb-4">
                            <h2 class="card-title" x-text="template.name"></h2>
                            <div class="dropdown dropdown-end">
                                <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </div>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                    <li><a @click="editTemplate(template)"><i class="bi bi-pencil"></i> Edit</a></li>
                                    <li><a @click="previewTemplate(template)"><i class="bi bi-eye"></i> Preview</a></li>
                                    <li><a @click="duplicateTemplate(template)"><i class="bi bi-files"></i> Duplicate</a></li>
                                    <li><a @click="downloadTemplate(template)"><i class="bi bi-download"></i> Download</a></li>
                                    <li><a @click="deleteTemplate(template)" class="text-error"><i class="bi bi-trash"></i> Delete</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <p class="text-gray-600 text-sm mb-4" x-text="template.description"></p>
                        
                        <div class="flex flex-wrap gap-2 mb-4">
                            <div class="badge badge-primary" x-text="template.category"></div>
                            <div class="badge badge-secondary" x-text="template.type"></div>
                            <div class="badge badge-accent" x-text="template.status"></div>
                        </div>
                        
                        <div class="card-actions justify-between">
                            <div class="text-sm text-gray-500">
                                <i class="bi bi-calendar"></i>
                                <span x-text="formatDate(template.created_at)"></span>
                            </div>
                            <button @click="previewTemplate(template)" class="btn btn-primary btn-sm">
                                <i class="bi bi-play"></i>
                                Preview
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <span class="loading loading-spinner loading-lg"></span>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && templates.length === 0" class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-file-earmark-code text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Templates Found</h3>
            <p class="text-gray-500 mb-4">Create your first template to get started</p>
            <button @click="showCreateModal = true" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Create Template
            </button>
        </div>
    </main>

    <!-- Create/Edit Template Modal -->
    <div x-show="showCreateModal || showEditModal" class="modal modal-open">
        <div class="modal-box w-11/12 max-w-6xl">
            <h3 class="font-bold text-lg mb-4">
                <span x-text="showEditModal ? 'Edit Template' : 'Create New Template'"></span>
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Template Form -->
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Template Name</span>
                        </label>
                        <input type="text" x-model="currentTemplate.name" class="input input-bordered" placeholder="Enter template name">
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Description</span>
                        </label>
                        <textarea x-model="currentTemplate.description" class="textarea textarea-bordered" placeholder="Enter template description"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Category</span>
                            </label>
                            <select x-model="currentTemplate.category" class="select select-bordered">
                                <option value="landing">Landing Page</option>
                                <option value="blog">Blog</option>
                                <option value="portfolio">Portfolio</option>
                                <option value="ecommerce">E-commerce</option>
                                <option value="corporate">Corporate</option>
                            </select>
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Type</span>
                            </label>
                            <select x-model="currentTemplate.type" class="select select-bordered">
                                <option value="html">HTML</option>
                                <option value="component">Component</option>
                                <option value="layout">Layout</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Template Code (Handlebars)</span>
                        </label>
                        <textarea x-model="currentTemplate.template" class="textarea textarea-bordered code-editor h-64" placeholder="Enter Handlebars template code..."></textarea>
                    </div>
                </div>
                
                <!-- Preview -->
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Sample Data (JSON)</span>
                        </label>
                        <textarea x-model="currentTemplate.sampleData" class="textarea textarea-bordered code-editor h-32" placeholder='{"title": "Hello World", "content": "Sample content"}'></textarea>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Preview</span>
                        </label>
                        <div class="template-preview bg-white border-2 border-dashed border-gray-300 rounded-lg p-4 h-64 overflow-auto">
                            <div x-html="previewHtml"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-action">
                <button @click="closeModals()" class="btn btn-ghost">Cancel</button>
                <button @click="saveTemplate()" class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    Save Template
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div x-show="showPreviewModal" class="modal modal-open">
        <div class="modal-box w-11/12 max-w-4xl">
            <h3 class="font-bold text-lg mb-4">Template Preview</h3>
            
            <div class="tabs">
                <a class="tab tab-bordered" :class="previewTab === 'rendered' ? 'tab-active' : ''" @click="previewTab = 'rendered'">Rendered HTML</a>
                <a class="tab tab-bordered" :class="previewTab === 'source' ? 'tab-active' : ''" @click="previewTab = 'source'">Source Code</a>
            </div>
            
            <div class="mt-4">
                <div x-show="previewTab === 'rendered'" class="border rounded-lg p-4 bg-white">
                    <div x-html="previewHtml"></div>
                </div>
                
                <div x-show="previewTab === 'source'" class="border rounded-lg p-4 bg-gray-900 text-white">
                    <pre class="code-editor"><code x-text="previewHtml"></code></pre>
                </div>
            </div>
            
            <div class="modal-action">
                <button @click="showPreviewModal = false" class="btn btn-ghost">Close</button>
                <button @click="downloadPreview()" class="btn btn-primary">
                    <i class="bi bi-download"></i>
                    Download HTML
                </button>
            </div>
        </div>
    </div>

    <script>
        function templatesApp() {
            return {
                // State
                templates: [],
                loading: true,
                showCreateModal: false,
                showEditModal: false,
                showPreviewModal: false,
                showImportModal: false,
                previewTab: 'rendered',
                
                currentTemplate: {
                    id: null,
                    name: '',
                    description: '',
                    category: 'landing',
                    type: 'html',
                    template: '',
                    sampleData: '{"title": "Hello World", "content": "Sample content"}',
                    status: 'active'
                },
                
                // Computed
                get activeTemplates() {
                    return this.templates.filter(t => t.status === 'active').length;
                },
                
                get totalDownloads() {
                    return this.templates.reduce((sum, t) => sum + (t.downloads || 0), 0);
                },
                
                get totalUsers() {
                    return this.templates.reduce((sum, t) => sum + (t.users || 0), 0);
                },
                
                get previewHtml() {
                    if (!this.currentTemplate.template || !this.currentTemplate.sampleData) {
                        return '<p class="text-gray-500">Enter template code and sample data to see preview</p>';
                    }
                    
                    try {
                        const template = Handlebars.compile(this.currentTemplate.template);
                        const data = JSON.parse(this.currentTemplate.sampleData);
                        return template(data);
                    } catch (error) {
                        return `<p class="text-red-500">Error: ${error.message}</p>`;
                    }
                },
                
                // Methods
                async init() {
                    await this.loadTemplates();
                },
                
                async loadTemplates() {
                    this.loading = true;
                    try {
                        const response = await fetch('api/templates.php?action=list');
                        const data = await response.json();
                        this.templates = data.templates || [];
                    } catch (error) {
                        console.error('Error loading templates:', error);
                        this.templates = [];
                    } finally {
                        this.loading = false;
                    }
                },
                
                async refreshTemplates() {
                    await this.loadTemplates();
                },
                
                editTemplate(template) {
                    this.currentTemplate = { ...template };
                    this.showEditModal = true;
                },
                
                previewTemplate(template) {
                    this.currentTemplate = { ...template };
                    this.showPreviewModal = true;
                },
                
                async duplicateTemplate(template) {
                    const newTemplate = {
                        ...template,
                        id: null,
                        name: template.name + ' (Copy)',
                        created_at: new Date().toISOString()
                    };
                    
                    try {
                        const response = await fetch('api/templates.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'create', template: newTemplate })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            await this.loadTemplates();
                            this.showNotification('Template duplicated successfully', 'success');
                        }
                    } catch (error) {
                        this.showNotification('Error duplicating template', 'error');
                    }
                },
                
                async downloadTemplate(template) {
                    try {
                        const response = await fetch(`api/templates.php?action=download&id=${template.id}`);
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `${template.name}.html`;
                        a.click();
                        window.URL.revokeObjectURL(url);
                    } catch (error) {
                        this.showNotification('Error downloading template', 'error');
                    }
                },
                
                async deleteTemplate(template) {
                    if (!confirm(`Are you sure you want to delete "${template.name}"?`)) return;
                    
                    try {
                        const response = await fetch('api/templates.php', {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: template.id })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            await this.loadTemplates();
                            this.showNotification('Template deleted successfully', 'success');
                        }
                    } catch (error) {
                        this.showNotification('Error deleting template', 'error');
                    }
                },
                
                async saveTemplate() {
                    try {
                        const response = await fetch('api/templates.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: this.showEditModal ? 'update' : 'create',
                                template: this.currentTemplate
                            })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            await this.loadTemplates();
                            this.closeModals();
                            this.showNotification('Template saved successfully', 'success');
                        } else {
                            this.showNotification(result.message || 'Error saving template', 'error');
                        }
                    } catch (error) {
                        this.showNotification('Error saving template', 'error');
                    }
                },
                
                async exportTemplates() {
                    try {
                        const response = await fetch('api/templates.php?action=export');
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'templates-export.json';
                        a.click();
                        window.URL.revokeObjectURL(url);
                    } catch (error) {
                        this.showNotification('Error exporting templates', 'error');
                    }
                },
                
                downloadPreview() {
                    const blob = new Blob([this.previewHtml], { type: 'text/html' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `${this.currentTemplate.name || 'template'}.html`;
                    a.click();
                    window.URL.revokeObjectURL(url);
                },
                
                closeModals() {
                    this.showCreateModal = false;
                    this.showEditModal = false;
                    this.showPreviewModal = false;
                    this.showImportModal = false;
                    this.currentTemplate = {
                        id: null,
                        name: '',
                        description: '',
                        category: 'landing',
                        type: 'html',
                        template: '',
                        sampleData: '{"title": "Hello World", "content": "Sample content"}',
                        status: 'active'
                    };
                },
                
                formatDate(dateString) {
                    return new Date(dateString).toLocaleDateString();
                },
                
                showNotification(message, type) {
                    // Simple notification - you can enhance this
                    alert(`${type.toUpperCase()}: ${message}`);
                }
            }
        }
    </script>
</body>
</html>
