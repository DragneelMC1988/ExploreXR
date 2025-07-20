<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the admin page callbacks
require_once EXPOXR_PLUGIN_DIR . 'admin/core/admin-pages.php';

// Include the loading options page
require_once EXPOXR_PLUGIN_DIR . 'admin/pages/loading-options-page.php';

// Include the custom admin UI
require_once EXPOXR_PLUGIN_DIR . 'admin/core/admin-ui.php';

// Include custom functions
require_once EXPOXR_PLUGIN_DIR . 'admin/core/functions.php';

// Include the modern model browser
require_once EXPOXR_PLUGIN_DIR . 'admin/models/modern-model-browser.php';

// Include the edit link redirector
require_once EXPOXR_PLUGIN_DIR . 'admin/core/edit-redirector.php';

// Include the model debug tool
require_once EXPOXR_PLUGIN_DIR . 'admin/models/model-debug.php';



// Include custom plugin action links
require_once EXPOXR_PLUGIN_DIR . 'admin/core/plugin-links.php';

// Include premium upgrade page
require_once EXPOXR_PLUGIN_DIR . 'admin/pages/premium-upgrade-page.php';

/**
 * Register admin menu pages for ExpoXR Free
 */
function expoxr_register_admin_menu() {
    // Main menu page
    add_menu_page(
        'ExploreXR', 
        'ExploreXR', 
        'manage_options', 
        'explorexr', 
        'expoxr_dashboard_page', 
        'dashicons-admin-customizer', 
        75
    );
    
    // Submenu pages - Free version has limited functionality
    add_submenu_page('explorexr', 'Dashboard', 'Dashboard', 'manage_options', 'explorexr', 'expoxr_dashboard_page');
    add_submenu_page('explorexr', 'Create 3D Model', 'Create New Model', 'manage_options', 'expoxr-create-model', 'expoxr_create_model_page');
    add_submenu_page('explorexr', 'Browse Models', 'Browse Models', 'manage_options', 'expoxr-browse-models', 'expoxr_browse_models_page');
    add_submenu_page('explorexr', '3D Model Files', '3D Files', 'manage_options', 'expoxr-files', 'expoxr_files_page');
    add_submenu_page('explorexr', 'Loading Options', 'Loading Options', 'manage_options', 'expoxr-loading-options', 'expoxr_loading_options_page');
    add_submenu_page('explorexr', 'Settings', 'Settings', 'manage_options', 'expoxr-settings', 'expoxr_settings_page');
    
    // Premium upgrade page - promoting premium features
    add_submenu_page('explorexr', 'Go Premium', 'Go Premium', 'manage_options', 'expoxr-premium', 'expoxr_premium_upgrade_page');
    
    // Hidden submenu for editing models (not shown in menu but accessible via URL)
    if (function_exists('expoxr_edit_model_page')) {
        add_submenu_page(null, 'Edit 3D Model', 'Edit 3D Model', 'manage_options', 'expoxr-edit-model', 'expoxr_edit_model_page');
    }
}
add_action('admin_menu', 'expoxr_register_admin_menu');

/**
 * Fix admin menu highlighting for edit model page
 */
function expoxr_fix_admin_menu_highlighting($parent_file) {
    global $submenu_file;
    
    // Check if we're on the edit model page
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for display purposes only
    if (isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'expoxr-edit-model') {
        $parent_file = 'explorexr'; // Set ExploreXR as the parent menu
        $submenu_file = 'expoxr-browse-models'; // Highlight Browse Models submenu
    }
    
    return $parent_file;
}
add_filter('parent_file', 'expoxr_fix_admin_menu_highlighting');

/**
 * Enqueue admin scripts and styles
 */
function expoxr_admin_enqueue_scripts($hook) {
    // Get current screen to determine which page we're on
    $screen = get_current_screen();
    
    // Common CSS for all admin pages
    wp_enqueue_style('expoxr-admin-styles', EXPOXR_PLUGIN_URL . 'admin/css/admin-styles.css', array(), EXPOXR_VERSION);
    wp_enqueue_style('expoxr-button-system', EXPOXR_PLUGIN_URL . 'admin/css/button-system.css', array(), EXPOXR_VERSION);
    wp_enqueue_style('expoxr-banner-dismiss', EXPOXR_PLUGIN_URL . 'admin/css/banner-dismiss.css', array(), EXPOXR_VERSION);
    
    // Premium upgrade styles
    wp_enqueue_style('expoxr-premium-upgrade', EXPOXR_PLUGIN_URL . 'admin/css/premium-upgrade.css', array(), EXPOXR_VERSION);
    
    // Premium upgrade scripts (needed for notice dismissal functionality)
    wp_enqueue_script('expoxr-premium-upgrade-js', EXPOXR_PLUGIN_URL . 'admin/js/premium-upgrade.js', array('jquery'), EXPOXR_VERSION, true);
    
    // Localize script for premium upgrade functionality
    wp_localize_script('expoxr-premium-upgrade-js', 'expoxr_premium', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'dismiss_nonce' => wp_create_nonce('expoxr_dismiss_notice')
    ));
    
    // Page-specific styles and scripts
    if (strpos($hook, 'explorexr') !== false) {
        // Specific CSS files
        if (strpos($hook ?? '', 'expoxr-files') !== false) {
            wp_enqueue_style('expoxr-files-page-css', EXPOXR_PLUGIN_URL . 'admin/css/files-page.css', array(), EXPOXR_VERSION);
            wp_enqueue_script('expoxr-files-page-js', EXPOXR_PLUGIN_URL . 'admin/js/files-page.js', array('jquery'), EXPOXR_VERSION, true);
        }
        
        if (strpos($hook ?? '', 'expoxr-loading-options') !== false) {
            wp_enqueue_style('expoxr-loading-options-css', EXPOXR_PLUGIN_URL . 'admin/css/loading-options.css', array(), EXPOXR_VERSION);
            wp_enqueue_script('expoxr-loading-options-js', EXPOXR_PLUGIN_URL . 'admin/js/loading-options.js', array('jquery'), EXPOXR_VERSION, true);
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
        }
        
        if (strpos($hook ?? '', 'expoxr-browse-models') !== false) {
            wp_enqueue_style('expoxr-browse-models-css', EXPOXR_PLUGIN_URL . 'admin/css/browse-models.css', array(), EXPOXR_VERSION);
            wp_enqueue_script('expoxr-browse-models-js', EXPOXR_PLUGIN_URL . 'admin/js/browse-models.js', array('jquery'), EXPOXR_VERSION, true);
            
            // Localize script with nonce and URLs
            wp_localize_script('expoxr-browse-models-js', 'expoxr_admin', array(
                'nonce' => wp_create_nonce('expoxr_admin_nonce'),
                'create_model_url' => admin_url('admin.php?page=expoxr-create-model'),
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }
        
        if (strpos($hook ?? '', 'expoxr-create-model') !== false) {
            wp_enqueue_style('expoxr-create-model-css', EXPOXR_PLUGIN_URL . 'admin/css/create-model.css', array(), EXPOXR_VERSION);
            wp_enqueue_script('expoxr-create-model-js', EXPOXR_PLUGIN_URL . 'admin/js/create-model.js', array('jquery'), EXPOXR_VERSION, true);
            
            // Localize script with nonce
            wp_localize_script('expoxr-create-model-js', 'expoxr_admin', array(
                'nonce' => wp_create_nonce('expoxr_admin_nonce'),
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }
        
        if (strpos($hook ?? '', 'expoxr-settings') !== false) {
            wp_enqueue_style('expoxr-settings-page-css', EXPOXR_PLUGIN_URL . 'admin/css/settings-page.css', array(), EXPOXR_VERSION);
            wp_enqueue_script('expoxr-settings-page-js', EXPOXR_PLUGIN_URL . 'admin/js/settings-page.js', array('jquery'), EXPOXR_VERSION, true);
        }
        
        // Dashboard page specific
        if (strpos($hook ?? '', 'toplevel_page_expoxr') !== false || $hook === 'toplevel_page_expoxr') {
            wp_enqueue_script('expoxr-dashboard-js', EXPOXR_PLUGIN_URL . 'admin/js/dashboard.js', array('jquery'), EXPOXR_VERSION, true);
            
            // Localize script with data for banner dismissal
            wp_localize_script('expoxr-dashboard-js', 'expoxr_dashboard', array(
                'nonce' => wp_create_nonce('expoxr_dashboard_nonce'),
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }
        
        // Common scripts for all pages
        wp_enqueue_script('expoxr-admin-ui', EXPOXR_PLUGIN_URL . 'admin/js/admin-ui.js', array('jquery'), EXPOXR_VERSION, true);
        wp_localize_script('expoxr-admin-ui', 'expoxrAdminUI', array(
            'strings' => array(
                'modelPreviewTitle' => __('Model Preview', 'explorexr')
            ),
            'nonce' => wp_create_nonce('expoxr_admin_nonce'),
            'ajax_url' => admin_url('admin-ajax.php')
        ));
        
        // Edit model page specific
        if (strpos($hook ?? '', 'expoxr-edit-model') !== false) {
            wp_enqueue_style('expoxr-edit-model-css', EXPOXR_PLUGIN_URL . 'admin/css/edit-model.css', array(), EXPOXR_VERSION);
            wp_enqueue_script('expoxr-edit-model-js', EXPOXR_PLUGIN_URL . 'admin/js/edit-model.js', array('jquery'), EXPOXR_VERSION, true);
            
            // Include WordPress media uploader
            wp_enqueue_media();
            
            // Color picker for settings
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            
            // Localize script with data
            wp_localize_script('expoxr-edit-model-js', 'expoxr_admin', array(
                'nonce' => wp_create_nonce('expoxr_admin_nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'plugin_url' => EXPOXR_PLUGIN_URL,
                'is_premium' => false,
                'premium_upgrade_url' => expoxr_get_premium_upgrade_url()
            ));
        }
    }
}
add_action('admin_enqueue_scripts', 'expoxr_admin_enqueue_scripts');

/**
 * Add custom body classes for admin pages
 */
function expoxr_admin_body_class($classes) {
    $screen = get_current_screen();
    
    if (strpos($screen->base, 'explorexr') !== false) {
        $classes .= ' expoxr-admin-page expoxr-version';
    }
    
    return $classes;
}
add_filter('admin_body_class', 'expoxr_admin_body_class');

/**
 * Premium upgrade notice has been moved to upgrade-system.php
 * Now shows globally across WordPress dashboard as a proper dismissible notice
 */

// Register plugin settings
add_action('admin_init', function() {
    // Register a settings section
    add_settings_section(
        'expoxr_general_settings',
        'General Settings',
        'expoxr_general_settings_callback',
        'expoxr-settings'
    );
    
    // Register CDN settings field
    add_settings_field(
        'expoxr_cdn_source',
        'Model Viewer Source',
        'expoxr_cdn_source_callback',
        'expoxr-settings',
        'expoxr_general_settings',
        array('label_for' => 'expoxr_cdn_source')
    );
    
    // Register Model Viewer version field
    add_settings_field(
        'expoxr_model_viewer_version',
        'Model Viewer Version',
        'expoxr_model_viewer_version_callback',
        'expoxr-settings',
        'expoxr_general_settings',
        array('label_for' => 'expoxr_model_viewer_version')
    );
    
    // Register Max Upload Size field
    add_settings_field(
        'expoxr_max_upload_size',
        'Max Upload Size (MB)',
        'expoxr_max_upload_size_callback',
        'expoxr-settings',
        'expoxr_general_settings',
        array('label_for' => 'expoxr_max_upload_size')
    );
    
    // Register debug mode field
    add_settings_field(
        'expoxr_debug_mode',
        'Debug Mode',
        'expoxr_debug_mode_callback',
        'expoxr-settings',
        'expoxr_general_settings',
        array('label_for' => 'expoxr_debug_mode')
    );
    
    // Register Debugging section
    add_settings_section(
        'expoxr_debugging_section',
        'Debugging Options',
        'expoxr_debugging_section_callback',
        'expoxr-settings'
    );
    
    // Register Debug Log field
    add_settings_field(
        'expoxr_debug_log',
        'Debug Log',
        'expoxr_debug_log_callback',
        'expoxr-settings',
        'expoxr_debugging_section',
        array('label_for' => 'expoxr_debug_log')
    );
    
    // Register View PHP Errors field
    add_settings_field(
        'expoxr_view_php_errors',
        'PHP Errors',
        'expoxr_view_php_errors_callback',
        'expoxr-settings',
        'expoxr_debugging_section',
        array('label_for' => 'expoxr_view_php_errors')
    );
    
    // Register Console Logging field
    add_settings_field(
        'expoxr_console_logging',
        'Console Logging',
        'expoxr_console_logging_callback',
        'expoxr-settings',
        'expoxr_debugging_section',
        array('label_for' => 'expoxr_console_logging')
    );
    
    // Register Debug Loading Info field
    add_settings_field(
        'expoxr_debug_loading_info',
        'Loading Information Debugging',
        'expoxr_debug_loading_info_callback',
        'expoxr-settings',
        'expoxr_debugging_section',
        array('label_for' => 'expoxr_debug_loading_info')
    );
    
    // Register settings with sanitization
    register_setting('expoxr_settings', 'expoxr_model_viewer_version', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('expoxr_settings', 'expoxr_max_upload_size', array(
        'sanitize_callback' => 'absint',
        'default' => 50
    ));
    register_setting('expoxr_settings', 'expoxr_debug_mode', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    
    // Register debugging settings with sanitization
    register_setting('expoxr_settings', 'expoxr_debug_log', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('expoxr_settings', 'expoxr_view_php_errors', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('expoxr_settings', 'expoxr_console_logging', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('expoxr_settings', 'expoxr_debug_loading_info', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
});

// AJAX Handlers for Premium Info
add_action('wp_ajax_expoxr_get_premium_info', 'expoxr_ajax_get_premium_info');

// AJAX Handlers for Debug Tools
add_action('wp_ajax_expoxr_clear_all_debug_logs', 'expoxr_ajax_clear_all_debug_logs');
add_action('wp_ajax_expoxr_run_diagnostics', 'expoxr_ajax_run_diagnostics');
add_action('wp_ajax_expoxr_export_debug_info', 'expoxr_ajax_export_debug_info');

// AJAX Handler for Premium Banner Dismissal
add_action('wp_ajax_expoxr_dismiss_premium_banner', 'expoxr_ajax_dismiss_premium_banner');

/**
 * AJAX handler for dismissing premium banner
 */
function expoxr_ajax_dismiss_premium_banner() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'expoxr_dashboard_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    // Set transient to hide banner for this session (until browser is closed)
    // Using a 12 hour expiration as a reasonable session length
    set_transient('expoxr_pro_banner_dismissed_' . get_current_user_id(), true, 12 * HOUR_IN_SECONDS);
    
    wp_send_json_success('Premium banner dismissed for this session');
}

/**
 * AJAX handler for getting premium information
 */
function expoxr_ajax_get_premium_info() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'expoxr_admin_nonce')) {
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
        'upgrade_url' => expoxr_get_premium_upgrade_url()
    ));
}

/**
 * AJAX handler for getting premium status (Free version)
 */
/**
 * AJAX handler for getting premium information (replaces addon status)
 */
function expoxr_ajax_get_premium_features() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'expoxr_admin_nonce')) {
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
            'upgrade_url' => admin_url('admin.php?page=expoxr-premium')
        );
    }
    
    wp_send_json_success(array(
        'features' => $feature_status,
        'license_info' => array(
            'status' => 'free',
            'type' => 'Free Version',
            'upgrade_url' => expoxr_get_premium_upgrade_url()
        )
    ));
}

/**
 * AJAX handler for clearing all debug logs
 */
function expoxr_ajax_clear_all_debug_logs() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'expoxr_clear_logs')) {
        wp_send_json_error('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    try {
        // Clear main debug logs
        delete_option('expoxr_debug_log_data');
        delete_transient('expoxr_debug_log_cache');
        
        // Clear debug logs
        $debug_options = array(
            'expoxr_debug_ar_log',
            'expoxr_debug_camera_log',
            'expoxr_debug_loading_log'
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
function expoxr_ajax_run_diagnostics() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'expoxr_diagnostics')) {
        wp_send_json_error('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    try {
        $diagnostics = expoxr_generate_system_diagnostics();
        wp_send_json_success($diagnostics);
    } catch (Exception $e) {
        wp_send_json_error('Error running diagnostics: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for exporting debug information
 */
function expoxr_ajax_export_debug_info() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'expoxr_export_debug')) {
        wp_send_json_error('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    try {
        $debug_info = expoxr_generate_debug_export();
        wp_send_json_success($debug_info);
    } catch (Exception $e) {
        wp_send_json_error('Error exporting debug info: ' . $e->getMessage());
    }
}

/**
 * Generate comprehensive system diagnostics
 */
function expoxr_generate_system_diagnostics() {
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
    $html .= '<tr><th>Version</th><td>' . EXPOXR_VERSION . '</td></tr>';
    $html .= '<tr><th>Debug Mode</th><td>' . (get_option('expoxr_debug_mode') ? '<span class="warning">Enabled</span>' : '<span class="success">Disabled</span>') . '</td></tr>';
    $html .= '<tr><th>Model Viewer Version</th><td>' . get_option('expoxr_model_viewer_version', '3.3.0') . '</td></tr>';
    $html .= '<tr><th>CDN Source</th><td>' . get_option('expoxr_cdn_source', 'cdn') . '</td></tr>';
    $html .= '</table>';
    $html .= '</div>';
    
    // Debug Settings
    $html .= '<div class="section">';
    $html .= '<h2>Debug Settings</h2>';
    $debug_settings = array(
        'expoxr_debug_log' => 'Debug Logging',
        'expoxr_view_php_errors' => 'PHP Error Display',
        'expoxr_console_logging' => 'Console Logging',
        'expoxr_debug_camera_controls' => 'Camera Controls Debug',
        'expoxr_debug_loading_info' => 'Loading Info Debug'
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
function expoxr_generate_debug_export() {
    $export = "ExploreXR Debug Information Export\n";
    $export .= "=====================================\n";
    $export .= "Generated: " . gmdate('Y-m-d H:i:s') . "\n\n";
    
    // System Information
    $export .= "SYSTEM INFORMATION\n";
    $export .= "------------------\n";
    $export .= "WordPress Version: " . get_bloginfo('version') . "\n";
    $export .= "PHP Version: " . PHP_VERSION . "\n";
    $export .= "MySQL Version: " . $GLOBALS['wpdb']->db_version() . "\n";
    $export .= "ExploreXR Version: " . EXPOXR_VERSION . "\n";
    $export .= "Active Theme: " . wp_get_theme()->get('Name') . " v" . wp_get_theme()->get('Version') . "\n";
    $export .= "Debug Mode: " . (WP_DEBUG ? 'Enabled' : 'Disabled') . "\n\n";
    
    // ExploreXR Settings
    $export .= "EXPOXR SETTINGS\n";
    $export .= "---------------\n";
    $export .= "Debug Mode: " . (get_option('expoxr_debug_mode') ? 'Enabled' : 'Disabled') . "\n";
    $export .= "Model Viewer Version: " . get_option('expoxr_model_viewer_version', '3.3.0') . "\n";
    $export .= "CDN Source: " . get_option('expoxr_cdn_source', 'cdn') . "\n";
    
    $debug_settings = array(
        'expoxr_debug_log' => 'Debug Logging',
        'expoxr_view_php_errors' => 'PHP Error Display',
        'expoxr_console_logging' => 'Console Logging',
        'expoxr_debug_ar_features' => 'AR Features Debug',
        'expoxr_debug_camera_controls' => 'Camera Controls Debug',
        // Animation and annotation debug features are not available in the Free version
        'expoxr_debug_loading_info' => 'Loading Info Debug'
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
    if (get_option('expoxr_debug_log', false)) {
        $export .= "RECENT DEBUG LOGS\n";
        $export .= "-----------------\n";
        $debug_log = get_option('expoxr_debug_log_data', '');
        if (!empty($debug_log)) {
            $export .= $debug_log . "\n";
        } else {
            $export .= "No debug log data available.\n";
        }
    }
    
    return $export;
}






