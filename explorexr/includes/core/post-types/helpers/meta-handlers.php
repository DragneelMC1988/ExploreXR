<?php
/**
 * Meta Handlers - Functions for managing post meta
 *
 * @package ExpoXR
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
function expoxr_save_all_post_meta($post_id) {
    // Don't process auto-saves or revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    if (wp_is_post_revision($post_id)) {
        return $post_id;
    }
    
    // Check if we're working with the right post type
    if (get_post_type($post_id) !== 'expoxr_model') {
        return $post_id;
    }
    
    // Check if this is an edit mode submission
    $edit_mode = isset($_POST['expoxr_edit_mode']) ? true : false;
    
    // Enable debug logging regardless of mode to help troubleshoot
    $debug_log = expoxr_debug_log_post_data($post_id);
    
    // Add extra logging for edit mode
    if ($edit_mode) {
        if (get_option('expoxr_debug_mode', false)) {
            error_log('ExpoXR: Processing edit mode submission for post ' . $post_id);
        }
        
        // Save diagnostic data if provided
        if (isset($_POST['expoxr_edit_diagnostic'])) {
            update_post_meta($post_id, '_expoxr_last_edit_diagnostic', $_POST['expoxr_edit_diagnostic']);
        }
    }
      // Security check: verify nonce if it exists
    // Note: We do this after initial logging so we can debug nonce failures better
    if (isset($_POST['expoxr_nonce'])) {
        $nonce_verified = wp_verify_nonce($_POST['expoxr_nonce'], 'expoxr_save_model');
        if (!$nonce_verified) {
            if (get_option('expoxr_debug_mode', false)) {
                error_log('ExpoXR: Security check failed for post ' . $post_id . '. Nonce verification failed.');
            }
            
            // Create a detailed debug log for nonce failures
            $nonce_debug = array(
                'post_id' => $post_id,
                'nonce_value' => $_POST['expoxr_nonce'],
                'nonce_action' => 'expoxr_save_model',
                'verification_result' => $nonce_verified,
                'user_id' => get_current_user_id(),
                'post_data' => array_keys($_POST)
            );
            expoxr_create_debug_log($nonce_debug, 'nonce-failure-' . $post_id);
            
            return false;
        }
    } else if ($edit_mode) {
        // Enforce nonce requirement in edit mode for security
        if (get_option('expoxr_debug_mode', false)) {
            error_log('ExpoXR: No nonce provided for edit mode submission for post ' . $post_id . ' - blocking for security');
        }
        
        // Create a debug log file for missing nonce
        $no_nonce_debug = array(
            'post_id' => $post_id,
            'edit_mode' => $edit_mode,
            'post_data' => array_keys($_POST),
            'user_id' => get_current_user_id(),
            'security_action' => 'blocked_missing_nonce'
        );
        expoxr_create_debug_log($no_nonce_debug, 'missing-nonce-' . $post_id);
        
        // Block execution for security - nonce is required in edit mode
        return false;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        if (get_option('expoxr_debug_mode', false)) {
            error_log('ExpoXR: User lacks permission to edit post ' . $post_id);
        }
        
        // Log permission issue
        $permission_debug = array(
            'post_id' => $post_id,
            'user_id' => get_current_user_id(),
            'user_caps' => get_userdata(get_current_user_id())->allcaps,
            'post_author' => get_post_field('post_author', $post_id)
        );
        expoxr_create_debug_log($permission_debug, 'permission-denied-' . $post_id);
        
        return false;
    }
      // Basic fields
    if (array_key_exists('expoxr_model_file', $_POST)) {
        update_post_meta($post_id, '_expoxr_model_file', sanitize_text_field($_POST['expoxr_model_file']));
        if ($edit_mode) {
            if (get_option('expoxr_debug_mode', false)) {
                error_log('ExpoXR: Updated model file: ' . $_POST['expoxr_model_file']);
            }
        }
    }
    
    if (array_key_exists('expoxr_model_name', $_POST)) {
        update_post_meta($post_id, '_expoxr_model_name', sanitize_text_field($_POST['expoxr_model_name']));
    }
    
    if (array_key_exists('expoxr_model_alt_text', $_POST)) {
        update_post_meta($post_id, '_expoxr_model_alt_text', sanitize_text_field($_POST['expoxr_model_alt_text']));
    }
    
    // Handle new model file upload
    if (isset($_FILES['expoxr_new_model']) && $_FILES['expoxr_new_model']['size'] > 0) {
        $upload_result = expoxr_handle_model_upload($_FILES['expoxr_new_model']);
        
        if ($upload_result && !empty($upload_result['file_url'])) {
            update_post_meta($post_id, '_expoxr_model_file', $upload_result['file_url']);
            
            // If no model name exists yet, set it from the filename
            $model_name = get_post_meta($post_id, '_expoxr_model_name', true);
            if (empty($model_name)) {
                $filename = basename($upload_result['file_url']);
                $model_name = preg_replace('/\.[^.]+$/', '', $filename);
                update_post_meta($post_id, '_expoxr_model_name', $model_name);
            }
            
            if ($edit_mode) {
                if (get_option('expoxr_debug_mode', false)) {
                    error_log('ExpoXR: Uploaded new model file: ' . $upload_result['file_url']);
                }
            }
        } else {
            // Log upload failures
            $upload_error = isset($upload_result['error']) ? $upload_result['error'] : 'Unknown error';
            if (get_option('expoxr_debug_mode', false)) {
                error_log('ExpoXR: Model upload failed: ' . $upload_error);
            }
            
            // Create a debug log for the upload issue
            if ($edit_mode) {
                $upload_debug = array(
                    'post_id' => $post_id,
                    'file_info' => array(
                        'name' => $_FILES['expoxr_new_model']['name'],
                        'type' => $_FILES['expoxr_new_model']['type'],
                        'size' => $_FILES['expoxr_new_model']['size'],
                        'error' => $_FILES['expoxr_new_model']['error']
                    ),
                    'upload_result' => $upload_result,
                    'php_version' => phpversion(),
                    'memory_limit' => ini_get('memory_limit'),
                    'max_upload_size' => wp_max_upload_size(),
                    'user_id' => get_current_user_id()
                );
                expoxr_create_debug_log($upload_debug, 'upload-failure-' . $post_id);
            }
        }
    }// Save size settings
    expoxr_save_size_settings($post_id);
    
    // Save poster settings
    expoxr_save_poster_settings($post_id);
      // Save camera and accessibility settings - only if Camera addon is available
    if (function_exists('expoxr_is_addon_available') && expoxr_is_addon_available('expoxr-camera-addon', 'camera')) {
        // First run debug functions if they exist
        if (function_exists('expoxr_debug_camera_settings') && $edit_mode) {
            // Include additional debug file
            require_once plugin_dir_path(__FILE__) . 'debug-camera-settings.php';
            $camera_debug = expoxr_debug_camera_settings($post_id, $edit_mode);
            if (get_option('expoxr_debug_mode', false)) {
                error_log('ExpoXR: Camera settings debug collected for post ' . $post_id);
            }
        }
        
        // Then run the actual camera settings save
        expoxr_save_camera_settings($post_id, $edit_mode);
    }
    
    // Save annotations - only if Annotations addon is available
    if (function_exists('expoxr_is_addon_available') && expoxr_is_addon_available('expoxr-annotations-addon', 'annotations')) {
        expoxr_save_annotation_settings($post_id);
    }
      // Save animation settings
    expoxr_save_animation_settings($post_id, $edit_mode);
    
    // Create a comprehensive checkbox debug report if in edit mode
    if ($edit_mode && function_exists('expoxr_debug_checkbox_processing')) {
        // Include debug file if not already included
        if (!function_exists('expoxr_debug_checkbox_processing')) {
            require_once plugin_dir_path(__FILE__) . 'debug-camera-settings.php';
        }
        
        $checkbox_report = expoxr_debug_checkbox_processing($post_id);
        if (get_option('expoxr_debug_mode', false)) {
            error_log('ExpoXR: Generated checkbox debug report for post ' . $post_id);
        }
    }
    
    // AR functionality is not available in the Free version
    
    // If in edit mode, log the save operation for debugging
    if ($edit_mode) {
        if (get_option('expoxr_debug_mode', false)) {
            error_log('ExpoXR: Edit mode meta save completed for post ' . $post_id);
        }
    }
    
    return true;
}

/**
 * Save size settings
 * 
 * @param int $post_id The post ID
 */
function expoxr_save_size_settings($post_id) {
    if (array_key_exists('viewer_size', $_POST)) {
        update_post_meta($post_id, '_expoxr_viewer_size', sanitize_text_field($_POST['viewer_size']));
    }
    
    if (array_key_exists('viewer_width', $_POST)) {
        update_post_meta($post_id, '_expoxr_viewer_width', sanitize_text_field($_POST['viewer_width']));
    }
    
    if (array_key_exists('viewer_height', $_POST)) {
        update_post_meta($post_id, '_expoxr_viewer_height', sanitize_text_field($_POST['viewer_height']));
    }
    
    if (array_key_exists('tablet_viewer_width', $_POST)) {
        update_post_meta($post_id, '_expoxr_tablet_viewer_width', sanitize_text_field($_POST['tablet_viewer_width']));
    }
    
    if (array_key_exists('tablet_viewer_height', $_POST)) {
        update_post_meta($post_id, '_expoxr_tablet_viewer_height', sanitize_text_field($_POST['tablet_viewer_height']));
    }
    
    if (array_key_exists('mobile_viewer_width', $_POST)) {
        update_post_meta($post_id, '_expoxr_mobile_viewer_width', sanitize_text_field($_POST['mobile_viewer_width']));
    }
    
    if (array_key_exists('mobile_viewer_height', $_POST)) {
        update_post_meta($post_id, '_expoxr_mobile_viewer_height', sanitize_text_field($_POST['mobile_viewer_height']));
    }
}

/**
 * Save poster settings
 * 
 * @param int $post_id The post ID
 */
function expoxr_save_poster_settings($post_id) {
    // Handle poster image
    if (isset($_FILES['model_poster']) && $_FILES['model_poster']['size'] > 0) {
        // Use WordPress media handling for the image
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $poster_attachment_id = media_handle_upload('model_poster', $post_id);
        if (!is_wp_error($poster_attachment_id)) {
            $poster_url = wp_get_attachment_url($poster_attachment_id);
            update_post_meta($post_id, '_expoxr_model_poster', $poster_url);
            update_post_meta($post_id, '_expoxr_model_poster_id', $poster_attachment_id);
        }
    }
    
    // Handle removing poster if checkbox is checked
    if (isset($_POST['remove_poster']) && $_POST['remove_poster'] == '1') {
        delete_post_meta($post_id, '_expoxr_model_poster');
        delete_post_meta($post_id, '_expoxr_model_poster_id');
    }
    
    // Handle poster from media library
    if (isset($_POST['model_poster_id']) && !empty($_POST['model_poster_id'])) {
        $poster_id = sanitize_text_field($_POST['model_poster_id']);
        $poster_url = wp_get_attachment_url($poster_id);
        update_post_meta($post_id, '_expoxr_model_poster', $poster_url);
        update_post_meta($post_id, '_expoxr_model_poster_id', $poster_id);
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
function expoxr_save_camera_settings($post_id, $edit_mode = false) {
    if (array_key_exists('expoxr_model_alt_text', $_POST)) {
        update_post_meta($post_id, '_expoxr_model_alt_text', sanitize_text_field($_POST['expoxr_model_alt_text']));
    }
    
    // Interaction controls - new enable interactions logic (same pattern as auto-rotate)
    $enable_interactions = isset($_POST['expoxr_enable_interactions']) ? 'on' : 'off';
    update_post_meta($post_id, '_expoxr_enable_interactions', $enable_interactions);
    
    // Also update the disable_interactions field for backward compatibility
    $disable_interactions = ($enable_interactions === 'off') ? 'on' : 'off';
    update_post_meta($post_id, '_expoxr_disable_interactions', $disable_interactions);
    
    // Camera controls - for backwards compatibility and addon support
    $camera_controls = 'off';
    if (isset($_POST['expoxr_camera_controls']) || (isset($_POST['expoxr_camera_controls_state']) && $_POST['expoxr_camera_controls_state'] === '1')) {
        $camera_controls = 'on';
    }
    update_post_meta($post_id, '_expoxr_camera_controls', $camera_controls);
    
    if ($edit_mode) {
        if (get_option('expoxr_debug_mode', false)) {
            error_log('ExpoXR: Interactions enabled: ' . $enable_interactions . ', Camera controls setting: ' . $camera_controls);
        }
    }
    
    // Auto-rotate settings - support both checkbox and state field
    $auto_rotate = 'off';
    if (isset($_POST['expoxr_auto_rotate']) || (isset($_POST['expoxr_auto_rotate_state']) && $_POST['expoxr_auto_rotate_state'] === '1')) {
        $auto_rotate = 'on';
    }
    update_post_meta($post_id, '_expoxr_auto_rotate', $auto_rotate);
    
    // Handle auto-rotate delay with validation
    if (array_key_exists('expoxr_auto_rotate_delay', $_POST)) {
        $delay_value = sanitize_text_field($_POST['expoxr_auto_rotate_delay']);
        // Ensure the delay is a numeric value
        if (is_numeric($delay_value)) {
            update_post_meta($post_id, '_expoxr_auto_rotate_delay', $delay_value);
        } else {
            // Use default if not numeric
            update_post_meta($post_id, '_expoxr_auto_rotate_delay', '5000');
        }
        
        if ($edit_mode) {
            if (get_option('expoxr_debug_mode', false)) {
                error_log('ExpoXR: Updated auto-rotate delay: ' . $delay_value);
            }
        }
    }
    
    // Handle auto-rotate speed (map from form field to database field)
    if (array_key_exists('expoxr_auto_rotate_speed', $_POST)) {
        $speed_value = sanitize_text_field($_POST['expoxr_auto_rotate_speed']);
        update_post_meta($post_id, '_expoxr_rotation_per_second', $speed_value);
        
        if ($edit_mode) {
            if (get_option('expoxr_debug_mode', false)) {
                error_log('ExpoXR: Updated rotation speed: ' . $speed_value);
            }
        }
    }
    
    // Legacy field support
    if (array_key_exists('expoxr_rotation_per_second', $_POST)) {
        $speed_value = sanitize_text_field($_POST['expoxr_rotation_per_second']);
        update_post_meta($post_id, '_expoxr_rotation_per_second', $speed_value);
    }
    
    // Check if Camera add-on is active for saving advanced settings
    if (function_exists('expoxr_is_camera_addon_active') && expoxr_is_camera_addon_active() && function_exists('expoxr_camera_save_advanced_settings')) {
        expoxr_camera_save_advanced_settings($post_id, $edit_mode);
    }
}

/**
 * Save annotation settings
 * 
 * @param int $post_id The post ID
 */
function expoxr_save_annotation_settings($post_id) {
    if (isset($_POST['expoxr_annotations']) && is_array($_POST['expoxr_annotations'])) {
        $annotations = array();
        
        // Clean up the array by filtering out empty annotations and sanitizing values
        foreach ($_POST['expoxr_annotations'] as $index => $annotation) {
            // Only add annotations with all required position values
            if (!empty($annotation['position_x']) && !empty($annotation['position_y']) && !empty($annotation['position_z'])) {                $annotations[] = array(                    'title' => sanitize_text_field($annotation['title']),
                    'heading_type' => sanitize_text_field($annotation['heading_type']),
                    'text' => wp_kses_post(isset($annotation['text']) ? $annotation['text'] : ''),
                    'text_color' => sanitize_hex_color(isset($annotation['text_color']) && $annotation['text_color'] ? $annotation['text_color'] : '#ffffff'),
                    'bg_color' => sanitize_hex_color(isset($annotation['bg_color']) && $annotation['bg_color'] ? $annotation['bg_color'] : '#000000'),
                    'position_x' => (float) $annotation['position_x'],
                    'position_y' => (float) $annotation['position_y'],
                    'position_z' => (float) $annotation['position_z']
                );
            }
        }
        
        // Save cleaned annotations array
        update_post_meta($post_id, '_expoxr_model_annotations', $annotations);
    } else {
        // If no annotations were submitted, empty the annotations array
        update_post_meta($post_id, '_expoxr_model_annotations', array());
    }
}

/**
 * Save animation settings
 * 
 * @param int $post_id The post ID
 * @param bool $edit_mode Whether edit mode is enabled
 */
function expoxr_save_animation_settings($post_id, $edit_mode = false) {
    // Animation enabled - support both checkbox and state field
    $animation_enabled = 'off';
    if (isset($_POST['expoxr_animation_enabled']) || (isset($_POST['expoxr_animation_enabled_state']) && $_POST['expoxr_animation_enabled_state'] === '1')) {
        $animation_enabled = 'on';
    }
    update_post_meta($post_id, '_expoxr_animation_enabled', $animation_enabled);
    
    if ($edit_mode) {
        if (get_option('expoxr_debug_mode', false)) {
            error_log('ExpoXR: Animation enabled setting: ' . $animation_enabled);
        }
    }
    
    if (array_key_exists('expoxr_animation_name', $_POST)) {
        update_post_meta($post_id, '_expoxr_animation_name', sanitize_text_field($_POST['expoxr_animation_name']));
    }
    
    if (array_key_exists('expoxr_animation_crossfade_duration', $_POST)) {
        update_post_meta($post_id, '_expoxr_animation_crossfade_duration', sanitize_text_field($_POST['expoxr_animation_crossfade_duration']));
    }
    
    // Animation autoplay - support both checkbox and state field
    $animation_autoplay = 'off';
    if (isset($_POST['expoxr_animation_autoplay']) || (isset($_POST['expoxr_animation_autoplay_state']) && $_POST['expoxr_animation_autoplay_state'] === '1')) {
        $animation_autoplay = 'on';
    }
    update_post_meta($post_id, '_expoxr_animation_autoplay', $animation_autoplay);
    
    if ($edit_mode) {
        if (get_option('expoxr_debug_mode', false)) {
            error_log('ExpoXR: Animation autoplay setting: ' . $animation_autoplay);
        }
    }
    
    if (array_key_exists('expoxr_animation_repeat', $_POST)) {
        update_post_meta($post_id, '_expoxr_animation_repeat', sanitize_text_field($_POST['expoxr_animation_repeat']));
    }
}

/**
 * Check if an addon is installed, active, and licensed
 *
 * @param string $addon_slug The addon slug (e.g., 'expoxr-ar-addon')
 * @param string $license_key The license key identifier (e.g., 'ar')
 * @return bool Whether the addon is ready to use
 */
function expoxr_is_addon_available($addon_slug, $license_key) {
    // Addons are not available in the Free version
    return false;
}





