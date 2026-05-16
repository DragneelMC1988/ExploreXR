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
 * Install a free-allowed addon from the update server, then save it as the
 * selected addon. Reuses the same action name as Premium so the JS can share
 * the same handler.
 */
function explorexr_free_ajax_direct_download_addon(): void {
    check_ajax_referer( 'explorexr_install_addon_nonce', 'nonce' );

    if ( ! current_user_can( 'install_plugins' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'explorexr' ) ) );
    }

    $slug = isset( $_POST['slug'] )
        ? sanitize_key( wp_unslash( $_POST['slug'] ) )
        : '';

    $allowed = array( 'ar', 'animation', 'camera', 'annotations' );

    if ( ! in_array( $slug, $allowed, true ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid addon slug.', 'explorexr' ) ) );
    }

    $meta_url = 'https://update.expoxr.com/explorexr/premium/addon-' . $slug
              . '/explorexr-' . $slug . '-addon.json';
    $response = wp_remote_get( $meta_url, array( 'timeout' => 30 ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array(
            'message' => sprintf(
                /* translators: %s: error message */
                __( 'Could not reach update server: %s', 'explorexr' ),
                $response->get_error_message()
            ),
        ) );
    }

    $code = (int) wp_remote_retrieve_response_code( $response );
    if ( 200 !== $code ) {
        wp_send_json_error( array(
            'message' => sprintf(
                /* translators: %d: HTTP status code */
                __( 'Update server returned HTTP %d.', 'explorexr' ),
                $code
            ),
        ) );
    }

    $meta = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( empty( $meta['download_url'] ) ) {
        wp_send_json_error( array( 'message' => __( 'No download URL in server response.', 'explorexr' ) ) );
    }

    $download_url = esc_url_raw( $meta['download_url'] );

    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';

    $skin     = new WP_Ajax_Upgrader_Skin();
    $upgrader = new Plugin_Upgrader( $skin );
    $result   = $upgrader->install( $download_url );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array(
            'message' => sprintf(
                /* translators: %s: error message */
                __( 'Install failed: %s', 'explorexr' ),
                $result->get_error_message()
            ),
        ) );
    }

    if ( false === $result ) {
        $msgs = $skin->get_upgrade_messages();
        wp_send_json_error( array(
            'message' => __( 'Install failed.', 'explorexr' ) . ( $msgs ? ' ' . implode( ' ', $msgs ) : '' ),
        ) );
    }

    $plugin_file = "explorexr-{$slug}-addon/explorexr-{$slug}-addon.php";
    activate_plugin( $plugin_file );

    // Save as the selected free addon
    update_option( 'explorexr_free_selected_addon', sanitize_key( $slug ) );

    wp_send_json_success( array(
        'message'     => __( 'Addon installed and selected successfully.', 'explorexr' ),
        'plugin_file' => $plugin_file,
        'reload'      => true,
    ) );
}

/**
 * Save the selected free addon (already installed, just switching selection).
 */
function explorexr_free_ajax_select_addon(): void {
    check_ajax_referer( 'explorexr_free_select_addon_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'explorexr' ) ) );
    }

    $slug = isset( $_POST['slug'] )
        ? sanitize_key( wp_unslash( $_POST['slug'] ) )
        : '';

    $allowed = array( 'ar', 'animation', 'camera', 'annotations' );

    if ( ! in_array( $slug, $allowed, true ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid addon slug.', 'explorexr' ) ) );
    }

    update_option( 'explorexr_free_selected_addon', $slug );

    wp_send_json_success( array(
        'message' => __( 'Add-on selected.', 'explorexr' ),
        'reload'  => true,
    ) );
}

/**
 * Dismiss the "new feature" hint for the current user.
 */
function explorexr_free_ajax_dismiss_addon_hint(): void {
    check_ajax_referer( 'explorexr_dismiss_free_addon_hint', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'explorexr' ) ) );
    }

    update_user_meta( get_current_user_id(), 'explorexr_free_addon_hint_dismissed', '1' );
    wp_send_json_success();
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
add_action('wp_ajax_explorexr_delete_model',               'explorexr_ajax_delete_model');
add_action('wp_ajax_explorexr_direct_download_addon',      'explorexr_free_ajax_direct_download_addon');
add_action('wp_ajax_explorexr_free_select_addon',          'explorexr_free_ajax_select_addon');
add_action('wp_ajax_explorexr_dismiss_free_addon_hint',    'explorexr_free_ajax_dismiss_addon_hint');
