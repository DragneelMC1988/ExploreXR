<?php
/**
 * File Upload Sanitization and Validation Functions
 * 
 * This file provides secure file upload handling functions with proper
 * sanitization, validation, and security checks as required by WordPress.org
 * 
 * @package ExploreXR
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitize and validate file upload input
 * 
 * This function performs comprehensive validation of file uploads including:
 * - Permission checks
 * - Upload error detection
 * - File size validation
 * - MIME type verification
 * - File extension validation
 * - Security checks for malicious files
 * 
 * @param array $file The $_FILES array element to validate
 * @param array $args Optional arguments for validation
 *              - 'allowed_types' (array) Allowed MIME types
 *              - 'max_size' (int) Maximum file size in bytes
 *              - 'allowed_extensions' (array) Allowed file extensions
 * @return array|WP_Error Sanitized file data or WP_Error on failure
 */
function explorexr_sanitize_file_upload($file, $args = array()) {
    // Default arguments
    $defaults = array(
        'allowed_types' => array(
            'model/gltf-binary',
            'model/gltf+json',
            'application/octet-stream', // GLB files may report as this
        ),
        'max_size' => 104857600, // 100MB default
        'allowed_extensions' => array('glb', 'gltf'),
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Check if file exists
    if (empty($file) || !isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return new WP_Error(
            'no_file',
            __('No file was uploaded.', 'explorexr')
        );
    }
    
    // Verify user has permission to upload files
    if (!current_user_can('upload_files')) {
        return new WP_Error(
            'permission_denied',
            __('You do not have permission to upload files.', 'explorexr')
        );
    }
    
    // Check for upload errors
    if (!isset($file['error']) || is_array($file['error'])) {
        return new WP_Error(
            'invalid_file',
            __('Invalid file upload.', 'explorexr')
        );
    }
    
    // Handle specific upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return new WP_Error(
                'no_file',
                __('No file was uploaded.', 'explorexr')
            );
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return new WP_Error(
                'file_too_large',
                __('The uploaded file exceeds the maximum file size.', 'explorexr')
            );
        case UPLOAD_ERR_PARTIAL:
            return new WP_Error(
                'partial_upload',
                __('The file was only partially uploaded.', 'explorexr')
            );
        default:
            return new WP_Error(
                'upload_error',
                sprintf(
                    /* translators: %d: error code */
                    __('File upload failed with error code: %d', 'explorexr'),
                    $file['error']
                )
            );
    }
    
    // Validate file is actually uploaded
    if (!is_uploaded_file($file['tmp_name'])) {
        return new WP_Error(
            'security_error',
            __('Security check failed. File must be uploaded via HTTP POST.', 'explorexr')
        );
    }
    
    // Sanitize filename
    $filename = isset($file['name']) ? sanitize_file_name($file['name']) : '';
    
    if (empty($filename)) {
        return new WP_Error(
            'invalid_filename',
            __('Invalid filename.', 'explorexr')
        );
    }
    
    // Validate file size
    $file_size = isset($file['size']) ? absint($file['size']) : 0;
    
    if ($file_size === 0) {
        return new WP_Error(
            'empty_file',
            __('The uploaded file is empty.', 'explorexr')
        );
    }
    
    if ($file_size > $args['max_size']) {
        return new WP_Error(
            'file_too_large',
            sprintf(
                /* translators: %s: maximum file size */
                __('File size exceeds maximum allowed size of %s.', 'explorexr'),
                size_format($args['max_size'])
            )
        );
    }
    
    // Validate file type and extension using WordPress function
    $filetype = wp_check_filetype_and_ext($file['tmp_name'], $filename);
    
    if (!$filetype['ext'] || !$filetype['type']) {
        return new WP_Error(
            'invalid_file_type',
            __('Invalid file type. Only GLB and GLTF files are allowed.', 'explorexr')
        );
    }
    
    // Additional extension validation for 3D model files
    $file_ext = strtolower($filetype['ext']);
    
    if (!in_array($file_ext, $args['allowed_extensions'], true)) {
        return new WP_Error(
            'extension_not_allowed',
            sprintf(
                /* translators: %s: file extension */
                __('File extension "%s" is not allowed.', 'explorexr'),
                esc_html($file_ext)
            )
        );
    }
    
    // Verify MIME type if provided
    if (!empty($filetype['type'])) {
        $is_allowed_mime = false;
        
        // Check if MIME type is in allowed list
        foreach ($args['allowed_types'] as $allowed_type) {
            if ($filetype['type'] === $allowed_type) {
                $is_allowed_mime = true;
                break;
            }
        }
        
        // For GLB files, be more lenient as they often report as application/octet-stream
        if (!$is_allowed_mime && $file_ext === 'glb') {
            if (in_array($filetype['type'], array('application/octet-stream', 'model/gltf-binary'), true)) {
                $is_allowed_mime = true;
            }
        }
        
        if (!$is_allowed_mime) {
            return new WP_Error(
                'mime_type_not_allowed',
                sprintf(
                    /* translators: %s: MIME type */
                    __('MIME type "%s" is not allowed for 3D model files.', 'explorexr'),
                    esc_html($filetype['type'])
                )
            );
        }
    }
    
    // Additional security: Check for potential PHP code in the file
    // This is a basic check - more sophisticated scanning could be added
    $file_contents = file_get_contents($file['tmp_name'], false, null, 0, 1024);
    if ($file_contents === false) {
        return new WP_Error(
            'read_error',
            __('Unable to read uploaded file.', 'explorexr')
        );
    }
    
    // Check for PHP tags (basic security measure)
    if (!empty($file_contents) && (strpos($file_contents, '<?php') !== false || strpos($file_contents, '<?') !== false)) {
        return new WP_Error(
            'potential_malicious_file',
            __('File appears to contain executable code and has been rejected.', 'explorexr')
        );
    }
    
    // Return sanitized file data
    return array(
        'name' => $filename,
        'type' => $filetype['type'],
        'tmp_name' => $file['tmp_name'],
        'size' => $file_size,
        'ext' => $file_ext,
        'error' => 0,
    );
}

/**
 * Validate and sanitize model file upload with additional 3D model specific checks
 * 
 * @param array $file The $_FILES array element
 * @return array|WP_Error Sanitized file data or error
 */
function explorexr_validate_model_file_upload($file) {
    // Get plugin settings for max upload size
    $max_upload_mb = get_option('explorexr_max_upload_size', 100);
    $max_upload_bytes = $max_upload_mb * 1024 * 1024;
    
    // Prepare validation arguments
    $args = array(
        'allowed_types' => array(
            'model/gltf-binary',
            'model/gltf+json',
            'application/octet-stream',
        ),
        'max_size' => $max_upload_bytes,
        'allowed_extensions' => array('glb', 'gltf'),
    );
    
    // Sanitize and validate the file
    $result = explorexr_sanitize_file_upload($file, $args);
    
    // Additional validation for 3D models could be added here
    // For example: checking for valid GLTF/GLB structure
    
    return $result;
}

/**
 * Validate and sanitize USDZ file upload for AR support
 * 
 * @param array $file The $_FILES array element
 * @return array|WP_Error Sanitized file data or error
 */
function explorexr_validate_usdz_file_upload($file) {
    // Get plugin settings for max upload size
    $max_upload_mb = get_option('explorexr_max_upload_size', 100);
    $max_upload_bytes = $max_upload_mb * 1024 * 1024;
    
    // Prepare validation arguments for USDZ files
    $args = array(
        'allowed_types' => array(
            'model/vnd.usdz+zip',
            'application/octet-stream',
            'application/zip',
        ),
        'max_size' => $max_upload_bytes,
        'allowed_extensions' => array('usdz'),
    );
    
    // Sanitize and validate the file
    return explorexr_sanitize_file_upload($file, $args);
}

/**
 * Validate and sanitize poster/texture image file upload
 * 
 * @param array $file The $_FILES array element
 * @return array|WP_Error Sanitized file data or error
 */
function explorexr_validate_image_file_upload($file) {
    // Prepare validation arguments for image files
    $args = array(
        'allowed_types' => array(
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
        ),
        'max_size' => 10485760, // 10MB for images
        'allowed_extensions' => array('jpg', 'jpeg', 'png', 'gif', 'webp'),
    );
    
    // Sanitize and validate the file
    return explorexr_sanitize_file_upload($file, $args);
}

/**
 * Get human-readable upload error message
 * 
 * @param WP_Error $error The WP_Error object
 * @return string Error message
 */
function explorexr_get_file_upload_error_message($error) {
    if (!is_wp_error($error)) {
        return __('Unknown error occurred during file upload.', 'explorexr');
    }
    
    return $error->get_error_message();
}

/**
 * Check if file upload is valid before processing
 * 
 * This is a lightweight check to determine if a file upload should be processed
 * without performing the full validation.
 * 
 * @param array $file The $_FILES array element
 * @return bool True if file appears valid for processing
 */
function explorexr_should_process_file_upload($file) {
    // Check basic requirements
    if (empty($file) || !is_array($file)) {
        return false;
    }
    
    // Check if tmp_name exists and is not empty
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }
    
    // Check if size is greater than 0
    if (!isset($file['size']) || $file['size'] <= 0) {
        return false;
    }
    
    // Check for upload errors
    if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    return true;
}
