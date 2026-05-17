<?php
/**
 * Safe String Operations Utility
 * 
 * Provides safe string manipulation functions for ExploreXR plugin
 * 
 * @package ExploreXR
 * @version 1.0.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Safely sanitize a string for output
 * 
 * @param string $string The string to sanitize
 * @return string Sanitized string
 */
function explorexr_safe_sanitize_text($string) {
    return sanitize_text_field($string);
}

/**
 * Safely escape a string for HTML output
 * 
 * @param string $string The string to escape
 * @return string Escaped string
 */
function explorexr_safe_escape_html($string) {
    return esc_html($string ?? '');
}

/**
 * Safely escape a string for attribute output
 * 
 * @param string $string The string to escape
 * @return string Escaped string
 */
function explorexr_safe_escape_attr($string) {
    return esc_attr($string ?? '');
}

/**
 * Safely escape a URL
 * 
 * @param string $url The URL to escape
 * @return string Escaped URL
 */
function explorexr_safe_escape_url($url) {
    return esc_url($url ?? '');
}

/**
 * Safely strip tags from a string
 * 
 * @param string $string The string to strip tags from
 * @return string String with tags removed
 */
function explorexr_safe_strip_tags($string) {
    return wp_strip_all_tags($string);
}

/**
 * Safely validate and sanitize a numeric value
 * 
 * @param mixed $value The value to validate
 * @param int $default Default value if validation fails
 * @return int Validated integer
 */
function explorexr_safe_int($value, $default = 0) {
    $int_value = intval($value);
    return is_numeric($value) ? $int_value : $default;
}

/**
 * Safely validate and sanitize a float value
 * 
 * @param mixed $value The value to validate
 * @param float $default Default value if validation fails
 * @return float Validated float
 */
function explorexr_safe_float($value, $default = 0.0) {
    $float_value = floatval($value);
    return is_numeric($value) ? $float_value : $default;
}
