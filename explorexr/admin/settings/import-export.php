<?php
/**
 * ExpoXR Import/Export Settings
 *
 * Handles importing and exporting plugin settings
 *
 * @package ExpoXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register import/export settings section
 */
function expoxr_register_import_export_settings() {
    // Add Import/Export settings section
    add_settings_section(
        'expoxr_import_export_settings',
        esc_html__('Import/Export Settings', 'explorexr'),
        'expoxr_import_export_settings_callback',
        'expoxr-settings'
    );

    // Add Import/Export field
    add_settings_field(
        'expoxr_import_export',
        esc_html__('Backup & Restore', 'explorexr'),
        'expoxr_import_export_callback',
        'expoxr-settings',
        'expoxr_import_export_settings'
    );
}
add_action('admin_init', 'expoxr_register_import_export_settings');

/**
 * Import/Export settings section callback
 */
function expoxr_import_export_settings_callback() {
    echo '<p>' . esc_html__('Backup your ExpoXR settings or restore from a previous backup.', 'explorexr') . '</p>';
}

/**
 * Import/Export field callback
 */
function expoxr_import_export_callback() {
    ?>
    <div class="expoxr-import-export-container">
        <div class="expoxr-export-section">
            <h4><?php esc_html_e('Export Settings', 'explorexr'); ?></h4>
            <p><?php esc_html_e('Export all your ExpoXR settings as a JSON file that you can use to restore your configuration later or migrate to another site.', 'explorexr'); ?></p>
            <form method="post" action="" id="expoxr-export-form">
                <?php wp_nonce_field('expoxr_export_nonce', 'expoxr_export_nonce'); ?>
                <input type="hidden" name="expoxr_action" value="export_settings" />
                <p>
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Export Settings', 'explorexr'); ?>
                    </button>
                </p>
                <div class="export-note">
                    <p><em><?php esc_html_e('Note: The exported file will contain all ExpoXR settings including viewer configuration, loading options, and plugin preferences.', 'explorexr'); ?></em></p>
                </div>
            </form>
        </div>

        <div class="expoxr-import-section">
            <h4><?php esc_html_e('Import Settings', 'explorexr'); ?></h4>
            <p><?php esc_html_e('Import settings from a previously exported JSON file.', 'explorexr'); ?></p>
            <form method="post" enctype="multipart/form-data" action="" id="expoxr-import-form">
                <?php wp_nonce_field('expoxr_import_nonce', 'expoxr_import_nonce'); ?>
                <input type="hidden" name="expoxr_action" value="import_settings" />
                
                <div class="import-note">
                    <p><span class="dashicons dashicons-info"></span> <?php esc_html_e('Importing settings will merge them with your existing configuration. You can choose to override existing settings with the checkbox below.', 'explorexr'); ?></p>
                </div>
                
                <p>
                    <input type="file" name="expoxr_import_file" id="expoxr-import-file" required accept=".json" />
                </p>
                
                <div id="expoxr-import-preview"></div>
                
                <p>
                    <label>
                        <input type="checkbox" name="expoxr_import_override" value="1" />
                        <?php esc_html_e('Override existing settings', 'explorexr'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('If checked, imported settings will replace your existing settings. If unchecked, only new settings will be added.', 'explorexr'); ?></p>
                </p>
                
                <p>
                    <button type="submit" class="button button-primary" disabled>
                        <span class="dashicons dashicons-upload"></span>
                        <?php esc_html_e('Import Settings', 'explorexr'); ?>
                    </button>
                </p>            </form>
        </div>
    </div>
    <?php
}

/**
 * Handle export request
 */
function expoxr_handle_settings_export() {
    if (!isset($_POST['expoxr_action']) || sanitize_text_field(wp_unslash($_POST['expoxr_action'])) !== 'export_settings') {
        return;
    }

    // Verify nonce
    if (!isset($_POST['expoxr_export_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['expoxr_export_nonce'])), 'expoxr_export_nonce')) {
        wp_die(esc_html__('Security check failed. Please try again.', 'explorexr'));
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to export settings.', 'explorexr'));
    }

    // Get all options that start with 'expoxr_' using WordPress functions
    $expoxr_options = wp_load_alloptions();
    $options = [];
    
    foreach ($expoxr_options as $option_name => $option_value) {
        if (strpos($option_name, 'expoxr_') === 0) {
            $options[] = (object) [
                'option_name' => $option_name,
                'option_value' => $option_value
            ];
        }
    }

    // Prepare export data with organized categories
    $export_data = array(
        'core_settings' => array(),
        'viewer_settings' => array(),
        'loading_settings' => array(),
        'other_settings' => array()
    );
    
    $setting_count = 0;
    
    foreach ($options as $option) {
        // Skip transients and temporary data
        if (strpos($option->option_name, '_transient') !== false) {
            continue;
        }
        
        $option_name = $option->option_name;
        $option_value = maybe_unserialize($option->option_value);
        
        // Categorize settings
        if (in_array($option_name, array(
            'expoxr_cdn_source',
            'expoxr_model_viewer_version',
            'expoxr_max_upload_size',
            'expoxr_debug_mode',
            'expoxr_view_php_errors'
        ))) {
            $export_data['core_settings'][$option_name] = $option_value;
        }        // Viewer settings
        elseif ($option_name && is_string($option_name) && strpos($option_name, 'expoxr_viewer_') === 0) {
            $export_data['viewer_settings'][$option_name] = $option_value;
        }
        // Loading settings
        elseif ($option_name && is_string($option_name) && (
                strpos($option_name, 'expoxr_loading_') === 0 || 
                strpos($option_name, 'expoxr_large_model_') === 0 ||
                strpos($option_name, 'expoxr_percentage_') === 0)) {
            $export_data['loading_settings'][$option_name] = $option_value;
        }
        // Everything else
        else {
            $export_data['other_settings'][$option_name] = $option_value;
        }
        
        $setting_count++;
    }
    
    // Clean up empty categories
    foreach ($export_data as $category => $settings) {
        if (empty($settings)) {
            unset($export_data[$category]);
        }
    }
    
    // Add export metadata
    $export_data['_export_info'] = array(
        'date' => gmdate('Y-m-d H:i:s'),
        'plugin_version' => EXPOXR_VERSION,
        'site' => get_bloginfo('name'),
        'url' => get_bloginfo('url'),
        'setting_count' => $setting_count,
        'wp_version' => get_bloginfo('version')
    );

    // Generate filename
    $site_name = sanitize_title_with_dashes(get_bloginfo('name'));
    $filename = 'expoxr-settings-' . $site_name . '-' . gmdate('Y-m-d') . '.json';

    // Clean any previous output to prevent headers already sent error
    if (ob_get_length()) {
        ob_clean();
    }
    
    // Set headers for file download if they haven't been sent yet
    if (!headers_sent()) {
        header('Content-disposition: attachment; filename=' . $filename);
        header('Content-type: application/json');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    // Output the settings as JSON
    echo wp_json_encode($export_data, JSON_PRETTY_PRINT);
    exit;
}
add_action('admin_init', 'expoxr_handle_settings_export');

/**
 * Handle import request
 */
function expoxr_handle_settings_import() {
    if (!isset($_POST['expoxr_action']) || sanitize_text_field(wp_unslash($_POST['expoxr_action'])) !== 'import_settings') {
        return;
    }

    // Verify nonce
    if (!isset($_POST['expoxr_import_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['expoxr_import_nonce'])), 'expoxr_import_nonce')) {
        add_settings_error('expoxr_messages', 'expoxr_import_error', esc_html__('Security check failed. Please try again.', 'explorexr'), 'error');
        return;
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        add_settings_error('expoxr_messages', 'expoxr_import_error', esc_html__('You do not have sufficient permissions to import settings.', 'explorexr'), 'error');
        return;
    }

    // Check if a file was uploaded
    if (!isset($_FILES['expoxr_import_file']) || empty($_FILES['expoxr_import_file']['tmp_name'])) {
        add_settings_error('expoxr_messages', 'expoxr_import_error', esc_html__('No file was uploaded. Please select a file to import.', 'explorexr'), 'error');
        return;
    }

    // Check for upload errors
    if (isset($_FILES['expoxr_import_file']['error']) && $_FILES['expoxr_import_file']['error'] !== UPLOAD_ERR_OK) {
        $upload_error_messages = array(
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
        );
        $file_error = isset($_FILES['expoxr_import_file']['error']) ? intval($_FILES['expoxr_import_file']['error']) : UPLOAD_ERR_NO_FILE;
        $error_message = isset($upload_error_messages[$file_error]) ? 
                         $upload_error_messages[$file_error] : 
                         'Unknown upload error.';
        
        /* translators: %s: Error message from file upload */
        add_settings_error('expoxr_messages', 'expoxr_import_error', sprintf(esc_html__('Error uploading file: %s', 'explorexr'), $error_message), 'error');
        return;
    }

    // Check file size
    if (isset($_FILES['expoxr_import_file']['size']) && $_FILES['expoxr_import_file']['size'] > 5242880) { // 5MB limit
        add_settings_error('expoxr_messages', 'expoxr_import_error', esc_html__('File is too large. Maximum size is 5MB.', 'explorexr'), 'error');
        return;
    }

    // Check file extension
    if (isset($_FILES['expoxr_import_file']['name'])) {
        $file_extension = strtolower(pathinfo(sanitize_file_name($_FILES['expoxr_import_file']['name']), PATHINFO_EXTENSION));
        if ($file_extension !== 'json') {
            add_settings_error('expoxr_messages', 'expoxr_import_error', esc_html__('Invalid file format. Please upload a JSON file.', 'explorexr'), 'error');
            return;
        }
    }

    // Get file contents
    if (isset($_FILES['expoxr_import_file']['tmp_name'])) {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- File path is handled by WordPress file upload
        $import_file = file_get_contents($_FILES['expoxr_import_file']['tmp_name']);
    } else {
        $import_file = false;
    }
    if (!$import_file) {
        add_settings_error('expoxr_messages', 'expoxr_import_error', esc_html__('Could not read the import file. Please try again.', 'explorexr'), 'error');
        return;
    }

    // Decode JSON data
    $import_data = json_decode($import_file, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        add_settings_error(
            'expoxr_messages', 
            'expoxr_import_error', 
            /* translators: %s: JSON error message */
            sprintf(esc_html__('Invalid JSON file: %s. Could not import settings.', 'explorexr'), json_last_error_msg()), 
            'error'
        );
        return;
    }

    // Validate that this is an ExpoXR settings file
    if (!isset($import_data['_export_info'])) {
        add_settings_error(
            'expoxr_messages',
            'expoxr_import_error',
            esc_html__('This does not appear to be a valid ExpoXR settings file. Missing export metadata.', 'explorexr'),
            'error'
        );
        return;
    }

    // Check if override is enabled
    $override = isset($_POST['expoxr_import_override']) && sanitize_text_field(wp_unslash($_POST['expoxr_import_override'])) === '1';
    
    // Import counts
    $imported = 0;
    $skipped = 0;
    $updated = 0;
    
    // Track modified settings categories
    $modified_categories = array();
    
    // Import settings
    // First, check if we're dealing with the new categorized format
    $categorized_format = isset($import_data['core_settings']) || 
                         isset($import_data['viewer_settings']) || 
                         isset($import_data['loading_settings']) || 
                         isset($import_data['other_settings']);
    
    if ($categorized_format) {
        // Process categorized settings
        $categories = array('core_settings', 'viewer_settings', 'loading_settings', 'other_settings');
        
        foreach ($categories as $category) {
            if (!isset($import_data[$category]) || !is_array($import_data[$category])) {
                continue;
            }
            
            foreach ($import_data[$category] as $option_name => $option_value) {
                // Ensure option_name is a string
                if (!is_string($option_name) || empty($option_name)) {
                    $skipped++;
                    continue;
                }
                
                // Only process ExpoXR options
                if (!is_string($option_name) || strpos($option_name, 'expoxr_') !== 0) {
                    $skipped++;
                    continue;
                }
                
                $existing_value = get_option($option_name, null);
                
                // Skip if option exists and override is not enabled
                if (!$override && $existing_value !== null) {
                    $skipped++;
                    continue;
                }
                
                // Determine if this is a new option or updating an existing one
                if ($existing_value === null) {
                    $imported++;
                } else {
                    $updated++;
                }
                
                // Update option
                update_option($option_name, $option_value);
                
                // Track which category of settings was modified
                if ($category === 'loading_settings') {
                    $modified_categories['loading'] = true;
                } elseif ($category === 'viewer_settings') {
                    $modified_categories['viewer'] = true;
                } elseif ($category === 'core_settings') {
                    $modified_categories['core'] = true;
                }
            }
        }
    } else {
        // Process old format (flat structure)
        foreach ($import_data as $option_name => $option_value) {
            // Ensure option_name is a string
            if (!is_string($option_name) || empty($option_name)) {
                $skipped++;
                continue;
            }
            
            // Skip metadata field
            if ($option_name === '_export_info') {
                continue;
            }
            
            // Only process ExpoXR options
            if (!is_string($option_name) || strpos($option_name, 'expoxr_') !== 0) {
                $skipped++;
                continue;
            }
            
            $existing_value = get_option($option_name, null);
            
            // Skip if option exists and override is not enabled
            if (!$override && $existing_value !== null) {
                $skipped++;
                continue;
            }
            
            // Determine if this is a new option or updating an existing one
            if ($existing_value === null) {
                $imported++;
            } else {
                $updated++;
            }
            
            // Update option
            update_option($option_name, $option_value);
            
            // Track which category of settings was modified
            // Make sure option_name is a string before using strpos
            if (is_string($option_name)) {
                if (strpos($option_name, 'expoxr_loading_') === 0) {
                    $modified_categories['loading'] = true;
                } elseif (strpos($option_name, 'expoxr_viewer_') === 0) {
                    $modified_categories['viewer'] = true;
                }
            }
        }
    }
    
    // Build category message
    $category_message = '';
    if (!empty($modified_categories)) {
        $category_list = array();
        if (isset($modified_categories['loading'])) {
            $category_list[] = esc_html__('Loading Options', 'explorexr');
        }
        if (isset($modified_categories['viewer'])) {
            $category_list[] = esc_html__('Viewer Settings', 'explorexr');
        }
        if (isset($modified_categories['core'])) {
            $category_list[] = esc_html__('Core Settings', 'explorexr');
        }
        
        if (!empty($category_list)) {
            $category_message = ' ' . sprintf(
                // translators: %s: List of modified setting categories
                esc_html__('Modified setting categories: %s.', 'explorexr'),
                implode(', ', $category_list)
            );
        }
    }

    // Display success message
    $message = sprintf(
        // translators: %1$d: Number of new settings added, %2$d: Number of existing settings updated, %3$d: Number of settings skipped
        esc_html__('Import completed successfully. Added %1$d new settings, updated %2$d existing settings, and skipped %3$d settings.', 'explorexr'),
        $imported,
        $updated,
        $skipped
    );
    
    $message .= $category_message;
    
    if (isset($import_data['_export_info'])) {
        $message .= '<br>';
        $message .= sprintf(
            // translators: %1$s: Site name, %2$s: Export date, %3$s: Plugin version
            esc_html__('Settings were exported from "%1$s" on %2$s (plugin version: %3$s).', 'explorexr'),
            esc_html($import_data['_export_info']['site']),
            esc_html($import_data['_export_info']['date']),
            esc_html($import_data['_export_info']['plugin_version'])
        );
    }
    
    add_settings_error('expoxr_messages', 'expoxr_import_success', $message, 'success');
}
add_action('admin_init', 'expoxr_handle_settings_import');

/**
 * Enqueue Import/Export assets
 */
function expoxr_enqueue_import_export_assets($hook) {
    // Only load on our settings page
    if ($hook !== 'expoxr_page_expoxr-settings') {
        return;
    }
    
    // Enqueue CSS
    wp_enqueue_style(
        'expoxr-import-export-css',
        EXPOXR_PLUGIN_URL . 'admin/css/import-export.css',
        array(),
        EXPOXR_VERSION
    );
    
    // Enqueue JS
    wp_enqueue_script(
        'expoxr-import-export-js',
        EXPOXR_PLUGIN_URL . 'admin/js/import-export.js',
        array('jquery'),
        EXPOXR_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'expoxr_enqueue_import_export_assets');





