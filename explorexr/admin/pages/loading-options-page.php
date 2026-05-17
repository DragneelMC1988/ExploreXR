<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitize a color value for the Load Model button. Accepts hex (#abc / #aabbcc)
 * and falls back to the supplied default when invalid.
 */
function explorexr_sanitize_load_button_color($value) {
    if (function_exists('explorexr_sanitize_hex_color')) {
        return explorexr_sanitize_hex_color($value, '');
    }
    $value = is_string($value) ? trim($value) : '';
    if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
        return $value;
    }
    return '';
}

/**
 * Sanitize a CSS length value for the Load Model button radius (e.g. 4px, 50%, 0.5rem).
 */
function explorexr_sanitize_load_button_radius($value) {
    $value = is_string($value) ? trim($value) : '';
    if ($value === '') {
        return '';
    }
    if (preg_match('/^\d{1,3}(\.\d+)?(px|%|em|rem)$/', $value)) {
        return $value;
    }
    return '';
}

/**
 * Register loading options settings
 */
function explorexr_loading_options_register_settings() {
    // Register core loading settings with sanitization
    register_setting('explorexr_loading_settings', 'explorexr_loading_display', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('explorexr_loading_settings', 'explorexr_large_model_handling', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('explorexr_loading_settings', 'explorexr_large_model_size_threshold', array(
        'sanitize_callback' => 'absint'
    ));
    register_setting('explorexr_loading_settings', 'explorexr_lazy_load_poster', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));

    // Load Model Button customization settings
    register_setting('explorexr_loading_settings', 'explorexr_load_button_text', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('explorexr_loading_settings', 'explorexr_load_button_bg', array(
        'sanitize_callback' => 'explorexr_sanitize_load_button_color'
    ));
    register_setting('explorexr_loading_settings', 'explorexr_load_button_color', array(
        'sanitize_callback' => 'explorexr_sanitize_load_button_color'
    ));
    register_setting('explorexr_loading_settings', 'explorexr_load_button_hover_bg', array(
        'sanitize_callback' => 'explorexr_sanitize_load_button_color'
    ));
    register_setting('explorexr_loading_settings', 'explorexr_load_button_hover_color', array(
        'sanitize_callback' => 'explorexr_sanitize_load_button_color'
    ));
    register_setting('explorexr_loading_settings', 'explorexr_load_button_radius', array(
        'sanitize_callback' => 'explorexr_sanitize_load_button_radius'
    ));

    // Add settings sections
    add_settings_section(
        'explorexr_loading_core_section',
        esc_html__('Core Loading Settings', 'explorexr'),
        'explorexr_loading_core_section_callback',
        'explorexr-loading-settings'
    );
    
    add_settings_section(
        'explorexr_loading_lazy_section',
        esc_html__('Lazy Loading Options', 'explorexr'),
        'explorexr_loading_lazy_section_callback',
        'explorexr-loading-settings'
    );
    
    add_settings_section(
        'explorexr_loading_large_section',
        esc_html__('Large Model Handling', 'explorexr'),
        'explorexr_loading_large_section_callback',
        'explorexr-loading-settings'
    );
    
    // Add settings fields for core section
    add_settings_field(
        'explorexr_loading_display',
        esc_html__('Display Type', 'explorexr'),
        'explorexr_loading_display_callback',
        'explorexr-loading-settings',
        'explorexr_loading_core_section'
    );
    
    // Add settings fields for lazy loading section
    add_settings_field(
        'explorexr_lazy_load_poster',
        esc_html__('Lazy Load Poster Images', 'explorexr'),
        'explorexr_lazy_load_poster_callback',
        'explorexr-loading-settings',
        'explorexr_loading_lazy_section'
    );
    
    // Add settings fields for large model section
    add_settings_field(
        'explorexr_large_model_size_threshold',
        esc_html__('Size Threshold (MB)', 'explorexr'),
        'explorexr_large_model_size_threshold_callback',
        'explorexr-loading-settings',
        'explorexr_loading_large_section'
    );
    
    add_settings_field(
        'explorexr_large_model_handling',
        esc_html__('Large Model Behavior', 'explorexr'),
        'explorexr_large_model_handling_callback',
        'explorexr-loading-settings',
        'explorexr_loading_large_section'
    );
}
add_action('admin_init', 'explorexr_loading_options_register_settings');

/**
 * Section callbacks
 */
function explorexr_loading_core_section_callback() {
    echo '<p>' . esc_html__('Configure essential loading behavior for your 3D models.', 'explorexr') . '</p>';
}

function explorexr_loading_lazy_section_callback() {
    echo '<p>' . esc_html__('Configure lazy loading behavior for faster initial page loading.', 'explorexr') . '</p>';
}

function explorexr_loading_large_section_callback() {
    echo '<p>' . esc_html__('Configure how to handle large 3D models that may cause slower loading times.', 'explorexr') . '</p>';
}

/**
 * Field callbacks
 */
function explorexr_loading_display_callback() {
    $loading_display = get_option('explorexr_loading_display', 'bar');
    ?>
    <fieldset>
        <label>
            <input type="radio" name="explorexr_loading_display" value="bar" <?php checked($loading_display, 'bar'); ?>>
            <?php esc_html_e('Loading Bar Only', 'explorexr'); ?>
        </label><br>
        
        <label>
            <input type="radio" name="explorexr_loading_display" value="percentage" <?php checked($loading_display, 'percentage'); ?>>
            <?php esc_html_e('Percentage Counter Only', 'explorexr'); ?>
        </label><br>
        
        <label>
            <input type="radio" name="explorexr_loading_display" value="both" <?php checked($loading_display, 'both'); ?>>
            <?php esc_html_e('Both Loading Bar and Percentage', 'explorexr'); ?>
        </label>
    </fieldset>
    <p class="description">
        <?php 
        printf(
            // translators: %s: Link to Premium version
            esc_html__('For more styling options and effects, consider using the Loading Addon %s.', 'explorexr'),
            '<a href="' . esc_url(admin_url('admin.php?page=explorexr-addons')) . '">' . esc_html__('ExploreXR Addons', 'explorexr') . '</a>'
        ); ?>
    </p>
    <?php
}

function explorexr_lazy_load_poster_callback() {
    $lazy_load_poster = get_option('explorexr_lazy_load_poster', false);
    ?>
    <label for="explorexr_lazy_load_poster">
        <input type="checkbox" id="explorexr_lazy_load_poster" name="explorexr_lazy_load_poster" value="1" <?php checked($lazy_load_poster, true); ?>>
        <?php esc_html_e('Enable lazy loading for poster images', 'explorexr'); ?>
    </label>
    <p class="description"><?php esc_html_e('Only load poster images when they come into the viewport. Improves initial page load time.', 'explorexr'); ?></p>
    <?php
}

function explorexr_large_model_size_threshold_callback() {
    $large_model_size_threshold = get_option('explorexr_large_model_size_threshold', 16);
    ?>
    <input type="number" name="explorexr_large_model_size_threshold" value="<?php echo esc_attr($large_model_size_threshold); ?>" class="small-text" min="1" max="100">
    <p class="description"><?php esc_html_e('Models larger than this size (in MB) will be treated as large models.', 'explorexr'); ?></p>
    <?php
}

function explorexr_large_model_handling_callback() {
    $large_model_handling = get_option('explorexr_large_model_handling', 'direct');
    ?>
    <fieldset>
        <label>
            <input type="radio" name="explorexr_large_model_handling" value="direct" <?php checked($large_model_handling, 'direct'); ?>>
            <?php esc_html_e('Load directly (Default)', 'explorexr'); ?>
        </label>
        <p class="description"><?php esc_html_e('Always load models directly, regardless of size.', 'explorexr'); ?></p>
        
        <br><br>
        
        <label>
            <input type="radio" name="explorexr_large_model_handling" value="poster_button" <?php checked($large_model_handling, 'poster_button'); ?>>
            <?php esc_html_e('Show poster with load button', 'explorexr'); ?>
        </label>
        <p class="description"><?php esc_html_e('For large models, show a poster image with a button to load the model.', 'explorexr'); ?></p>
        
        <br><br>
        
        <label>
            <input type="radio" name="explorexr_large_model_handling" value="lazy" <?php checked($large_model_handling, 'lazy'); ?>>
            <?php esc_html_e('Lazy load when visible', 'explorexr'); ?>
        </label>
        <p class="description"><?php esc_html_e('Only load models when they are about to enter the viewport.', 'explorexr'); ?></p>
    </fieldset>
    <?php
}

// Loading Options page callback using standardized UI
function explorexr_loading_options_page() {
    // Check if user has permissions
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'explorexr'));
    }
    
    // Process loading settings form submission
    if (isset($_POST['explorexr_action']) && $_POST['explorexr_action'] === 'save_loading_options' && isset($_POST['explorexr_loading_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['explorexr_loading_nonce'])), 'explorexr_loading_settings')) {
        // Process loading settings fields
        if (isset($_POST['explorexr_loading_display'])) {
            update_option('explorexr_loading_display', sanitize_text_field(wp_unslash($_POST['explorexr_loading_display'])));
        }
        if (isset($_POST['explorexr_large_model_handling'])) {
            update_option('explorexr_large_model_handling', sanitize_text_field(wp_unslash($_POST['explorexr_large_model_handling'])));
        }
        if (isset($_POST['explorexr_large_model_size_threshold'])) {
            $threshold = absint($_POST['explorexr_large_model_size_threshold']);
            if ($threshold > 0) {
                update_option('explorexr_large_model_size_threshold', $threshold);
            }
        }
        // Handle checkbox for lazy load poster
        if (isset($_POST['explorexr_lazy_load_poster']) && $_POST['explorexr_lazy_load_poster'] === '1') {
            update_option('explorexr_lazy_load_poster', true);
        } else {
            update_option('explorexr_lazy_load_poster', false);
        }

        // Load Model button customization
        if (isset($_POST['explorexr_load_button_text'])) {
            update_option('explorexr_load_button_text', sanitize_text_field(wp_unslash($_POST['explorexr_load_button_text'])));
        }
        $explorexr_button_color_fields = array(
            'explorexr_load_button_bg',
            'explorexr_load_button_color',
            'explorexr_load_button_hover_bg',
            'explorexr_load_button_hover_color',
        );
        foreach ($explorexr_button_color_fields as $explorexr_color_field) {
            if (isset($_POST[$explorexr_color_field])) {
                update_option(
                    $explorexr_color_field,
                    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- custom hex sanitization via explorexr_sanitize_load_button_color()
                explorexr_sanitize_load_button_color(wp_unslash($_POST[$explorexr_color_field]))
                );
            }
        }
        if (isset($_POST['explorexr_load_button_radius'])) {
            update_option(
                'explorexr_load_button_radius',
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- custom CSS length sanitization via explorexr_sanitize_load_button_radius()
                explorexr_sanitize_load_button_radius(wp_unslash($_POST['explorexr_load_button_radius']))
            );
        }

        echo '<div class="notice notice-success is-dismissible"><p>Loading options have been saved successfully!</p></div>';
    }
    
    // Loading options for free version
    $notice_content = '';
    
    // Prepare settings page structure using the standardized template
    $settings_args = array(
        'page_title'    => esc_html__('Loading Options', 'explorexr'),
        'plugin_name'   => esc_html__('explorexr', 'explorexr'),
        'plugin_version' => defined('EXPLOREXR_VERSION') ? EXPLOREXR_VERSION : '1.0.1',
        'doc_url'       => 'https://expoxr.com/explorexr/documentation/',
        'settings_group' => 'explorexr_loading_settings',
        'settings_page' => 'explorexr-loading-settings',
        'show_submit'   => true,
        'sections'      => array(
            array(
                'title'       => esc_html__('Customize Loading Experience', 'explorexr'),
                'description' => esc_html__('Customize how models appear while loading to provide the best experience for your users.', 'explorexr'),
                'icon'        => 'performance',
                'content'     => $notice_content,
                'section_id'  => 'explorexr_loading_core_section'
            ),
            array(
                'title'       => esc_html__('Lazy Loading Options', 'explorexr'),
                'description' => esc_html__('Configure lazy loading behavior for faster initial page loading.', 'explorexr'),
                'icon'        => 'clock',
                'section_id'  => 'explorexr_loading_lazy_section'
            ),
            array(
                'title'       => esc_html__('Large Model Handling', 'explorexr'),
                'description' => esc_html__('Configure how to handle large 3D models that may cause slower loading times.', 'explorexr'),
                'icon'        => 'admin-settings',
                'section_id'  => 'explorexr_loading_large_section'
            )
        )
    );
    
    // Render the loading options page
    // Set up header variables
    $page_title = 'Loading Options';
    $header_actions = '<a href="https://expoxr.com/explorexr/documentation/" target="_blank" class="button">
                        <span class="dashicons dashicons-book"></span> Documentation
                      </a>';
    ?>
    <div class="wrap">
        <h1>Loading Options</h1>
        
        <!-- WordPress.org Compliance: This div.wp-header-end is required for WordPress to place admin notices properly -->
        <div class="wp-header-end"></div>
        
        <!-- ExploreXR Plugin Content -->
        <div class="explorexr-admin-container">
        <!-- WordPress admin notices appear here automatically before our custom content -->
        
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; ?>
        
        <!-- Loading Options Settings -->
        <?php
        $card_title = 'Loading Options';
        $card_icon = 'performance';
        ob_start();
        ?>
        <p>Configure how 3D models are loaded and displayed on your website. These settings help optimize performance and user experience.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('explorexr_loading_settings', 'explorexr_loading_nonce'); ?>
            <input type="hidden" name="explorexr_action" value="save_loading_options">
            
            <?php
            // Get current option values
            $loading_display = get_option('explorexr_loading_display', 'bar');
            $large_model_handling = get_option('explorexr_large_model_handling', 'direct');
            $large_model_size_threshold = get_option('explorexr_large_model_size_threshold', 16);
            $lazy_load_poster = get_option('explorexr_lazy_load_poster', false);
            ?>
            
            <h3><?php esc_html_e('Core Loading Settings', 'explorexr'); ?></h3>
            <p><?php esc_html_e('Configure essential loading behavior for your 3D models.', 'explorexr'); ?></p>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="explorexr_loading_display"><?php esc_html_e('Display Type', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="explorexr_loading_display" id="explorexr_loading_display_bar" value="bar" <?php checked($loading_display, 'bar'); ?>>
                                <?php esc_html_e('Loading Bar Only', 'explorexr'); ?>
                            </label><br>
                            
                            <label>
                                <input type="radio" name="explorexr_loading_display" id="explorexr_loading_display_percentage" value="percentage" <?php checked($loading_display, 'percentage'); ?>>
                                <?php esc_html_e('Percentage Counter Only', 'explorexr'); ?>
                            </label><br>
                            
                            <label>
                                <input type="radio" name="explorexr_loading_display" id="explorexr_loading_display_both" value="both" <?php checked($loading_display, 'both'); ?>>
                                <?php esc_html_e('Both Loading Bar and Percentage', 'explorexr'); ?>
                            </label>
                        </fieldset>
                        <p class="description">
                            <?php 
                            printf(
                                // translators: %s: Link to Premium version
                                esc_html__('For more styling options and effects, consider using the Loading Addon %s.', 'explorexr'),
                                '<a href="' . esc_url(admin_url('admin.php?page=explorexr-addons')) . '">' . esc_html__('ExploreXR Addons', 'explorexr') . '</a>'
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
                        <label for="explorexr_lazy_load_poster"><?php esc_html_e('Lazy Load Poster Images', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="explorexr_lazy_load_poster" name="explorexr_lazy_load_poster" value="1" <?php checked($lazy_load_poster, true); ?>>
                        <label for="explorexr_lazy_load_poster"><?php esc_html_e('Enable lazy loading for poster images', 'explorexr'); ?></label>
                        <p class="description"><?php esc_html_e('Poster images will only load when they are about to enter the viewport, improving initial page load times.', 'explorexr'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Large Model Handling', 'explorexr'); ?></h3>
            <p><?php esc_html_e('Configure how to handle large 3D models that may cause slower loading times.', 'explorexr'); ?></p>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="explorexr_large_model_size_threshold"><?php esc_html_e('Size Threshold (MB)', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="explorexr_large_model_size_threshold" id="explorexr_large_model_size_threshold" value="<?php echo esc_attr($large_model_size_threshold); ?>" class="small-text" min="1" max="100"> MB
                        <p class="description"><?php esc_html_e('Models larger than this size will be treated as "large models" and use the behavior settings below.', 'explorexr'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="explorexr_large_model_handling"><?php esc_html_e('Large Model Behavior', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="explorexr_large_model_handling" id="explorexr_large_model_handling_direct" value="direct" <?php checked($large_model_handling, 'direct'); ?>>
                                <?php esc_html_e('Load directly (Default)', 'explorexr'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Always load models directly, regardless of size.', 'explorexr'); ?></p>
                            
                            <br><br>
                            
                            <label>
                                <input type="radio" name="explorexr_large_model_handling" id="explorexr_large_model_handling_poster" value="poster_button" <?php checked($large_model_handling, 'poster_button'); ?>>
                                <?php esc_html_e('Show poster with load button', 'explorexr'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('For large models, show a poster image with a button to load the model.', 'explorexr'); ?></p>
                            
                            <br><br>
                            
                            <label>
                                <input type="radio" name="explorexr_large_model_handling" id="explorexr_large_model_handling_lazy" value="lazy" <?php checked($large_model_handling, 'lazy'); ?>>
                                <?php esc_html_e('Lazy load when visible', 'explorexr'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Only load models when they are about to enter the viewport.', 'explorexr'); ?></p>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Load Model Button', 'explorexr'); ?></h3>
            <p><?php esc_html_e('Customize the button shown over the poster when using the "Poster + Load Button" mode.', 'explorexr'); ?></p>

            <?php
            $explorexr_button_text         = get_option('explorexr_load_button_text', '');
            $explorexr_button_bg           = get_option('explorexr_load_button_bg', '');
            $explorexr_button_color        = get_option('explorexr_load_button_color', '');
            $explorexr_button_hover_bg     = get_option('explorexr_load_button_hover_bg', '');
            $explorexr_button_hover_color  = get_option('explorexr_load_button_hover_color', '');
            $explorexr_button_radius       = get_option('explorexr_load_button_radius', '');
            ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="explorexr_load_button_text"><?php esc_html_e('Button Text', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="explorexr_load_button_text" id="explorexr_load_button_text" value="<?php echo esc_attr($explorexr_button_text); ?>" class="regular-text" placeholder="<?php esc_attr_e('Load 3D Model', 'explorexr'); ?>">
                        <p class="description"><?php esc_html_e('Label shown on the load button. Leave empty for the default "Load 3D Model".', 'explorexr'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="explorexr_load_button_bg"><?php esc_html_e('Background Color', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="explorexr_load_button_bg" id="explorexr_load_button_bg" value="<?php echo esc_attr($explorexr_button_bg); ?>" class="small-text" placeholder="#0073aa">
                        <p class="description"><?php esc_html_e('Hex color (e.g. #0073aa). Leave empty for default.', 'explorexr'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="explorexr_load_button_color"><?php esc_html_e('Text Color', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="explorexr_load_button_color" id="explorexr_load_button_color" value="<?php echo esc_attr($explorexr_button_color); ?>" class="small-text" placeholder="#ffffff">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="explorexr_load_button_hover_bg"><?php esc_html_e('Hover Background', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="explorexr_load_button_hover_bg" id="explorexr_load_button_hover_bg" value="<?php echo esc_attr($explorexr_button_hover_bg); ?>" class="small-text" placeholder="#005177">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="explorexr_load_button_hover_color"><?php esc_html_e('Hover Text Color', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="explorexr_load_button_hover_color" id="explorexr_load_button_hover_color" value="<?php echo esc_attr($explorexr_button_hover_color); ?>" class="small-text" placeholder="#ffffff">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="explorexr_load_button_radius"><?php esc_html_e('Border Radius', 'explorexr'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="explorexr_load_button_radius" id="explorexr_load_button_radius" value="<?php echo esc_attr($explorexr_button_radius); ?>" class="small-text" placeholder="4px">
                        <p class="description"><?php esc_html_e('CSS length value: px, %, em, or rem (e.g. 4px, 50%, 0.5rem).', 'explorexr'); ?></p>
                    </td>
                </tr>
            </table>

            <h3><?php esc_html_e('Compression & Texture Optimization', 'explorexr'); ?></h3>
            <p><?php esc_html_e('ExploreXR ships Draco, KTX2/Basis Universal and Meshopt decoders. Model files using these formats decode automatically — no per-model configuration needed.', 'explorexr'); ?></p>

            <?php
            $explorexr_draco_path   = EXPLOREXR_PLUGIN_DIR . 'assets/vendor/draco/draco_decoder.wasm';
            $explorexr_ktx2_path    = EXPLOREXR_PLUGIN_DIR . 'assets/vendor/basis-universal/basis_transcoder.wasm';
            $explorexr_meshopt_path = EXPLOREXR_PLUGIN_DIR . 'assets/vendor/meshopt/meshopt_decoder.module.js';
            $explorexr_compression_rows = array(
                array(
                    'label' => esc_html__('Draco Geometry Decoder', 'explorexr'),
                    'ok'    => file_exists($explorexr_draco_path),
                    'path'  => 'assets/vendor/draco/',
                ),
                array(
                    'label' => esc_html__('KTX2 / Basis Universal Texture Transcoder', 'explorexr'),
                    'ok'    => file_exists($explorexr_ktx2_path),
                    'path'  => 'assets/vendor/basis-universal/',
                ),
                array(
                    'label' => esc_html__('Meshopt Decoder', 'explorexr'),
                    'ok'    => file_exists($explorexr_meshopt_path),
                    'path'  => 'assets/vendor/meshopt/',
                ),
            );
            ?>
            <table class="form-table" role="presentation">
                <?php foreach ($explorexr_compression_rows as $explorexr_row) : ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($explorexr_row['label']); ?></th>
                        <td>
                            <?php if ($explorexr_row['ok']) : ?>
                                <span style="color:#1a7f37;font-weight:600;">&#x2713; <?php esc_html_e('Available', 'explorexr'); ?></span>
                            <?php else : ?>
                                <span style="color:#b32d2e;font-weight:600;">&#x2717; <?php esc_html_e('Missing', 'explorexr'); ?></span>
                            <?php endif; ?>
                            <code><?php echo esc_html($explorexr_row['path']); ?></code>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th scope="row"><?php esc_html_e('Decoder Attributes', 'explorexr'); ?></th>
                    <td>
                        <p class="description">
                            <?php esc_html_e('Injected automatically on every <model-viewer> element via the explorexr_premium_model_viewer_attributes filter:', 'explorexr'); ?>
                            <code>draco-decoder-location</code>, <code>ktx2-transcoder-location</code>, <code>meshopt-decoder</code>.
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Save Loading Options'); ?>
        </form>
        <?php
        $card_content = ob_get_clean();
        include EXPLOREXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
        
        <!-- ExploreXR Footer -->
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
        
        </div><!-- .explorexr-admin-container -->
    </div><!-- .wrap -->
    
    <?php
}





