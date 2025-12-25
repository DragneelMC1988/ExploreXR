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
    $loading_display = get_option('explorexr_loading_display', 'bar');
    
    $options = array(
        // Core display settings
        'loading_type' => $loading_display,
        'loading_display' => $loading_display, // Alias for backwards compatibility
        
        // Loading bar settings
        'loading_bar_color' => get_option('explorexr_loading_bar_color', '#1e88e5'),
        'loading_bar_size' => get_option('explorexr_loading_bar_size', 'medium'),
        'loading_bar_position' => get_option('explorexr_loading_bar_position', 'middle'),
        
        // Percentage counter settings
        'percentage_font_size' => get_option('explorexr_percentage_font_size', 24),
        'percentage_font_family' => get_option('explorexr_percentage_font_family', 'Arial, sans-serif'),
        'percentage_font_color' => get_option('explorexr_percentage_font_color', '#333333'),
        'percentage_position' => get_option('explorexr_percentage_position', 'center-center'),
        
        // Overlay settings
        'overlay_bg_color' => get_option('explorexr_overlay_bg_color', '#FFFFFF'),
        'overlay_bg_opacity' => get_option('explorexr_overlay_bg_opacity', 70),
        
        // Large model handling
        'large_model_handling' => get_option('explorexr_large_model_handling', 'direct'),
        'large_model_size_threshold' => get_option('explorexr_large_model_size_threshold', 16),
        
        // Lazy loading
        'lazy_load_poster' => get_option('explorexr_lazy_load_poster', false),
        
        // Computed flags for JavaScript
        'show_progress_bar' => ($loading_display === 'bar' || $loading_display === 'both'),
        'show_percentage' => ($loading_display === 'percentage' || $loading_display === 'both'),
        
        // Metadata
        'version' => defined('EXPLOREXR_VERSION') ? EXPLOREXR_VERSION : '1.0.7',
        'timestamp' => time() // Cache busting
    );
    
    return $options;
}



/**
 * Add loading options to model-viewer element
 * Adds data attributes to the model-viewer HTML element for JavaScript configuration
 */
function explorexr_add_loading_options_to_model_viewer($attributes, $model_id = null) {
    $loading_options = explorexr_get_loading_options();
    
    // Add all loading options as data attributes for JavaScript
    $attributes['data-loading-type'] = $loading_options['loading_type'];
    $attributes['data-loading-bar-color'] = $loading_options['loading_bar_color'];
    $attributes['data-loading-bar-size'] = $loading_options['loading_bar_size'];
    $attributes['data-loading-bar-position'] = $loading_options['loading_bar_position'];
    $attributes['data-percentage-font-size'] = $loading_options['percentage_font_size'];
    $attributes['data-percentage-font-family'] = $loading_options['percentage_font_family'];
    $attributes['data-percentage-font-color'] = $loading_options['percentage_font_color'];
    $attributes['data-percentage-position'] = $loading_options['percentage_position'];
    $attributes['data-overlay-bg-color'] = $loading_options['overlay_bg_color'];
    $attributes['data-overlay-bg-opacity'] = $loading_options['overlay_bg_opacity'];
    
    // Add flags for JavaScript logic
    if ($loading_options['show_progress_bar']) {
        $attributes['data-show-progress-bar'] = 'true';
    }
    if ($loading_options['show_percentage']) {
        $attributes['data-show-percentage'] = 'true';
    }
    
    return $attributes;
}
add_filter('explorexr_model_viewer_attributes', 'explorexr_add_loading_options_to_model_viewer', 10, 2);






