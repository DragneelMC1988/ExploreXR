<?php
/**
 * Helper functions for handling 3D models
 * 
  * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compatibility function for file validation
 * Premium version has advanced validation, free version does basic checks in explorexr_handle_model_upload()
 * 
 * @param array $file The uploaded file data
 * @return array|WP_Error File data or error
 */
function explorexr_validate_model_file_upload($file) {
    // Basic checks - full validation happens in explorexr_handle_model_upload()
    if (empty($file) || !isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return new WP_Error('no_file', __('No file was uploaded.', 'explorexr'));
    }
    
    if (!current_user_can('upload_files')) {
        return new WP_Error('permission_denied', __('You do not have permission to upload files.', 'explorexr'));
    }
    
    // Return file as-is for explorexr_handle_model_upload() to process
    return $file;
}

/**
 * Handle model file upload
 * 
 * @param array $file The uploaded file data
 * @return array|bool Array of file data on success, false on failure
 */
function explorexr_handle_model_upload($file) {
    // Allowed file types
    $allowed_types = array(
        'model/gltf-binary' => 'glb',
        'model/gltf+json' => 'gltf',
        'application/octet-stream' => 'glb',
        'text/plain' => 'gltf'
    );
    
    // Check if a valid file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
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
        return false;
    }
    
    // Use the WordPress uploads models directory
    $models_dir = EXPLOREXR_MODELS_DIR;
    
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
        return false;
    }
    
    // Move to our models directory
    $new_file = $models_dir . $filename;
    if (!copy($upload_result['file'], $new_file)) {
        wp_delete_file($upload_result['file']); // Clean up temp file
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
    }
    
    // Return the model data using plugin-defined constants
    $file_url = EXPLOREXR_MODELS_URL . $filename;
    
    return array(
        'file_path' => $new_file,
        'file_url' => $file_url,
        'file_name' => $filename,
        'file_type' => $file_ext
    );
}

