<?php
/**
 * ExploreXR Animation Add-On - Uninstall
 *
 * Uninstall routine for the ExploreXR Animation Add-On.
 */

// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Uninstall function for the plugin
 */
function explorexr_premium_animation_addon_uninstall() {
    // Remove addon-specific options
    delete_option('explorexr_premium_animation_ping_pong_enabled');
    delete_option('explorexr_premium_animation_crossfade_duration');
    delete_option('explorexr_premium_animation_show_controls');
    
    // Remove migration options
    delete_option('explorexr_premium_animation_migration_complete');
    delete_option('explorexr_premium_animation_migration_count');
    delete_option('explorexr_premium_animation_migration_date');
    
    // DO NOT remove settings from individual models
    // This preserves user data in case the plugin is reinstalled later
    // Model data is stored as post meta with keys like '_explorexr_premium_animation_enabled', etc.
    
    // Clear any transients
    delete_transient('explorexr_premium_animation_addon_activated');
    delete_transient('explorexr_premium_animation_migration_notice');
}

// Execute uninstall
explorexr_premium_animation_addon_uninstall();
