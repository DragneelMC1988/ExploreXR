<?php
/**
 * ExploreXR Form Submission Handler
 *
 * Handles form submissions for the ExploreXR plugin, ensuring proper data validation,
 * sanitization, and preservation of settings between different sections.
 *
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Process form submission data with enhanced field handling
 * 
 * @param array $post_data The POST data from the form
 * @param int $post_id Post ID (if editing a post)
 * @param bool $edit_mode Whether we're in edit mode or not
 * @return array Processed form data
 */
function ExploreXR_process_form_submission($post_data, $post_id = 0, $edit_mode = false) {
    // Default result array
    $result = array(
        'success' => true,
        'message' => __('Form data processed successfully.', 'explorexr'),
        'data' => array()
    );
    // Get all hidden state fields
    $state_fields = array();
    foreach ($post_data as $key => $value) {
        // Make sure $key is a string before using strpos
        if (is_string($key) && strpos($key, '_state') !== false) {
            $original_key = str_replace('_state', '', $key ?? '');
            $state_fields[$original_key] = $value;
        }
    }
    

    
    // Process viewer size fields
    if (isset($post_data)) {
        
        // Check for state fields and ensure they're processed
        foreach ($state_fields as $original_key => $value) {
            if (!isset($post_data[$original_key]) && $value) {
                $post_data[$original_key] = $value;
                

            }
        }
        
        // Special handling for viewer size
        
        // Handle viewer_size_value (custom dimensions)
        if (isset($post_data['viewer_size_value']) && !empty($post_data['viewer_size_value'])) {
            // Use a custom size
            $post_data['viewer_size'] = $post_data['viewer_size_value'];
            

        }
        // Handle viewer_size_preset (preset size)
        elseif (isset($post_data['viewer_size_preset']) && !empty($post_data['viewer_size_preset'])) {
            // Use a preset size
            $post_data['viewer_size'] = $post_data['viewer_size_preset'];
            

        }
        // Handle if viewer_size is directly provided as an array
        elseif (isset($post_data['viewer_size']) && is_array($post_data['viewer_size'])) {
            // This might happen when loading settings from hidden fields
            // Just ensure it's a string
            $post_data['viewer_size'] = implode(',', $post_data['viewer_size']);
            

        }
        // Default fallback
        else {
            // Set default size if none specified
            $post_data['viewer_size'] = 'medium';
            

        }
        
        // Clean up size fields to prevent duplicate processing
        // We've already consolidated them into viewer_size
        unset($post_data['viewer_size_value']);
        unset($post_data['viewer_size_preset']);
        
        // Process checkbox fields which may not be present in POST data when unchecked
        $checkbox_fields = array(
            'camera_controls',
            'auto_rotate',
            'ar_enabled',
            // Animation features are not available in the Free version
            'show_progress',
            'show_loading_dots',
            'enable_draco',
            'debug_mode'
        );
        
        // Get any state data for checkboxes
        foreach ($checkbox_fields as $field) {
            $state_key = $field . '_state';
            
            // If a state exists but the checkbox is not in POST data
            if (isset($post_data[$state_key])) {
                // If checkbox was submitted, use its value
                if (isset($post_data[$field])) {
                    // Checkbox is present in the form submission
                } else {
                    // Checkbox was not submitted, which means it's unchecked
                    $post_data[$field] = 'off';
                }
                
                // Remove the state key to clean up data
                unset($post_data[$state_key]);
            }
        }
    }
    
    // Store processed data in the result
    $result['data'] = $post_data;
    
    return $result;
}

/**
 * Sanitize form input fields
 * 
 * @param array $data Form data to sanitize
 * @return array Sanitized form data
 */
function ExploreXR_sanitize_form_data($data) {
    $sanitized = array();
    
    // Ensure data is an array and not null
    if (!is_array($data)) {
        return $sanitized;
    }
    
    // Define sanitization rules for each field type
    foreach ($data as $key => $value) {
        // Ensure key is not null and is a string
        if (!is_string($key) || $key === null) {
            continue;
        }
        
        // Skip sanitizing state fields
        if (is_string($key) && strpos($key, '_state') !== false) {
            $sanitized[$key] = $value;
            continue;
        }
        
        // Text fields
        if (in_array($key, array('title', 'description', 'model_alt'))) {
            $sanitized[$key] = sanitize_text_field($value);
        }
        // URLs
        elseif (in_array($key, array('model_file', 'poster_image'))) {
            $sanitized[$key] = !empty($value) ? esc_url_raw($value) : '';
        }
        // Numeric fields
        elseif (in_array($key, array('rotation_speed'))) {
            $sanitized[$key] = is_numeric($value) ? floatval($value) : 0;
        }
        // Checkbox fields (on/off)
        elseif (in_array($key, array('camera_controls', 'auto_rotate', 'ar_enabled'))) {
            $sanitized[$key] = ($value === 'on' || $value === '1' || $value === 'true') ? 'on' : 'off';
        }
        // Color fields
        elseif (in_array($key, array('background_color', 'loading_color'))) {
            $sanitized[$key] = ExploreXR_sanitize_hex_color($value);
        }
        // Select fields (specific options)
        elseif ($key === 'viewer_size') {
            $allowed = array('small', 'medium', 'large', 'custom');
            $sanitized[$key] = in_array($value, $allowed) ? $value : 'medium';
        }
        // Custom dimensions (custom size)
        elseif ($key === 'custom_width' || $key === 'custom_height') {
            // Allow CSS units and numeric values
            $sanitized[$key] = preg_replace('/[^0-9a-z%\.\-]/i', '', $value);
        }
        // Default fallback
        else {
            $sanitized[$key] = sanitize_text_field($value);
        }
    }
    
    return $sanitized;
}

/**
 * Create an empty hidden field to preserve state between form reloads
 * 
 * @param string $field_name The base name of the field
 * @param string $value The current value to preserve
 * @return string HTML for hidden state field
 */
function ExploreXR_create_state_field($field_name, $value = '') {
    return '<input type="hidden" name="' . esc_attr($field_name) . '_state" value="' . esc_attr($value) . '">';
}






