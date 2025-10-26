# Three-Panel Editor Implementation Guide

## Overview

The JSB Website Builder now features a completely redesigned three-panel editor interface built with **Pines UI Library** (Tailwind CSS + Alpine.js). This architecture provides a professional, Webflow-style editing experience with enhanced security through iframe sandboxing.

## Architecture

### Panel Layout

```
┌─────────────────────────────────────────────────────────┐
│                      TOP BAR                            │
│  Logo | Project Name | Undo/Redo | Viewport | Zoom | Actions │
└─────────────────────────────────────────────────────────┘
┌────────────┬────────────────────────┬────────────────────┐
│            │                        │                    │
│  LIBRARY   │       CANVAS           │    INSPECTOR       │
│  (Left)    │      (Center)          │     (Right)        │
│            │                        │                    │
│  Search    │   iframe Sandbox       │   Tabs:            │
│  Categories│   - Desktop/Tablet/Mobile │   - Content      │
│  Elements  │   - Zoom 25-200%       │   - Layout         │
│  Drag/Drop │   - Grid/Snapping      │   - Typography     │
│            │   - postMessage API    │   - A11y           │
│            │                        │   - Advanced       │
│            │                        │                    │
│            │                        │   Box Model UI     │
│            │                        │   Visual Controls  │
└────────────┴────────────────────────┴────────────────────┘
```

### Key Features

1. **Library Panel (Left)**
   - Searchable element library
   - Categorized by: Layout, Content, Media, Forms, Components
   - Accordion-style categories using Pines pattern
   - Drag-and-drop to canvas
   - Click to add elements
   - Responsive: slide-over drawer on mobile

2. **Canvas Panel (Center)**
   - iframe sandbox with `allow-scripts allow-same-origin`
   - postMessage communication for security
   - Viewport switcher (Desktop/Tablet/Mobile)
   - Zoom controls (25%-200%)
   - Grid and snapping guides
   - Visual element selection
   - Drop zones for drag-and-drop

3. **Inspector Panel (Right)**
   - Tabbed interface using Pines tabs
   - Visual box-model controls with lock sync
   - Spacing presets (0/8/16/24/32)
   - Unit switcher (px/rem/%)
   - Real-time updates with debouncing (200ms)
   - Accessibility controls (ARIA, roles)
   - Custom CSS editor
   - Responsive: slide-over drawer on mobile

## Files Structure

```
/home/engine/project/
├── buildr.php                    # Main three-panel editor
├── assets/
│   └── js/
│       ├── editor-canvas.js      # Canvas + iframe management
│       ├── editor-library.js     # Library compatibility layer
│       └── editor-inspector.js   # Inspector compatibility layer
├── docs/
│   ├── pines-usage.md           # Pines components documentation
│   └── three-panel-editor-readme.md  # This file
└── .htaccess                     # CSP and security headers
```

## Technology Stack

### Frontend

- **Alpine.js 3.x** - Reactive data binding and component logic
- **Tailwind CSS 3.x** - Utility-first CSS framework
- **Pines UI Library** - Pre-built Alpine + Tailwind components
- **Vanilla JavaScript** - Canvas iframe communication

### Security

- **iframe sandbox** - Canvas content isolated in sandboxed iframe
- **postMessage API** - Secure parent-iframe communication
- **CSP Headers** - Content Security Policy via .htaccess
- **X-Frame-Options** - Clickjacking protection

### Backend

- **PHP 8.1+** - Server-side rendering and API endpoints
- **Existing API endpoints** - project_save.php, etc.

## Usage

### Accessing the Editor

```
https://yourdomain.com/buildr.php
https://yourdomain.com/buildr.php?load=123  # Load project #123
```

### Element Library

**Available Elements:**

- **Layout:** Section, Container, Row, Column
- **Content:** Heading, Paragraph, Button, Link, List, Divider
- **Media:** Image, Video, Icon
- **Forms:** Input, Textarea, Select, Checkbox, Radio
- **Components:** Card, Navbar, Tabs, Accordion, Modal

**Adding Elements:**
1. Drag from library to canvas
2. Click element card to add to canvas
3. Elements appear in iframe with selection outline

### Inspector Controls

**Content Tab:**
- Text content editor (textarea)
- HTML tag selector (div/section/article/etc.)

**Layout Tab:**
- Visual box-model diagram
- Margin/padding controls (4 sides)
- Lock button for synchronized spacing
- Width/height controls
- Spacing unit selector (px/rem/%)
- Presets: 0, 8, 16, 24, 32

**Typography Tab:**
- Font size (number input)
- Font weight (select: 300-700)
- Text color (color picker)
- Text alignment (left/center/right buttons)

**Accessibility Tab:**
- ARIA label (text input)
- Role selector (button/link/nav/main/etc.)
- Focusable checkbox

**Advanced Tab:**
- Element ID
- CSS classes
- Custom CSS (textarea)
- Delete element button

### Keyboard Shortcuts

- `Ctrl/Cmd + Z` - Undo
- `Ctrl/Cmd + Shift + Z` - Redo
- `Ctrl/Cmd + S` - Save project
- `A` - Toggle library panel (planned)
- `I` - Toggle inspector panel (planned)

## Security Implementation

### iframe Sandbox

Canvas content is rendered in a sandboxed iframe:

```html
<iframe 
    id="canvasFrame"
    sandbox="allow-scripts allow-same-origin"
    srcdoc="...">
</iframe>
```

**Sandbox Restrictions:**
- `allow-scripts` - JavaScript execution (required for interactivity)
- `allow-same-origin` - Access to parent via postMessage
- **NOT allowed:** form submission, popups, top navigation

### postMessage Communication

Parent-to-iframe messages:

```javascript
iframe.contentWindow.postMessage({
    type: 'addElement',
    elementType: 'heading',
    elementId: 'element-1',
    elementHTML: '<h2>...</h2>'
}, '*');
```

Iframe-to-parent messages:

```javascript
window.parent.postMessage({
    type: 'elementSelected',
    elementId: 'element-1',
    elementType: 'heading'
}, '*');
```

### CSP Headers

Content Security Policy configured in `.htaccess`:

```apache
Header always set Content-Security-Policy "
    frame-ancestors 'self';
    default-src 'self' https://unpkg.com https://cdn.tailwindcss.com ...;
    script-src 'self' 'unsafe-inline' 'unsafe-eval' ...;
    style-src 'self' 'unsafe-inline' ...;
    img-src 'self' data: https:;
"
```

**Protection against:**
- Clickjacking (frame-ancestors)
- XSS attacks (script-src restrictions)
- Data exfiltration (connect-src limitations)
- Mixed content (https only)

### Additional Headers

- `X-Frame-Options: SAMEORIGIN` - Prevent embedding on other sites
- `X-Content-Type-Options: nosniff` - Prevent MIME sniffing
- `X-XSS-Protection: 1; mode=block` - Browser XSS filter
- `Referrer-Policy: strict-origin-when-cross-origin` - Limit referrer leakage

## Alpine.js Integration

### Main Application Component

```javascript
function editorApp() {
    return {
        // State
        projectId: null,
        projectTitle: 'Untitled',
        viewport: 'desktop',
        zoom: 1.0,
        selectedElement: null,
        
        // Methods
        init() { ... },
        addElement(type) { ... },
        updateElement() { ... },
        saveProject() { ... }
    }
}
```

### Reactive Data Binding

```html
<!-- Two-way binding -->
<input x-model="selectedElementProps.text">

<!-- Computed values -->
<span x-text="Math.round(zoom * 100) + '%'">100%</span>

<!-- Conditional rendering -->
<div x-show="selectedElement">...</div>

<!-- Loop rendering -->
<template x-for="category in library">
    <div x-text="category.name"></div>
</template>
```

## Responsive Design

### Desktop (1024px+)

- Three panels visible side-by-side
- Library: 320px fixed width
- Canvas: flexible width (center)
- Inspector: 320px fixed width

### Tablet (768px - 1023px)

- Canvas takes full width
- Library: slide-over from left
- Inspector: slide-over from right
- Floating action buttons to toggle panels

### Mobile (<768px)

- Canvas takes full width with reduced zoom
- Library: full-screen slide-over
- Inspector: full-screen slide-over
- Touch-optimized controls (min 44px tap targets)

## Performance Optimizations

### Debouncing

Inspector updates debounced to 200ms:

```javascript
updateElement() {
    clearTimeout(this.updateTimer);
    this.updateTimer = setTimeout(() => {
        // Apply updates
    }, 200);
}
```

### Efficient Rendering

- `x-show` for toggle visibility (keeps in DOM)
- `x-if` for conditional rendering (removes from DOM)
- `x-collapse` for smooth height animations
- CSS transforms for better performance

### Asset Optimization

**Development:**
- CDN-delivered Tailwind and Alpine
- Unminified for debugging

**Production:**
- Local Tailwind build (PurgeCSS)
- Bundled and minified Alpine
- HTTP/2 multiplexing
- Asset compression (gzip/brotli)

## Browser Support

### Fully Supported

- Chrome 90+ ✅
- Firefox 88+ ✅
- Safari 14+ ✅
- Edge 90+ ✅

### Partial Support

- Opera 76+ ⚠️ (backdrop-filter may vary)
- Brave ⚠️ (may block some CDN resources)

### Not Supported

- Internet Explorer ❌
- Legacy browsers without ES6

### Required Features

- CSS Grid
- CSS Flexbox
- postMessage API
- iframe sandbox
- backdrop-filter (optional, degrades gracefully)

## Development Workflow

### Local Development

1. Start local PHP server:
   ```bash
   php -S localhost:8000
   ```

2. Access editor:
   ```
   http://localhost:8000/buildr.php
   ```

3. Check browser console for:
   - Alpine.js initialization
   - postMessage events
   - Canvas updates

### Debugging

**Canvas Communication:**
```javascript
// In browser console
window.addEventListener('message', e => console.log('Message:', e.data));
```

**Alpine State:**
```javascript
// In console (with Alpine Devtools)
Alpine.raw($data)
```

**CSP Issues:**
Check browser console for CSP violations:
```
Content Security Policy: ...
```

## Production Deployment

### 1. Build Optimized Assets

```bash
# Install dependencies
npm install -D tailwindcss @tailwindcss/forms

# Create tailwind.config.js
npx tailwindcss init

# Build production CSS
npx tailwindcss -i ./src/input.css -o ./assets/css/buildr.css --minify
```

### 2. Replace CDN Links

In `buildr.php`, replace:

```html
<!-- Development -->
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

With:

```html
<!-- Production -->
<link rel="stylesheet" href="assets/css/buildr.css">
<script defer src="assets/js/alpine.min.js"></script>
```

### 3. Enable Caching

Already configured in `.htaccess`:

```apache
<IfModule mod_expires.c>
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 4. Configure CSP

Update CSP in `.htaccess` to remove CDN sources:

```apache
Header set Content-Security-Policy "
    frame-ancestors 'self';
    default-src 'self';
    script-src 'self';
    style-src 'self';
"
```

## Troubleshooting

### Issue: Panels not showing

**Solution:** Check Alpine.js initialization:
```html
<script>
document.addEventListener('alpine:init', () => {
    console.log('Alpine initialized');
});
</script>
```

### Issue: iframe not loading

**Solution:** Check CSP headers and sandbox attribute:
```javascript
console.log(document.getElementById('canvasFrame').contentWindow);
```

### Issue: Drag-and-drop not working

**Solution:** Verify `draggable="true"` and event handlers:
```javascript
element.addEventListener('dragstart', e => {
    e.dataTransfer.setData('text/plain', type);
});
```

### Issue: Inspector updates not applying

**Solution:** Check postMessage communication:
```javascript
// In iframe console
window.addEventListener('message', e => {
    console.log('Received:', e.data);
});
```

## Future Enhancements

### Planned Features

1. **Undo/Redo History**
   - State snapshots
   - Time-travel debugging
   - Undo stack visualization

2. **Keyboard Navigation**
   - Arrow keys to move elements
   - Tab to cycle through elements
   - Shortcuts for common actions

3. **Collaborative Editing**
   - Real-time sync with WebSockets
   - User presence indicators
   - Conflict resolution

4. **Templates & Blocks**
   - Save element groups as templates
   - Pre-built section library
   - Import/export templates

5. **Advanced Layout Tools**
   - Flexbox visual editor
   - Grid layout editor
   - Responsive breakpoint manager

6. **Component System**
   - Reusable components
   - Props and variants
   - Component library

## Resources

### Documentation

- [Pines UI Library](https://devdojo.com/pines/docs)
- [Alpine.js Documentation](https://alpinejs.dev)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)

### API References

- [postMessage API](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage)
- [iframe sandbox](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/iframe#attr-sandbox)
- [Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)

### Related Files

- `pines-usage.md` - Detailed Pines component usage
- `buildr.php` - Main editor file
- `editor-canvas.js` - Canvas module documentation

## Support

For issues or questions:

1. Check browser console for errors
2. Review `pines-usage.md` for component patterns
3. Verify CSP headers in `.htaccess`
4. Test in different browsers
5. Check PHP error logs for API issues

## License

Same as parent project.

---

**Version:** 1.0 (Pines Edition)  
**Last Updated:** 2024  
**Maintainers:** JSB Development Team
