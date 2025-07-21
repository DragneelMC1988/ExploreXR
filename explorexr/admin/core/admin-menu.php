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

// Include the model debug tool
require_once EXPLOREXR_PLUGIN_DIR . 'admin/models/model-debug.php';

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
    if (function_exists('explorexr_edit_model_page')) {
        add_submenu_page(null, 'Edit 3D Model', 'Edit 3D Model', 'manage_options', 'explorexr-edit-model', 'explorexr_edit_model_page');
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
    wp_enqueue_style('explorexr-banner-dismiss', EXPLOREXR_PLUGIN_URL . 'admin/css/banner-dismiss.css', array(), EXPLOREXR_VERSION);
    
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
    if (strpos($hook, 'explorexr') !== false) {
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
            
            // Localize script for AJAX functionality
            wp_localize_script('explorexr-settings-page-js', 'explorexr_settings', array(
                'nonce' => wp_create_nonce('explorexr_debug_nonce'),
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }
        
        // Dashboard page specific
        if (strpos($hook ?? '', 'toplevel_page_ExploreXR') !== false || $hook === 'toplevel_page_ExploreXR') {
            wp_enqueue_script('explorexr-dashboard-js', EXPLOREXR_PLUGIN_URL . 'admin/js/dashboard.js', array('jquery'), EXPLOREXR_VERSION, true);
            
            // Localize script with data for banner dismissal
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

/**
 * Premium upgrade notice has been moved to upgrade-system.php
 * Now shows globally across WordPress dashboard as a proper dismissible notice
 */

// Register plugin settings
add_action('admin_init', function() {
    // Register a settings section
    add_settings_section(
        'EXPLOREXR_general_settings',
        'General Settings',
        'EXPLOREXR_general_settings_callback',
        'explorexr-settings'
    );
    
    // Register CDN settings field
    add_settings_field(
        'EXPLOREXR_cdn_source',
        'Model Viewer Source',
        'EXPLOREXR_cdn_source_callback',
        'explorexr-settings',
        'EXPLOREXR_general_settings',
        array('label_for' => 'EXPLOREXR_cdn_source')
    );
    
    // Register Model Viewer version field
    add_settings_field(
        'EXPLOREXR_model_viewer_version',
        'Model Viewer Version',
        'EXPLOREXR_model_viewer_version_callback',
        'explorexr-settings',
        'EXPLOREXR_general_settings',
        array('label_for' => 'EXPLOREXR_model_viewer_version')
    );
    
    // Register Max Upload Size field
    add_settings_field(
        'EXPLOREXR_max_upload_size',
        'Max Upload Size (MB)',
        'EXPLOREXR_max_upload_size_callback',
        'explorexr-settings',
        'EXPLOREXR_general_settings',
        array('label_for' => 'EXPLOREXR_max_upload_size')
    );
    
    // Register debug mode field
    add_settings_field(
        'EXPLOREXR_debug_mode',
        'Debug Mode',
        'EXPLOREXR_debug_mode_callback',
        'explorexr-settings',
        'EXPLOREXR_general_settings',
        array('label_for' => 'EXPLOREXR_debug_mode')
    );
    
    // Register Debugging section
    add_settings_section(
        'EXPLOREXR_debugging_section',
        'Debugging Options',
        'EXPLOREXR_debugging_section_callback',
        'explorexr-settings'
    );
    
    // Register Debug Log field
    add_settings_field(
        'EXPLOREXR_debug_log',
        'Debug Log',
        'EXPLOREXR_debug_log_callback',
        'explorexr-settings',
        'EXPLOREXR_debugging_section',
        array('label_for' => 'EXPLOREXR_debug_log')
    );
    
    // Register View PHP Errors field
    add_settings_field(
        'EXPLOREXR_view_php_errors',
        'PHP Errors',
        'EXPLOREXR_view_php_errors_callback',
        'explorexr-settings',
        'EXPLOREXR_debugging_section',
        array('label_for' => 'EXPLOREXR_view_php_errors')
    );
    
    // Register Console Logging field
    add_settings_field(
        'EXPLOREXR_console_logging',
        'Console Logging',
        'EXPLOREXR_console_logging_callback',
        'explorexr-settings',
        'EXPLOREXR_debugging_section',
        array('label_for' => 'EXPLOREXR_console_logging')
    );
    
    // Register Debug Loading Info field
    add_settings_field(
        'EXPLOREXR_debug_loading_info',
        'Loading Information Debugging',
        'EXPLOREXR_debug_loading_info_callback',
        'explorexr-settings',
        'EXPLOREXR_debugging_section',
        array('label_for' => 'EXPLOREXR_debug_loading_info')
    );
    
    // Register settings with sanitization
    register_setting('EXPLOREXR_settings', 'EXPLOREXR_model_viewer_version', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('EXPLOREXR_settings', 'EXPLOREXR_max_upload_size', array(
        'sanitize_callback' => 'absint',
        'default' => 50
    ));
    register_setting('EXPLOREXR_settings', 'EXPLOREXR_debug_mode', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    
    // Register debugging settings with sanitization
    register_setting('EXPLOREXR_settings', 'EXPLOREXR_debug_log', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('EXPLOREXR_settings', 'EXPLOREXR_view_php_errors', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('EXPLOREXR_settings', 'EXPLOREXR_console_logging', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('EXPLOREXR_settings', 'EXPLOREXR_debug_loading_info', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
});

// AJAX Handlers for Premium Info
add_action('wp_ajax_EXPLOREXR_get_premium_info', 'EXPLOREXR_ajax_get_premium_info');

// AJAX Handlers for Debug Tools
add_action('wp_ajax_EXPLOREXR_clear_all_debug_logs', 'EXPLOREXR_ajax_clear_all_debug_logs');
add_action('wp_ajax_EXPLOREXR_run_diagnostics', 'EXPLOREXR_ajax_run_diagnostics');
add_action('wp_ajax_EXPLOREXR_export_debug_info', 'EXPLOREXR_ajax_export_debug_info');

// AJAX Handler for Premium Banner Dismissal
add_action('wp_ajax_EXPLOREXR_dismiss_premium_banner', 'EXPLOREXR_ajax_dismiss_premium_banner');

/**
 * AJAX handler for dismissing premium banner
 */
function EXPLOREXR_ajax_dismiss_premium_banner() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'EXPLOREXR_dashboard_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    // Set transient to hide banner for this session (until browser is closed)
    // Using a 12 hour expiration as a reasonable session length
    set_transient('EXPLOREXR_pro_banner_dismissed_' . get_current_user_id(), true, 12 * HOUR_IN_SECONDS);
    
    wp_send_json_success('Premium banner dismissed for this session');
}

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

/**
 * AJAX handler for getting premium status (Free version)
 */
/**
 * AJAX handler for getting premium information (replaces addon status)
 */
function EXPLOREXR_ajax_get_premium_features() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'explorexr_admin_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
    }
    
    // Free version response - premium features available for upgrade
    $premium_features = array(
        'camera-controls' => 'Camera Controls',
        'ar' => 'AR Support'
    );
    
    $feature_status = array();
    foreach ($premium_features as $slug => $name) {
        $feature_status[] = array(
            'slug' => $slug,
            'status' => 'premium',
            'status_text' => 'Premium Only',
            'name' => $name,
            'upgrade_url' => admin_url('admin.php?page=explorexr-premium')
        );
    }
    
    wp_send_json_success(array(
        'features' => $feature_status,
        'license_info' => array(
            'status' => 'free',
            'type' => 'Free Version',
            'upgrade_url' => EXPLOREXR_get_premium_upgrade_url()
        )
    ));
}

/**
 * AJAX handler for clearing all debug logs
 */
function EXPLOREXR_ajax_clear_all_debug_logs() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'EXPLOREXR_clear_logs')) {
        wp_send_json_error('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    try {
        // Clear main debug logs
        delete_option('explorexr_debug_log_data');
        delete_transient('explorexr_debug_log_cache');
        
        // Clear debug logs
        $debug_options = array(
            'explorexr_debug_loading_log'
            // Animation and annotation debug features are not available in the Free version
        );
        
        foreach ($debug_options as $option) {
            delete_option($option);
        }
        
        // Clear any error logs
        if (function_exists('error_clear_last')) {
            error_clear_last();
        }
        
        wp_send_json_success('All debug logs cleared successfully');
    } catch (Exception $e) {
        wp_send_json_error('Error clearing logs: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for running system diagnostics
 */
function EXPLOREXR_ajax_run_diagnostics() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'EXPLOREXR_diagnostics')) {
        wp_send_json_error('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    try {
        $diagnostics = EXPLOREXR_generate_system_diagnostics();
        wp_send_json_success($diagnostics);
    } catch (Exception $e) {
        wp_send_json_error('Error running diagnostics: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for exporting debug information
 */
function EXPLOREXR_ajax_export_debug_info() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'EXPLOREXR_export_debug')) {
        wp_send_json_error('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    try {
        $debug_info = EXPLOREXR_generate_debug_export();
        wp_send_json_success($debug_info);
    } catch (Exception $e) {
        wp_send_json_error('Error exporting debug info: ' . $e->getMessage());
    }
}

/**
 * Generate comprehensive system diagnostics
 */
function EXPLOREXR_generate_system_diagnostics() {
    $html = '<html><head><title>ExploreXR System Diagnostics</title>';
    $html .= '<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #0073aa; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .section { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: #46b450; font-weight: bold; }
        .warning { color: #ffb900; font-weight: bold; }
        .error { color: #dc3232; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f1f1f1; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; }
    </style></head><body>';
    
    $html .= '<div class="header"><h1>ExploreXR System Diagnostics</h1><p>Generated on: ' . gmdate('Y-m-d H:i:s') . '</p></div>';
    
    // WordPress Environment
    $html .= '<div class="section">';
    $html .= '<h2>WordPress Environment</h2>';
    $html .= '<table>';
    $html .= '<tr><th>WordPress Version</th><td>' . get_bloginfo('version') . '</td></tr>';
    $html .= '<tr><th>PHP Version</th><td>' . PHP_VERSION . '</td></tr>';
    $html .= '<tr><th>MySQL Version</th><td>' . $GLOBALS['wpdb']->db_version() . '</td></tr>';
    $html .= '<tr><th>Theme</th><td>' . wp_get_theme()->get('Name') . ' v' . wp_get_theme()->get('Version') . '</td></tr>';
    $html .= '<tr><th>Debug Mode</th><td>' . (WP_DEBUG ? '<span class="warning">Enabled</span>' : '<span class="success">Disabled</span>') . '</td></tr>';
    $html .= '</table>';
    $html .= '</div>';
    
    // ExploreXR Configuration
    $html .= '<div class="section">';
    $html .= '<h2>ExploreXR Configuration</h2>';
    $html .= '<table>';
    $html .= '<tr><th>Version</th><td>' . EXPLOREXR_VERSION . '</td></tr>';
    $html .= '<tr><th>Debug Mode</th><td>' . (get_option('EXPLOREXR_debug_mode') ? '<span class="warning">Enabled</span>' : '<span class="success">Disabled</span>') . '</td></tr>';
    $html .= '<tr><th>Model Viewer Version</th><td>' . get_option('EXPLOREXR_model_viewer_version', '3.3.0') . '</td></tr>';
    $html .= '<tr><th>CDN Source</th><td>' . get_option('EXPLOREXR_cdn_source', 'cdn') . '</td></tr>';
    $html .= '</table>';
    $html .= '</div>';
    
    // Debug Settings
    $html .= '<div class="section">';
    $html .= '<h2>Debug Settings</h2>';
    $debug_settings = array(
        'EXPLOREXR_debug_log' => 'Debug Logging',
        'EXPLOREXR_view_php_errors' => 'PHP Error Display',
        'EXPLOREXR_console_logging' => 'Console Logging',
        'EXPLOREXR_debug_loading_info' => 'Loading Info Debug'
    );
    
    $html .= '<table>';
    foreach ($debug_settings as $option => $label) {
        $value = get_option($option, false);
        $html .= '<tr>';
        $html .= '<td>' . $label . '</td>';
        $html .= '<td>' . ($value ? '<span class="warning">Enabled</span>' : '<span class="success">Disabled</span>') . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
    $html .= '</div>';
    
    // System Resources
    $html .= '<div class="section">';
    $html .= '<h2>System Resources</h2>';
    $html .= '<table>';
    $html .= '<tr><th>Memory Limit</th><td>' . ini_get('memory_limit') . '</td></tr>';
    $html .= '<tr><th>Max Execution Time</th><td>' . ini_get('max_execution_time') . ' seconds</td></tr>';
    $html .= '<tr><th>Upload Max Size</th><td>' . ini_get('upload_max_filesize') . '</td></tr>';
    $html .= '<tr><th>Post Max Size</th><td>' . ini_get('post_max_size') . '</td></tr>';
    $html .= '<tr><th>Max Input Vars</th><td>' . ini_get('max_input_vars') . '</td></tr>';
    $html .= '</table>';
    $html .= '</div>';
    
    $html .= '</body></html>';
    
    return $html;
}

/**
 * Generate debug information export
 */
function EXPLOREXR_generate_debug_export() {
    $export = "ExploreXR Debug Information Export\n";
    $export .= "=====================================\n";
    $export .= "Generated: " . gmdate('Y-m-d H:i:s') . "\n\n";
    
    // System Information
    $export .= "SYSTEM INFORMATION\n";
    $export .= "------------------\n";
    $export .= "WordPress Version: " . get_bloginfo('version') . "\n";
    $export .= "PHP Version: " . PHP_VERSION . "\n";
    $export .= "MySQL Version: " . $GLOBALS['wpdb']->db_version() . "\n";
    $export .= "ExploreXR Version: " . EXPLOREXR_VERSION . "\n";
    $export .= "Active Theme: " . wp_get_theme()->get('Name') . " v" . wp_get_theme()->get('Version') . "\n";
    $export .= "Debug Mode: " . (WP_DEBUG ? 'Enabled' : 'Disabled') . "\n\n";
    
    // ExploreXR Settings
    $export .= "ExploreXR SETTINGS\n";
    $export .= "---------------\n";
    $export .= "Debug Mode: " . (get_option('explorexr_debug_mode') ? 'Enabled' : 'Disabled') . "\n";
    $export .= "Model Viewer Version: " . get_option('explorexr_model_viewer_version', '3.3.0') . "\n";
    $export .= "CDN Source: " . get_option('explorexr_cdn_source', 'cdn') . "\n";
    
    $debug_settings = array(
        'explorexr_debug_log' => 'Debug Logging',
        'explorexr_view_php_errors' => 'PHP Error Display',
        'explorexr_console_logging' => 'Console Logging',
        // Animation and annotation debug features are not available in the Free version
        'explorexr_debug_loading_info' => 'Loading Info Debug'
    );
    
    foreach ($debug_settings as $option => $label) {
        $value = get_option($option, false);
        $export .= $label . ": " . ($value ? 'Enabled' : 'Disabled') . "\n";
    }
    $export .= "\n";
    
    // System Resources
    $export .= "SYSTEM RESOURCES\n";
    $export .= "----------------\n";
    $export .= "Memory Limit: " . ini_get('memory_limit') . "\n";
    $export .= "Max Execution Time: " . ini_get('max_execution_time') . " seconds\n";
    $export .= "Upload Max Size: " . ini_get('upload_max_filesize') . "\n";
    $export .= "Post Max Size: " . ini_get('post_max_size') . "\n";
    $export .= "Max Input Vars: " . ini_get('max_input_vars') . "\n\n";
    
    // Recent Errors (if debug logging is enabled)
    if (get_option('EXPLOREXR_debug_log', false)) {
        $export .= "RECENT DEBUG LOGS\n";
        $export .= "-----------------\n";
        $debug_log = get_option('EXPLOREXR_debug_log_data', '');
        if (!empty($debug_log)) {
            $export .= $debug_log . "\n";
        } else {
            $export .= "No debug log data available.\n";
        }
    }
    
    return $export;
}






