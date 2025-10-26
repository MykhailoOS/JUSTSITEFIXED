<?php
// Alternative router for beautiful URLs if .htaccess doesn't work
// This file should be renamed to index.php and placed in the root directory

// Check if this is a beautiful URL request
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// Remove query string
$requestUri = strtok($requestUri, '?');

// Remove script name from request URI
$path = str_replace($scriptName, '', $requestUri);
$path = trim($path, '/');

// Check if this is a beautiful user URL: /u/username/project-slug
if (preg_match('/^u\/([^\/]+)\/([^\/]+)\/?$/', $path, $matches)) {
    $usernameSlug = $matches[1];
    $projectSlug = $matches[2];
    
    // Include the user view handler
    $_GET['path'] = $usernameSlug . '/' . $projectSlug;
    include __DIR__ . '/u.php';
    exit;
}

// Check if this is a stats URL: /stats/project-slug
if (preg_match('/^stats\/([^\/]+)\/?$/', $path, $matches)) {
    $projectSlug = $matches[1];
    
    // Include the stats handler
    $_GET['slug'] = $projectSlug;
    include __DIR__ . '/stats.php';
    exit;
}

// Check if this is a view URL: /view/project-slug
if (preg_match('/^view\/([^\/]+)\/?$/', $path, $matches)) {
    $projectSlug = $matches[1];
    
    // Include the view handler
    $_GET['slug'] = $projectSlug;
    include __DIR__ . '/view.php';
    exit;
}

// If no match, include the main index.php
include __DIR__ . '/index.php';
?>
