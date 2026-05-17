<?php
/**
 * ExploreXR AR Add-On - Settings
 * 
 * Handles settings for the AR Add-On.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register AR settings
 */
function explorexr_premium_ar_addon_register_settings() {
    // Register basic settings
    register_setting('explorexr_premium_ar_settings', 'explorexr_premium_ar_button_text');
    register_setting('explorexr_premium_ar_settings', 'explorexr_premium_ar_fallback_text');
    register_setting('explorexr_premium_ar_settings', 'explorexr_premium_ar_bg_color');
    
    // Register AR technology settings
    register_setting('explorexr_premium_ar_settings', 'explorexr_premium_ar_modes');
    register_setting('explorexr_premium_ar_settings', 'explorexr_premium_ar_scale');
    register_setting('explorexr_premium_ar_settings', 'explorexr_premium_ar_placement');
    
    // Register advanced settings
    register_setting('explorexr_premium_ar_settings', 'explorexr_premium_ar_button_image');
    register_setting('explorexr_premium_ar_settings', 'explorexr_premium_ar_usdz_model');
    register_setting('explorexr_premium_ar_settings', 'explorexr_premium_ar_xr_environment');
    register_setting('explorexr_premium_ar_settings', 'explorexr_premium_ar_min_height');
    
    // Add settings sections
    add_settings_section(
        'explorexr_premium_ar_general_section',
        __('AR General Settings', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_general_section_callback',
        'explorexr-premium-ar-settings'
    );
    
    add_settings_section(
        'explorexr_premium_ar_technology_section',
        __('AR Technology Settings', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_technology_section_callback',
        'explorexr-premium-ar-settings'
    );
      add_settings_section(
        'explorexr_premium_ar_advanced_section',
        __('Advanced AR Settings', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_advanced_section_callback',
        'explorexr-premium-ar-settings'
    );
    
    add_settings_section(
        'explorexr_premium_ar_maintenance_section',
        __('AR Maintenance & Tools', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_maintenance_section_callback',
        'explorexr-premium-ar-settings'
    );
    
    // Add settings fields - General Section
    add_settings_field(
        'explorexr_premium_ar_button_text',
        __('AR Button Text', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_button_text_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_general_section'
    );
    
    add_settings_field(
        'explorexr_premium_ar_fallback_text',
        __('AR Fallback Text', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_fallback_text_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_general_section'
    );
    
    add_settings_field(
        'explorexr_premium_ar_bg_color',
        __('AR Background Color', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_bg_color_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_general_section'
    );
    
    // Add settings fields - Technology Section
    add_settings_field(
        'explorexr_premium_ar_modes',
        __('AR Modes', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_modes_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_technology_section'
    );
    
    add_settings_field(
        'explorexr_premium_ar_scale',
        __('AR Scale', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_scale_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_technology_section'
    );
    
    add_settings_field(
        'explorexr_premium_ar_placement',
        __('AR Placement', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_placement_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_technology_section'
    );
    
    // Add settings fields - Advanced Section
    add_settings_field(
        'explorexr_premium_ar_button_image',
        __('AR Button Image', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_button_image_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_advanced_section'
    );
    
    add_settings_field(
        'explorexr_premium_ar_usdz_model',
        __('USDZ Model Path (iOS)', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_usdz_model_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_advanced_section'
    );
    
    add_settings_field(
        'explorexr_premium_ar_xr_environment',
        __('AR Environment Map', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_xr_environment_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_advanced_section'
    );
      add_settings_field(
        'explorexr_premium_ar_min_height',
        __('Minimum AR Viewer Height', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_min_height_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_advanced_section'
    );
    
    // Add settings fields - Maintenance Section
    add_settings_field(
        'explorexr_premium_ar_reset_options',
        __('Reset AR Options', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_reset_options_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_maintenance_section'
    );
    
    add_settings_field(
        'explorexr_premium_ar_clear_cache',
        __('Clear AR Cache', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_clear_cache_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_maintenance_section'
    );
    
    add_settings_field(
        'explorexr_premium_ar_clear_model_cache',
        __('Clear Model Cache', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_clear_model_cache_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_maintenance_section'
    );
    
    add_settings_field(
        'explorexr_premium_ar_flush_js_cache',
        __('Flush AR JavaScript Cache', 'explorexr-ar-addon'),
        'explorexr_premium_ar_addon_flush_js_cache_callback',
        'explorexr-premium-ar-settings',
        'explorexr_premium_ar_maintenance_section'
    );
}
add_action('admin_init', 'explorexr_premium_ar_addon_register_settings');

/**
 * General section callback
 */
function explorexr_premium_ar_addon_general_section_callback() {
	echo '<p>' . esc_html__( 'Configure general settings for AR functionality.', 'explorexr-ar-addon' ) . '</p>';
}

/**
 * Technology section callback
 */
function explorexr_premium_ar_addon_technology_section_callback() {
	echo '<p>' . esc_html__( 'Configure AR technology compatibility settings for different devices.', 'explorexr-ar-addon' ) . '</p>';
}

/**
 * Advanced section callback
 */
function explorexr_premium_ar_addon_advanced_section_callback() {
	echo '<p>' . esc_html__( 'Advanced settings for AR experience customization.', 'explorexr-ar-addon' ) . '</p>';
}

/**
 * Maintenance section callback
 */
function explorexr_premium_ar_addon_maintenance_section_callback() {
	echo '<p>' . esc_html__( 'Tools for maintaining and troubleshooting your AR configuration. Use these tools to reset settings, clear cache, and flush JavaScript files.', 'explorexr-ar-addon' ) . '</p>';
	echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Warning:', 'explorexr-ar-addon' ) . '</strong> ' . esc_html__( 'These actions cannot be undone. Make sure you have a backup before proceeding.', 'explorexr-ar-addon' ) . '</p></div>';
}

/**
 * AR button text field callback
 */
function explorexr_premium_ar_addon_button_text_callback() {
    $button_text = get_option('explorexr_premium_ar_button_text', '');
    ?>
    <input type="text" name="explorexr_premium_ar_button_text" value="<?php echo esc_attr($button_text); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('Text for the AR button that appears on model viewers.', 'explorexr-ar-addon'); ?></p>
    <?php
}

/**
 * AR fallback text field callback
 */
function explorexr_premium_ar_addon_fallback_text_callback() {
    $fallback_text = get_option('explorexr_premium_ar_fallback_text', 'AR not supported on this device');
    ?>
    <input type="text" name="explorexr_premium_ar_fallback_text" value="<?php echo esc_attr($fallback_text); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e('Text shown when AR is not supported on the user\'s device.', 'explorexr-ar-addon'); ?></p>
    <?php
}

/**
 * AR background color field callback
 */
function explorexr_premium_ar_addon_bg_color_callback() {
    $bg_color = get_option('explorexr_premium_ar_bg_color', '#ffffff');
    ?>
    <input type="text" name="explorexr_premium_ar_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="explorexr-premium-color-picker" data-default-color="#ffffff" />    <p class="description"><?php esc_html_e('Background color for the AR experience.', 'explorexr-ar-addon'); ?></p>
    <?php
}

/**
 * AR modes field callback
 */
function explorexr_premium_ar_addon_modes_callback() {
    $ar_modes = get_option('explorexr_premium_ar_modes', array('webxr', 'scene-viewer', 'quick-look'));
    
    if (!is_array($ar_modes)) {
        $ar_modes = array();
    }
    
    $mode_options = array(
        'webxr' => __('WebXR (AR on web browsers)', 'explorexr-ar-addon'),
        'scene-viewer' => __('Scene Viewer (Android)', 'explorexr-ar-addon'),
        'quick-look' => __('Quick Look (iOS)', 'explorexr-ar-addon')
    );
    
    echo '<div class="explorexr-premium-checkbox-group">';
    foreach ($mode_options as $value => $label) {
        $checked = in_array($value, $ar_modes) ? 'checked' : '';
        echo '<label class="explorexr-premium-checkbox-label">
            <input type="checkbox" name="explorexr_premium_ar_modes[]" value="' . esc_attr($value) . '" ' . $checked . '>
            <span>' . esc_html($label) . '</span>
        </label>';
    }    echo '</div>';
    echo '<p class="description">' . esc_html__( "Choose which AR technologies to support. It's recommended to enable all for maximum compatibility.", 'explorexr-ar-addon' ) . '</p>';
    
    // Note: AR mode validation handled in external JavaScript file (ar-settings.js)
}

/**
 * AR scale field callback
 */
function explorexr_premium_ar_addon_scale_callback() {
    $ar_scale = get_option('explorexr_premium_ar_scale', 'auto');
    $options = array(
        'auto' => __('Auto (default)', 'explorexr-ar-addon'),
        'fixed' => __('Fixed (use model\'s true size)', 'explorexr-ar-addon')
    );
    
    echo '<select name="explorexr_premium_ar_scale">';
    foreach ($options as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($ar_scale, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( "Determines how the model is scaled in AR. 'Auto' adjusts to reasonable size, 'Fixed' uses the model's actual dimensions.", 'explorexr-ar-addon' ) . '</p>';
}

/**
 * AR placement field callback
 */
function explorexr_premium_ar_addon_placement_callback() {
    $ar_placement = get_option('explorexr_premium_ar_placement', 'floor');
    $options = array(
        'floor' => __( 'Floor (default)', 'explorexr-ar-addon' ),
        'wall'  => __( 'Wall', 'explorexr-ar-addon' ),
        'table' => __( 'Table / Surface', 'explorexr-ar-addon' ),
    );
    
    echo '<select name="explorexr_premium_ar_placement">';
    foreach ($options as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($ar_placement, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Where the model should be placed in the real world. Choose the option most appropriate for your model type.', 'explorexr-ar-addon' ) . '</p>';
}

/**
 * AR button image field callback
 */
function explorexr_premium_ar_addon_button_image_callback() {
    $button_image = get_option('explorexr_premium_ar_button_image', '');
    $image_preview = !empty($button_image) ? '<div class="image-preview"><img src="' . esc_url($button_image) . '" alt="' . esc_attr__('AR Button Preview', 'explorexr-ar-addon') . '" style="max-height: 50px;"></div>' : '';
    
    ?>
    <div class="image-upload-field">
        <input type="text" id="explorexr_premium_ar_button_image_field" name="explorexr_premium_ar_button_image" value="<?php echo esc_attr($button_image); ?>" class="regular-text" readonly>
        <button type="button" class="explorexr-premium-button explorexr-premium-button-secondary ar-select-image" data-target="explorexr_premium_ar_button_image_field"><?php esc_html_e('Select Image', 'explorexr-ar-addon'); ?></button>
        <?php if ($button_image) : ?>
            <button type="button" class="explorexr-premium-button explorexr-premium-button-link ar-remove-image" data-target="explorexr_premium_ar_button_image_field"><?php esc_html_e('Remove', 'explorexr-ar-addon'); ?></button>
        <?php endif; ?>
        <div class="image-preview-container"><?php echo $image_preview; ?></div>
    </div>
    <p class="description"><?php esc_html_e('Optional: Use a custom image for the AR button instead of text.', 'explorexr-ar-addon'); ?></p>
    <?php
}

/**
 * AR USDZ model field callback
 */
function explorexr_premium_ar_addon_usdz_model_callback() {
    $usdz_model = get_option('explorexr_premium_ar_usdz_model', '');
    ?>
    <div class="file-upload-field">
        <input type="text" id="explorexr_premium_ar_usdz_model_field" name="explorexr_premium_ar_usdz_model" value="<?php echo esc_attr($usdz_model); ?>" class="regular-text">
        <button type="button" class="explorexr-premium-button explorexr-premium-button-secondary ar-select-file" data-target="explorexr_premium_ar_usdz_model_field"><?php esc_html_e('Select File', 'explorexr-ar-addon'); ?></button>
    </div>
    <p class="description"><?php esc_html_e('For better iOS AR support, provide a default USDZ model. Individual models can override this setting.', 'explorexr-ar-addon'); ?></p>
    <?php
}

/**
 * AR environment map field callback
 */
function explorexr_premium_ar_addon_xr_environment_callback() {
    $environment = get_option('explorexr_premium_ar_xr_environment', '');
    ?>
    <div class="file-upload-field">
        <input type="text" id="explorexr_premium_ar_xr_environment_field" name="explorexr_premium_ar_xr_environment" value="<?php echo esc_attr($environment); ?>" class="regular-text">
        <button type="button" class="explorexr-premium-button explorexr-premium-button-secondary ar-select-file" data-target="explorexr_premium_ar_xr_environment_field"><?php esc_html_e('Select File', 'explorexr-ar-addon'); ?></button>
    </div>
    <p class="description"><?php esc_html_e('Optional: URL to an HDR environment map for lighting models in AR. Leave empty for default lighting.', 'explorexr-ar-addon'); ?></p>
    <?php
}

/**
 * AR minimum height field callback
 */
function explorexr_premium_ar_addon_min_height_callback() {
    $min_height = get_option('explorexr_premium_ar_min_height', '400px');
    ?>
    <input type="text" name="explorexr_premium_ar_min_height" value="<?php echo esc_attr($min_height); ?>" class="small-text" placeholder="400px">
    <p class="description"><?php esc_html_e('Minimum height of the model viewer on mobile devices when in AR mode. (e.g., 400px)', 'explorexr-ar-addon'); ?></p>
    <?php
}

/**
 * Reset AR options callback
 */
function explorexr_premium_ar_addon_reset_options_callback() {
    ?>
    <div class="maintenance-tool">
        <button type="button" class="button button-secondary ar-reset-options" data-action="reset_ar_options">
            <?php esc_html_e('Reset All AR Options', 'explorexr-ar-addon'); ?>
        </button>
        <p class="description">
            <?php esc_html_e('Reset all AR settings to their default values. This will clear all custom configurations.', 'explorexr-ar-addon'); ?>
        </p>
        <div class="ar-action-result" id="reset-options-result"></div>
    </div>
    <?php
}

/**
 * Clear AR cache callback
 */
function explorexr_premium_ar_addon_clear_cache_callback() {
    ?>
    <div class="maintenance-tool">
        <button type="button" class="button button-secondary ar-clear-cache" data-action="clear_ar_cache">
            <?php esc_html_e('Clear AR Cache', 'explorexr-ar-addon'); ?>
        </button>
        <p class="description">
            <?php esc_html_e('Clear the AR-specific cache including processed AR configurations and temporary data.', 'explorexr-ar-addon'); ?>
        </p>
        <div class="ar-action-result" id="clear-cache-result"></div>
    </div>
    <?php
}

/**
 * Clear model cache callback
 */
function explorexr_premium_ar_addon_clear_model_cache_callback() {
    ?>
    <div class="maintenance-tool">
        <button type="button" class="button button-secondary ar-clear-model-cache" data-action="clear_model_cache">
            <?php esc_html_e('Clear Model Cache', 'explorexr-ar-addon'); ?>
        </button>
        <p class="description">
            <?php esc_html_e('Clear cached 3D model data and thumbnails. This will force models to be reprocessed on next load.', 'explorexr-ar-addon'); ?>
        </p>
        <div class="ar-action-result" id="clear-model-cache-result"></div>
    </div>
    <?php
}

/**
 * Flush AR JavaScript cache callback
 */
function explorexr_premium_ar_addon_flush_js_cache_callback() {
    ?>
    <div class="maintenance-tool">
        <button type="button" class="button button-secondary ar-flush-js-cache" data-action="flush_js_cache">
            <?php esc_html_e('Flush AR JavaScript Cache', 'explorexr-ar-addon'); ?>
        </button>
        <p class="description">
            <?php esc_html_e('Clear and regenerate AR JavaScript files. Use this if you\'re experiencing issues with AR functionality.', 'explorexr-ar-addon'); ?>
        </p>
        <div class="ar-action-result" id="flush-js-cache-result"></div>
    </div>
    <?php
}

/**
 * Enhanced admin script enqueuing for AR settings page
 * Includes proper localization and external file loading
 */
function explorexr_premium_ar_addon_enqueue_enhanced_admin_scripts($hook) {
    if ('explorexr_premium_page_explorexr-premium-ar-settings' !== $hook) {
        return;
    }
    
    // Add the color picker CSS file
    wp_enqueue_style('wp-color-picker');
    
    // Make sure to enqueue media scripts
    wp_enqueue_media();
    
    // Enqueue existing admin styles (enhanced)
    wp_enqueue_style(
        'explorexr-premium-ar-admin-enhanced',
        explorexr_premium_AR_PLUGIN_URL . 'assets/css/ar-admin.css',
        array(),
        explorexr_premium_AR_VERSION
    );
    
    // Enqueue enhanced admin scripts
    wp_enqueue_script(
        'explorexr-premium-ar-settings-enhanced',
        explorexr_premium_AR_PLUGIN_URL . 'assets/js/ar-settings.js',
        array('jquery', 'wp-color-picker'),
        explorexr_premium_AR_VERSION,
        true
    );
    
    // Enhanced localization with all required strings
    wp_localize_script('explorexr-premium-ar-settings-enhanced', 'explorexrARSettings', array(
        'selectImage' => __('Select or Upload AR Button Image', 'explorexr-ar-addon'),
        'useImage' => __('Use this image', 'explorexr-ar-addon'),
        'removeImage' => __('Remove', 'explorexr-ar-addon'),
        'selectFile' => __('Select or Upload File', 'explorexr-ar-addon'),
        'useFile' => __('Use this file', 'explorexr-ar-addon'),
        'modeRequired' => __('At least one AR mode must be selected.', 'explorexr-ar-addon'),
        'confirmRemove' => __('Are you sure you want to remove this file?', 'explorexr-ar-addon'),
        'uploadError' => __('Error uploading file. Please try again.', 'explorexr-ar-addon'),
        'selectingFile' => __('Selecting...', 'explorexr-ar-addon'),
        'maintenanceNonce' => wp_create_nonce('explorexr_premium_ar_maintenance') // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    ));
}
add_action('admin_enqueue_scripts', 'explorexr_premium_ar_addon_enqueue_enhanced_admin_scripts');

/**
 * Sync settings with addon manager options
 * 
 * This function ensures that changes to settings are reflected in the addon options system
 */
function explorexr_premium_ar_sync_settings_with_addon_options() {
    // Only proceed if addon manager exists
    if (!class_exists('ExploreXR_Addon_Manager')) {
        return;
    }
    
    $addon_manager = ExploreXR_Addon_Manager::get_instance();
    
    // Get current settings
    $settings = array(
        'explorexr_premium_ar_enabled' => true, // Always enabled for global settings
        'explorexr_premium_ar_button_text' => get_option('explorexr_premium_ar_button_text', ''),
        'explorexr_premium_ar_fallback_text' => get_option('explorexr_premium_ar_fallback_text', 'AR not supported on this device'),
        'explorexr_premium_ar_bg_color' => get_option('explorexr_premium_ar_bg_color', '#ffffff'),
        'explorexr_premium_ar_modes' => get_option('explorexr_premium_ar_modes', array('webxr', 'scene-viewer', 'quick-look')),
        'explorexr_premium_ar_scale' => get_option('explorexr_premium_ar_scale', 'auto'),
        'explorexr_premium_ar_placement' => get_option('explorexr_premium_ar_placement', 'floor'),
        'explorexr_premium_ar_button_image' => get_option('explorexr_premium_ar_button_image', ''),
        'explorexr_premium_ar_usdz_model' => get_option('explorexr_premium_ar_usdz_model', ''),
        'explorexr_premium_ar_xr_environment' => get_option('explorexr_premium_ar_xr_environment', ''),
        'explorexr_premium_ar_min_height' => get_option('explorexr_premium_ar_min_height', '400px')
    );
    
    // Update addon options
    $addon_manager->update_addon_options('explorexr-premium-ar', $settings);
}

/**
 * Hook into option updates to sync with addon manager
 */
function explorexr_premium_ar_option_update_hooks() {
    $ar_options = array(
        'explorexr_premium_ar_button_text',
        'explorexr_premium_ar_fallback_text',
        'explorexr_premium_ar_bg_color',
        'explorexr_premium_ar_modes',
        'explorexr_premium_ar_scale',
        'explorexr_premium_ar_placement',
        'explorexr_premium_ar_button_image',
        'explorexr_premium_ar_usdz_model',
        'explorexr_premium_ar_xr_environment',
        'explorexr_premium_ar_min_height'
    );
    
    foreach ($ar_options as $option) {
        add_action("update_option_$option", 'explorexr_premium_ar_sync_settings_with_addon_options', 10, 0);
        add_action("add_option_$option", 'explorexr_premium_ar_sync_settings_with_addon_options', 10, 0);
    }
}
add_action('init', 'explorexr_premium_ar_option_update_hooks');

/**
 * Load settings from addon options
 * 
 * This function ensures that the addon options are loaded into WordPress options
 * when the options page is loaded.
 */
function explorexr_premium_ar_load_settings_from_addon_options() {
    // Only run on settings page
    $screen = get_current_screen();
    if (!$screen || 'explorexr_premium_page_explorexr-premium-ar-settings' !== $screen->id) {
        return;
    }
    
    // Only proceed if addon manager exists
    if (!class_exists('ExploreXR_Addon_Manager')) {
        return;
    }
    
    $addon_manager = ExploreXR_Addon_Manager::get_instance();
    $addon_options = $addon_manager->get_addon_options('explorexr-premium-ar');
    
    if (!is_array($addon_options)) {
        return;
    }
    
    // Settings to sync and their defaults
    $settings_map = array(
        'explorexr_premium_ar_button_text' => '',
        'explorexr_premium_ar_fallback_text' => 'AR not supported on this device',
        'explorexr_premium_ar_bg_color' => '#ffffff',
        'explorexr_premium_ar_modes' => array('webxr', 'scene-viewer', 'quick-look'),
        'explorexr_premium_ar_scale' => 'auto',
        'explorexr_premium_ar_placement' => 'floor',
        'explorexr_premium_ar_button_image' => '',
        'explorexr_premium_ar_usdz_model' => '',
        'explorexr_premium_ar_xr_environment' => '',
        'explorexr_premium_ar_min_height' => '400px'
    );
    
    // Update WordPress options from addon options if they exist
    foreach ($settings_map as $option_name => $default_value) {
        if (isset($addon_options[$option_name])) {
            update_option($option_name, $addon_options[$option_name]);
        } else {
            // Ensure default exists
            add_option($option_name, $default_value);
        }
    }
}
add_action('current_screen', 'explorexr_premium_ar_load_settings_from_addon_options');

/**
 * Handle AR maintenance actions via AJAX
 */
function explorexr_premium_ar_handle_maintenance_action() {
	check_ajax_referer( 'explorexr_premium_ar_maintenance', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Insufficient permissions' );
	}

	$action = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : '';
    $result = array('success' => false, 'message' => '');
    
    switch ($action) {
        case 'reset_ar_options':
            $result = explorexr_premium_ar_reset_all_options();
            break;
            
        case 'clear_ar_cache':
            $result = explorexr_premium_ar_clear_ar_cache();
            break;
            
        case 'clear_model_cache':
            $result = explorexr_premium_ar_clear_model_cache();
            break;
            
        case 'flush_js_cache':
            $result = explorexr_premium_ar_flush_js_cache();
            break;
            
        default:
            $result['message'] = __('Invalid action specified.', 'explorexr-ar-addon');
    }
    
    wp_send_json($result);
}
add_action('wp_ajax_explorexr_premium_ar_maintenance', 'explorexr_premium_ar_handle_maintenance_action');

/**
 * Reset all AR options to defaults
 */
function explorexr_premium_ar_reset_all_options() {
    $default_options = array(
        'explorexr_premium_ar_button_text' => '',
        'explorexr_premium_ar_fallback_text' => 'AR not supported on this device',
        'explorexr_premium_ar_bg_color' => '#ffffff',
        'explorexr_premium_ar_modes' => array('webxr', 'scene-viewer', 'quick-look'),
        'explorexr_premium_ar_scale' => 'auto',
        'explorexr_premium_ar_placement' => 'floor',
        'explorexr_premium_ar_button_image' => '',
        'explorexr_premium_ar_usdz_model' => '',
        'explorexr_premium_ar_xr_environment' => '',
        'explorexr_premium_ar_min_height' => '400px'
    );
    
    try {
        // Reset WordPress options
        foreach ($default_options as $option => $default_value) {
            update_option($option, $default_value);
        }
        
        // Sync with addon manager if available
        if (class_exists('ExploreXR_Addon_Manager')) {
            $addon_manager = ExploreXR_Addon_Manager::get_instance();
            $addon_manager->update_addon_options('explorexr-premium-ar', $default_options);
        }
        
        return array(
            'success' => true,
            'message' => __('All AR options have been reset to their default values.', 'explorexr-ar-addon')
        );
        
    } catch (Exception $e) {
        return array(
            'success' => false,
            'message' => __('Error resetting AR options: ', 'explorexr-ar-addon') . $e->getMessage()
        );
    }
}

/**
 * Clear AR-specific cache
 */
function explorexr_premium_ar_clear_ar_cache() {
    try {
        $cleared_items = 0;
        
        // Clear WordPress transients related to AR
        global $wpdb;
        $ar_transients = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options}
                WHERE option_name LIKE %s OR option_name LIKE %s",
                $wpdb->esc_like( '_transient_explorexr_premium_ar_' ) . '%',
                $wpdb->esc_like( '_transient_timeout_explorexr_premium_ar_' ) . '%'
            )
        );
        
        foreach ($ar_transients as $transient) {
            $transient_name = str_replace(array('_transient_', '_transient_timeout_'), '', $transient->option_name);
            delete_transient($transient_name);
            $cleared_items++;
        }
        
        // Clear AR-specific upload cache
        $upload_dir = wp_upload_dir();
        $ar_cache_dir = $upload_dir['basedir'] . '/explorexr-premium-ar-cache/';
        
        if (is_dir($ar_cache_dir)) {
            $files = glob($ar_cache_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $cleared_items++;
                }
            }
        }
        
        // Clear object cache if available
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group('explorexr_premium_ar');
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('AR cache cleared successfully. %d items removed.', 'explorexr-ar-addon'), $cleared_items)
        );
        
    } catch (Exception $e) {
        return array(
            'success' => false,
            'message' => __('Error clearing AR cache: ', 'explorexr-ar-addon') . $e->getMessage()
        );
    }
}

/**
 * Clear model cache
 */
function explorexr_premium_ar_clear_model_cache() {
    try {
        $cleared_items = 0;
        
        // Clear WordPress transients related to models
        global $wpdb;
        $model_transients = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options}
                WHERE option_name LIKE %s OR option_name LIKE %s",
                $wpdb->esc_like( '_transient_explorexr_premium_model_' ) . '%',
                $wpdb->esc_like( '_transient_timeout_explorexr_premium_model_' ) . '%'
            )
        );
        
        foreach ($model_transients as $transient) {
            $transient_name = str_replace(array('_transient_', '_transient_timeout_'), '', $transient->option_name);
            delete_transient($transient_name);
            $cleared_items++;
        }
        
        // Clear model thumbnails and cache
        $upload_dir = wp_upload_dir();
        $model_cache_dir = $upload_dir['basedir'] . '/explorexr-premium-model-cache/';
        
        if (is_dir($model_cache_dir)) {
            $files = glob($model_cache_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $cleared_items++;
                }
            }
        }
        
        // Clear model-specific object cache
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group('explorexr_premium_models');
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('Model cache cleared successfully. %d items removed.', 'explorexr-ar-addon'), $cleared_items)
        );
        
    } catch (Exception $e) {
        return array(
            'success' => false,
            'message' => __('Error clearing model cache: ', 'explorexr-ar-addon') . $e->getMessage()
        );
    }
}

/**
 * Flush AR JavaScript cache
 */
function explorexr_premium_ar_flush_js_cache() {
    try {
        $cleared_items = 0;
        
        // Clear minified/combined JS files
        $upload_dir = wp_upload_dir();
        $js_cache_dir = $upload_dir['basedir'] . '/explorexr-premium-js-cache/';
        
        if (is_dir($js_cache_dir)) {
            $files = glob($js_cache_dir . '*.js');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $cleared_items++;
                }
            }
        }
        
        // Clear JS-related transients
        global $wpdb;
        $js_transients = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options}
                WHERE option_name LIKE %s OR option_name LIKE %s",
                $wpdb->esc_like( '_transient_explorexr_premium_js_' ) . '%',
                $wpdb->esc_like( '_transient_timeout_explorexr_premium_js_' ) . '%'
            )
        );
        
        foreach ($js_transients as $transient) {
            $transient_name = str_replace(array('_transient_', '_transient_timeout_'), '', $transient->option_name);
            delete_transient($transient_name);
            $cleared_items++;
        }
        
        // Force browser cache refresh by updating version numbers
        $ar_js_files = array(
            'ar-features.js',
            'ar-handler.js', 
            'ar-activation-control.js',
            'ar-enhanced.js',
            'ar-settings.js'
        );
        
        foreach ($ar_js_files as $file) {
            $version_option = 'explorexr_premium_ar_js_version_' . str_replace('.js', '', $file);
            update_option($version_option, time());
            $cleared_items++;
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('AR JavaScript cache flushed successfully. %d items cleared.', 'explorexr-ar-addon'), $cleared_items)
        );
        
    } catch (Exception $e) {
        return array(
            'success' => false,
            'message' => __('Error flushing JS cache: ', 'explorexr-ar-addon') . $e->getMessage()
        );
    }
}
