<?php
/**
 * ExploreXR Uninstall Settings
 * 
 * Defines settings for controlling plugin data during uninstallation
 *
 * @package ExpoXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register uninstall settings
 */
function expoxr_register_uninstall_settings() {
    // Add Uninstall settings section
    add_settings_section(
        'expoxr_uninstall_settings',
        esc_html__('Uninstall Settings', 'explorexr'),
        'expoxr_uninstall_settings_callback',
        'expoxr_settings'
    );

    // Remove all data on uninstall
    add_settings_field(
        'expoxr_remove_data_on_uninstall',
        esc_html__('Data Cleanup', 'explorexr'),
        'expoxr_remove_data_on_uninstall_callback',
        'expoxr_settings',
        'expoxr_uninstall_settings'
    );
    register_setting('expoxr_settings', 'expoxr_remove_data_on_uninstall', 'sanitize_checkbox');

    // Remove models on uninstall
    add_settings_field(
        'expoxr_remove_models_on_uninstall',
        esc_html__('Remove 3D Models', 'explorexr'),
        'expoxr_remove_models_on_uninstall_callback',
        'expoxr_settings',
        'expoxr_uninstall_settings'
    );
    register_setting('expoxr_settings', 'expoxr_remove_models_on_uninstall', 'sanitize_checkbox');

    // Remove uploads on uninstall
    add_settings_field(
        'expoxr_remove_uploads_on_uninstall',
        esc_html__('Remove Uploaded Files', 'explorexr'),
        'expoxr_remove_uploads_on_uninstall_callback',
        'expoxr_settings',
        'expoxr_uninstall_settings'
    );    register_setting('expoxr_settings', 'expoxr_remove_uploads_on_uninstall', 'sanitize_checkbox');

    // Remove addons on uninstall
    add_settings_field(
        'expoxr_remove_addons_on_uninstall',
        esc_html__('Remove Addon Plugins', 'explorexr'),
        'expoxr_remove_addons_on_uninstall_callback',
        'expoxr_settings',
        'expoxr_uninstall_settings'
    );
    register_setting('expoxr_settings', 'expoxr_remove_addons_on_uninstall', 'sanitize_checkbox');
}
add_action('admin_init', 'expoxr_register_uninstall_settings');

/**
 * Uninstall settings section callback
 */
function expoxr_uninstall_settings_callback() {
    echo '<p>' . esc_html__('Control what happens when ExploreXR is uninstalled. These settings determine which data will be removed from your site.', 'explorexr') . '</p>';
    echo '<p><strong>' . esc_html__('Note:', 'explorexr') . '</strong> ' . esc_html__('These settings only affect complete plugin uninstallation, not deactivation.', 'explorexr') . '</p>';
    
    // Check if add-ons are installed
    $addon_installed = false;
    if (function_exists('get_plugins')) {
        $addon_patterns = array('expoxr-camera-addon', 'expoxr-annotations-addon', 'expoxr-ar-addon');
        $all_plugins = get_plugins();
        
        foreach ($all_plugins as $plugin_path => $plugin_data) {
            foreach ($addon_patterns as $pattern) {
                if (strpos($plugin_path, $pattern) === 0) {
                    $addon_installed = true;
                    break 2;
                }
            }
        }
    }
    
    if ($addon_installed) {
        echo '<div class="notice notice-info inline"><p>' . esc_html__('<strong>Add-ons Detected:</strong> You have ExploreXR add-on plugin(s) installed. Use the "Remove Addon Plugins" option below to automatically remove them when uninstalling the main plugin.', 'explorexr') . '</p></div>';
    }
}

/**
 * Remove all data on uninstall callback
 */
function expoxr_remove_data_on_uninstall_callback() {
    $remove_data = get_option('expoxr_remove_data_on_uninstall', false);
    ?>
    <label>
        <input type="checkbox" name="expoxr_remove_data_on_uninstall" value="1" <?php checked($remove_data, true); ?>>
        <?php esc_html_e('Remove all plugin settings and data on uninstall', 'explorexr'); ?>
    </label>
    <p class="description"><?php esc_html_e('When enabled, all plugin settings, license information, and addon plugin data will be completely removed when uninstalling the plugin.', 'explorexr'); ?></p>
    <p class="description"><strong><?php esc_html_e('Note:', 'explorexr'); ?></strong> <?php esc_html_e('This option will also automatically remove addon plugins if the "Remove Addon Plugins" option below is enabled.', 'explorexr'); ?></p>
    <?php
}

/**
 * Remove models on uninstall callback
 */
function expoxr_remove_models_on_uninstall_callback() {
    $remove_models = get_option('expoxr_remove_models_on_uninstall', false);
    ?>
    <label>
        <input type="checkbox" name="expoxr_remove_models_on_uninstall" value="1" <?php checked($remove_models, true); ?>>
        <?php esc_html_e('Delete all 3D model posts on uninstall', 'explorexr'); ?>
    </label>
    <p class="description"><?php esc_html_e('When enabled, all 3D model posts created by this plugin will be permanently deleted when uninstalling.', 'explorexr'); ?></p>
    <?php
}

/**
 * Remove uploads on uninstall callback
 */
function expoxr_remove_uploads_on_uninstall_callback() {
    $remove_uploads = get_option('expoxr_remove_uploads_on_uninstall', false);
    ?>
    <label>
        <input type="checkbox" name="expoxr_remove_uploads_on_uninstall" value="1" <?php checked($remove_uploads, true); ?>>
        <?php esc_html_e('Delete all uploaded 3D model files on uninstall', 'explorexr'); ?>
    </label>    <p class="description"><?php esc_html_e('When enabled, all uploaded 3D model files will be permanently deleted from your server when uninstalling.', 'explorexr'); ?></p>
    <?php
}

/**
 * Helper function to sanitize checkbox values
 */
function expoxr_sanitize_checkbox($input) {
    return (isset($input) && $input == 1) ? 1 : 0;
}





