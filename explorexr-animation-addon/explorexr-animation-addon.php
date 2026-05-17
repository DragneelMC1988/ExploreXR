<?php
/**
 * Plugin Name: ExploreXR - Animation Add-On
 * Plugin URI: https://expoxr.com/addon/animation/
 * Description: Adds advanced animation controls and features to ExploreXR 3D model viewer.
 * Version: 1.3.1
 * Author: Ayal Othman
 * Author URI: https://ExploreXR.de
 * Text Domain: explorexr-premium-animation-addon
 * License: GPL2
 * Requires at least: 5.6
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EXPLOREXR_ANIMATION_VERSION', '1.3.1');
define('EXPLOREXR_ANIMATION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EXPLOREXR_ANIMATION_PLUGIN_URL', plugin_dir_url(__FILE__));

// Backward-compat aliases retained for one release cycle
if (!defined('explorexr_premium_ANIMATION_VERSION')) {
    define('explorexr_premium_ANIMATION_VERSION', EXPLOREXR_ANIMATION_VERSION);
}
if (!defined('explorexr_premium_ANIMATION_PLUGIN_DIR')) {
    define('explorexr_premium_ANIMATION_PLUGIN_DIR', EXPLOREXR_ANIMATION_PLUGIN_DIR);
}
if (!defined('explorexr_premium_ANIMATION_PLUGIN_URL')) {
    define('explorexr_premium_ANIMATION_PLUGIN_URL', EXPLOREXR_ANIMATION_PLUGIN_URL);
}

// Load shared addon notice helper from whichever host (Premium or Free) ships it.
foreach (array(
    WP_PLUGIN_DIR . '/explorexr-premium/includes/shared/addon-notices-helper.php',
    WP_PLUGIN_DIR . '/explorexr/includes/shared/addon-notices-helper.php',
) as $explorexr_shared_helper) {
    if (file_exists($explorexr_shared_helper)) {
        require_once $explorexr_shared_helper;
        break;
    }
}
unset($explorexr_shared_helper);

/**
 * Check if ExploreXR Premium plugin is active
 * Uses the new centralized check function from Premium plugin
 */
function explorexr_premium_animation_addon_check_main_plugin() {
    // Use the new centralized function if available
    if (function_exists('explorexr_premium_is_active')) {
        if (!explorexr_premium_is_active()) {
            add_action('admin_notices', 'explorexr_premium_animation_addon_missing_premium_notice');
            return false;
        }
        return true;
    }
    
    // Fallback: host-agnostic — accept either Free or Premium so long as the manager is present.
    return class_exists('ExploreXR_Addon_Manager');
}

/**
 * Display notice if ExploreXR Premium plugin is missing
 * Uses shared helper function for standardized notices
 */
function explorexr_premium_animation_addon_missing_premium_notice() {
    if (function_exists('explorexr_addon_missing_premium_notice')) {
        explorexr_addon_missing_premium_notice('Animation Add-On');
    }
}


/**
 * Check if this add-on is licensed for the current installation
 */
function explorexr_premium_animation_addon_is_licensed() {
    // Use the standard addon license check function from Premium plugin
    if (function_exists('explorexr_premium_is_addon_licensed')) {
        return explorexr_premium_is_addon_licensed('animation');
    }
    
    // If Premium plugin is not loaded or function not available, addon is not licensed
    return false;
}

/**
 * Register addon with the Addon Manager
 * This runs independently of licensing to ensure addon always appears in lists
 */
function explorexr_premium_animation_addon_register() {
    // Register with whichever host (Free or Premium) provides the Addon Manager.
    if (!class_exists('ExploreXR_Addon_Manager')) {
        return;
    }
    
    $addon_manager = ExploreXR_Addon_Manager::get_instance();
    if ($addon_manager) {
        $addon_manager->register_addon('animation', array(
            'name' => 'Animation Add-On',
            'version' => EXPLOREXR_ANIMATION_VERSION,
            'min_core_version' => '0.2.0',
            'max_core_version' => '2.0.0',
            'file' => __FILE__,
            'main_class' => 'ExploreXR_Animation_Handler',
            'dependencies' => array(),
            'description' => 'Adds advanced animation controls and features to ExploreXR 3D model viewer.',
            'settings_page' => 'explorexr-premium-animation-settings',
            'default_options' => array(
                'explorexr_premium_animation_ping_pong_enabled' => 'on',
                'explorexr_premium_animation_crossfade_duration' => '300',
                'explorexr_premium_animation_show_controls' => 'on'
            )
        ));
    }
}
add_action('init', 'explorexr_premium_animation_addon_register', 15);

/**
 * Main initialization function
 */
function explorexr_premium_animation_addon_init() {
    // Check for main plugin
    if (!explorexr_premium_animation_addon_check_main_plugin()) {
        return;
    }
    
    // Always load settings file (needed for settings page even if not licensed)
    if (file_exists(EXPLOREXR_ANIMATION_PLUGIN_DIR . 'includes/settings.php')) {
        require_once(EXPLOREXR_ANIMATION_PLUGIN_DIR . 'includes/settings.php');
    }
    
    // Check license BEFORE loading handler to prevent unlicensed functionality
    if (!explorexr_premium_animation_addon_is_licensed()) {
        add_action('admin_notices', 'explorexr_premium_animation_addon_license_notice');
        return; // Don't load handler or additional functionality without a valid license
    }
    
    // Load animation handler only when licensed
    require_once(EXPLOREXR_ANIMATION_PLUGIN_DIR . 'includes/animation-handler.php');
    
    // Load addon UI functionality if licensed
    // Note: Legacy metabox (animation-metabox.php) removed - replaced by modern CardUI (animation-settings-card.php)
    require_once(EXPLOREXR_ANIMATION_PLUGIN_DIR . 'includes/migration.php');
    
    // Register scripts and styles
    add_action('wp_enqueue_scripts', 'explorexr_premium_animation_addon_enqueue_scripts');
    add_action('admin_enqueue_scripts', 'explorexr_premium_animation_addon_admin_enqueue_scripts');
    
    // Register settings
    add_action('admin_init', 'explorexr_premium_animation_addon_register_settings');
    
    // Register integration with main plugin
    add_filter('explorexr_premium_addon_available', 'explorexr_premium_animation_addon_register_availability', 10, 2);
    
    // Note: The main model attribute filter is added in animation-handler.php
    // This duplicate registration has been removed to prevent conflicts
    
    // Add admin notice after activation
    add_action('admin_notices', 'explorexr_premium_animation_addon_admin_notices');
}

/**
 * Display license notice
 * Uses shared helper function for standardized notices
 */
function explorexr_premium_animation_addon_license_notice() {
    if (function_exists('explorexr_addon_license_notice')) {
        explorexr_addon_license_notice('Animation Add-On', 'animation');
    }
}
add_action('plugins_loaded', 'explorexr_premium_animation_addon_init');

/**
 * Initialize plugin update checker
 * Checks for updates from private update server
 */
function explorexr_animation_addon_init_updater() {
    if (!is_admin()) {
        return;
    }
    
    // Try to load shared updater class from whichever host ships it.
    foreach (array(
        WP_PLUGIN_DIR . '/explorexr-premium/includes/shared/class-explorexr-addon-updater.php',
        WP_PLUGIN_DIR . '/explorexr/includes/shared/class-explorexr-addon-updater.php',
    ) as $shared_updater) {
        if (file_exists($shared_updater)) {
            require_once $shared_updater;
            if (class_exists('ExploreXR_Addon_Updater')) {
                new ExploreXR_Addon_Updater(
                    __FILE__,
                    'https://update.expoxr.com/explorexr/premium/addon-animation/explorexr-animation-addon.json',
                    'explorexr-animation-addon'
                );
            }
            break;
        }
    }
}
add_action('plugins_loaded', 'explorexr_animation_addon_init_updater', 20);

/**
 * Enqueue scripts and styles
 */
function explorexr_premium_animation_addon_enqueue_scripts() {
    // Only enqueue on pages with model viewers
    global $post;
    $has_model_viewers = false;
    
    if (function_exists('explorexr_premium_has_model_viewers') && explorexr_premium_has_model_viewers($post)) {
        $has_model_viewers = true;
    }

    // Fallback: check shortcode content or post type directly
    if (!$has_model_viewers && $post && (has_shortcode($post->post_content, 'explorexr_model') || has_shortcode($post->post_content, 'explorexr_premium_model') || get_post_type($post) === 'explorexr_model')) {
        $has_model_viewers = true;
    }

    if ($has_model_viewers) {
        // Premium plugin handles model-viewer registration at priority 5
        // No need to enqueue it here - it's already available
        
        // Now load our animation handler
        // Note: model-viewer is loaded via the template system, not wp_enqueue_scripts,
        // so we must NOT declare it as a script dependency (it's not registered on frontend).
        wp_enqueue_script('explorexr-premium-animation-handler', EXPLOREXR_ANIMATION_PLUGIN_URL . 'assets/js/animation-handler.js', array('jquery'), EXPLOREXR_ANIMATION_VERSION, true);
        wp_enqueue_style('explorexr-premium-animation-styles', EXPLOREXR_ANIMATION_PLUGIN_URL . 'assets/css/animation-styles.css', array(), EXPLOREXR_ANIMATION_VERSION);
        
        // Localize script for AJAX
        wp_localize_script('explorexr-premium-animation-handler', 'explorexr_premium_animation_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('explorexr_premium_ajax'), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ));
    }
}

/**
 * Enqueue admin scripts and styles
 */
function explorexr_premium_animation_addon_admin_enqueue_scripts($hook) {
    // Only enqueue on our settings page, model edit pages, and custom edit model page
    // Check for both old and new page slugs
    if (strpos($hook, 'explorexr-premium-animation-settings') !== false ||
        (in_array(get_post_type(), array('explorexr_premium_model', 'explorexr_model'), true) && ($hook === 'post.php' || $hook === 'post-new.php')) ||
        strpos($hook, 'explorexr-premium-edit-model') !== false ||
        strpos($hook, 'explorexr-edit-model') !== false ||
        (isset($_GET['page']) && $_GET['page'] === 'explorexr-edit-model')) {
        
        // Card-based UI assets
        wp_enqueue_style('animation-admin-card-css', EXPLOREXR_ANIMATION_PLUGIN_URL . 'assets/css/animation-admin-card.css', array(), EXPLOREXR_ANIMATION_VERSION);
        wp_enqueue_script('animation-admin-preview-js', EXPLOREXR_ANIMATION_PLUGIN_URL . 'assets/js/animation-admin-preview.js', array('jquery'), EXPLOREXR_ANIMATION_VERSION, true);
        wp_enqueue_style('explorexr-premium-animation-addon-card', EXPLOREXR_ANIMATION_PLUGIN_URL . 'assets/css/animation-addon-card.css', array(), EXPLOREXR_ANIMATION_VERSION);
        
        // Premium plugin already registers model-viewer at priority 5
        // No need to enqueue it here
    }
}

/**
 * Register animation settings
 */
function explorexr_premium_animation_addon_register_settings() {
    // Registration handled in settings.php
    if (function_exists('explorexr_premium_animation_register_settings')) {
        explorexr_premium_animation_register_settings();
    }
}

/**
 * Register addon availability for the main plugin
 */
function explorexr_premium_animation_addon_register_availability($addons, $type) {
    if ($type === 'animation') {
        $addons['explorexr-animation-addon'] = true;
    }
    return $addons;
}

/**
 * Missing main plugin notice
 */
function explorexr_premium_animation_addon_missing_main_plugin_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php esc_html_e('ExploreXR Animation Add-On requires the ExploreXR Premium main plugin.', 'explorexr-animation-addon'); ?></strong>
            <?php esc_html_e('Please install and activate ExploreXR Premium first.', 'explorexr-animation-addon'); ?>
        </p>
    </div>
    <?php
}

/**
 * Display admin notices
 */
function explorexr_premium_animation_addon_admin_notices() {
    // Show welcome notice after activation
    if (get_transient('explorexr_premium_animation_addon_activated')) {
        delete_transient('explorexr_premium_animation_addon_activated');
        explorexr_premium_animation_addon_welcome_notice();
    }
}

/**
 * Welcome notice after activation
 * Uses shared helper function for standardized notices
 */
function explorexr_premium_animation_addon_welcome_notice() {
    if (function_exists('explorexr_addon_welcome_notice')) {
        explorexr_addon_welcome_notice(
            'Animation Add-On',
            'animation',
            'You can now add advanced animations to your 3D models.'
        );
    }
}

/**
 * Add menu item
 */
function explorexr_premium_animation_addon_add_menu() {
    $parent_slug = (defined('EXPLOREXR_IS_PREMIUM') && EXPLOREXR_IS_PREMIUM)
        ? 'edit.php?post_type=explorexr_premium_model'
        : 'edit.php?post_type=explorexr_model';
    add_submenu_page(
        $parent_slug,
        __('Animation Settings', 'explorexr-animation-addon'),
        __('Animation Settings', 'explorexr-animation-addon'),
        'manage_options',
        'explorexr-premium-animation-settings',
        'explorexr_premium_animation_settings_page'
    );
}

/**
 * Apply animation attributes to model viewer
 */
function explorexr_premium_animation_addon_apply_attributes($attributes, $model_id) {
    // Implementation in animation-handler.php
    if (function_exists('explorexr_premium_animation_apply_advanced_attributes')) {
        return explorexr_premium_animation_apply_advanced_attributes($attributes, $model_id);
    }
    return $attributes;
}

/**
 * Plugin activation hook
 */
function explorexr_premium_animation_addon_activate() {
    // Check for main plugin
    if (!explorexr_premium_animation_addon_check_main_plugin()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__('ExploreXR Animation Add-On requires ExploreXR Premium to be installed and activated.', 'explorexr-animation-addon'), 'Plugin Activation Error', array('back_link' => true));
        return;
    }
    
    // Use centralized addon manager for option activation if available
    if (class_exists('ExploreXR_Addon_Manager')) {
        ExploreXR_Addon_Manager::get_instance()->ensure_addon_options_active('animation');
    } else {
        // Fallback: Set default options manually
        add_option('explorexr_premium_animation_ping_pong_enabled', 'on');
        add_option('explorexr_premium_animation_crossfade_duration', '300');
        add_option('explorexr_premium_animation_show_controls', 'on');
    }
    
    // Set transient for welcome notice
    set_transient('explorexr_premium_animation_addon_activated', true, 60);
    
    // Schedule migration if needed
    if (!wp_next_scheduled('explorexr_premium_animation_run_migration')) {
        wp_schedule_single_event(time() + 10, 'explorexr_premium_animation_run_migration');
    }
}
register_activation_hook(__FILE__, 'explorexr_premium_animation_addon_activate');

/**
 * Plugin deactivation hook
 */
function explorexr_premium_animation_addon_deactivate() {
    // Clean up transients
    delete_transient('explorexr_premium_animation_addon_activated');
    
    // Clear scheduled events
    wp_clear_scheduled_hook('explorexr_premium_animation_run_migration');
    
    // Clean up all animation addon settings from models
    if (function_exists('explorexr_cleanup_addon_meta')) {
        $meta_count = explorexr_cleanup_addon_meta('_explorexr_animation_');
        $option_count = explorexr_cleanup_addon_options('explorexr_animation_');
        explorexr_log_addon_cleanup('Animation', $meta_count, $option_count);
    }
}
register_deactivation_hook(__FILE__, 'explorexr_premium_animation_addon_deactivate');

/**
 * Render per-model settings in Edit 3D Model page
 * 
 * This function includes the Animation settings card template which provides
 * a consistent UI experience across all ExploreXR addons.
 * 
 * @param int $model_id The model ID
 */
function explorexr_animation_addon_settings($model_id) {
    // Get the post object
    $post = get_post($model_id);
    
    if (!$post) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Invalid model ID', 'explorexr-animation-addon') . '</p></div>';
        return;
    }
    
    // Get model file for template
    $model_file = get_post_meta($model_id, '_explorexr_model_file', true);
    
    // Include the animation settings card template
    $template_file = EXPLOREXR_ANIMATION_PLUGIN_DIR . 'templates/admin/animation-settings-card.php';
    if (file_exists($template_file)) {
        include $template_file;
    } else {
        echo '<div class="notice notice-error"><p>' . esc_html__('Animation settings template not found.', 'explorexr-animation-addon') . '</p></div>';
    }
    return;
}
