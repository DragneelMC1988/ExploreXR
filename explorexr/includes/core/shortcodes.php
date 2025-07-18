<?php
/**
 * ExpoXR Shortcodes
 *
 * @package ExpoXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include helper functions only when needed (moved from top level to prevent circular dependencies)
// require_once EXPOXR_PLUGIN_DIR . 'includes/models/model-helper.php';

// Make sure we have access to plugin functions
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

// Enqueue model loader script when needed
function expoxr_enqueue_model_loader() {
    wp_enqueue_script('expoxr-model-loader', EXPOXR_PLUGIN_URL . 'assets/js/model-loader.js', array(), '1.0', true);
    
    // Pass debug settings to model-loader script
    $debug_mode = get_option('expoxr_debug_mode', false);
    if ($debug_mode) {
        wp_localize_script('expoxr-model-loader', 'exploreXRDebug', array(
            'enabled' => true,
            'version' => EXPOXR_VERSION
        ));
    }
}

// Helper function to build model-viewer attributes
function expoxr_build_model_attributes($model_id, $model_file, $alt_text, $width, $height, $model_poster = '') {
    // Basic attributes
    $attributes = array(
        'src' => $model_file,
        'alt' => $alt_text,
        'style' => "width: {$width}; height: {$height};"
    );
    
    // Add poster if available
    if (!empty($model_poster)) {
        $attributes['poster'] = $model_poster;
    }
    
    // Basic interaction controls (free version)
    // Check enable_interactions setting
    $enable_interactions = get_post_meta($model_id, '_expoxr_enable_interactions', true) === 'on';
    
    // Add camera-controls if interactions are enabled
    if ($enable_interactions) {
        $attributes['camera-controls'] = '';
    }
    
    // Add touch-action
    $touch_action = get_post_meta($model_id, '_expoxr_touch_action', true);
    if (!empty($touch_action)) {
        $attributes['touch-action'] = $touch_action;
    }
    
    // Add orbit sensitivity
    $orbit_sensitivity = get_post_meta($model_id, '_expoxr_orbit_sensitivity', true);
    if (!empty($orbit_sensitivity)) {
        $attributes['orbit-sensitivity'] = $orbit_sensitivity;
    }
    
    // Add auto-rotate
    $auto_rotate = get_post_meta($model_id, '_expoxr_auto_rotate', true) === 'on';
    if ($auto_rotate) {
        $attributes['auto-rotate'] = '';
        
        // Add auto-rotate delay (default to 5000ms if not set)
        $auto_rotate_delay = get_post_meta($model_id, '_expoxr_auto_rotate_delay', true);
        if (empty($auto_rotate_delay) || !is_numeric($auto_rotate_delay)) {
            $auto_rotate_delay = '5000';
        }
        $attributes['auto-rotate-delay'] = $auto_rotate_delay;
        
        // Add rotation speed (default to 30deg if not set)
        $rotation_per_second = get_post_meta($model_id, '_expoxr_rotation_per_second', true);
        if (empty($rotation_per_second)) {
            $rotation_per_second = '30deg';
        }
        
        // Ensure rotation speed has 'deg' suffix if it's just a number
        if (is_numeric($rotation_per_second)) {
            $rotation_per_second .= 'deg';
        }
        
        $attributes['rotation-per-second'] = $rotation_per_second;
    }
    
    // Add interaction prompt settings
    $interaction_prompt = get_post_meta($model_id, '_expoxr_interaction_prompt', true);
    if (!empty($interaction_prompt)) {
        $attributes['interaction-prompt'] = $interaction_prompt;
    }
    
    $interaction_prompt_style = get_post_meta($model_id, '_expoxr_interaction_prompt_style', true);
    if (!empty($interaction_prompt_style)) {
        $attributes['interaction-prompt-style'] = $interaction_prompt_style;
    }
    
    $interaction_prompt_threshold = get_post_meta($model_id, '_expoxr_interaction_prompt_threshold', true);
    if (!empty($interaction_prompt_threshold)) {
        $attributes['interaction-prompt-threshold'] = $interaction_prompt_threshold;
    }
    
    // Basic camera settings (advanced camera controls are not available in free version)
    $camera_orbit = get_post_meta($model_id, '_expoxr_camera_orbit', true);
    if (!empty($camera_orbit)) {
        $attributes['camera-orbit'] = $camera_orbit;
    }
    
    $camera_target = get_post_meta($model_id, '_expoxr_camera_target', true);
    if (!empty($camera_target)) {
        $attributes['camera-target'] = $camera_target;
    }
    
    $field_of_view = get_post_meta($model_id, '_expoxr_field_of_view', true);
    if (!empty($field_of_view)) {
        $attributes['field-of-view'] = $field_of_view;
    }
    
    $max_camera_orbit = get_post_meta($model_id, '_expoxr_max_camera_orbit', true);
    if (!empty($max_camera_orbit)) {
        $attributes['max-camera-orbit'] = $max_camera_orbit;
    }
    
    $min_camera_orbit = get_post_meta($model_id, '_expoxr_min_camera_orbit', true);
    if (!empty($min_camera_orbit)) {
        $attributes['min-camera-orbit'] = $min_camera_orbit;
    }
    
    $max_field_of_view = get_post_meta($model_id, '_expoxr_max_field_of_view', true);
    if (!empty($max_field_of_view)) {
        $attributes['max-field-of-view'] = $max_field_of_view;
    }
    
    $min_field_of_view = get_post_meta($model_id, '_expoxr_min_field_of_view', true);
    if (!empty($min_field_of_view)) {
        $attributes['min-field-of-view'] = $min_field_of_view;
    }
    
    // Animation features are not available in the Free version
    // This feature is available in the Pro version only

    // AR features are available in premium version only
    // Free version does not include AR support
    
    // Add any additional data attributes from plugin settings
    $attributes = apply_filters('expoxr_model_viewer_attributes', $attributes, $model_id);
    
    return $attributes;
}

// Convert attributes array to HTML attributes string
function expoxr_generate_attributes_html($attributes) {
    $html = '';
    
    foreach ($attributes as $key => $value) {
        // Annotations are not available in the Free version
        if ($key === 'annotations') {
            continue;
        }
        
        if ($value === '') {
            // For boolean attributes
            $html .= ' ' . esc_attr($key);
        } else {
            $html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }
    
    return $html;
}

// Register a shortcode to display 3D models
add_shortcode('expoxr_model', function ($atts) {
    // Include helper functions when needed
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/models/model-helper.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/models/model-helper.php';
    }
    
    $atts = shortcode_atts(['id' => ''], $atts, 'expoxr_model');
    $model_id = intval($atts['id']);
    
    if (!$model_id) {
        return 'Invalid model ID.';
    }

    $model_file = get_post_meta($model_id, '_expoxr_model_file', true);
    if (!$model_file) {
        return ' ExploreXR Alert: Model not found. Possibly abducted by polygons from another dimension.';
    }
    
    // Get alt text for accessibility
    $alt_text = get_post_meta($model_id, '_expoxr_model_alt_text', true);
    if (empty($alt_text)) {
        $alt_text = get_the_title($model_id) . ' 3D Model';
    }
    
    // Get poster image if available
    $model_poster = get_post_meta($model_id, '_expoxr_model_poster', true);
    $model_poster_id = get_post_meta($model_id, '_expoxr_model_poster_id', true);

    // Get size settings
    $viewer_size = get_post_meta($model_id, '_expoxr_viewer_size', true);
    $width = '100%';
    $height = '500px';
    
    // Apply preset sizes or custom dimensions
    if ($viewer_size === 'small') {
        $width = '300px';
        $height = '300px';
    } elseif ($viewer_size === 'medium') {
        $width = '500px';
        $height = '500px';
    } elseif ($viewer_size === 'large') {
        $width = '800px';
        $height = '600px';
    } elseif ($viewer_size === 'full') {
        $width = '98vw';
        $height = '98vh';
    } else {
        // Custom size
        $custom_width = get_post_meta($model_id, '_expoxr_viewer_width', true);
        $custom_height = get_post_meta($model_id, '_expoxr_viewer_height', true);
        
        if (!empty($custom_width)) {
            $width = $custom_width;
        }
        
        if (!empty($custom_height)) {
            $height = $custom_height;
        }
    }
    
    // Get responsive sizes for tablet and mobile
    $tablet_width = get_post_meta($model_id, '_expoxr_tablet_viewer_width', true);
    $tablet_height = get_post_meta($model_id, '_expoxr_tablet_viewer_height', true);
    $mobile_width = get_post_meta($model_id, '_expoxr_mobile_viewer_width', true);
    $mobile_height = get_post_meta($model_id, '_expoxr_mobile_viewer_height', true);
    
    // Generate unique CSS ID for this model instance
    $model_css_id = 'expoxr-model-' . $model_id . '-' . uniqid();
    
    // Generate responsive CSS if tablet or mobile sizes are set
    $responsive_css = '';
    if (!empty($tablet_width) || !empty($tablet_height) || !empty($mobile_width) || !empty($mobile_height)) {
        $responsive_css .= '<style>';
        
        // Tablet styles (768px to 1024px)
        if (!empty($tablet_width) || !empty($tablet_height)) {
            $responsive_css .= '@media (min-width: 768px) and (max-width: 1024px) {';
            $responsive_css .= '#' . $model_css_id . ' {';
            if (!empty($tablet_width)) {
                $responsive_css .= 'width: ' . esc_attr($tablet_width) . ' !important;';
            }
            if (!empty($tablet_height)) {
                $responsive_css .= 'height: ' . esc_attr($tablet_height) . ' !important;';
            }
            $responsive_css .= '}';
            $responsive_css .= '}';
        }
        
        // Mobile styles (up to 767px)
        if (!empty($mobile_width) || !empty($mobile_height)) {
            $responsive_css .= '@media (max-width: 767px) {';
            $responsive_css .= '#' . $model_css_id . ' {';
            if (!empty($mobile_width)) {
                $responsive_css .= 'width: ' . esc_attr($mobile_width) . ' !important;';
            }
            if (!empty($mobile_height)) {
                $responsive_css .= 'height: ' . esc_attr($mobile_height) . ' !important;';
            }
            $responsive_css .= '}';
            $responsive_css .= '}';
        }
        
        $responsive_css .= '</style>';
    }
    
    // Annotations are not available in the free version
    $annotations = null;
    
    // Enqueue required scripts and styles
    wp_enqueue_style('expoxr-model-viewer', EXPOXR_PLUGIN_URL . 'assets/css/model-viewer.css', array(), EXPOXR_VERSION);
    
    // Make sure the script is loaded when shortcode is used
    ob_start();
    include EXPOXR_PLUGIN_DIR . 'template-parts/model-viewer-script.php';
    $script = ob_get_clean();    // Check if the model is a large file that needs special handling
    $large_model_handling = get_option('expoxr_large_model_handling', 'direct');
    $large_model_size_threshold = get_option('expoxr_large_model_size_threshold', 16);
    
    // Check file size if the file exists locally
    $is_large_model = false;
    $file_path = '';
      // Only run str_replace if $model_file is a string
    if (is_string($model_file)) {
        $file_path = str_replace(EXPOXR_MODELS_URL, EXPOXR_MODELS_DIR, $model_file);
        
        if (file_exists($file_path)) {
            $file_size_mb = filesize($file_path) / (1024 * 1024); // Convert to MB
            if ($file_size_mb >= $large_model_size_threshold) {
                $is_large_model = true;
            }
        } elseif (strpos($model_file, 'http') === 0) {
            // For external files, try to get the size with a HEAD request
            $request = wp_remote_head($model_file);
            if (!is_wp_error($request) && isset($request['headers']['content-length'])) {
                $file_size_mb = intval($request['headers']['content-length']) / (1024 * 1024);
                if ($file_size_mb >= $large_model_size_threshold) {
                    $is_large_model = true;
                }
            }
        }
    }
    
    // Only use poster_button mode if specifically configured AND it's a large model AND we have a poster image
    if ($is_large_model && $large_model_handling === 'poster_button' && !empty($model_poster)) {
        // Generate a unique ID for this model instance
        $model_instance_id = 'expoxr-model-' . $model_id . '-' . uniqid();
        
        // Enqueue the model loader script
        expoxr_enqueue_model_loader();
        
        // Build model attributes using our helper function
        $model_attributes = expoxr_build_model_attributes($model_id, $model_file, $alt_text, $width, $height, $model_poster);
        
        // Add unique CSS ID for responsive styling
        $model_attributes['id'] = $model_css_id;
        
        // Annotations are not available in the Free version
        // This feature is available in the Pro version only
        
        // Pass the model-viewer attributes to the JavaScript
        $model_attributes_json = json_encode($model_attributes);
        
        // Check for JSON encoding errors
        if ($model_attributes_json === false) {
            // Log error and fall back to empty object
            if (get_option('expoxr_debug_mode', false)) {
                expoxr_log('ExpoXR: JSON encoding error in model attributes for model ID: ' . $model_id, 'error');
            }
            $model_attributes_json = '{}';
        }
        
        // Make sure we have valid JSON for JavaScript
        $model_attributes_json = preg_replace('/[\r\n\t]/', '', $model_attributes_json);
        
        // Load the large model template
        ob_start();
        include EXPOXR_PLUGIN_DIR . 'template-parts/large-model-template.php';
        $model_html = ob_get_clean();
        
        // Return the complete HTML with responsive CSS
        return $responsive_css . $script . $model_html;
    } else {
        // Regular loading approach for smaller models or if set to direct loading
        // Build model attributes using our helper function
        $model_attributes = expoxr_build_model_attributes($model_id, $model_file, $alt_text, $width, $height, $model_poster);
        
        // Add class for our wrapper script and unique CSS ID for responsive styling
        $model_attributes['class'] = 'expoxr-model';
        $model_attributes['id'] = $model_css_id;
        
        // Convert attributes to HTML string
        $attributes_html = expoxr_generate_attributes_html($model_attributes);
        
        // Annotations are not available in the Free version
        $annotations_html = '';
        
        // Load the standard model template
        ob_start();
        include EXPOXR_PLUGIN_DIR . 'template-parts/standard-model-template.php';
        $model_html = ob_get_clean();
        
        // Return the complete HTML with responsive CSS
        return $responsive_css . $script . $model_html;
    }
});

// Enqueue admin scripts
add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos($hook, 'explorexr') !== false) {
        // Add the script directly in admin
        wp_enqueue_script('expoxr-model-loader', EXPOXR_PLUGIN_URL . 'assets/js/model-loader.js', array('jquery'), EXPOXR_VERSION, true);
    }
});





