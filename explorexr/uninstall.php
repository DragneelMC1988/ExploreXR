<?php
/**
 * ExploreXR Uninstaller
 * 
 * Cleans up all plugin data when the plugin is deleted (not just deactivated)
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
 * Remove all plugin data
 */
function expoxr_free_uninstall() {
    global $wpdb;

    // Remove all posts of type 'expoxr_model'
    $models = get_posts(array(
        'post_type' => 'expoxr_model',
        'numberposts' => -1,
        'fields' => 'ids'
    ));

    foreach ($models as $model_id) {
        // Delete all associated meta data
        delete_post_meta($model_id, '_expoxr_model_file');
        delete_post_meta($model_id, '_expoxr_model_poster');
        delete_post_meta($model_id, '_expoxr_model_width');
        delete_post_meta($model_id, '_expoxr_model_height');
        delete_post_meta($model_id, '_expoxr_camera_controls');
        delete_post_meta($model_id, '_expoxr_auto_rotate');
        delete_post_meta($model_id, '_expoxr_loading');
        delete_post_meta($model_id, '_expoxr_ar');
        delete_post_meta($model_id, '_expoxr_ios_src');
        delete_post_meta($model_id, '_expoxr_environment_image');
        delete_post_meta($model_id, '_expoxr_skybox_image');
        delete_post_meta($model_id, '_expoxr_enable_interactions');
        delete_post_meta($model_id, '_expoxr_file_missing');
        delete_post_meta($model_id, '_expoxr_model_size_source');
        delete_post_meta($model_id, '_expoxr_model_custom_width');
        delete_post_meta($model_id, '_expoxr_model_custom_height');
        
        // Force delete the post
        wp_delete_post($model_id, true);
    }

    // Remove all plugin options
    $options_to_delete = array(
        'expoxr_free_activated',
        'expoxr_model_viewer_version',
        'expoxr_loading_color',
        'expoxr_loading_text',
        'expoxr_loading_background_color',
        'expoxr_enable_loading_screen',
        'expoxr_loading_bar_color',
        'expoxr_loading_bar_background',
        'expoxr_loading_logo_url',
        'expoxr_loading_logo_width',
        'expoxr_loading_logo_height',
        'expoxr_script_loading_strategy',
        'expoxr_script_load_location',
        'expoxr_optimize_performance',
        'expoxr_enable_debug_mode',
        'expoxr_cache_models',
        'expoxr_preload_models',
        'expoxr_lazy_loading',
        'expoxr_compression_enabled',
        'expoxr_quality_settings',
        // Additional options found in codebase
        'expoxr_debug_mode',
        'expoxr_debug_log',
        'expoxr_debug_log_data',
        'expoxr_view_php_errors',
        'expoxr_console_logging',
        'expoxr_debug_ar_features',
        'expoxr_debug_camera_controls',
        'expoxr_debug_loading_info',
        'expoxr_cdn_source',
        'expoxr_max_upload_size',
        'expoxr_script_location',
        'expoxr_script_loading_timing',
        'expoxr_lazy_load_poster',
        'expoxr_lazy_load_model',
        'expoxr_loading_display',
        'expoxr_loading_bar_size',
        'expoxr_loading_bar_position',
        'expoxr_percentage_font_size',
        'expoxr_percentage_font_family',
        'expoxr_percentage_font_color',
        'expoxr_percentage_position',
        'expoxr_loading_text_position',
        'expoxr_loading_text_font_size',
        'expoxr_loading_text_font_family',
        'expoxr_loading_text_font_color',
        'expoxr_overlay_bg_color',
        'expoxr_overlay_bg_opacity',
        'expoxr_loading_type',
        'expoxr_large_model_handling',
        'expoxr_large_model_size_threshold',
        'expoxr_admin_notice'
    );

    foreach ($options_to_delete as $option) {
        delete_option($option);
    }

    // Final cleanup: Remove any remaining options that start with 'expoxr_' not covered above
    // This is required because:
    // 1. WordPress.org Plugin Directory Guidelines mandate complete data removal on uninstall
    // 2. Plugin may create transient/dynamic options during runtime that can't be statically tracked
    // 3. No WordPress core API exists for bulk wildcard option deletion
    // 4. This ensures 100% clean uninstall regardless of plugin usage patterns
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Necessary for complete plugin cleanup during uninstall, no alternative WordPress API for bulk wildcard deletion
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall is one-time operation, caching not applicable
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like('expoxr_') . '%'
    ));

    // Clean up transients (temporary cached data)
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Required for transient cleanup, no bulk WordPress API available
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall is one-time operation, caching not applicable  
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like('_transient_expoxr_') . '%',
        $wpdb->esc_like('_transient_timeout_expoxr_') . '%'
    ));

    // Clean up any uploaded model files
    $upload_dir = wp_upload_dir();
    $models_dir = $upload_dir['basedir'] . '/expoxr-models/';
    
    if (is_dir($models_dir)) {
        expoxr_free_remove_directory($models_dir);
    }

    // Also clean up the plugin's models directory if it exists
    $plugin_models_dir = WP_PLUGIN_DIR . '/expoxr/models/';
    if (is_dir($plugin_models_dir)) {
        expoxr_free_remove_directory($plugin_models_dir);
    }

    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Recursively remove directory and all contents
 * 
 * @param string $dir Directory path to remove
 */
function expoxr_free_remove_directory($dir) {
    if (!is_dir($dir)) {
        return;
    }

    // Initialize WP_Filesystem
    if (!function_exists('WP_Filesystem')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    WP_Filesystem();
    global $wp_filesystem;
    
    // Use WP_Filesystem to remove directory recursively
    if ($wp_filesystem) {
        $wp_filesystem->rmdir($dir, true);
    } else {
        // Fallback to manual removal if WP_Filesystem is not available
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $file_path = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($file_path)) {
                expoxr_free_remove_directory($file_path);
            } else {
                wp_delete_file($file_path);
            }
        }
        
        // Use WP_Filesystem rmdir if available
        if (function_exists('WP_Filesystem')) {
            WP_Filesystem();
            global $wp_filesystem;
            if ($wp_filesystem) {
                $wp_filesystem->rmdir($dir, false);
            }
        }
    }
}

// Run the uninstall
expoxr_free_uninstall();





