<?php
/**
 * ExploreXR WordPress Standard Debugging
 * 
 * Uses WordPress standard debugging instead of custom logging system
 * 
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if WordPress debugging is enabled
 * 
 * @return bool True if WordPress debugging is enabled
 */
if (!function_exists('explorexr_is_debug_enabled')) {
    function explorexr_is_debug_enabled() {
        return defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
    }
}

/**
 * Standard WordPress logging function
 * 
 * @param mixed $message The message to log (string, array, object, etc.)
 * @param string $level Optional log level (info, warning, error) - for compatibility only
 */
if (!function_exists('explorexr_log')) {
    function explorexr_log($message, $level = 'info') {
        // Only log if WordPress debugging is enabled
        if (!explorexr_is_debug_enabled()) {
            return;
        }
        
        // Convert arrays/objects to readable strings
        if (is_array($message) || is_object($message)) {
            $message = wp_json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if ($message === false) {
                $message = 'Unable to encode message';
            }
        }
        
        // Use WordPress standard error_log
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Standard WordPress debugging
        error_log('ExploreXR [' . strtoupper($level) . ']: ' . $message);
    }
}

/**
 * Legacy uppercase function for backwards compatibility
 * Used by security handler and other legacy code
 * 
 * @param mixed $message The message to log
 * @param string $level Optional log level
 */
if (!function_exists('ExploreXR_log')) {
    function ExploreXR_log($message, $level = 'info') {
        explorexr_log($message, $level);
    }
}

/**
 * Legacy function for backwards compatibility - use WordPress standard debugging
 * 
 * @param mixed $message The message to log
 * @param string $level Optional log level
 */
if (!function_exists('explorexr_debug_log')) {
    function explorexr_debug_log($message, $level = 'info') {
        explorexr_log($message, $level);
    }
}

/**
 * Initialize debugging system
 * Called by main plugin file during initialization
 */
if (!function_exists('explorexr_init_debugging')) {
    function explorexr_init_debugging() {
        // Nothing special needed for WordPress standard debugging
        // This function exists for backwards compatibility
    }
}