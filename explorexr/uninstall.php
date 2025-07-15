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
        'expoxr_loading_animation_type',
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
        'expoxr_quality_settings'
    );

    foreach ($options_to_delete as $option) {
        delete_option($option);
    }

    // Remove any remaining options that start with 'expoxr_'
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Required for complete plugin cleanup during uninstall
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall script doesn't require caching
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Required for complete plugin cleanup during uninstall
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        'expoxr_%'
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
    } else {
        // Only use WP_Filesystem if available
        if (function_exists('WP_Filesystem')) {
            WP_Filesystem();
            global $wp_filesystem;
            if ($wp_filesystem) {
                $wp_filesystem->rmdir($dir, false);
            }
        }
    }
    }
}

// Run the uninstall
expoxr_free_uninstall();





