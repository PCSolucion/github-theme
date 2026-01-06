<?php
/**
 * Temporary debugging file to check for PHP errors
 * 
 * INSTRUCTIONS:
 * 1. Upload this file to your WordPress root directory
 * 2. Access it via: http://yoursite.com/debug-check.php
 * 3. It will show any PHP errors
 * 4. DELETE this file after debugging
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>WordPress Debug Check</h1>";
echo "<p>Checking for PHP errors...</p>";

// Try to load WordPress
define('WP_USE_THEMES', false);
require_once('./wp-load.php');

echo "<p style='color: green;'><strong>✓ WordPress loaded successfully!</strong></p>";

// Check theme
$current_theme = wp_get_theme();
echo "<h2>Current Theme</h2>";
echo "<p><strong>Name:</strong> " . $current_theme->get('Name') . "</p>";
echo "<p><strong>Version:</strong> " . $current_theme->get('Version') . "</p>";
echo "<p><strong>Template:</strong> " . $current_theme->get_template() . "</p>";

// Try to load theme functions
echo "<h2>Testing Theme Functions</h2>";
try {
    $functions_file = get_template_directory() . '/functions.php';
    if (file_exists($functions_file)) {
        echo "<p style='color: green;'>✓ functions.php exists</p>";
        
        // Check for syntax errors
        $output = shell_exec("php -l " . escapeshellarg($functions_file) . " 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<p style='color: green;'>✓ No syntax errors in functions.php</p>";
        } else {
            echo "<p style='color: red;'>✗ Syntax error in functions.php:</p>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
    } else {
        echo "<p style='color: red;'>✗ functions.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Memory Usage</h2>";
echo "<p>Current: " . size_format(memory_get_usage(true)) . "</p>";
echo "<p>Peak: " . size_format(memory_get_peak_usage(true)) . "</p>";
echo "<p>Limit: " . ini_get('memory_limit') . "</p>";

echo "<hr>";
echo "<p><strong>If you see this message without errors above, your theme is loading correctly.</strong></p>";
echo "<p><strong style='color: red;'>IMPORTANT: Delete this file after debugging!</strong></p>";
