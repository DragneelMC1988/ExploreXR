<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Settings page callback
function explorexr_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'explorexr'));
    }
    
    // Process reset request
    if (isset($_POST['explorexr_reset_settings']) && isset($_POST['explorexr_reset_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['explorexr_reset_nonce'])), 'explorexr_reset_settings')) {
        // Reset option values to defaults
        $default_options = array(
            'explorexr_loading_display' => 'bar',
            'explorexr_loading_bar_color' => '#1e88e5',
            'explorexr_loading_bar_size' => 'medium',
            'explorexr_loading_bar_position' => 'middle',
            'explorexr_percentage_font_size' => 24,
            'explorexr_percentage_font_family' => 'Arial, sans-serif',
            'explorexr_percentage_font_color' => '#333333',
            'explorexr_percentage_position' => 'center-center',
            'explorexr_large_model_handling' => 'direct',
            'explorexr_large_model_size_threshold' => 16,
            'explorexr_overlay_bg_color' => '#FFFFFF',
            'explorexr_overlay_bg_opacity' => 70,
            'explorexr_model_viewer_version' => '3.3.0',
            'explorexr_max_upload_size' => 50
        );
        
        // Update all options to defaults
        foreach ($default_options as $option_name => $default_value) {
            update_option($option_name, $default_value);
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>All settings have been reset to default values!</p></div>';
    }
      // Process cache clearing
    if (isset($_POST['explorexr_clear_cache']) && isset($_POST['explorexr_clear_cache_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['explorexr_clear_cache_nonce'])), 'explorexr_clear_cache')) {
        // Clear any transients or stored cache
        delete_transient('explorexr_viewer_version_check');
        delete_option('explorexr_last_cdn_check');
        
        // Add more cache clearing as needed
        
        echo '<div class="notice notice-success is-dismissible"><p>Model viewer cache has been cleared successfully!</p></div>';
    }
    
    // Process general settings form submission
    if (isset($_POST['explorexr_action']) && $_POST['explorexr_action'] === 'save_general_settings' && isset($_POST['explorexr_general_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['explorexr_general_nonce'])), 'explorexr_general_settings')) {
        // Process general settings fields
        if (isset($_POST['explorexr_model_viewer_version'])) {
            update_option('explorexr_model_viewer_version', sanitize_text_field(wp_unslash($_POST['explorexr_model_viewer_version'])));
        }
        if (isset($_POST['explorexr_max_upload_size'])) {
            $max_upload = absint($_POST['explorexr_max_upload_size']);
            if ($max_upload > 0) {
                update_option('explorexr_max_upload_size', $max_upload);
            }
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>General settings have been saved successfully!</p></div>';
    }
    
    // Get system information for the new System Info section
    $system_info = explorexr_get_system_info();
      // Set up header variables
    $page_title = 'ExploreXR Settings';
    $header_actions = '<a href="https://expoxr.com/explorexr/documentation/settings" target="_blank" class="button">
                        <span class="dashicons dashicons-book settings-icon"></span> Documentation
                      </a>';
    ?>
    <div class="wrap">
        <h1>ExploreXR Settings</h1>
        
        <!-- WordPress.org Compliance: This div.wp-header-end is required for WordPress to place admin notices properly -->
        <div class="wp-header-end"></div>
        
        <!-- ExploreXR Plugin Content -->
        <div class="explorexr-admin-container explorexr-settings-page">
        
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; ?>
        
        <!-- General Settings -->
        <?php
        $card_title = 'General Settings';
        $card_icon = 'admin-settings';
        ob_start();
        ?>
        <form method="post" action="" id="explorexr-general-settings-form">
            <?php wp_nonce_field('explorexr_general_settings', 'explorexr_general_nonce'); ?>
            <input type="hidden" name="explorexr_action" value="save_general_settings">
            
            <!-- Get current option values -->
            <?php
            $model_viewer_version = get_option('explorexr_model_viewer_version', '3.3.0');
            $max_upload_size = get_option('explorexr_max_upload_size', 50);
            $server_max = function_exists('explorexr_get_server_max_upload') ? explorexr_get_server_max_upload() : 50;
            ?>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="explorexr_model_viewer_version">Model Viewer Version</label>
                    </th>
                    <td>
                        <select name="explorexr_model_viewer_version" id="explorexr_model_viewer_version">
                            <option value="3.3.0" <?php selected($model_viewer_version, '3.3.0'); ?>>v3.3.0 (Latest stable)</option>
                            <option value="3.2.0" <?php selected($model_viewer_version, '3.2.0'); ?>>v3.2.0</option>
                            <option value="3.1.1" <?php selected($model_viewer_version, '3.1.1'); ?>>v3.1.1</option>
                            <option value="3.0.0" <?php selected($model_viewer_version, '3.0.0'); ?>>v3.0.0</option>
                        </select>
                        <p class="description">The version of Google's Model Viewer library to use. Newer versions may have more features but could be less stable.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="explorexr_max_upload_size">Max Upload Size (MB)</label>
                    </th>
                    <td>
                        <input type="number" name="explorexr_max_upload_size" id="explorexr_max_upload_size" 
                               value="<?php echo esc_attr($max_upload_size); ?>" class="small-text" min="1" max="<?php echo esc_attr($server_max); ?>"> MB
                        <p class="description">Maximum file size for 3D model uploads. Server limit: <?php echo esc_html($server_max); ?> MB.</p>
                    </td>
                </tr>
            </table>
            
            <!-- Add hidden fields to preserve settings when submitting general settings -->
            <input type="hidden" name="explorexr_preserve_settings" value="1">
            
            <?php submit_button('Save General Settings'); ?>
        </form>
        <?php
        $card_content = ob_get_clean();
        include EXPLOREXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
        
        <!-- System Information -->
        <?php
        $card_title = 'System Information';
        $card_icon = 'performance';
        ob_start();
        ?>
        <p>Detailed information about your system environment:</p>
        
        <table class="widefat striped">
            <tbody>
                <tr>
                    <th>WordPress Version</th>
                    <td><?php echo esc_html($system_info['wp_version']); ?></td>
                </tr>
                <tr>
                    <th>PHP Version</th>
                    <td><?php echo esc_html($system_info['php_version']); ?>
                        <?php if (version_compare($system_info['php_version'], '7.2', '<')): ?>
                            <span class="explorexr-badge error">Outdated</span>
                        <?php else: ?>
                            <span class="explorexr-badge ar">OK</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>MySQL Version</th>
                    <td><?php echo esc_html($system_info['mysql_version']); ?></td>
                </tr>
                <tr>
                    <th>Server Software</th>
                    <td><?php echo esc_html($system_info['server_software']); ?></td>
                </tr>
                <tr>
                    <th>Max Upload Size</th>
                    <td><?php echo esc_html($system_info['max_upload_size']); ?> MB</td>
                </tr>
                <tr>
                    <th>PHP Memory Limit</th>
                    <td><?php echo esc_html($system_info['memory_limit']); ?></td>
                </tr>
                <tr>
                    <th>PHP Max Execution Time</th>
                    <td><?php echo esc_html($system_info['max_execution_time']); ?> seconds</td>
                </tr>
                <tr>
                    <th>PHP Post Max Size</th>
                    <td><?php echo esc_html($system_info['post_max_size']); ?></td>
                </tr>
                <tr>
                    <th>GD Library</th>
                    <td><?php echo wp_kses_post($system_info['gd_installed'] ? '<span class="explorexr-badge ar">Installed</span>' : '<span class="explorexr-badge error">Not Installed</span>'); ?></td>
                </tr>
                <tr>
                    <th>Imagick</th>
                    <td><?php echo wp_kses_post($system_info['imagick_installed'] ? '<span class="explorexr-badge ar">Installed</span>' : '<span class="explorexr-badge">Not Installed</span>'); ?></td>
                </tr>
                <tr>
                    <th>Model Viewer Version</th>
                    <td><?php echo esc_html($system_info['model_viewer_version']); ?></td>
                </tr>
                <tr>
                    <th>Model Viewer Source</th>
                    <td><?php echo esc_html($system_info['model_viewer_source']); ?></td>
                </tr>
                <tr>
                    <th>Active Theme</th>
                    <td><?php echo esc_html($system_info['theme_name']); ?> v<?php echo esc_html($system_info['theme_version']); ?></td>
                </tr>
                <tr>
                    <th>ExploreXR Version</th>
                    <td><?php echo esc_html(EXPLOREXR_VERSION); ?></td>
                </tr>
                <tr>
                    <th>Browser</th>
                    <td id="explorexr-user-agent"><?php echo esc_html(isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : 'N/A'); ?></td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">                <button type="button" id="explorexr-copy-system-info" class="button copy-system-info-button">
                    <span class="dashicons dashicons-clipboard settings-icon"></span> Copy System Information                </button>
        </p>
        
        <?php
        $card_content = ob_get_clean();
        include EXPLOREXR_PLUGIN_DIR . 'admin/templates/card.php';        ?>
        
        <!-- Reset Settings -->
        <?php
        $card_title = 'Reset Settings';
        $card_icon = 'image-rotate';
        ob_start();
        ?>
        <p>Use this option to reset all plugin settings to their default values. This will not delete your 3D models or uploaded files.</p>
        
        <?php
        $alert_message = '<p><strong>Warning:</strong> This action cannot be undone. All your customized settings will be lost.</p>';
        include EXPLOREXR_PLUGIN_DIR . 'admin/templates/info-alert.php';
        ?>
        
        <form method="post" onsubmit="return confirm('Are you sure you want to reset all settings to default values? This action cannot be undone.'));\">
            <?php wp_nonce_field('explorexr_reset_settings', 'explorexr_reset_nonce'); ?>
            <p class="submit">
                <input type="submit" name="explorexr_reset_settings" class="button button-secondary" value="Reset All Settings">
            </p>
        </form>
        <?php
        $card_content = ob_get_clean();
        include EXPLOREXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
          <!-- Cache Management -->
        <?php
        $card_title = 'Model Viewer Cache';
        $card_icon = 'update-alt';
        ob_start();
        ?>
        <p>In some cases, clearing the model viewer cache can help resolve display issues with 3D models.</p>
        <form method="post" onsubmit="return confirm('Are you sure you want to clear the model viewer cache? This will not affect your saved settings.'));\">
            <?php wp_nonce_field('explorexr_clear_cache', 'explorexr_clear_cache_nonce'); ?>
            <p class="submit">
                <input type="submit" name="explorexr_clear_cache" class="button button-secondary" value="Clear Cache">
            </p>        </form>
        <?php
        $card_content = ob_get_clean();
        include EXPLOREXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
          <!-- Import/Export Settings -->
        <?php
        $card_title = 'Import/Export Settings';
        $card_icon = 'migrate';
        ob_start();
        ?>
        <div class="explorexr-import-export-section">
            <?php 
            // Call the section callback manually to display the description
            echo '<p>' . esc_html__('Backup your ExploreXR settings or restore from a previous backup.', 'explorexr') . '</p>';
            
            // Call the import/export callback manually to show the UI
            if (function_exists('explorexr_import_export_callback')) {
                explorexr_import_export_callback();
            } else {
                echo '<p class="description">Import/Export functionality is not available.</p>';
            }
            ?>
        </div>
        <?php
        $card_content = ob_get_clean();
        include EXPLOREXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
        
        <!-- About -->
        <?php
        $card_title = 'About ExploreXR';
        $card_icon = 'info';
        ob_start();
        ?>        <div class="explorexr-about-section">
            <div class="explorexr-logo-container">
                <?php 
                // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Plugin logo for admin interface
                printf('<img src="%s" alt="%s" class="explorexr-logo" loading="lazy">', 
                    esc_url(EXPLOREXR_PLUGIN_URL . 'assets/img/logos/explorexr-Logo-dark.png'), 
                    esc_attr__('ExploreXR Logo', 'explorexr')
                );
                ?>
            </div>
            <div>
                <p class="explorexr-version-info"><strong>Version:</strong> <?php echo esc_html(EXPLOREXR_VERSION); ?></p>
                <p class="explorexr-description">ExploreXR enhances your WordPress site with 3D model display capabilities, using Google's Model-Viewer technology.</p>
            </div>
        </div>
        <p>For documentation, support, and more information, please visit our website:</p>
        <p><a href="https://expoxr.com" class="button" target="_blank">Visit ExploreXR Website</a></p>
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

/**
 * Register general settings
 */
function explorexr_general_settings_register_settings() {
    // Register general settings with sanitization callbacks
    register_setting('explorexr_settings', 'explorexr_model_viewer_version', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('explorexr_settings', 'explorexr_max_upload_size', array(
        'sanitize_callback' => 'absint'
    ));
    
    // Add settings section
    add_settings_section(
        'explorexr_general_settings',
        esc_html__('General Configuration', 'explorexr'),
        'explorexr_general_settings_callback',
        'explorexr-settings'
    );
    
    // Add settings fields
    add_settings_field(
        'explorexr_model_viewer_version',
        esc_html__('Model Viewer Version', 'explorexr'),
        'explorexr_model_viewer_version_callback',
        'explorexr-settings',
        'explorexr_general_settings'
    );
    
    add_settings_field(
        'explorexr_max_upload_size',
        esc_html__('Max Upload Size (MB)', 'explorexr'),
        'explorexr_max_upload_size_callback',
        'explorexr-settings',
        'explorexr_general_settings'
    );
}
add_action('admin_init', 'explorexr_general_settings_register_settings');






