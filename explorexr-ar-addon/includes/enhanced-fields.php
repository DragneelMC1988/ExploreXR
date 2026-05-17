<?php
/**
 * Enhanced Field Rendering for AR Addon
 *
 * Provides specialized field rendering for AR addon settings
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register field type handlers for the AR addon
 */
function explorexr_premium_ar_register_field_type_handlers() {
    add_filter('explorexr_premium_addon_field_renderer', 'explorexr_premium_ar_render_enhanced_fields', 10, 5);
}
add_action('init', 'explorexr_premium_ar_register_field_type_handlers');

/**
 * Enhanced field renderer for AR addon settings
 * 
 * @param string $output Current field output
 * @param string $option_key Option key
 * @param mixed $default_value Default value
 * @param mixed $option_value Current option value
 * @param string $field_name Field name
 * @return string Enhanced field output
 */
function explorexr_premium_ar_render_enhanced_fields($output, $option_key, $default_value, $option_value, $field_name) {
    // Only handle our addon's fields
    if (strpos($field_name, 'explorexr_premium_addon_settings[explorexr-premium-ar]') !== 0) {
        return $output;
    }
    
    // Generate custom field output based on the option key
    $custom_output = '';
    
    // Handle enabled field as a checkbox
    if ($option_key === 'explorexr_premium_ar_enabled') {
        $checked = !empty($option_value);
        $custom_output = '<label class="explorexr-premium-checkbox-label">
            <input type="checkbox" name="' . esc_attr($field_name) . '" value="1" ' . checked($checked, true, false) . '>
            <span>' . esc_html__('Enable AR Mode', 'explorexr-ar-addon') . '</span>
        </label>
        <p class="description">' . esc_html__('When enabled, visitors can view this model in AR on supported devices.', 'explorexr-ar-addon') . '</p>';
    }
    
    // Handle AR modes as checkboxes
    elseif ($option_key === 'explorexr_premium_ar_modes') {
        $current_values = is_array($option_value) ? $option_value : array();
        $mode_options = array(
            'webxr' => __('WebXR (AR on web browsers)', 'explorexr-ar-addon'),
            'scene-viewer' => __('Scene Viewer (Android)', 'explorexr-ar-addon'),
            'quick-look' => __('Quick Look (iOS)', 'explorexr-ar-addon')
        );
        
        $custom_output = '<div class="explorexr-premium-checkbox-group">';
        foreach ($mode_options as $value => $label) {
            $checked = in_array($value, $current_values);
            $custom_output .= '<label class="explorexr-premium-checkbox-label">
                <input type="checkbox" name="' . esc_attr($field_name) . '[]" value="' . esc_attr($value) . '" ' . checked($checked, true, false) . '>
                <span>' . esc_html($label) . '</span>
            </label>';
        }
        $custom_output .= '</div>
        <p class="description">' . esc_html__('Choose which AR technologies to support. It\'s recommended to enable all for maximum compatibility.', 'explorexr-ar-addon') . '</p>';
    }
    
    // Handle AR scale as a dropdown
    elseif ($option_key === 'explorexr_premium_ar_scale') {
        $options = array(
            'auto' => __('Auto (default)', 'explorexr-ar-addon'),
            'fixed' => __('Fixed (use model\'s true size)', 'explorexr-ar-addon')
        );
        
        $custom_output = '<select name="' . esc_attr($field_name) . '">';
        foreach ($options as $value => $label) {
            $custom_output .= '<option value="' . esc_attr($value) . '" ' . selected($option_value, $value, false) . '>' . esc_html($label) . '</option>';
        }
        $custom_output .= '</select>
        <p class="description">' . esc_html__('Determines how the model is scaled in AR. \'Auto\' adjusts to reasonable size, \'Fixed\' uses the model\'s actual dimensions.', 'explorexr-ar-addon') . '</p>';
    }
    
    // Handle AR placement as a dropdown
    elseif ($option_key === 'explorexr_premium_ar_placement') {
        $options = array(
            'floor' => __('Floor (default)', 'explorexr-ar-addon'),
            'wall' => __('Wall', 'explorexr-ar-addon'),
            'ceiling' => __('Ceiling', 'explorexr-ar-addon')
        );
        
        $custom_output = '<select name="' . esc_attr($field_name) . '">';
        foreach ($options as $value => $label) {
            $custom_output .= '<option value="' . esc_attr($value) . '" ' . selected($option_value, $value, false) . '>' . esc_html($label) . '</option>';
        }
        $custom_output .= '</select>
        <p class="description">' . esc_html__('Where the model should be placed in the real world. Choose the option most appropriate for your model type.', 'explorexr-ar-addon') . '</p>';
    }
    
    // Handle button image with media uploader
    elseif ($option_key === 'explorexr_premium_ar_button_image') {
        $image_preview = !empty($option_value) ? '<div class="image-preview"><img src="' . esc_url($option_value) . '" alt="' . esc_html__('AR Button Preview', 'explorexr-ar-addon') . '" style="max-height: 50px;"></div>' : '';
        
        $custom_output = '<div class="image-upload-field">
            <input type="text" id="' . esc_attr($field_name) . '_field" name="' . esc_attr($field_name) . '" value="' . esc_attr($option_value) . '" class="regular-text" readonly>
            <button type="button" class="button ar-select-image" data-target="' . esc_attr($field_name) . '_field">' . esc_html__('Select Image', 'explorexr-ar-addon') . '</button>
            ' . ($option_value ? '<button type="button" class="button ar-remove-image" data-target="' . esc_attr($field_name) . '_field">' . esc_html__('Remove', 'explorexr-ar-addon') . '</button>' : '') . '
            <div class="image-preview-container">' . $image_preview . '</div>
        </div>
        <p class="description">' . esc_html__('Optional: Use a custom image for the AR button instead of text.', 'explorexr-ar-addon') . '</p>';
    }
    
    // Handle USDZ model with file select
    elseif ($option_key === 'explorexr_premium_ar_usdz_model') {
        $custom_output = '<div class="file-upload-field">
            <input type="text" id="' . esc_attr($field_name) . '_field" name="' . esc_attr($field_name) . '" value="' . esc_attr($option_value) . '" class="regular-text">
            <button type="button" class="button ar-select-file" data-target="' . esc_attr($field_name) . '_field">' . esc_html__('Select File', 'explorexr-ar-addon') . '</button>
        </div>
        <p class="description">' . esc_html__('For better iOS AR support, provide a USDZ version of your model. If not provided, the plugin will try to use the main model.', 'explorexr-ar-addon') . '</p>';
    }
    
    // Handle XR environment with file select
    elseif ($option_key === 'explorexr_premium_ar_xr_environment') {
        $custom_output = '<div class="file-upload-field">
            <input type="text" id="' . esc_attr($field_name) . '_field" name="' . esc_attr($field_name) . '" value="' . esc_attr($option_value) . '" class="regular-text">
            <button type="button" class="button ar-select-file" data-target="' . esc_attr($field_name) . '_field">' . esc_html__('Select File', 'explorexr-ar-addon') . '</button>
        </div>
        <p class="description">' . esc_html__('Optional: URL to an HDR environment map for lighting the model in AR. Leave empty for default lighting.', 'explorexr-ar-addon') . '</p>';
    }
    
    // Handle min height with validation
    elseif ($option_key === 'explorexr_premium_ar_min_height') {
        $custom_output = '<input type="text" name="' . esc_attr($field_name) . '" value="' . esc_attr($option_value) . '" class="small-text" placeholder="400px">
        <p class="description">' . esc_html__('Minimum height of the model viewer on mobile devices when in AR mode. (e.g., 400px)', 'explorexr-ar-addon') . '</p>';
    }
    
    // Return custom output if we generated one, otherwise fallback to default
    if (!empty($custom_output)) {
        return $custom_output;
    }
    
    return $output;
}

/**
 * Enqueue enhanced fields JavaScript
 */
function explorexr_premium_ar_enhanced_fields_js() {
    // Only load on model edit page
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'explorexr_premium_model') {
        return;
    }
    
    // Enqueue media scripts for file uploads
    wp_enqueue_media();
    
    // Enqueue enhanced fields script
    wp_enqueue_script(
        'explorexr-premium-ar-enhanced-fields',
        explorexr_premium_AR_PLUGIN_URL . 'assets/js/ar-enhanced-fields.js',
        array('jquery', 'wp-media-utils'),
        explorexr_premium_AR_VERSION,
        true
    );
    
    // Localize script with translations
    wp_localize_script('explorexr-premium-ar-enhanced-fields', 'explorexrAREnhanced', array(
        'selectImage' => __('Select or Upload AR Button Image', 'explorexr-ar-addon'),
        'useImage' => __('Use this image', 'explorexr-ar-addon'),
        'removeImage' => __('Remove', 'explorexr-ar-addon'),
        'selectFile' => __('Select or Upload File', 'explorexr-ar-addon'),
        'useFile' => __('Use this file', 'explorexr-ar-addon'),
        'modeRequired' => __('At least one AR mode must be selected.', 'explorexr-ar-addon'),
        'confirmRemove' => __('Are you sure you want to remove this file?', 'explorexr-ar-addon'),
        'uploadError' => __('Error uploading file. Please try again.', 'explorexr-ar-addon'),
        'selectingFile' => __('Selecting...', 'explorexr-ar-addon'),
        'buttonPreview' => __('AR Button Preview', 'explorexr-ar-addon'),
        'unsavedChanges' => __('You have unsaved changes. Are you sure you want to leave?', 'explorexr-ar-addon'),
        'webxrDesc' => __('AR support in web browsers that support WebXR', 'explorexr-ar-addon'),
        'sceneViewerDesc' => __('Android AR using Google Scene Viewer', 'explorexr-ar-addon'),
        'quickLookDesc' => __('iOS AR using Apple Quick Look', 'explorexr-ar-addon')
    ));
}
add_action('admin_enqueue_scripts', 'explorexr_premium_ar_enhanced_fields_js');

/**
 * Enqueue enhanced fields CSS
 */
function explorexr_premium_ar_enhanced_fields_css() {
    // Only load on model edit page
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'explorexr_premium_model') {
        return;
    }
    
    // Enqueue existing admin styles that now include enhanced field styles
    wp_enqueue_style(
        'explorexr-premium-ar-admin-enhanced-fields',
        explorexr_premium_AR_PLUGIN_URL . 'assets/css/ar-admin.css',
        array(),
        explorexr_premium_AR_VERSION
    );
}
add_action('admin_enqueue_scripts', 'explorexr_premium_ar_enhanced_fields_css');
