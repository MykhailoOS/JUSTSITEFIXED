/**
 * JSB Website Builder - Library Module
 * Handles: element categories, search, drag-and-drop source
 * NO jQuery - Pure vanilla JavaScript
 */

class EditorLibrary {
  constructor(libraryElement) {
    this.library = libraryElement;
    this.searchInput = null;
    this.elementsContainer = null;
    this.currentCategory = 'all';
    this.searchTerm = '';
    
    this.elements = this.getElementDefinitions();
    
    this.init();
  }
  
  init() {
    this.render();
    this.setupEventListeners();
  }
  
  getElementDefinitions() {
    return [
      // Layout Elements
      { 
        type: 'container', 
        label: 'Container', 
        category: 'layout', 
        icon: this.getIcon('container'),
        description: 'Generic container for grouping elements'
      },
      { 
        type: 'row', 
        label: 'Row', 
        category: 'layout', 
        icon: this.getIcon('row'),
        description: 'Horizontal layout with columns'
      },
      { 
        type: 'section', 
        label: 'Section', 
        category: 'layout', 
        icon: this.getIcon('section'),
        description: 'Full-width section container'
      },
      { 
        type: 'divider', 
        label: 'Divider', 
        category: 'layout', 
        icon: this.getIcon('divider'),
        description: 'Horizontal line separator'
      },
      { 
        type: 'spacer', 
        label: 'Spacer', 
        category: 'layout', 
        icon: this.getIcon('spacer'),
        description: 'Vertical spacing element'
      },
      
      // Content Elements
      { 
        type: 'heading', 
        label: 'Heading', 
        category: 'content', 
        icon: this.getIcon('heading'),
        description: 'H1-H6 heading text'
      },
      { 
        type: 'paragraph', 
        label: 'Paragraph', 
        category: 'content', 
        icon: this.getIcon('text'),
        description: 'Body text paragraph'
      },
      { 
        type: 'text', 
        label: 'Text', 
        category: 'content', 
        icon: this.getIcon('text'),
        description: 'Generic text element'
      },
      { 
        type: 'list', 
        label: 'List', 
        category: 'content', 
        icon: this.getIcon('list'),
        description: 'Bulleted or numbered list'
      },
      { 
        type: 'button', 
        label: 'Button', 
        category: 'content', 
        icon: this.getIcon('button'),
        description: 'Call-to-action button'
      },
      { 
        type: 'badge', 
        label: 'Badge', 
        category: 'content', 
        icon: this.getIcon('badge'),
        description: 'Small label or tag'
      },
      { 
        type: 'alert', 
        label: 'Alert', 
        category: 'content', 
        icon: this.getIcon('alert'),
        description: 'Information or warning message'
      },
      
      // Media Elements
      { 
        type: 'image', 
        label: 'Image', 
        category: 'media', 
        icon: this.getIcon('image'),
        description: 'Image element'
      },
      { 
        type: 'icon', 
        label: 'Icon', 
        category: 'media', 
        icon: this.getIcon('icon'),
        description: 'SVG icon or symbol'
      },
      { 
        type: 'video', 
        label: 'Video', 
        category: 'media', 
        icon: this.getIcon('video'),
        description: 'Video embed placeholder'
      },
      
      // Form Elements
      { 
        type: 'form', 
        label: 'Form', 
        category: 'forms', 
        icon: this.getIcon('form'),
        description: 'Basic contact form'
      },
      { 
        type: 'input', 
        label: 'Input', 
        category: 'forms', 
        icon: this.getIcon('input'),
        description: 'Text input field'
      },
      { 
        type: 'textarea', 
        label: 'Textarea', 
        category: 'forms', 
        icon: this.getIcon('textarea'),
        description: 'Multi-line text area'
      },
      { 
        type: 'select', 
        label: 'Select', 
        category: 'forms', 
        icon: this.getIcon('select'),
        description: 'Dropdown select menu'
      },
      { 
        type: 'checkbox', 
        label: 'Checkbox', 
        category: 'forms', 
        icon: this.getIcon('checkbox'),
        description: 'Checkbox input'
      },
      { 
        type: 'radio', 
        label: 'Radio', 
        category: 'forms', 
        icon: this.getIcon('radio'),
        description: 'Radio button input'
      },
      
      // Component Elements
      { 
        type: 'card', 
        label: 'Card', 
        category: 'components', 
        icon: this.getIcon('card'),
        description: 'Content card with shadow'
      },
      { 
        type: 'modal', 
        label: 'Modal', 
        category: 'components', 
        icon: this.getIcon('modal'),
        description: 'Modal dialog placeholder'
      },
      { 
        type: 'tabs', 
        label: 'Tabs', 
        category: 'components', 
        icon: this.getIcon('tabs'),
        description: 'Tabbed content switcher'
      },
      { 
        type: 'accordion', 
        label: 'Accordion', 
        category: 'components', 
        icon: this.getIcon('accordion'),
        description: 'Collapsible content panels'
      },
      { 
        type: 'navbar', 
        label: 'Navbar', 
        category: 'components', 
        icon: this.getIcon('navbar'),
        description: 'Navigation bar'
      }
    ];
  }
  
  getIcon(type) {
    // Simple SVG icons for each element type
    const icons = {
      container: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>',
      row: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="7" height="10"/><rect x="14" y="7" width="7" height="10"/></svg>',
      section: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="1"/></svg>',
      divider: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/></svg>',
      spacer: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="8" y1="5" x2="16" y2="5"/><line x1="8" y1="19" x2="16" y2="19"/></svg>',
      heading: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4v16M18 4v16M8 12h8"/></svg>',
      text: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 6H3M21 12H8M21 18H8M3 12v6"/></svg>',
      list: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="4" cy="6" r="1" fill="currentColor"/><circle cx="4" cy="12" r="1" fill="currentColor"/><circle cx="4" cy="18" r="1" fill="currentColor"/></svg>',
      button: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="8" rx="2"/></svg>',
      badge: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="9" width="12" height="6" rx="3"/></svg>',
      alert: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="0.5" fill="currentColor"/></svg>',
      image: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>',
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
      video: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M10 9l5 3-5 3z" fill="currentColor"/></svg>',
      form: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="7" y1="16" x2="11" y2="16"/></svg>',
      input: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="8" rx="1"/></svg>',
      textarea: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="1"/><line x1="7" y1="9" x2="17" y2="9"/><line x1="7" y1="13" x2="17" y2="13"/></svg>',
      select: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="8" rx="1"/><path d="M15 11l-3 3-3-3"/></svg>',
      checkbox: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 12l3 3 5-5"/></svg>',
      radio: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>',
      card: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><line x1="4" y1="10" x2="20" y2="10"/></svg>',
      modal: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="5" width="14" height="14" rx="2"/><rect x="8" y="8" width="8" height="8" rx="1"/></svg>',
      tabs: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="12" rx="1"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="9" y1="8" x2="9" y2="12"/><line x1="15" y1="8" x2="15" y2="12"/></svg>',
      accordion: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="4"/><rect x="3" y="11" width="18" height="4"/><rect x="3" y="17" width="18" height="4"/></svg>',
      navbar: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="4" rx="1"/><circle cx="7" cy="6" r="0.5" fill="currentColor"/><circle cx="12" cy="6" r="0.5" fill="currentColor"/><circle cx="17" cy="6" r="0.5" fill="currentColor"/></svg>'
    };
    
    return icons[type] || icons.container;
  }
  
  render() {
    const sidebar = this.library.querySelector('.jsb-editor__sidebar-body');
    if (!sidebar) return;
    
    sidebar.innerHTML = `
      <div class="jsb-library__search">
        <div class="jsb-search">
          <svg class="jsb-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="M21 21l-4.35-4.35"/>
          </svg>
          <input 
            type="text" 
            class="jsb-search__input" 
            placeholder="Search elements..." 
            aria-label="Search elements"
          >
        </div>
      </div>
      
      <div class="jsb-library__categories" id="libraryCategories">
        ${this.renderCategories()}
      </div>
    `;
    
    this.searchInput = sidebar.querySelector('.jsb-search__input');
    this.elementsContainer = sidebar.querySelector('#libraryCategories');
  }
  
  renderCategories() {
    const categories = {
      layout: 'Layout',
      content: 'Content',
      media: 'Media',
      forms: 'Forms',
      components: 'Components'
    };
    
    let html = '';
    
    for (const [key, label] of Object.entries(categories)) {
      const elements = this.getFilteredElements().filter(el => el.category === key);
      
      if (elements.length === 0) continue;
      
      html += `
        <div class="jsb-library__category" data-category="${key}">
          <div class="jsb-library__category-title">${label}</div>
          <div class="jsb-library__elements">
            ${elements.map(el => this.renderElement(el)).join('')}
          </div>
        </div>
      `;
    }
    
    return html || '<div class="jsb-library__empty" style="padding: 24px; text-align: center; color: #757575;">No elements found</div>';
  }
  
  renderElement(element) {
    return `
      <div 
        class="jsb-library__element" 
        draggable="true"
        data-element-type="${element.type}"
        title="${element.description}"
        role="button"
        tabindex="0"
        aria-label="Add ${element.label}"
      >
        <div class="jsb-library__element-icon">${element.icon}</div>
        <div class="jsb-library__element-label">${element.label}</div>
      </div>
    `;
  }
  
  getFilteredElements() {
    let elements = this.elements;
    
    // Filter by search term
    if (this.searchTerm) {
      const term = this.searchTerm.toLowerCase();
      elements = elements.filter(el => 
        el.label.toLowerCase().includes(term) ||
        el.type.toLowerCase().includes(term) ||
        el.description.toLowerCase().includes(term)
      );
    }
    
    // Filter by category
    if (this.currentCategory !== 'all') {
      elements = elements.filter(el => el.category === this.currentCategory);
    }
    
    return elements;
  }
  
  setupEventListeners() {
    // Search input
    if (this.searchInput) {
      this.searchInput.addEventListener('input', (e) => {
        this.searchTerm = e.target.value;
        this.updateDisplay();
      });
    }
    
    // Drag start on element cards
    this.library.addEventListener('dragstart', (e) => {
      const card = e.target.closest('.jsb-library__element');
      if (!card) return;
      
      const elementType = card.dataset.elementType;
      e.dataTransfer.setData('text/plain', elementType);
      e.dataTransfer.effectAllowed = 'copy';
      
      card.classList.add('jsb-library__element--dragging');
    });
    
    this.library.addEventListener('dragend', (e) => {
      const card = e.target.closest('.jsb-library__element');
      if (!card) return;
      
      card.classList.remove('jsb-library__element--dragging');
    });
    
    // Click to add (alternative to drag)
    this.library.addEventListener('click', (e) => {
      const card = e.target.closest('.jsb-library__element');
      if (!card) return;
      
      const elementType = card.dataset.elementType;
      this.dispatchEvent('elementAdd', { type: elementType });
    });
    
    // Keyboard support
    this.library.addEventListener('keydown', (e) => {
      const card = e.target.closest('.jsb-library__element');
      if (!card) return;
      
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const elementType = card.dataset.elementType;
        this.dispatchEvent('elementAdd', { type: elementType });
      }
    });
  }
  
  updateDisplay() {
    if (!this.elementsContainer) return;
    
    this.elementsContainer.innerHTML = this.renderCategories();
  }
  
  dispatchEvent(eventName, detail = {}) {
    const event = new CustomEvent('library:' + eventName, { detail });
    document.dispatchEvent(event);
  }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
  module.exports = EditorLibrary;
}
