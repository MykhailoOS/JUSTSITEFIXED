/**
 * JSB Website Builder - Canvas Module (Pines Edition)
 * 
 * Handles:
 * - iframe sandbox management with postMessage
 * - Drag-and-drop onto canvas
 * - Element selection and highlighting
 * - Zoom and viewport controls
 * - Grid and snapping guides
 * 
 * Security: All canvas content rendered in sandboxed iframe
 */

class EditorCanvas {
    constructor(iframeElement) {
        this.iframe = iframeElement;
        this.viewport = 'desktop';
        this.zoom = 1.0;
        this.gridSize = 8;
        this.gridEnabled = true;
        this.snappingEnabled = true;
        this.selectedElementId = null;
        this.elements = [];
        this.elementIdCounter = 0;
        
        this.init();
    }
    
    init() {
        this.setupIframe();
        this.setupMessageListener();
        this.setupDropZone();
    }
    
    /**
     * Initialize iframe with base HTML and styles
     */
    setupIframe() {
        const baseHTML = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { 
            box-sizing: border-box; 
        }
        body { 
            margin: 0; 
            padding: 16px; 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
        }
        .jsb-element { 
            position: relative; 
            min-height: 40px;
            transition: outline 0.15s ease;
        }
        .jsb-element:hover { 
            outline: 2px dashed #3B82F6;
            outline-offset: 2px;
        }
        .jsb-element-selected { 
            outline: 2px solid #3B82F6 !important;
            outline-offset: 2px;
        }
        .jsb-element-placeholder {
            background: repeating-linear-gradient(
                45deg,
                #f3f4f6,
                #f3f4f6 10px,
                #e5e7eb 10px,
                #e5e7eb 20px
            );
            border: 2px dashed #d1d5db;
            border-radius: 8px;
        }
        .jsb-drop-zone-active {
            background: rgba(59, 130, 246, 0.05) !important;
            outline: 2px dashed #3B82F6 !important;
            outline-offset: 4px;
        }
        ${this.gridEnabled ? this.getGridCSS() : ''}
    </style>
</head>
<body id="canvas-body">
    <div id="canvas-root" class="min-h-screen">
        <div class="text-center py-16 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <p class="text-lg font-medium mb-2">Start Building Your Page</p>
            <p class="text-sm">Drag elements from the library or click to add</p>
        </div>
    </div>
    
    <script>
        // Element click handler
        document.addEventListener('click', function(e) {
            const element = e.target.closest('.jsb-element');
            if (element) {
                e.stopPropagation();
                
                // Remove previous selection
                document.querySelectorAll('.jsb-element-selected').forEach(el => {
                    el.classList.remove('jsb-element-selected');
                });
                
                // Add selection to clicked element
                element.classList.add('jsb-element-selected');
                
                // Notify parent
                window.parent.postMessage({
                    type: 'elementSelected',
                    elementId: element.dataset.elementId,
                    elementType: element.dataset.elementType
                }, '*');
            } else {
                // Clicked on canvas background - deselect
                document.querySelectorAll('.jsb-element-selected').forEach(el => {
                    el.classList.remove('jsb-element-selected');
                });
                window.parent.postMessage({ type: 'elementDeselected' }, '*');
            }
        });
        
        // Listen for messages from parent
        window.addEventListener('message', function(e) {
            if (e.data.type === 'addElement') {
                addElement(e.data.elementType, e.data.elementId, e.data.elementHTML);
            } else if (e.data.type === 'updateElement') {
                updateElement(e.data.elementId, e.data.props);
            } else if (e.data.type === 'deleteElement') {
                deleteElement(e.data.elementId);
            } else if (e.data.type === 'getCanvasHTML') {
                window.parent.postMessage({
                    type: 'canvasHTML',
                    html: document.getElementById('canvas-root').innerHTML
                }, '*');
            }
        });
        
        function addElement(type, id, html) {
            const root = document.getElementById('canvas-root');
            const placeholder = root.querySelector('.text-center.py-16');
            if (placeholder) {
                placeholder.remove();
            }
            
            const element = document.createElement('div');
            element.className = 'jsb-element';
            element.dataset.elementId = id;
            element.dataset.elementType = type;
            element.innerHTML = html;
            
            root.appendChild(element);
        }
        
        function updateElement(id, props) {
            const element = document.querySelector(\`[data-element-id="\${id}"]\`);
            if (!element) return;
            
            // Apply styles
            if (props.text !== undefined) {
                const textElement = element.querySelector('[data-text-content]');
                if (textElement) textElement.textContent = props.text;
            }
            
            if (props.marginTop !== undefined) element.style.marginTop = props.marginTop + 'px';
            if (props.marginRight !== undefined) element.style.marginRight = props.marginRight + 'px';
            if (props.marginBottom !== undefined) element.style.marginBottom = props.marginBottom + 'px';
            if (props.marginLeft !== undefined) element.style.marginLeft = props.marginLeft + 'px';
            
            if (props.paddingTop !== undefined) element.style.paddingTop = props.paddingTop + 'px';
            if (props.paddingRight !== undefined) element.style.paddingRight = props.paddingRight + 'px';
            if (props.paddingBottom !== undefined) element.style.paddingBottom = props.paddingBottom + 'px';
            if (props.paddingLeft !== undefined) element.style.paddingLeft = props.paddingLeft + 'px';
            
            if (props.width !== undefined) element.style.width = props.width;
            if (props.height !== undefined) element.style.height = props.height;
            
            if (props.fontSize !== undefined) element.style.fontSize = props.fontSize + 'px';
            if (props.fontWeight !== undefined) element.style.fontWeight = props.fontWeight;
            if (props.color !== undefined) element.style.color = props.color;
            if (props.textAlign !== undefined) element.style.textAlign = props.textAlign;
            
            if (props.ariaLabel !== undefined) element.setAttribute('aria-label', props.ariaLabel);
            if (props.role !== undefined) element.setAttribute('role', props.role);
            if (props.id !== undefined) element.id = props.id;
            if (props.classes !== undefined) element.className = 'jsb-element ' + props.classes;
            
            if (props.customCSS !== undefined) {
                let styleTag = element.querySelector('style');
                if (!styleTag) {
                    styleTag = document.createElement('style');
                    element.appendChild(styleTag);
                }
                styleTag.textContent = props.customCSS;
            }
        }
        
        function deleteElement(id) {
            const element = document.querySelector(\`[data-element-id="\${id}"]\`);
            if (element) {
                element.remove();
                
                // Show placeholder if canvas is empty
                const root = document.getElementById('canvas-root');
                if (root.children.length === 0) {
                    root.innerHTML = \`
                        <div class="text-center py-16 text-gray-400">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-lg font-medium mb-2">Canvas Empty</p>
                            <p class="text-sm">Drag elements from the library to start building</p>
                        </div>
                    \`;
                }
            }
        }
        
        // Drag and drop handlers
        document.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            
            const root = document.getElementById('canvas-root');
            root.classList.add('jsb-drop-zone-active');
        });
        
        document.addEventListener('dragleave', function(e) {
            if (e.target.id === 'canvas-root') {
                e.target.classList.remove('jsb-drop-zone-active');
            }
        });
        
        document.addEventListener('drop', function(e) {
            e.preventDefault();
            const root = document.getElementById('canvas-root');
            root.classList.remove('jsb-drop-zone-active');
            
            const type = e.dataTransfer.getData('text/plain');
            if (type) {
                window.parent.postMessage({ type: 'dropElement', elementType: type }, '*');
            }
        });
    </script>
</body>
</html>
        `;
        
        this.iframe.srcdoc = baseHTML;
    }
    
    /**
     * Get CSS for grid background
     */
    getGridCSS() {
        return `
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(59, 130, 246, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.1) 1px, transparent 1px);
            background-size: ${this.gridSize}px ${this.gridSize}px;
            pointer-events: none;
            z-index: -1;
        }
        `;
    }
    
    /**
     * Setup postMessage listener for iframe communication
     */
    setupMessageListener() {
        window.addEventListener('message', (e) => {
            if (e.source !== this.iframe.contentWindow) return;
            
            switch (e.data.type) {
                case 'elementSelected':
                    this.handleElementSelected(e.data.elementId, e.data.elementType);
                    break;
                case 'elementDeselected':
                    this.handleElementDeselected();
                    break;
                case 'dropElement':
                    this.handleDropElement(e.data.elementType);
                    break;
                case 'canvasHTML':
                    this.handleCanvasHTML(e.data.html);
                    break;
            }
        });
    }
    
    /**
     * Setup drop zone on parent (for iframe wrapper)
     */
    setupDropZone() {
        const dropZone = this.iframe.parentElement;
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            const type = e.dataTransfer.getData('text/plain');
            if (type) {
                this.addElement(type);
            }
        });
    }
    
    /**
     * Add element to canvas
     */
    addElement(type) {
        const elementId = `element-${++this.elementIdCounter}`;
        const elementHTML = this.getElementHTML(type);
        
        this.elements.push({
            id: elementId,
            type: type,
            props: this.getDefaultProps(type)
        });
        
        this.iframe.contentWindow.postMessage({
            type: 'addElement',
            elementType: type,
            elementId: elementId,
            elementHTML: elementHTML
        }, '*');
        
        // Dispatch event for history
        window.dispatchEvent(new CustomEvent('canvas:elementAdded', {
            detail: { elementId, elementType: type }
        }));
    }
    
    /**
     * Get HTML template for element type
     */
    getElementHTML(type) {
        const templates = {
            heading: '<h2 data-text-content class="text-3xl font-bold text-gray-900">Heading Text</h2>',
            paragraph: '<p data-text-content class="text-base text-gray-700">This is a paragraph. Edit the text in the inspector panel.</p>',
            button: '<button class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">Button Text</button>',
            link: '<a href="#" data-text-content class="text-blue-600 hover:text-blue-700 underline">Link Text</a>',
            image: '<img src="https://via.placeholder.com/400x300" alt="Placeholder" class="w-full h-auto rounded-lg">',
            video: '<div class="aspect-video bg-gray-200 rounded-lg flex items-center justify-center"><svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>',
            icon: '<svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
            list: '<ul class="list-disc list-inside space-y-2 text-gray-700"><li>List item 1</li><li>List item 2</li><li>List item 3</li></ul>',
            divider: '<hr class="border-t border-gray-300 my-8">',
            section: '<section class="py-16"><div data-text-content class="text-center text-gray-600">Section Container</div></section>',
            container: '<div class="max-w-6xl mx-auto px-4"><div data-text-content class="text-center text-gray-600">Container</div></div>',
            row: '<div class="flex gap-4"><div class="flex-1 p-4 bg-gray-100 rounded">Column 1</div><div class="flex-1 p-4 bg-gray-100 rounded">Column 2</div></div>',
            column: '<div class="flex flex-col gap-4"><div class="p-4 bg-gray-100 rounded">Row 1</div><div class="p-4 bg-gray-100 rounded">Row 2</div></div>',
            input: '<input type="text" placeholder="Enter text..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">',
            textarea: '<textarea rows="4" placeholder="Enter text..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>',
            select: '<select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><option>Option 1</option><option>Option 2</option><option>Option 3</option></select>',
            checkbox: '<label class="flex items-center gap-2"><input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"><span class="text-gray-700">Checkbox label</span></label>',
            radio: '<label class="flex items-center gap-2"><input type="radio" name="radio-group" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"><span class="text-gray-700">Radio label</span></label>',
            card: '<div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm"><h3 class="text-xl font-semibold mb-2">Card Title</h3><p class="text-gray-600">Card content goes here</p></div>',
            navbar: '<nav class="bg-white border-b border-gray-200 px-4 py-3"><div class="flex items-center justify-between"><div class="font-semibold text-gray-900">Brand</div><div class="flex gap-4"><a href="#" class="text-gray-600 hover:text-gray-900">Link 1</a><a href="#" class="text-gray-600 hover:text-gray-900">Link 2</a></div></div></nav>',
            tabs: '<div class="border-b border-gray-200"><div class="flex gap-4"><button class="px-4 py-2 border-b-2 border-blue-600 text-blue-600 font-medium">Tab 1</button><button class="px-4 py-2 text-gray-600 hover:text-gray-900">Tab 2</button></div></div><div class="p-4">Tab content goes here</div>',
            accordion: '<div class="border border-gray-200 rounded-lg"><button class="w-full flex items-center justify-between p-4 text-left font-medium hover:bg-gray-50">Accordion Item<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button></div>',
            modal: '<div class="jsb-element-placeholder p-8 text-center text-gray-500"><svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg><p>Modal Placeholder</p></div>'
        };
        
        return templates[type] || '<div data-text-content class="p-4 text-gray-600">New Element</div>';
    }
    
    /**
     * Get default properties for element type
     */
    getDefaultProps(type) {
        return {
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
        };
    }
    
    /**
     * Update element properties
     */
    updateElement(elementId, props) {
        const element = this.elements.find(el => el.id === elementId);
        if (element) {
            element.props = { ...element.props, ...props };
        }
        
        this.iframe.contentWindow.postMessage({
            type: 'updateElement',
            elementId: elementId,
            props: props
        }, '*');
    }
    
    /**
     * Delete element
     */
    deleteElement(elementId) {
        this.elements = this.elements.filter(el => el.id !== elementId);
        
        this.iframe.contentWindow.postMessage({
            type: 'deleteElement',
            elementId: elementId
        }, '*');
    }
    
    /**
     * Handle element selection
     */
    handleElementSelected(elementId, elementType) {
        this.selectedElementId = elementId;
        
        const element = this.elements.find(el => el.id === elementId);
        
        window.dispatchEvent(new CustomEvent('canvas:elementSelected', {
            detail: {
                elementId: elementId,
                elementType: elementType,
                props: element ? element.props : this.getDefaultProps(elementType)
            }
        }));
    }
    
    /**
     * Handle element deselection
     */
    handleElementDeselected() {
        this.selectedElementId = null;
        window.dispatchEvent(new CustomEvent('canvas:elementDeselected'));
    }
    
    /**
     * Handle drop element event from iframe
     */
    handleDropElement(elementType) {
        this.addElement(elementType);
    }
    
    /**
     * Handle canvas HTML response
     */
    handleCanvasHTML(html) {
        this.canvasHTML = html;
        window.dispatchEvent(new CustomEvent('canvas:htmlReady', { detail: { html } }));
    }
    
    /**
     * Get canvas HTML
     */
    getCanvasHTML() {
        return new Promise((resolve) => {
            const handler = (e) => {
                if (e.detail.html) {
                    window.removeEventListener('canvas:htmlReady', handler);
                    resolve(e.detail.html);
                }
            };
            window.addEventListener('canvas:htmlReady', handler);
            this.iframe.contentWindow.postMessage({ type: 'getCanvasHTML' }, '*');
        });
    }
    
    /**
     * Set viewport
     */
    setViewport(viewport) {
        this.viewport = viewport;
        window.dispatchEvent(new CustomEvent('canvas:viewportChanged', { detail: { viewport } }));
    }
    
    /**
     * Set zoom level
     */
    setZoom(zoom) {
        this.zoom = Math.max(0.25, Math.min(2.0, zoom));
        window.dispatchEvent(new CustomEvent('canvas:zoomChanged', { detail: { zoom: this.zoom } }));
    }
    
    /**
     * Toggle grid
     */
    toggleGrid() {
        this.gridEnabled = !this.gridEnabled;
        this.setupIframe(); // Reinitialize to apply grid CSS
    }
    
    /**
     * Toggle snapping
     */
    toggleSnapping() {
        this.snappingEnabled = !this.snappingEnabled;
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EditorCanvas;
}
