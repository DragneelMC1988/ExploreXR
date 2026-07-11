<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once EXPLOREXR_PLUGIN_DIR . 'admin/core/admin-pages.php';
require_once EXPLOREXR_PLUGIN_DIR . 'admin/pages/loading-options-page.php';
require_once EXPLOREXR_PLUGIN_DIR . 'admin/core/functions.php';
require_once EXPLOREXR_PLUGIN_DIR . 'admin/core/edit-redirector.php';

// Free-version pages
require_once EXPLOREXR_PLUGIN_DIR . 'admin/pages/addons-page.php';
require_once EXPLOREXR_PLUGIN_DIR . 'admin/pages/go-premium-page.php';

/**
 * Register the admin menu for ExploreXR (free).
 */
function explorexr_register_admin_menu() {
    add_menu_page(
        esc_html__('ExploreXR', 'explorexr'),
        esc_html__('ExploreXR', 'explorexr'),
        'manage_options',
        'explorexr',
        'explorexr_dashboard_page',
        'dashicons-admin-customizer',
        75
    );

    add_submenu_page('explorexr', esc_html__('Dashboard', 'explorexr'),       esc_html__('Dashboard', 'explorexr'),       'manage_options', 'explorexr',         'explorexr_dashboard_page');
    add_submenu_page('explorexr', esc_html__('Create 3D Model', 'explorexr'), esc_html__('Create New Model', 'explorexr'),'manage_options', 'explorexr-create-model',    'explorexr_create_model_page');
    add_submenu_page('explorexr', esc_html__('Browse Models', 'explorexr'),   esc_html__('Browse Models', 'explorexr'),   'manage_options', 'explorexr-browse-models',   'explorexr_browse_models_page');
    add_submenu_page('explorexr', esc_html__('3D Model Files', 'explorexr'),  esc_html__('3D Files', 'explorexr'),        'manage_options', 'explorexr-files',           'explorexr_files_page');
    add_submenu_page('explorexr', esc_html__('Loading Options', 'explorexr'), esc_html__('Loading Options', 'explorexr'), 'manage_options', 'explorexr-loading-options', 'explorexr_loading_options_page');
    add_submenu_page('explorexr', esc_html__('Settings', 'explorexr'),        esc_html__('Settings', 'explorexr'),        'manage_options', 'explorexr-settings',        'explorexr_settings_page');
    add_submenu_page('explorexr', esc_html__('Addons', 'explorexr'),          esc_html__('Addons', 'explorexr'),          'manage_options', 'explorexr-addons',          'explorexr_free_addons_page');
    add_submenu_page('explorexr', esc_html__('Go Premium', 'explorexr'),      esc_html__('Go Premium', 'explorexr'),      'manage_options', 'explorexr-go-premium',      'explorexr_free_go_premium_page');

    // Hidden submenu for editing models
    if (function_exists('explorexr_edit_model_page')) {
        add_submenu_page('', esc_html__('Edit 3D Model', 'explorexr'), esc_html__('Edit 3D Model', 'explorexr'), 'manage_options', 'explorexr-edit-model', 'explorexr_edit_model_page');
    }
}
add_action('admin_menu', 'explorexr_register_admin_menu');

function explorexr_set_edit_model_title() {
    if (isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'explorexr-edit-model') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        global $title, $admin_title;
        $title       = esc_html__('Edit 3D Model', 'explorexr');
        $admin_title = esc_html__('Edit 3D Model', 'explorexr'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    }
}
add_action('admin_init', 'explorexr_set_edit_model_title', 1);

function explorexr_fix_admin_title($admin_title, $title) {
    if (isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'explorexr-edit-model') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (empty($title)) {
            $title = esc_html__('Edit 3D Model', 'explorexr');
        }
        return $title . ' &lsaquo; ExploreXR';
    }
    return $admin_title; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
}
add_filter('admin_title', 'explorexr_fix_admin_title', 10, 2);

function explorexr_fix_admin_menu_highlighting($parent_file) {
    global $submenu_file;
    if (isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'explorexr-edit-model') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $parent_file  = 'explorexr';
        $submenu_file = 'explorexr-browse-models';
    }
    return $parent_file;
}
add_filter('parent_file', 'explorexr_fix_admin_menu_highlighting');

/**
 * Enqueue admin styles + scripts on ExploreXR pages.
 */
function explorexr_admin_enqueue_scripts($hook) {
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'explorexr') !== false) {
        add_action('admin_head', 'explorexr_add_admin_viewport_meta');
    }

    wp_enqueue_style('explorexr-admin-styles', EXPLOREXR_PLUGIN_URL . 'admin/css/admin-styles.css', array(), EXPLOREXR_VERSION);
    wp_enqueue_style('explorexr-button-system', EXPLOREXR_PLUGIN_URL . 'admin/css/button-system.css', array(), EXPLOREXR_VERSION);
    wp_enqueue_style('explorexr-premium-upgrade', EXPLOREXR_PLUGIN_URL . 'admin/css/premium-upgrade.css', array(), EXPLOREXR_VERSION);

    if (strpos($hook ?? '', 'explorexr') === false && strpos($hook ?? '', 'explorexr_page_explorexr') === false) {
        return;
    }

    if (strpos($hook ?? '', 'explorexr-files') !== false) {
        wp_enqueue_style('explorexr-files-page-css', EXPLOREXR_PLUGIN_URL . 'admin/css/files-page.css', array(), EXPLOREXR_VERSION);
        wp_enqueue_script('explorexr-files-page-js', EXPLOREXR_PLUGIN_URL . 'admin/js/files-page.js', array('jquery'), EXPLOREXR_VERSION, true);
    }

    if (strpos($hook ?? '', 'explorexr-loading-options') !== false) {
        wp_enqueue_style('explorexr-loading-options-css', EXPLOREXR_PLUGIN_URL . 'admin/css/loading-options.css', array(), EXPLOREXR_VERSION);
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('explorexr-loading-options-js', EXPLOREXR_PLUGIN_URL . 'admin/js/loading-options.js', array('jquery', 'wp-color-picker'), EXPLOREXR_VERSION, true);
    }

    if (strpos($hook ?? '', 'explorexr-browse-models') !== false) {
        wp_enqueue_style('explorexr-browse-models-css', EXPLOREXR_PLUGIN_URL . 'admin/css/browse-models.css', array(), EXPLOREXR_VERSION);
        wp_enqueue_script('explorexr-browse-models-js', EXPLOREXR_PLUGIN_URL . 'admin/js/browse-models.js', array('jquery'), EXPLOREXR_VERSION, true);
        wp_localize_script('explorexr-browse-models-js', 'explorexr_admin', array(
            'nonce'            => wp_create_nonce('explorexr_admin_nonce'),
            'create_model_url' => admin_url('admin.php?page=explorexr-create-model'),
            'ajax_url'         => admin_url('admin-ajax.php'),
        ));
    }

    if (strpos($hook ?? '', 'explorexr-create-model') !== false) {
        wp_enqueue_style('explorexr-create-model-css', EXPLOREXR_PLUGIN_URL . 'admin/css/create-model.css', array(), EXPLOREXR_VERSION);
        wp_enqueue_script('explorexr-create-model-js', EXPLOREXR_PLUGIN_URL . 'admin/js/create-model.js', array('jquery'), EXPLOREXR_VERSION, true);
        wp_enqueue_script('explorexr-model-size', EXPLOREXR_PLUGIN_URL . 'assets/js/model-size.js', array('jquery'), EXPLOREXR_VERSION, true);
        wp_localize_script('explorexr-create-model-js', 'explorexr_admin', array(
            'nonce'    => wp_create_nonce('explorexr_admin_nonce'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }

    if (strpos($hook ?? '', 'explorexr-settings') !== false) {
        wp_enqueue_style('explorexr-settings-page-css', EXPLOREXR_PLUGIN_URL . 'admin/css/settings-page.css', array(), EXPLOREXR_VERSION);
        wp_enqueue_script('explorexr-settings-page-js', EXPLOREXR_PLUGIN_URL . 'admin/js/settings-page.js', array('jquery'), EXPLOREXR_VERSION, true);
    }

    if (strpos($hook ?? '', 'explorexr-addons') !== false) {
        wp_enqueue_style('explorexr-addon-cards-shared-css', EXPLOREXR_PLUGIN_URL . 'admin/css/addon-cards-shared.css', array(), EXPLOREXR_VERSION);
        wp_enqueue_style('explorexr-addon-cards-css',        EXPLOREXR_PLUGIN_URL . 'admin/css/addon-cards.css',        array('explorexr-addon-cards-shared-css'), EXPLOREXR_VERSION);
        wp_enqueue_style('explorexr-addons-page-css',        EXPLOREXR_PLUGIN_URL . 'admin/css/addons-page.css',        array('explorexr-addon-cards-css'), EXPLOREXR_VERSION);
        wp_enqueue_script('explorexr-addons-page-js',        EXPLOREXR_PLUGIN_URL . 'admin/js/addons-page.js',          array('jquery'), EXPLOREXR_VERSION, true);
        wp_localize_script('explorexr-addons-page-js', 'explorexrPremiumAdminAjax', array(
            'ajaxUrl'      => admin_url('admin-ajax.php'),
            'nonce'        => wp_create_nonce('explorexr_admin_nonce'),
            'installNonce' => wp_create_nonce('explorexr_install_addon_nonce'),
            'strings'      => array(
                'installing' => esc_html__('Installing…', 'explorexr'),
                'error'      => esc_html__('Install failed', 'explorexr'),
            ),
        ));
    }

    if (strpos($hook ?? '', 'toplevel_page_explorexr') !== false || $hook === 'toplevel_page_explorexr') {
        wp_enqueue_script('explorexr-dashboard-js', EXPLOREXR_PLUGIN_URL . 'admin/js/dashboard.js', array('jquery'), EXPLOREXR_VERSION, true);
        wp_localize_script('explorexr-dashboard-js', 'EXPLOREXR_dashboard', array(
            'nonce'    => wp_create_nonce('EXPLOREXR_dashboard_nonce'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }

    // Common admin UI scripts
    wp_enqueue_script('explorexr-admin-ui', EXPLOREXR_PLUGIN_URL . 'admin/js/admin-ui.js', array('jquery'), EXPLOREXR_VERSION, true);
    wp_localize_script('explorexr-admin-ui', 'ExploreXRAdminUI', array(
        'strings'  => array('modelPreviewTitle' => esc_html__('Model Preview', 'explorexr')),
        'nonce'    => wp_create_nonce('explorexr_admin_nonce'),
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
    wp_localize_script('explorexr-admin-ui', 'ExploreXRAdminVars', array(
        'pluginUrl' => EXPLOREXR_PLUGIN_URL,
    ));
    wp_enqueue_script('explorexr-model-viewer-modal-js', EXPLOREXR_PLUGIN_URL . 'admin/js/model-viewer-modal.js', array('jquery'), EXPLOREXR_VERSION, true);

    if (strpos($hook ?? '', 'explorexr-go-premium') !== false) {
        wp_enqueue_style('explorexr-go-premium-css', EXPLOREXR_PLUGIN_URL . 'admin/css/go-premium-page.css', array(), EXPLOREXR_VERSION);
    }

    if (strpos($hook ?? '', 'explorexr-edit-model') !== false) {
        wp_enqueue_style('explorexr-edit-model-css', EXPLOREXR_PLUGIN_URL . 'admin/css/edit-model.css', array(), EXPLOREXR_VERSION);
        wp_enqueue_style('explorexr-addon-cards-css', EXPLOREXR_PLUGIN_URL . 'admin/css/addon-cards.css', array(), EXPLOREXR_VERSION);
        wp_enqueue_style('explorexr-addon-cards-shared-css', EXPLOREXR_PLUGIN_URL . 'admin/css/addon-cards-shared.css', array(), EXPLOREXR_VERSION);
        wp_enqueue_script('explorexr-edit-model-js', EXPLOREXR_PLUGIN_URL . 'admin/js/edit-model.js', array('jquery'), EXPLOREXR_VERSION, true);
        wp_enqueue_script('explorexr-model-preview-card-js', EXPLOREXR_PLUGIN_URL . 'admin/js/model-preview-card.js', array('jquery'), EXPLOREXR_VERSION, true);
        wp_enqueue_script('explorexr-viewer-controls-card-js', EXPLOREXR_PLUGIN_URL . 'admin/js/viewer-controls-card.js', array('jquery'), EXPLOREXR_VERSION, true);
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_localize_script('explorexr-edit-model-js', 'explorexr_admin', array(
            'nonce'               => wp_create_nonce('explorexr_admin_nonce'),
            'ajax_url'            => admin_url('admin-ajax.php'),
            'plugin_url'          => EXPLOREXR_PLUGIN_URL,
            'is_premium'          => false,
            'premium_upgrade_url' => admin_url('admin.php?page=explorexr-go-premium'),
        ));
    }
}
add_action('admin_enqueue_scripts', 'explorexr_admin_enqueue_scripts');

function explorexr_add_admin_viewport_meta() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
}

function explorexr_init_admin_viewport_meta() {
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'explorexr') !== false) {
        add_action('admin_head', 'explorexr_add_admin_viewport_meta');
    }
}
add_action('current_screen', 'explorexr_init_admin_viewport_meta');

function EXPLOREXR_admin_body_class($classes) {
    $screen = get_current_screen();
    if ($screen && strpos($screen->base, 'explorexr') !== false) {
        $classes .= ' explorexr-admin-page explorexr-free-version';
    }
    return $classes;
}
add_filter('admin_body_class', 'EXPLOREXR_admin_body_class');

// Register plugin settings
add_action('admin_init', function() {
    $cleanup_done = get_option('explorexr_debug_cleanup_done', false);
    if (!$cleanup_done) {
        delete_option('explorexr_debug_mode');
        delete_option('explorexr_view_php_errors');
        delete_option('explorexr_console_logging');
        delete_option('explorexr_debug_loading_info');
        delete_option('explorexr_debug_ar_features');
        delete_option('explorexr_debug_camera_controls');
        update_option('explorexr_debug_cleanup_done', true);
    }

    add_settings_section('explorexr_general_settings', esc_html__('General Settings', 'explorexr'), 'explorexr_general_settings_callback', 'explorexr-settings');

    add_settings_field('explorexr_model_viewer_version', esc_html__('Model Viewer Version', 'explorexr'), 'explorexr_model_viewer_version_callback', 'explorexr-settings', 'explorexr_general_settings', array('label_for' => 'explorexr_model_viewer_version'));
    add_settings_field('explorexr_max_upload_size',      esc_html__('Max Upload Size (MB)', 'explorexr'), 'explorexr_max_upload_size_callback',      'explorexr-settings', 'explorexr_general_settings', array('label_for' => 'explorexr_max_upload_size'));

    register_setting('explorexr_settings', 'explorexr_model_viewer_version', array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('explorexr_settings', 'explorexr_max_upload_size',      array('sanitize_callback' => 'absint', 'default' => 50));
    register_setting('explorexr_settings', 'explorexr_default_tone_mapping', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'aces'));
});

/**
 * Strip an unwanted "‹" character occasionally inserted by WP into ExploreXR
 * admin page titles.
 */
add_action('admin_enqueue_scripts', function($hook) {
    if (strpos($hook, 'explorexr') === false) {
        return;
    }
    $script = "document.addEventListener('DOMContentLoaded', function() {\n"
        . "    var t = document.querySelector('.wrap h1');\n"
        . "    if (t && t.textContent.indexOf('‹') !== -1) {\n"
        . "        t.textContent = t.textContent.replace(/‹\\s*/g, '');\n"
        . "    }\n"
        . "});";
    wp_add_inline_script('jquery', $script);
});
