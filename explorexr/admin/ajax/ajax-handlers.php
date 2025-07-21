<?php
/**
 * AJAX handlers for ExploreXR Free
 *
 * Basic AJAX functionality for the free version
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Basic AJAX handlers for the free version
// Premium addon management features are not available in the free version

/**
 * AJAX handler for deleting models
 */
function explorexr_ajax_delete_model() {
    // Check nonce for security
    if (!isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'explorexr_admin_nonce')) {
        wp_send_json_error(array(
            'message' => 'Security check failed. Please refresh the page and try again.'
        ));
        return;
    }

    // Check if user has permission to delete posts
    if (!current_user_can('delete_posts')) {
        wp_send_json_error(array(
            'message' => 'You do not have permission to delete models.'
        ));
        return;
    }

    // Get and validate the model ID
    if (!isset($_POST['model_id']) || !is_numeric($_POST['model_id'])) {
        wp_send_json_error(array(
            'message' => 'Invalid model ID provided.'
        ));
        return;
    }

    $model_id = intval($_POST['model_id']);

    // Check if the post exists and is the correct post type
    $post = get_post($model_id);
    if (!$post || $post->post_type !== 'explorexr_model') {
        wp_send_json_error(array(
            'message' => 'Model not found or invalid model type.'
        ));
        return;
    }

    // Check if user can delete this specific post
    if (!current_user_can('delete_post', $model_id)) {
        wp_send_json_error(array(
            'message' => 'You do not have permission to delete this model.'
        ));
        return;
    }

    // Attempt to delete the model
    $result = wp_delete_post($model_id, true);

    if ($result === false) {
        wp_send_json_error(array(
            'message' => 'Failed to delete the model. Please try again.'
        ));
        return;
    }

    // Success
    wp_send_json_success(array(
        'message' => 'Model deleted successfully.'
    ));
}

/**
 * Basic AJAX response for free version
 */
function explorexr_free_ajax_response() {
    wp_send_json_error(array(
        'message' => 'Premium features are not available in the free version.'
    ));
}

// Register AJAX handlers
add_action('wp_ajax_explorexr_delete_model', 'explorexr_ajax_delete_model');
