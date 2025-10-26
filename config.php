<?php
// Basic configuration for JustSite PHP backend

// Update these according to your MySQL setup
define('DB_HOST', 'sql308.infinityfree.com');
define('DB_NAME', 'if0_39948852_just_db');
define('DB_USER', 'if0_39948852');
define('DB_PASS', 'MF10WtR86K8GIHA');

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

// App settings
define('APP_NAME', 'JustSite');
define('APP_BASE_URL', '/'); // set to '/' if site is in domain root. Use '/Code' if in subfolder

// Error reporting in development
error_reporting(E_ALL);
ini_set('display_errors', 1);


