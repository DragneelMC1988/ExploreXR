<?php
/**
 * ExploreXR Core Model Validator
 * 
 * Validates model loading requirements and provides fallbacks
 * when addons            retu            return array(
                'accessible' => false, 
                'error' => 'Relative model file path not found',
                'attempted_path' => $file_path
            );ray(
                'accessible' => false, 
                'error' => 'Remote model file returned HTTP ' . $response_code,
                'response_code' => $response_code
            );not available
 * 
  * @package ExploreXR
 * @since 0.2.7.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validate model loading environment
 */
function explorexr_validate_model_environment($model_id) {
    $validation_result = array(
        'valid' => true,
        'errors' => array(),
        'warnings' => array(),
        'fallbacks_applied' => array()
    );
    
    // Check if model ID exists
    if (!$model_id || !get_post($model_id)) {
        $validation_result['valid'] = false;
        $validation_result['errors'][] = 'The 3D model could not be found. Please verify the model ID is correct.';
        return $validation_result;
    }
    
    // Check model file
    $model_file = get_post_meta($model_id, '_explorexr_model_file', true) ?: '';
    if (empty($model_file)) {
        $validation_result['valid'] = false;
        $validation_result['errors'][] = 'No 3D model file has been uploaded for this model. Please add a model file.';
        return $validation_result;
    }
    
    // Check if model file is accessible
    $file_check = explorexr_check_model_file_access($model_file);
    if (!$file_check['accessible']) {
        $validation_result['valid'] = false;
        $validation_result['errors'][] = 'The 3D model file is currently unavailable. Please try refreshing the page or contact support if this continues.';
        
        return $validation_result;
    }
    
    // Check required scripts and styles
    $script_checks = explorexr_check_required_assets();
    if (!$script_checks['valid']) {
        $validation_result['warnings'] = array_merge($validation_result['warnings'], $script_checks['warnings']);
    }
    
    return $validation_result;
}

/**
 * Check model file accessibility
 */
function explorexr_check_model_file_access($model_file) {
    if (empty($model_file) || !is_string($model_file)) {
        return array('accessible' => false, 'error' => 'No model file path provided');
    }
    
    // Check if it's a plugin file first (most specific)
    if (strpos($model_file, EXPLOREXR_PLUGIN_URL) === 0) {
        // Convert plugin URL to file path
        $file_path = str_replace(EXPLOREXR_PLUGIN_URL, EXPLOREXR_PLUGIN_DIR, $model_file);
        
        if (file_exists($file_path)) {
            return array('accessible' => true, 'file_path' => $file_path);
        } else {
            return array(
                'accessible' => false, 
                'error' => 'Plugin model file missing',
                'attempted_path' => $file_path
            );
        }
    }
    // Check if it's a local file (home or site URL)
    elseif (strpos($model_file, home_url()) === 0 || strpos($model_file, site_url()) === 0) {
        // Convert URL to file path
        $file_path = str_replace(array(home_url(), site_url()), ABSPATH, $model_file);
        
        if (file_exists($file_path)) {
            return array('accessible' => true, 'file_path' => $file_path);
        } else {
            return array(
                'accessible' => false, 
                'error' => 'Local model file missing',
                'attempted_path' => $file_path
            );
        }
    } elseif (strpos($model_file, 'http') === 0) {
        // External file - try HEAD request with short timeout
        $response = wp_remote_head($model_file, array('timeout' => 3));
        
        if (is_wp_error($response)) {
            return array(
                'accessible' => false, 
                'error' => 'Unable to access the 3D model file. Please check the file URL and try again. Error: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code === 200) {
            return array('accessible' => true, 'response_code' => $response_code);
        } else {
            return array(
                'accessible' => false,
                'error' => 'Unable to access the 3D model file. The server returned an error code: ' . $response_code,
                'response_code' => $response_code
            );
        }
    } else {
        // Relative path - convert to absolute
        $file_path = ABSPATH . ltrim($model_file, '/');
        if (file_exists($file_path)) {
            return array('accessible' => true, 'file_path' => $file_path);
        } else {
            return array(
                'accessible' => false,
                'error' => 'Relative path not found',
                'attempted_path' => $file_path
            );
        }
    }
}

/**
 * Check required assets (scripts and styles)
 */
function explorexr_check_required_assets() {
    $result = array(
        'valid' => true,
        'warnings' => array()
    );
    
    // Check if model-viewer script is available
    $model_viewer_script = EXPLOREXR_PLUGIN_DIR . 'assets/js/model-loader.js';
    if (!file_exists($model_viewer_script)) {
        $result['warnings'][] = 'Model loader script not found';
    }
    
    // Check if CSS is available
    $model_viewer_css = EXPLOREXR_PLUGIN_DIR . 'assets/css/model-viewer.css';
    if (!file_exists($model_viewer_css)) {
        $result['warnings'][] = 'Model viewer CSS not found';
    }
    
    // Check if template parts exist
    $script_template = EXPLOREXR_PLUGIN_DIR . 'template-parts/model-viewer-script.php';
    if (!file_exists($script_template)) {
        $result['warnings'][] = 'Model viewer script template not found';
    }
    
    return $result;
}

/**
 * Generate error message for model loading failures
 */
function explorexr_generate_model_error_message($validation_result) {
    if ($validation_result['valid']) {
        return null; // No error
    }
    
    $error_html = '<div class="explorexr-model-error" style="padding: 20px; border: 2px solid #dc3232; background: #fff; text-align: center; font-family: Arial, sans-serif;">';
    $error_html .= '<div class="error-icon" style="font-size: 48px; color: #dc3232; margin-bottom: 10px;">⚠️</div>';
    $error_html .= '<h4 style="color: #dc3232; margin: 10px 0;">3D Model Unavailable</h4>';
    
    // Primary error message
    if (!empty($validation_result['errors'])) {
        $error_html .= '<p style="margin: 10px 0;">' . esc_html($validation_result['errors'][0]) . '</p>';
    }
    
    // Troubleshooting steps
    $error_html .= '<div style="margin-top: 15px; text-align: left; background: #f9f9f9; padding: 10px; border-radius: 4px;">';
    $error_html .= '<strong>What you can try:</strong><br>';
    $error_html .= '• Refresh the page and try again<br>';
    $error_html .= '• Check your internet connection<br>';
    $error_html .= '• Contact support if the problem persists<br>';
    
    // Add specific guidance based on error type
    if (!empty($validation_result['errors'])) {
        $error = $validation_result['errors'][0];
        if (strpos($error, 'unavailable') !== false) {
            $error_html .= '• Try again in a few minutes<br>';
        } elseif (strpos($error, 'could not be found') !== false) {
            $error_html .= '• Verify you have the correct link or page<br>';
        }
    }
    
    $error_html .= '</div>';
    
    // Admin-only debugging info
    if (current_user_can('manage_options') && !empty($validation_result['warnings'])) {
        $error_html .= '<div style="margin-top: 10px; text-align: left; background: #fff3cd; padding: 8px; border-radius: 4px; font-size: 12px;">';
        $error_html .= '<strong>Admin Debug Info:</strong><br>';
        foreach ($validation_result['warnings'] as $warning) {
            $error_html .= '• ' . esc_html($warning) . '<br>';
        }
        $error_html .= '</div>';
    }
    
    $error_html .= '</div>';
    
    return $error_html;
}

/**
 * Enhanced model shortcode with validation
 */
function explorexr_safe_model_shortcode($atts) {
    $atts = shortcode_atts(['id' => ''], $atts, 'explorexr_model');
    $model_id = intval($atts['id']);
    
    // Validate the model environment
    $validation = explorexr_validate_model_environment($model_id);
    
    // If validation fails, return error message
    if (!$validation['valid']) {
        return explorexr_generate_model_error_message($validation);
    }
    
    // If we get here, the model should load safely
    // Return to the normal shortcode processing
    return explorexr_process_validated_model($model_id, $validation);
}

/**
 * Process model after validation passes
 */
function explorexr_process_validated_model($model_id, $validation) {
    // Get the original shortcode function and call it
    // This is a safe fallback to the existing shortcode implementation
    
    // Include helper functions if not already loaded
    if (!function_exists('explorexr_process_annotations')) {
        if (file_exists(EXPLOREXR_PLUGIN_DIR . 'includes/models/model-helper.php')) {
            require_once EXPLOREXR_PLUGIN_DIR . 'includes/models/model-helper.php';
        }
    }
    
    // Call the original shortcode logic from shortcodes.php
    // We'll create a wrapper that handles the validation results
    $model_file = get_post_meta($model_id, '_explorexr_model_file', true) ?: '';
    
    // Get alt text for accessibility
    $alt_text = get_post_meta($model_id, '_explorexr_model_alt_text', true) ?: '';
    if (empty($alt_text)) {
        $alt_text = get_the_title($model_id) . ' 3D Model';
    }
    
    // Basic model viewer with minimal dependencies
    $width = get_post_meta($model_id, '_explorexr_viewer_width', true) ?: '100%';
    $height = get_post_meta($model_id, '_explorexr_viewer_height', true) ?: '500px';
    
    // Enqueue core styles
    wp_enqueue_style('explorexr-model-viewer', EXPLOREXR_PLUGIN_URL . 'assets/css/model-viewer.css', array(), EXPLOREXR_VERSION);
    
    // Build basic attributes
    $attributes = array(
        'src' => $model_file,
        'alt' => $alt_text,
        'style' => "width: {$width}; height: {$height};",
        'class' => 'explorexr-model-core'
    );
    
    // Only add features that don't require addons or are safely fallback
    $camera_controls = get_post_meta($model_id, '_explorexr_camera_controls', true) ?: '';
    if ($camera_controls === 'on') {
        $attributes['camera-controls'] = '';
    }
    
    $auto_rotate = get_post_meta($model_id, '_explorexr_auto_rotate', true) ?: '';
    if ($auto_rotate === 'on') {
        $attributes['auto-rotate'] = '';
    }
    
    // Convert attributes to HTML
    $attributes_html = '';
    foreach ($attributes as $key => $value) {
        if ($value === '') {
            $attributes_html .= ' ' . esc_attr($key);
        } else {
            $attributes_html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }
    
    // Include the script template
    ob_start();
    include EXPLOREXR_PLUGIN_DIR . 'template-parts/model-viewer-script.php';
    $script = ob_get_clean();
    
    // Return the model HTML
    $html = $script;
    $html .= '<model-viewer' . $attributes_html . '>';
    $html .= '<div slot="error" class="explorexr-model-error">';
    $html .= '<div>The 3D model is currently unavailable. Please try refreshing the page.</div>';
    $html .= '</div>';
    $html .= '</model-viewer>';
    
    return $html;
}





