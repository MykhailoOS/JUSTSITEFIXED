<?php
/**
 * Test file for language API
 * This file helps debug the language switching API
 */

// Start session
session_start();

// Simulate a logged-in user for testing
$_SESSION['user_id'] = 1;

// Simulate POST data
$_POST['language'] = 'en';

echo "Testing language API...\n";
echo "Session user_id: " . ($_SESSION['user_id'] ?? 'not set') . "\n";
echo "POST language: " . ($_POST['language'] ?? 'not set') . "\n";

// Capture output
ob_start();

// Include the API file
include 'api/change_language.php';

// Get the output
$output = ob_get_clean();

echo "API Output:\n";
echo $output . "\n";

// Try to parse as JSON
$json = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "Valid JSON response:\n";
    print_r($json);
} else {
    echo "Invalid JSON. Error: " . json_last_error_msg() . "\n";
    echo "Raw output length: " . strlen($output) . "\n";
    echo "First 200 characters: " . substr($output, 0, 200) . "\n";
}
?>
