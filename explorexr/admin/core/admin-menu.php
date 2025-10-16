<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the admin page callbacks
require_once EXPLOREXR_PLUGIN_DIR . 'admin/core/admin-pages.php';

// Include the loading options page
require_once EXPLOREXR_PLUGIN_DIR . 'admin/pages/loading-options-page.php';

// Include the custom admin UI
require_once EXPLOREXR_PLUGIN_DIR . 'admin/core/admin-ui.php';

// Include custom functions
require_once EXPLOREXR_PLUGIN_DIR . 'admin/core/functions.php';

// Include the modern model browser
require_once EXPLOREXR_PLUGIN_DIR . 'admin/models/modern-model-browser.php';

// Include the edit link redirector
require_once EXPLOREXR_PLUGIN_DIR . 'admin/core/edit-redirector.php';

// Include premium upgrade page
require_once EXPLOREXR_PLUGIN_DIR . 'admin/pages/premium-upgrade-page.php';

/**
 * Register admin menu pages for ExploreXR Free
 */
function explorexr_register_admin_menu() {
    // Main menu page
    add_menu_page(
        'ExploreXR', 
        'ExploreXR', 
        'manage_options', 
        'explorexr', 
        'explorexr_dashboard_page', 
        'dashicons-admin-customizer', 
        75
    );
    
    // Submenu pages - Free version has limited functionality
    add_submenu_page('explorexr', 'Dashboard', 'Dashboard', 'manage_options', 'explorexr', 'explorexr_dashboard_page');
    add_submenu_page('explorexr', 'Create 3D Model', 'Create New Model', 'manage_options', 'explorexr-create-model', 'explorexr_create_model_page');
    add_submenu_page('explorexr', 'Browse Models', 'Browse Models', 'manage_options', 'explorexr-browse-models', 'explorexr_browse_models_page');
    add_submenu_page('explorexr', '3D Model Files', '3D Files', 'manage_options', 'explorexr-files', 'explorexr_files_page');
    add_submenu_page('explorexr', 'Loading Options', 'Loading Options', 'manage_options', 'explorexr-loading-options', 'explorexr_loading_options_page');
    add_submenu_page('explorexr', 'Settings', 'Settings', 'manage_options', 'explorexr-settings', 'explorexr_settings_page');
    
    // Premium upgrade page - promoting premium features
    add_submenu_page('explorexr', 'Go Premium', 'Go Premium', 'manage_options', 'explorexr-premium', 'explorexr_premium_upgrade_page');
    
    // Hidden submenu for editing models (not shown in menu but accessible via URL)
    // Use empty string instead of null to avoid PHP 8.1+ deprecation warnings
    if (function_exists('explorexr_edit_model_page')) {
        add_submenu_page('', 'Edit 3D Model', 'Edit 3D Model', 'manage_options', 'explorexr-edit-model', 'explorexr_edit_model_page');
    }
}
add_action('admin_menu', 'explorexr_register_admin_menu');

/**
 * Fix admin menu highlighting for edit model page
 */
function explorexr_fix_admin_menu_highlighting($parent_file) {
    global $submenu_file;
    
    // Check if we're on the edit model page
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for display purposes only
    if (isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'explorexr-edit-model') {
        $parent_file = 'explorexr'; // Set ExploreXR as the parent menu
        $submenu_file = 'explorexr-browse-models'; // Highlight Browse Models submenu
    }
    
    return $parent_file;
}
add_filter('parent_file', 'explorexr_fix_admin_menu_highlighting');

/**
 * Enqueue admin scripts and styles
 */
function explorexr_admin_enqueue_scripts($hook) {
    // Get current screen to determine which page we're on
    $screen = get_current_screen();
    
    // Add viewport meta tag for admin pages that use model-viewer
    if ($screen && strpos($screen->id, 'explorexr') !== false) {
        add_action('admin_head', 'explorexr_add_admin_viewport_meta');
    }
    
    // Common CSS for all admin pages
    wp_enqueue_style('explorexr-admin-styles', EXPLOREXR_PLUGIN_URL . 'admin/css/admin-styles.css', array(), EXPLOREXR_VERSION);
    wp_enqueue_style('explorexr-button-system', EXPLOREXR_PLUGIN_URL . 'admin/css/button-system.css', array(), EXPLOREXR_VERSION);
    
    // Premium upgrade styles
    wp_enqueue_style('explorexr-premium-upgrade', EXPLOREXR_PLUGIN_URL . 'admin/css/premium-upgrade.css', array(), EXPLOREXR_VERSION);
    
    // Premium upgrade scripts (needed for notice dismissal functionality)
    wp_enqueue_script('explorexr-premium-upgrade-js', EXPLOREXR_PLUGIN_URL . 'admin/js/premium-upgrade.js', array('jquery'), EXPLOREXR_VERSION, true);
    
    // Localize script for premium upgrade functionality
    wp_localize_script('explorexr-premium-upgrade-js', 'explorexr_premium', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'dismiss_nonce' => wp_create_nonce('EXPLOREXR_dismiss_notice')
    ));
    
    // Page-specific styles and scripts
    if (strpos($hook ?? '', 'explorexr') !== false) {
        // Specific CSS files
        if (strpos($hook ?? '', 'explorexr-files') !== false) {
            wp_enqueue_style('explorexr-files-page-css', EXPLOREXR_PLUGIN_URL . 'admin/css/files-page.css', array(), EXPLOREXR_VERSION);
            wp_enqueue_script('explorexr-files-page-js', EXPLOREXR_PLUGIN_URL . 'admin/js/files-page.js', array('jquery'), EXPLOREXR_VERSION, true);
        }
        
        if (strpos($hook ?? '', 'explorexr-loading-options') !== false) {
            wp_enqueue_style('explorexr-loading-options-css', EXPLOREXR_PLUGIN_URL . 'admin/css/loading-options.css', array(), EXPLOREXR_VERSION);
            wp_enqueue_script('explorexr-loading-options-js', EXPLOREXR_PLUGIN_URL . 'admin/js/loading-options.js', array('jquery'), EXPLOREXR_VERSION, true);
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
        }
        
        if (strpos($hook ?? '', 'explorexr-browse-models') !== false) {
            wp_enqueue_style('explorexr-browse-models-css', EXPLOREXR_PLUGIN_URL . 'admin/css/browse-models.css', array(), EXPLOREXR_VERSION);
            wp_enqueue_script('explorexr-browse-models-js', EXPLOREXR_PLUGIN_URL . 'admin/js/browse-models.js', array('jquery'), EXPLOREXR_VERSION, true);
            
            // Localize script with nonce and URLs
            wp_localize_script('explorexr-browse-models-js', 'explorexr_admin', array(
                'nonce' => wp_create_nonce('explorexr_admin_nonce'),
                'create_model_url' => admin_url('admin.php?page=explorexr-create-model'),
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }
        
        if (strpos($hook ?? '', 'explorexr-create-model') !== false) {
            wp_enqueue_style('explorexr-create-model-css', EXPLOREXR_PLUGIN_URL . 'admin/css/create-model.css', array(), EXPLOREXR_VERSION);
            wp_enqueue_script('explorexr-create-model-js', EXPLOREXR_PLUGIN_URL . 'admin/js/create-model.js', array('jquery'), EXPLOREXR_VERSION, true);
            
            // Localize script with nonce
            wp_localize_script('explorexr-create-model-js', 'explorexr_admin', array(
                'nonce' => wp_create_nonce('explorexr_admin_nonce'),
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }
        
        if (strpos($hook ?? '', 'explorexr-settings') !== false) {
            wp_enqueue_style('explorexr-settings-page-css', EXPLOREXR_PLUGIN_URL . 'admin/css/settings-page.css', array(), EXPLOREXR_VERSION);
            wp_enqueue_script('explorexr-settings-page-js', EXPLOREXR_PLUGIN_URL . 'admin/js/settings-page.js', array('jquery'), EXPLOREXR_VERSION, true);
        }
        
        // Dashboard page specific
        if (strpos($hook ?? '', 'toplevel_page_ExploreXR') !== false || $hook === 'toplevel_page_ExploreXR') {
            wp_enqueue_script('explorexr-dashboard-js', EXPLOREXR_PLUGIN_URL . 'admin/js/dashboard.js', array('jquery'), EXPLOREXR_VERSION, true);
            
            // Localize script with dashboard data
            wp_localize_script('explorexr-dashboard-js', 'EXPLOREXR_dashboard', array(
                'nonce' => wp_create_nonce('EXPLOREXR_dashboard_nonce'),
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }
        
        // Common scripts for all pages
        wp_enqueue_script('explorexr-admin-ui', EXPLOREXR_PLUGIN_URL . 'admin/js/admin-ui.js', array('jquery'), EXPLOREXR_VERSION, true);
        wp_localize_script('explorexr-admin-ui', 'ExploreXRAdminUI', array(
            'strings' => array(
                'modelPreviewTitle' => __('Model Preview', 'explorexr')
            ),
            'nonce' => wp_create_nonce('explorexr_admin_nonce'),
            'ajax_url' => admin_url('admin-ajax.php')
        ));
        
        // Localize script for admin vars (required by admin-ui.js)
        wp_localize_script('explorexr-admin-ui', 'ExploreXRAdminVars', array(
            'pluginUrl' => EXPLOREXR_PLUGIN_URL
        ));
        
        // Edit model page specific
        if (strpos($hook ?? '', 'explorexr-edit-model') !== false) {
            wp_enqueue_style('explorexr-edit-model-css', EXPLOREXR_PLUGIN_URL . 'admin/css/edit-model.css', array(), EXPLOREXR_VERSION);
            wp_enqueue_script('explorexr-edit-model-js', EXPLOREXR_PLUGIN_URL . 'admin/js/edit-model.js', array('jquery'), EXPLOREXR_VERSION, true);
            
            // Include WordPress media uploader
            wp_enqueue_media();
            
            // Color picker for settings
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            
            // Localize script with data
            wp_localize_script('explorexr-edit-model-js', 'explorexr_admin', array(
                'nonce' => wp_create_nonce('explorexr_admin_nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'plugin_url' => EXPLOREXR_PLUGIN_URL,
                'is_premium' => false,
                'premium_upgrade_url' => EXPLOREXR_get_premium_upgrade_url()
            ));
        }
    }
}
add_action('admin_enqueue_scripts', 'explorexr_admin_enqueue_scripts');

/**
 * Add viewport meta tag to admin pages that use model-viewer
 */
function explorexr_add_admin_viewport_meta() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
}

/**
 * Initialize viewport meta tag for ExploreXR admin pages
 */
function explorexr_init_admin_viewport_meta() {
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'explorexr') !== false) {
        add_action('admin_head', 'explorexr_add_admin_viewport_meta');
    }
}
add_action('current_screen', 'explorexr_init_admin_viewport_meta');

/**
 * Add custom body classes for admin pages
 */
function EXPLOREXR_admin_body_class($classes) {
    $screen = get_current_screen();
    
    if ($screen && strpos($screen->base, 'explorexr') !== false) {
        $classes .= ' explorexr-admin-page explorexr-version';
    }
    
    return $classes;
}
add_filter('admin_body_class', 'EXPLOREXR_admin_body_class');

// Register plugin settings
add_action('admin_init', function() {
    // Clean up removed debug options on plugin initialization (one time)
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
    
    // Register a settings section
    add_settings_section(
        'explorexr_general_settings',
        'General Settings',
        'explorexr_general_settings_callback',
        'explorexr-settings'
    );
    
    // Register Model Viewer version field
    add_settings_field(
        'explorexr_model_viewer_version',
        'Model Viewer Version',
        'explorexr_model_viewer_version_callback',
        'explorexr-settings',
        'explorexr_general_settings',
        array('label_for' => 'explorexr_model_viewer_version')
    );
    
    // Register Max Upload Size field
    add_settings_field(
        'explorexr_max_upload_size',
        'Max Upload Size (MB)',
        'explorexr_max_upload_size_callback',
        'explorexr-settings',
        'explorexr_general_settings',
        array('label_for' => 'explorexr_max_upload_size')
    );
    
    // Register settings with sanitization
    register_setting('explorexr_settings', 'explorexr_model_viewer_version', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('explorexr_settings', 'explorexr_max_upload_size', array(
        'sanitize_callback' => 'absint',
        'default' => 50
    ));
});

// AJAX Handlers for Premium Info
add_action('wp_ajax_EXPLOREXR_get_premium_info', 'EXPLOREXR_ajax_get_premium_info');

/**
 * AJAX handler for getting premium information
 */
function EXPLOREXR_ajax_get_premium_info() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'explorexr_admin_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
    }
    
    // Return premium features and pricing info
    wp_send_json_success(array(
        'premium_features' => array(
            'ar' => array(
                'name' => 'AR Support',
                'description' => 'Augmented Reality experiences for mobile devices',
                'icon' => '📱'
            ),
            'camera-controls' => array(
                'name' => 'Camera Controls',
                'description' => 'Camera interaction controls',
                'icon' => '📷'
            )
        ),
        'pricing_tiers' => array(
            'pro' => array(
                'name' => 'Pro',
                'price' => '59',
                'currency' => 'EUR',
                'period' => 'year',
                'features' => array('2 Premium Features', 'Email Support', 'Basic License Management')
            ),
            'plus' => array(
                'name' => 'Plus',
                'price' => '99',
                'currency' => 'EUR',
                'period' => 'year',
                'features' => array('2 Premium Features', 'Priority Support', 'License Management', 'Custom Branding'),
                'popular' => true
            ),
            'ultra' => array(
                'name' => 'Ultra',
                'price' => '199',
                'currency' => 'EUR',
                'period' => 'year',
                'features' => array('All Premium Features', 'VIP Support', 'White-label Options', 'Custom Development')
            )
        ),
        'upgrade_url' => EXPLOREXR_get_premium_upgrade_url()
    ));
}
