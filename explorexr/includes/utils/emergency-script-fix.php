<?php
/**
 * Emergency Script Conflict Fix for ExploreXR
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
function ExploreXR_emergency_script_fix() {
    // Temporarily disable all ExploreXR scripts that might cause conflicts
    remove_action('wp_enqueue_scripts', 'ExploreXR_register_scripts', 15);
    remove_action('admin_enqueue_scripts', 'ExploreXR_register_scripts', 15);
    remove_action('wp_enqueue_scripts', 'ExploreXR_conditional_enqueue_scripts', 20);
    remove_action('admin_enqueue_scripts', 'ExploreXR_conditional_enqueue_scripts', 20);
    
    // Log the emergency fix activation
    if (explorexr_is_debug_enabled()) {
        ExploreXR_log('ExploreXR: Emergency script fix activated - all scripts disabled to prevent WordPress core corruption', 'error');
    }
}

/**
 * Check if emergency mode should be activated
 */
function ExploreXR_should_activate_emergency_mode() {
    // Check for emergency mode constants/parameters
    if (defined('ExploreXR_EMERGENCY_MODE') && ExploreXR_EMERGENCY_MODE) {
        return true;
    }
    
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Emergency mode check for corrupted installations
    if (isset($_GET['ExploreXR_emergency']) && sanitize_text_field(wp_unslash($_GET['ExploreXR_emergency'])) === '1') {
        return true;
    }
    
    // Check for signs of script corruption
    $signs_of_corruption = array(
        // Check if WordPress is loading properly
        !function_exists('wp_enqueue_script'),
        !function_exists('wp_script_is'),
        // Check for specific error conditions that indicate core corruption
        (isset($_SERVER['REQUEST_URI']) && strpos(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), 'wp-admin') !== false && 
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
function ExploreXR_complete_script_disable() {
    // Remove ALL ExploreXR script hooks
    remove_all_actions('wp_enqueue_scripts');
    remove_all_actions('admin_enqueue_scripts');
    
    // Prevent any ExploreXR scripts from loading
    add_filter('script_loader_src', 'ExploreXR_block_plugin_scripts', 10, 2);
    
    // Add emergency notice
    add_action('admin_notices', 'ExploreXR_emergency_notice');
}

/**
 * Block ExploreXR plugin scripts
 */
function ExploreXR_block_plugin_scripts($src, $handle) {
    if (strpos($src, 'explorexr') !== false || strpos($handle, 'explorexr') !== false) {
        return false; // Block ExploreXR scripts
    }
    return $src;
}

/**
 * Display emergency notice
 */
function ExploreXR_emergency_notice() {
    echo '<div class="notice notice-error"><p><strong>ExploreXR Emergency Mode:</strong> All plugin scripts disabled due to WordPress core conflicts. Please contact support.</p></div>';
}

/**
 * Gradual script re-enabling for safe recovery
 */
function ExploreXR_gradual_script_enable() {
    // Only allow specific safe scripts first
    $safe_scripts = array(
        'ExploreXR-utils', // Basic utilities only
    );
    
    add_filter('script_loader_src', function($src, $handle) use ($safe_scripts) {
        if (strpos($src, 'explorexr') !== false || strpos($handle, 'explorexr') !== false) {
            if (!in_array($handle, $safe_scripts)) {
                return false; // Still block most ExploreXR scripts
            }
        }
        return $src;
    }, 10, 2);
    
    add_action('admin_notices', function() {
        echo '<div class="notice notice-warning"><p><strong>ExploreXR Recovery Mode:</strong> Gradual script re-enabling active. Only essential scripts loaded.</p></div>';
    });
}

/**
 * Check if gradual recovery mode should be used
 */
function ExploreXR_use_gradual_recovery() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Emergency recovery mode check for corrupted installations
    return isset($_GET['ExploreXR_recovery']) && sanitize_text_field(wp_unslash($_GET['ExploreXR_recovery'])) === '1';
}

// Activate emergency mode or gradual recovery
if (ExploreXR_should_activate_emergency_mode()) {
    if (ExploreXR_use_gradual_recovery()) {
        add_action('plugins_loaded', 'ExploreXR_gradual_script_enable', 1);
    } else {
        add_action('plugins_loaded', 'ExploreXR_complete_script_disable', 1);
    }
}






