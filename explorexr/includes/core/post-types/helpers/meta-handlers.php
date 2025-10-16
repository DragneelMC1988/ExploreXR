<?php
/**
 * Meta Handlers - Functions for managing post meta
 *
  * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Save all post meta for the 3D model
 *
 * @param int $post_id The ID of the post being saved
 * @return int|bool The post ID on success, false on failure
 */
function explorexr_save_all_post_meta($post_id) {
    // Don't process autosaves or revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    if (wp_is_post_revision($post_id)) {
        return $post_id;
    }
    
    // Check if we're working with the right post type
    if (get_post_type($post_id) !== 'explorexr_model') {
        return $post_id;
    }
    
    // Security check: verify nonce first before processing any POST data
    // Require nonce for ALL form submissions, not just edit mode
    if (!isset($_POST['explorexr_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['explorexr_nonce'])), 'explorexr_save_model')) {
        
        // Create a detailed debug log for nonce failures
        // WordPress.org compliance: Log specific sanitized keys instead of all $_POST keys
        $important_post_keys = array('post_title', 'explorexr_model_file', 'explorexr_model_name', 'viewer_size');
        $sanitized_post_keys = array();
        foreach ($important_post_keys as $key) {
            if (isset($_POST[$key])) {
                $sanitized_post_keys[] = $key;
            }
        }
        
        return false;
    }
    
    // Check if this is an edit mode submission (after nonce verification)
    $edit_mode = isset($_POST['explorexr_edit_mode']) ? true : false;
    


    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return false;
    }
      // Basic fields
    if (array_key_exists('explorexr_model_file', $_POST)) {
        update_post_meta($post_id, '_explorexr_model_file', sanitize_text_field(wp_unslash($_POST['explorexr_model_file'])));
    }
    
    if (array_key_exists('explorexr_model_name', $_POST)) {
        update_post_meta($post_id, '_explorexr_model_name', sanitize_text_field(wp_unslash($_POST['explorexr_model_name'])));
    }
    
    if (array_key_exists('explorexr_model_alt_text', $_POST)) {
        update_post_meta($post_id, '_explorexr_model_alt_text', sanitize_text_field(wp_unslash($_POST['explorexr_model_alt_text'])));
    }
    
    // Handle new model file upload
    if (isset($_FILES['explorexr_new_model']) && isset($_FILES['explorexr_new_model']['size']) && $_FILES['explorexr_new_model']['size'] > 0) {
        // Manually sanitize $_FILES data to avoid nonce verification warnings
        $file_upload = array(
            'name' => isset($_FILES['explorexr_new_model']['name']) ? sanitize_file_name(wp_unslash($_FILES['explorexr_new_model']['name'])) : '',
            'type' => isset($_FILES['explorexr_new_model']['type']) ? sanitize_mime_type(wp_unslash($_FILES['explorexr_new_model']['type'])) : '',
            'tmp_name' => isset($_FILES['explorexr_new_model']['tmp_name']) ? sanitize_text_field(wp_unslash($_FILES['explorexr_new_model']['tmp_name'])) : '',
            'error' => isset($_FILES['explorexr_new_model']['error']) ? absint($_FILES['explorexr_new_model']['error']) : UPLOAD_ERR_NO_FILE,
            'size' => isset($_FILES['explorexr_new_model']['size']) ? absint($_FILES['explorexr_new_model']['size']) : 0,
        );
        
        // Validate the sanitized file data
        $sanitized_file = explorexr_validate_model_file_upload($file_upload);
        
        if (is_wp_error($sanitized_file)) {
        } else {
            // Pass sanitized file to upload handler
            $upload_result = explorexr_handle_model_upload($sanitized_file);
        
            if ($upload_result && !empty($upload_result['file_url'])) {
                update_post_meta($post_id, '_explorexr_model_file', $upload_result['file_url']);
                
                // If no model name exists yet, set it from the filename
                $model_name = get_post_meta($post_id, '_explorexr_model_name', true) ?: '';
                if (empty($model_name)) {
                    $filename = basename($upload_result['file_url']);
                    $model_name = preg_replace('/\.[^.]+$/', '', $filename);
                    update_post_meta($post_id, '_explorexr_model_name', $model_name);
                }
                
                if ($edit_mode) {
                }
            } else {
                // Upload failed
                if ($edit_mode) {
                    // Log upload failure for debugging
                }
            }
        }
    }
    
    // Save size settings
    explorexr_save_size_settings($post_id);
    
    // Save poster settings
    explorexr_save_poster_settings($post_id);
    
    // Save camera and accessibility settings - basic functionality only
    // Camera settings - basic functionality only
    if (false) { // Premium camera features disabled in free version
        // First run debug functions if they exist

        
        // Then run the actual camera settings save
        explorexr_save_camera_settings($post_id, $edit_mode);
    }
    
    // Annotations and Animation functionality are not available in the Free version
    // These features are available in the Pro version only
    
    // AR functionality is not available in the Free version
    

    
    return true;
}

/**
 * Save size settings
 * 
 * @param int $post_id The post ID
 */
function explorexr_save_size_settings($post_id) {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (array_key_exists('viewer_size', $_POST)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        update_post_meta($post_id, '_explorexr_viewer_size', sanitize_text_field(wp_unslash($_POST['viewer_size'])));
    }
    
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (array_key_exists('viewer_width', $_POST)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        update_post_meta($post_id, '_explorexr_viewer_width', sanitize_text_field(wp_unslash($_POST['viewer_width'])));
    }
    
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (array_key_exists('viewer_height', $_POST)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        update_post_meta($post_id, '_explorexr_viewer_height', sanitize_text_field(wp_unslash($_POST['viewer_height'])));
    }
    
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (array_key_exists('tablet_viewer_width', $_POST)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        update_post_meta($post_id, '_explorexr_tablet_viewer_width', sanitize_text_field(wp_unslash($_POST['tablet_viewer_width'])));
    }
    
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (array_key_exists('tablet_viewer_height', $_POST)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        update_post_meta($post_id, '_explorexr_tablet_viewer_height', sanitize_text_field(wp_unslash($_POST['tablet_viewer_height'])));
    }
    
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (array_key_exists('mobile_viewer_width', $_POST)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        update_post_meta($post_id, '_explorexr_mobile_viewer_width', sanitize_text_field(wp_unslash($_POST['mobile_viewer_width'])));
    }
    
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (array_key_exists('mobile_viewer_height', $_POST)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        update_post_meta($post_id, '_explorexr_mobile_viewer_height', sanitize_text_field(wp_unslash($_POST['mobile_viewer_height'])));
    }
}

/**
 * Save poster settings
 * 
 * @param int $post_id The post ID
 */
function explorexr_save_poster_settings($post_id) {
    // Handle poster image
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (isset($_FILES['model_poster']) && isset($_FILES['model_poster']['size']) && $_FILES['model_poster']['size'] > 0) {
        // Use WordPress media handling for the image
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $poster_attachment_id = media_handle_upload('model_poster', $post_id);
        if (!is_wp_error($poster_attachment_id)) {
            $poster_url = wp_get_attachment_url($poster_attachment_id);
            update_post_meta($post_id, '_explorexr_model_poster', $poster_url);
            update_post_meta($post_id, '_explorexr_model_poster_id', $poster_attachment_id);
        }
    }
    
    // Handle removing poster if checkbox is checked
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (isset($_POST['remove_poster']) && sanitize_text_field(wp_unslash($_POST['remove_poster'])) == '1') {
        delete_post_meta($post_id, '_explorexr_model_poster');
        delete_post_meta($post_id, '_explorexr_model_poster_id');
    }
    
    // Handle poster from media library
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (isset($_POST['model_poster_id']) && !empty($_POST['model_poster_id'])) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        $poster_id = sanitize_text_field(wp_unslash($_POST['model_poster_id']));
        $poster_url = wp_get_attachment_url($poster_id);
        update_post_meta($post_id, '_explorexr_model_poster', $poster_url);
        update_post_meta($post_id, '_explorexr_model_poster_id', $poster_id);
    }
}

/**
 * Save camera settings
 * 
 * @param int $post_id The post ID
 * @param bool $edit_mode Whether edit mode is enabled
 */
/**
 * Save camera settings to post meta
 */
function explorexr_save_camera_settings($post_id, $edit_mode = false) {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (array_key_exists('explorexr_model_alt_text', $_POST)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        update_post_meta($post_id, '_explorexr_model_alt_text', sanitize_text_field(wp_unslash($_POST['explorexr_model_alt_text'])));
    }
    
    // Interaction controls - new enable interactions logic (same pattern as auto-rotate)
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    $enable_interactions = isset($_POST['explorexr_enable_interactions']) ? 'on' : 'off';
    update_post_meta($post_id, '_explorexr_enable_interactions', $enable_interactions);
    
    // Also update the disable_interactions field for backward compatibility
    $disable_interactions = ($enable_interactions === 'off') ? 'on' : 'off';
    update_post_meta($post_id, '_explorexr_disable_interactions', $disable_interactions);
    
    // Camera controls - for backwards compatibility 
    // Addon integration removed from free version
    $camera_controls = 'off';
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (isset($_POST['explorexr_camera_controls']) || (isset($_POST['explorexr_camera_controls_state']) && $_POST['explorexr_camera_controls_state'] === '1')) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        $camera_controls = 'on';
    }
    update_post_meta($post_id, '_explorexr_camera_controls', $camera_controls);
    

    
    // Auto-rotate settings - support both checkbox and state field
    $auto_rotate = 'off';
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (isset($_POST['explorexr_auto_rotate']) || (isset($_POST['explorexr_auto_rotate_state']) && $_POST['explorexr_auto_rotate_state'] === '1')) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        $auto_rotate = 'on';
    }
    update_post_meta($post_id, '_explorexr_auto_rotate', $auto_rotate);
    
    // Handle auto-rotate delay with validation
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (array_key_exists('explorexr_auto_rotate_delay', $_POST)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        $delay_value = sanitize_text_field(wp_unslash($_POST['explorexr_auto_rotate_delay']));
        // Ensure the delay is a numeric value
        if (is_numeric($delay_value)) {
            update_post_meta($post_id, '_explorexr_auto_rotate_delay', $delay_value);
        } else {
            // Use default if not numeric
            update_post_meta($post_id, '_explorexr_auto_rotate_delay', '5000');
        }
        

    }
    
    // Handle auto-rotate speed (map from form field to database field)
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (array_key_exists('explorexr_auto_rotate_speed', $_POST)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        $speed_value = sanitize_text_field(wp_unslash($_POST['explorexr_auto_rotate_speed']));
        update_post_meta($post_id, '_explorexr_rotation_per_second', $speed_value);
    }
    
    // Legacy field support
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
    if (array_key_exists('explorexr_rotation_per_second', $_POST)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        $speed_value = sanitize_text_field(wp_unslash($_POST['explorexr_rotation_per_second']));
        update_post_meta($post_id, '_explorexr_rotation_per_second', $speed_value);
    }
    
    // Camera settings - premium features are not available in the free version
    if (false) { // Premium camera features disabled in free version
        // Premium camera features are not available in the free version
    }
}

/**
 * Check if a premium feature is available
 *
 * @param string $feature_slug The feature slug
 * @param string $license_key The license key
 * @return bool Whether the feature is ready to use
 */
function explorexr_is_premium_feature_available_with_license($feature_slug, $license_key) {
    // Premium features are not available in the Free version
    return false;
}






