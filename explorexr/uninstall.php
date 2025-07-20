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
function expoxr_free_uninstall() {
    global $wpdb;

    // Clean up temporary transients only (temporary cached data)
    // Direct database query is required here because:
    // 1. WordPress has no bulk API for deleting transients by prefix
    // 2. This is uninstall cleanup - one-time operation that doesn't need caching
    // 3. Individual delete_transient() calls would be inefficient for bulk cleanup
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Required for bulk transient cleanup during uninstall, no WordPress API alternative
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall is one-time operation, caching not applicable  
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like('_transient_expoxr_') . '%',
        $wpdb->esc_like('_transient_timeout_expoxr_') . '%'
    ));

    // Clean up temporary admin notices
    delete_option('expoxr_admin_notice');
    delete_option('expoxr_debug_log_data');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Run the minimal uninstall
expoxr_free_uninstall();





