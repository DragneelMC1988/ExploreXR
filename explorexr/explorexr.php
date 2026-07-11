<?php
/**
 * Plugin Name: ExploreXR
 * Plugin URI: https://expoxr.com/explorexr/
 * Description: Free 3D model viewer for WordPress. Embed glTF/GLB/USDZ models with Google's <model-viewer>. Supports a single addon from a curated list. Upgrade to Premium for advanced features.
 * Version: 1.3.2
 * Author: Ayal Othman
 * Author URI: https://expoxr.com
 * Text Domain: explorexr
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants. Free plugin defines both the canonical EXPLOREXR_* names
// and the EXPLOREXR_PREMIUM_* aliases so addons and shared code that reference
// either set of constants keep working unchanged.
define('EXPLOREXR_VERSION', '1.3.2');
define('EXPLOREXR_PLUGIN_FILE', __FILE__);
define('EXPLOREXR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EXPLOREXR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EXPLOREXR_PLUGIN_BASENAME', plugin_basename(__FILE__));

if (!defined('EXPLOREXR_PREMIUM_VERSION'))         { define('EXPLOREXR_PREMIUM_VERSION', EXPLOREXR_VERSION); }
if (!defined('EXPLOREXR_PREMIUM_PLUGIN_FILE'))     { define('EXPLOREXR_PREMIUM_PLUGIN_FILE', EXPLOREXR_PLUGIN_FILE); }
if (!defined('EXPLOREXR_PREMIUM_PLUGIN_DIR'))      { define('EXPLOREXR_PREMIUM_PLUGIN_DIR', EXPLOREXR_PLUGIN_DIR); }
if (!defined('EXPLOREXR_PREMIUM_PLUGIN_URL'))      { define('EXPLOREXR_PREMIUM_PLUGIN_URL', EXPLOREXR_PLUGIN_URL); }
if (!defined('EXPLOREXR_PREMIUM_PLUGIN_BASENAME')) { define('EXPLOREXR_PREMIUM_PLUGIN_BASENAME', EXPLOREXR_PLUGIN_BASENAME); }

// Models directory + URL
if (!defined('EXPLOREXR_MODELS_DIR')) {
    $explorexr_upload_dir = wp_upload_dir();
    define('EXPLOREXR_MODELS_DIR', $explorexr_upload_dir['basedir'] . '/explorexr-models/');
}
if (!defined('EXPLOREXR_MODELS_URL')) {
    $explorexr_upload_dir = wp_upload_dir();
    define('EXPLOREXR_MODELS_URL', $explorexr_upload_dir['baseurl'] . '/explorexr-models/');
}

// Tier flags
if (!defined('EXPLOREXR_IS_PREMIUM')) { define('EXPLOREXR_IS_PREMIUM', false); }
if (!defined('EXPLOREXR_IS_FREE'))    { define('EXPLOREXR_IS_FREE', true); }

/**
 * Self-deactivate if the Premium plugin is loaded. Premium runs at priority 1
 * and deactivates the Free file path; running first guarantees no overlap.
 */
function explorexr_free_check_premium_active() {
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    if (is_plugin_active('explorexr-premium/ExploreXR-Premium.php')) {
        deactivate_plugins(plugin_basename(__FILE__), true);
        if (is_admin() && current_user_can('activate_plugins')) {
            add_action('admin_notices', static function() {
                echo '<div class="notice notice-warning is-dismissible"><p>';
                esc_html_e('ExploreXR has been deactivated because ExploreXR Premium is active.', 'explorexr');
                echo '</p></div>';
            });
        }
    }
}
add_action('plugins_loaded', 'explorexr_free_check_premium_active', 1);

/**
 * One-time migration: CDN option to local (WordPress.org compliance).
 * Gated by explorexr_cdn_migrated.
 */
function explorexr_free_migrate_cdn_option() {
    if (get_option('explorexr_cdn_migrated') === '1') {
        return;
    }
    if (get_option('explorexr_cdn_source') === 'cdn') {
        update_option('explorexr_cdn_source', 'local');
    }
    update_option('explorexr_cdn_migrated', '1', false);
}
add_action('admin_init', 'explorexr_free_migrate_cdn_option', 1);

/**
 * One-time migration: stop autoloading display/loading settings that are only
 * read when a model actually renders. Keeps the alloptions cache lean on
 * every other page load. Gated by explorexr_autoload_migrated.
 */
function explorexr_free_migrate_option_autoload() {
    if (get_option('explorexr_autoload_migrated') === '1') {
        return;
    }
    $no_autoload_options = array(
        'explorexr_loading_display',
        'explorexr_large_model_handling',
        'explorexr_large_model_size_threshold',
        'explorexr_lazy_load_poster',
        'explorexr_load_button_text',
        'explorexr_load_button_bg',
        'explorexr_load_button_color',
        'explorexr_load_button_hover_bg',
        'explorexr_load_button_hover_color',
        'explorexr_load_button_radius',
        'explorexr_model_viewer_version',
        'explorexr_default_tone_mapping',
        'explorexr_max_upload_size',
    );
    if (function_exists('wp_set_option_autoload_values')) {
        // WP 6.4+: flip autoload in one query without touching values.
        // call_user_func avoids static-analysis false-positives on minimum WP version.
        call_user_func('wp_set_option_autoload_values', array_fill_keys($no_autoload_options, false));
    } else {
        foreach ($no_autoload_options as $option_name) {
            $value = get_option($option_name, '__explorexr_unset__');
            if ($value === '__explorexr_unset__') {
                continue;
            }
            delete_option($option_name);
            add_option($option_name, $value, '', false);
        }
    }
    update_option('explorexr_autoload_migrated', '1', false);
}
add_action('admin_init', 'explorexr_free_migrate_option_autoload', 1);

/**
 * One-time migration: legacy lazy-load post meta keys → unified
 * _explorexr_premium_load_behavior. Gated by explorexr_load_behavior_migrated.
 */
function explorexr_free_migrate_load_behavior_meta() {
    if (get_option('explorexr_load_behavior_migrated') === '1') {
        return;
    }
    $query = new WP_Query(array(
        'post_type'      => array('explorexr_model'),
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- one-time migration gated by option flag
        'meta_query'     => array(
            'relation' => 'OR',
            array('key' => '_explorexr_premium_lazy_load_model', 'compare' => 'EXISTS'),
            array('key' => '_explorexr_lazy_load_model', 'compare' => 'EXISTS'),
        ),
    ));
    foreach ($query->posts as $migration_post_id) {
        $legacy = get_post_meta($migration_post_id, '_explorexr_premium_lazy_load_model', true);
        if ($legacy === '' || $legacy === false) {
            $legacy = get_post_meta($migration_post_id, '_explorexr_lazy_load_model', true);
        }
        if ($legacy === 'on') {
            $existing = get_post_meta($migration_post_id, '_explorexr_premium_load_behavior', true);
            if ($existing === '' || $existing === false) {
                update_post_meta($migration_post_id, '_explorexr_premium_load_behavior', 'lazy');
            }
        }
        delete_post_meta($migration_post_id, '_explorexr_premium_lazy_load_model');
        delete_post_meta($migration_post_id, '_explorexr_lazy_load_model');
    }
    update_option('explorexr_load_behavior_migrated', '1');
}
add_action('admin_init', 'explorexr_free_migrate_load_behavior_meta', 5);

/**
 * Register frontend assets early so page-builder previews can find them.
 */
function explorexr_free_register_frontend_assets() {
    if (!wp_script_is('explorexr-model-loader', 'registered')) {
        wp_register_script(
            'explorexr-model-loader',
            EXPLOREXR_PLUGIN_URL . 'assets/js/model-loader.js',
            array('jquery'),
            EXPLOREXR_VERSION,
            true
        );
    }
    if (!wp_style_is('explorexr-model-viewer', 'registered')) {
        wp_register_style(
            'explorexr-model-viewer',
            EXPLOREXR_PLUGIN_URL . 'assets/css/model-viewer.css',
            array(),
            EXPLOREXR_VERSION
        );
    }
}
add_action('wp_enqueue_scripts', 'explorexr_free_register_frontend_assets', 5);
add_action('elementor/frontend/after_register_scripts', 'explorexr_free_register_frontend_assets');
add_action('elementor/frontend/after_register_styles', 'explorexr_free_register_frontend_assets');
add_action('fl_builder_before_render_shortcodes', 'explorexr_free_register_frontend_assets');
add_action('et_builder_ready', 'explorexr_free_register_frontend_assets');

/**
 * Main plugin initialization.
 */
function explorexr_free_init() {
    // Helper functions MUST load first - addons depend on these stubs.
    require_once EXPLOREXR_PLUGIN_DIR . 'includes/core/helper-functions.php';

    // Core
    require_once EXPLOREXR_PLUGIN_DIR . 'includes/core/post-types.php';
    require_once EXPLOREXR_PLUGIN_DIR . 'includes/core/shortcodes.php';
    require_once EXPLOREXR_PLUGIN_DIR . 'includes/core/model-validator.php';

    // Models
    foreach (array('file-handler.php', 'model-cleanup.php', 'model-helper.php') as $explorexr_models_file) {
        if (file_exists(EXPLOREXR_PLUGIN_DIR . 'includes/models/' . $explorexr_models_file)) {
            require_once EXPLOREXR_PLUGIN_DIR . 'includes/models/' . $explorexr_models_file;
        }
    }

    // UI / utils
    foreach (array(
        'includes/ui/admin-bar.php',
        'includes/ui/deactivation-handler.php',
        'includes/utils/size-validator.php',
        'includes/utils/strip-tags-fix.php',
        'includes/shared/addon-notices-helper.php',
    ) as $explorexr_extra) {
        if (file_exists(EXPLOREXR_PLUGIN_DIR . $explorexr_extra)) {
            require_once EXPLOREXR_PLUGIN_DIR . $explorexr_extra;
        }
    }

    // Admin
    if (is_admin()) {
        require_once EXPLOREXR_PLUGIN_DIR . 'includes/admin/class-admin-notices.php';
        require_once EXPLOREXR_PLUGIN_DIR . 'admin/core/admin-menu.php';
        require_once EXPLOREXR_PLUGIN_DIR . 'admin/core/admin-pages.php';
        if (file_exists(EXPLOREXR_PLUGIN_DIR . 'admin/core/functions.php')) {
            require_once EXPLOREXR_PLUGIN_DIR . 'admin/core/functions.php';
        }
        foreach (array('settings-callbacks.php', 'import-export.php', 'loading-options.php') as $explorexr_settings_file) {
            if (file_exists(EXPLOREXR_PLUGIN_DIR . 'admin/settings/' . $explorexr_settings_file)) {
                require_once EXPLOREXR_PLUGIN_DIR . 'admin/settings/' . $explorexr_settings_file;
            }
        }
        require_once EXPLOREXR_PLUGIN_DIR . 'admin/ajax/ajax-handlers.php';
        if (file_exists(EXPLOREXR_PLUGIN_DIR . 'admin/ajax/render-model-ajax.php')) {
            require_once EXPLOREXR_PLUGIN_DIR . 'admin/ajax/render-model-ajax.php';
        }
    }

    // Addon manager (Free-tier: one addon, whitelist of 3: AR, Animation, Loading).
    require_once EXPLOREXR_PLUGIN_DIR . 'includes/addons/free-addon-manager.php';
    ExploreXR_Addon_Manager::get_instance();

    // Page builder integrations
    if (file_exists(EXPLOREXR_PLUGIN_DIR . 'includes/integrations/class-page-builder-loader.php')) {
        require_once EXPLOREXR_PLUGIN_DIR . 'includes/integrations/class-page-builder-loader.php';
        if (class_exists('ExploreXR_Page_Builder_Loader')) {
            ExploreXR_Page_Builder_Loader::init();
        }
    }

    // Emit <link rel="preload"> hints for decoder WASM binaries into <head>.
    // Registered here so wp_head fires them before the_content renders.
    add_action('wp_head', 'explorexr_free_preload_decoder_assets', 1);
}
add_action('plugins_loaded', 'explorexr_free_init', 5);

/**
 * Output <link rel="preload"> hints for Draco and Basis Universal WASM decoders.
 * Only emits on frontend pages that contain model-viewer shortcodes, and only
 * for files that are confirmed present on disk.
 */
function explorexr_free_preload_decoder_assets() {
    if (!function_exists('explorexr_premium_has_model_viewers') || !explorexr_premium_has_model_viewers()) {
        return;
    }

    static $draco_exists = null;
    static $basis_exists = null;
    if ($draco_exists === null) {
        $draco_exists = file_exists(EXPLOREXR_PLUGIN_DIR . 'assets/vendor/draco/draco_decoder.wasm');
        $basis_exists = file_exists(EXPLOREXR_PLUGIN_DIR . 'assets/vendor/basis-universal/basis_transcoder.wasm');
    }

    if ($draco_exists) {
        echo '<link rel="preload" href="' . esc_url(EXPLOREXR_PLUGIN_URL . 'assets/vendor/draco/draco_decoder.wasm') . '" as="fetch" crossorigin>' . "\n";
    }
    if ($basis_exists) {
        echo '<link rel="preload" href="' . esc_url(EXPLOREXR_PLUGIN_URL . 'assets/vendor/basis-universal/basis_transcoder.wasm') . '" as="fetch" crossorigin>' . "\n";
    }
}

/**
 * Enqueue frontend assets when a model viewer shortcode is present.
 */
function explorexr_free_enqueue_scripts() {
    if (!function_exists('explorexr_premium_has_model_viewers') || !explorexr_premium_has_model_viewers()) {
        return;
    }

    if (file_exists(EXPLOREXR_PLUGIN_DIR . 'assets/css/frontend.css')) {
        wp_enqueue_style('explorexr-frontend', EXPLOREXR_PLUGIN_URL . 'assets/css/frontend.css', array(), EXPLOREXR_VERSION);
    }
    if (file_exists(EXPLOREXR_PLUGIN_DIR . 'assets/css/lazy-load.css')) {
        wp_enqueue_style('explorexr-lazy-load', EXPLOREXR_PLUGIN_URL . 'assets/css/lazy-load.css', array(), EXPLOREXR_VERSION);
    }
    if (file_exists(EXPLOREXR_PLUGIN_DIR . 'assets/js/frontend.js')) {
        wp_enqueue_script('explorexr-frontend', EXPLOREXR_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), EXPLOREXR_VERSION, true);
    }
}
add_action('wp_enqueue_scripts', 'explorexr_free_enqueue_scripts', 10);

/**
 * Admin styles and scripts.
 */
function explorexr_free_admin_enqueue_scripts($hook) {
    $explorexr_pages = array(
        'toplevel_page_explorexr',
        'explorexr_page_explorexr-settings',
        'explorexr_page_explorexr-loading-options',
        'explorexr_page_explorexr-addons',
        'explorexr_page_explorexr-go-premium',
        'explorexr_page_explorexr-browse-models',
        'explorexr_page_explorexr-create-model',
        'explorexr_page_explorexr-edit-model',
        'explorexr_page_explorexr-files',
        'admin_page_explorexr-edit-model',
    );

    global $post_type;
    $page              = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $is_explorexr_page = in_array($hook, $explorexr_pages, true)
        || $post_type === 'explorexr_model'
        || $page === 'explorexr-edit-model';

    if (!$is_explorexr_page) {
        return;
    }

    wp_enqueue_style('explorexr-admin', EXPLOREXR_PLUGIN_URL . 'admin/css/admin-ui.css', array(), EXPLOREXR_VERSION);
    wp_enqueue_script('explorexr-admin', EXPLOREXR_PLUGIN_URL . 'admin/js/admin-ui.js', array('jquery'), EXPLOREXR_VERSION, true);
    wp_localize_script('explorexr-admin', 'explorexrAdmin', array(
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('explorexr_admin_nonce'),
        'isPremium' => false,
        'version'   => EXPLOREXR_VERSION,
        'pluginUrl' => EXPLOREXR_PLUGIN_URL,
    ));
}
add_action('admin_enqueue_scripts', 'explorexr_free_admin_enqueue_scripts');

/**
 * Plugin activation.
 */
function explorexr_free_activate() {
    if (function_exists('explorexr_register_post_types')) {
        explorexr_register_post_types();
    }
    flush_rewrite_rules();
    update_option('explorexr_version', EXPLOREXR_VERSION);
    // Model uploads need the dedicated dir to exist before the file handler
    // can write to it. wp_mkdir_p is a no-op if the path is already there.
    if (defined('EXPLOREXR_MODELS_DIR') && !is_dir(EXPLOREXR_MODELS_DIR)) {
        wp_mkdir_p(EXPLOREXR_MODELS_DIR);
    }
}
register_activation_hook(__FILE__, 'explorexr_free_activate');

/**
 * Add Settings + Go Premium links on the wp-admin Plugins screen row.
 */
function explorexr_free_plugin_action_links($links) {
    if (!is_array($links)) {
        $links = array();
    }
    $custom = array(
        'settings'   => sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=explorexr-settings')),
            esc_html__('Settings', 'explorexr')
        ),
        'go-premium' => sprintf(
            '<a href="%s" style="color:#d63638;font-weight:600;">%s</a>',
            esc_url(admin_url('admin.php?page=explorexr-go-premium')),
            esc_html__('Go Premium', 'explorexr')
        ),
    );
    return array_merge($custom, $links);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'explorexr_free_plugin_action_links');

/**
 * Plugin deactivation.
 */
function explorexr_free_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'explorexr_free_deactivate');
