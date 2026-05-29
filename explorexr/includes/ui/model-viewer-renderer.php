<?php
/**
 * Model Viewer Renderer
 * 
 * Centralized rendering function for model-viewer elements across all addons
 * Ensures consistent attribute application and filter support
 *
 * @package ExploreXR Premium
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render a model-viewer element with standardized attributes
 * 
 * @param int $model_id The model post ID
 * @param array $custom_attrs Custom attributes to merge with defaults
 * @param bool $apply_addon_filters Whether to apply addon attribute filters (default true)
 * @return void Echoes the model-viewer HTML
 */
function explorexr_premium_render_model_viewer($model_id, $custom_attrs = array(), $apply_addon_filters = true) {
    // Get model file URL
    $model_file = get_post_meta($model_id, '_explorexr_model_file', true);
    
    if (empty($model_file)) {
        echo '<!-- No model file available -->';
        return;
    }
    
    // Default attributes
    $default_attrs = array(
        'src'             => esc_url($model_file),
        'alt'             => get_the_title($model_id) . ' 3D Model',
        'camera-controls' => '',
        'class'           => 'explorexr-model-viewer',
        // Ensure visibility even if parent lacks height; width stays fluid, height fixed
        'style'           => 'width:100%;height:520px;background:#f5f5f5;'
    );
    
    // Merge custom attributes with defaults
    $attributes = array_merge($default_attrs, $custom_attrs);
    
    // Apply filters to allow addons to modify attributes
    if ($apply_addon_filters) {
        $attributes = apply_filters('explorexr_premium_model_viewer_attributes', $attributes, $model_id);
    }
    
    // Build attribute string
    $attr_string = '';
    foreach ($attributes as $key => $value) {
        // Handle boolean attributes (like 'camera-controls', 'autoplay', 'loop')
        $is_data_attribute = (strpos($key, 'data-') === 0);
        $is_boolean_attr = ($value === '' || $value === 'true' || $value === true || $value === 1 || $value === '1');
        if ($is_boolean_attr && !$is_data_attribute) {
            $attr_string .= ' ' . esc_attr($key);
        }
        // Handle style attribute separately to preserve formatting
        elseif ($key === 'style') {
            $attr_string .= ' style="' . esc_attr($value) . '"';
        }
        // Handle class attribute
        elseif ($key === 'class') {
            $attr_string .= ' class="' . esc_attr($value) . '"';
        }
        // Skip null or false values
        elseif ($value !== null && $value !== false && $value !== '') {
            $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }
    
    // Output the model-viewer element
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes are escaped individually in loop above
    echo '<model-viewer' . $attr_string . '></model-viewer>';
}

/**
 * Get model viewer attributes for a specific model
 * Useful for getting attributes without rendering
 * 
 * @param int $model_id The model post ID
 * @param array $custom_attrs Custom attributes to merge
 * @return array Filtered attributes array
 */
function explorexr_premium_get_model_viewer_attributes($model_id, $custom_attrs = array()) {
    $model_file = get_post_meta($model_id, '_explorexr_model_file', true);
    
    if (empty($model_file)) {
        return array();
    }
    
    $default_attrs = array(
        'src' => esc_url($model_file),
        'alt' => get_the_title($model_id) . ' 3D Model',
        'camera-controls' => 'true',
        'class' => 'explorexr-model-viewer',
        'style' => 'width: 100%; height: 500px;'
    );
    
    $attributes = array_merge($default_attrs, $custom_attrs);
    $attributes = apply_filters('explorexr_premium_model_viewer_attributes', $attributes, $model_id);
    
    return $attributes;
}

/**
 * Build attribute string from array
 * Helper function to convert attribute array to HTML string
 * 
 * @param array $attributes Attributes array
 * @return string HTML attribute string
 */
function explorexr_premium_build_attribute_string($attributes) {
    $attr_string = '';
    
    foreach ($attributes as $key => $value) {
        $is_data_attribute = (strpos($key, 'data-') === 0);
        $is_boolean_attr = ($value === '' || $value === 'true' || $value === true || $value === 1 || $value === '1');
        if ($is_boolean_attr && !$is_data_attribute) {
            $attr_string .= ' ' . esc_attr($key);
        } elseif ($key === 'style') {
            $attr_string .= ' style="' . esc_attr($value) . '"';
        } elseif ($key === 'class') {
            $attr_string .= ' class="' . esc_attr($value) . '"';
        } elseif ($value !== null && $value !== false && $value !== '') {
            $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }
    
    return $attr_string;
}
