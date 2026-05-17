<?php
/**
 * Uninstall ExploreXR AR Add-On
 *
 * @package ExploreXR AR Add-On
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all migration options
delete_option('explorexr_premium_ar_migration_completed');
delete_option('explorexr_premium_ar_migration_count');
delete_option('explorexr_premium_ar_migration_date');

// Remove settings
delete_option('explorexr_premium_ar_button_text');
delete_option('explorexr_premium_ar_fallback_text');
delete_option('explorexr_premium_ar_button_style');
delete_option('explorexr_premium_ar_modes');
delete_option('explorexr_premium_ar_bg_color');

// Clear any transients
delete_transient('explorexr_premium_ar_addon_activated');

// We don't delete AR data from models to prevent data loss
// If a user reinstalls the plugin, they'll want their AR settings to still be there
$users = get_users(array('fields' => 'ID'));
foreach ($users as $user_id) {
    delete_user_meta($user_id, 'explorexr_premium_ar_migration_notice_dismissed');
}

// The AR-specific post meta will be left in place when the plugin is uninstalled
// This ensures that if the plugin is reinstalled, the settings will still be available
// If you want to remove them, uncomment the code below:

/*
// Get all model posts
$models = get_posts(array(
    'post_type' => 'explorexr_premium_model',
    'posts_per_page' => -1,
    'post_status' => 'any'
));

// Meta keys to remove
$meta_keys = array(
    '_explorexr_premium_ar_enabled',
    '_explorexr_premium_ar_modes',
    '_explorexr_premium_ar_scale',
    '_explorexr_premium_ar_placement',
    '_explorexr_premium_ar_usdz_model',
    '_explorexr_premium_ar_button_text',
    '_explorexr_premium_ar_button_image',
    '_explorexr_premium_ar_xr_environment',
    '_explorexr_premium_ar_min_height',
    'explorexr_premium_ar_view_count',
    'explorexr_premium_ar_session_count',
    'explorexr_premium_ar_device_counts'
);

// Loop through each model and remove meta
foreach ($models as $model) {
    foreach ($meta_keys as $key) {
        delete_post_meta($model->ID, $key);
    }
}
*/
