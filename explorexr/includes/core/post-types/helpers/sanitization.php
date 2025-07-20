<?php
/**
 * Sanitization functions for ExpoXR
 *
 * @package ExpoXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitize hex color
 *
 * @param string $color Color string to sanitize
 * @return string Sanitized color or default color
 */
if (!function_exists('expoxr_sanitize_hex_color')) {
    function expoxr_sanitize_hex_color($color) {
        if ('' === $color) {
            return '';
        }

        // 3 or 6 hex digits, or the empty string.
        if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
            return $color;
        }

        return '';
    }
}

/**
 * Sanitization wrapper for model upload handling
 * 
 * Calls the main model upload handler in file-handler.php
 *
 * @param array $file File data from $_FILES
 * @return array|false Array with file information or false on failure
 */
function expoxr_sanitize_model_upload($file) {
    // Check if original function exists
    if (function_exists('expoxr_handle_model_upload')) {
        return expoxr_handle_model_upload($file);
    }
    
    return false;
}

/**
 * Get 3D model meta with default values
 *
 * @param int $post_id Post ID
 * @param string $key Meta key
 * @param mixed $default Default value
 * @return mixed Meta value or default
 */
function expoxr_get_model_meta($post_id, $key, $default = '') {
    $value = get_post_meta($post_id, $key, true);
    return !empty($value) ? $value : $default;
}

/**
 * Validate camera orbit value
 *
 * @param string $value Camera orbit value
 * @return string Validated value
 */
function expoxr_validate_camera_orbit($value) {
    // Allow empty values
    if (empty($value)) {
        return '';
    }
    
    // Simple regex to validate camera orbit format (e.g., "45deg 55deg 2m")
    if (preg_match('/^(\d+deg\s+\d+deg\s+\d+[m|cm])|auto$/', $value)) {
        return $value;
    }
    
    return '';
}

/**
 * Validate field of view value
 *
 * @param string $value Field of view value
 * @return string Validated value
 */
function expoxr_validate_field_of_view($value) {
    // Allow empty values
    if (empty($value)) {
        return '';
    }
    
    // Simple regex to validate field of view format (e.g., "30deg")
    if (preg_match('/^\d+deg$/', $value)) {
        return $value;
    }
    
    return '';
}

/**
 * Validate camera target value
 *
 * @param string $value Camera target value
 * @return string Validated value
 */
function expoxr_validate_camera_target($value) {
    // Allow empty values
    if (empty($value)) {
        return '';
    }
    
    // Simple regex to validate camera target format (e.g., "0m 1.5m 0m")
    if (preg_match('/^(-?\d+\.?\d*[m|cm]\s+-?\d+\.?\d*[m|cm]\s+-?\d+\.?\d*[m|cm])|auto$/', $value)) {
        return $value;
    }
    
    return '';
}

/**
 * Validate numeric range
 *
 * @param float $value Value to validate
 * @param float $min Minimum allowed value
 * @param float $max Maximum allowed value
 * @param float $default Default value if invalid
 * @return float Validated value
 */
function expoxr_validate_numeric_range($value, $min, $max, $default) {
    $value = (float) $value;
    
    if ($value < $min || $value > $max) {
        return $default;
    }
    
    return $value;
}





