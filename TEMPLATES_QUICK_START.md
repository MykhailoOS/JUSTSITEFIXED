# Templates - Quick Start Guide

## 🚀 Getting Started

### 1. Initialize Sample Templates
Visit: `http://your-domain.com/init_templates.php`
This will create 5 sample templates to get you started.

### 2. Access Templates
Navigate to: `http://your-domain.com/templates.php`
Or click "📄 Templates" in the admin panel.

## 🎯 Key Features

### Create Templates
- Click "Create New Template"
- Fill in name, description, category
- Write Handlebars template code
- Add sample JSON data
- Preview in real-time

### Handlebars Syntax
```handlebars
<!-- Variables -->
<h1>{{title}}</h1>

<!-- Loops -->
{{#each items}}
<div>{{name}}</div>
{{/each}}

<!-- Conditionals -->
{{#if featured}}
<span class="badge">Featured</span>
{{/if}}
```

### Sample Data (JSON)
```json
{
    "title": "Hello World",
    "items": [
        {"name": "Item 1"},
        {"name": "Item 2"}
    ],
    "featured": true
}
```

## 📋 Available Actions

- **Create**: New template from scratch
- **Edit**: Modify existing template
- **Preview**: See rendered output
- **Duplicate**: Copy template
- **Download**: Get as HTML file
- **Delete**: Remove template
- **Export**: Download all as JSON

## 🎨 Design Stack

- **TailwindCSS**: Utility-first CSS
- **DaisyUI**: Beautiful components
- **Alpine.js**: Reactive JavaScript
- **Handlebars**: Template engine
- **Material Icons**: Bootstrap Icons

## 🔧 Technical Details

### Template Categories
- Landing Page
- Blog
- Portfolio
- E-commerce
- Corporate

### Template Types
- HTML: Full page templates
- Component: Reusable components
- Layout: Page layouts

### Status Options
- Active: Ready to use
- Inactive: Disabled
- Draft: Work in progress

## 🚨 Important Notes

- Admin access required
- Templates are stored in database
- Real-time preview available
- Export/import functionality
- Mobile-responsive design

## 📞 Support

For issues or questions:
1. Check the full documentation: `TEMPLATES_DOCUMENTATION.md`
2. Verify admin permissions
3. Check database connection
4. Ensure all dependencies are loaded

## 🎉 You're Ready!

Start creating amazing templates with Handlebars syntax and modern UI components!
