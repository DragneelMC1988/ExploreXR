<?php
/**
 * AR Data Migration
 *
 * This file handles the migration of AR settings from the main plugin to the AR addon.
 *
 * @package ExploreXR AR Add-On
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize migration functions
 */
function explorexr_premium_ar_migration_init() {
    add_action('explorexr_premium_ar_addon_after_activation', 'explorexr_premium_ar_migrate_settings');
    
    // DISABLED: Migration hook was interfering with normal saves
    // This hook looked for underscore-prefixed fields that don't exist in current template
    // and would potentially overwrite AR settings on every save.
    // Migration is a ONE-TIME operation and should only run on activation.
    // add_action('save_post_explorexr_premium_model', 'explorexr_premium_ar_migrate_model_ar_settings', 10, 3);
    
    add_filter('explorexr_premium_addon_option_value', 'explorexr_premium_ar_backward_compatible_option_values', 10, 4);
}
add_action('init', 'explorexr_premium_ar_migration_init');

/**
 * Migrate AR settings from the main plugin to the addon
 */
function explorexr_premium_ar_migrate_settings() {
    // Check if migration has already been done
    $migration_completed = get_option('explorexr_premium_ar_migration_completed', false);
    if ($migration_completed) {
        return;
    }
    
    // Get all model posts
    $models = get_posts(array(
        'post_type' => 'explorexr_premium_model',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    if (empty($models)) {
        // No models to migrate, mark as completed
        update_option('explorexr_premium_ar_migration_completed', true);
        return;
    }
    
    $migration_count = 0;
    
    // Loop through each model
    foreach ($models as $model) {
        $model_id = $model->ID;
        
        // Check if model has AR settings in the main plugin
        $ar_enabled = get_post_meta($model_id, '_explorexr_premium_ar_enabled', true);
        
        // Only migrate if AR settings exist
        if (!empty($ar_enabled)) {
            // Map of meta keys to migrate
            $meta_keys = array(
                '_explorexr_premium_ar_enabled' => '_explorexr_premium_ar_enabled',
                '_explorexr_premium_ar_modes' => '_explorexr_premium_ar_modes',
                '_explorexr_premium_ar_scale' => '_explorexr_premium_ar_scale',
                '_explorexr_premium_ar_placement' => '_explorexr_premium_ar_placement',
                '_explorexr_premium_ar_usdz_model' => '_explorexr_premium_ar_usdz_model',
                '_explorexr_premium_ar_button_text' => '_explorexr_premium_ar_button_text',
                '_explorexr_premium_ar_button_image' => '_explorexr_premium_ar_button_image',
                '_explorexr_premium_ar_xr_environment' => '_explorexr_premium_ar_xr_environment',
                '_explorexr_premium_ar_min_height' => '_explorexr_premium_ar_min_height'
            );
            
            // Migrate each meta key
            foreach ($meta_keys as $old_key => $new_key) {
                $value = get_post_meta($model_id, $old_key, true);
                if (!empty($value)) {
                    update_post_meta($model_id, $new_key, $value);
                }
            }
            
            $migration_count++;
        }
    }
    
    // Mark migration as completed
    update_option('explorexr_premium_ar_migration_completed', true);
    update_option('explorexr_premium_ar_migration_count', $migration_count);
    update_option('explorexr_premium_ar_migration_date', current_time('mysql'));
    
    // Log migration results
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        error_log( sprintf( 'ExploreXR AR Add-on: Migrated AR settings for %d models.', $migration_count ) );
    }
}

/**
 * Run migration when plugin is activated
 */
function explorexr_premium_ar_run_migration() {
    // Schedule migration to run once after plugin activation
    if (!wp_next_scheduled('explorexr_premium_ar_migration_event')) {
        wp_schedule_single_event(time() + 10, 'explorexr_premium_ar_migration_event');
    }
}

// Note: We use a callback function instead of an activation hook here
// The activation hook is already registered in the main plugin file
add_action('explorexr_premium_ar_addon_after_activation', 'explorexr_premium_ar_run_migration');

/**
 * Hook for scheduled migration
 */
function explorexr_premium_ar_run_scheduled_migration() {
    explorexr_premium_ar_migrate_settings();
}
add_action('explorexr_premium_ar_migration_event', 'explorexr_premium_ar_run_scheduled_migration');

/**
 * Add migration notice in admin
 */
function explorexr_premium_ar_migration_notice() {
    // Only show to admins
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->id, array('explorexr_premium_model', 'edit-explorexr_premium_model', 'explorexr_premium_page_explorexr-premium-ar-settings'))) {
        return;
    }
    
    // Check if migration is completed
    $migration_completed = get_option('explorexr_premium_ar_migration_completed', false);
    $migration_count = get_option('explorexr_premium_ar_migration_count', 0);
    
    // Show completion notice if migration has been done
    if ($migration_completed && $migration_count > 0) {
        // Only show notice once per user
        $user_id = get_current_user_id();
        $notice_dismissed = get_user_meta($user_id, 'explorexr_premium_ar_migration_notice_dismissed', true);
        
        if ($notice_dismissed) {
            return;
        }
        
        ?>
        <div class="notice notice-success is-dismissible explorexr-premium-ar-migration-notice">
            <p>
                <strong><?php esc_html_e('AR Settings Migration Complete', 'explorexr-ar-addon'); ?></strong>
            </p>
            <p>
                <?php printf(
                    __('Successfully migrated AR settings for %d models from the main ExploreXR plugin to the AR Add-on.', 'explorexr-ar-addon'),
                    $migration_count
                ); ?>
            </p>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $(document).on('click', '.explorexr-premium-ar-migration-notice .notice-dismiss', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'explorexr_premium_ar_dismiss_migration_notice',
                            nonce: '<?php echo esc_js(wp_create_nonce('explorexr_premium_ar_dismiss_migration_notice')); ?>'
                        }
                    });
                });
            });
        </script>
        <?php
    }
}
add_action('admin_notices', 'explorexr_premium_ar_migration_notice');

/**
 * AJAX handler to dismiss migration notice
 */
function explorexr_premium_ar_dismiss_migration_notice() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(wp_unslash($_POST['nonce']), 'explorexr_premium_ar_dismiss_migration_notice')) {
        wp_die('Invalid security token');
    }
    
    // Update user meta to dismiss notice
    update_user_meta(get_current_user_id(), 'explorexr_premium_ar_migration_notice_dismissed', true);
    
    wp_die();
}
add_action('wp_ajax_explorexr_premium_ar_dismiss_migration_notice', 'explorexr_premium_ar_dismiss_migration_notice');

/**
 * Add migration status to AR settings page
 */
function explorexr_premium_ar_migration_status($content) {
    $migration_completed = get_option('explorexr_premium_ar_migration_completed', false);
    $migration_count = get_option('explorexr_premium_ar_migration_count', 0);
    $migration_date = get_option('explorexr_premium_ar_migration_date', '');
    
    if ($migration_completed) {
        $status = '<div class="explorexr-premium-migration-status">';
        $status .= '<h3>' . esc_html__('Data Migration Status', 'explorexr-ar-addon') . '</h3>';
        $status .= '<p><strong>' . esc_html__('Migration Status:', 'explorexr-ar-addon') . '</strong> ' . esc_html__('Completed', 'explorexr-ar-addon') . '</p>';
        $status .= '<p><strong>' . esc_html__('Models Migrated:', 'explorexr-ar-addon') . '</strong> ' . $migration_count . '</p>';
        
        if (!empty($migration_date)) {
            $status .= '<p><strong>' . esc_html__('Migration Date:', 'explorexr-ar-addon') . '</strong> ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($migration_date)) . '</p>';
        }
        
        $status .= '</div>';
        
        return $content . $status;
    }
    
    return $content;
}
add_filter('explorexr_premium_ar_settings_after_form', 'explorexr_premium_ar_migration_status');

/**
 * Add manual migration button to settings page
 */
function explorexr_premium_ar_add_migration_button($content) {
    $can_migrate = current_user_can('manage_options');
    
    if (!$can_migrate) {
        return $content;
    }
    
    $migration_completed = get_option('explorexr_premium_ar_migration_completed', false);
    
    $button = '<div class="explorexr-premium-manual-migration">';
    $button .= '<h3>' . esc_html__('AR Settings Migration', 'explorexr-ar-addon') . '</h3>';
    
    if ($migration_completed) {
        $button .= '<p>' . esc_html__('AR settings have already been migrated from the main ExploreXR plugin.', 'explorexr-ar-addon') . '</p>';
        $button .= '<p><a href="' . wp_nonce_url(add_query_arg('explorexr_premium_ar_force_migrate', '1'), 'explorexr_premium_ar_force_migrate', 'migrate_nonce') . '" class="button">' . esc_html__('Run Migration Again', 'explorexr-ar-addon') . '</a></p>';
    } else {
        $button .= '<p>' . esc_html__('If you had AR settings in the main ExploreXR plugin, you can migrate them to the AR Add-on using the button below.', 'explorexr-ar-addon') . '</p>';
        $button .= '<p><a href="' . wp_nonce_url(add_query_arg('explorexr_premium_ar_migrate', '1'), 'explorexr_premium_ar_migrate', 'migrate_nonce') . '" class="button button-primary">' . esc_html__('Migrate AR Settings', 'explorexr-ar-addon') . '</a></p>';
    }
    
    $button .= '</div>';
    
    return $content . $button;
}
add_filter('explorexr_premium_ar_settings_after_form', 'explorexr_premium_ar_add_migration_button');

/**
 * Handle manual migration request
 */
function explorexr_premium_ar_handle_manual_migration() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Handle migration request
    if (isset($_GET['explorexr_premium_ar_migrate']) && sanitize_text_field(wp_unslash($_GET['explorexr_premium_ar_migrate'])) == '1' && 
        isset($_GET['migrate_nonce']) && wp_verify_nonce(wp_unslash($_GET['migrate_nonce']), 'explorexr_premium_ar_migrate')) {
        
        // Reset migration status
        delete_option('explorexr_premium_ar_migration_completed');
        delete_option('explorexr_premium_ar_migration_count');
        delete_option('explorexr_premium_ar_migration_date');
        
        // Run migration
        explorexr_premium_ar_migrate_settings();
        
        // Redirect to settings page with success parameter
        wp_safe_redirect(add_query_arg('migration', 'success', remove_query_arg(array('explorexr_premium_ar_migrate', 'migrate_nonce'))));
        exit;
    }
    
    // Handle force migration request
    if (isset($_GET['explorexr_premium_ar_force_migrate']) && sanitize_text_field(wp_unslash($_GET['explorexr_premium_ar_force_migrate'])) == '1' && 
        isset($_GET['migrate_nonce']) && wp_verify_nonce(wp_unslash($_GET['migrate_nonce']), 'explorexr_premium_ar_force_migrate')) {
        
        // Reset migration status
        delete_option('explorexr_premium_ar_migration_completed');
        delete_option('explorexr_premium_ar_migration_count');
        delete_option('explorexr_premium_ar_migration_date');
        
        // Run migration
        explorexr_premium_ar_migrate_settings();
        
        // Redirect to settings page with success parameter
        wp_safe_redirect(add_query_arg('migration', 'success', remove_query_arg(array('explorexr_premium_ar_force_migrate', 'migrate_nonce'))));
        exit;
    }
}
add_action('admin_init', 'explorexr_premium_ar_handle_manual_migration');

/**
 * Show migration success notice
 */
function explorexr_premium_ar_migration_success_notice() {
    if (!isset($_GET['migration']) || sanitize_text_field(wp_unslash($_GET['migration'])) != 'success') {
        return;
    }
    
    $migration_count = get_option('explorexr_premium_ar_migration_count', 0);
    
    ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <strong><?php esc_html_e('AR Settings Migration Complete', 'explorexr-ar-addon'); ?></strong>        </p>
        <p>
            <?php printf(
                __('Successfully migrated AR settings for %d models from the main ExploreXR plugin to the AR Add-on.', 'explorexr-ar-addon'),
                $migration_count
            ); ?>
        </p>
    </div>
    <?php
}
add_action('admin_notices', 'explorexr_premium_ar_migration_success_notice');

/**
 * Migrate AR settings on model save
 * 
 * @param int $post_id The post ID
 * @param WP_Post $post The post object
 * @param bool $update Whether this is an update
 */
function explorexr_premium_ar_migrate_model_ar_settings($post_id, $post, $update) {
    // Skip autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Skip if not main post
    if (!$update || wp_is_post_revision($post_id)) {
        return;
    }
    
    // Check if we have AR settings from the metabox
    $has_ar_settings = false;
    $ar_fields = array(
        '_explorexr_premium_ar_enabled',
        '_explorexr_premium_ar_modes',
        '_explorexr_premium_ar_scale',
        '_explorexr_premium_ar_placement',
        '_explorexr_premium_ar_usdz_model',
        '_explorexr_premium_ar_button_text',
        '_explorexr_premium_ar_button_image',
        '_explorexr_premium_ar_xr_environment',
        '_explorexr_premium_ar_min_height'
    );
    
    foreach ($ar_fields as $field) {
        if (isset($_POST[$field])) {
            $has_ar_settings = true;
            break;
        }
    }
    
    // If we have AR settings, sync them to the addon settings
    if ($has_ar_settings) {
        // Get current addon settings
        $addon_settings = get_post_meta($post_id, '_explorexr_premium_addon_settings', true);
        if (!is_array($addon_settings)) {
            $addon_settings = array();
        }
        
        // Initialize AR addon settings if needed
        if (!isset($addon_settings['explorexr-premium-ar'])) {
            $addon_settings['explorexr-premium-ar'] = array();
        }
        
        // Sync enabled state
        if (isset($_POST['_explorexr_premium_ar_enabled'])) {
            $addon_settings['explorexr-premium-ar']['explorexr_premium_ar_enabled'] = true;
        } else {
            $addon_settings['explorexr-premium-ar']['explorexr_premium_ar_enabled'] = false;
        }
        
        // Sync AR modes
        if (isset($_POST['_explorexr_premium_ar_modes']) && is_array($_POST['_explorexr_premium_ar_modes'])) {
            $addon_settings['explorexr-premium-ar']['explorexr_premium_ar_modes'] = array_map('sanitize_text_field', array_map('wp_unslash', $_POST['_explorexr_premium_ar_modes']));
        }
        
        // Sync other fields
        $field_mapping = array(
            '_explorexr_premium_ar_scale' => 'explorexr_premium_ar_scale',
            '_explorexr_premium_ar_placement' => 'explorexr_premium_ar_placement',
            '_explorexr_premium_ar_usdz_model' => 'explorexr_premium_ar_usdz_model',
            '_explorexr_premium_ar_button_text' => 'explorexr_premium_ar_button_text',
            '_explorexr_premium_ar_button_image' => 'explorexr_premium_ar_button_image',
            '_explorexr_premium_ar_xr_environment' => 'explorexr_premium_ar_xr_environment',
            '_explorexr_premium_ar_min_height' => 'explorexr_premium_ar_min_height'
        );
        
        foreach ($field_mapping as $post_field => $addon_field) {
            if (isset($_POST[$post_field])) {
                $addon_settings['explorexr-premium-ar'][$addon_field] = sanitize_text_field($_POST[$post_field]);
            }
        }
        
        // Save updated addon settings
        update_post_meta($post_id, '_explorexr_premium_addon_settings', $addon_settings);
    }
}

/**
 * Provide backward compatible option values for AR settings
 * 
 * @param mixed $value The option value
 * @param string $option_key The option key
 * @param string $addon_slug The addon slug
 * @param int $post_id The post ID
 * @return mixed The option value
 */
function explorexr_premium_ar_backward_compatible_option_values($value, $option_key, $addon_slug, $post_id) {
    // Only handle AR addon options
    if ($addon_slug !== 'explorexr-premium-ar') {
        return $value;
    }
    
    // If the value is already set, return it
    if ($value !== null) {
        return $value;
    }
    
    // Map addon option keys to legacy meta keys
    $legacy_meta_map = array(
        'explorexr_premium_ar_enabled' => '_explorexr_premium_ar_enabled',
        'explorexr_premium_ar_modes' => '_explorexr_premium_ar_modes',
        'explorexr_premium_ar_scale' => '_explorexr_premium_ar_scale',
        'explorexr_premium_ar_placement' => '_explorexr_premium_ar_placement',
        'explorexr_premium_ar_usdz_model' => '_explorexr_premium_ar_usdz_model',
        'explorexr_premium_ar_button_text' => '_explorexr_premium_ar_button_text',
        'explorexr_premium_ar_button_image' => '_explorexr_premium_ar_button_image',
        'explorexr_premium_ar_xr_environment' => '_explorexr_premium_ar_xr_environment',
        'explorexr_premium_ar_min_height' => '_explorexr_premium_ar_min_height'
    );
    
    // If we have a legacy mapping, try to get the value from post meta
    if (isset($legacy_meta_map[$option_key])) {
        $meta_value = get_post_meta($post_id, $legacy_meta_map[$option_key], true);
        
        // Special handling for enabled state
        if ($option_key === 'explorexr_premium_ar_enabled') {
            return $meta_value === 'on';
        }
        
        // Special handling for array values
        if ($option_key === 'explorexr_premium_ar_modes' && !is_array($meta_value)) {
            if (empty($meta_value)) {
                return array('webxr', 'scene-viewer', 'quick-look');
            } else {
                return explode(',', $meta_value);
            }
        }
        
        return $meta_value;
    }
    
    return $value;
}
