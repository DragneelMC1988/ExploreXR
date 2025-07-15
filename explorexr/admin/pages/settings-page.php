<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Settings page callback
function expoxr_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'explorexr'));
    }
    
    // Process reset request
    if (isset($_POST['expoxr_reset_settings']) && isset($_POST['expoxr_reset_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['expoxr_reset_nonce'])), 'expoxr_reset_settings')) {
        // Reset option values to defaults
        $default_options = array(
            'expoxr_loading_display' => 'bar',
            'expoxr_loading_bar_color' => '#1e88e5',
            'expoxr_loading_bar_size' => 'medium',
            'expoxr_loading_bar_position' => 'middle',
            'expoxr_percentage_font_size' => 24,
            'expoxr_percentage_font_family' => 'Arial, sans-serif',
            'expoxr_percentage_font_color' => '#333333',
            'expoxr_percentage_position' => 'center-center',
            'expoxr_large_model_handling' => 'direct',
            'expoxr_large_model_size_threshold' => 16,
            'expoxr_overlay_bg_color' => '#FFFFFF',
            'expoxr_overlay_bg_opacity' => 70,
            'expoxr_cdn_source' => 'local',
            'expoxr_model_viewer_version' => '3.3.0',
            'expoxr_max_upload_size' => 50,
            'expoxr_debug_mode' => false
        );
        
        // Update all options to defaults
        foreach ($default_options as $option_name => $default_value) {
            update_option($option_name, $default_value);
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>All settings have been reset to default values!</p></div>';
    }
      // Process cache clearing
    if (isset($_POST['expoxr_clear_cache']) && isset($_POST['expoxr_clear_cache_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['expoxr_clear_cache_nonce'])), 'expoxr_clear_cache')) {
        // Clear any transients or stored cache
        delete_transient('expoxr_viewer_version_check');
        delete_option('expoxr_last_cdn_check');
        
        // Add more cache clearing as needed
        
        echo '<div class="notice notice-success is-dismissible"><p>Model viewer cache has been cleared successfully!</p></div>';
    }
    
    // Process general settings form submission
    if (isset($_POST['expoxr_action']) && $_POST['expoxr_action'] === 'save_general_settings' && isset($_POST['expoxr_general_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['expoxr_general_nonce'])), 'expoxr_general_settings')) {
        // Process general settings fields
        if (isset($_POST['expoxr_cdn_source'])) {
            update_option('expoxr_cdn_source', sanitize_text_field(wp_unslash($_POST['expoxr_cdn_source'])));
        }
        if (isset($_POST['expoxr_model_viewer_version'])) {
            update_option('expoxr_model_viewer_version', sanitize_text_field(wp_unslash($_POST['expoxr_model_viewer_version'])));
        }
        if (isset($_POST['expoxr_max_upload_size'])) {
            $max_upload = absint($_POST['expoxr_max_upload_size']);
            if ($max_upload > 0) {
                update_option('expoxr_max_upload_size', $max_upload);
            }
        }
        // Process debug mode checkbox
        if (isset($_POST['expoxr_debug_mode'])) {
            update_option('expoxr_debug_mode', true);
        } else {
            update_option('expoxr_debug_mode', false);
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>General settings have been saved successfully!</p></div>';
    }
    
    // Process debug settings form submission
    if (isset($_POST['submit']) && $_POST['submit'] === 'Save Debug Settings' && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'expoxr_settings-options')) {
        // Define debug setting fields
        $debug_fields = array(
            'expoxr_debug_log',
            'expoxr_view_php_errors',
            'expoxr_console_logging',
            'expoxr_debug_ar_features',
            'expoxr_debug_camera_controls',
            'expoxr_debug_animations',
            'expoxr_debug_annotations',
            'expoxr_debug_loading_info',
            'expoxr_debug_mode'
        );
        
        // Process each debug field
        foreach ($debug_fields as $field) {
            if (isset($_POST[$field])) {
                // Handle checkbox values (convert "1" to true, anything else to false)
                $value = ($_POST[$field] === '1' || $_POST[$field] === 'on') ? true : false;
                update_option($field, $value);
            } else {
                // If checkbox is not submitted, it means it's unchecked
                update_option($field, false);
            }
        }
        
        // Also process any other settings that might be in hidden fields
        $other_fields = array(
            'expoxr_cdn_source',
            'expoxr_model_viewer_version',
            'expoxr_max_upload_size'
        );
        
        foreach ($other_fields as $field) {
            if (isset($_POST[$field])) {
                update_option($field, sanitize_text_field(wp_unslash($_POST[$field])));
            }
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>Debug settings have been saved successfully!</p></div>';
    }
    
    // Get system information for the new System Info section
    $system_info = expoxr_get_system_info();
      // Set up header variables
    $page_title = 'ExploreXR Settings';
    $header_actions = '<a href="https://expoxr.com/explorexr/documentation/settings" target="_blank" class="button">
                        <span class="dashicons dashicons-book settings-icon"></span> Documentation
                      </a>';
    ?>
    <div class="wrap expoxr-admin-container expoxr-settings-page">
        <!-- WordPress admin notices appear here automatically before our custom content -->
        
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; ?>
        
        <!-- General Settings -->
        <?php
        $card_title = 'General Settings';
        $card_icon = 'admin-settings';
        ob_start();
        ?>
        <form method="post" action="" id="expoxr-general-settings-form">
            <?php wp_nonce_field('expoxr_general_settings', 'expoxr_general_nonce'); ?>
            <input type="hidden" name="expoxr_action" value="save_general_settings">
            
            <!-- Get current option values -->
            <?php
            $cdn_source = get_option('expoxr_cdn_source', 'local');
            $model_viewer_version = get_option('expoxr_model_viewer_version', '3.3.0');
            $max_upload_size = get_option('expoxr_max_upload_size', 50);
            $server_max = function_exists('expoxr_get_server_max_upload') ? expoxr_get_server_max_upload() : 50;
            ?>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="expoxr_cdn_source">Model Viewer Source</label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="expoxr_cdn_source" id="expoxr_cdn_source_local" value="local" <?php checked($cdn_source, 'local'); ?>>
                                Use Local File (Recommended)
                            </label>
                            <p class="description">Load Model Viewer from your server. Required for WordPress.org Plugin Check compliance.</p>
                            
                            <br><br>
                            
                            <label>
                                <input type="radio" name="expoxr_cdn_source" id="expoxr_cdn_source_cdn" value="cdn" <?php checked($cdn_source, 'cdn'); ?>>
                                Use CDN (Not Recommended)
                            </label>                            
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="expoxr_model_viewer_version">Model Viewer Version</label>
                    </th>
                    <td>
                        <select name="expoxr_model_viewer_version" id="expoxr_model_viewer_version">
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
                        <label for="expoxr_max_upload_size">Max Upload Size (MB)</label>
                    </th>
                    <td>
                        <input type="number" name="expoxr_max_upload_size" id="expoxr_max_upload_size" 
                               value="<?php echo esc_attr($max_upload_size); ?>" class="small-text" min="1" max="<?php echo esc_attr($server_max); ?>"> MB
                        <p class="description">Maximum file size for 3D model uploads. Server limit: <?php echo esc_html($server_max); ?> MB.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="expoxr_debug_mode">Enable Debug Mode</label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="expoxr_debug_mode" id="expoxr_debug_mode" value="1" <?php checked(get_option('expoxr_debug_mode', false)); ?>>
                            Enable debugging features
                        </label>
                        <p class="description">Enable debug mode to access advanced debugging options and troubleshooting tools.</p>
                    </td>
                </tr>
            </table>
            
            <!-- Add hidden fields to preserve debugging settings when submitting general settings -->
            <input type="hidden" name="expoxr_preserve_settings" value="1">
            <input type="hidden" id="expoxr_debug_log_hidden" name="expoxr_debug_log" value="<?php echo esc_attr(get_option('expoxr_debug_log', '') ? '1' : ''); ?>">
            <input type="hidden" id="expoxr_view_php_errors_hidden" name="expoxr_view_php_errors" value="<?php echo esc_attr(get_option('expoxr_view_php_errors', '') ? '1' : ''); ?>">
            <input type="hidden" id="expoxr_console_logging_hidden" name="expoxr_console_logging" value="<?php echo esc_attr(get_option('expoxr_console_logging', '') ? '1' : ''); ?>">
            <input type="hidden" id="expoxr_debug_ar_features_hidden" name="expoxr_debug_ar_features" value="<?php echo esc_attr(get_option('expoxr_debug_ar_features', '') ? '1' : ''); ?>">
            <input type="hidden" id="expoxr_debug_camera_controls_hidden" name="expoxr_debug_camera_controls" value="<?php echo esc_attr(get_option('expoxr_debug_camera_controls', '') ? '1' : ''); ?>">
            <input type="hidden" id="expoxr_debug_animations_hidden" name="expoxr_debug_animations" value="<?php echo esc_attr(get_option('expoxr_debug_animations', '') ? '1' : ''); ?>">
            <input type="hidden" id="expoxr_debug_annotations_hidden" name="expoxr_debug_annotations" value="<?php echo esc_attr(get_option('expoxr_debug_annotations', '') ? '1' : ''); ?>">
            <input type="hidden" id="expoxr_debug_loading_info_hidden" name="expoxr_debug_loading_info" value="<?php echo esc_attr(get_option('expoxr_debug_loading_info', '') ? '1' : ''); ?>">
            
            <?php submit_button('Save General Settings'); ?>
        </form>
        <?php
        $card_content = ob_get_clean();
        include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
        
        <!-- Debugging Options -->
        <?php
        // Only show debugging options card if debug mode is enabled
        $debug_mode = get_option('expoxr_debug_mode', false);
        
        if ($debug_mode): // Check if debug mode is enabled
            $card_title = 'Debugging Options';
            $card_icon = 'code-standards';
            ob_start();
        ?>        <form method="post" action="" id="expoxr-debug-settings-form">
            <?php wp_nonce_field('expoxr_settings-options'); ?>
            
            <p><strong>Configure debugging options for troubleshooting and development purposes.</strong></p>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="expoxr_debug_log">Debug Log</label>
                    </th>
                    <td>
                        <input type="checkbox" id="expoxr_debug_log" name="expoxr_debug_log" value="1" <?php checked(get_option('expoxr_debug_log', false)); ?>>
                        <label for="expoxr_debug_log">Enable debug logging to track plugin operations</label>
                        <p class="description">Logs debug information to help troubleshoot issues.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="expoxr_view_php_errors">View PHP Errors</label>
                    </th>
                    <td>
                        <input type="checkbox" id="expoxr_view_php_errors" name="expoxr_view_php_errors" value="1" <?php checked(get_option('expoxr_view_php_errors', false)); ?>>
                        <label for="expoxr_view_php_errors">Display PHP errors and warnings</label>
                        <p class="description">Shows PHP errors on the frontend (use only for debugging).</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="expoxr_console_logging">Console Logging</label>
                    </th>
                    <td>
                        <input type="checkbox" id="expoxr_console_logging" name="expoxr_console_logging" value="1" <?php checked(get_option('expoxr_console_logging', false)); ?>>
                        <label for="expoxr_console_logging">Enable JavaScript console logging</label>
                        <p class="description">Outputs debug information to the browser console.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="expoxr_debug_loading_info">Loading Information Debug</label>
                    </th>
                    <td>
                        <input type="checkbox" id="expoxr_debug_loading_info" name="expoxr_debug_loading_info" value="1" <?php checked(get_option('expoxr_debug_loading_info', false)); ?>>
                        <label for="expoxr_debug_loading_info">Debug model loading process</label>
                        <p class="description">Tracks model loading progress and performance metrics.</p>
                    </td>
                </tr>
            </table>
            
            <!-- Add hidden fields to preserve general settings when submitting debugging options -->
            <input type="hidden" name="expoxr_preserve_settings" value="1">
            <input type="hidden" id="expoxr_cdn_source_hidden" name="expoxr_cdn_source" value="<?php echo esc_attr(get_option('expoxr_cdn_source', 'cdn')); ?>">
            <input type="hidden" id="expoxr_model_viewer_version_hidden" name="expoxr_model_viewer_version" value="<?php echo esc_attr(get_option('expoxr_model_viewer_version', '3.3.0')); ?>">
            <input type="hidden" id="expoxr_max_upload_size_hidden" name="expoxr_max_upload_size" value="<?php echo esc_attr(get_option('expoxr_max_upload_size', 50)); ?>">
            <!-- Always include debug mode to ensure it stays enabled -->
            <input type="hidden" id="expoxr_debug_mode_hidden" name="expoxr_debug_mode" value="1">
            
            <div class="expoxr-debugging-tools">
                <h3>Debug Log Tools</h3>
                
                <?php if (get_option('expoxr_debug_log', false)): ?>
                    <div class="expoxr-debug-tool-actions">
                        <button type="button" id="expoxr-view-log" class="button">
                            <span class="dashicons dashicons-visibility"></span> View Debug Log
                        </button>
                        <button type="button" id="expoxr-clear-log" class="button">
                            <span class="dashicons dashicons-trash"></span> Clear Debug Log
                        </button>
                        <button type="button" id="expoxr-download-log" class="button">
                            <span class="dashicons dashicons-download"></span> Download Debug Log
                        </button>
                    </div>
                    
                    <div id="expoxr-debug-log-viewer">
                        <h4>Debug Log Contents</h4>
                        <div class="expoxr-log-container">
                            <pre id="expoxr-log-content">Loading log contents...</pre>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="description">Enable Debug Log to access debugging tools.</p>
                <?php endif; ?>
                  <div class="expoxr-debug-info">
                    <h4>Core Debugging Features</h4>
                    <ul class="expoxr-debug-status-list">
                        <li>
                            <span class="expoxr-debug-status-label">Loading Information Debugging:</span>
                            <span class="expoxr-debug-status-value <?php echo esc_attr(get_option('expoxr_debug_loading_info') ? 'active' : 'inactive'); ?>">
                                <?php echo esc_html(get_option('expoxr_debug_loading_info') ? 'Active' : 'Inactive'); ?>
                            </span>
                        </li>
                        <li>
                            <span class="expoxr-debug-status-label">Console Logging:</span>
                            <span class="expoxr-debug-status-value <?php echo esc_attr(get_option('expoxr_console_logging') ? 'active' : 'inactive'); ?>">
                                <?php echo esc_html(get_option('expoxr_console_logging') ? 'Active' : 'Inactive'); ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <?php submit_button('Save Debug Settings'); ?>
        </form>
        <?php
            $card_content = ob_get_clean();
            include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';
        endif; // End of debug mode check
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
                            <span class="expoxr-badge error">Outdated</span>
                        <?php else: ?>
                            <span class="expoxr-badge ar">OK</span>
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
                    <td><?php echo wp_kses_post($system_info['gd_installed'] ? '<span class="expoxr-badge ar">Installed</span>' : '<span class="expoxr-badge error">Not Installed</span>'); ?></td>
                </tr>
                <tr>
                    <th>Imagick</th>
                    <td><?php echo wp_kses_post($system_info['imagick_installed'] ? '<span class="expoxr-badge ar">Installed</span>' : '<span class="expoxr-badge">Not Installed</span>'); ?></td>
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
                    <th>Debug Mode</th>
                    <td><?php echo wp_kses_post($system_info['debug_mode'] ? '<span class="expoxr-badge">Enabled</span>' : '<span class="expoxr-badge ar">Disabled</span>'); ?></td>
                </tr>
                <tr>
                    <th>Active Theme</th>
                    <td><?php echo esc_html($system_info['theme_name']); ?> v<?php echo esc_html($system_info['theme_version']); ?></td>
                </tr>
                <tr>
                    <th>ExploreXR Version</th>
                    <td><?php echo esc_html(EXPOXR_VERSION); ?></td>
                </tr>
                <tr>
                    <th>Browser</th>
                    <td id="expoxr-user-agent"><?php echo esc_html(isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : 'N/A'); ?></td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">                <button type="button" id="expoxr-copy-system-info" class="button copy-system-info-button">
                    <span class="dashicons dashicons-clipboard settings-icon"></span> Copy System Information                </button>
        </p>
        
        <?php
        $card_content = ob_get_clean();
        include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';        ?>
        
        <!-- Reset Settings -->
        <?php
        $card_title = 'Reset Settings';
        $card_icon = 'image-rotate';
        ob_start();
        ?>
        <p>Use this option to reset all plugin settings to their default values. This will not delete your 3D models or uploaded files.</p>
        
        <?php
        $alert_message = '<p><strong>Warning:</strong> This action cannot be undone. All your customized settings will be lost.</p>';
        include EXPOXR_PLUGIN_DIR . 'admin/templates/info-alert.php';
        ?>
        
        <form method="post" onsubmit="return confirm('Are you sure you want to reset all settings to default values? This action cannot be undone.'));\">
            <?php wp_nonce_field('expoxr_reset_settings', 'expoxr_reset_nonce'); ?>
            <p class="submit">
                <input type="submit" name="expoxr_reset_settings" class="button button-secondary" value="Reset All Settings">
            </p>
        </form>
        <?php
        $card_content = ob_get_clean();
        include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
          <!-- Cache Management -->
        <?php
        $card_title = 'Model Viewer Cache';
        $card_icon = 'update-alt';
        ob_start();
        ?>
        <p>In some cases, clearing the model viewer cache can help resolve display issues with 3D models.</p>
        <form method="post" onsubmit="return confirm('Are you sure you want to clear the model viewer cache? This will not affect your saved settings.'));\">
            <?php wp_nonce_field('expoxr_clear_cache', 'expoxr_clear_cache_nonce'); ?>
            <p class="submit">
                <input type="submit" name="expoxr_clear_cache" class="button button-secondary" value="Clear Cache">
            </p>        </form>
        <?php
        $card_content = ob_get_clean();
        include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
          <!-- Import/Export Settings -->
        <?php
        $card_title = 'Import/Export Settings';
        $card_icon = 'migrate';
        ob_start();
        ?>
        <div class="expoxr-import-export-section">
            <?php 
            // Call the section callback manually to display the description
            echo '<p>' . esc_html__('Backup your ExpoXR settings or restore from a previous backup.', 'explorexr') . '</p>';
            
            // Call the import/export callback manually to show the UI
            if (function_exists('expoxr_import_export_callback')) {
                expoxr_import_export_callback();
            } else {
                echo '<p class="description">Import/Export functionality is not available.</p>';
            }
            ?>
        </div>
        <?php
        $card_content = ob_get_clean();
        include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
        
        <!-- About -->
        <?php
        $card_title = 'About ExploreXR';
        $card_icon = 'info';
        ob_start();
        ?>        <div class="expoxr-about-section">
            <div class="expoxr-logo-container">
                <?php 
                printf('<img src="%s" alt="%s" class="expoxr-logo" loading="lazy">', 
                    esc_url(EXPOXR_PLUGIN_URL . 'assets/img/logos/exploreXR-Logo-Dark.png'), 
                    esc_attr__('ExploreXR Logo', 'explorexr')
                );
                ?>
            </div>
            <div>
                <p class="expoxr-version-info"><strong>Version:</strong> <?php echo esc_html(EXPOXR_VERSION); ?></p>
                <p class="expoxr-description">ExploreXR enhances your WordPress site with 3D model display capabilities, using Google's Model-Viewer technology.</p>
            </div>
        </div>
        <p>For documentation, support, and more information, please visit our website:</p>
        <p><a href="https://expoxr.com" class="button" target="_blank">Visit ExploreXR Website</a></p>
        <?php
        $card_content = ob_get_clean();
        include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
    
    <!-- ExpoXR Footer -->
    <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    </div>
    <?php
}

/**
 * Register general settings
 */
function expoxr_general_settings_register_settings() {
    // Register general settings with sanitization callbacks
    register_setting('expoxr_settings', 'expoxr_cdn_source', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('expoxr_settings', 'expoxr_model_viewer_version', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('expoxr_settings', 'expoxr_max_upload_size', array(
        'sanitize_callback' => 'absint'
    ));
    
    // Add settings section
    add_settings_section(
        'expoxr_general_settings',
        esc_html__('General Configuration', 'explorexr'),
        'expoxr_general_settings_callback',
        'expoxr-settings'
    );
    
    // Add settings fields
    add_settings_field(
        'expoxr_cdn_source',
        esc_html__('Model Viewer Source', 'explorexr'),
        'expoxr_cdn_source_callback',
        'expoxr-settings',
        'expoxr_general_settings'
    );
    
    add_settings_field(
        'expoxr_model_viewer_version',
        esc_html__('Model Viewer Version', 'explorexr'),
        'expoxr_model_viewer_version_callback',
        'expoxr-settings',
        'expoxr_general_settings'
    );
    
    add_settings_field(
        'expoxr_max_upload_size',
        esc_html__('Max Upload Size (MB)', 'explorexr'),
        'expoxr_max_upload_size_callback',
        'expoxr-settings',
        'expoxr_general_settings'
    );
}
add_action('admin_init', 'expoxr_general_settings_register_settings');





