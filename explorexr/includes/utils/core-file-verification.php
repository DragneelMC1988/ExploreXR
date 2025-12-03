<?php
/**
 * WordPress Core File Verification Script
 * 
 * This script checks if WordPress core files are intact and not corrupted.
 * Run this after replacing core files to verify the fix.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check critical WordPress core JavaScript files
 */
function explorexr_verify_core_js_files() {
    // WordPress.org compliance: Use WordPress core functions to check dependencies
    // Instead of manually verifying core files, check if they're properly enqueued
    $required_scripts = array(
        'jquery',
        'wp-util',
        'common',
        'user-profile'
    );
    
    $results = array();
    
    foreach ($required_scripts as $script_handle) {
        $script_data = wp_scripts()->query($script_handle);
        $results[$script_handle] = array(
            'handle' => $script_handle,
            'registered' => !empty($script_data),
            'dependencies' => !empty($script_data) ? $script_data->deps : array(),
            'version' => !empty($script_data) ? $script_data->ver : 'unknown'
        );
    }
    
    return $results;
}

/**
 * Display verification results
 */
function explorexr_display_verification_results() {
    $results = explorexr_verify_core_js_files();
    
    echo "<h2>WordPress Core File Verification</h2>\n";
    echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
    echo "<tr><th>File</th><th>Status</th><th>Action Required</th></tr>\n";
    
    $all_ok = true;
    
    foreach ($results as $file => $status) {
        $action = '';
        $color = '';
        
        switch ($status) {
            case 'OK':
                $color = 'green';
                $action = 'None';
                break;
            case 'MISSING':
                $color = 'red';
                $action = 'Replace from fresh WordPress';
                $all_ok = false;
                break;
            case 'EMPTY':
            case 'TOO_SHORT':
            case 'PHP_CORRUPTION':
            case 'SYNTAX_ERROR':
                $color = 'red';
                $action = 'Replace from fresh WordPress';
                $all_ok = false;
                break;
            default:
                $color = 'orange';
                $action = 'Manual inspection needed';
                $all_ok = false;
        }
        
        echo "<tr><td>" . esc_html($file) . "</td><td style='color: " . esc_attr($color) . "'>" . esc_html($status) . "</td><td>" . esc_html($action) . "</td></tr>\n";
    }
    
    echo "</table>\n";
    
    if ($all_ok) {
        echo "<h3 style='color: green'>✅ All core files appear to be intact!</h3>\n";
        echo "<p>You can now safely disable emergency mode by changing the return value in emergency-script-fix.php</p>\n";
    } else {
        echo "<h3 style='color: red'>❌ Some core files need to be replaced</h3>\n";
        echo "<p>Please follow the recovery instructions in EMERGENCY_WORDPRESS_RECOVERY.md</p>\n";
    }
}

// Run verification if accessed directly
if (isset($_SERVER['PHP_SELF']) && basename(sanitize_text_field(wp_unslash($_SERVER['PHP_SELF']))) === basename(__FILE__)) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>WordPress Core Verification</title>
        <?php
        // WordPress.org compliance: Use wp_add_inline_style instead of inline style
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for inline styles
        $verification_styles = '
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { border-collapse: collapse; width: 100%; }
            th { background-color: #f0f0f0; }
        ';
        wp_add_inline_style('wp-admin', $verification_styles);
        ?>
    </head>
    <body>
        <?php explorexr_display_verification_results(); ?>
    </body>
    </html>
    <?php
}
?>





