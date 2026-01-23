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

/**
 * Get canonical viewer size presets for all device breakpoints.
 *
 * Returns a normalized array to keep size logic consistent across admin forms,
 * shortcode rendering, and metabox saves.
 *
 * @return array
 */
function explorexr_get_viewer_size_presets() {
    return array(
        'small'  => array(
            'desktop' => array('width' => '300px', 'height' => '300px'),
            'tablet'  => array('width' => '280px', 'height' => '280px'),
            'mobile'  => array('width' => '100%', 'height' => '280px'),
        ),
        'medium' => array(
            'desktop' => array('width' => '500px', 'height' => '500px'),
            'tablet'  => array('width' => '450px', 'height' => '450px'),
            'mobile'  => array('width' => '100%', 'height' => '400px'),
        ),
        'large'  => array(
            'desktop' => array('width' => '800px', 'height' => '600px'),
            'tablet'  => array('width' => '600px', 'height' => '450px'),
            'mobile'  => array('width' => '100%', 'height' => '400px'),
        ),
        'full'   => array(
            'desktop' => array('width' => '98vw', 'height' => '98vh'),
            'tablet'  => array('width' => '98vw', 'height' => '98vh'),
            'mobile'  => array('width' => '98vw', 'height' => '98vh'),
        ),
    );
}

/**
 * Normalize viewer size inputs into deterministic device widths/heights.
 *
 * - Applies presets when viewer_size matches a known preset.
 * - Ensures tablet/mobile fall back to desktop when missing.
 * - Prevents width/height from both using % by forcing height to px when needed.
 *
 * @param string $viewer_size   Requested viewer size (preset or custom).
 * @param array  $dimensions    Raw dimension inputs keyed by device.
 * @return array Normalized dimensions with keys: width, height, tablet_width, tablet_height, mobile_width, mobile_height, viewer_size.
 */
function explorexr_normalize_viewer_sizes($viewer_size, $dimensions = array()) {
    $presets = explorexr_get_viewer_size_presets();
    $requested_size = $viewer_size ?: 'custom';
    
    // Build base structure
    $normalized = array(
        'viewer_size'      => $requested_size,
        'width'            => isset($dimensions['width']) ? trim($dimensions['width']) : '',
        'height'           => isset($dimensions['height']) ? trim($dimensions['height']) : '',
        'tablet_width'     => isset($dimensions['tablet_width']) ? trim($dimensions['tablet_width']) : '',
        'tablet_height'    => isset($dimensions['tablet_height']) ? trim($dimensions['tablet_height']) : '',
        'mobile_width'     => isset($dimensions['mobile_width']) ? trim($dimensions['mobile_width']) : '',
        'mobile_height'    => isset($dimensions['mobile_height']) ? trim($dimensions['mobile_height']) : '',
    );
    
    // Apply preset if available
    if (isset($presets[$requested_size])) {
        $preset = $presets[$requested_size];
        $normalized['viewer_size']   = $requested_size;
        $normalized['width']         = $preset['desktop']['width'];
        $normalized['height']        = $preset['desktop']['height'];
        $normalized['tablet_width']  = $preset['tablet']['width'];
        $normalized['tablet_height'] = $preset['tablet']['height'];
        $normalized['mobile_width']  = $preset['mobile']['width'];
        $normalized['mobile_height'] = $preset['mobile']['height'];
    } else {
        // Custom: ensure we have desktop values
        $normalized['width']  = $normalized['width'] ?: '100%';
        $normalized['height'] = $normalized['height'] ?: '500px';
        
        // Device fallbacks
        $normalized['tablet_width']  = $normalized['tablet_width'] ?: $normalized['width'];
        $normalized['tablet_height'] = $normalized['tablet_height'] ?: $normalized['height'];
        $normalized['mobile_width']  = $normalized['mobile_width'] ?: $normalized['width'];
        $normalized['mobile_height'] = $normalized['mobile_height'] ?: $normalized['height'];
    }
    
    // Enforce dimension safety rules per device
    $desktop_safe                = explorexr_sanitize_dimension_pair($normalized['width'], $normalized['height'], '500px');
    $normalized['width']         = $desktop_safe['width'];
    $normalized['height']        = $desktop_safe['height'];
    $tablet_safe                 = explorexr_sanitize_dimension_pair($normalized['tablet_width'], $normalized['tablet_height'], $normalized['height']);
    $mobile_safe                 = explorexr_sanitize_dimension_pair($normalized['mobile_width'], $normalized['mobile_height'], $normalized['height']);
    $normalized['tablet_width']  = $tablet_safe['width'];
    $normalized['tablet_height'] = $tablet_safe['height'];
    $normalized['mobile_width']  = $mobile_safe['width'];
    $normalized['mobile_height'] = $mobile_safe['height'];
    
    return $normalized;
}

/**
 * Ensure width/height pairing does not use % for both dimensions.
 * If both are %, height is forced to a safe pixel fallback.
 *
 * @param string $width        Raw width value.
 * @param string $height       Raw height value.
 * @param string $fallback_px  Fallback pixel height.
 * @param bool   $prefer_existing_height When true, reuse already-sanitized height where possible.
 * @return array Array with sanitized width/height.
 */
function explorexr_sanitize_dimension_pair($width, $height, $fallback_px = '500px', $prefer_existing_height = false) {
    $width  = trim((string) $width);
    $height = trim((string) $height);
    
    $is_width_percent  = (bool) preg_match('/%$/', $width);
    $is_height_percent = (bool) preg_match('/%$/', $height);
    
    // If both are percentages, force height to px fallback to avoid invisible model
    if ($is_width_percent && $is_height_percent) {
        $height = $prefer_existing_height && !$is_height_percent ? $height : $fallback_px;
        $is_height_percent = false;
    }
    
    return array(
        'width'  => $width ?: '100%',
        'height' => $height ?: $fallback_px,
    );
}
