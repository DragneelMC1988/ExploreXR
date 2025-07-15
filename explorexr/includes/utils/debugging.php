<?php
/**
 * ExploreXR Debugging Functionality
 * 
 * Handles debug logging and debugging features
 * 
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize debugging functionality
 */
function expoxr_init_debugging() {
    // Register AJAX handlers for debug log operations
    add_action('wp_ajax_expoxr_get_debug_log', 'expoxr_ajax_get_debug_log');
    add_action('wp_ajax_expoxr_clear_debug_log', 'expoxr_ajax_clear_debug_log');
}

/**
 * Custom logging function that respects WordPress debug settings
 * 
 * @param mixed $message The message to log (string, array, object, etc.)
 * @param string $level Optional log level (info, warning, error)
 */
function expoxr_log($message, $level = 'info') {
    // Only log if WordPress debugging is enabled
    if (!defined('WP_DEBUG') || !WP_DEBUG || !defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
        return;
    }
    
    // Convert arrays/objects to readable strings
    if (is_array($message) || is_object($message)) {
        $message = wp_json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($message === false) {
            // Fallback to print_r if JSON encoding fails
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Only used in debug mode when JSON encoding fails
            $message = print_r($message, true);
        }
    }
    
    // Format the message with timestamp and level
    $formatted_message = sprintf(
        '[%s] [%s] %s',
        gmdate('Y-m-d H:i:s'),
        strtoupper($level),
        $message
    );
    
    // Log to WordPress debug log
    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Used for debug logging functionality
    error_log($formatted_message);
}
    // Add console logging if enabled
    if (get_option('expoxr_console_logging', false)) {
        add_action('wp_footer', 'expoxr_add_console_logging');
        add_action('admin_footer', 'expoxr_add_console_logging');
    }
    
    // Display PHP errors for admins if enabled (only in development environments)
    if (get_option('expoxr_view_php_errors', false) && current_user_can('manage_options') && get_option('expoxr_debug_mode', false) && defined('WP_DEBUG') && WP_DEBUG) {
        // Only modify error reporting in development environments
        if (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
            // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Required for debugging functionality
            ini_set('display_errors', 1);
            // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Required for debugging functionality
            ini_set('display_startup_errors', 1);
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting -- Only used in debug mode
            error_reporting(E_ALL);
        }
    }
    
    // Set up error handler to catch and log string function errors (only in debug mode)
    if (get_option('expoxr_debug_mode', false) && defined('WP_DEBUG') && WP_DEBUG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Used for debug error handling
        set_error_handler('expoxr_error_handler', E_WARNING | E_NOTICE | E_DEPRECATED);
        
        // Also register a shutdown function to catch fatal errors
        register_shutdown_function('expoxr_shutdown_function');
    }
}
}

/**
 * Custom error handler to catch and log PHP errors
 * 
 * @param int $errno Error number
 * @param string $errstr Error message
 * @param string $errfile File where error occurred
 * @param int $errline Line number where error occurred
 * @return bool Whether to continue with PHP internal error handler
 */
function expoxr_error_handler($errno, $errstr, $errfile, $errline) {
    // Check if error is related to string functions with null arguments
    if (strpos($errstr, 'strpos()') !== false || 
        strpos($errstr, 'str_replace()') !== false || 
        strpos($errstr, 'preg_match') !== false ||
        strpos($errstr, 'substr') !== false) {
        
        $log_message = sprintf(
            "String function error detected: %s in %s on line %d",
            $errstr,
            $errfile,
            $errline
        );
        
        expoxr_debug_log($log_message, 'warning');
    }
    
    // Return false to allow PHP to handle the error as well
    return false;
}

/**
 * Shutdown function to catch fatal errors
 */
function expoxr_shutdown_function() {
    $error = error_get_last();
    
    if ($error !== null && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
        $log_message = sprintf(
            "Fatal error detected: %s in %s on line %d",
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        expoxr_debug_log($log_message, 'error');
    }
}
add_action('init', 'expoxr_init_debugging');

/**
 * Add console logging to 3D model viewers
 */
function expoxr_add_console_logging() {
    // Only add if we're on a page with a 3D model and the query has been run
    if (is_admin() || !is_main_query()) {
        return;
    }
    
    global $post;
    if (!$post || (!has_shortcode($post->post_content ?? '', 'expoxr_model') && !has_shortcode($post->post_content ?? '', 'model-viewer'))) {
        return;
    }
    
    // Get debugging options
    $plugin_version = EXPOXR_VERSION;
    $debug_ar = get_option('expoxr_debug_ar_features', false) ? 'true' : 'false';
    $debug_camera = get_option('expoxr_debug_camera_controls', false) ? 'true' : 'false';
    $debug_animations = get_option('expoxr_debug_animations', false) ? 'true' : 'false';
    $debug_annotations = get_option('expoxr_debug_annotations', false) ? 'true' : 'false';
    $debug_loading = get_option('expoxr_debug_loading_info', false) ? 'true' : 'false';
    
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('ExploreXR Debug Information:');
        console.log('Plugin Version: <?php echo esc_js($plugin_version); ?>');
        
        // Find all model viewers on the page
        const modelViewers = document.querySelectorAll('model-viewer');
        console.log(`Found ${modelViewers.length} model viewer(s) on this page`);
        
        modelViewers.forEach((modelViewer, index) => {
            console.log(`Model Viewer #${index + 1}:`);
            
            <?php if (get_option('expoxr_debug_loading_info', false)): ?>
            // Loading Information Debugging
            console.log(`  Loading Attributes: loading=${modelViewer.loading}, poster=${modelViewer.hasAttribute('poster')}`);
            
            modelViewer.addEventListener('progress', event => {
                console.log(`  Loading Progress: ${Math.floor(event.detail.totalProgress * 100)}%`);
            });
            
            modelViewer.addEventListener('load', () => console.log('  Model successfully loaded'));
            modelViewer.addEventListener('error', error => console.error('  Model loading error:', error));
            <?php endif; ?>
        });
    });
    </script>
    <?php
}

/**
 * Log a message to the debug log file
 * 
 * @param string $message The message to log
 * @param string $level The log level (info, warning, error)
 */
function expoxr_debug_log($message, $level = 'info') {
    // Only log if debug logging is enabled
    if (!get_option('expoxr_debug_log', false) || !get_option('expoxr_debug_mode', false)) {
        return;
    }
    
    $log_file = EXPOXR_PLUGIN_DIR . 'debug.log';
    $timestamp = current_time('mysql');
    $formatted_message = sprintf("[%s] [%s] %s\n", $timestamp, strtoupper($level), $message);
    
    // Append to log file
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Used for debug logging functionality
        error_log($formatted_message, 3, $log_file);
    }
}

/**
 * AJAX handler for getting debug log contents
 */
function expoxr_ajax_get_debug_log() {
    // Use centralized security validation
    $security_check = expoxr_validate_ajax_security('expoxr_debug_nonce', 'manage_options');
    if (is_wp_error($security_check)) {
        wp_send_json_error(array('message' => $security_check->get_error_message()));
        return;
    }
    
    // Rate limiting - allow 10 requests per minute
    if (!expoxr_check_rate_limit('debug_log_get', 10, 60)) {
        wp_send_json_error(array('message' => 'Rate limit exceeded. Please wait before requesting debug log again.'));
        return;
    }
    
    // Log security event
    expoxr_log_security_event('debug_log_accessed', array(
        'user_id' => get_current_user_id(),
        'ip' => expoxr_get_client_ip(),
        'timestamp' => current_time('mysql')
    ));
    
    $log_file = EXPOXR_PLUGIN_DIR . 'debug.log';
    
    if (file_exists($log_file)) {
        $content = file_get_contents($log_file);
        wp_send_json_success(array('content' => $content));
    } else {
        wp_send_json_success(array('content' => 'No debug log file exists yet.'));
    }
}

/**
 * AJAX handler for clearing debug log
 */
function expoxr_ajax_clear_debug_log() {
    // Validate security using centralized function
    $security_check = expoxr_validate_ajax_security('expoxr_debug_nonce', 'manage_options');
    if (is_wp_error($security_check)) {
        expoxr_log_security_event(
            'ajax_security_failure',
            'Debug log clear blocked: ' . $security_check->get_error_message(),
            // Note: nonce verification is handled by expoxr_validate_ajax_security() function
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in expoxr_validate_ajax_security()
            array('action' => 'clear_debug_log', 'nonce_provided' => isset($_POST['nonce']))
        );
        wp_send_json_error(array('message' => $security_check->get_error_message()));
    }
    
    // Check rate limiting (max 5 clear operations per minute)
    if (!expoxr_check_rate_limit('clear_debug_log', 5, 60)) {
        expoxr_log_security_event(
            'rate_limit_exceeded',
            'Debug log clear rate limit exceeded',
            array('action' => 'clear_debug_log', 'limit' => 5)
        );
        wp_send_json_error(array('message' => 'Too many clear requests. Please wait before trying again.'));
    }
    
    // Log security event for debug log clearing
    expoxr_log_security_event('debug_log_cleared', array(
        'user_id' => get_current_user_id(),
        'ip' => expoxr_get_client_ip(),
        'timestamp' => current_time('mysql')
    ));
    
    $log_file = EXPOXR_PLUGIN_DIR . 'debug.log';
    
    if (file_exists($log_file)) {
        // Clear the file using WordPress file functions
        if (WP_Filesystem()) {
            global $wp_filesystem;
            $wp_filesystem->put_contents($log_file, '');
            wp_send_json_success(array('message' => 'Debug log cleared successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Unable to access file system.'));
        }
    } else {
        wp_send_json_success(array('message' => 'No debug log file exists.'));
    }
}

/**
 * Add sample debug entries to log file for testing
 * Only used during development/testing
 */
function expoxr_add_sample_debug_entries() {
    expoxr_debug_log('Plugin initialized', 'info');
    expoxr_debug_log('Model viewer loaded: example-model.glb', 'info');
    expoxr_debug_log('AR session started on iOS device', 'info');
    expoxr_debug_log('Failed to load texture: texture-file.jpg', 'warning');
    expoxr_debug_log('Model load error: Invalid file format', 'error');
    expoxr_debug_log('Camera position changed: 0 1.5 2.5', 'info');
    expoxr_debug_log('Animation started: "Spin"', 'info');
    expoxr_debug_log('Hotspot clicked: Annotation #3', 'info');
    expoxr_debug_log('Loading progress: 75%', 'info');
    expoxr_debug_log('Model successfully loaded after 3.2s', 'info');
}

/**
 * Safe wrapper for WordPress conditional tags
 * Prevents "Function was called incorrectly" notices by checking if the main query is ready
 */
function expoxr_safe_conditional_tag_check($tag_function, $args = array()) {
    // Only run the function if we're past the parse_query hook or in the admin
    if (did_action('parse_query') || is_admin()) {
        if (is_callable($tag_function)) {
            return call_user_func_array($tag_function, $args);
        }
    }
    return false;
}

/**
 * Safe version of is_page()
 * 
 * @param mixed $page Page ID, title, slug, or array of such
 * @return bool Whether the query is for an existing page
 */
function expoxr_is_page($page = '') {
    return expoxr_safe_conditional_tag_check('is_page', array($page));
}

/**
 * Safe version of is_search()
 * 
 * @return bool Whether the query is a search
 */
function expoxr_is_search() {
    return expoxr_safe_conditional_tag_check('is_search');
}

/**
 * Safe version of is_home()
 * 
 * @return bool Whether the query is for the blog homepage
 */
function expoxr_is_home() {
    return expoxr_safe_conditional_tag_check('is_home');
}

/**
 * Safe version of is_single()
 * 
 * @param mixed $post Post ID, title, slug, or array of such
 * @return bool Whether the query is for an existing single post
 */
function expoxr_is_single($post = '') {
    return expoxr_safe_conditional_tag_check('is_single', array($post));
}





