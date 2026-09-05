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
 * Validate legacy-channel metadata without pinning the server-advertised version.
 *
 * @param mixed  $meta Add-on metadata.
 * @param string $slug Free add-on slug.
 * @return array|WP_Error
 */
function explorexr_free_validate_addon_metadata($meta, $slug) {
    if (!is_array($meta)) {
        return new WP_Error('invalid_addon_metadata', __('The update server returned invalid add-on metadata.', 'explorexr'));
    }

    $required_fields = array('slug', 'version', 'download_url', 'requires', 'tested', 'requires_php');
    foreach ($required_fields as $field) {
        if (!isset($meta[$field]) || !is_string($meta[$field]) || '' === trim($meta[$field])) {
            return new WP_Error('invalid_addon_metadata', __('The update server returned incomplete add-on metadata.', 'explorexr'));
        }
    }

    $expected_slug = 'explorexr-' . $slug . '-addon';
    if ($expected_slug !== $meta['slug']) {
        return new WP_Error('invalid_addon_slug', __('The update server returned metadata for an unexpected add-on.', 'explorexr'));
    }

    $version      = sanitize_text_field($meta['version']);
    $requires     = sanitize_text_field($meta['requires']);
    $tested       = sanitize_text_field($meta['tested']);
    $requires_php = sanitize_text_field($meta['requires_php']);
    $version_rule = '/^\d+(?:\.\d+){1,3}(?:[-+][0-9A-Za-z.-]+)?$/';
    $compat_rule  = '/^\d+(?:\.\d+){0,3}$/';

    if (!preg_match($version_rule, $version)
        || !preg_match($compat_rule, $requires)
        || !preg_match($compat_rule, $tested)
        || !preg_match($compat_rule, $requires_php)
        || version_compare($tested, $requires, '<')) {
        return new WP_Error('invalid_addon_compatibility', __('The update server returned invalid compatibility information.', 'explorexr'));
    }

    if (!is_wp_version_compatible($requires)) {
        return new WP_Error(
            'incompatible_wordpress',
            sprintf(
                /* translators: %s: minimum required WordPress version */
                __('This add-on requires WordPress %s or newer.', 'explorexr'),
                $requires
            )
        );
    }

    if (!is_php_version_compatible($requires_php)) {
        return new WP_Error(
            'incompatible_php',
            sprintf(
                /* translators: %s: minimum required PHP version */
                __('This add-on requires PHP %s or newer.', 'explorexr'),
                $requires_php
            )
        );
    }

    $download_url  = esc_url_raw($meta['download_url'], array('https'));
    $url_scheme    = strtolower((string) wp_parse_url($download_url, PHP_URL_SCHEME));
    $url_host      = strtolower((string) wp_parse_url($download_url, PHP_URL_HOST));
    $allowed_hosts = array('update.expoxr.com', 'downloads.expoxr.com');

    if (!$download_url || !wp_http_validate_url($download_url) || 'https' !== $url_scheme) {
        return new WP_Error('invalid_addon_url', __('The add-on download URL must use HTTPS.', 'explorexr'));
    }
    if (!in_array($url_host, $allowed_hosts, true)) {
        return new WP_Error('untrusted_addon_url', __('The add-on download URL is not from a trusted source.', 'explorexr'));
    }

    return array(
        'slug'         => $expected_slug,
        'version'      => $version,
        'download_url' => $download_url,
        'requires'     => $requires,
        'tested'       => $tested,
        'requires_php' => $requires_php,
    );
}

/**
 * Direct download + activate a free addon from update.expoxr.com.
 *
 * Free version: whitelist limited to AR, Animation, Loading.
 * Enforces one-addon-at-a-time before install.
 */
function explorexr_free_ajax_direct_download_addon() {
    check_ajax_referer('explorexr_install_addon_nonce', 'nonce');

    if (!current_user_can('install_plugins') || !current_user_can('activate_plugins')) {
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions.', 'explorexr')));
    }

    $slug = isset($_POST['slug'])
        ? sanitize_key(wp_unslash($_POST['slug']))
        : '';

    // Single source of truth for the free-tier whitelist lives in the addon manager.
    $allowed = class_exists('ExploreXR_Addon_Manager')
        ? ExploreXR_Addon_Manager::WHITELIST
        : array('ar', 'animation', 'loading');

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
    if (JSON_ERROR_NONE !== json_last_error()) {
        wp_send_json_error(array('message' => esc_html__('The update server returned malformed JSON.', 'explorexr')));
    }

    $meta = explorexr_free_validate_addon_metadata($meta, $slug);
    if (is_wp_error($meta)) {
        wp_send_json_error(array('message' => esc_html($meta->get_error_message())));
    }

    $expected_directory = 'explorexr-' . $slug . '-addon';
    $plugin_file        = $expected_directory . '/explorexr-' . $slug . '-addon.php';
    $plugin_path        = WP_PLUGIN_DIR . '/' . $plugin_file;

    if (is_dir(WP_PLUGIN_DIR . '/' . $expected_directory) || file_exists($plugin_path)) {
        wp_send_json_error(array('message' => esc_html__('The add-on is already installed. Activate it from the Plugins screen.', 'explorexr')));
    }

    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $skin     = new WP_Ajax_Upgrader_Skin();
    $upgrader = new Plugin_Upgrader($skin);
    $result   = $upgrader->install($meta['download_url']);

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

    $plugin_root_real    = realpath(WP_PLUGIN_DIR);
    $installed_dir_real  = realpath(WP_PLUGIN_DIR . '/' . $expected_directory);
    $valid_installed_dir = false !== $plugin_root_real
        && false !== $installed_dir_real
        && $plugin_root_real === dirname($installed_dir_real)
        && $expected_directory === basename($installed_dir_real);

    if (!$valid_installed_dir) {
        wp_send_json_error(array('message' => esc_html__('The downloaded package did not install into the expected add-on directory.', 'explorexr')));
    }

    if (!file_exists($plugin_path) || 0 !== validate_file($plugin_file)) {
        wp_send_json_error(array('message' => esc_html__('The downloaded package is missing the expected add-on main file.', 'explorexr')));
    }

    $plugin_data       = get_plugin_data($plugin_path, false, false);
    $installed_version = isset($plugin_data['Version']) ? sanitize_text_field($plugin_data['Version']) : '';
    if ('' === $installed_version || $meta['version'] !== $installed_version) {
        wp_send_json_error(array('message' => esc_html__('The installed add-on version does not match the update server metadata.', 'explorexr')));
    }

    $activate_result = activate_plugin($plugin_file);

    if (is_wp_error($activate_result)) {
        wp_send_json_error(array(
            'message' => sprintf(
                /* translators: %s: activation error message */
                esc_html__('The add-on was installed but activation failed: %s', 'explorexr'),
                esc_html($activate_result->get_error_message())
            ),
        ));
    }

    if (!is_plugin_active($plugin_file)) {
        wp_send_json_error(array('message' => esc_html__('The add-on was installed but could not be activated.', 'explorexr')));
    }

    wp_send_json_success(array(
        'message'     => esc_html__('Addon installed and activated successfully.', 'explorexr'),
        'plugin_file' => $plugin_file,
        'reload'      => true,
    ));
}
add_action('wp_ajax_explorexr_direct_download_addon', 'explorexr_free_ajax_direct_download_addon');
