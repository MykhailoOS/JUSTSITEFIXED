<!-- Simple Templates Integration -->
<div id="templates-simple-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 8px; width: 90%; max-width: 800px; max-height: 80vh; overflow: hidden;">
        <div style="padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Choose Template</h3>
            <button onclick="closeTemplatesSimple()" style="background: none; border: none; font-size: 20px; cursor: pointer;">✕</button>
        </div>
        <div style="padding: 20px; max-height: 60vh; overflow-y: auto;">
            <div id="templates-simple-list">
                <div style="text-align: center; padding: 40px;">
                    <div style="width: 40px; height: 40px; border: 3px solid #e5e7eb; border-top: 3px solid #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                    <p style="margin-top: 16px; color: #6b7280;">Loading templates...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.template-item {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.template-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.template-name {
    font-weight: 600;
    color: #111827;
    margin-bottom: 4px;
}

.template-category {
    font-size: 12px;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 4px;
    display: inline-block;
    margin-bottom: 8px;
}

.template-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}

.template-btn {
    padding: 6px 12px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
}

.template-btn-preview {
    background: #f3f4f6;
    color: #374151;
}

.template-btn-insert {
    background: #3b82f6;
    color: white;
}

.template-btn:hover {
    opacity: 0.8;
}
</style>

<script>
let templatesSimple = [];

// Show templates modal
function showTemplatesSimple() {
    document.getElementById('templates-simple-modal').style.display = 'block';
    loadTemplatesSimple();
}

// Close templates modal
function closeTemplatesSimple() {
    document.getElementById('templates-simple-modal').style.display = 'none';
}

// Load templates
async function loadTemplatesSimple() {
    try {
        const response = await fetch('api/templates_simple.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            templatesSimple = data.templates;
            renderTemplatesSimple();
        } else {
            showTemplatesError('Failed to load templates');
        }
    } catch (error) {
        console.error('Error loading templates:', error);
        showTemplatesError('Error loading templates');
    }
}

// Render templates
function renderTemplatesSimple() {
    const container = document.getElementById('templates-simple-list');
    
    if (templatesSimple.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #6b7280;">No templates found</div>';
        return;
    }
    
    container.innerHTML = templatesSimple.map(template => `
        <div class="template-item" onclick="selectTemplateSimple(${template.id})">
            <div class="template-name">${template.name}</div>
            <div class="template-category">${template.category}</div>
            <div class="template-actions">
                <button class="template-btn template-btn-preview" onclick="event.stopPropagation(); previewTemplateSimple(${template.id})">Preview</button>
                <button class="template-btn template-btn-insert" onclick="event.stopPropagation(); insertTemplateSimple(${template.id})">Insert</button>
            </div>
        </div>
    `).join('');
}

// Select template
function selectTemplateSimple(templateId) {
    insertTemplateSimple(templateId);
}

// Preview template
function previewTemplateSimple(templateId) {
    const template = templatesSimple.find(t => t.id == templateId);
    if (template) {
        alert('Template: ' + template.name + '\n\nHTML Preview:\n' + template.template.substring(0, 200) + '...');
    }
}

// Insert template
async function insertTemplateSimple(templateId) {
    const template = templatesSimple.find(t => t.id == templateId);
    if (!template) return;
    
    try {
        // Get current project ID (simplified)
        const projectId = getCurrentProjectIdSimple();
        
        if (!projectId) {
            alert('No project selected. Please create or open a project first.');
            return;
        }
        
        // Insert template HTML into canvas
        const canvas = document.querySelector('.general_canva');
        if (canvas) {
            const templateDiv = document.createElement('div');
            templateDiv.innerHTML = template.template;
            templateDiv.className = 'template-inserted';
            canvas.appendChild(templateDiv);
            
            // Show success message
            showNotificationSimple('Template inserted successfully!', 'success');
            
            // Close modal
            closeTemplatesSimple();
        }
    } catch (error) {
        console.error('Error inserting template:', error);
        showNotificationSimple('Error inserting template', 'error');
    }
}

// Get current project ID (simplified)
function getCurrentProjectIdSimple() {
    // Try to get from URL
    const urlParams = new URLSearchParams(window.location.search);
    const projectId = urlParams.get('project');
    if (projectId) return projectId;
    
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
    
    // Return a default ID for testing
    return '1';
}

// Show error
function showTemplatesError(message) {
    document.getElementById('templates-simple-list').innerHTML = `
        <div style="text-align: center; padding: 40px; color: #dc2626;">
            <div style="font-size: 24px; margin-bottom: 8px;">⚠️</div>
            <p>${message}</p>
            <button onclick="loadTemplatesSimple()" style="margin-top: 12px; padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Retry</button>
        </div>
    `;
}

// Show notification
function showNotificationSimple(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 16px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 1001;
        ${type === 'success' ? 'background: #10b981;' : 'background: #ef4444;'}
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Global functions
window.showTemplatesSimple = showTemplatesSimple;
window.closeTemplatesSimple = closeTemplatesSimple;
</script>
