<?php
// Template Selector Component for Constructor Integration
// This component provides a modal interface for selecting and inserting templates
?>

<div id="template-selector-modal" class="modal" style="display: none;">
    <div class="modal-box w-11/12 max-w-6xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Choose Template</h3>
            <button onclick="closeTemplateSelector()" class="btn btn-sm btn-circle btn-ghost">✕</button>
        </div>
        
        <!-- Search and Filters -->
        <div class="flex flex-wrap gap-4 mb-6">
            <div class="flex-1 min-w-64">
                <input 
                    type="text" 
                    id="template-search" 
                    placeholder="Search templates..." 
                    class="input input-bordered w-full"
                    onkeyup="searchTemplates()"
                >
            </div>
            <select id="template-category" class="select select-bordered" onchange="filterTemplates()">
                <option value="">All Categories</option>
                <option value="landing">Landing Page</option>
                <option value="blog">Blog</option>
                <option value="portfolio">Portfolio</option>
                <option value="ecommerce">E-commerce</option>
                <option value="corporate">Corporate</option>
            </select>
            <select id="template-type" class="select select-bordered" onchange="filterTemplates()">
                <option value="">All Types</option>
                <option value="html">Full Page</option>
                <option value="component">Component</option>
                <option value="layout">Layout</option>
            </select>
        </div>
        
        <!-- Loading State -->
        <div id="templates-loading" class="flex justify-center py-8">
            <span class="loading loading-spinner loading-lg"></span>
        </div>
        
        <!-- Templates Grid -->
        <div id="templates-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" style="display: none;">
            <!-- Templates will be loaded here -->
        </div>
        
        <!-- Empty State -->
        <div id="templates-empty" class="text-center py-8" style="display: none;">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-file-earmark-code text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Templates Found</h3>
            <p class="text-gray-500">Try adjusting your search or filters</p>
        </div>
        
        <!-- Template Preview Modal -->
        <div id="template-preview-modal" class="modal" style="display: none;">
            <div class="modal-box w-11/12 max-w-4xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Template Preview</h3>
                    <button onclick="closeTemplatePreview()" class="btn btn-sm btn-circle btn-ghost">✕</button>
                </div>
                
                <div class="tabs">
                    <a class="tab tab-bordered tab-active" onclick="showPreviewTab('rendered')">Preview</a>
                    <a class="tab tab-bordered" onclick="showPreviewTab('code')">Code</a>
                    <a class="tab tab-bordered" onclick="showPreviewTab('data')">Data</a>
                </div>
                
                <div class="mt-4">
                    <div id="preview-rendered" class="preview-content">
                        <div class="border rounded-lg p-4 bg-white max-h-96 overflow-auto">
                            <div id="preview-html"></div>
                        </div>
                    </div>
                    
                    <div id="preview-code" class="preview-content" style="display: none;">
                        <div class="border rounded-lg p-4 bg-gray-900 text-white max-h-96 overflow-auto">
                            <pre><code id="preview-code-content"></code></pre>
                        </div>
                    </div>
                    
                    <div id="preview-data" class="preview-content" style="display: none;">
                        <div class="border rounded-lg p-4 bg-white max-h-96 overflow-auto">
                            <pre><code id="preview-data-content" class="text-sm"></code></pre>
                        </div>
                    </div>
                </div>
                
                <div class="modal-action">
                    <button onclick="closeTemplatePreview()" class="btn btn-ghost">Cancel</button>
                    <button onclick="insertSelectedTemplate()" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i>
                        Insert Template
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Template Selector JavaScript
let templates = [];
let filteredTemplates = [];
let selectedTemplate = null;
let currentProjectId = null;

// Initialize template selector
function initTemplateSelector(projectId) {
    currentProjectId = projectId;
    loadTemplates();
}

// Load templates from API
async function loadTemplates() {
    try {
        showLoading(true);
        const response = await fetch('api/templates_constructor.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            templates = data.templates;
            filteredTemplates = [...templates];
            renderTemplates();
        } else {
            showError('Failed to load templates');
        }
    } catch (error) {
        console.error('Error loading templates:', error);
        showError('Error loading templates');
    } finally {
        showLoading(false);
    }
}

// Render templates grid
function renderTemplates() {
    const grid = document.getElementById('templates-grid');
    const empty = document.getElementById('templates-empty');
    
    if (filteredTemplates.length === 0) {
        grid.style.display = 'none';
        empty.style.display = 'block';
        return;
    }
    
    grid.style.display = 'grid';
    empty.style.display = 'none';
    
    grid.innerHTML = filteredTemplates.map(template => `
        <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer" 
             onclick="selectTemplate(${template.id})">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <h4 class="card-title text-sm">${template.name}</h4>
                    <div class="badge badge-primary badge-sm">${template.category}</div>
                </div>
                
                <p class="text-xs text-gray-600 mb-3 line-clamp-2">${template.description}</p>
                
                <div class="flex flex-wrap gap-1 mb-3">
                    <div class="badge badge-outline badge-xs">${template.type}</div>
                    <div class="badge badge-outline badge-xs">${template.downloads} downloads</div>
                </div>
                
                <div class="card-actions justify-between">
                    <button onclick="event.stopPropagation(); previewTemplate(${template.id})" 
                            class="btn btn-ghost btn-xs">
                        <i class="bi bi-eye"></i>
                        Preview
                    </button>
                    <button onclick="event.stopPropagation(); insertTemplate(${template.id})" 
                            class="btn btn-primary btn-xs">
                        <i class="bi bi-plus"></i>
                        Insert
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Search templates
function searchTemplates() {
    const searchTerm = document.getElementById('template-search').value.toLowerCase();
    const category = document.getElementById('template-category').value;
    const type = document.getElementById('template-type').value;
    
    filteredTemplates = templates.filter(template => {
        const matchesSearch = !searchTerm || 
            template.name.toLowerCase().includes(searchTerm) ||
            template.description.toLowerCase().includes(searchTerm);
        
        const matchesCategory = !category || template.category === category;
        const matchesType = !type || template.type === type;
        
        return matchesSearch && matchesCategory && matchesType;
    });
    
    renderTemplates();
}

// Filter templates
function filterTemplates() {
    searchTemplates();
}

// Select template
function selectTemplate(templateId) {
    selectedTemplate = templates.find(t => t.id == templateId);
    if (selectedTemplate) {
        previewTemplate(templateId);
    }
}

// Preview template
async function previewTemplate(templateId) {
    try {
        const response = await fetch(`api/templates_constructor.php?action=get&id=${templateId}`);
        const data = await response.json();
        
        if (data.success) {
            selectedTemplate = data.template;
            showTemplatePreview();
        } else {
            showError('Failed to load template preview');
        }
    } catch (error) {
        console.error('Error previewing template:', error);
        showError('Error loading template preview');
    }
}

// Show template preview modal
function showTemplatePreview() {
    if (!selectedTemplate) return;
    
    document.getElementById('template-preview-modal').style.display = 'block';
    document.getElementById('preview-html').innerHTML = selectedTemplate.preview_html;
    document.getElementById('preview-code-content').textContent = selectedTemplate.template;
    document.getElementById('preview-data-content').textContent = JSON.stringify(selectedTemplate.sample_data, null, 2);
}

// Close template preview
function closeTemplatePreview() {
    document.getElementById('template-preview-modal').style.display = 'none';
}

// Show preview tab
function showPreviewTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.preview-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab').forEach(el => el.classList.remove('tab-active'));
    
    // Show selected tab
    document.getElementById(`preview-${tab}`).style.display = 'block';
    event.target.classList.add('tab-active');
}

// Insert template
async function insertTemplate(templateId) {
    if (!currentProjectId) {
        showError('No project selected');
        return;
    }
    
    try {
        const response = await fetch('api/templates_constructor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'insert',
                template_id: templateId,
                project_id: currentProjectId,
                position: 'end'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Close modal
            closeTemplateSelector();
            
            // Notify constructor about new content
            if (window.onTemplateInserted) {
                window.onTemplateInserted(data.html);
            }
            
            showSuccess('Template inserted successfully');
        } else {
            showError(data.message || 'Failed to insert template');
        }
    } catch (error) {
        console.error('Error inserting template:', error);
        showError('Error inserting template');
    }
}

// Insert selected template (from preview)
function insertSelectedTemplate() {
    if (selectedTemplate) {
        insertTemplate(selectedTemplate.id);
    }
}

// Show template selector
function showTemplateSelector(projectId = null) {
    // Get current project ID from global variable if not provided
    if (!projectId && window.currentProjectId) {
        currentProjectId = window.currentProjectId;
    } else if (projectId) {
        currentProjectId = projectId;
    } else {
        // Try to get from URL or other sources
        currentProjectId = getCurrentProjectId();
    }
    
    document.getElementById('template-selector-modal').style.display = 'block';
    loadTemplates();
}

// Get current project ID from various sources
function getCurrentProjectId() {
    // Try to get from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const projectId = urlParams.get('project');
    if (projectId) return projectId;
    
    // Try to get from global variables
    if (window.currentProjectId) return window.currentProjectId;
    
    // Try to get from localStorage
    const savedProject = localStorage.getItem('currentProject');
    if (savedProject) {
        try {
            const project = JSON.parse(savedProject);
            return project.id;
        } catch (e) {
            console.warn('Could not parse saved project:', e);
        }
    }
    
    // Return null if no project found
    return null;
}

// Close template selector
function closeTemplateSelector() {
    document.getElementById('template-selector-modal').style.display = 'none';
    selectedTemplate = null;
}

// Show loading state
function showLoading(show) {
    document.getElementById('templates-loading').style.display = show ? 'flex' : 'none';
}

// Show error message
function showError(message) {
    alert('Error: ' + message);
}

// Show success message
function showSuccess(message) {
    alert('Success: ' + message);
}

// Global functions for constructor integration
window.showTemplateSelector = showTemplateSelector;
window.closeTemplateSelector = closeTemplateSelector;
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.preview-content {
    min-height: 200px;
}

/* Template card hover effects */
.card:hover {
    transform: translateY(-2px);
}

/* Loading animation */
.loading {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
