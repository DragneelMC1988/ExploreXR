<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register loading options settings
 */
function expoxr_loading_options_register_settings() {
    // Register core loading settings with sanitization
    register_setting('expoxr_loading_settings', 'expoxr_loading_display', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('expoxr_loading_settings', 'expoxr_large_model_handling', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('expoxr_loading_settings', 'expoxr_large_model_size_threshold', array(
        'sanitize_callback' => 'absint'
    ));
    register_setting('expoxr_loading_settings', 'expoxr_lazy_load_poster', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    
    // Add settings sections
    add_settings_section(
        'expoxr_loading_core_section',
        esc_html__('Core Loading Settings', 'explorexr'),
        'expoxr_loading_core_section_callback',
        'expoxr-loading-settings'
    );
    
    add_settings_section(
        'expoxr_loading_lazy_section',
        esc_html__('Lazy Loading Options', 'explorexr'),
        'expoxr_loading_lazy_section_callback',
        'expoxr-loading-settings'
    );
    
    add_settings_section(
        'expoxr_loading_large_section',
        esc_html__('Large Model Handling', 'explorexr'),
        'expoxr_loading_large_section_callback',
        'expoxr-loading-settings'
    );
    
    // Add settings fields for core section
    add_settings_field(
        'expoxr_loading_display',
        esc_html__('Display Type', 'explorexr'),
        'expoxr_loading_display_callback',
        'expoxr-loading-settings',
        'expoxr_loading_core_section'
    );
    
    // Add settings fields for lazy loading section
    add_settings_field(
        'expoxr_lazy_load_poster',
        esc_html__('Lazy Load Poster Images', 'explorexr'),
        'expoxr_lazy_load_poster_callback',
        'expoxr-loading-settings',
        'expoxr_loading_lazy_section'
    );
    
    // Add settings fields for large model section
    add_settings_field(
        'expoxr_large_model_size_threshold',
        esc_html__('Size Threshold (MB)', 'explorexr'),
        'expoxr_large_model_size_threshold_callback',
        'expoxr-loading-settings',
        'expoxr_loading_large_section'
    );
    
    add_settings_field(
        'expoxr_large_model_handling',
        esc_html__('Large Model Behavior', 'explorexr'),
        'expoxr_large_model_handling_callback',
        'expoxr-loading-settings',
        'expoxr_loading_large_section'
    );
}
add_action('admin_init', 'expoxr_loading_options_register_settings');

/**
 * Section callbacks
 */
function expoxr_loading_core_section_callback() {
    echo '<p>' . esc_html__('Configure essential loading behavior for your 3D models.', 'explorexr') . '</p>';
}

function expoxr_loading_lazy_section_callback() {
    echo '<p>' . esc_html__('Configure lazy loading behavior for faster initial page loading.', 'explorexr') . '</p>';
}

function expoxr_loading_large_section_callback() {
    echo '<p>' . esc_html__('Configure how to handle large 3D models that may cause slower loading times.', 'explorexr') . '</p>';
}

/**
 * Field callbacks
 */
function expoxr_loading_display_callback() {
    $loading_display = get_option('expoxr_loading_display', 'bar');
    ?>
    <fieldset>
        <label>
            <input type="radio" name="expoxr_loading_display" value="bar" <?php checked($loading_display, 'bar'); ?>>
            <?php esc_html_e('Loading Bar Only', 'explorexr'); ?>
        </label><br>
        
        <label>
            <input type="radio" name="expoxr_loading_display" value="percentage" <?php checked($loading_display, 'percentage'); ?>>
            <?php esc_html_e('Percentage Counter Only', 'explorexr'); ?>
        </label><br>
        
        <label>
            <input type="radio" name="expoxr_loading_display" value="both" <?php checked($loading_display, 'both'); ?>>
            <?php esc_html_e('Both Loading Bar and Percentage', 'explorexr'); ?>
        </label>
    </fieldset>
    <p class="description">
        <?php 
        printf(
            // translators: %s: Link to Premium version
            esc_html__('For more styling options and effects, consider upgrading to %s.', 'explorexr'),
            '<a href="' . esc_url(admin_url('admin.php?page=expoxr-premium')) . '">' . esc_html__('ExploreXR Premium', 'explorexr') . '</a>'
        ); ?>
    </p>
    <?php
}

function expoxr_lazy_load_poster_callback() {
    $lazy_load_poster = get_option('expoxr_lazy_load_poster', false);
    ?>
    <label for="expoxr_lazy_load_poster">
        <input type="checkbox" id="expoxr_lazy_load_poster" name="expoxr_lazy_load_poster" value="1" <?php checked($lazy_load_poster, true); ?>>
        <?php esc_html_e('Enable lazy loading for poster images', 'explorexr'); ?>
    </label>
    <p class="description"><?php esc_html_e('Only load poster images when they come into the viewport. Improves initial page load time.', 'explorexr'); ?></p>
    <?php
}

function expoxr_large_model_size_threshold_callback() {
    $large_model_size_threshold = get_option('expoxr_large_model_size_threshold', 16);
    ?>
    <input type="number" name="expoxr_large_model_size_threshold" value="<?php echo esc_attr($large_model_size_threshold); ?>" class="small-text" min="1" max="100">
    <p class="description"><?php esc_html_e('Models larger than this size (in MB) will be treated as large models.', 'explorexr'); ?></p>
    <?php
}

function expoxr_large_model_handling_callback() {
    $large_model_handling = get_option('expoxr_large_model_handling', 'direct');
    ?>
    <fieldset>
        <label>
            <input type="radio" name="expoxr_large_model_handling" value="direct" <?php checked($large_model_handling, 'direct'); ?>>
            <?php esc_html_e('Load directly (Default)', 'explorexr'); ?>
        </label>
        <p class="description"><?php esc_html_e('Always load models directly, regardless of size.', 'explorexr'); ?></p>
        
        <br><br>
        
        <label>
            <input type="radio" name="expoxr_large_model_handling" value="poster_button" <?php checked($large_model_handling, 'poster_button'); ?>>
            <?php esc_html_e('Show poster with load button', 'explorexr'); ?>
        </label>
        <p class="description"><?php esc_html_e('For large models, show a poster image with a button to load the model.', 'explorexr'); ?></p>
        
        <br><br>
        
        <label>
            <input type="radio" name="expoxr_large_model_handling" value="lazy" <?php checked($large_model_handling, 'lazy'); ?>>
            <?php esc_html_e('Lazy load when visible', 'explorexr'); ?>
        </label>
        <p class="description"><?php esc_html_e('Only load models when they are about to enter the viewport.', 'explorexr'); ?></p>
    </fieldset>
    <?php
}

// Loading Options page callback using standardized UI
function expoxr_loading_options_page() {
    // Check if user has permissions
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'explorexr'));
    }
    
    // Process loading settings form submission
    if (isset($_POST['expoxr_action']) && $_POST['expoxr_action'] === 'save_loading_options' && isset($_POST['expoxr_loading_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['expoxr_loading_nonce'])), 'expoxr_loading_settings')) {
        // Process loading settings fields
        if (isset($_POST['expoxr_loading_display'])) {
            update_option('expoxr_loading_display', sanitize_text_field(wp_unslash($_POST['expoxr_loading_display'])));
        }
        if (isset($_POST['expoxr_large_model_handling'])) {
            update_option('expoxr_large_model_handling', sanitize_text_field(wp_unslash($_POST['expoxr_large_model_handling'])));
        }
        if (isset($_POST['expoxr_large_model_size_threshold'])) {
            $threshold = absint($_POST['expoxr_large_model_size_threshold']);
            if ($threshold > 0) {
                update_option('expoxr_large_model_size_threshold', $threshold);
            }
        }
        // Handle checkbox for lazy load poster
        if (isset($_POST['expoxr_lazy_load_poster']) && $_POST['expoxr_lazy_load_poster'] === '1') {
            update_option('expoxr_lazy_load_poster', true);
        } else {
            update_option('expoxr_lazy_load_poster', false);
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>Loading options have been saved successfully!</p></div>';
    }
    
    // Check if Loading Add-On is active and show notice
    $notice_content = '';
    
    // Prepare settings page structure using the standardized template
    $settings_args = array(
        'page_title'    => esc_html__('Loading Options', 'explorexr'),
        'plugin_name'   => esc_html__('ExploreXR', 'explorexr'),
        'plugin_version' => defined('EXPOXR_VERSION') ? EXPOXR_VERSION : '1.0.0',
        'doc_url'       => 'https://expoxr.com/explorexr/documentation/loading-options',
        'settings_group' => 'expoxr_loading_settings',
        'settings_page' => 'expoxr-loading-settings',
        'show_submit'   => true,
        'sections'      => array(
            array(
                'title'       => esc_html__('Customize Loading Experience', 'explorexr'),
                'description' => esc_html__('Customize how models appear while loading to provide the best experience for your users.', 'explorexr'),
                'icon'        => 'performance',
                'content'     => $notice_content,
                'section_id'  => 'expoxr_loading_core_section'
            ),
            array(
                'title'       => esc_html__('Lazy Loading Options', 'explorexr'),
                'description' => esc_html__('Configure lazy loading behavior for faster initial page loading.', 'explorexr'),
                'icon'        => 'clock',
                'section_id'  => 'expoxr_loading_lazy_section'
            ),
            array(
                'title'       => esc_html__('Large Model Handling', 'explorexr'),
                'description' => esc_html__('Configure how to handle large 3D models that may cause slower loading times.', 'explorexr'),
                'icon'        => 'admin-settings',
                'section_id'  => 'expoxr_loading_large_section'
            )
        )
    );
    
    // Render the loading options page
    // Set up header variables
    $page_title = 'Loading Options';
    $header_actions = '<a href="https://expoxr.com/explorexr/documentation/loading-options" target="_blank" class="button">
                        <span class="dashicons dashicons-book"></span> Documentation
                      </a>';
    ?>
    <div class="wrap expoxr-admin-container">
        <!-- WordPress admin notices appear here automatically before our custom content -->
        
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; ?>
        
        <!-- Loading Options Settings -->
        <?php
        $card_title = 'Loading Options';
        $card_icon = 'performance';
        ob_start();
        ?>
        <p>Configure how 3D models are loaded and displayed on your website. These settings help optimize performance and user experience.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('expoxr_loading_settings', 'expoxr_loading_nonce'); ?>
            <input type="hidden" name="expoxr_action" value="save_loading_options">
            
            <?php
            // Get current option values
            $loading_display = get_option('expoxr_loading_display', 'bar');
            $large_model_handling = get_option('expoxr_large_model_handling', 'direct');
            $large_model_size_threshold = get_option('expoxr_large_model_size_threshold', 16);
            $lazy_load_poster = get_option('expoxr_lazy_load_poster', false);
            ?>
            
            <h3><?php esc_html_e('Core Loading Settings', 'explorexr'); ?></h3>
            <p><?php esc_html_e('Configure essential loading behavior for your 3D models.', 'explorexr'); ?></p>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="expoxr_loading_display"><?php esc_html_e('Display Type', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="expoxr_loading_display" id="expoxr_loading_display_bar" value="bar" <?php checked($loading_display, 'bar'); ?>>
                                <?php esc_html_e('Loading Bar Only', 'explorexr'); ?>
                            </label><br>
                            
                            <label>
                                <input type="radio" name="expoxr_loading_display" id="expoxr_loading_display_percentage" value="percentage" <?php checked($loading_display, 'percentage'); ?>>
                                <?php esc_html_e('Percentage Counter Only', 'explorexr'); ?>
                            </label><br>
                            
                            <label>
                                <input type="radio" name="expoxr_loading_display" id="expoxr_loading_display_both" value="both" <?php checked($loading_display, 'both'); ?>>
                                <?php esc_html_e('Both Loading Bar and Percentage', 'explorexr'); ?>
                            </label>
                        </fieldset>
                        <p class="description">
                            <?php 
                            printf(
                                // translators: %s: Link to Premium version
                                esc_html__('For more styling options and effects, consider upgrading to %s.', 'explorexr'),
                                '<a href="' . esc_url(admin_url('admin.php?page=expoxr-premium')) . '">' . esc_html__('ExploreXR Premium', 'explorexr') . '</a>'
                            ); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Lazy Loading Options', 'explorexr'); ?></h3>
            <p><?php esc_html_e('Configure lazy loading behavior for faster initial page loading.', 'explorexr'); ?></p>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="expoxr_lazy_load_poster"><?php esc_html_e('Lazy Load Poster Images', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="expoxr_lazy_load_poster" name="expoxr_lazy_load_poster" value="1" <?php checked($lazy_load_poster, true); ?>>
                        <label for="expoxr_lazy_load_poster"><?php esc_html_e('Enable lazy loading for poster images', 'explorexr'); ?></label>
                        <p class="description"><?php esc_html_e('Poster images will only load when they are about to enter the viewport, improving initial page load times.', 'explorexr'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Large Model Handling', 'explorexr'); ?></h3>
            <p><?php esc_html_e('Configure how to handle large 3D models that may cause slower loading times.', 'explorexr'); ?></p>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="expoxr_large_model_size_threshold"><?php esc_html_e('Size Threshold (MB)', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="expoxr_large_model_size_threshold" id="expoxr_large_model_size_threshold" value="<?php echo esc_attr($large_model_size_threshold); ?>" class="small-text" min="1" max="100"> MB
                        <p class="description"><?php esc_html_e('Models larger than this size will be treated as "large models" and use the behavior settings below.', 'explorexr'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="expoxr_large_model_handling"><?php esc_html_e('Large Model Behavior', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="expoxr_large_model_handling" id="expoxr_large_model_handling_direct" value="direct" <?php checked($large_model_handling, 'direct'); ?>>
                                <?php esc_html_e('Load directly (Default)', 'explorexr'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Always load models directly, regardless of size.', 'explorexr'); ?></p>
                            
                            <br><br>
                            
                            <label>
                                <input type="radio" name="expoxr_large_model_handling" id="expoxr_large_model_handling_poster" value="poster_button" <?php checked($large_model_handling, 'poster_button'); ?>>
                                <?php esc_html_e('Show poster with load button', 'explorexr'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('For large models, show a poster image with a button to load the model.', 'explorexr'); ?></p>
                            
                            <br><br>
                            
                            <label>
                                <input type="radio" name="expoxr_large_model_handling" id="expoxr_large_model_handling_lazy" value="lazy" <?php checked($large_model_handling, 'lazy'); ?>>
                                <?php esc_html_e('Lazy load when visible', 'explorexr'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Only load models when they are about to enter the viewport.', 'explorexr'); ?></p>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Save Loading Options'); ?>
        </form>
        <?php
        $card_content = ob_get_clean();
        include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
        
        <!-- ExpoXR Footer -->
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    </div>
    <?php
}





