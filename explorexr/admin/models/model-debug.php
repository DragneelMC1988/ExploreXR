<?php
/**
 * Model Debug Functionality
 * 
 * Provides debugging functions for 3D model issues
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add debug information to model preview URLs
 */
function explorexr_add_debug_to_url() {
    add_action('admin_footer', 'explorexr_add_model_debug_script');
}
add_action('admin_init', 'explorexr_add_debug_to_url');

/**
 * Add Model Debug AJAX handler
 */
function explorexr_register_model_debug_ajax() {
    add_action('wp_ajax_explorexr_check_model_file', 'explorexr_ajax_check_model_file');
}
add_action('admin_init', 'explorexr_register_model_debug_ajax');

/**
 * AJAX handler to check if a model file exists and is accessible
 */
function explorexr_ajax_check_model_file() {
    // Validate security using centralized function (using 'security' field)
    if (!isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'explorexr_admin_nonce')) {
        explorexr_log_security_event(
            'ajax_security_failure',
            'Model file check blocked: Invalid nonce',
            array('action' => 'check_model_file', 'nonce_provided' => isset($_POST['security']))
        );
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    // Check user capability
    if (!current_user_can('edit_posts')) {
        explorexr_log_security_event(
            'ajax_security_failure',
            'Model file check blocked: Insufficient permissions',
            array('action' => 'check_model_file', 'user_id' => get_current_user_id())
        );
        wp_send_json_error(array('message' => 'You do not have permission to check model files.'));
    }
    
    // Check rate limiting (max 20 checks per minute)
    if (!explorexr_check_rate_limit('check_model_file', 20, 60)) {
        explorexr_log_security_event(
            'rate_limit_exceeded',
            'Model file check rate limit exceeded',
            array('action' => 'check_model_file', 'limit' => 20)
        );
        wp_send_json_error(array('message' => 'Too many file check requests. Please wait before trying again.'));
    }
    
    // Get the file URL
    $file_url = isset($_POST['file_url']) ? sanitize_text_field(wp_unslash($_POST['file_url'])) : '';
    if (empty($file_url)) {
        wp_send_json_error(array('message' => 'No file URL provided'));
    }
    
    $result = array(
        'url' => $file_url,
        'exists' => false,
        'size' => 0,
        'mime_type' => '',
        'is_local' => false,
        'path' => '',
        'message' => ''
    );    // Check if it's a local file or remote URL
    $is_local = false;
    if (is_string($file_url)) {
        $is_local = (strpos($file_url, site_url()) !== false || strpos($file_url, 'http') !== 0);
    }
    $result['is_local'] = $is_local;      if ($is_local) {
        // Try to convert URL to local path
        $upload_dir = wp_upload_dir();
        
        // Ensure $file_url is a string before using str_replace
        if (is_string($file_url)) {
            $file_path = str_replace(
                array($upload_dir['baseurl'], site_url('/'), EXPLOREXR_PLUGIN_URL),
                array($upload_dir['basedir'], ABSPATH, EXPLOREXR_PLUGIN_DIR),
                $file_url
            );
            
            $result['path'] = $file_path;
        }
        
        // Check if file exists locally
        if (file_exists($file_path)) {
            $result['exists'] = true;
            $result['size'] = size_format(filesize($file_path));
            $result['mime_type'] = mime_content_type($file_path);
            $result['message'] = 'File exists and is accessible.';
        } else {
            $result['message'] = 'File does not exist at the specified path.';
        }
    } else {
        // Check remote file
        $response = wp_remote_head($file_url, array('timeout' => 15));
        
        if (!is_wp_error($response)) {
            $result['exists'] = wp_remote_retrieve_response_code($response) === 200;
            
            if ($result['exists']) {
                $result['size'] = size_format(wp_remote_retrieve_header($response, 'content-length'));
                $result['mime_type'] = wp_remote_retrieve_header($response, 'content-type');
                $result['message'] = 'Remote file exists and is accessible.';
            } else {
                $result['message'] = 'Remote file is not accessible. HTTP status: ' . wp_remote_retrieve_response_code($response);
            }
        } else {
            $result['message'] = 'Error checking remote file: ' . $response->get_error_message();
        }
    }
    
    wp_send_json_success($result);
}

/**
 * Add JavaScript for model debugging
 */
function explorexr_add_model_debug_script() {    // Only add on model-related pages
    $screen = get_current_screen();
    if (!$screen || (strpos($screen->id ?? '', 'explorexr') === false && $screen->id !== 'explorexr_model')) {
        return;
    }
    
    // Only enable debug buttons on the edit model page where advanced debugging is needed
    // Exclude dashboard, browse models, and other regular pages
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for conditional debug display only
    $current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    
    // Return early and show no debug buttons on any of these pages
    $excluded_pages = [
        'explorexr',               // Dashboard
        'explorexr-dashboard',     // Dashboard (alternate slug)
        'explorexr-browse-models', // Browse models
        'explorexr-create-model',  // Create model
        'explorexr-files',         // Model files
        'explorexr-settings',      // Settings
        'explorexr-loading-options', // Loading options
        'explorexr-license'        // License page
    ];
    
    if (in_array($current_page, $excluded_pages)) {
        return;
    }
    
    ?>
    
    <?php
    // Enqueue required scripts for model debug functionality
    wp_enqueue_script('jquery');
    
    // Model debug functionality
    $model_debug_script = "
    jQuery(document).ready(function($) {
        // Add debug button next to view buttons - only on specific debug and edit pages
        $('.view-3d-model').each(function() {
            const modelUrl = $(this).data('model-url');
            
            // Check if a debug button already exists
            if ($(this).next('.explorexr-debug-model').length === 0) {
                const \$debugButton = $('<button type=\"button\" class=\"button button-small explorexr-debug-model\" style=\"margin-left: 5px; background-color: #f0f0f0;\">' +
                            '<span class=\"dashicons dashicons-code-standards\" style=\"color: #0073aa;\"></span> Debug' +
                            '</button>');
                
                \$debugButton.data('model-url', modelUrl);
                $(this).after(\$debugButton);
            }
        });
        
        // Handle debug button click
        $(document).on('click', '.explorexr-debug-model', function(e) {
            e.preventDefault();
            const modelUrl = $(this).data('model-url');
            const \$button = $(this);
            
            // Disable button and show loading state
            \$button.prop('disabled', true);
            \$button.html('<span class=\"dashicons dashicons-update spinning\"></span>');
            
            // Send AJAX request to check file
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'explorexr_check_model_file',
                    file_url: modelUrl,
                    security: explorexr_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Create debug modal
                        const result = response.data;
                        const html = '<div class=\"explorexr-debug-modal\" style=\"position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 999999; background-color: rgba(0,0,0,0.5);\">' +
                            '<div class=\"explorexr-debug-modal-content\" style=\"position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; max-width: 800px; background-color: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 0 20px rgba(0,0,0,0.3);\">' +
                                '<span class=\"explorexr-debug-close\" style=\"position: absolute; top: 10px; right: 15px; font-size: 20px; cursor: pointer;\">&times;</span>' +
                                '<h2 style=\"margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;\">3D Model Debug Information</h2>' +
                                '<div class=\"explorexr-debug-results\" style=\"max-height: 400px; overflow-y: auto;\">' +
                                    '<p><strong>Result:</strong> <span style=\"color: ' + (result.exists ? 'green' : 'red') + ';\">' + result.message + '</span></p>' +
                                    '<h3>File Information</h3>' +
                                    '<table style=\"width: 100%; border-collapse: collapse;\">' +
                                        '<tr style=\"background-color: #f9f9f9;\">' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd; width: 30%;\"><strong>URL:</strong></td>' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd; word-break: break-all;\">' + result.url + '</td>' +
                                        '</tr>' +
                                        '<tr>' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd;\"><strong>File Exists:</strong></td>' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd;\">' + (result.exists ? 'Yes' : 'No') + '</td>' +
                                        '</tr>' +
                                        '<tr style=\"background-color: #f9f9f9;\">' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd;\"><strong>File Size:</strong></td>' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd;\">' + (result.size || 'N/A') + '</td>' +
                                        '</tr>' +
                                        '<tr>' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd;\"><strong>MIME Type:</strong></td>' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd;\">' + (result.mime_type || 'N/A') + '</td>' +
                                        '</tr>' +
                                        '<tr style=\"background-color: #f9f9f9;\">' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd;\"><strong>File Location:</strong></td>' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd;\">' + (result.is_local ? 'Local' : 'Remote') + '</td>' +
                                        '</tr>' +
                                        (result.is_local ? '<tr>' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd;\"><strong>Server Path:</strong></td>' +
                                            '<td style=\"padding: 8px; border: 1px solid #ddd; word-break: break-all;\">' + result.path + '</td>' +
                                        '</tr>' : '') +
                                    '</table>' +
                                    '<div style=\"margin-top: 20px; background-color: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa;\">' +
                                        '<h3 style=\"margin-top: 0;\">Troubleshooting Tips</h3>' +
                                        '<ul style=\"margin-left: 20px;\">' +
                                            '<li>Make sure the file exists at the specified path</li>' +
                                            '<li>Check that the file has the correct MIME type (should be model/gltf-binary for .glb files)</li>' +
                                            '<li>Ensure the file is accessible via the web (try opening the URL directly in your browser)</li>' +
                                            '<li>Verify that the file is a valid 3D model in GLB or GLTF format</li>' +
                                            '<li>Check for file permission issues if the file exists but cannot be accessed</li>' +
                                        '</ul>' +
                                    '</div>' +
                                '</div>' +
                                '<div style=\"margin-top: 20px; text-align: right;\">' +
                                    '<button class=\"button explorexr-debug-close-btn\">Close</button>' +
                                '</div>' +
                            '</div>' +
                        '</div>';
                        
                        // Append to body
                        \$('body').append(html);
                        
                        // Handle close button click
                        \$('.explorexr-debug-close, .explorexr-debug-close-btn').on('click', function() {
                            \$('.explorexr-debug-modal').remove();
                        });
                    } else {
                        alert('Error checking file: ' + (response.data ? response.data.message : 'Unknown error'));
                    }
                    
                    // Reset button
                    \$button.prop('disabled', false);
                    \$button.html('<span class=\"dashicons dashicons-code-standards\" style=\"color: #0073aa;\"></span> Debug');
                },
                error: function() {
                    alert('Error connecting to server');
                    
                    // Reset button
                    \$button.prop('disabled', false);
                    \$button.html('<span class=\"dashicons dashicons-code-standards\" style=\"color: #0073aa;\"></span> Debug');
                }
            });
        });
    });
    ";
    
    wp_add_inline_script('jquery', $model_debug_script);
    ?>
    
    <?php
}





