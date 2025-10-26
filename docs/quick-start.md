# Quick Start Guide - Three-Panel Editor

Get started with the new JSB Website Builder three-panel editor in 5 minutes!

## 🚀 Access the Editor

### New Project
```
https://yourdomain.com/buildr.php
```

### Edit Existing Project
```
https://yourdomain.com/buildr.php?load=PROJECT_ID
```

## 🎨 Interface Overview

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃  🏠 Logo | 📝 Project | ↶↷ | 💻📱 | ➕➖🔍 | 💾👁️🚀  ┃
┣━━━━━━━━┳━━━━━━━━━━━━━━━━━━━━━━━━━┳━━━━━━━━━━━━━━┫
┃        ┃                         ┃              ┃
┃ LIBRARY┃        CANVAS           ┃  INSPECTOR   ┃
┃        ┃                         ┃              ┃
┃ 🔍     ┃   [Your Design]         ┃ 📋 Content   ┃
┃        ┃                         ┃ 📐 Layout    ┃
┃ Layout ┃   iframe Preview        ┃ ✍️  Type     ┃
┃ Content┃                         ┃ ♿ A11y      ┃
┃ Media  ┃   Click to Select       ┃ ⚙️  Advanced ┃
┃ Forms  ┃   Drag to Position      ┃              ┃
┃        ┃                         ┃              ┃
┗━━━━━━━━┻━━━━━━━━━━━━━━━━━━━━━━━━━┻━━━━━━━━━━━━━━┛
```

## 📚 Add Your First Element

### Method 1: Drag and Drop
1. Find an element in the Library (left panel)
2. Click and hold on the element card
3. Drag it onto the Canvas (center)
4. Release to drop

### Method 2: Click to Add
1. Click any element card in the Library
2. Element appears on the Canvas immediately

### Popular Elements
- **Heading** - H1-H6 text
- **Paragraph** - Body text
- **Button** - Call-to-action
- **Image** - Pictures
- **Section** - Content container
- **Row** - Horizontal layout

## ✏️ Edit Elements

### 1. Select Element
Click any element on the Canvas. It will show a blue outline.

### 2. Edit Properties

**Content Tab** (📋)
- Change text content
- Select HTML tag (div, section, article, etc.)

**Layout Tab** (📐)
- Adjust margins and padding
- Set width and height
- Use visual box model

**Typography Tab** (✍️)
- Font size
- Font weight
- Text color
- Text alignment

**Accessibility Tab** (♿)
- ARIA labels
- Semantic roles
- Keyboard focus

**Advanced Tab** (⚙️)
- Element ID
- CSS classes
- Custom CSS
- Delete element

## 🎯 Visual Box Model

The Layout tab has a visual box model:

```
┌──────────────────────────────┐
│ Margin (orange)              │
│  ┌────────────────────────┐  │
│  │ Padding (green)        │  │
│  │  ┌──────────────────┐  │  │
│  │  │   Content        │  │  │
│  │  └──────────────────┘  │  │
│  └────────────────────────┘  │
└──────────────────────────────┘
```

### Quick Spacing

**Use Presets:**
- 0 - No spacing
- 8 - Tight spacing
- 16 - Normal spacing
- 24 - Comfortable spacing
- 32 - Loose spacing

**Lock Button:**
- 🔒 Locked - All sides change together
- 🔓 Unlocked - Each side independent

## 📱 Responsive Design

### Viewport Switcher (Top Bar)

- **💻 Desktop** - 1280px wide
- **📱 Tablet** - 768px wide
- **📱 Mobile** - 375px wide

Click the icons to preview your design on different devices.

## 🔍 Zoom Controls

- **➕ Zoom In** - Increase zoom (up to 200%)
- **➖ Zoom Out** - Decrease zoom (down to 25%)
- **100%** - Current zoom level

## ⌨️ Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl/Cmd + S` | Save project |
| `Ctrl/Cmd + Z` | Undo |
| `Ctrl/Cmd + Shift + Z` | Redo |

## 💾 Save Your Work

### Auto-Save (Coming Soon)
Projects will auto-save every 30 seconds.

### Manual Save
1. Click **Save** button (top-right)
2. Enter project name
3. Click "Save"

### Deploy
1. Click **Deploy** button (top-right)
2. Configure deployment settings
3. Click "Deploy"

Your site goes live instantly!

## 📱 Mobile Editing

On mobile devices:

1. **Library Panel**
   - Tap the menu button (bottom-left)
   - Slides in from left
   - Tap outside to close

2. **Inspector Panel**
   - Tap the settings button (bottom-right)
   - Slides in from right
   - Tap outside to close

3. **Canvas**
   - Tap element to select
   - Pinch to zoom
   - Scroll to navigate

## 🎨 Common Tasks

### Change Text
1. Select element
2. Go to Content tab
3. Edit text in textarea

### Change Color
1. Select element
2. Go to Typography tab
3. Click color picker
4. Choose color

### Add Spacing
1. Select element
2. Go to Layout tab
3. Use presets or type values
4. Lock/unlock for all sides

### Align Text
1. Select element
2. Go to Typography tab
3. Click alignment buttons:
   - ⬅️ Left
   - ⬆️ Center
   - ➡️ Right

### Delete Element
1. Select element
2. Go to Advanced tab
3. Click "Delete Element" button
4. Confirm deletion

## 🐛 Troubleshooting

### Element won't select?
- Click directly on the element
- Avoid clicking on nested elements
- Try clicking the parent container

### Changes not applying?
- Wait 200ms (auto-debounce)
- Check browser console for errors
- Refresh page and try again

### Drag-and-drop not working?
- Try clicking element instead
- Check if JavaScript is enabled
- Use a supported browser

### Panels missing on mobile?
- Tap the floating buttons
- Bottom-left: Library
- Bottom-right: Inspector

## 🎓 Learning Resources

### Documentation
- [Complete Guide](three-panel-editor-readme.md)
- [Pines Components](pines-usage.md)
- [Migration Guide](migration-guide.md)

### Video Tutorials (Coming Soon)
- Getting Started
- Building Your First Page
- Advanced Techniques
- Responsive Design Tips

### External Resources
- [Pines UI Library](https://devdojo.com/pines/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Alpine.js](https://alpinejs.dev)

## 🌟 Pro Tips

1. **Use Keyboard Shortcuts**
   - Save time with Ctrl+S
   - Undo mistakes with Ctrl+Z

2. **Lock Spacing for Consistency**
   - Use the lock button in Layout tab
   - All sides change together

3. **Test on Multiple Viewports**
   - Switch between Desktop/Tablet/Mobile
   - Ensure responsive design

4. **Save Often**
   - Don't lose your work
   - Use Ctrl+S frequently

5. **Use Presets**
   - Consistent spacing = better design
   - Start with 8, 16, 24, 32

6. **Grid and Snapping**
   - Enable grid for alignment
   - Enable snapping for precision

7. **Semantic HTML**
   - Use proper tags (header, main, section)
   - Better SEO and accessibility

8. **ARIA Labels**
   - Add for screen readers
   - Improve accessibility score

## ✅ Checklist for Your First Page

- [ ] Open buildr.php
- [ ] Add a Section element
- [ ] Add a Heading inside
- [ ] Change heading text
- [ ] Add a Paragraph
- [ ] Add spacing (try preset 16)
- [ ] Add a Button
- [ ] Change button text
- [ ] Test on Tablet viewport
- [ ] Test on Mobile viewport
- [ ] Save your project
- [ ] Deploy when ready

## 🎉 Next Steps

Now that you know the basics:

1. **Experiment with Elements**
   - Try all element types
   - Combine them creatively

2. **Learn Advanced Features**
   - Custom CSS
   - Accessibility controls
   - Complex layouts

3. **Build a Complete Page**
   - Header with navigation
   - Hero section
   - Content sections
   - Footer

4. **Share Your Work**
   - Deploy to production
   - Share the link
   - Get feedback

## 🆘 Need Help?

- Check the [Complete Guide](three-panel-editor-readme.md)
- Review [Common Issues](three-panel-editor-readme.md#troubleshooting)
- Check browser console for errors
- Contact support team

## 🚀 You're Ready!

You now know enough to build amazing websites. Start creating!

**Happy Building! 🎨**

---

*Quick Start Guide v1.0 - Three-Panel Editor (Pines Edition)*
