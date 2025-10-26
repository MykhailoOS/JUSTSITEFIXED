/**
 * JSB Website Builder - Inspector Module (Pines Edition)
 * 
 * Handles:
 * - Property editing with visual box-model controls
 * - Tab navigation
 * - Debounced updates to canvas
 * 
 * Note: Most functionality moved to Alpine.js in buildr.php
 * This module provides compatibility layer and helper functions
 */

class EditorInspector {
    constructor(panelElement) {
        this.panel = panelElement;
        this.selectedElement = null;
        this.updateTimer = null;
        this.debounceDelay = 200; // ms
        
        console.log('EditorInspector initialized (Pines Edition)');
        this.setupEventListeners();
    }
    
    /**
     * Setup event listeners for canvas communication
     */
    setupEventListeners() {
        // Listen for element selection from canvas
        window.addEventListener('canvas:elementSelected', (e) => {
            this.handleElementSelected(e.detail);
        });
        
        window.addEventListener('canvas:elementDeselected', () => {
            this.handleElementDeselected();
        });
    }
    
    /**
     * Handle element selection
     */
    handleElementSelected(detail) {
        this.selectedElement = {
            id: detail.elementId,
            type: detail.elementType,
            props: detail.props
        };
        
        console.log('Inspector: Element selected', this.selectedElement);
        
        // Dispatch event to Alpine.js
        window.dispatchEvent(new CustomEvent('inspector:elementSelected', {
            detail: this.selectedElement
        }));
    }
    
    /**
     * Handle element deselection
     */
    handleElementDeselected() {
        this.selectedElement = null;
        console.log('Inspector: Element deselected');
        
        window.dispatchEvent(new CustomEvent('inspector:elementDeselected'));
    }
    
    /**
     * Update element with debouncing
     */
    updateElement(elementId, props) {
        clearTimeout(this.updateTimer);
        
        this.updateTimer = setTimeout(() => {
            console.log('Inspector: Updating element', elementId, props);
            
            // Dispatch to canvas
            window.dispatchEvent(new CustomEvent('inspector:updateElement', {
                detail: { elementId, props }
            }));
        }, this.debounceDelay);
    }
    
    /**
     * Apply spacing preset to all sides
     */
    applySpacingPreset(value, unit = 'px') {
        if (!this.selectedElement) return;
        
        const props = {
            marginTop: value,
            marginRight: value,
            marginBottom: value,
            marginLeft: value,
            paddingTop: value,
            paddingRight: value,
            paddingBottom: value,
            paddingLeft: value
        };
        
        this.updateElement(this.selectedElement.id, props);
    }
    
    /**
     * Toggle spacing lock
     * When locked, all spacing values change together
     */
    toggleSpacingLock() {
        console.log('Inspector: Toggle spacing lock');
    }
    
    /**
     * Get box model diagram HTML
     * Returns visual representation of margin/padding
     */
    getBoxModelHTML(props) {
        return `
            <div class="box-model-visual">
                <div class="box-model-margin">
                    <span>Margin</span>
                    <div class="box-model-padding">
                        <span>Padding</span>
                        <div class="box-model-content">
                            <span>Content</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

// Export for compatibility
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EditorInspector;
}
