/**
 * JSB Website Builder - Library Module (Pines Edition)
 * 
 * Handles:
 * - Element library categorization and filtering
 * - Drag-and-drop initialization
 * - Search and filter functionality
 * 
 * Note: Most functionality moved to Alpine.js in buildr.php
 * This module provides compatibility layer for old code
 */

class EditorLibrary {
    constructor(panelElement) {
        this.panel = panelElement;
        this.categories = [];
        this.searchTerm = '';
        
        // Compatibility layer - most logic now in Alpine.js
        console.log('EditorLibrary initialized (Pines Edition)');
    }
    
    /**
     * Initialize library
     * Note: In Pines edition, this is handled by Alpine.js
     */
    init() {
        console.log('Library init called - using Alpine.js implementation');
    }
    
    /**
     * Filter library by search term
     * Note: In Pines edition, this is handled by Alpine.js computed property
     */
    filter(term) {
        this.searchTerm = term;
        console.log('Library filter:', term);
    }
}

// Export for compatibility
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EditorLibrary;
}
