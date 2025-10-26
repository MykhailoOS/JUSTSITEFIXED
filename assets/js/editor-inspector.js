/**
 * JSB Website Builder - Inspector Module
 * Handles: property panel, box model controls, typography, accessibility
 * NO jQuery - Pure vanilla JavaScript
 */

class EditorInspector {
  constructor(inspectorElement) {
    this.inspector = inspectorElement;
    this.selectedElement = null;
    this.currentTab = 'content';
    this.debounceTimer = null;
    this.debounceDelay = 200; // ms
    
    this.init();
  }
  
  init() {
    this.renderEmptyState();
    this.setupEventListeners();
  }
  
  renderEmptyState() {
    const body = this.inspector.querySelector('.jsb-editor__sidebar-body');
    if (!body) return;
    
    body.innerHTML = `
      <div class="jsb-inspector__empty">
        <svg class="jsb-inspector__empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 2L2 7l10 5 10-5-10-5z"/>
          <path d="M2 17l10 5 10-5"/>
          <path d="M2 12l10 5 10-5"/>
        </svg>
        <p>Select an element to edit its properties</p>
      </div>
    `;
  }
  
  setElement(element) {
    this.selectedElement = element;
    this.render();
  }
  
  clearElement() {
    this.selectedElement = null;
    this.renderEmptyState();
  }
  
  render() {
    if (!this.selectedElement) {
      this.renderEmptyState();
      return;
    }
    
    const body = this.inspector.querySelector('.jsb-editor__sidebar-body');
    if (!body) return;
    
    const elementType = this.selectedElement.dataset.elementType || 'unknown';
    
    body.innerHTML = `
      <div class="jsb-inspector__tabs">
        <button class="jsb-inspector__tab ${this.currentTab === 'content' ? 'jsb-inspector__tab--active' : ''}" data-tab="content">Content</button>
        <button class="jsb-inspector__tab ${this.currentTab === 'layout' ? 'jsb-inspector__tab--active' : ''}" data-tab="layout">Layout</button>
        <button class="jsb-inspector__tab ${this.currentTab === 'style' ? 'jsb-inspector__tab--active' : ''}" data-tab="style">Style</button>
        <button class="jsb-inspector__tab ${this.currentTab === 'a11y' ? 'jsb-inspector__tab--active' : ''}" data-tab="a11y">A11y</button>
      </div>
      
      <div class="jsb-inspector__panel">
        ${this.renderTabContent()}
      </div>
      
      <div style="padding: 16px; border-top: 1px solid #E0E0E0;">
        <button type="button" class="jsb-btn jsb-btn--secondary" style="width: 100%; margin-bottom: 8px;" id="inspectorDuplicate">
          <svg class="jsb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="9" y="9" width="13" height="13" rx="2"/>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
          </svg>
          Duplicate
        </button>
        <button type="button" class="jsb-btn jsb-btn--secondary" style="width: 100%;" id="inspectorDelete">
          <svg class="jsb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
          </svg>
          Delete
        </button>
      </div>
    `;
    
    this.setupPanelEventListeners();
  }
  
  renderTabContent() {
    switch (this.currentTab) {
      case 'content':
        return this.renderContentTab();
      case 'layout':
        return this.renderLayoutTab();
      case 'style':
        return this.renderStyleTab();
      case 'a11y':
        return this.renderAccessibilityTab();
      default:
        return '<p>Unknown tab</p>';
    }
  }
  
  renderContentTab() {
    const el = this.selectedElement;
    const type = el.dataset.elementType;
    
    let fields = '';
    
    // ID
    fields += this.renderField('Element ID', 'input', 'text', el.dataset.elementId, 'elementId', true);
    
    // Type
    fields += this.renderField('Type', 'input', 'text', type, 'elementType', true);
    
    // Content-specific fields
    if (['heading', 'text', 'paragraph', 'button'].includes(type)) {
      const textContent = el.textContent || '';
      fields += this.renderField('Text Content', 'textarea', null, textContent, 'textContent');
    }
    
    if (type === 'image') {
      const img = el.querySelector('img');
      if (img) {
        fields += this.renderField('Image URL', 'input', 'url', img.src, 'imageSrc');
        fields += this.renderField('Alt Text', 'input', 'text', img.alt, 'imageAlt');
        fields += '<p style="font-size:12px;color:#757575;margin:8px 0;">Upload image: <input type="file" accept="image/*" id="imageUpload" style="font-size:12px;"></p>';
      }
    }
    
    if (type === 'video') {
      const videoUrl = el.dataset.videoUrl || '';
      fields += this.renderField('Video URL', 'input', 'url', videoUrl, 'videoUrl', false, 'YouTube or Vimeo URL');
    }
    
    if (type === 'button') {
      const btn = el.querySelector('button');
      if (btn) {
        fields += this.renderField('Button Text', 'input', 'text', btn.textContent, 'buttonText');
        fields += this.renderField('Button Link', 'input', 'url', btn.dataset.href || '', 'buttonHref');
      }
    }
    
    return `
      <div class="jsb-inspector__section">
        <h3 class="jsb-inspector__section-title">Element Properties</h3>
        ${fields}
      </div>
    `;
  }
  
  renderLayoutTab() {
    const el = this.selectedElement;
    const computed = window.getComputedStyle(el);
    
    return `
      <div class="jsb-inspector__section">
        <h3 class="jsb-inspector__section-title">Box Model</h3>
        ${this.renderBoxModel()}
      </div>
      
      <div class="jsb-inspector__section">
        <h3 class="jsb-inspector__section-title">Size</h3>
        ${this.renderField('Width', 'input', 'text', el.style.width || 'auto', 'width')}
        ${this.renderField('Height', 'input', 'text', el.style.height || 'auto', 'height')}
        ${this.renderField('Min Width', 'input', 'text', el.style.minWidth || '', 'minWidth')}
        ${this.renderField('Max Width', 'input', 'text', el.style.maxWidth || '', 'maxWidth')}
      </div>
      
      <div class="jsb-inspector__section">
        <h3 class="jsb-inspector__section-title">Display</h3>
        ${this.renderSelect('Display', ['block', 'inline-block', 'flex', 'grid', 'none'], el.style.display || 'block', 'display')}
        ${this.renderSelect('Position', ['static', 'relative', 'absolute', 'fixed', 'sticky'], el.style.position || 'static', 'position')}
        ${this.renderField('Z-Index', 'input', 'number', el.style.zIndex || '', 'zIndex')}
      </div>
      
      ${el.style.display === 'flex' ? this.renderFlexControls() : ''}
    `;
  }
  
  renderBoxModel() {
    const el = this.selectedElement;
    
    // Parse current values
    const margin = this.parseBoxValues(el.style.margin);
    const padding = this.parseBoxValues(el.style.padding);
    
    return `
      <div class="jsb-box-model" style="margin-bottom: 16px;">
        <div class="jsb-box-model__layer jsb-box-model__layer--margin">
          <div style="display: grid; grid-template-columns: 1fr auto 1fr; grid-template-rows: auto 1fr auto; gap: 4px; height: 100%;">
            <div style="grid-column: 2;"></div>
            <input type="text" class="jsb-box-model__input" data-property="marginTop" value="${margin.top}" placeholder="0" style="grid-column: 2; justify-self: center;">
            <div style="grid-column: 2;"></div>
            
            <input type="text" class="jsb-box-model__input" data-property="marginLeft" value="${margin.left}" placeholder="0" style="grid-row: 2; justify-self: start; align-self: center;">
            
            <div class="jsb-box-model__layer jsb-box-model__layer--border" style="grid-row: 2; grid-column: 2;">
              <div class="jsb-box-model__layer jsb-box-model__layer--padding" style="display: grid; grid-template-columns: 1fr auto 1fr; grid-template-rows: auto 1fr auto; gap: 4px; height: 100%;">
                <div style="grid-column: 2;"></div>
                <input type="text" class="jsb-box-model__input" data-property="paddingTop" value="${padding.top}" placeholder="0" style="grid-column: 2; justify-self: center;">
                <div style="grid-column: 2;"></div>
                
                <input type="text" class="jsb-box-model__input" data-property="paddingLeft" value="${padding.left}" placeholder="0" style="grid-row: 2; justify-self: start; align-self: center;">
                
                <div class="jsb-box-model__layer jsb-box-model__layer--content" style="grid-row: 2; grid-column: 2; display: flex; align-items: center; justify-content: center; min-width: 60px; min-height: 40px;">
                  Content
                </div>
                
                <input type="text" class="jsb-box-model__input" data-property="paddingRight" value="${padding.right}" placeholder="0" style="grid-row: 2; grid-column: 3; justify-self: end; align-self: center;">
                
                <div style="grid-column: 2; grid-row: 3;"></div>
                <input type="text" class="jsb-box-model__input" data-property="paddingBottom" value="${padding.bottom}" placeholder="0" style="grid-row: 3; grid-column: 2; justify-self: center;">
                <div style="grid-column: 2;"></div>
              </div>
            </div>
            
            <input type="text" class="jsb-box-model__input" data-property="marginRight" value="${margin.right}" placeholder="0" style="grid-row: 2; grid-column: 3; justify-self: end; align-self: center;">
            
            <div style="grid-column: 2; grid-row: 3;"></div>
            <input type="text" class="jsb-box-model__input" data-property="marginBottom" value="${margin.bottom}" placeholder="0" style="grid-row: 3; grid-column: 2; justify-self: center;">
            <div style="grid-column: 2;"></div>
          </div>
        </div>
      </div>
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px;">
        <button type="button" class="jsb-btn jsb-btn--sm jsb-btn--secondary" data-preset="padding" data-value="0">P: 0</button>
        <button type="button" class="jsb-btn jsb-btn--sm jsb-btn--secondary" data-preset="padding" data-value="8px">P: 8</button>
        <button type="button" class="jsb-btn jsb-btn--sm jsb-btn--secondary" data-preset="padding" data-value="16px">P: 16</button>
        <button type="button" class="jsb-btn jsb-btn--sm jsb-btn--secondary" data-preset="padding" data-value="24px">P: 24</button>
      </div>
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
        <button type="button" class="jsb-btn jsb-btn--sm jsb-btn--secondary" data-preset="margin" data-value="0">M: 0</button>
        <button type="button" class="jsb-btn jsb-btn--sm jsb-btn--secondary" data-preset="margin" data-value="8px">M: 8</button>
        <button type="button" class="jsb-btn jsb-btn--sm jsb-btn--secondary" data-preset="margin" data-value="16px">M: 16</button>
        <button type="button" class="jsb-btn jsb-btn--sm jsb-btn--secondary" data-preset="margin" data-value="24px">M: 24</button>
      </div>
    `;
  }
  
  parseBoxValues(value) {
    if (!value) return { top: '', right: '', bottom: '', left: '' };
    
    const parts = value.trim().split(/\s+/);
    
    if (parts.length === 1) {
      return { top: parts[0], right: parts[0], bottom: parts[0], left: parts[0] };
    } else if (parts.length === 2) {
      return { top: parts[0], right: parts[1], bottom: parts[0], left: parts[1] };
    } else if (parts.length === 3) {
      return { top: parts[0], right: parts[1], bottom: parts[2], left: parts[1] };
    } else {
      return { top: parts[0], right: parts[1], bottom: parts[2], left: parts[3] };
    }
  }
  
  renderFlexControls() {
    const el = this.selectedElement;
    
    return `
      <div class="jsb-inspector__section">
        <h3 class="jsb-inspector__section-title">Flexbox</h3>
        ${this.renderSelect('Direction', ['row', 'column', 'row-reverse', 'column-reverse'], el.style.flexDirection || 'row', 'flexDirection')}
        ${this.renderSelect('Justify', ['flex-start', 'center', 'flex-end', 'space-between', 'space-around'], el.style.justifyContent || 'flex-start', 'justifyContent')}
        ${this.renderSelect('Align', ['flex-start', 'center', 'flex-end', 'stretch', 'baseline'], el.style.alignItems || 'flex-start', 'alignItems')}
        ${this.renderField('Gap', 'input', 'text', el.style.gap || '', 'gap')}
      </div>
    `;
  }
  
  renderStyleTab() {
    const el = this.selectedElement;
    
    return `
      <div class="jsb-inspector__section">
        <h3 class="jsb-inspector__section-title">Typography</h3>
        ${this.renderField('Font Size', 'input', 'text', el.style.fontSize || '', 'fontSize', false, 'e.g., 16px, 1rem')}
        ${this.renderField('Font Weight', 'input', 'text', el.style.fontWeight || '', 'fontWeight', false, 'e.g., 400, 700, bold')}
        ${this.renderField('Line Height', 'input', 'text', el.style.lineHeight || '', 'lineHeight', false, 'e.g., 1.5, 24px')}
        ${this.renderField('Letter Spacing', 'input', 'text', el.style.letterSpacing || '', 'letterSpacing', false, 'e.g., 0.05em')}
        ${this.renderSelect('Text Align', ['left', 'center', 'right', 'justify'], el.style.textAlign || 'left', 'textAlign')}
        ${this.renderField('Color', 'input', 'color', el.style.color || '#000000', 'color')}
      </div>
      
      <div class="jsb-inspector__section">
        <h3 class="jsb-inspector__section-title">Background</h3>
        ${this.renderField('Background Color', 'input', 'color', el.style.backgroundColor || '#FFFFFF', 'backgroundColor')}
      </div>
      
      <div class="jsb-inspector__section">
        <h3 class="jsb-inspector__section-title">Border</h3>
        ${this.renderField('Border Width', 'input', 'text', el.style.borderWidth || '', 'borderWidth', false, 'e.g., 1px')}
        ${this.renderSelect('Border Style', ['none', 'solid', 'dashed', 'dotted'], el.style.borderStyle || 'none', 'borderStyle')}
        ${this.renderField('Border Color', 'input', 'color', el.style.borderColor || '#000000', 'borderColor')}
        ${this.renderField('Border Radius', 'input', 'text', el.style.borderRadius || '', 'borderRadius', false, 'e.g., 4px, 50%')}
      </div>
      
      <div class="jsb-inspector__section">
        <h3 class="jsb-inspector__section-title">Effects</h3>
        ${this.renderField('Opacity', 'input', 'range', el.style.opacity || '1', 'opacity', false, '', { min: '0', max: '1', step: '0.1' })}
        ${this.renderField('Box Shadow', 'input', 'text', el.style.boxShadow || '', 'boxShadow', false, 'e.g., 0 2px 4px rgba(0,0,0,0.1)')}
      </div>
    `;
  }
  
  renderAccessibilityTab() {
    const el = this.selectedElement;
    
    return `
      <div class="jsb-inspector__section">
        <h3 class="jsb-inspector__section-title">Accessibility</h3>
        <p style="font-size: 12px; color: #757575; margin-bottom: 16px;">Improve accessibility for screen readers and assistive technologies.</p>
        
        ${this.renderField('ARIA Label', 'input', 'text', el.getAttribute('aria-label') || '', 'ariaLabel', false, 'Descriptive label for screen readers')}
        ${this.renderField('ARIA Role', 'input', 'text', el.getAttribute('role') || '', 'ariaRole', false, 'e.g., button, navigation, main')}
        ${this.renderField('Tab Index', 'input', 'number', el.getAttribute('tabindex') || '', 'tabindex', false, '-1 to remove from tab order, 0 for default')}
        ${this.renderField('Title', 'input', 'text', el.getAttribute('title') || '', 'title', false, 'Tooltip text on hover')}
      </div>
      
      <div class="jsb-inspector__section">
        <h3 class="jsb-inspector__section-title">Advanced</h3>
        ${this.renderField('Custom Classes', 'input', 'text', el.className.replace('jsb-canvas__element', '').trim(), 'customClasses', false, 'Space-separated CSS classes')}
        ${this.renderField('Custom ID', 'input', 'text', el.id || '', 'customId', false, 'HTML id attribute')}
      </div>
    `;
  }
  
  renderField(label, type, inputType, value, name, readonly = false, placeholder = '', attrs = {}) {
    const attrString = Object.entries(attrs).map(([k, v]) => `${k}="${v}"`).join(' ');
    
    return `
      <div class="jsb-form-group">
        <label class="jsb-form-label" for="inspector_${name}">${label}</label>
        ${type === 'textarea' 
          ? `<textarea class="jsb-form-textarea" id="inspector_${name}" name="${name}" ${readonly ? 'readonly' : ''} placeholder="${placeholder}">${value}</textarea>`
          : `<input type="${inputType}" class="jsb-form-input" id="inspector_${name}" name="${name}" value="${value}" ${readonly ? 'readonly' : ''} placeholder="${placeholder}" ${attrString}>`
        }
      </div>
    `;
  }
  
  renderSelect(label, options, value, name) {
    return `
      <div class="jsb-form-group">
        <label class="jsb-form-label" for="inspector_${name}">${label}</label>
        <select class="jsb-form-select" id="inspector_${name}" name="${name}">
          ${options.map(opt => `<option value="${opt}" ${opt === value ? 'selected' : ''}>${opt}</option>`).join('')}
        </select>
      </div>
    `;
  }
  
  setupEventListeners() {
    // Listen for element selection from canvas
    document.addEventListener('canvas:elementSelected', (e) => {
      this.setElement(e.detail.element);
    });
    
    document.addEventListener('canvas:elementDeselected', () => {
      this.clearElement();
    });
  }
  
  setupPanelEventListeners() {
    const body = this.inspector.querySelector('.jsb-editor__sidebar-body');
    if (!body) return;
    
    // Tab switching
    body.querySelectorAll('.jsb-inspector__tab').forEach(tab => {
      tab.addEventListener('click', (e) => {
        this.currentTab = e.target.dataset.tab;
        this.render();
      });
    });
    
    // Form inputs with debounce
    body.querySelectorAll('.jsb-form-input, .jsb-form-select, .jsb-form-textarea').forEach(input => {
      input.addEventListener('input', (e) => {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
          this.handleInputChange(e.target);
        }, this.debounceDelay);
      });
    });
    
    // Box model inputs
    body.querySelectorAll('.jsb-box-model__input').forEach(input => {
      input.addEventListener('input', (e) => {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
          this.handleBoxModelChange(e.target);
        }, this.debounceDelay);
      });
    });
    
    // Preset buttons
    body.querySelectorAll('[data-preset]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const preset = e.target.dataset.preset;
        const value = e.target.dataset.value;
        this.applyPreset(preset, value);
      });
    });
    
    // Delete button
    const deleteBtn = body.querySelector('#inspectorDelete');
    if (deleteBtn) {
      deleteBtn.addEventListener('click', () => {
        if (this.selectedElement) {
          const confirmed = confirm('Delete this element?');
          if (confirmed) {
            this.selectedElement.remove();
            this.clearElement();
          }
        }
      });
    }
    
    // Duplicate button
    const duplicateBtn = body.querySelector('#inspectorDuplicate');
    if (duplicateBtn) {
      duplicateBtn.addEventListener('click', () => {
        if (this.selectedElement) {
          const clone = this.selectedElement.cloneNode(true);
          clone.dataset.elementId = 'el_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
          this.selectedElement.parentNode.insertBefore(clone, this.selectedElement.nextSibling);
        }
      });
    }
    
    // Image upload
    const imageUpload = body.querySelector('#imageUpload');
    if (imageUpload) {
      imageUpload.addEventListener('change', (e) => {
        this.handleImageUpload(e.target);
      });
    }
  }
  
  handleInputChange(input) {
    if (!this.selectedElement) return;
    
    const name = input.name;
    const value = input.value;
    
    // Map input names to element properties or attributes
    switch (name) {
      case 'textContent':
        this.selectedElement.textContent = value;
        break;
      case 'imageSrc':
        const img = this.selectedElement.querySelector('img');
        if (img) img.src = value;
        break;
      case 'imageAlt':
        const imgAlt = this.selectedElement.querySelector('img');
        if (imgAlt) imgAlt.alt = value;
        break;
      case 'videoUrl':
        this.selectedElement.dataset.videoUrl = value;
        this.updateVideoEmbed(value);
        break;
      case 'ariaLabel':
        if (value) this.selectedElement.setAttribute('aria-label', value);
        else this.selectedElement.removeAttribute('aria-label');
        break;
      case 'ariaRole':
        if (value) this.selectedElement.setAttribute('role', value);
        else this.selectedElement.removeAttribute('role');
        break;
      case 'tabindex':
        if (value) this.selectedElement.setAttribute('tabindex', value);
        else this.selectedElement.removeAttribute('tabindex');
        break;
      case 'title':
        if (value) this.selectedElement.setAttribute('title', value);
        else this.selectedElement.removeAttribute('title');
        break;
      case 'customClasses':
        this.selectedElement.className = 'jsb-canvas__element ' + value;
        break;
      case 'customId':
        this.selectedElement.id = value;
        break;
      default:
        // Style properties
        this.selectedElement.style[name] = value;
    }
    
    this.dispatchEvent('propertyChanged', { name, value });
  }
  
  handleBoxModelChange(input) {
    if (!this.selectedElement) return;
    
    const property = input.dataset.property;
    let value = input.value.trim();
    
    // Add 'px' if only number provided
    if (value && !isNaN(value)) {
      value += 'px';
    }
    
    this.selectedElement.style[property] = value;
    this.dispatchEvent('propertyChanged', { name: property, value });
  }
  
  applyPreset(preset, value) {
    if (!this.selectedElement) return;
    
    if (preset === 'padding') {
      this.selectedElement.style.padding = value;
    } else if (preset === 'margin') {
      this.selectedElement.style.margin = value;
    }
    
    // Re-render to update inputs
    this.render();
    this.dispatchEvent('presetApplied', { preset, value });
  }
  
  updateVideoEmbed(url) {
    if (!this.selectedElement) return;
    
    const container = this.selectedElement.querySelector('[data-video-container]');
    if (!container) return;
    
    // Extract video ID and platform
    let embedUrl = '';
    
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
      const videoId = this.extractYouTubeId(url);
      if (videoId) {
        embedUrl = `https://www.youtube.com/embed/${videoId}`;
      }
    } else if (url.includes('vimeo.com')) {
      const videoId = this.extractVimeoId(url);
      if (videoId) {
        embedUrl = `https://player.vimeo.com/video/${videoId}`;
      }
    }
    
    if (embedUrl) {
      container.innerHTML = `<iframe src="${embedUrl}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" allowfullscreen></iframe>`;
    } else {
      container.innerHTML = '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; text-align: center;">Invalid video URL<br><small style="font-size:12px;">Please enter a valid YouTube or Vimeo URL</small></div>';
    }
  }
  
  extractYouTubeId(url) {
    const patterns = [
      /(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/,
      /youtube\.com\/embed\/([^&\n?#]+)/
    ];
    
    for (const pattern of patterns) {
      const match = url.match(pattern);
      if (match) return match[1];
    }
    return null;
  }
  
  extractVimeoId(url) {
    const pattern = /vimeo\.com\/(\d+)/;
    const match = url.match(pattern);
    return match ? match[1] : null;
  }
  
  handleImageUpload(fileInput) {
    const file = fileInput.files[0];
    if (!file || !this.selectedElement) return;
    
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = this.selectedElement.querySelector('img');
      if (img) {
        img.src = e.target.result;
        // Update the URL input too
        const urlInput = document.querySelector('#inspector_imageSrc');
        if (urlInput) urlInput.value = 'data:image (uploaded)';
      }
    };
    reader.readAsDataURL(file);
  }
  
  dispatchEvent(eventName, detail = {}) {
    const event = new CustomEvent('inspector:' + eventName, { detail });
    document.dispatchEvent(event);
  }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
  module.exports = EditorInspector;
}
