<?php
/**
 * ExploreXR Loading Options
 * 
 * Handles loading options for 3D models.
 * 
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register loading options settings
 */
function explorexr_register_loading_options_settings() {
    // Register loading type setting (use same name as admin page)
    register_setting(
        'explorexr_options',
        'explorexr_loading_display',
        array(
            'type' => 'string',
            'default' => 'bar',
            'sanitize_callback' => 'sanitize_text_field'
        )
    );
    
    // Register loading color setting (for free version this is set and not changeable)
    register_setting(
        'explorexr_options',
        'explorexr_loading_bar_color',
        array(
            'type' => 'string',
            'default' => '#1e88e5',
            'sanitize_callback' => 'explorexr_sanitize_hex_color'
        )
    );
}
add_action('admin_init', 'explorexr_register_loading_options_settings');

/**
 * Get loading options from settings
 */
function explorexr_get_loading_options() {
    $options = array(
        'loading_type' => get_option('explorexr_loading_display', 'bar'), // Use the same option name as admin page
        'loading_color' => get_option('explorexr_loading_bar_color', '#1e88e5'), // Match the actual option name
        'show_progress_bar' => true,
        'show_percentage' => true,
        'version' => EXPLOREXR_VERSION,
        'timestamp' => time() // Add timestamp for cache busting
    );
    
    // Adjust flags based on loading type
    if ($options['loading_type'] === 'bar') {
        $options['show_percentage'] = false;
    } elseif ($options['loading_type'] === 'percentage') {
        $options['show_progress_bar'] = false;
    }
    
    return $options;
}



/**
 * Add loading options to model-viewer element
 *
 * Skipped when the Loading Addon is active and enabled for this model — the
 * addon injects its own full set of data-loading-* attributes at priority 15
 * and the core loading UI defers to it.
 */
function explorexr_add_loading_options_to_model_viewer($attributes, $model_id = null) {
    // If Loading Addon is enabled for this specific model, skip core loading
    // attributes.  The addon handles everything at priority 15.
    if ( $model_id ) {
        $loading_enabled = get_post_meta( $model_id, '_explorexr_loading_enable', true );
        if ( $loading_enabled === '1' || $loading_enabled === 'on' ) {
            return $attributes;
        }
    }

    $loading_options = explorexr_get_loading_options();
    
    // Set loading-type data attribute
    $attributes['data-loading-type'] = $loading_options['loading_type'];
    
    // Set loading-color data attribute
    $attributes['data-loading-color'] = $loading_options['loading_color'];
    
    return $attributes;
}
add_filter('explorexr_premium_model_viewer_attributes', 'explorexr_add_loading_options_to_model_viewer', 10, 2);






