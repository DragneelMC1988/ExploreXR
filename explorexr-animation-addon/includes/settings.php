<?php
/**
 * ExploreXR Animation Add-On - Settings
 * 
 * Handles the settings page and options for the animation add-on.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register animation settings
 */
function explorexr_premium_animation_register_settings() {
    // Register global animation settings
    register_setting(
        'explorexr_premium_animation_settings',
        'explorexr_premium_animation_show_controls',
        array(
            'type' => 'string',
            'default' => 'on',
            'sanitize_callback' => 'sanitize_text_field'
        )
    );
}

/**
 * Render the animation settings page
 */
function explorexr_premium_animation_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get current settings
    $show_controls = get_option('explorexr_premium_animation_show_controls', 'on') === 'on';
    
    // Save settings if form is submitted
    if (isset($_POST['submit'])) {
        // Check nonce
        check_admin_referer('explorexr_premium_animation_settings');
        
        // Update settings
        $show_controls = isset($_POST['explorexr_premium_animation_show_controls']) ? 'on' : 'off';
        update_option('explorexr_premium_animation_show_controls', $show_controls);
        
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'explorexr-animation-addon') . '</p></div>';
    }

    // Prepare content for the general animation settings card
    ob_start();
    ?>
    <div class="explorexr-premium-setting-field">
        <label>
            <input type="checkbox" name="explorexr_premium_animation_show_controls" <?php checked($show_controls); ?>>
            <?php echo esc_html__('Show Animation Controls', 'explorexr-animation-addon'); ?>
        </label>
        <p class="description"><?php echo esc_html__('Display animation controls on the model viewer.', 'explorexr-animation-addon'); ?></p>
    </div>
    
    <div class="explorexr-premium-setting-field explorexr-premium-info-box">
        <h4><?php echo esc_html__('How to Implement Animations in Models', 'explorexr-animation-addon'); ?></h4>
        <p><?php echo esc_html__('To use animations in your 3D models:', 'explorexr-animation-addon'); ?></p>
        <ol>
            <li><?php echo esc_html__('Ensure your model contains animations created in your 3D modeling software.', 'explorexr-animation-addon'); ?></li>
            <li><?php echo esc_html__('Upload your model to ExploreXR.', 'explorexr-animation-addon'); ?></li>
            <li><?php echo esc_html__('Go to Edit Model page and enable the animation option.', 'explorexr-animation-addon'); ?></li>
            <li><?php echo esc_html__('Configure animation settings including Ping Pong mode and Crossfade Duration.', 'explorexr-animation-addon'); ?></li>
            <li><?php echo esc_html__('Save your changes and preview the animation.', 'explorexr-animation-addon'); ?></li>
        </ol>
        <p><?php echo esc_html__('Note: The animation settings for each model can be found in the Edit Model page.', 'explorexr-animation-addon'); ?></p>
    </div>
    <?php
    $general_content = ob_get_clean();
    
    // Prepare settings arguments
    $settings_args = array(
        'page_title'    => __('Animation Settings', 'explorexr-animation-addon'),
        'addon_name'    => __('Animation Add-On', 'explorexr-animation-addon'),
        'addon_version' => defined('explorexr_premium_ANIMATION_VERSION') ? explorexr_premium_ANIMATION_VERSION : '1.0.0',
        'doc_url'       => 'https://docs.explorexr.com/animation-addon',
        'show_submit'   => true,
        'sections'      => array(
            array(
                'title'       => __('General Animation Settings', 'explorexr-animation-addon'),
                'description' => __('Configure animation behavior for your 3D models.', 'explorexr-animation-addon'),
                'icon'        => 'controls-play',
                'content'     => $general_content
            )
        )
    );
    
    // Add nonce field to content
    wp_nonce_field( 'explorexr_premium_animation_settings', '_wpnonce' );    
    // Render the standardized settings page
    explorexr_premium_render_addon_settings_page($settings_args);
}
