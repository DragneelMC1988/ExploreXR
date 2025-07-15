<?php
/**
 * Emergency Script Conflict Fix for ExpoXR
 * 
 * This file temporarily disables problematic script loading
 * to prevent WordPress core file corruption
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Emergency function to disable problematic scripts
 */
function expoxr_emergency_script_fix() {
    // Temporarily disable all ExpoXR scripts that might cause conflicts
    remove_action('wp_enqueue_scripts', 'expoxr_register_scripts', 15);
    remove_action('admin_enqueue_scripts', 'expoxr_register_scripts', 15);
    remove_action('wp_enqueue_scripts', 'expoxr_conditional_enqueue_scripts', 20);
    remove_action('admin_enqueue_scripts', 'expoxr_conditional_enqueue_scripts', 20);
    
    // Log the emergency fix activation
    if (get_option('expoxr_debug_mode', false)) {
        error_log('ExpoXR: Emergency script fix activated - all scripts disabled to prevent WordPress core corruption');
    }
}

/**
 * Check if emergency mode should be activated
 */
function expoxr_should_activate_emergency_mode() {
    // Check for emergency mode constants/parameters
    if (defined('EXPOXR_EMERGENCY_MODE') && EXPOXR_EMERGENCY_MODE) {
        return true;
    }
    
    if (isset($_GET['expoxr_emergency']) && $_GET['expoxr_emergency'] === '1') {
        return true;
    }
    
    // Check for signs of script corruption
    $signs_of_corruption = array(
        // Check if WordPress is loading properly
        !function_exists('wp_enqueue_script'),
        !function_exists('wp_script_is'),
        // Check for specific error conditions that indicate core corruption
        (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== false && 
         !function_exists('wp_admin_css_uri'))
    );
    
    foreach ($signs_of_corruption as $condition) {
        if ($condition) {
            return true;
        }
    }
    
    // Check for any actual corruption signs
    // Return false for normal operation - emergency mode disabled
    return false;
}

/**
 * Complete script disable function
 */
function expoxr_complete_script_disable() {
    // Remove ALL ExpoXR script hooks
    remove_all_actions('wp_enqueue_scripts');
    remove_all_actions('admin_enqueue_scripts');
    
    // Prevent any ExpoXR scripts from loading
    add_filter('script_loader_src', 'expoxr_block_plugin_scripts', 10, 2);
    
    // Add emergency notice
    add_action('admin_notices', 'expoxr_emergency_notice');
}

/**
 * Block ExpoXR plugin scripts
 */
function expoxr_block_plugin_scripts($src, $handle) {
    if (strpos($src, 'explorexr') !== false || strpos($handle, 'explorexr') !== false) {
        return false; // Block ExpoXR scripts
    }
    return $src;
}

/**
 * Display emergency notice
 */
function expoxr_emergency_notice() {
    echo '<div class="notice notice-error"><p><strong>ExpoXR Emergency Mode:</strong> All plugin scripts disabled due to WordPress core conflicts. Please contact support.</p></div>';
}

/**
 * Gradual script re-enabling for safe recovery
 */
function expoxr_gradual_script_enable() {
    // Only allow specific safe scripts first
    $safe_scripts = array(
        'expoxr-utils', // Basic utilities only
    );
    
    add_filter('script_loader_src', function($src, $handle) use ($safe_scripts) {
        if (strpos($src, 'explorexr') !== false || strpos($handle, 'explorexr') !== false) {
            if (!in_array($handle, $safe_scripts)) {
                return false; // Still block most ExpoXR scripts
            }
        }
        return $src;
    }, 10, 2);
    
    add_action('admin_notices', function() {
        echo '<div class="notice notice-warning"><p><strong>ExpoXR Recovery Mode:</strong> Gradual script re-enabling active. Only essential scripts loaded.</p></div>';
    });
}

/**
 * Check if gradual recovery mode should be used
 */
function expoxr_use_gradual_recovery() {
    return isset($_GET['expoxr_recovery']) && $_GET['expoxr_recovery'] === '1';
}

// Activate emergency mode or gradual recovery
if (expoxr_should_activate_emergency_mode()) {
    if (expoxr_use_gradual_recovery()) {
        add_action('plugins_loaded', 'expoxr_gradual_script_enable', 1);
    } else {
        add_action('plugins_loaded', 'expoxr_complete_script_disable', 1);
    }
}





