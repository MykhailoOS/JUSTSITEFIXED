<?php
// Database configuration for infinityfree hosting
define('DB_HOST', 'sql308.infinityfree.com');
define('DB_USER', 'if0_39948852');
define('DB_PASS', 'MF10WtR86K8GIHA');
define('DB_NAME', 'if0_39948852_XXX');

// Create connection
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>