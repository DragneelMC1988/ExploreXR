<?php
/**
 * Form Helper Functions
 *
 * PHP 8.1+ compatible wrapper functions for WordPress form helpers
 * to prevent null parameter deprecation warnings.
 *
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Safe wrapper for WordPress checked() function
 * Ensures no null values are passed to prevent PHP 8.1+ deprecation warnings
 *
 * @param mixed $checked One of the values to compare
 * @param mixed $current The other value to compare if not just true
 * @param bool  $echo    Whether to echo or just return the string
 * @return string HTML attribute or empty string
 */
function explorexr_checked($checked, $current = true, $echo = true) {
    // Convert null to empty string to prevent PHP 8.1+ warnings
    $checked = $checked ?? '';
    $current = $current ?? '';
    
    return checked($checked, $current, $echo);
}

/**
 * Safe wrapper for WordPress selected() function
 * Ensures no null values are passed to prevent PHP 8.1+ deprecation warnings
 *
 * @param mixed $selected One of the values to compare
 * @param mixed $current  The other value to compare if not just true
 * @param bool  $echo     Whether to echo or just return the string
 * @return string HTML attribute or empty string
 */
function explorexr_selected($selected, $current = true, $echo = true) {
    // Convert null to empty string to prevent PHP 8.1+ warnings
    $selected = $selected ?? '';
    $current = $current ?? '';
    
    return selected($selected, $current, $echo);
}

/**
 * Safe wrapper for WordPress disabled() function
 * Ensures no null values are passed to prevent PHP 8.1+ deprecation warnings
 *
 * @param mixed $disabled One of the values to compare
 * @param mixed $current  The other value to compare if not just true
 * @param bool  $echo     Whether to echo or just return the string
 * @return string HTML attribute or empty string
 */
function explorexr_disabled($disabled, $current = true, $echo = true) {
    // Convert null to empty string to prevent PHP 8.1+ warnings
    $disabled = $disabled ?? '';
    $current = $current ?? '';
    
    return disabled($disabled, $current, $echo);
}

/**
 * Safe get_post_meta wrapper that ensures a string is always returned
 * Prevents null values that cause PHP 8.1+ deprecation warnings
 *
 * @param int    $post_id Post ID
 * @param string $key     Meta key
 * @param bool   $single  Whether to return a single value
 * @return mixed Meta value, never null
 */
function explorexr_get_post_meta($post_id, $key, $single = false) {
    $value = get_post_meta($post_id, $key, $single);
    
    // If single value requested and it's null/false, return empty string
    if ($single && ($value === null || $value === false)) {
        return '';
    }
    
    return $value;
}

/**
 * Safe get_option wrapper that ensures a string is always returned
 * Prevents null values that cause PHP 8.1+ deprecation warnings
 *
 * @param string $option  Option name
 * @param mixed  $default Default value
 * @return mixed Option value, never null unless default is null
 */
function explorexr_get_option($option, $default = '') {
    $value = get_option($option, $default);
    
    // If value is null or false and no specific default, return empty string
    if (($value === null || $value === false) && $default === '') {
        return '';
    }
    
    return $value;
}
