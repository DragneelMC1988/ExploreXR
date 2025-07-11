<?php
/**
 * ExploreXR Loading Options
 * 
 * Handles loading options for 3D models.
 * 
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register loading options settings
 */
function expoxr_register_loading_options_settings() {
    // Register loading type setting
    register_setting(
        'expoxr_options',
        'expoxr_loading_type',
        array(
            'type' => 'string',
            'default' => 'both',
            'sanitize_callback' => 'sanitize_text_field'
        )
    );
    
    // Register loading color setting (for free version this is set and not changeable)
    register_setting(
        'expoxr_options',
        'expoxr_loading_color',
        array(
            'type' => 'string',
            'default' => '#1e88e5',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
}
add_action('admin_init', 'expoxr_register_loading_options_settings');

/**
 * Get loading options from settings
 */
function expoxr_get_loading_options() {
    $options = array(
        'loading_type' => get_option('expoxr_loading_type', 'both'),
        'loading_color' => get_option('expoxr_loading_color', '#1e88e5'), 
        'show_progress_bar' => true,
        'show_percentage' => true,
        'version' => EXPOXR_VERSION,
        'debug_mode' => get_option('expoxr_debug_mode', false),
        'timestamp' => time() // Add timestamp for cache busting
    );
    
    // Adjust flags based on loading type
    if ($options['loading_type'] === 'bar') {
        $options['show_percentage'] = false;
    } elseif ($options['loading_type'] === 'percentage') {
        $options['show_progress_bar'] = false;
    }
    
    return $options;
}

/**
 * Add the loading options to the page admin
 */
function expoxr_add_loading_options_page() {
    add_submenu_page(
        'expoxr-dashboard',
        __('Loading Options', 'explorexr'),
        __('Loading Options', 'explorexr'),
        'manage_options',
        'expoxr-loading-options',
        'expoxr_render_loading_options_page'
    );
}
add_action('admin_menu', 'expoxr_add_loading_options_page', 30);

/**
 * Render loading options admin page
 */
function expoxr_render_loading_options_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Get current options
    $loading_type = get_option('expoxr_loading_type', 'both');
    
    // Save settings if form is submitted
    if (isset($_POST['expoxr_save_loading_options']) && check_admin_referer('expoxr_loading_options_nonce')) {
        $loading_type = sanitize_text_field($_POST['expoxr_loading_type']);
        
        // Save options
        update_option('expoxr_loading_type', $loading_type);
        
        // Show success message
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Loading options saved.', 'explorexr') . '</p></div>';
    }
    
    // Enqueue scripts and styles
    wp_enqueue_style('expoxr-admin-styles', EXPOXR_PLUGIN_URL . 'admin/css/loading-options.css', array(), EXPOXR_VERSION);
    wp_enqueue_script('expoxr-loading-options', EXPOXR_PLUGIN_URL . 'admin/js/loading-options.js', array('jquery'), EXPOXR_VERSION, true);
    
    ?>
    <div class="wrap expoxr-loading-options-page">
        <h1><?php esc_html_e('3D Model Loading Options', 'explorexr'); ?></h1>
        
        <div class="expoxr-loading-preview-container">
            <h2><?php esc_html_e('Preview', 'explorexr'); ?></h2>
            <div class="expoxr-loading-preview" data-loading-type="<?php echo esc_attr($loading_type); ?>">
                <div class="expoxr-loading-progress-bar">
                    <div class="expoxr-loading-progress" style="width: 75%;"></div>
                </div>
                <div class="expoxr-loading-percentage">75%</div>
            </div>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('expoxr_loading_options_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Loading Type', 'explorexr'); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e('Loading Type', 'explorexr'); ?></legend>
                            <p>
                                <label>
                                    <input type="radio" name="expoxr_loading_type" value="bar" <?php checked($loading_type, 'bar'); ?>>
                                    <?php esc_html_e('Progress Bar Only', 'explorexr'); ?>
                                </label>
                            </p>
                            <p>
                                <label>
                                    <input type="radio" name="expoxr_loading_type" value="percentage" <?php checked($loading_type, 'percentage'); ?>>
                                    <?php esc_html_e('Percentage Only', 'explorexr'); ?>
                                </label>
                            </p>
                            <p>
                                <label>
                                    <input type="radio" name="expoxr_loading_type" value="both" <?php checked($loading_type, 'both'); ?>>
                                    <?php esc_html_e('Both Progress Bar and Percentage', 'explorexr'); ?>
                                </label>
                            </p>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Loading Color', 'explorexr'); ?></th>
                    <td>
                        <p class="expoxr-version-notice">
                            <?php esc_html_e('Custom loading colors are available in the premium version.', 'explorexr'); ?>
                            <span class="expoxr-loading-color-preview" style="background-color: #1e88e5;"></span>
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="expoxr_save_loading_options" class="button button-primary" value="<?php esc_attr_e('Save Options', 'explorexr'); ?>">
            </p>
        </form>
    </div>
    <?php
}

/**
 * Add loading options to model-viewer element
 */
function expoxr_add_loading_options_to_model_viewer($attributes, $model_id = null) {
    $loading_options = expoxr_get_loading_options();
    
    // Set loading-type data attribute
    $attributes['data-loading-type'] = $loading_options['loading_type'];
    
    // Set loading-color data attribute
    $attributes['data-loading-color'] = $loading_options['loading_color'];
    
    return $attributes;
}
add_filter('expoxr_model_viewer_attributes', 'expoxr_add_loading_options_to_model_viewer', 10, 2);





