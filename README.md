# BugForge - Bug Tracking & Project Management System (PHP Version)

BugForge is a comprehensive web application for tracking software bugs and managing projects. This PHP version provides all the core functionality without requiring a framework.

## Features

- **Bug Tracking**: Create, manage, and track software bugs with priority levels and status updates
- **Project Management**: Organize projects with timelines, progress tracking, and team assignments
- **Team Management**: Manage team members, roles, and project assignments
- **Responsive Design**: Works seamlessly across desktop and mobile devices
- **MySQL Database**: Persistent data storage for all application data

## Project Structure

```
bugforge/
├── assets/
│   └── css/
│       └── style.css          # Application styles
├── config/
│   └── database.php           # Database configuration
├── database/
│   └── schema.sql             # Database schema and sample data
├── includes/
│   ├── functions.php          # Application functions
│   ├── layout.php             # Main layout template
│   └── sidebar.php            # Sidebar component
├── index.php                  # Dashboard page
├── bugs.php                   # Bug tracking page
├── projects.php               # Project management page
└── teams.php                  # Team management page
```

## Getting Started

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)

### Installation

1. **Set up the database**:
   - Create a MySQL database named `bugforge`
   - Execute the SQL script in `database/schema.sql` to create tables and sample data

2. **Configure database connection**:
   - Update the database credentials in `config/database.php`

3. **Deploy files**:
   - Upload all files to your web server
   - Ensure the web server has read permissions for all files

4. **Access the application**:
   - Navigate to your web server's URL where you deployed the files
   - The application should be accessible at `http://your-domain.com/`

## Database Schema

The application uses a MySQL database with the following tables:

- `team_members`: Stores information about team members
- `projects`: Stores project information
- `bugs`: Stores bug reports
- `project_assignments`: Many-to-many relationship between team members and projects

## Pages

### Dashboard (`index.php`)
The main dashboard providing an overview of the application with statistics and quick actions.

### Bug Tracking (`bugs.php`)
Manage software bugs with features for:
- Creating new bug reports
- Viewing existing bugs in a table format
- Filtering by status and priority
- Assigning bugs to team members and projects

### Project Management (`projects.php`)
Organize and track projects with:
- Creating new projects with start/end dates
- Viewing project progress with visual indicators
- Managing project status (planning, active, completed, on-hold)

### Team Management (`teams.php`)
Manage team members with:
- Adding new team members
- Viewing team member details
- Assigning roles

## Hosting

This PHP application can be hosted on any standard web hosting provider that supports:
- PHP 7.4+
- MySQL 5.7+
- URL rewriting (for cleaner URLs, if desired)

Popular hosting options include:
- Shared hosting providers (Bluehost, HostGator, etc.)
- VPS hosting (DigitalOcean, Linode, etc.)
- Cloud hosting (AWS, Google Cloud, Azure)

## Contributing

Contributions are welcome! Please follow these steps:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.