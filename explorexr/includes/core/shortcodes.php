<?php
/**
 * ExploreXR Shortcodes
 *
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include helper functions only when needed (moved from top level to prevent circular dependencies)
// require_once EXPLOREXR_PLUGIN_DIR . 'includes/models/model-helper.php';

// Note: The is_plugin_active() function is only available in admin context
// We don't need it in shortcodes since they run on frontend
// If needed in admin, WordPress automatically loads plugin.php

/**
 * Convert percentage-based CSS values to viewport units.
 *
 * Percentage units are no longer accepted for new models, but existing
 * models saved before this change may still have "%" values in post meta.
 * This backward-compatibility helper converts those legacy "%" values to
 * "vw" (width) or "vh" (height) so the container always resolves to a
 * concrete pixel value.
 *
 * @param string $value     CSS size value, e.g. "100%", "50%", "500px".
 * @param string $dimension Either 'width' or 'height'.
 * @return string Converted value, or the original if no conversion needed.
 */
function explorexr_premium_convert_percent_to_viewport($value, $dimension = 'width') {
    if (empty($value)) {
        return $value;
    }
    // Match values ending in % (e.g. "100%", "50%") — legacy backward compat only
    if (preg_match('/^(\d+(?:\.\d+)?)\s*%$/', trim($value), $matches)) {
        $unit = ($dimension === 'height') ? 'vh' : 'vw';
        return $matches[1] . $unit;
    }
    return $value;
}

// Enqueue model loader script when needed
function EXPLOREXR_enqueue_model_loader() {
    wp_enqueue_script('explorexr-model-loader', EXPLOREXR_PREMIUM_PLUGIN_URL . 'assets/js/model-loader.js', array(), EXPLOREXR_PREMIUM_VERSION, true);
}

// Helper function to build model-viewer attributes
function EXPLOREXR_build_model_attributes($model_id, $model_file, $alt_text, $width, $height, $model_poster = '') {
    // Basic attributes - DO NOT add width/height here (applied to wrapper container instead)
    $attributes = array(
        'src' => $model_file,
        'alt' => $alt_text,
        // Store dimensions separately for wrapper container usage
        '_container_width' => $width,
        '_container_height' => $height
    );
    
    // Add poster if available
    if (!empty($model_poster)) {
        $attributes['poster'] = $model_poster;
    }
    
    // Base interaction controls — Premium plugin is always the standard for these
    $enable_interactions = get_post_meta($model_id, '_explorexr_enable_interactions', true);

    // Add camera-controls by default unless explicitly disabled
    if ($enable_interactions !== 'off') {
        $attributes['camera-controls'] = '';
    }

    // Add touch-action
    $touch_action = get_post_meta($model_id, '_explorexr_touch_action', true) ?: '';
    if (!empty($touch_action)) {
        $attributes['touch-action'] = $touch_action;
    }

    // Add orbit sensitivity
    $orbit_sensitivity = get_post_meta($model_id, '_explorexr_orbit_sensitivity', true) ?: '';
    if (!empty($orbit_sensitivity)) {
        $attributes['orbit-sensitivity'] = $orbit_sensitivity;
    }

    // Add auto-rotate
    $auto_rotate = get_post_meta($model_id, '_explorexr_auto_rotate', true) === 'on';
    if ($auto_rotate) {
        $attributes['auto-rotate'] = '';

        // Add auto-rotate delay (default to 5000ms if not set)
        $auto_rotate_delay = get_post_meta($model_id, '_explorexr_auto_rotate_delay', true) ?: '';
        if (empty($auto_rotate_delay) || !is_numeric($auto_rotate_delay)) {
            $auto_rotate_delay = '5000';
        }
        $attributes['auto-rotate-delay'] = $auto_rotate_delay;

        // Add rotation speed (default to 30deg if not set)
        $rotation_per_second = get_post_meta($model_id, '_explorexr_rotation_per_second', true) ?: '';
        if (empty($rotation_per_second)) {
            $rotation_per_second = '30deg';
        }

        // Ensure rotation speed has 'deg' suffix if it's just a number
        if (is_numeric($rotation_per_second)) {
            $rotation_per_second .= 'deg';
        }

        $attributes['rotation-per-second'] = $rotation_per_second;
    } else {
        // Add a flag to indicate auto-rotate should not be added automatically
        $attributes['no-auto-rotate'] = 'true';
    }

    // Add interaction prompt settings
    $interaction_prompt = get_post_meta($model_id, '_explorexr_interaction_prompt', true) ?: '';
    if (!empty($interaction_prompt)) {
        $attributes['interaction-prompt'] = $interaction_prompt;
    }

    $interaction_prompt_style = get_post_meta($model_id, '_explorexr_interaction_prompt_style', true) ?: '';
    if (!empty($interaction_prompt_style)) {
        $attributes['interaction-prompt-style'] = $interaction_prompt_style;
    }

    $interaction_prompt_threshold = get_post_meta($model_id, '_explorexr_interaction_prompt_threshold', true) ?: '';
    if (!empty($interaction_prompt_threshold)) {
        $attributes['interaction-prompt-threshold'] = $interaction_prompt_threshold;
    }
    
    // Basic camera settings (advanced camera controls are not available in free version)
    // Only apply camera-orbit if explicitly set AND camera addon is not enabled
    $camera_orbit = get_post_meta($model_id, '_explorexr_camera_orbit', true) ?: '';
    
    // Check if camera addon is active + enabled for this model
    $camera_addon_active = false;
    if (function_exists('explorexr_premium_is_addon_active')) {
        $camera_addon_active = explorexr_premium_is_addon_active('camera', true);
    }

    $camera_addon_enabled = $camera_addon_active;
    if ($camera_addon_active && function_exists('explorexr_camera_addon_is_enabled')) {
        $camera_addon_enabled = explorexr_camera_addon_is_enabled($model_id);
    }
    
    // Don't apply ANY base camera attributes if camera addon is enabled
    // (the camera addon filter handles ALL camera positioning and limits)
    if (!$camera_addon_enabled) {
        if (!empty($camera_orbit)) {
            $attributes['camera-orbit'] = $camera_orbit;
        }

        $camera_target = get_post_meta($model_id, '_explorexr_camera_target', true) ?: '';
        if (!empty($camera_target)) {
            $attributes['camera-target'] = $camera_target;
        }

        $field_of_view = get_post_meta($model_id, '_explorexr_field_of_view', true) ?: '';
        if (!empty($field_of_view)) {
            $attributes['field-of-view'] = $field_of_view;
        }

        $max_camera_orbit = get_post_meta($model_id, '_explorexr_max_camera_orbit', true) ?: '';
        if (!empty($max_camera_orbit)) {
            $attributes['max-camera-orbit'] = $max_camera_orbit;
        }

        $min_camera_orbit = get_post_meta($model_id, '_explorexr_min_camera_orbit', true) ?: '';
        if (!empty($min_camera_orbit)) {
            $attributes['min-camera-orbit'] = $min_camera_orbit;
        }

        $max_field_of_view = get_post_meta($model_id, '_explorexr_max_field_of_view', true) ?: '';
        if (!empty($max_field_of_view)) {
            $attributes['max-field-of-view'] = $max_field_of_view;
        }

        $min_field_of_view = get_post_meta($model_id, '_explorexr_min_field_of_view', true) ?: '';
        if (!empty($min_field_of_view)) {
            $attributes['min-field-of-view'] = $min_field_of_view;
        }
    }
    
    // Animation features are not available in the Free version
    // This feature is available in the Pro version only

    // AR features are available in premium version only
    // Free version does not include AR support
    
    // Apply site-wide tone-mapping default. Model Viewer v4.0+ changed the default from 'aces'
    // to 'pbr-neutral'. We preserve 'aces' here to maintain visual consistency for existing
    // installations. Admins can change this via Settings > Model Viewer > Default Tone Mapping.
    // Addons (e.g. Environment) can still override per-model via the filter below.
    if ( ! isset( $attributes['tone-mapping'] ) ) {
        $default_tone_mapping = get_option( 'explorexr_default_tone_mapping', 'aces' );
        if ( ! empty( $default_tone_mapping ) ) {
            $attributes['tone-mapping'] = sanitize_text_field( $default_tone_mapping );
        }
    }

    // Add any additional data attributes from plugin settings
    // Standardized filter for all addons
    $attributes = apply_filters('explorexr_premium_model_viewer_attributes', $attributes, $model_id);
    
    // Enhanced debug mode for frontend troubleshooting
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // Add debug config to frontend
        $debug_config = array(
            'model_id' => $model_id,
            'post_type' => get_post_type($model_id),
            'filter_count' => count($attributes),
            'has_animation' => isset($attributes['animation-name']) || isset($attributes['data-animation-name']),
            'has_pp' => isset($attributes['data-pp-enabled']),
            'active_addons' => array()
        );
        
        // Detect which add-ons injected attributes
        foreach ($attributes as $key => $value) {
            if (strpos($key, 'animation') !== false || strpos($key, 'data-animation') !== false) {
                $debug_config['active_addons']['animation'] = true;
            }
            if (strpos($key, 'data-pp-') !== false) {
                $debug_config['active_addons']['post_processing'] = true;
            }
            if (strpos($key, 'data-annotations') !== false) {
                $debug_config['active_addons']['annotations'] = true;
            }
        }
        
        $attributes['data-debug-mode'] = 'true';
        $attributes['data-debug-config'] = esc_attr(json_encode($debug_config));
    }
    
    return $attributes;
}

// Convert attributes array to HTML attributes string
function EXPLOREXR_generate_attributes_html($attributes) {
    $html = '';
    
    foreach ($attributes as $key => $value) {
        // Annotations are not available in the Free version
        if ($key === 'annotations') {
            continue;
        }
        
        // Skip internal flags that shouldn't appear in the HTML
        if ($key === 'no-camera-controls' || $key === 'no-auto-rotate') {
            continue;
        }
        
        // Skip internal container dimension attributes (used by wrapper, not model-viewer)
        if ($key === '_container_width' || $key === '_container_height') {
            continue;
        }
        
        // PHASE 2 FIX: Proper boolean attribute handling
        // Boolean attributes should be rendered without values (e.g., autoplay, loop, camera-controls)
        // Only treat empty string or boolean true as boolean — numeric values like '1'
        // must preserve their value (e.g., orbit-sensitivity="1", zoom-sensitivity="1")
        $is_boolean_attr = ($value === '' || $value === true);

        // IMPORTANT: Never treat data-* attributes as boolean; they must preserve their actual values
        $is_data_attribute = (strpos($key, 'data-') === 0);
        
        if ($is_boolean_attr && !$is_data_attribute) {
            // For boolean attributes - just output the key name
            $html .= ' ' . esc_attr($key);
        } else {
            // For attributes with values
            $html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }
    
    return $html;
}

// Register a shortcode to display 3D models
add_shortcode('explorexr_model', function ($atts) {
    // Include helper functions when needed
    if (file_exists(EXPLOREXR_PLUGIN_DIR . 'includes/models/model-helper.php')) {
        require_once EXPLOREXR_PLUGIN_DIR . 'includes/models/model-helper.php';
    }
    
    $atts = shortcode_atts(['id' => ''], $atts, 'explorexr_model');
    $model_id = intval($atts['id']);
    
    if (!$model_id) {
        return 'Invalid model ID.';
    }

    $model_file = get_post_meta($model_id, '_explorexr_model_file', true) ?: '';
    if (!$model_file) {
        return ' ExploreXR Alert: Model not found. Possibly abducted by polygons from another dimension.';
    }
    
    // Get alt text for accessibility
    $alt_text = get_post_meta($model_id, '_explorexr_model_alt_text', true) ?: '';
    if (empty($alt_text)) {
        $alt_text = get_the_title($model_id) . ' 3D Model';
    }
    
    // Get poster image if available
    $model_poster = get_post_meta($model_id, '_explorexr_model_poster', true) ?: '';
    $model_poster_id = get_post_meta($model_id, '_explorexr_model_poster_id', true) ?: '';

    // Get size settings
    $viewer_size = get_post_meta($model_id, '_explorexr_viewer_size', true) ?: '';
    $width = '100vw';
    $height = '500px';
    
    // Initialize responsive size variables
    $tablet_width = '';
    $tablet_height = '';
    $mobile_width = '';
    $mobile_height = '';

    // Apply preset sizes or custom dimensions
    // Note: model-viewer does not work with percentage (%) widths/heights because
    // its parent container may not have an explicit size. Use vw/vh instead.
    if ($viewer_size === 'small') {
        $width = '300px';
        $height = '300px';
        $tablet_width = '280px';
        $tablet_height = '280px';
        $mobile_width = '100vw';
        $mobile_height = '250px';
    } elseif ($viewer_size === 'medium') {
        $width = '500px';
        $height = '500px';
        $tablet_width = '400px';
        $tablet_height = '400px';
        $mobile_width = '100vw';
        $mobile_height = '350px';
    } elseif ($viewer_size === 'large') {
        $width = '800px';
        $height = '600px';
        $tablet_width = '600px';
        $tablet_height = '450px';
        $mobile_width = '100vw';
        $mobile_height = '400px';
    } elseif ($viewer_size === 'full') {
        $width = '100vw';
        $height = '90vh';
        $tablet_width = '100vw';
        $tablet_height = '70vh';
        $mobile_width = '100vw';
        $mobile_height = '60vh';
    } else {
        // Custom size — read from meta
        $custom_width = get_post_meta($model_id, '_explorexr_viewer_width', true) ?: '';
        $custom_height = get_post_meta($model_id, '_explorexr_viewer_height', true) ?: '';

        if (!empty($custom_width)) {
            $width = $custom_width;
        }

        if (!empty($custom_height)) {
            $height = $custom_height;
        }

        // Read custom responsive sizes from meta
        $tablet_width = get_post_meta($model_id, '_explorexr_tablet_viewer_width', true) ?: '';
        $tablet_height = get_post_meta($model_id, '_explorexr_tablet_viewer_height', true) ?: '';
        $mobile_width = get_post_meta($model_id, '_explorexr_mobile_viewer_width', true) ?: '';
        $mobile_height = get_post_meta($model_id, '_explorexr_mobile_viewer_height', true) ?: '';
    }

    // Convert any percentage (%) values to viewport units (vw/vh) since
    // model-viewer cannot resolve percentage-based dimensions reliably.
    $width = explorexr_premium_convert_percent_to_viewport($width, 'width');
    $height = explorexr_premium_convert_percent_to_viewport($height, 'height');
    $tablet_width = explorexr_premium_convert_percent_to_viewport($tablet_width, 'width');
    $tablet_height = explorexr_premium_convert_percent_to_viewport($tablet_height, 'height');
    $mobile_width = explorexr_premium_convert_percent_to_viewport($mobile_width, 'width');
    $mobile_height = explorexr_premium_convert_percent_to_viewport($mobile_height, 'height');
    
    // Generate unique CSS ID for this model instance
    $model_css_id = 'explorexr-model-' . $model_id . '-' . uniqid();
    
    // Generate responsive CSS if tablet or mobile sizes are set
    // Use wp_add_inline_style for proper page builder compatibility
    $responsive_css_content = '';
    
    // Desktop sizing — uses the unique instance ID so multiple models on one page
    // don't collide. No inline styles on the container; CSS is the sole authority.
    $responsive_css_content .= '#' . $model_css_id . ' { ';
    $responsive_css_content .= 'width: ' . esc_attr($width) . ' !important; ';
    $responsive_css_content .= 'height: ' . esc_attr($height) . ' !important; ';
    $responsive_css_content .= '} ';

    // Tablet styles (768px to 1024px)
    if (!empty($tablet_width) || !empty($tablet_height)) {
        $responsive_css_content .= '@media (min-width: 768px) and (max-width: 1024px) {';
        $responsive_css_content .= '#' . $model_css_id . ' {';
        if (!empty($tablet_width)) {
            $responsive_css_content .= 'width: ' . esc_attr($tablet_width) . ' !important; max-width: 100vw !important;';
        }
        if (!empty($tablet_height)) {
            $responsive_css_content .= 'height: ' . esc_attr($tablet_height) . ' !important; max-height: 100vh !important;';
        }
        $responsive_css_content .= '}';
        $responsive_css_content .= '}';
    }

    // Mobile styles (up to 767px)
    if (!empty($mobile_width) || !empty($mobile_height)) {
        $responsive_css_content .= '@media (max-width: 767px) {';
        $responsive_css_content .= '#' . $model_css_id . ' {';
        if (!empty($mobile_width)) {
            $responsive_css_content .= 'width: ' . esc_attr($mobile_width) . ' !important; max-width: 100vw !important;';
        }
        if (!empty($mobile_height)) {
            $responsive_css_content .= 'height: ' . esc_attr($mobile_height) . ' !important; max-height: 100vh !important;';
        }
        $responsive_css_content .= '}';
        $responsive_css_content .= '}';
    }
    
    // Use wp_add_inline_style for proper page builder compatibility
    if (!empty($responsive_css_content)) {
        // Create unique style handle per model instance
        $style_handle = 'explorexr-model-' . $model_id;
        
        // Ensure base model-viewer style is registered/enqueued
        if (!wp_style_is('explorexr-model-viewer', 'enqueued')) {
            wp_enqueue_style('explorexr-model-viewer', EXPLOREXR_PREMIUM_PLUGIN_URL . 'assets/css/model-viewer.css', array(), EXPLOREXR_PREMIUM_VERSION);
        }
        
        // Add inline styles to the base handle
        wp_add_inline_style('explorexr-model-viewer', $responsive_css_content);
    }
    
    // Annotations are not available in the free version
    $annotations = null;
    
    // Enqueue required scripts and styles
    wp_enqueue_style('explorexr-model-viewer', EXPLOREXR_PREMIUM_PLUGIN_URL . 'assets/css/model-viewer.css', array(), EXPLOREXR_PREMIUM_VERSION);

    // Trigger model-viewer script registration (addons handle their own assets)
    include EXPLOREXR_PLUGIN_DIR . 'template-parts/model-viewer-script.php';
    
    // Check if the model is a large file that needs special handling
    $large_model_handling = get_option('explorexr_large_model_handling', 'direct');
    $large_model_size_threshold = get_option('explorexr_large_model_size_threshold', 16);

    // Check file size if the file exists locally
    $is_large_model = false;
    $file_path = '';
      // Only run str_replace if $model_file is a string and constants are defined
    if (is_string($model_file) && defined('EXPLOREXR_MODELS_URL') && defined('EXPLOREXR_MODELS_DIR')) {
        $file_path = str_replace(EXPLOREXR_MODELS_URL, EXPLOREXR_MODELS_DIR, $model_file ?? '');

        if (file_exists($file_path)) {
            $file_size_mb = filesize($file_path) / (1024 * 1024); // Convert to MB
            if ($file_size_mb >= $large_model_size_threshold) {
                $is_large_model = true;
            }
        } elseif (!empty($model_file) && is_string($model_file) && strpos($model_file, 'http') === 0) {
            // For external files use a cached HEAD request so every page render
            // does not block on a synchronous HTTP round-trip.
            $transient_key = 'explorexr_fsize_' . md5($model_file);
            $cached_size   = get_transient($transient_key);
            if ($cached_size === false) {
                $request     = wp_remote_head($model_file, array('timeout' => 5));
                $cached_size = 0;
                if (!is_wp_error($request) && isset($request['headers']['content-length'])) {
                    $cached_size = intval($request['headers']['content-length']);
                }
                // Cache for 24 hours — external model files don't change frequently.
                set_transient($transient_key, $cached_size, DAY_IN_SECONDS);
            }
            $file_size_mb = $cached_size / (1024 * 1024);
            if ($file_size_mb >= $large_model_size_threshold) {
                $is_large_model = true;
            }
        }
    }

    // Per-model override. Allowed values: '' (use global) | 'direct' | 'poster_button' | 'lazy'.
    // Per-model override forces the behavior regardless of file size; global behavior only
    // activates when the file is actually larger than the configured threshold.
    $model_behavior_allowed = array('', 'direct', 'poster_button', 'lazy');
    $model_behavior         = get_post_meta($model_id, '_explorexr_premium_load_behavior', true);
    if (!in_array($model_behavior, $model_behavior_allowed, true)) {
        $model_behavior = '';
    }
    $per_model_override = ($model_behavior !== '');
    $effective_handling = $per_model_override ? $model_behavior : $large_model_handling;
    $should_apply       = $per_model_override || $is_large_model;

    // Only use poster_button mode if effective handling is poster_button AND we have a poster image
    // AND either the per-model override forces it OR the file is genuinely large.
    if ($should_apply && $effective_handling === 'poster_button' && !empty($model_poster)) {
        // Generate a unique ID for this model instance
        $model_instance_id = 'explorexr-model-' . $model_id . '-' . uniqid();
        
        // Enqueue the model loader script
        EXPLOREXR_enqueue_model_loader();
        
        // Build model attributes using our helper function
        $model_attributes = EXPLOREXR_build_model_attributes($model_id, $model_file, $alt_text, $width, $height, $model_poster);
        
        // Add unique CSS ID for responsive styling
        $model_attributes['id'] = $model_css_id;
        
        // Filter out any numeric keys that might have been added by filters
        $model_attributes = array_filter($model_attributes, function($key) {
            return is_string($key) && !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
        
        // Annotations are not available in the Free version
        // This feature is available in the Pro version only
        
        // CRITICAL FIX: Pass raw array to template - wp_json_encode will handle encoding
        // DO NOT pre-encode here or we get double-encoding bug
        // (Previously: json_encode + wp_json_encode = double encoding = attribute loss)
        
        // Load the large model template
        ob_start();
        include EXPLOREXR_PLUGIN_DIR . 'template-parts/large-model-template.php';
        $model_html = ob_get_clean();
        
        // Return the complete HTML (styles handled via wp_add_inline_style)
        return $model_html;
    } elseif ($should_apply && $effective_handling === 'lazy') {
        // Lazy load when visible - use Intersection Observer
        // Generate a unique ID for this model instance
        $model_instance_id = 'explorexr-model-' . $model_id . '-' . uniqid();
        
        // Enqueue the lazy loader script
        wp_enqueue_script('explorexr-lazy-loader', EXPLOREXR_PREMIUM_PLUGIN_URL . 'assets/js/lazy-loader.js', array(), EXPLOREXR_PREMIUM_VERSION, true);
        
        // Build model attributes using our helper function
        $model_attributes = EXPLOREXR_build_model_attributes($model_id, $model_file, $alt_text, $width, $height, $model_poster);
        
        // Add unique CSS ID for responsive styling
        $model_attributes['id'] = $model_css_id;
        
        // Filter out any numeric keys that might have been added by filters
        $model_attributes = array_filter($model_attributes, function($key) {
            return is_string($key) && !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
        
        // Load the lazy loading template
        ob_start();
        include EXPLOREXR_PLUGIN_DIR . 'template-parts/lazy-load-template.php';
        $model_html = ob_get_clean();
        
        // Return the complete HTML (styles handled via wp_add_inline_style)
        return $model_html;
    } else {
        // Regular loading approach for smaller models or if set to direct loading
        // Build model attributes using our helper function
        $model_attributes = EXPLOREXR_build_model_attributes($model_id, $model_file, $alt_text, $width, $height, $model_poster);
        
        // Add class for our wrapper script and unique CSS ID for responsive styling
        $model_attributes['class'] = 'explorexr-model';
        $model_attributes['id'] = $model_css_id;
        
        // Filter out any numeric keys that might have been added by filters
        $model_attributes = array_filter($model_attributes, function($key) {
            return is_string($key) && !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
        
        // Convert attributes to HTML string
        $attributes_html = EXPLOREXR_generate_attributes_html($model_attributes);
        
        // Annotations are not available in the Free version
        $annotations_html = '';
        
        // Load the standard model template
        ob_start();
        include EXPLOREXR_PLUGIN_DIR . 'template-parts/standard-model-template.php';
        $model_html = ob_get_clean();
        
        // Return the complete HTML (styles handled via wp_add_inline_style)
        return $model_html;
    }
});

// Add user-friendly shortcode aliases
add_shortcode('explorexr', function ($atts) {
    return do_shortcode('[explorexr_model id="' . (isset($atts['id']) ? $atts['id'] : '') . '"]');
});

// Legacy alias kept so content authored against the Premium plugin keeps rendering.
add_shortcode('explorexr-premium', function ($atts) {
    return do_shortcode('[explorexr_model id="' . (isset($atts['id']) ? $atts['id'] : '') . '"]');
});

// Legacy uppercase alias for backwards compatibility
add_shortcode('EXPLOREXR_model', function ($atts) {
    return do_shortcode('[explorexr_model id="' . (isset($atts['id']) ? $atts['id'] : '') . '"]');
});

// Enqueue admin scripts
add_action('admin_enqueue_scripts', function ($hook) {
    // Add null check to prevent deprecated warning in PHP 8.1+
    if (!empty($hook) && is_string($hook) && strpos($hook, 'explorexr') !== false) {
        // Add the script directly in admin
        wp_enqueue_script('explorexr-model-loader', EXPLOREXR_PREMIUM_PLUGIN_URL . 'assets/js/model-loader.js', array('jquery'), EXPLOREXR_PREMIUM_VERSION, true);
    }
});

/**
 * FIX 3: Shared renderer function for admin previews
 * 
 * Renders a model-viewer element with all filters applied.
 * Use this in admin metaboxes instead of hardcoded HTML.
 * 
 * @param int $model_id The model post ID
 * @param string $context 'admin' or 'frontend'
 * @param array $overrides Optional attribute overrides (e.g., ['style' => 'height:300px'])
 * @return string HTML for model-viewer element
 */
function explorexr_render_model_viewer($model_id, $context = 'admin', $overrides = array()) {
    // Get model file and metadata
    $model_file = get_post_meta($model_id, '_explorexr_model_file', true);
    if (empty($model_file)) {
        return '<div class="explorexr-error">Model file not found</div>';
    }
    
    $alt_text = get_post_meta($model_id, '_explorexr_alt_text', true) ?: get_the_title($model_id);
    $model_poster = get_post_meta($model_id, '_explorexr_model_poster', true) ?: '';
    
    // Default dimensions for admin previews
    $default_width = ($context === 'admin') ? '100%' : '600px';
    $default_height = ($context === 'admin') ? '400px' : '600px';
    
    // Build attributes using standard function
    $attributes = EXPLOREXR_build_model_attributes(
        $model_id,
        $model_file,
        $alt_text,
        $default_width,
        $default_height,
        $model_poster
    );
    
    // Apply overrides
    $attributes = array_merge($attributes, $overrides);
    
    // PHASE 3 FIX: Force camera-controls in admin context
    // Only apply if we're ACTUALLY in admin backend (not ACF/Elementor frontend previews)
    if ($context === 'admin' && is_admin() && !isset($attributes['camera-controls'])) {
        $attributes['camera-controls'] = ''; // Boolean attribute
    }
    
    // Generate HTML
    $html = '<model-viewer';
    foreach ($attributes as $key => $value) {
        if ($value === '' && in_array($key, ['camera-controls', 'autoplay', 'loop', 'auto-rotate'], true)) {
            // Boolean attributes
            $html .= ' ' . esc_attr($key);
        } else {
            $html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }
    $html .= '></model-viewer>';
    
    return $html;
}
