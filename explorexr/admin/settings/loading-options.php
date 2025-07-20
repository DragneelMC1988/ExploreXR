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
function explorexr_register_loading_options_settings() {
    // Register loading type setting
    register_setting(
        'explorexr_options',
        'explorexr_loading_type',
        array(
            'type' => 'string',
            'default' => 'both',
            'sanitize_callback' => 'sanitize_text_field'
        )
    );
    
    // Register loading color setting (for free version this is set and not changeable)
    register_setting(
        'explorexr_options',
        'explorexr_loading_color',
        array(
            'type' => 'string',
            'default' => '#1e88e5',
            'sanitize_callback' => 'explorexr_sanitize_hex_color'
        )
    );
}
add_action('admin_init', 'explorexr_register_loading_options_settings');

/**
 * Get loading options from settings
 */
function explorexr_get_loading_options() {
    $options = array(
        'loading_type' => get_option('explorexr_loading_type', 'both'),
        'loading_color' => get_option('explorexr_loading_color', '#1e88e5'), 
        'show_progress_bar' => true,
        'show_percentage' => true,
        'version' => EXPLOREXR_VERSION,
        'debug_mode' => get_option('explorexr_debug_mode', false),
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
function explorexr_add_loading_options_page() {
    add_submenu_page(
        'explorexr-dashboard',
        __('Loading Options', 'explorexr'),
        __('Loading Options', 'explorexr'),
        'manage_options',
        'explorexr-loading-options',
        'explorexr_render_loading_options_page'
    );
}
add_action('admin_menu', 'explorexr_add_loading_options_page', 30);

/**
 * Render loading options admin page
 */
function explorexr_render_loading_options_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Get current options
    $loading_type = get_option('explorexr_loading_type', 'both');
    
    // Save settings if form is submitted
    if (isset($_POST['explorexr_save_loading_options']) && check_admin_referer('explorexr_loading_options_nonce')) {
        $loading_type = isset($_POST['explorexr_loading_type']) ? sanitize_text_field(wp_unslash($_POST['explorexr_loading_type'])) : 'both';
        
        // Save options
        update_option('explorexr_loading_type', $loading_type);
        
        // Show success message
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Loading options saved.', 'explorexr') . '</p></div>';
    }
    
    // Enqueue scripts and styles
    wp_enqueue_style('explorexr-admin-styles', EXPLOREXR_PLUGIN_URL . 'admin/css/loading-options.css', array(), EXPLOREXR_VERSION);
    wp_enqueue_script('explorexr-loading-options', EXPLOREXR_PLUGIN_URL . 'admin/js/loading-options.js', array('jquery'), EXPLOREXR_VERSION, true);
    
    ?>
    <div class="wrap explorexr-loading-options-page">
        <h1><?php esc_html_e('3D Model Loading Options', 'explorexr'); ?></h1>
        
        <div class="explorexr-loading-preview-container">
            <h2><?php esc_html_e('Preview', 'explorexr'); ?></h2>
            <div class="explorexr-loading-preview" data-loading-type="<?php echo esc_attr($loading_type); ?>">
                <div class="explorexr-loading-progress-bar">
                    <div class="explorexr-loading-progress" style="width: 75%;"></div>
                </div>
                <div class="explorexr-loading-percentage">75%</div>
            </div>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('explorexr_loading_options_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Loading Type', 'explorexr'); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e('Loading Type', 'explorexr'); ?></legend>
                            <p>
                                <label>
                                    <input type="radio" name="explorexr_loading_type" value="bar" <?php checked($loading_type, 'bar'); ?>>
                                    <?php esc_html_e('Progress Bar Only', 'explorexr'); ?>
                                </label>
                            </p>
                            <p>
                                <label>
                                    <input type="radio" name="explorexr_loading_type" value="percentage" <?php checked($loading_type, 'percentage'); ?>>
                                    <?php esc_html_e('Percentage Only', 'explorexr'); ?>
                                </label>
                            </p>
                            <p>
                                <label>
                                    <input type="radio" name="explorexr_loading_type" value="both" <?php checked($loading_type, 'both'); ?>>
                                    <?php esc_html_e('Both Progress Bar and Percentage', 'explorexr'); ?>
                                </label>
                            </p>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Loading Color', 'explorexr'); ?></th>
                    <td>
                        <p class="explorexr-version-notice">
                            <?php esc_html_e('Custom loading colors are available in the premium version.', 'explorexr'); ?>
                            <span class="explorexr-loading-color-preview" style="background-color: #1e88e5;"></span>
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="explorexr_save_loading_options" class="button button-primary" value="<?php esc_attr_e('Save Options', 'explorexr'); ?>">
            </p>
        </form>
    </div>
    <?php
}

/**
 * Add loading options to model-viewer element
 */
function explorexr_add_loading_options_to_model_viewer($attributes, $model_id = null) {
    $loading_options = explorexr_get_loading_options();
    
    // Set loading-type data attribute
    $attributes['data-loading-type'] = $loading_options['loading_type'];
    
    // Set loading-color data attribute
    $attributes['data-loading-color'] = $loading_options['loading_color'];
    
    return $attributes;
}
add_filter('explorexr_model_viewer_attributes', 'explorexr_add_loading_options_to_model_viewer', 10, 2);





