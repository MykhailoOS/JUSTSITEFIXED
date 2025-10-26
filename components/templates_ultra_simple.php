<!-- Ultra Simple Templates Integration -->
<div id="templates-ultra-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 8px; width: 90%; max-width: 600px; max-height: 80vh; overflow: hidden;">
        <div style="padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">Choose Template</h3>
            <button onclick="closeTemplatesUltra()" style="background: none; border: none; font-size: 20px; cursor: pointer;">✕</button>
        </div>
        <div style="padding: 20px; max-height: 60vh; overflow-y: auto;">
            <div id="templates-ultra-list">
                <div style="text-align: center; padding: 40px;">
                    <div style="width: 40px; height: 40px; border: 3px solid #eee; border-top: 3px solid #007cba; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                    <p style="margin-top: 16px; color: #666;">Loading templates...</p>
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

.template-ultra-item {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.template-ultra-item:hover {
    border-color: #007cba;
    box-shadow: 0 2px 8px rgba(0, 124, 186, 0.1);
}

.template-ultra-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.template-ultra-preview {
    font-size: 12px;
    color: #666;
    background: #f8f9fa;
    padding: 8px;
    border-radius: 4px;
    margin-top: 8px;
}

.template-ultra-btn {
    background: #007cba;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    margin-top: 10px;
}

.template-ultra-btn:hover {
    background: #005a87;
}
</style>

<script>
let templatesUltra = [];

// Show templates modal
function showTemplatesUltra() {
    document.getElementById('templates-ultra-modal').style.display = 'block';
    loadTemplatesUltra();
}

// Close templates modal
function closeTemplatesUltra() {
    document.getElementById('templates-ultra-modal').style.display = 'none';
}

// Load templates
async function loadTemplatesUltra() {
    try {
        const response = await fetch('api/templates_ultra_simple.php');
        const data = await response.json();
        
        if (data.success) {
            templatesUltra = data.templates;
            renderTemplatesUltra();
        } else {
            showTemplatesUltraError('Failed to load templates');
        }
    } catch (error) {
        console.error('Error loading templates:', error);
        showTemplatesUltraError('Error loading templates');
    }
}

// Render templates
function renderTemplatesUltra() {
    const container = document.getElementById('templates-ultra-list');
    
    if (templatesUltra.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #666;">No templates found</div>';
        return;
    }
    
    container.innerHTML = templatesUltra.map(template => `
        <div class="template-ultra-item" onclick="insertTemplateUltra(${template.id})">
            <div class="template-ultra-name">${template.name}</div>
            <div class="template-ultra-preview">
                ${template.template.substring(0, 100)}...
            </div>
            <button class="template-ultra-btn" onclick="event.stopPropagation(); insertTemplateUltra(${template.id})">Insert Template</button>
        </div>
    `).join('');
}

// Insert template
async function insertTemplateUltra(templateId) {
    try {
        const response = await fetch('api/templates_ultra_simple.php?id=' + templateId);
        const data = await response.json();
        
        if (data.success) {
            // Process template variables
            let processedHtml = data.html;
            
            // Replace common variables with sample data
            processedHtml = processedHtml.replace(/\[TITLE\]/g, 'Sample Title');
            processedHtml = processedHtml.replace(/\[DESCRIPTION\]/g, 'Sample Description');
            processedHtml = processedHtml.replace(/\[BUTTON_TEXT\]/g, 'Click Me');
            processedHtml = processedHtml.replace(/\[IMAGE\]/g, 'https://via.placeholder.com/300x200');
            processedHtml = processedHtml.replace(/\[PRICE\]/g, '$99');
            processedHtml = processedHtml.replace(/\[AUTHOR\]/g, 'John Doe');
            processedHtml = processedHtml.replace(/\[DATE\]/g, new Date().toLocaleDateString());
            processedHtml = processedHtml.replace(/\[VIEWS\]/g, '1,234');
            
            // Handle Handlebars-like syntax
            processedHtml = processedHtml.replace(/\{\{#each\s+(\w+)\}\}(.*?)\{\{\/each\}\}/gs, function(match, arrayName, content) {
                // Simple array replacement
                if (arrayName === 'plans') {
                    return `
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
                            <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center;">
                                <h3>Starter Plan</h3>
                                <div style="font-size: 2rem; font-weight: bold; color: #333;">$9/month</div>
                                <p>Perfect for individuals</p>
                                <ul style="text-align: left; margin: 20px 0;">
                                    <li>✓ 5 Projects</li>
                                    <li>✓ 10GB Storage</li>
                                    <li>✓ Email Support</li>
                                </ul>
                                <button style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; width: 100%;">Get Started</button>
                            </div>
                            <div style="border: 2px solid #007cba; border-radius: 8px; padding: 20px; text-align: center; position: relative;">
                                <div style="background: #007cba; color: white; padding: 4px 8px; border-radius: 4px; position: absolute; top: -10px; left: 50%; transform: translateX(-50%); font-size: 12px;">Most Popular</div>
                                <h3>Professional Plan</h3>
                                <div style="font-size: 2rem; font-weight: bold; color: #333;">$29/month</div>
                                <p>Best for growing businesses</p>
                                <ul style="text-align: left; margin: 20px 0;">
                                    <li>✓ Unlimited Projects</li>
                                    <li>✓ 100GB Storage</li>
                                    <li>✓ Priority Support</li>
                                    <li>✓ Advanced Analytics</li>
                                </ul>
                                <button style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; width: 100%;">Start Free Trial</button>
                            </div>
                            <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center;">
                                <h3>Enterprise Plan</h3>
                                <div style="font-size: 2rem; font-weight: bold; color: #333;">$99/month</div>
                                <p>For large organizations</p>
                                <ul style="text-align: left; margin: 20px 0;">
                                    <li>✓ Everything in Pro</li>
                                    <li>✓ Custom Integrations</li>
                                    <li>✓ Dedicated Support</li>
                                    <li>✓ SLA Guarantee</li>
                                </ul>
                                <button style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 4px; width: 100%;">Contact Sales</button>
                            </div>
                        </div>
                    `;
                } else if (arrayName === 'features') {
                    return `
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
                            <div style="text-align: center; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
                                <div style="font-size: 2rem; margin-bottom: 10px;">🚀</div>
                                <h3>Fast Performance</h3>
                                <p>Lightning-fast loading times for better user experience</p>
                            </div>
                            <div style="text-align: center; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
                                <div style="font-size: 2rem; margin-bottom: 10px;">🔒</div>
                                <h3>Secure</h3>
                                <p>Enterprise-grade security to protect your data</p>
                            </div>
                            <div style="text-align: center; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
                                <div style="font-size: 2rem; margin-bottom: 10px;">📱</div>
                                <h3>Mobile Ready</h3>
                                <p>Responsive design that works on all devices</p>
                            </div>
                        </div>
                    `;
                }
                return content;
            });
            
            // Handle simple variables
            processedHtml = processedHtml.replace(/\{\{(\w+)\}\}/g, function(match, variable) {
                const replacements = {
                    'title': 'Welcome to Our Platform',
                    'subtitle': 'Build amazing websites with our powerful tools',
                    'buttonText': 'Get Started',
                    'excerpt': 'Learn the fundamentals of modern web development with our comprehensive guide...',
                    'author': 'John Doe',
                    'date': new Date().toLocaleDateString(),
                    'views': '1,234',
                    'featured': 'true'
                };
                return replacements[variable] || variable;
            });
            
            // Handle if conditions
            processedHtml = processedHtml.replace(/\{\{#if\s+(\w+)\}\}(.*?)\{\{\/if\}\}/gs, function(match, condition, content) {
                if (condition === 'featured') {
                    return content; // Show featured content
                }
                return '';
            });
            
            // Insert into canvas
            const canvas = document.querySelector('.general_canva');
            if (canvas) {
                const templateDiv = document.createElement('div');
                templateDiv.innerHTML = processedHtml;
                templateDiv.className = 'template-inserted';
                canvas.appendChild(templateDiv);
                
                // Show success message
                showNotificationUltra('Template inserted successfully!', 'success');
                
                // Close modal
                closeTemplatesUltra();
            }
        } else {
            showNotificationUltra('Error loading template', 'error');
        }
    } catch (error) {
        console.error('Error inserting template:', error);
        showNotificationUltra('Error inserting template', 'error');
    }
}

// Show error
function showTemplatesUltraError(message) {
    document.getElementById('templates-ultra-list').innerHTML = `
        <div style="text-align: center; padding: 40px; color: #dc3545;">
            <div style="font-size: 24px; margin-bottom: 8px;">⚠️</div>
            <p>${message}</p>
            <button onclick="loadTemplatesUltra()" style="margin-top: 12px; padding: 8px 16px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer;">Retry</button>
        </div>
    `;
}

// Show notification
function showNotificationUltra(message, type) {
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
        ${type === 'success' ? 'background: #28a745;' : 'background: #dc3545;'}
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Save to Library function
function saveToLibrary() {
    const canvas = document.querySelector('.general_canva');
    if (!canvas) {
        alert('No content to save');
        return;
    }
    
    // Get all content from canvas
    const content = canvas.innerHTML;
    if (!content.trim()) {
        alert('Canvas is empty');
        return;
    }
    
    const name = prompt('Enter template name:');
    if (!name) return;
    
    const category = prompt('Enter category (landing, blog, portfolio, etc.):') || 'general';
    
    // Save to library
    fetch('api/templates_community.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name: name,
            html: content,
            category: category
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Template saved to library!');
        } else {
            alert('Error saving template: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving template');
    });
}

// Global functions
window.showTemplatesUltra = showTemplatesUltra;
window.closeTemplatesUltra = closeTemplatesUltra;
window.saveToLibrary = saveToLibrary;
</script>
