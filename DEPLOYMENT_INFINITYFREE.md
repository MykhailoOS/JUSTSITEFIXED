# Deployment Guide for InfinityFree Hosting

This guide explains how to deploy the BugForge application to InfinityFree hosting.

## Prerequisites

1. InfinityFree account (https://infinityfree.net/)
2. Database credentials provided by InfinityFree:
   - MySQL Username: if0_39948852
   - MySQL Password: ***************
   - MySQL Database Name: if0_39948852_XXX
   - MySQL Hostname: sql308.infinityfree.com
   - MySQL Port: 3306 (default)

## Deployment Steps

### 1. Set up the Database

1. Log in to your InfinityFree account
2. Go to the control panel and create a database if you haven't already
3. Note the database name, username, and password (you've already provided these)
4. Use a MySQL client (like phpMyAdmin) to execute the `database/schema.sql` file:
   - Go to phpMyAdmin in your InfinityFree control panel
   - Select your database (`if0_39948852_XXX`)
   - Click on the "Import" tab
   - Choose the `database/schema.sql` file from your computer
   - Click "Go" to execute the SQL script

### 2. Upload Files

1. Download all files from the bugforge directory
2. Use an FTP client (like FileZilla) or the InfinityFree file manager to upload files:
   - Connect to your InfinityFree account via FTP
   - Upload all files to the `htdocs` directory (or `public_html` if that's your setup)
   - Make sure to maintain the directory structure

### 3. Configure Database Connection

The database connection is already configured in `config/database.php` with your credentials. No additional changes are needed.

### 4. Test the Application

1. Visit your website URL (provided by InfinityFree)
2. You should see the BugForge dashboard
3. Test all functionality:
   - Navigate between pages using the sidebar
   - Create new bugs, projects, and team members
   - Edit and delete existing items

## Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Double-check the database credentials in `config/database.php`
   - Ensure the database name matches exactly what InfinityFree provided
   - Verify that the database user has proper permissions

2. **File Permissions**:
   - Ensure all PHP files have read permissions
   - Configuration files should not be publicly accessible (place outside web root if possible)

3. **URL Rewriting**:
   - This application doesn't require URL rewriting, so no .htaccess configuration is needed

### Support

If you encounter issues:
1. Check the InfinityFree support forums
2. Verify all file uploads completed successfully
3. Confirm the database was created and populated correctly
4. Check PHP error logs in the InfinityFree control panel

## Notes

- InfinityFree provides free hosting with some limitations
- Database connections may be slower than paid hosting
- Make sure to backup your data regularly
- The free tier may have resource limitations during high traffic periods