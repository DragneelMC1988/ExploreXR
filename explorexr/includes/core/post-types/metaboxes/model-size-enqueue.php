<?php
/**
 * Model Size Metabox Enqueue
 *
 * @package ExpoXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue scripts and styles for the model size metabox
 */
function expoxr_model_size_metabox_enqueue_scripts($hook) {
    // Only load on model edit page
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'expoxr_model') {
        return;
    }
    
    // Enqueue CSS
    wp_enqueue_style(
        'expoxr-model-size',
        EXPOXR_PLUGIN_URL . 'assets/css/model-size.css',
        array(),
        EXPOXR_VERSION
    );
    
    // Enqueue JavaScript
    wp_enqueue_script(
        'expoxr-model-size',
        EXPOXR_PLUGIN_URL . 'assets/js/model-size.js',
        array('jquery', 'wp-media-utils'),
        EXPOXR_VERSION,
        true
    );
    
    // Make sure media scripts are loaded
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'expoxr_model_size_metabox_enqueue_scripts');





