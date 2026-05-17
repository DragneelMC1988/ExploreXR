<?php
/**
 * AJAX handler for rendering model-viewer with all addon filters applied
 *
 * @package ExploreXR_Premium
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render model for modal preview via AJAX
 * Returns fully-filtered model-viewer HTML with all active addon settings
 */
function explorexr_ajax_render_model_for_preview() {
    // Verify nonce for security
    check_ajax_referer('explorexr-admin-ajax', 'nonce');
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'explorexr')));
        return;
    }
    
    // Get model ID from request
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above with check_ajax_referer
    $model_id = isset($_POST['model_id']) ? absint($_POST['model_id']) : 0;
    
    if (!$model_id || get_post_type($model_id) !== 'explorexr_model') {
        wp_send_json_error(array('message' => esc_html__('Invalid model ID', 'explorexr')));
        return;
    }
    
    // Use centralized rendering function to get fully-filtered HTML
    // This ensures all addon filters are applied
    $html = explorexr_render_model_viewer($model_id, 'admin', array(
        'style' => 'width: 100%; height: 500px;',
    ));
    
    if (empty($html)) {
        wp_send_json_error(array('message' => esc_html__('Failed to render model', 'explorexr')));
        return;
    }
    
    wp_send_json_success(array(
        'html' => $html,
        'title' => get_the_title($model_id),
    ));
}

// Register AJAX handlers for both logged-in users
add_action('wp_ajax_explorexr_render_model_preview', 'explorexr_ajax_render_model_for_preview');
