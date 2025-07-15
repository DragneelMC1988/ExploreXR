<?php
/**
 * Helper functions for handling 3D models
 * 
 * @package ExpoXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include debugging functions
require_once EXPOXR_PLUGIN_DIR . 'includes/utils/debugging.php';

/**
 * Handle model file upload
 * 
 * @param array $file The uploaded file data
 * @return array|bool Array of file data on success, false on failure
 */
function expoxr_handle_model_upload($file) {
    // Allowed file types
    $allowed_types = array(
        'model/gltf-binary' => 'glb',
        'model/gltf+json' => 'gltf',
        'application/octet-stream' => 'glb',
        'text/plain' => 'gltf'
    );
    
    // Check if a valid file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        if (get_option('expoxr_debug_mode', false)) {
            expoxr_log('ExpoXR: No file was uploaded or file is empty', 'warning');
        }
        return false;
    }
    
    // Make sure it's a valid MIME type or extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $valid_mime = false;
    
    // Check MIME type
    $mime_type = isset($file['type']) ? $file['type'] : '';
    
    // Check by MIME if available
    if (!empty($mime_type) && array_key_exists($mime_type, $allowed_types)) {
        $valid_mime = true;
    }
      // If MIME check failed, verify by extension (more reliable for 3D models)
    if (!$valid_mime && $file_ext && ($file_ext == 'glb' || $file_ext == 'gltf')) {
        $valid_mime = true;
    }
      if (!$valid_mime) {
        if (get_option('expoxr_debug_mode', false)) {
            expoxr_log('ExpoXR: Invalid file format. Only GLB and GLTF files are allowed. Received: ' . $mime_type . ' with extension ' . $file_ext, 'error');
        }
        return false;
    }
    
    // Use the plugin's defined models directory
    $models_dir = EXPOXR_MODELS_DIR;
    
    // Create models directory if it doesn't exist
    if (!file_exists($models_dir)) {
        wp_mkdir_p($models_dir);
    }
    
    // Secure the filename
    $filename = sanitize_file_name($file['name']);
    $filename = wp_unique_filename($models_dir, $filename);
    $new_file = $models_dir . $filename;
    
    // Move the file to our models directory using WordPress upload handling
    $upload_result = wp_handle_upload($file, array(
        'test_form' => false,
        'upload_error_handler' => 'wp_handle_upload_error'
    ));
    
    if (!$upload_result || isset($upload_result['error'])) {
        if (get_option('expoxr_debug_mode', false)) {
            expoxr_log('ExpoXR: Failed to move uploaded file: ' . (isset($upload_result['error']) ? $upload_result['error'] : 'Unknown error'), 'error');
        }
        return false;
    }
    
    // Move to our models directory
    $new_file = $models_dir . $filename;
    if (!copy($upload_result['file'], $new_file)) {
        wp_delete_file($upload_result['file']); // Clean up temp file
        if (get_option('expoxr_debug_mode', false)) {
            expoxr_log('ExpoXR: Failed to copy file to models directory: ' . $new_file, 'error');
        }
        return false;
    }
    
    // Clean up original upload
    wp_delete_file($upload_result['file']);
    
    // Set proper permissions using WP_Filesystem
    if (!function_exists('WP_Filesystem')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    WP_Filesystem();
    global $wp_filesystem;
    
    if ($wp_filesystem) {
        $wp_filesystem->chmod($new_file, 0644);
    } else {
        // Log error if WP_Filesystem is not available for chmod operation
        if (function_exists('error_log') && get_option('expoxr_debug_mode', false)) {
            expoxr_log('ExploreXR: WP_Filesystem not available for chmod operation', 'error');
        }
    }
    
    // Return the model data using plugin-defined constants
    $file_url = EXPOXR_MODELS_URL . $filename;
    
    // Debug log for successful upload
    if (get_option('expoxr_debug_mode', false)) {
        expoxr_log('ExpoXR: Successfully uploaded model file to: ' . $file_url);
    }
      return array(
        'file_path' => $new_file,
        'file_url' => $file_url,
        'file_name' => $filename,
        'file_type' => $file_ext
    );
}

/**
 * Handle USDZ file upload for AR support
 * 
 * @param array $file The uploaded file array from $_FILES
 * @param int $model_id The model ID to associate the USDZ file with
 * @return array|false Upload result array or false on failure
 */
function expoxr_handle_usdz_upload($file, $model_id = null) {
    // Check for upload errors first
    if (!isset($file['error']) || is_array($file['error'])) {
        if (get_option('expoxr_debug_mode', false)) {
            expoxr_log('ExpoXR: Invalid file parameters for USDZ upload', 'error');
        }
        return false;
    }
    
    // Check for upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            if (get_option('expoxr_debug_mode', false)) {
                expoxr_log('ExpoXR: No USDZ file was uploaded', 'warning');
            }
            return false;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            if (get_option('expoxr_debug_mode', false)) {
                expoxr_log('ExpoXR: USDZ file exceeded the upload size limit', 'error');
            }
            return false;
        default:
            if (get_option('expoxr_debug_mode', false)) {
                expoxr_log('ExpoXR: Unknown error during USDZ file upload', 'error');
            }
            return false;
    }
    
    // Validate file extension
    $file_info = pathinfo($file['name']);
    $file_ext = isset($file_info['extension']) ? strtolower($file_info['extension']) : '';
    
    if ($file_ext !== 'usdz') {
        if (get_option('expoxr_debug_mode', false)) {
            expoxr_log('ExpoXR: Invalid USDZ file extension: ' . $file_ext, 'error');
        }
        return false;
    }
    
    // Check file size limit (same as models)
    $max_size = get_option('expoxr_max_upload_size', 50) * 1024 * 1024; // Convert to bytes
    if ($file['size'] > $max_size) {
        if (get_option('expoxr_debug_mode', false)) {
            expoxr_log('ExpoXR: USDZ file size exceeds limit: ' . $file['size'], 'error');
        }
        return false;
    }
    
    // Create unique filename
    $filename = $file_info['filename'];
    if ($model_id) {
        $filename = 'model_' . $model_id . '_' . $filename;
    }
    $filename = sanitize_file_name($filename) . '.' . $file_ext;
    
    // Make sure filename is unique
    $counter = 1;
    $original_filename = $filename;
    while (file_exists(EXPOXR_MODELS_DIR . $filename)) {
        $filename = pathinfo($original_filename, PATHINFO_FILENAME) . '_' . $counter . '.' . $file_ext;
        $counter++;
    }
    
    // Move the uploaded file
    $new_file = EXPOXR_MODELS_DIR . $filename;
    
    // Ensure upload directory exists
    if (!file_exists(EXPOXR_MODELS_DIR)) {
        wp_mkdir_p(EXPOXR_MODELS_DIR);
    }
    
    // Move the uploaded file using WordPress upload handling
    $upload_result = wp_handle_upload($file, array(
        'test_form' => false,
        'upload_error_handler' => 'wp_handle_upload_error'
    ));
    
    if (!$upload_result || isset($upload_result['error'])) {
        if (get_option('expoxr_debug_mode', false)) {
            expoxr_log('ExpoXR: Failed to move uploaded USDZ file: ' . (isset($upload_result['error']) ? $upload_result['error'] : 'Unknown error'), 'error');
        }
        return false;
    }
    
    // Move to our models directory
    if (!copy($upload_result['file'], $new_file)) {
        wp_delete_file($upload_result['file']); // Clean up temp file
        if (get_option('expoxr_debug_mode', false)) {
            expoxr_log('ExpoXR: Failed to copy USDZ file to models directory: ' . $new_file, 'error');
        }
        return false;
    }
    
    // Clean up original upload
    wp_delete_file($upload_result['file']);
    
    // Set proper permissions using WP_Filesystem
    if (!function_exists('WP_Filesystem')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    WP_Filesystem();
    global $wp_filesystem;
    
    if ($wp_filesystem) {
        $wp_filesystem->chmod($new_file, 0644);
    } else {
        // Log error if WP_Filesystem is not available for chmod operation
        if (function_exists('error_log')) {
            expoxr_log('ExploreXR: WP_Filesystem not available for chmod operation', 'error');
        }
    }
    
    // Return the file data
    $file_url = EXPOXR_MODELS_URL . $filename;
    
    // Debug log for successful upload
    if (get_option('expoxr_debug_mode', false)) {
        expoxr_log('ExpoXR: Successfully uploaded USDZ file to: ' . $file_url);
    }
    
    return array(
        'file_path' => $new_file,
        'file_url' => $file_url,
        'file_name' => $filename,
        'file_type' => $file_ext
    );
}

/**
 * Get model data for a post
 * 
 * @param int $post_id The post ID
 * @return array Model data
 */
function expoxr_get_model_data($post_id) {
    $model_data = array(
        'model_file' => get_post_meta($post_id, '_expoxr_model_file', true),
        'model_name' => get_post_meta($post_id, '_expoxr_model_name', true),
        'model_alt_text' => get_post_meta($post_id, '_expoxr_model_alt_text', true),
        'poster' => get_post_meta($post_id, '_expoxr_model_poster', true),
        'poster_id' => get_post_meta($post_id, '_expoxr_model_poster_id', true),
        
        // Viewer size settings
        'viewer_size' => get_post_meta($post_id, '_expoxr_viewer_size', true),
        'viewer_width' => get_post_meta($post_id, '_expoxr_viewer_width', true),
        'viewer_height' => get_post_meta($post_id, '_expoxr_viewer_height', true),
        'tablet_viewer_width' => get_post_meta($post_id, '_expoxr_tablet_viewer_width', true),
        'tablet_viewer_height' => get_post_meta($post_id, '_expoxr_tablet_viewer_height', true),
        'mobile_viewer_width' => get_post_meta($post_id, '_expoxr_mobile_viewer_width', true),
        'mobile_viewer_height' => get_post_meta($post_id, '_expoxr_mobile_viewer_height', true),
        
        // Camera settings
        'camera_controls' => get_post_meta($post_id, '_expoxr_camera_controls', true),
        'disable_pan' => get_post_meta($post_id, '_expoxr_disable_pan', true),
        'disable_tap' => get_post_meta($post_id, '_expoxr_disable_tap', true),
        'disable_zoom' => get_post_meta($post_id, '_expoxr_disable_zoom', true),
        'touch_action' => get_post_meta($post_id, '_expoxr_touch_action', true),
        'orbit_sensitivity' => get_post_meta($post_id, '_expoxr_orbit_sensitivity', true),
        'zoom_sensitivity' => get_post_meta($post_id, '_expoxr_zoom_sensitivity', true),
        'pan_sensitivity' => get_post_meta($post_id, '_expoxr_pan_sensitivity', true),
        
        // Auto-rotate settings
        'auto_rotate' => get_post_meta($post_id, '_expoxr_auto_rotate', true),
        'auto_rotate_delay' => get_post_meta($post_id, '_expoxr_auto_rotate_delay', true),
        'rotation_per_second' => get_post_meta($post_id, '_expoxr_rotation_per_second', true),
        
        // Interaction prompt settings
        'interaction_prompt' => get_post_meta($post_id, '_expoxr_interaction_prompt', true),
        'interaction_prompt_style' => get_post_meta($post_id, '_expoxr_interaction_prompt_style', true),
        'interaction_prompt_threshold' => get_post_meta($post_id, '_expoxr_interaction_prompt_threshold', true),
        
        // Basic camera settings (advanced camera controls are not available in free version)
        'camera_orbit' => get_post_meta($post_id, '_expoxr_camera_orbit', true),
        'camera_target' => get_post_meta($post_id, '_expoxr_camera_target', true),
        'field_of_view' => get_post_meta($post_id, '_expoxr_field_of_view', true),
        'max_camera_orbit' => get_post_meta($post_id, '_expoxr_max_camera_orbit', true),
        'min_camera_orbit' => get_post_meta($post_id, '_expoxr_min_camera_orbit', true),
        'max_field_of_view' => get_post_meta($post_id, '_expoxr_max_field_of_view', true),
        'min_field_of_view' => get_post_meta($post_id, '_expoxr_min_field_of_view', true),
        'interpolation_decay' => get_post_meta($post_id, '_expoxr_interpolation_decay', true),
        
        // Animation settings are not available in the Free version
        // This feature is available in the Pro version only
        
        // AR settings
        // AR features are not available in the free version
        'ar_enabled' => false,
        'ar_modes' => '',
        'ar_scale' => '',
        'ar_placement' => '',
        'ar_usdz_model' => '',
        'ar_button_text' => '',
        'ar_button_image' => '',
        'ar_xr_environment' => '',
        'ar_min_height' => '',
        
        // Annotations are not available in the free version
        'annotations' => null
    );
    
    // Set defaults if values are empty
    if (empty($model_data['viewer_size'])) {
        $model_data['viewer_size'] = 'custom';
    }
    
    if (empty($model_data['camera_controls'])) {
        $model_data['camera_controls'] = 'off';
    }
    
    if (empty($model_data['auto_rotate'])) {
        $model_data['auto_rotate'] = 'off';
    }
    
    // Animation features are not available in the Free version
    // This feature is available in the Pro version only
    
    // For free version, disable premium features
    $model_data['ar_enabled'] = false;
    $model_data['ar_modes'] = '';
    $model_data['annotations'] = null;
    
    // Log model data when in debug mode
    if (get_option('expoxr_debug_mode', false)) {
        // Only log essential info to avoid huge logs
        $log_data = array(
            'model_file' => $model_data['model_file'],
            'camera_controls' => $model_data['camera_controls'],
            'auto_rotate' => $model_data['auto_rotate']
        );
        expoxr_log('ExpoXR: Retrieved model data for post #' . $post_id . ': ' . json_encode($log_data));
    }
    
    return $model_data;
}





