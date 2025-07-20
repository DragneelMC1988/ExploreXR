<?php
/**
 * Debug functions for ExploreXR
 * 
  * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Log important POST data for debugging edit mode issues
 *
 * @param int $post_id The post ID
 * @return array Debug information collected
 */
function explorexr_debug_log_post_data($post_id) {
    // Only log essential info to avoid huge logs
    $important_keys = array(
        'explorexr_model_file',
        'explorexr_model_name',
        'viewer_size',
        'viewer_width',
        'viewer_height',
        'explorexr_camera_controls',
        'explorexr_camera_controls_state',
        'explorexr_auto_rotate',
        'explorexr_auto_rotate_state'
        // Animation features are not available in the Free version
    );
    
    // Create comprehensive debug data
    $log_data = array(
        'post_id' => $post_id,
        'timestamp' => current_time('mysql'),
        'user_id' => get_current_user_id(),
        'request_time' => isset($_SERVER['REQUEST_TIME']) ? gmdate('Y-m-d H:i:s', sanitize_text_field(wp_unslash($_SERVER['REQUEST_TIME']))) : 'unknown',
        'referring_page' => isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])) : 'unknown',
        'post_status' => get_post_status($post_id),
        'form_data' => array(),
        'nonce_status' => isset($_POST['explorexr_nonce']) ? wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['explorexr_nonce'])), 'explorexr_save_model') : 'not provided',
        'memory_usage' => memory_get_usage() / 1024 / 1024 . ' MB',
    );
    
    // Log both key data and missing fields
    $missing_fields = array();
    foreach ($important_keys as $key) {
        if (isset($_POST[$key])) {
            $log_data['form_data'][$key] = sanitize_text_field(wp_unslash($_POST[$key]));
        } else {
            $missing_fields[] = $key;
        }
    }
    
    // Add missing fields to the log
    if (!empty($missing_fields)) {
        $log_data['missing_fields'] = $missing_fields;
    }
    
    // Check for checkbox states - this is crucial for debugging checkbox issues
    $checkbox_fields = array(
        'explorexr_camera_controls',
        'explorexr_auto_rotate'
        // Animation features are not available in the Free version
    );
    
    $checkbox_states = array();
    foreach ($checkbox_fields as $field) {
        $state_field = $field . '_state';
        $checkbox_states[$field] = array(
            'checkbox_present' => isset($_POST[$field]),
            'state_field_present' => isset($_POST[$state_field]),
            'state_value' => isset($_POST[$state_field]) ? sanitize_text_field(wp_unslash($_POST[$state_field])) : 'not set'
        );
    }
    $log_data['checkbox_states'] = $checkbox_states;
    
    // Check if we're in an AJAX request
    $log_data['is_ajax'] = defined('DOING_AJAX') && DOING_AJAX;
    
    // Log any errors that might have occurred
    if (isset($GLOBALS['wp_errors']) && is_wp_error($GLOBALS['wp_errors'])) {
        $log_data['wp_errors'] = $GLOBALS['wp_errors']->get_error_messages();
    }
    
    // Convert the log data to JSON
    $json_log_data = json_encode($log_data, JSON_PRETTY_PRINT);
    
    // Log the data
    if (get_option('explorexr_debug_mode', false)) {
        explorexr_log('ExploreXR: Edit mode submission debug for post ' . $post_id . ': ' . $json_log_data);
    }
    
    // Save the debug info to post meta for troubleshooting
    update_post_meta($post_id, '_explorexr_last_edit_debug', $json_log_data);
    update_post_meta($post_id, '_explorexr_last_edit_time', current_time('mysql'));
    
    return $log_data;
}

/**
 * Get debug information from a post
 *
 * @param int $post_id The post ID
 * @return array|false Debug information or false if none found
 */
function explorexr_get_debug_info($post_id) {
    $debug_info = get_post_meta($post_id, '_explorexr_last_edit_debug', true);
    $debug_time = get_post_meta($post_id, '_explorexr_last_edit_time', true);
    
    if (empty($debug_info)) {
        return false;
    }
    
    $debug_data = json_decode($debug_info, true);
    $debug_data['_explorexr_last_edit_time'] = $debug_time;
    
    return $debug_data;
}

/**
 * Create a debug log file with detailed information
 *
 * @param array $data The data to log
 * @param string $label Optional label for the log entry
 * @return string|false Path to the log file or false on failure
 */
function explorexr_create_debug_log($data, $label = '') {
    $upload_dir = wp_upload_dir();
    $log_dir = $upload_dir['basedir'] . '/explorexr-logs';
    
    // Create logs directory if it doesn't exist
    if (!file_exists($log_dir)) {
        if (!wp_mkdir_p($log_dir)) {
            if (get_option('explorexr_debug_mode', false)) {
                explorexr_log('ExploreXR: Failed to create log directory at ' . $log_dir, 'error');
            }
            return false;
        }
        
        // Create .htaccess to protect logs
        $htaccess = "Order deny,allow\nDeny from all";
        @file_put_contents($log_dir . '/.htaccess', $htaccess);
    }
    
    // Create log file
    $timestamp = gmdate('Y-m-d-H-i-s');
    $label = !empty($label) ? '-' . sanitize_file_name($label) : '';
    $filename = 'explorexr-debug-' . $timestamp . $label . '.log';
    $log_file = $log_dir . '/' . $filename;
    
    // Add system info to the log
    $system_info = array(
        'WordPress Version' => get_bloginfo('version'),
        'PHP Version' => phpversion(),
        'Memory Limit' => ini_get('memory_limit'),
        'ExploreXR Version' => defined('EXPLOREXR_VERSION') ? EXPLOREXR_VERSION : 'unknown',
        'Server Software' => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'unknown',
        'Active Plugins' => array()
    );
    
    // Get active plugins
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins', array());
    
    foreach ($active_plugins as $plugin) {
        if (isset($all_plugins[$plugin])) {
            $system_info['Active Plugins'][$plugin] = $all_plugins[$plugin]['Version'];
        }
    }
    
    // Combine all info
    $log_data = array(
        'timestamp' => $timestamp,
        'system_info' => $system_info,
        'debug_data' => $data
    );
    
    // Write to file
    $result = @file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        if (get_option('explorexr_debug_mode', false)) {
            explorexr_log('ExploreXR: Failed to write to log file ' . $log_file, 'error');
        }
        return false;
    }
    
    return $log_file;
}





