# Templates System Documentation

## Overview

The Templates system provides a powerful Handlebars-based template engine for creating reusable HTML components. It allows users to create templates with dynamic content and generate HTML output using JSON data.

## Features

- **Handlebars Template Engine**: Full support for Handlebars syntax including variables, loops, and conditionals
- **Modern UI**: Built with TailwindCSS, DaisyUI, and Alpine.js for a responsive and beautiful interface
- **Template Management**: Create, edit, delete, duplicate, and preview templates
- **Export/Import**: Export templates as JSON or download as HTML files
- **Real-time Preview**: Live preview of templates with sample data
- **Admin Integration**: Seamlessly integrated into the admin panel

## Architecture

### Frontend
- **TailwindCSS**: Utility-first CSS framework for styling
- **DaisyUI**: Component library built on TailwindCSS
- **Alpine.js**: Lightweight JavaScript framework for interactivity
- **Handlebars.js**: Client-side template compilation for preview

### Backend
- **PHP**: Server-side logic and API endpoints
- **MySQL**: Database storage for templates and metadata
- **Custom Handlebars Engine**: PHP implementation for server-side rendering

## File Structure

```
├── templates.php              # Main templates page
├── api/
│   ├── templates.php         # Templates API endpoints
│   └── seed_templates.php    # Sample templates data
├── init_templates.php        # Template initialization page
└── TEMPLATES_DOCUMENTATION.md # This documentation
```

## Database Schema

### Templates Table
```sql
CREATE TABLE templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) DEFAULT 'landing',
    type VARCHAR(50) DEFAULT 'html',
    template LONGTEXT NOT NULL,
    sample_data JSON,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    user_id INT,
    INDEX idx_category (category),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id)
);
```

## API Endpoints

### GET /api/templates.php
- `action=list`: List all templates
- `action=get&id={id}`: Get specific template
- `action=download&id={id}`: Download template as HTML
- `action=export`: Export all templates as JSON
- `action=preview&id={id}`: Preview template with sample data

### POST /api/templates.php
- `action=create`: Create new template
- `action=update`: Update existing template
- `action=duplicate`: Duplicate template

### DELETE /api/templates.php
- Delete template by ID

## Handlebars Syntax Support

### Variables
```handlebars
<h1>{{title}}</h1>
<p>{{description}}</p>
```

### Nested Properties
```handlebars
<h2>{{user.name}}</h2>
<p>{{user.email}}</p>
```

### Loops
```handlebars
{{#each items}}
<div class="item">
    <h3>{{title}}</h3>
    <p>{{description}}</p>
</div>
{{/each}}
```

### Conditionals
```handlebars
{{#if featured}}
<div class="badge">Featured</div>
{{/if}}
```

## Sample Templates

The system comes with 5 pre-built sample templates:

1. **Hero Section**: Modern hero section with CTA button
2. **Feature Cards**: Responsive feature cards with icons
3. **Blog Post Card**: Blog post card with metadata
4. **Pricing Table**: Three-tier pricing table
5. **Contact Form**: Professional contact form

## Usage Examples

### Creating a Template

1. Navigate to Templates page
2. Click "Create New Template"
3. Fill in template details:
   - Name: "My Component"
   - Description: "A custom component"
   - Category: "landing"
   - Type: "component"
4. Enter Handlebars template code:
```handlebars
<div class="card">
    <h2>{{title}}</h2>
    <p>{{content}}</p>
</div>
```
5. Add sample data:
```json
{
    "title": "Hello World",
    "content": "This is sample content"
}
```
6. Click "Save Template"

### Using Templates in Code

```php
// Get template from database
$stmt = $pdo->prepare('SELECT template, sample_data FROM templates WHERE id = ?');
$stmt->execute([$templateId]);
$template = $stmt->fetch();

// Generate HTML
$html = generateHtmlFromTemplate($template['template'], json_decode($template['sample_data'], true));
echo $html;
```

## Installation

1. Ensure database connection is configured
2. Navigate to `/init_templates.php` to create sample templates
3. Access templates via `/templates.php`

## Security

- Admin-only access required
- Input validation and sanitization
- SQL injection prevention with prepared statements
- XSS protection with htmlspecialchars()

## Performance

- Database indexing on frequently queried columns
- Efficient template compilation
- Caching of compiled templates (future enhancement)

## Future Enhancements

- Template versioning
- Template sharing between users
- Advanced Handlebars helpers
- Template marketplace
- Real-time collaboration
- Template analytics

## Troubleshooting

### Common Issues

1. **Templates not loading**: Check database connection and table creation
2. **Preview not working**: Ensure Handlebars.js is loaded
3. **Styling issues**: Verify TailwindCSS and DaisyUI are properly loaded

### Debug Mode

Enable debug mode by adding to templates.php:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Contributing

When adding new features:
1. Follow existing code patterns
2. Add proper error handling
3. Update documentation
4. Test with sample data
5. Ensure mobile responsiveness
