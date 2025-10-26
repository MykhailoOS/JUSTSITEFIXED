# Pines UI Library - Component Usage Documentation

This document lists all Pines components used in the JSB Website Builder's three-panel editor implementation.

## Overview

Pines is a library of Alpine.js and Tailwind CSS UI components. The editor leverages Pines patterns for consistent, accessible, and responsive UI elements.

**Official Documentation:** https://devdojo.com/pines/docs

## Components Used

### 1. Navigation / Menu Bar

**Used in:** Top bar navigation with logo, project name, and action buttons

**Pines Reference:** https://devdojo.com/pines/docs/navigation

**Implementation:**
- Horizontal menu bar with logo, navigation items, and user dropdown
- Sticky positioning at top of viewport
- Responsive collapse on mobile

**Code Location:** `buildr.php` lines 147-284

---

### 2. Dropdown Menu

**Used in:** User profile dropdown in top-right corner

**Pines Reference:** https://devdojo.com/pines/docs/dropdown

**Features:**
- Click to toggle visibility
- Click-away to close (`@click.away`)
- Smooth transitions with `x-transition`
- Positioned absolutely relative to trigger button

**Code Location:** `buildr.php` lines 269-281

**Example:**
```html
<div x-data="{ open: false }">
    <button @click="open = !open">User Menu</button>
    <div x-show="open" @click.away="open = false" x-transition>
        <!-- Menu items -->
    </div>
</div>
```

---

### 3. Accordion

**Used in:** Element library categories (collapsible sections)

**Pines Reference:** https://devdojo.com/pines/docs/accordion

**Features:**
- Click to expand/collapse categories
- Smooth expand animation with `x-collapse`
- Individual category state management
- Icons rotate on open/close

**Code Location:** `buildr.php` lines 327-343

**Example:**
```html
<div x-data="{ open: false }">
    <button @click="open = !open">Category Name</button>
    <div x-show="open" x-collapse>
        <!-- Category content -->
    </div>
</div>
```

---

### 4. Tabs

**Used in:** Inspector panel sections (Content, Layout, Typography, A11y, Advanced)

**Pines Reference:** https://devdojo.com/pines/docs/tabs

**Features:**
- Multiple content sections with tab navigation
- Active state styling
- Smooth content switching with `x-show`
- Keyboard accessible

**Code Location:** `buildr.php` lines 425-438

**Example:**
```html
<div x-data="{ activeTab: 'content' }">
    <nav>
        <button @click="activeTab = 'content'" :class="activeTab === 'content' ? 'active' : ''">
            Content
        </button>
    </nav>
    <div x-show="activeTab === 'content'">
        <!-- Content panel -->
    </div>
</div>
```

---

### 5. Slide-Over / Drawer

**Used in:** Mobile-responsive library and inspector panels

**Pines Reference:** https://devdojo.com/pines/docs/slide-over

**Features:**
- Fixed positioning overlaying content
- Slide in from left (library) or right (inspector)
- Backdrop blur effect
- Smooth transitions
- Close button and click-away support

**Code Location:** `buildr.php` lines 289-355, 401-557

**Example:**
```html
<aside 
    x-show="showPanel"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    class="fixed inset-y-0 left-0 w-80 bg-white shadow-xl">
    <!-- Panel content -->
</aside>
```

---

### 6. Toast Notifications

**Used in:** Success/error/info messages (save confirmations, errors, etc.)

**Pines Reference:** https://devdojo.com/pines/docs/notification

**Features:**
- Fixed positioning (bottom-right corner)
- Auto-dismiss after 5 seconds
- Smooth enter/exit transitions
- Color-coded by type (success/error/info)
- Stacked vertically with spacing

**Code Location:** `buildr.php` lines 563-595

**Example:**
```javascript
showToast(message, type = 'info') {
    const toast = { id: ++this.toastId, message, type, show: true };
    this.toasts.push(toast);
    setTimeout(() => this.removeToast(toast.id), 5000);
}
```

---

### 7. Form Controls (Select, Input, Switch)

**Used in:** Inspector property controls

**Pines Reference:** 
- Select: https://devdojo.com/pines/docs/select
- Input: https://devdojo.com/pines/docs/input
- Switch: https://devdojo.com/pines/docs/toggle

**Features:**
- Consistent Tailwind styling
- Focus states with ring
- Proper labels and accessibility
- Real-time binding with `x-model`

**Code Location:** `buildr.php` lines 440-556

---

### 8. Button Groups

**Used in:** Viewport switcher, zoom controls, text alignment buttons

**Pines Reference:** https://devdojo.com/pines/docs/button

**Features:**
- Grouped buttons with consistent spacing
- Active state styling
- Icon-only or with text
- Background highlight for active state

**Code Location:** `buildr.php` lines 212-248

---

### 9. Tooltip / Hover Card

**Used in:** Button tooltips throughout the interface

**Implementation:** Native HTML `title` attribute for simplicity

**Pines Reference:** https://devdojo.com/pines/docs/tooltip

**Note:** Can be enhanced with Pines tooltip for richer styling

---

### 10. Modal (Implicit Pattern)

**Not currently used but available for:**
- Save project dialog
- Confirmation dialogs
- Settings panels

**Pines Reference:** https://devdojo.com/pines/docs/modal

**Pattern:**
```html
<div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
    <div @click="showModal = false" class="fixed inset-0 bg-black/50"></div>
    <div class="relative bg-white rounded-lg p-6">
        <!-- Modal content -->
    </div>
</div>
```

---

## Alpine.js Directives Used

### Core Directives

- `x-data`: Component state initialization
- `x-show`: Conditional visibility
- `x-if`: Conditional rendering (removes from DOM)
- `x-for`: List rendering
- `x-model`: Two-way data binding
- `x-text`: Text content binding
- `x-html`: HTML content binding
- `@click`: Click event handler
- `@click.away`: Click outside handler
- `:class`: Dynamic class binding
- `:style`: Dynamic style binding

### Transitions

- `x-transition`: Full transition
- `x-transition:enter`: Enter transition
- `x-transition:enter-start`: Enter start state
- `x-transition:enter-end`: Enter end state
- `x-transition:leave`: Leave transition
- `x-transition:leave-start`: Leave start state
- `x-transition:leave-end`: Leave end state
- `x-collapse`: Smooth height collapse/expand

### Special

- `x-cloak`: Hide until Alpine is ready
- `[x-cloak] { display: none !important; }` in CSS

---

## Tailwind CSS Patterns

### Layout Classes

- `flex`, `flex-col`: Flexbox layouts
- `grid`, `grid-cols-*`: Grid layouts
- `fixed`, `absolute`, `relative`: Positioning
- `inset-*`: Position coordinates
- `z-*`: Z-index stacking

### Spacing

- `p-*`: Padding
- `m-*`: Margin
- `gap-*`: Gap in flex/grid
- `space-*`: Spacing between children

### Colors

- `bg-*`: Background colors
- `text-*`: Text colors
- `border-*`: Border colors

### Transitions

- `transition`: Base transition
- `duration-*`: Transition duration
- `ease-*`: Timing function

### Responsive

- `md:*`: Medium breakpoint (768px+)
- `lg:*`: Large breakpoint (1024px+)
- `hidden`, `block`, `flex`: Display utilities

---

## Backdrop Blur Usage

Blur effects are applied to panels for visual depth:

```css
backdrop-filter: blur(10px);
background: rgba(255, 255, 255, 0.95);
```

**Applied to:**
- Left panel (library)
- Right panel (inspector)
- Modal overlays
- Dropdown backgrounds

**Browser Support:** Modern browsers (Chrome, Firefox, Safari, Edge)

---

## Accessibility Features

All Pines components follow accessibility best practices:

1. **Keyboard Navigation:**
   - Tab navigation for all interactive elements
   - Enter/Space for buttons
   - Escape to close modals/dropdowns

2. **ARIA Attributes:**
   - `aria-label` for icon-only buttons
   - `role` attributes for semantic meaning
   - `aria-expanded` for collapsible sections

3. **Focus Management:**
   - Visible focus rings (`focus:ring-*`)
   - Focus trap in modals
   - Restore focus on close

4. **Screen Reader Support:**
   - Semantic HTML elements
   - Descriptive labels
   - Hidden content marked appropriately

---

## Production Build Recommendations

For production deployment:

1. **Replace CDN with Local Builds:**
   ```bash
   # Install dependencies
   npm install tailwindcss alpinejs
   
   # Build Tailwind
   npx tailwindcss -i ./src/input.css -o ./assets/css/output.css --minify
   ```

2. **Configure Tailwind:**
   Create `tailwind.config.js`:
   ```javascript
   module.exports = {
     content: ['./buildr.php', './assets/js/**/*.js'],
     theme: { extend: {} },
     plugins: []
   }
   ```

3. **Bundle Alpine.js:**
   Import only needed components to reduce bundle size

4. **Optimize:**
   - Minify JavaScript
   - Compress CSS
   - Enable HTTP/2 or HTTP/3
   - Use CDN for production assets

---

## Additional Resources

- **Pines Documentation:** https://devdojo.com/pines/docs
- **Alpine.js Documentation:** https://alpinejs.dev
- **Tailwind CSS Documentation:** https://tailwindcss.com/docs
- **MDN postMessage API:** https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage
- **iframe sandbox attribute:** https://developer.mozilla.org/en-US/docs/Web/HTML/Element/iframe#attr-sandbox

---

## Maintenance Notes

- **Keep Pines patterns updated** from official documentation
- **Test accessibility** with keyboard and screen readers
- **Monitor Alpine.js updates** for breaking changes
- **Optimize bundle size** by removing unused components
- **Test cross-browser** compatibility for backdrop-filter

---

Last Updated: 2024
Version: 1.0 (Pines Edition)
