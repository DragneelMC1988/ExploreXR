<?php
/**
 * ExploreXR Uninstaller
 * 
 * Standard WordPress uninstall handler - removes only temporary data
 * Settings and uploaded models are preserved for user data retention
 */

// Exit if uninstall not called from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Only run if user has proper permissions
if (!current_user_can('activate_plugins')) {
    return;
}

// Double-check that we're uninstalling this plugin
if (plugin_basename(__FILE__) !== WP_UNINSTALL_PLUGIN) {
    return;
}

/**
 * Clean up temporary plugin data only
 * Preserves user settings and uploaded models
 */
function explorexr_free_uninstall() {
    global $wpdb;

    // Clean up temporary transients only (temporary cached data)
    // Use WordPress API to clean up transients individually for better compliance
    $transient_options = get_option('explorexr_temp_transients', array());
    if (!empty($transient_options)) {
        foreach ($transient_options as $transient_key) {
            delete_transient($transient_key);
        }
        delete_option('explorexr_temp_transients');
    }
    
    // Alternative: Clean up known transients individually
    delete_transient('explorexr_model_cache');
    delete_transient('explorexr_admin_stats');
    delete_transient('explorexr_file_validation');

    // Clean up temporary admin notices
    delete_option('explorexr_admin_notice');
    delete_option('explorexr_debug_log_data');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Run the minimal uninstall
explorexr_free_uninstall();





