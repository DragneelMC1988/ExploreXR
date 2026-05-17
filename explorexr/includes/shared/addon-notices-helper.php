<?php
/**
 * ExploreXR Addon Notices Helper
 * 
 * Shared helper functions for displaying standardized notices across all addons
 * Integrates with Premium Plugin's centralized notice system
 * 
 * @package ExploreXR_Shared
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display addon license notice
 * Standardized notice for all addons requiring license activation
 * 
 * @param string $addon_name Display name of the addon (e.g., "Animation Add-On")
 * @param string $addon_slug Slug identifier for the addon
 * @return void
 */
if (!function_exists('explorexr_addon_license_notice')) :
function explorexr_addon_license_notice($addon_name, $addon_slug) {
    $screen = get_current_screen();
    if (!$screen || ($screen->id !== 'plugins' && strpos($screen->id, 'explorexr') === false)) {
        return;
    }
    
    // Check if license is active
    $is_license_active = function_exists('explorexr_premium_is_license_active') && explorexr_premium_is_license_active();
    
    // Show notice only if license is not active
    if (!$is_license_active) {
        // Try to use centralized notice system if available
        if (function_exists('explorexr_admin_notices')) {
            $message = sprintf(
                '<strong>ExploreXR %s</strong><br>This addon requires an active ExploreXR Premium license. Please <a href="%s">activate your license</a> to use this addon.',
                esc_html($addon_name),
                esc_url(admin_url('admin.php?page=explorexr-license'))
            );
            explorexr_admin_notices()->warning($message, true, 'addon_license_' . $addon_slug);
        } else {
            // Fallback to standard WordPress notice
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong><?php echo esc_html('ExploreXR ' . $addon_name); ?></strong><br>
                    <?php
                    $license_url = esc_url(admin_url('admin.php?page=explorexr-license'));
                    $message = sprintf(
                        /* translators: %s: URL to the ExploreXR license page */
                        __('This addon requires an active ExploreXR Premium license. Please <a href="%s">activate your license</a> to use this addon.', 'explorexr'),
                        $license_url
                    );
                    echo wp_kses_post($message);
                    ?>
                </p>
            </div>
            <?php
        }
    }
}

endif;

/**
 * Display addon missing Premium plugin notice
 * Standardized error notice when Premium plugin is not active
 * 
 * @param string $addon_name Display name of the addon
 * @return void
 */
if (!function_exists('explorexr_addon_missing_premium_notice')) :
function explorexr_addon_missing_premium_notice($addon_name) {
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php echo esc_html('ExploreXR ' . $addon_name); ?> requires the ExploreXR Premium plugin.</strong><br>
            <?php esc_html_e('Please install and activate ExploreXR Premium first.', 'explorexr'); ?>
        </p>
    </div>
    <?php
}

endif;

/**
 * Display addon welcome notice after activation
 * Standardized success notice shown once after addon activation
 * 
 * @param string $addon_name Display name of the addon
 * @param string $addon_slug Slug identifier for dismissible tracking
 * @param string $description Optional description text
 * @return void
 */
if (!function_exists('explorexr_addon_welcome_notice')) :
function explorexr_addon_welcome_notice($addon_name, $addon_slug, $description = '') {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <strong><?php echo esc_html('ExploreXR ' . $addon_name . ' activated!'); ?></strong><br>
            <?php echo $description ? esc_html($description) : ''; ?>
        </p>
        <p>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=explorexr_premium_model')); ?>" class="button button-primary">
                <?php esc_html_e('Edit Models', 'explorexr'); ?>
            </a>
        </p>
    </div>
    <?php
}

endif;

/**
 * Display addon missing dependency notice
 * For addons that require other plugins (e.g., WooCommerce)
 * 
 * @param string $addon_name Display name of the addon
 * @param string $required_plugin Name of required plugin
 * @param string $required_slug Slug of required plugin
 * @return void
 */
if (!function_exists('explorexr_addon_missing_dependency_notice')) :
function explorexr_addon_missing_dependency_notice($addon_name, $required_plugin, $required_slug = '') {
    $plugin_link = $required_slug 
        ? sprintf('<a href="%s">%s</a>', esc_url(admin_url('plugin-install.php?s=' . $required_slug . '&tab=search')), esc_html($required_plugin))
        : esc_html($required_plugin);
    
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php echo esc_html('ExploreXR ' . $addon_name); ?> requires <?php echo wp_kses_post($plugin_link); ?>.</strong><br>
            <?php
            printf(
                /* translators: %s: Required plugin name. */
                esc_html__('Please install and activate %s first.', 'explorexr'),
                esc_html($required_plugin)
            );
            ?>
        </p>
    </div>
    <?php
}

endif;

/**
 * Check if addon should display notices
 * Helper to determine if notices should be shown on current screen
 * 
 * @return bool True if on plugins or ExploreXR pages
 */
if (!function_exists('explorexr_addon_should_show_notices')) :
function explorexr_addon_should_show_notices() {
    $screen = get_current_screen();
    if (!$screen) {
        return false;
    }
    
    // Show on plugins page
    if ($screen->id === 'plugins') {
        return true;
    }
    
    // Show on any ExploreXR admin page
    if (strpos($screen->id, 'explorexr') !== false) {
        return true;
    }
    
    // Show on explorexr_premium_model post type pages
    if (isset($screen->post_type) && $screen->post_type === 'explorexr_premium_model') {
        return true;
    }
    
    return false;
}

endif;

/**
 * Register addon notice hooks with proper priority
 * Ensures addon notices integrate with Premium plugin notice system
 * 
 * @param callable $callback Notice callback function
 * @param int $priority Priority for admin_notices hook (default 5)
 * @return void
 */
if (!function_exists('explorexr_addon_register_notice')) :
function explorexr_addon_register_notice($callback, $priority = 5) {
    if (!is_callable($callback)) {
        return;
    }
    
    // Register at priority 5 (after Premium plugin's priority 1, before WordPress default 10)
    add_action('admin_notices', $callback, $priority);
}
endif;
