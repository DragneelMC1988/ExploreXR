<?php
/**
 * ExploreXR Security Handler
 *
 * Centralized security functions for the ExploreXR plugin to ensure proper
 * authentication, authorization, and input validation across all components.
 *
 * @package ExploreXR
 * @since 0.2.4.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validate AJAX request security
 * 
 * @param string $nonce_action The nonce action to verify against
 * @param string $capability Required user capability (default: 'edit_posts')
 * @param bool $require_nonce Whether nonce is required (default: true)
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function ExploreXR_validate_ajax_security($nonce_action, $capability = 'edit_posts', $require_nonce = true) {
    // Check if user is logged in for AJAX requests that require authentication
    if (!is_user_logged_in() && $capability !== 'public') {
        return new WP_Error(
            'unauthorized', 
            __('You must be logged in to perform this action.', 'explorexr'),
            array('status' => 401)
        );
    }
      // Verify nonce if required
    if ($require_nonce) {
        // Check for nonce in both 'nonce' and 'security' parameters (for compatibility)
        $nonce = '';
        if (isset($_POST['nonce'])) {
            $nonce = sanitize_text_field(wp_unslash($_POST['nonce']));
        } elseif (isset($_POST['security'])) {
            $nonce = sanitize_text_field(wp_unslash($_POST['security']));
        }
        
        if (empty($nonce)) {
            return new WP_Error(
                'missing_nonce',
                __('Security token is missing.', 'explorexr'),
                array('status' => 403)
            );
        }
        
        if (!wp_verify_nonce($nonce, $nonce_action)) {
            return new WP_Error(
                'invalid_nonce',
                __('Security verification failed.', 'explorexr'),
                array('status' => 403)
            );
        }
    }
    
    // Check user capabilities
    if ($capability !== 'public' && !current_user_can($capability)) {
        return new WP_Error(
            'insufficient_permissions',
            __('You do not have permission to perform this action.', 'explorexr'),
            array('status' => 403)
        );
    }
    
    return true;
}

/**
 * Validate and sanitize model-related input data
 * 
 * @param array $data Raw input data
 * @param array $allowed_fields Optional array of allowed field names
 * @return array Sanitized data
 */
function ExploreXR_validate_model_input($data, $allowed_fields = array()) {
    $sanitized = array();
    
    // Define comprehensive sanitization rules
    $sanitization_rules = array(
        // Text fields
        'text_fields' => array(
            'title', 'description', 'model_alt', 
            'ar_button_text', 'loading_text', 'error_message'
        ),
        
        // URL fields  
        'url_fields' => array(
            'model_file', 'poster_image', 'usdz_file', 'alt_model_file'
        ),
        
        // Numeric fields
        'numeric_fields' => array(
            'rotation_speed', 'field_of_view',
            'min_field_of_view', 'max_field_of_view'
        ),
        
        // Boolean/checkbox fields
        'boolean_fields' => array(
            'camera_controls', 'auto_rotate', 'ar_enabled',
            'show_progress', 'show_loading_dots', 'enable_draco', 'debug_mode', 'disable_pan',
            'disable_tap', 'disable_zoom'
        ),
        
        // Color fields
        'color_fields' => array(
            'background_color', 'loading_color'
        ),
        
        // Select fields with allowed values
        'select_fields' => array(
            'viewer_size' => array('small', 'medium', 'large', 'custom'),
            'ar_scale' => array('auto', 'fixed'),
            'ar_placement' => array('floor', 'wall')
        ),
        
        // Dimension fields (CSS units allowed)
        'dimension_fields' => array(
            'custom_width', 'custom_height', 'camera_orbit', 'camera_target'
        ),
        
        // Array fields (multi-select)
        'array_fields' => array(
            'ar_modes'
        )
    );
    
    foreach ($data as $key => $value) {
        // Skip tracking and state fields
        if (is_string($key) && (strpos($key, '_state') !== false || strpos($key, '_tracking') !== false)) {
            $sanitized[$key] = sanitize_text_field($value);
            continue;
        }
        
        // Check if field is in allowed list (if provided)
        if (!empty($allowed_fields) && !in_array($key, $allowed_fields)) {
            continue; // Skip fields not in allowed list
        }
        
        // Apply appropriate sanitization based on field type
        if (in_array($key, $sanitization_rules['text_fields'])) {
            $sanitized[$key] = sanitize_text_field($value);
            
        } elseif (in_array($key, $sanitization_rules['url_fields'])) {
            $sanitized[$key] = esc_url_raw($value);
            
        } elseif (in_array($key, $sanitization_rules['numeric_fields'])) {
            $sanitized[$key] = is_numeric($value) ? floatval($value) : 0;
            
        } elseif (in_array($key, $sanitization_rules['boolean_fields'])) {
            $sanitized[$key] = ($value === 'on' || $value === '1' || $value === 'true') ? 'on' : 'off';
            
        } elseif (in_array($key, $sanitization_rules['color_fields'])) {
            $sanitized[$key] = ExploreXR_sanitize_hex_color($value) ?: '#000000';
            
        } elseif (isset($sanitization_rules['select_fields'][$key])) {
            $allowed_values = $sanitization_rules['select_fields'][$key];
            $sanitized[$key] = in_array($value, $allowed_values) ? $value : $allowed_values[0];
            
        } elseif (in_array($key, $sanitization_rules['dimension_fields'])) {
            // Allow CSS units and coordinate values
            $sanitized[$key] = preg_replace('/[^0-9a-z%\.\-\s]/i', '', $value);
            
        } elseif (in_array($key, $sanitization_rules['array_fields'])) {
            if (is_array($value)) {
                $sanitized[$key] = array_map('sanitize_text_field', $value);
            } else {
                // Handle JSON strings
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $sanitized[$key] = array_map('sanitize_text_field', $decoded);
                } else {
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
            
        } else {
            // Default sanitization for unknown fields
            $sanitized[$key] = sanitize_text_field($value);
        }
    }
    
    return $sanitized;
}

/**
 * Validate file upload security
 * 
 * @param array $file $_FILES array element
 * @param array $allowed_types Allowed MIME types
 * @param int $max_size Maximum file size in bytes
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function ExploreXR_validate_file_upload($file, $allowed_types = array(), $max_size = 52428800) { // 50MB default
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return new WP_Error(
            'invalid_upload',
            __('Invalid file upload.', 'explorexr')
        );
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return new WP_Error(
            'file_too_large',
            /* translators: %s: Maximum allowed file size */
            sprintf(__('File size exceeds the maximum allowed size of %s.', 'explorexr'), size_format($max_size))
        );
    }
    
    // Check MIME type if specified
    if (!empty($allowed_types)) {
        $file_mime = mime_content_type($file['tmp_name']);
        if (!in_array($file_mime, $allowed_types)) {
            return new WP_Error(
                'invalid_file_type',
                /* translators: %s: File MIME type */
                sprintf(__('File type %s is not allowed.', 'explorexr'), $file_mime)
            );
        }
    }
    
    // Additional security checks for 3D model files
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (in_array($extension, array('glb', 'gltf', 'usdz'))) {
        // Basic file header validation
        $file_header = file_get_contents($file['tmp_name'], false, null, 0, 12);
        
        if ($extension === 'glb' && is_string($file_header) && substr($file_header, 0, 4) !== 'glTF') {
            return new WP_Error(
                'invalid_glb_file',
                __('Invalid GLB file format.', 'explorexr')
            );
        }
    }
    
    return true;
}

/**
 * Rate limiting for AJAX requests
 * 
 * @param string $action The action being performed
 * @param int $max_requests Maximum requests per time window
 * @param int $time_window Time window in seconds (default: 60)
 * @return bool Whether the request is allowed
 */
function ExploreXR_check_rate_limit($action, $max_requests = 10, $time_window = 60) {
    $user_id = get_current_user_id();
    $ip_address = ExploreXR_get_client_ip();
    
    // Use user ID if logged in, otherwise use IP address
    $identifier = $user_id ? 'user_' . $user_id : 'ip_' . md5($ip_address);
    
    $transient_key = 'ExploreXR_rate_limit_' . $action . '_' . $identifier;
    $current_count = get_transient($transient_key);
    
    if ($current_count === false) {
        // First request in this time window
        set_transient($transient_key, 1, $time_window);
        return true;
    } elseif ($current_count < $max_requests) {
        // Increment counter
        set_transient($transient_key, $current_count + 1, $time_window);
        return true;
    } else {
        // Rate limit exceeded
        return false;
    }
}

/**
 * Get client IP address safely
 * 
 * @return string Client IP address
 */
function ExploreXR_get_client_ip() {
    $ip_keys = array(
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    );
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ip = sanitize_text_field(wp_unslash($_SERVER[$key]));
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '0.0.0.0';
}

/**
 * Log security events for monitoring
 * 
 * @param string $event_type Type of security event
 * @param string|array $message Event message or data
 * @param array $context Additional context data
 */
function ExploreXR_log_security_event($event_type, $message, $context = array()) {
    if (!explorexr_is_debug_enabled()) {
        return; // Only log when debug mode is enabled
    }
    
    // Handle case where message is an array (backward compatibility)
    if (is_array($message)) {
        $context = array_merge($message, $context);
        $message = 'Event: ' . $event_type; // Use event type as default message
    }
    
    // Ensure message is a string
    $message = is_string($message) ? $message : 'Security event logged'; 
    
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'event_type' => $event_type,
        'message' => $message,
        'user_id' => get_current_user_id(),
        'ip_address' => ExploreXR_get_client_ip(),
        'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
        'context' => $context
    );
    
    if (explorexr_is_debug_enabled()) {
        ExploreXR_log('ExploreXR Security Event: ' . $log_entry, 'warning');
    }
}

/**
 * Initialize security features
 */
function ExploreXR_init_security() {
    // Add security headers for admin pages
    if (is_admin()) {
        add_action('send_headers', 'ExploreXR_add_security_headers');
    }
    
    // Initialize security logging
    if (explorexr_is_debug_enabled()) {
        add_action('wp_login_failed', 'ExploreXR_log_failed_login');
        add_action('wp_login', 'ExploreXR_log_successful_login');
    }
}

/**
 * Add security headers to admin pages
 */
function ExploreXR_add_security_headers() {
    // Only proceed if headers haven't been sent yet
    if (headers_sent()) {
        return;
    }
    
    // Only add headers on ExploreXR admin pages
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check for admin page identification
    if (is_admin() && isset($_GET['page']) && 
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check for admin page identification
        is_string($_GET['page']) && strpos(sanitize_text_field(wp_unslash($_GET['page'])), 'explorexr') !== false) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
    }
}

/**
 * Log failed login attempts
 */
function ExploreXR_log_failed_login($username) {
    ExploreXR_log_security_event(
        'login_failed',
        'Failed login attempt for username: ' . $username,
        array('username' => $username)
    );
}

/**
 * Log successful logins
 */
function ExploreXR_log_successful_login($user_login, $user = null) {
    if ($user) {
        ExploreXR_log_security_event(
            'login_success',
            'Successful login for user: ' . $user_login,
            array('user_id' => $user->ID, 'username' => $user_login)
        );
    }
}

// Initialize security features
add_action('init', 'ExploreXR_init_security');






