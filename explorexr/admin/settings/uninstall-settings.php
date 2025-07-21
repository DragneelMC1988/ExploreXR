<?php
/**
 * ExploreXR Uninstall Settings
 * 
 * Defines settings for controlling plugin data during uninstallation
 *
  * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register uninstall settings
 */
function explorexr_register_uninstall_settings() {
    // Add Uninstall settings section
    add_settings_section(
        'explorexr_uninstall_settings',
        esc_html__('Uninstall Settings', 'explorexr'),
        'explorexr_uninstall_settings_callback',
        'explorexr_settings'
    );

    // Remove all data on uninstall
    add_settings_field(
        'explorexr_remove_data_on_uninstall',
        esc_html__('Data Cleanup', 'explorexr'),
        'explorexr_remove_data_on_uninstall_callback',
        'explorexr_settings',
        'explorexr_uninstall_settings'
    );
    register_setting('explorexr_settings', 'explorexr_remove_data_on_uninstall', 'sanitize_checkbox');

    // Remove models on uninstall
    add_settings_field(
        'explorexr_remove_models_on_uninstall',
        esc_html__('Remove 3D Models', 'explorexr'),
        'explorexr_remove_models_on_uninstall_callback',
        'explorexr_settings',
        'explorexr_uninstall_settings'
    );
    register_setting('explorexr_settings', 'explorexr_remove_models_on_uninstall', 'sanitize_checkbox');

    // Remove uploads on uninstall
    add_settings_field(
        'explorexr_remove_uploads_on_uninstall',
        esc_html__('Remove Uploaded Files', 'explorexr'),
        'explorexr_remove_uploads_on_uninstall_callback',
        'explorexr_settings',
        'explorexr_uninstall_settings'
    );    register_setting('explorexr_settings', 'explorexr_remove_uploads_on_uninstall', 'sanitize_checkbox');
}
add_action('admin_init', 'explorexr_register_uninstall_settings');

/**
 * Uninstall settings section callback
 */
function explorexr_uninstall_settings_callback() {
    echo '<p>' . esc_html__('Control what happens when ExploreXR is uninstalled. These settings determine which data will be removed from your site.', 'explorexr') . '</p>';
    echo '<p><strong>' . esc_html__('Note:', 'explorexr') . '</strong> ' . esc_html__('These settings only affect complete plugin uninstallation, not deactivation.', 'explorexr') . '</p>';
}

/**
 * Remove all data on uninstall callback
 */
function explorexr_remove_data_on_uninstall_callback() {
    $remove_data = get_option('explorexr_remove_data_on_uninstall', false);
    ?>
    <label>
        <input type="checkbox" name="explorexr_remove_data_on_uninstall" value="1" <?php checked($remove_data, true); ?>>
        <?php esc_html_e('Remove all plugin settings and data on uninstall', 'explorexr'); ?>
    </label>
    <p class="description"><?php esc_html_e('When enabled, all plugin settings and license information will be completely removed when uninstalling the plugin.', 'explorexr'); ?></p>
    <?php
}

/**
 * Remove models on uninstall callback
 */
function explorexr_remove_models_on_uninstall_callback() {
    $remove_models = get_option('explorexr_remove_models_on_uninstall', false);
    ?>
    <label>
        <input type="checkbox" name="explorexr_remove_models_on_uninstall" value="1" <?php checked($remove_models, true); ?>>
        <?php esc_html_e('Delete all 3D model posts on uninstall', 'explorexr'); ?>
    </label>
    <p class="description"><?php esc_html_e('When enabled, all 3D model posts created by this plugin will be permanently deleted when uninstalling.', 'explorexr'); ?></p>
    <?php
}

/**
 * Remove uploads on uninstall callback
 */
function explorexr_remove_uploads_on_uninstall_callback() {
    $remove_uploads = get_option('explorexr_remove_uploads_on_uninstall', false);
    ?>
    <label>
        <input type="checkbox" name="explorexr_remove_uploads_on_uninstall" value="1" <?php checked($remove_uploads, true); ?>>
        <?php esc_html_e('Delete all uploaded 3D model files on uninstall', 'explorexr'); ?>
    </label>    <p class="description"><?php esc_html_e('When enabled, all uploaded 3D model files will be permanently deleted from your server when uninstalling.', 'explorexr'); ?></p>
    <?php
}

/**
 * Helper function to sanitize checkbox values
 */
function explorexr_sanitize_checkbox($input) {
    return (isset($input) && $input == 1) ? 1 : 0;
}





