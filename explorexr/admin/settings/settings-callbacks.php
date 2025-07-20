<?php
/**
 * ExploreXR Settings Callbacks
 * Callback functions for settings fields and sections
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * General settings section callback
 */
function expoxr_general_settings_callback() {
    echo '<p>Configure general settings for the ExploreXR plugin.</p>';
}

/**
 * Model Viewer version field callback
 */
function expoxr_model_viewer_version_callback() {
    $model_viewer_version = get_option('expoxr_model_viewer_version', '3.3.0');
    $versions = [
        '3.3.0' => 'v3.3.0 (Latest stable)',
        '3.2.0' => 'v3.2.0',
        '3.1.0' => 'v3.1.0',
        '3.0.0' => 'v3.0.0',
        '2.4.0' => 'v2.4.0 (Legacy)',
    ];
    ?>
    <select name="expoxr_model_viewer_version" id="expoxr_model_viewer_version">
        <?php foreach ($versions as $version => $label) : ?>
            <option value="<?php echo esc_attr($version); ?>" <?php selected($model_viewer_version, $version); ?>>
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p class="description">The version of Google's Model Viewer library to use. Newer versions may have more features but could be less stable.</p>
    <?php
}

/**
 * Max upload size field callback
 */
function expoxr_max_upload_size_callback() {
    $max_upload_size = get_option('expoxr_max_upload_size', 50);
    $server_max = expoxr_get_server_max_upload();
    ?>
    <input type="number" name="expoxr_max_upload_size" id="expoxr_max_upload_size" 
           value="<?php echo esc_attr($max_upload_size); ?>" class="small-text" min="1" max="<?php echo esc_attr($server_max); ?>"> MB
    <p class="description">Maximum file size for 3D model uploads. Server limit: <?php echo esc_html($server_max); ?> MB.</p>
    <?php
}

/**
 * Debug mode field callback
 */
function expoxr_debug_mode_callback() {
    $debug_mode = get_option('expoxr_debug_mode', false);
    ?>
    <label>
        <input type="checkbox" name="expoxr_debug_mode" value="1" <?php checked($debug_mode, true); ?>>
        Enable debug mode
    </label>
    <p class="description">When enabled, additional debugging information will be displayed for admins. Not recommended for production sites.</p>
    <?php
}

/**
 * Debugging section callback
 */
function expoxr_debugging_section_callback() {
    echo '<p>Configure debugging options to help troubleshoot issues with the ExploreXR plugin.</p>';
}

/**
 * Debug log callback
 */
function expoxr_debug_log_callback() {
    $debug_log = get_option('expoxr_debug_log', false);
    ?>
    <label>
        <input type="checkbox" name="expoxr_debug_log" value="1" <?php checked($debug_log, true); ?>>
        Enable debugging log
    </label>
    <p class="description">When enabled, ExploreXR will log plugin activity to a debug log file. This can help identify issues with model loading, AR functionality, etc.</p>
    <p class="description">Log location: <code><?php echo esc_html(EXPOXR_PLUGIN_DIR . 'debug.log'); ?></code></p>
    <?php
}

/**
 * View PHP errors callback
 */
function expoxr_view_php_errors_callback() {
    $view_php_errors = get_option('expoxr_view_php_errors', false);
    ?>
    <label>
        <input type="checkbox" name="expoxr_view_php_errors" value="1" <?php checked($view_php_errors, true); ?>>
        Display PHP errors for administrators
    </label>
    <p class="description">When enabled, PHP errors related to ExploreXR will be displayed to administrators. Only enable this for troubleshooting purposes.</p>
    <?php
}

/**
 * Console logging callback
 */
function expoxr_console_logging_callback() {
    $console_logging = get_option('expoxr_console_logging', false);
    ?>
    <label>
        <input type="checkbox" name="expoxr_console_logging" value="1" <?php checked($console_logging, true); ?>>
        Enable console logging
    </label>
    <p class="description">When enabled, ExploreXR will log information to the browser console, including plugin version, loading status, and errors.</p>
    <?php
}

/**
 * Debug loading information callback
 */
function expoxr_debug_loading_info_callback() {
    $debug_loading_info = get_option('expoxr_debug_loading_info', false);
    ?>
    <label>
        <input type="checkbox" name="expoxr_debug_loading_info" value="1" <?php checked($debug_loading_info, true); ?>>
        Debug loading information
    </label>
    <p class="description">When enabled, detailed loading information will be logged, including loading progress, load times, and resource loading status.</p>
    <?php
}

/**
 * Helper function to get the server's maximum upload size
 */
function expoxr_get_server_max_upload() {
    $max_upload = (int)(ini_get('upload_max_filesize'));
    $max_post = (int)(ini_get('post_max_size'));
    $memory_limit = (int)(ini_get('memory_limit'));
    
    return min($max_upload, $max_post, $memory_limit);
}

/**
 * Get comprehensive system information
 * Used in the System Information section of the settings page
 * 
 * @return array System information details
 */
function expoxr_get_system_info() {
    global $wpdb;
    
    // WordPress info
    $wp_version = get_bloginfo('version');
    
    // PHP info
    $php_version = phpversion();
    $memory_limit = ini_get('memory_limit');
    $max_execution_time = ini_get('max_execution_time');
    $post_max_size = ini_get('post_max_size');
    $upload_max_filesize = ini_get('upload_max_filesize');
    $max_upload_size = expoxr_get_server_max_upload();
    
    // MySQL info
    $mysql_version = $wpdb->db_version();
    
    // Server info
    $server_software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '';
    
    // Theme info
    $theme = wp_get_theme();
    $theme_name = $theme->get('Name');
    $theme_version = $theme->get('Version');
    
    // PHP extensions
    $gd_installed = extension_loaded('gd') && function_exists('gd_info');
    $imagick_installed = extension_loaded('imagick') && class_exists('Imagick');
    
    // Plugin-specific info
    $model_viewer_version = get_option('expoxr_model_viewer_version', '3.3.0');
    $model_viewer_source = get_option('expoxr_cdn_source', 'cdn') === 'cdn' ? 'CDN (unpkg.com)' : 'Local File';
    $debug_mode = get_option('expoxr_debug_mode', false);
    
    // Return all info as an array
    return array(
        'wp_version' => $wp_version,
        'php_version' => $php_version,
        'memory_limit' => $memory_limit,
        'max_execution_time' => $max_execution_time,
        'post_max_size' => $post_max_size,
        'upload_max_filesize' => $upload_max_filesize,
        'max_upload_size' => $max_upload_size,
        'mysql_version' => $mysql_version,
        'server_software' => $server_software,
        'theme_name' => $theme_name,
        'theme_version' => $theme_version,
        'gd_installed' => $gd_installed,
        'imagick_installed' => $imagick_installed,
        'model_viewer_version' => $model_viewer_version,
        'model_viewer_source' => $model_viewer_source,
        'debug_mode' => $debug_mode,
    );
}





