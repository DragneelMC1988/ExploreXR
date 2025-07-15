<?php
/**
 * AJAX handler for addon management
 * 
 * Handles saving addon order and visibility settings via AJAX
 * 
 * @package ExploreXR
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the AJAX action for saving addon settings
 */
function expoxr_register_addon_manager_ajax() {
    add_action('wp_ajax_expoxr_save_addon_settings', 'expoxr_save_addon_settings_callback');
    add_action('wp_ajax_expoxr_delete_model', 'expoxr_delete_model_callback');
}
add_action('init', 'expoxr_register_addon_manager_ajax');

/**
 * AJAX callback to handle saving addon settings
 */
function expoxr_save_addon_settings_callback() {
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'expoxr_addon_manager_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        die();
    }

    // Check if user has permission to edit models
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => 'You do not have permission to edit models'));
        die();
    }

    // Check for required data
    if (!isset($_POST['model_id']) || !isset($_POST['addon_order']) || !isset($_POST['addon_visibility'])) {
        wp_send_json_error(array('message' => 'Missing required data'));
        die();
    }

    $model_id = intval($_POST['model_id']);
    
    // Verify model exists and is an ExploreXR model
    if (!get_post($model_id) || get_post_type($model_id) !== 'expoxr_model') {
        wp_send_json_error(array('message' => 'Invalid model ID'));
        die();
    }

    // Process addon order
    $addon_order = json_decode(stripslashes(sanitize_text_field(wp_unslash($_POST['addon_order']))), true);
    if (is_array($addon_order)) {
        // Sanitize the array
        $sanitized_order = array_map('sanitize_key', $addon_order);
        update_post_meta($model_id, '_expoxr_addon_order', $sanitized_order);
    }

    // Process addon visibility
    $addon_visibility = json_decode(stripslashes(sanitize_text_field(wp_unslash($_POST['addon_visibility']))), true);
    if (is_array($addon_visibility)) {
        // Sanitize the array
        $sanitized_visibility = array();
        foreach ($addon_visibility as $key => $value) {
            $sanitized_visibility[sanitize_key($key)] = (bool) $value;
        }
        update_post_meta($model_id, '_expoxr_addon_visibility', $sanitized_visibility);
    }

    // Send success response
    wp_send_json_success(array(
        'message' => 'Addon settings saved successfully',
        'model_id' => $model_id,
        'addon_order' => $sanitized_order,
        'addon_visibility' => $sanitized_visibility
    ));
    die();
}

/**
 * AJAX callback to handle model deletion
 */
function expoxr_delete_model_callback() {
    // Check nonce for security
    if (!isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'expoxr_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        die();
    }

    // Check if user has permission to delete models
    if (!current_user_can('delete_posts')) {
        wp_send_json_error(array('message' => 'You do not have permission to delete models'));
        die();
    }

    // Check for required data
    if (!isset($_POST['model_id'])) {
        wp_send_json_error(array('message' => 'Model ID is required'));
        die();
    }

    $model_id = intval($_POST['model_id']);
    
    // Verify model exists and is an ExploreXR model
    $post = get_post($model_id);
    if (!$post || get_post_type($model_id) !== 'expoxr_model') {
        wp_send_json_error(array('message' => 'Invalid model ID or model does not exist'));
        die();
    }

    // Check if current user can delete this specific post
    if (!current_user_can('delete_post', $model_id)) {
        wp_send_json_error(array('message' => 'You do not have permission to delete this model'));
        die();
    }

    // Attempt to delete the model
    $deleted = wp_delete_post($model_id, true); // true = force delete (bypass trash)
    
    if ($deleted === false) {
        wp_send_json_error(array('message' => 'Failed to delete model. Please try again.'));
        die();
    }

    // Send success response
    wp_send_json_success(array(
        'message' => 'Model "' . esc_html($post->post_title) . '" deleted successfully',
        'model_id' => $model_id
    ));
    die();
}





