<?php
/**
 * AJAX handlers for ExploreXR Free
 *
 * Basic AJAX functionality. Premium addon-management endpoints (license
 * activation, custom update-server downloads, migration) live in the
 * Premium plugin only.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handler for deleting models
 */
function explorexr_ajax_delete_model() {
    if (!isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'explorexr_admin_nonce')) {
        wp_send_json_error(array(
            'message' => esc_html__('Security check failed. Please refresh the page and try again.', 'explorexr')
        ));
        return;
    }

    if (!current_user_can('delete_posts')) {
        wp_send_json_error(array(
            'message' => esc_html__('You do not have permission to delete models.', 'explorexr')
        ));
        return;
    }

    if (!isset($_POST['model_id']) || !is_numeric($_POST['model_id'])) {
        wp_send_json_error(array(
            'message' => esc_html__('Invalid model ID provided.', 'explorexr')
        ));
        return;
    }

    $model_id = intval($_POST['model_id']);

    $post = get_post($model_id);
    if (!$post || $post->post_type !== 'explorexr_model') {
        wp_send_json_error(array(
            'message' => esc_html__('Model not found or invalid model type.', 'explorexr')
        ));
        return;
    }

    if (!current_user_can('delete_post', $model_id)) {
        wp_send_json_error(array(
            'message' => esc_html__('You do not have permission to delete this model.', 'explorexr')
        ));
        return;
    }

    $result = wp_delete_post($model_id, true);

    if ($result === false) {
        wp_send_json_error(array(
            'message' => esc_html__('Failed to delete the model. Please try again.', 'explorexr')
        ));
        return;
    }

    wp_send_json_success(array(
        'message' => esc_html__('Model deleted successfully.', 'explorexr')
    ));
}
add_action('wp_ajax_explorexr_delete_model', 'explorexr_ajax_delete_model');

/**
 * Direct download + activate a free addon from update.expoxr.com.
 *
 * Free version: whitelist limited to AR, Animation, Loading.
 * Enforces one-addon-at-a-time before install.
 */
function explorexr_free_ajax_direct_download_addon() {
    check_ajax_referer('explorexr_install_addon_nonce', 'nonce');

    if (!current_user_can('install_plugins')) {
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions.', 'explorexr')));
    }

    $slug = isset($_POST['slug'])
        ? sanitize_key(wp_unslash($_POST['slug']))
        : '';

    $allowed = array('ar', 'animation', 'loading');

    if (!in_array($slug, $allowed, true)) {
        wp_send_json_error(array('message' => esc_html__('This addon is not available in the free version.', 'explorexr')));
    }

    // One-addon-at-a-time enforcement.
    if (class_exists('ExploreXR_Addon_Manager')) {
        $manager = ExploreXR_Addon_Manager::get_instance();
        foreach ($allowed as $check_slug) {
            if ($check_slug !== $slug && $manager->is_addon_active($check_slug)) {
                wp_send_json_error(array(
                    'message' => esc_html__('ExploreXR Free allows one addon at a time. Deactivate the current addon from the Plugins screen, then try again.', 'explorexr'),
                ));
            }
        }
    }

    $meta_url = 'https://update.expoxr.com/explorexr/premium/addon-' . $slug
              . '/explorexr-' . $slug . '-addon.json';
    $response = wp_remote_get($meta_url, array('timeout' => 30));

    if (is_wp_error($response)) {
        wp_send_json_error(array(
            'message' => sprintf(
                /* translators: %s: error message returned by WordPress HTTP API */
                esc_html__('Could not reach update server: %s', 'explorexr'),
                esc_html($response->get_error_message())
            ),
        ));
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if (200 !== $code) {
        wp_send_json_error(array(
            'message' => sprintf(
                /* translators: %d: HTTP status code */
                esc_html__('Update server returned HTTP %d.', 'explorexr'),
                $code
            ),
        ));
    }

    $meta = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($meta['download_url'])) {
        wp_send_json_error(array('message' => esc_html__('No download URL in server response.', 'explorexr')));
    }

    $download_url  = esc_url_raw($meta['download_url']);
    $allowed_hosts = array('update.expoxr.com', 'downloads.expoxr.com');
    $url_host      = wp_parse_url($download_url, PHP_URL_HOST);
    if (!in_array($url_host, $allowed_hosts, true)) {
        wp_send_json_error(array('message' => esc_html__('Download URL is not from a trusted source.', 'explorexr')));
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';

    $skin     = new WP_Ajax_Upgrader_Skin();
    $upgrader = new Plugin_Upgrader($skin);
    $result   = $upgrader->install($download_url);

    if (is_wp_error($result)) {
        wp_send_json_error(array(
            'message' => sprintf(
                /* translators: %s: install error message */
                esc_html__('Install failed: %s', 'explorexr'),
                esc_html($result->get_error_message())
            ),
        ));
    }

    if (false === $result) {
        $msgs = $skin->get_upgrade_messages();
        wp_send_json_error(array(
            'message' => esc_html__('Install failed.', 'explorexr') . ' ' . ($msgs ? esc_html(implode(' ', $msgs)) : esc_html__('Unknown error.', 'explorexr')),
        ));
    }

    $plugin_file     = "explorexr-{$slug}-addon/explorexr-{$slug}-addon.php";
    $activate_result = activate_plugin($plugin_file);

    if (is_wp_error($activate_result)) {
        wp_send_json_success(array(
            'message'     => sprintf(
                /* translators: %s: activation error message */
                esc_html__('Installed. Note: %s', 'explorexr'),
                esc_html($activate_result->get_error_message())
            ),
            'plugin_file' => $plugin_file,
            'reload'      => true,
        ));
    }

    wp_send_json_success(array(
        'message'     => esc_html__('Addon installed and activated successfully.', 'explorexr'),
        'plugin_file' => $plugin_file,
        'reload'      => true,
    ));
}
add_action('wp_ajax_explorexr_direct_download_addon', 'explorexr_free_ajax_direct_download_addon');
