<?php
/**
 * ExploreXR AR Addon - Migration: Apply button defaults
 *
 * One-time migration to fix existing AR-enabled models with missing defaults.
 * This runs once after upgrading to v1.0.7 to fix models broken by removal
 * of defaults in v1.0.6 (commits 73ef85d, a2b9f16).
 *
 * @package ExploreXR_AR_Addon
 * @since 1.0.7
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Migrate AR button defaults to existing models
 *
 * Applies sensible defaults to AR-enabled models that have never been configured.
 * Only runs once and respects user intent (doesn't override explicitly set values).
 *
 * @return int Number of models updated
 */
function explorexr_ar_migrate_button_defaults() {
    // Check if migration already run
    if (get_option('explorexr_ar_migration_defaults_v106', false)) {
        return 0;
    }

    global $wpdb;

    // Find all models with AR enabled
    $ar_enabled_models = $wpdb->get_col("
        SELECT post_id
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_explorexr_premium_ar_enabled'
        AND meta_value = 'on'
    ");

    if (empty($ar_enabled_models)) {
        // No AR-enabled models, mark migration as complete
        update_option('explorexr_ar_migration_defaults_v106', true);
        return 0;
    }

    $updated_count = 0;

    foreach ($ar_enabled_models as $model_id) {
        $needs_update = false;

        // Check button text - only add if NEVER SET
        if (!metadata_exists('post', $model_id, '_explorexr_premium_ar_button_text')) {
            update_post_meta($model_id, '_explorexr_premium_ar_button_text', 'View in AR');
            $needs_update = true;

            if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
                error_log(sprintf(
                    '[ExploreXR AR Migration] Model ID %d: Added default button text "View in AR"',
                    $model_id
                ));
            }
        }

        // Check background color
        if (!metadata_exists('post', $model_id, '_explorexr_premium_ar_button_bg_color')) {
            update_post_meta($model_id, '_explorexr_premium_ar_button_bg_color', '#000000');
            $needs_update = true;
        }

        // Check text color
        if (!metadata_exists('post', $model_id, '_explorexr_premium_ar_button_text_color')) {
            update_post_meta($model_id, '_explorexr_premium_ar_button_text_color', '#ffffff');
            $needs_update = true;
        }

        // Check button size
        if (!metadata_exists('post', $model_id, '_explorexr_premium_ar_button_size')) {
            update_post_meta($model_id, '_explorexr_premium_ar_button_size', 'medium');
            $needs_update = true;
        }

        // Check border radius
        if (!metadata_exists('post', $model_id, '_explorexr_premium_ar_button_border_radius')) {
            update_post_meta($model_id, '_explorexr_premium_ar_button_border_radius', '4');
            $needs_update = true;
        }

        // Check button position
        if (!metadata_exists('post', $model_id, '_explorexr_premium_ar_button_position')) {
            update_post_meta($model_id, '_explorexr_premium_ar_button_position', 'bottom-center');
            $needs_update = true;
        }

        if ($needs_update) {
            $updated_count++;
        }
    }

    // Mark migration as complete
    update_option('explorexr_ar_migration_defaults_v106', true);

    if (defined('WP_DEBUG') && WP_DEBUG && WP_DEBUG_LOG) {
        error_log(sprintf(
            '[ExploreXR AR Migration] Complete: Updated %d models with default button settings (out of %d AR-enabled models)',
            $updated_count,
            count($ar_enabled_models)
        ));
    }

    return $updated_count;
}

/**
 * Run migration on admin init
 *
 * Executes once when admin loads to ensure models are fixed before user visits pages.
 */
function explorexr_ar_run_migration() {
    // Only run in admin context
    if (!is_admin()) {
        return;
    }

    // Run migration
    explorexr_ar_migrate_button_defaults();
}
add_action('admin_init', 'explorexr_ar_run_migration');
