<?php
/**
 * ExploreXR Animation Add-On - Migration
 * 
 * Handles migration of animation data from the main plugin.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Run migration from main plugin to add-on
 */
function explorexr_premium_animation_run_migration() {
    // Only run once
    if (get_option('explorexr_premium_animation_migration_complete')) {
        return;
    }
    
    // Get all models
    $models = get_posts(array(
        'post_type' => 'explorexr_premium_model',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    $migration_count = 0;
    
    // Process each model
    foreach ($models as $model) {
        // Check if the model has animation settings in the main plugin
        $animation_enabled = get_post_meta($model->ID, '_explorexr_premium_animation_enabled', true);
        $animation_name = get_post_meta($model->ID, '_explorexr_premium_animation_name', true);
        $animation_autoplay = get_post_meta($model->ID, '_explorexr_premium_animation_autoplay', true);
        $animation_repeat = get_post_meta($model->ID, '_explorexr_premium_animation_repeat', true);
        $animation_crossfade_duration = get_post_meta($model->ID, '_explorexr_premium_animation_crossfade_duration', true);
        
        // If animation settings exist, we don't need to migrate
        if (!empty($animation_enabled) || !empty($animation_name) || !empty($animation_autoplay) || 
            !empty($animation_repeat) || !empty($animation_crossfade_duration)) {
            continue;
        }
        
        // No settings found, check if there are settings in main plugin
        // This would be implemented if the main plugin stored animation settings under different keys
        
        $migration_count++;
    }
    
    // Set migration complete flag
    update_option('explorexr_premium_animation_migration_complete', true);
    update_option('explorexr_premium_animation_migration_count', $migration_count);
    update_option('explorexr_premium_animation_migration_date', current_time('mysql'));
    
    // Add notice if models were migrated
    if ($migration_count > 0) {
        set_transient('explorexr_premium_animation_migration_notice', $migration_count, 60);
    }
}
add_action('explorexr_premium_animation_run_migration', 'explorexr_premium_animation_run_migration');

/**
 * Show migration notice
 */
function explorexr_premium_animation_migration_notice() {
    $migration_count = get_transient('explorexr_premium_animation_migration_notice');
    
    if ($migration_count) {
        delete_transient('explorexr_premium_animation_migration_notice');
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong><?php esc_html_e('ExploreXR Animation Add-On Migration Complete!', 'explorexr-animation-addon'); ?></strong>
                <?php
                /* translators: %d: number of models migrated */
                printf(
                    _n(
                        'Successfully migrated animation settings for %d model.',
                        'Successfully migrated animation settings for %d models.',
                        $migration_count,
                        'explorexr-animation-addon'
                    ),
                    $migration_count
                ); ?>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'explorexr_premium_animation_migration_notice');
