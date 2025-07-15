<?php
/**
 * AJAX handlers for ExploreXR Free
 *
 * Basic AJAX functionality for the free version
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Basic AJAX handlers for the free version
// Premium addon management features are not available in the free version

/**
 * Basic AJAX response for free version
 */
function expoxr_free_ajax_response() {
    wp_send_json_error(array(
        'message' => 'Premium features are not available in the free version.'
    ));
}

// Register basic AJAX handlers
add_action('wp_ajax_expoxr_save_addon_settings', 'expoxr_free_ajax_response');
add_action('wp_ajax_nopriv_expoxr_save_addon_settings', 'expoxr_free_ajax_response');
