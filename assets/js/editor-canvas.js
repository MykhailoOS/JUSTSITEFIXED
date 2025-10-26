/**
 * JSB Website Builder - Canvas Module
 * Handles: grid, rulers, snapping, selection, zoom, drag-and-drop
 * NO jQuery - Pure vanilla JavaScript
 */

class EditorCanvas {
  constructor(canvasElement) {
    this.canvas = canvasElement;
    this.content = canvasElement.querySelector('[data-canvas-content]');
    this.selectedElement = null;
    this.selectionBox = null;
    this.viewport = 'desktop'; // desktop, tablet, mobile
    this.zoom = 1.0;
    this.gridSize = 8; // px
    this.gridEnabled = true;
    this.rulersEnabled = true;
    this.snappingEnabled = true;
    this.isDragging = false;
    this.dragOffset = { x: 0, y: 0 };
    
    this.init();
  }
  
  init() {
    this.setupCanvas();
    this.setupEventListeners();
    this.render();
  }
  
  setupCanvas() {
    // Create rulers
    if (this.rulersEnabled) {
      this.createRulers();
    }
    
    // Create selection box overlay
    this.selectionBox = document.createElement('div');
    this.selectionBox.className = 'jsb-canvas__selection-box';
    this.selectionBox.style.cssText = `
      position: absolute;
      border: 2px solid #3B82F6;
      pointer-events: none;
      display: none;
      z-index: 9998;
      box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.5);
    `;
    this.canvas.style.position = 'relative';
    this.canvas.appendChild(this.selectionBox);
    
    // Create resize handles
    this.createResizeHandles();
  }
  
  createRulers() {
    // Horizontal ruler
    const hRuler = document.createElement('div');
    hRuler.className = 'jsb-canvas__ruler jsb-canvas__ruler--horizontal';
    hRuler.style.cssText = `
      position: sticky;
      top: 0;
      left: 0;
      width: 100%;
      height: 20px;
      background: #F8F9FA;
      border-bottom: 1px solid #E0E0E0;
      z-index: 100;
      font-size: 9px;
      color: #757575;
      overflow: hidden;
    `;
    
    // Vertical ruler
    const vRuler = document.createElement('div');
    vRuler.className = 'jsb-canvas__ruler jsb-canvas__ruler--vertical';
    vRuler.style.cssText = `
      position: sticky;
      left: 0;
      top: 20px;
      width: 20px;
      height: calc(100% - 20px);
      background: #F8F9FA;
      border-right: 1px solid #E0E0E0;
      z-index: 100;
      font-size: 9px;
      color: #757575;
      overflow: hidden;
    `;
    
    this.canvas.prepend(vRuler);
    this.canvas.prepend(hRuler);
    
    this.hRuler = hRuler;
    this.vRuler = vRuler;
    
    this.updateRulers();
  }
  
  updateRulers() {
    if (!this.rulersEnabled || !this.hRuler) return;
    
    // Draw horizontal ruler ticks
    const canvasWidth = this.content.offsetWidth;
    const hMarks = [];
    for (let i = 0; i <= canvasWidth; i += 50) {
      hMarks.push(`<span style="position:absolute;left:${i}px;height:8px;width:1px;background:#757575;"></span>`);
      if (i % 100 === 0) {
        hMarks.push(`<span style="position:absolute;left:${i + 2}px;top:2px;">${i}</span>`);
      }
    }
    this.hRuler.innerHTML = hMarks.join('');
    
    // Draw vertical ruler ticks
    const canvasHeight = this.content.offsetHeight;
    const vMarks = [];
    for (let i = 0; i <= canvasHeight; i += 50) {
      vMarks.push(`<span style="position:absolute;top:${i}px;width:8px;height:1px;background:#757575;"></span>`);
      if (i % 100 === 0) {
        vMarks.push(`<span style="position:absolute;top:${i + 2}px;left:2px;writing-mode:vertical-lr;transform:rotate(180deg);">${i}</span>`);
      }
    }
    this.vRuler.innerHTML = vMarks.join('');
  }
  
  createResizeHandles() {
    this.resizeHandles = document.createElement('div');
    this.resizeHandles.className = 'jsb-canvas__resize-handles';
    this.resizeHandles.style.cssText = 'display: none; position: absolute; pointer-events: none;';
    
    const positions = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'];
    positions.forEach(pos => {
      const handle = document.createElement('div');
      handle.className = `jsb-canvas__resize-handle jsb-canvas__resize-handle--${pos}`;
      handle.dataset.position = pos;
      handle.style.cssText = `
        position: absolute;
        width: 8px;
        height: 8px;
        background: #3B82F6;
        border: 1px solid white;
        border-radius: 50%;
        pointer-events: all;
        cursor: ${this.getResizeCursor(pos)};
      `;
      this.positionHandle(handle, pos);
      this.resizeHandles.appendChild(handle);
    });
    
    this.canvas.appendChild(this.resizeHandles);
  }
  
  getResizeCursor(pos) {
    const cursors = {
      nw: 'nw-resize', n: 'n-resize', ne: 'ne-resize',
      e: 'e-resize', se: 'se-resize', s: 's-resize',
      sw: 'sw-resize', w: 'w-resize'
    };
    return cursors[pos] || 'default';
  }
  
  positionHandle(handle, pos) {
    const positions = {
      nw: { top: '-4px', left: '-4px' },
      n: { top: '-4px', left: '50%', transform: 'translateX(-50%)' },
      ne: { top: '-4px', right: '-4px' },
      e: { top: '50%', right: '-4px', transform: 'translateY(-50%)' },
      se: { bottom: '-4px', right: '-4px' },
      s: { bottom: '-4px', left: '50%', transform: 'translateX(-50%)' },
      sw: { bottom: '-4px', left: '-4px' },
      w: { top: '50%', left: '-4px', transform: 'translateY(-50%)' }
    };
    Object.assign(handle.style, positions[pos]);
  }
  
  setupEventListeners() {
    // Canvas click for selection
    this.content.addEventListener('click', (e) => {
      if (e.target === this.content) {
        this.deselectElement();
      } else {
        const element = e.target.closest('[data-element-id]');
        if (element) {
          e.stopPropagation();
          this.selectElement(element);
        }
      }
    });
    
    // Drop zone for elements from library
    this.content.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'copy';
    });
    
    this.content.addEventListener('drop', (e) => {
      e.preventDefault();
      const elementType = e.dataTransfer.getData('text/plain');
      if (elementType) {
        const rect = this.content.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        this.addElement(elementType, x, y);
      }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
      // Delete key
      if (e.key === 'Delete' && this.selectedElement) {
        this.deleteElement(this.selectedElement);
      }
      
      // Escape key
      if (e.key === 'Escape') {
        this.deselectElement();
      }
      
      // Copy: Ctrl/Cmd + C
      if ((e.ctrlKey || e.metaKey) && e.key === 'c' && this.selectedElement) {
        this.copyElement();
      }
      
      // Paste: Ctrl/Cmd + V
      if ((e.ctrlKey || e.metaKey) && e.key === 'v') {
        this.pasteElement();
      }
      
      // Duplicate: Ctrl/Cmd + D
      if ((e.ctrlKey || e.metaKey) && e.key === 'd' && this.selectedElement) {
        e.preventDefault();
        this.duplicateElement(this.selectedElement);
      }
    });
  }
  
  selectElement(element) {
    // Deselect previous
    if (this.selectedElement) {
      this.selectedElement.classList.remove('jsb-canvas__element--selected');
    }
    
    this.selectedElement = element;
    element.classList.add('jsb-canvas__element--selected');
    
    // Show selection box
    const rect = element.getBoundingClientRect();
    const canvasRect = this.canvas.getBoundingClientRect();
    
    this.selectionBox.style.display = 'block';
    this.selectionBox.style.left = (rect.left - canvasRect.left + this.canvas.scrollLeft) + 'px';
    this.selectionBox.style.top = (rect.top - canvasRect.top + this.canvas.scrollTop) + 'px';
    this.selectionBox.style.width = rect.width + 'px';
    this.selectionBox.style.height = rect.height + 'px';
    
    // Show resize handles
    this.resizeHandles.style.display = 'block';
    this.resizeHandles.style.left = this.selectionBox.style.left;
    this.resizeHandles.style.top = this.selectionBox.style.top;
    this.resizeHandles.style.width = this.selectionBox.style.width;
    this.resizeHandles.style.height = this.selectionBox.style.height;
    
    // Dispatch event for inspector
    this.dispatchEvent('elementSelected', { element });
  }
  
  deselectElement() {
    if (this.selectedElement) {
      this.selectedElement.classList.remove('jsb-canvas__element--selected');
      this.selectedElement = null;
    }
    
    this.selectionBox.style.display = 'none';
    this.resizeHandles.style.display = 'none';
    
    this.dispatchEvent('elementDeselected');
  }
  
  addElement(type, x = null, y = null) {
    const element = this.createElementByType(type);
    
    if (x !== null && y !== null) {
      // Snap to grid if enabled
      if (this.snappingEnabled) {
        x = Math.round(x / this.gridSize) * this.gridSize;
        y = Math.round(y / this.gridSize) * this.gridSize;
      }
      
      element.style.position = 'absolute';
      element.style.left = x + 'px';
      element.style.top = y + 'px';
    }
    
    this.content.appendChild(element);
    this.selectElement(element);
    
    this.dispatchEvent('elementAdded', { element, type });
    
    return element;
  }
  
  createElementByType(type) {
    const id = 'el_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    const element = document.createElement('div');
    element.dataset.elementId = id;
    element.dataset.elementType = type;
    element.className = 'jsb-canvas__element';
    
    // Add default styles
    element.style.cssText = 'padding: 16px; margin: 8px 0; min-height: 40px; border: 1px dashed #E0E0E0; transition: border-color 0.15s;';
    
    switch (type) {
      case 'heading':
        element.innerHTML = '<h2 contenteditable="true">Heading Text</h2>';
        break;
      case 'text':
      case 'paragraph':
        element.innerHTML = '<p contenteditable="true">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>';
        break;
      case 'button':
        element.innerHTML = '<button type="button" style="padding: 12px 24px; background: #3B82F6; color: white; border: none; border-radius: 6px; cursor: pointer;">Click me</button>';
        break;
      case 'image':
        element.innerHTML = '<img src="https://via.placeholder.com/400x300" alt="Placeholder" style="max-width: 100%; height: auto;">';
        break;
      case 'icon':
        element.innerHTML = '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>';
        break;
      case 'list':
        element.innerHTML = '<ul contenteditable="true"><li>List item 1</li><li>List item 2</li><li>List item 3</li></ul>';
        break;
      case 'divider':
        element.innerHTML = '<hr style="border: none; border-top: 1px solid #E0E0E0; margin: 16px 0;">';
        break;
      case 'spacer':
        element.innerHTML = '<div style="height: 32px;"></div>';
        element.style.background = 'repeating-linear-gradient(45deg, transparent, transparent 10px, #F5F5F5 10px, #F5F5F5 20px)';
        break;
      case 'container':
        element.innerHTML = '<div style="padding: 24px; border: 2px dashed #BDBDBD;">Container</div>';
        break;
      case 'row':
        element.innerHTML = '<div style="display: flex; gap: 16px;"><div style="flex: 1; padding: 16px; border: 1px dashed #E0E0E0;">Column 1</div><div style="flex: 1; padding: 16px; border: 1px dashed #E0E0E0;">Column 2</div></div>';
        break;
      case 'form':
        element.innerHTML = `
          <form style="display: flex; flex-direction: column; gap: 12px;">
            <input type="text" placeholder="Name" style="padding: 8px; border: 1px solid #E0E0E0; border-radius: 4px;">
            <input type="email" placeholder="Email" style="padding: 8px; border: 1px solid #E0E0E0; border-radius: 4px;">
            <button type="submit" style="padding: 12px; background: #3B82F6; color: white; border: none; border-radius: 6px; cursor: pointer;">Submit</button>
          </form>
        `;
        break;
      case 'video':
        element.innerHTML = '<div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; background: #000;"><div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white;">Video Placeholder</div></div>';
        break;
      case 'badge':
        element.innerHTML = '<span style="display: inline-block; padding: 4px 12px; background: #3B82F6; color: white; border-radius: 12px; font-size: 12px;">Badge</span>';
        break;
      case 'alert':
        element.innerHTML = '<div style="padding: 12px 16px; background: #DBEAFE; border-left: 4px solid #3B82F6; border-radius: 4px; color: #1E40AF;">This is an alert message.</div>';
        break;
      case 'card':
        element.innerHTML = '<div style="padding: 24px; background: white; border: 1px solid #E0E0E0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"><h3>Card Title</h3><p>Card content goes here.</p></div>';
        break;
      default:
        element.innerHTML = '<div contenteditable="true">New Element</div>';
    }
    
    return element;
  }
  
  deleteElement(element) {
    if (!element) return;
    
    const confirmed = confirm('Delete this element?');
    if (confirmed) {
      element.remove();
      this.deselectElement();
      this.dispatchEvent('elementDeleted', { element });
    }
  }
  
  duplicateElement(element) {
    if (!element) return;
    
    const clone = element.cloneNode(true);
    clone.dataset.elementId = 'el_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    // Offset position if absolute
    if (element.style.position === 'absolute') {
      clone.style.left = (parseInt(element.style.left) + 20) + 'px';
      clone.style.top = (parseInt(element.style.top) + 20) + 'px';
    }
    
    element.parentNode.insertBefore(clone, element.nextSibling);
    this.selectElement(clone);
    
    this.dispatchEvent('elementDuplicated', { original: element, clone });
  }
  
  copyElement() {
    if (!this.selectedElement) return;
    
    this.clipboard = {
      type: this.selectedElement.dataset.elementType,
      html: this.selectedElement.outerHTML,
      styles: this.selectedElement.style.cssText
    };
    
    console.log('Element copied to clipboard');
  }
  
  pasteElement() {
    if (!this.clipboard) return;
    
    const temp = document.createElement('div');
    temp.innerHTML = this.clipboard.html;
    const element = temp.firstChild;
    
    // New ID
    element.dataset.elementId = 'el_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    this.content.appendChild(element);
    this.selectElement(element);
    
    this.dispatchEvent('elementPasted', { element });
  }
  
  setViewport(viewport) {
    this.viewport = viewport;
    this.content.dataset.viewport = viewport;
    this.updateRulers();
    this.dispatchEvent('viewportChanged', { viewport });
  }
  
  setZoom(zoom) {
    this.zoom = Math.max(0.25, Math.min(2.0, zoom));
    this.content.style.transform = `scale(${this.zoom})`;
    this.content.style.transformOrigin = 'top left';
    this.dispatchEvent('zoomChanged', { zoom: this.zoom });
  }
  
  toggleGrid() {
    this.gridEnabled = !this.gridEnabled;
    if (this.gridEnabled) {
      this.canvas.style.backgroundImage = `
        linear-gradient(45deg, #F5F5F5 25%, transparent 25%),
        linear-gradient(-45deg, #F5F5F5 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #F5F5F5 75%),
        linear-gradient(-45deg, transparent 75%, #F5F5F5 75%)
      `;
    } else {
      this.canvas.style.backgroundImage = 'none';
    }
    this.dispatchEvent('gridToggled', { enabled: this.gridEnabled });
  }
  
  toggleSnapping() {
    this.snappingEnabled = !this.snappingEnabled;
    this.dispatchEvent('snappingToggled', { enabled: this.snappingEnabled });
  }
  
  getSelectedElement() {
    return this.selectedElement;
  }
  
  updateElementStyle(property, value) {
    if (!this.selectedElement) return;
    
    this.selectedElement.style[property] = value;
    
    // Update selection box
    if (this.selectionBox.style.display === 'block') {
      const rect = this.selectedElement.getBoundingClientRect();
      const canvasRect = this.canvas.getBoundingClientRect();
      
      this.selectionBox.style.width = rect.width + 'px';
      this.selectionBox.style.height = rect.height + 'px';
    }
    
    this.dispatchEvent('elementStyleUpdated', { element: this.selectedElement, property, value });
  }
  
  render() {
    // Render loop for animations/updates
    requestAnimationFrame(() => this.render());
  }
  
  dispatchEvent(eventName, detail = {}) {
    const event = new CustomEvent('canvas:' + eventName, { detail });
    document.dispatchEvent(event);
  }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
  module.exports = EditorCanvas;
}
