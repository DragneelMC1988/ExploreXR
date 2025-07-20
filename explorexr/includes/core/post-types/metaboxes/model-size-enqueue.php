<?php
/**
 * Model Size Metabox Enqueue
 *
  * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue scripts and styles for the model size metabox
 */
function explorexr_model_size_metabox_enqueue_scripts($hook) {
    // Only load on model edit page
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'explorexr_model') {
        return;
    }
    
    // Enqueue CSS
    wp_enqueue_style(
        'explorexr-model-size',
        EXPLOREXR_PLUGIN_URL . 'assets/css/model-size.css',
        array(),
        EXPLOREXR_VERSION
    );
    
    // Enqueue JavaScript
    wp_enqueue_script(
        'explorexr-model-size',
        EXPLOREXR_PLUGIN_URL . 'assets/js/model-size.js',
        array('jquery', 'wp-media-utils'),
        EXPLOREXR_VERSION,
        true
    );
    
    // Make sure media scripts are loaded
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'explorexr_model_size_metabox_enqueue_scripts');





