<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Safely include a template file with error handling
 * 
 * @param string $template_path The path to the template file
 * @param string $fallback_path Optional fallback path if the primary template doesn't exist
 * @param array $vars Optional variables to pass to the template
 * @return bool True if template was included successfully, false otherwise
 */
function ExploreXR_safe_include_template($template_path, $fallback_path = '', $vars = array()) {
    // If no additional vars provided, share all variables from the calling scope
    if (empty($vars)) {
        // Import variables from the parent scope
        $vars = array();
        foreach ($GLOBALS as $key => $value) {
            if ($key != 'GLOBALS' && !is_object($value) && !is_array($value)) {
                $vars[$key] = $value;
            }
        }
        


        // These variables are always needed
        if (!isset($vars['model_id'])) {
            $vars['model_id'] = isset($GLOBALS['model_id']) ? $GLOBALS['model_id'] : 0;
        }
        
        if (!isset($vars['camera_controls'])) {
            $vars['camera_controls'] = isset($GLOBALS['camera_controls']) ? $GLOBALS['camera_controls'] : false;
        }
        
        if (!isset($vars['auto_rotate'])) {
            $vars['auto_rotate'] = isset($GLOBALS['auto_rotate']) ? $GLOBALS['auto_rotate'] : false;
        }
    }

    // Extract variables to make them available in the template scope
    if (!empty($vars) && is_array($vars)) {
        extract($vars);
    }
    
    if (file_exists($template_path)) {
        include $template_path;
        return true;
    } elseif (!empty($fallback_path) && file_exists($fallback_path)) {
        include $fallback_path;
        return true;
    } else {
        // Silently fail for missing templates in admin area to prevent header issues
        // Log error if logging function is available
        if (function_exists('explorexr_log')) {
            explorexr_log('Template file not found: ' . $template_path);
        }
        return false;
    }
}

/**
 * Custom Edit Model Page
 * 
 * A modern UI for editing 3D models that matches the plugin's styling
 * instead of using the WordPress standard editor.
 * 
 * @package ExploreXR
 */
function ExploreXR_edit_model_page() {
    // Get the model ID from the URL
    $model_id = isset($_GET['model_id']) ? intval($_GET['model_id']) : 0;
    
    // Make sure all styling/scripts for this custom screen are definitely enqueued
    if (function_exists('explorexr_enqueue_edit_model_styles')) {
        explorexr_enqueue_edit_model_styles();
    } else {
        // Fallback: enqueue the key styles/scripts directly
        wp_enqueue_style('explorexr-admin-styles', EXPLOREXR_PLUGIN_URL . 'admin/css/admin-styles.css', array(), EXPLOREXR_VERSION);
        wp_enqueue_style('explorexr-button-system', EXPLOREXR_PLUGIN_URL . 'admin/css/button-system.css', array('explorexr-admin-styles'), EXPLOREXR_VERSION);
        wp_enqueue_style('explorexr-edit-model-css', EXPLOREXR_PLUGIN_URL . 'admin/css/edit-model.css', array('explorexr-button-system'), EXPLOREXR_VERSION);
        wp_enqueue_style('explorexr-addon-cards-css', EXPLOREXR_PLUGIN_URL . 'admin/css/addon-cards.css', array('explorexr-button-system'), EXPLOREXR_VERSION);
        wp_enqueue_style('explorexr-addon-cards-shared-css', EXPLOREXR_PLUGIN_URL . 'admin/css/addon-cards-shared.css', array('explorexr-button-system'), EXPLOREXR_VERSION);
        wp_enqueue_script('explorexr-premium-model-viewer');
    }
    
    // Enqueue size validation and preview scripts
    wp_enqueue_script(
        'explorexr-size-validation',
        EXPLOREXR_PLUGIN_URL . 'admin/js/size-validation.js',
        array('jquery'),
        EXPLOREXR_VERSION,
        true
    );
    
    wp_enqueue_script(
        'explorexr-preview-size-sync',
        EXPLOREXR_PLUGIN_URL . 'admin/js/preview-size-sync.js',
        array('jquery'),
        EXPLOREXR_VERSION,
        true
    );
    
    wp_enqueue_script(
        'explorexr-size-preview-indicator',
        EXPLOREXR_PLUGIN_URL . 'admin/js/size-preview-indicator.js',
        array('jquery'),
        EXPLOREXR_VERSION,
        true
    );
    
    // Check if the model exists and is valid
    // Accept both explorexr_model and explorexr_premium_model post types
    $post_type = get_post_type($model_id);
    $valid_post_types = array('explorexr_model', 'explorexr_premium_model');
    
    if (!$model_id || !in_array($post_type, $valid_post_types, true)) {
        // Set error notification and redirect to dashboard
        add_option('explorexr_admin_notice', array(
            'type' => 'error',
            'message' => 'The requested model could not be found. Please check the model ID and try again.'
        ));
        wp_safe_redirect(admin_url('admin.php?page=explorexr'));
        exit;
    }
    
    // Verify the model post exists
    $model = get_post($model_id);
    if (!$model) {
        // Set error notification and redirect to dashboard
        add_option('explorexr_admin_notice', array(
            'type' => 'error',
            'message' => 'The requested model no longer exists. It may have been deleted.'
        ));
        wp_safe_redirect(admin_url('admin.php?page=explorexr'));
        exit;
    }
    
    // Ensure model-viewer is available for previews on this page
    ExploreXR_safe_include_template(EXPLOREXR_PLUGIN_DIR . 'template-parts/model-viewer-script.php', '', array('model_id' => $model_id));
    
    // Make sure the WordPress Media Library scripts are loaded
    wp_enqueue_media();
    
    // Initialize variables for messages
    $success_message = '';
    $error_message = '';
    
    // Get the model data
    $model = get_post($model_id);
    $model_title = $model ? $model->post_title : '';
    $model_description = $model ? $model->post_content : '';
    
    // Get model meta data
    $model_file = get_post_meta($model_id, '_explorexr_model_file', true) ?: '';
    $model_name = get_post_meta($model_id, '_explorexr_model_name', true) ?: '';
    $model_alt_text = get_post_meta($model_id, '_explorexr_model_alt_text', true) ?: '';
    
    // Get size settings
    $viewer_size = get_post_meta($model_id, '_explorexr_viewer_size', true) ?: 'custom';
    $viewer_width = get_post_meta($model_id, '_explorexr_viewer_width', true) ?: '100vw';
    $viewer_height = get_post_meta($model_id, '_explorexr_viewer_height', true) ?: '500px';
    $tablet_viewer_width = get_post_meta($model_id, '_explorexr_tablet_viewer_width', true) ?: '';
    $tablet_viewer_height = get_post_meta($model_id, '_explorexr_tablet_viewer_height', true) ?: '';
    $mobile_viewer_width = get_post_meta($model_id, '_explorexr_mobile_viewer_width', true) ?: '';
    $mobile_viewer_height = get_post_meta($model_id, '_explorexr_mobile_viewer_height', true) ?: '';

    // Get poster information
    $poster_url = get_post_meta($model_id, '_explorexr_model_poster', true) ?: '';
    $poster_id = get_post_meta($model_id, '_explorexr_model_poster_id', true) ?: '';
    
    // Camera controls and animation settings
    
    // Interaction controls with backward compatibility
    $enable_interactions_meta = get_post_meta($model_id, '_explorexr_enable_interactions', true) ?: '';
    if ($enable_interactions_meta === '') {
        // If not set, default to enabled (true) for new models
        $enable_interactions = true;
        // Set the default value in the database
        update_post_meta($model_id, '_explorexr_enable_interactions', 'on');
    } else {
        $enable_interactions = ($enable_interactions_meta === 'on');
    }
    
    // Auto-rotate controls with backward compatibility
    $auto_rotate_meta = get_post_meta($model_id, '_explorexr_auto_rotate', true) ?: '';
    if ($auto_rotate_meta === '') {
        // If not set, default to disabled (false) for new models
        $auto_rotate = false;
        // Set the default value in the database
        update_post_meta($model_id, '_explorexr_auto_rotate', 'off');
    } else {
        $auto_rotate = ($auto_rotate_meta === 'on');
    }
    // Animation settings are not available in the Free version
    // This feature is available in the Pro version only
    
    // Get available existing models (for model dropdown)
    $uploaded_files = explorexr_get_model_files_from_directory();
    $existing_models = array();
    foreach ($uploaded_files as $file) {
        // Ensure URL and name are strings, never null
        $file_url = isset($file['url']) ? (string) $file['url'] : '';
        $file_name = isset($file['name']) ? (string) $file['name'] : '';
        if (!empty($file_url) && !empty($file_name)) {
            $existing_models[$file_url] = $file_name;
        }
    }
    
    // Load size validator class for validation
    require_once EXPLOREXR_PLUGIN_DIR . 'includes/utils/size-validator.php';
    
    // Handle form submission
    if (isset($_POST['ExploreXR_edit_model_submit']) && check_admin_referer('explorexr_edit_model', 'explorexr_edit_nonce')) {
        // Update post title and content
        $updated_post = array(
            'ID' => $model_id,
            'post_title' => isset($_POST['model_title']) ? sanitize_text_field(wp_unslash($_POST['model_title'])) : '',
            'post_content' => isset($_POST['model_description']) ? wp_kses_post(wp_unslash($_POST['model_description'])) : ''
        );
        
        $update_result = wp_update_post($updated_post);
        
        if (!is_wp_error($update_result)) {
            // Process model file changes
            if (isset($_POST['model_source']) && $_POST['model_source'] === 'upload') {
                if (isset($_FILES['model_file']) && isset($_FILES['model_file']['size']) && $_FILES['model_file']['size'] > 0) {
                    // Manually sanitize $_FILES data to avoid nonce verification warnings
                    // Note: tmp_name is NOT sanitized - it's a server-generated path that must remain unchanged
                    $file_upload = array(
                        'name' => isset($_FILES['model_file']['name']) ? sanitize_file_name(wp_unslash($_FILES['model_file']['name'])) : '',
                        'type' => isset($_FILES['model_file']['type']) ? sanitize_mime_type(wp_unslash($_FILES['model_file']['type'])) : '',
                        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- tmp_name is server-generated path, sanitization breaks file operations
                        'tmp_name' => isset($_FILES['model_file']['tmp_name']) ? wp_unslash($_FILES['model_file']['tmp_name']) : '',
                        'error' => isset($_FILES['model_file']['error']) ? absint($_FILES['model_file']['error']) : UPLOAD_ERR_NO_FILE,
                        'size' => isset($_FILES['model_file']['size']) ? absint($_FILES['model_file']['size']) : 0,
                    );
                    
                    // Validate the sanitized file data
                    $sanitized_file = explorexr_validate_model_file_upload($file_upload);
                    
                    if (is_wp_error($sanitized_file)) {
                        // Handle validation error
                        $error_message = 'File validation failed: ' . $sanitized_file->get_error_message();
                    } else {
                        // Handle file upload with sanitized file
                        $upload_result = explorexr_handle_model_upload($sanitized_file);
                    
                        if ($upload_result && !is_wp_error($upload_result)) {
                            update_post_meta($model_id, '_explorexr_model_file', $upload_result['file_url']);
                            
                            // If model name is empty, set it from the filename
                            if (empty($_POST['model_name'])) {
                                $filename = basename($upload_result['file_url']);
                                $model_name = preg_replace('/\.[^.]+$/', '', $filename);
                                update_post_meta($model_id, '_explorexr_model_name', $model_name);
                            }
                        } else {
                            $error_message = 'Unable to upload model file: ' . ($upload_result['error'] ?? 'Unknown error');
                        }
                    }
                }
            } else if (isset($_POST['model_source']) && $_POST['model_source'] === 'existing' && !empty($_POST['existing_model'])) {
                // Use existing model
                update_post_meta($model_id, '_explorexr_model_file', sanitize_text_field(wp_unslash($_POST['existing_model'])));
            }
            
            // Process model name and alt text
            if (isset($_POST['model_name'])) {
                update_post_meta($model_id, '_explorexr_model_name', sanitize_text_field(wp_unslash($_POST['model_name'])));
            }
            
            if (isset($_POST['model_alt_text'])) {
                update_post_meta($model_id, '_explorexr_model_alt_text', sanitize_text_field(wp_unslash($_POST['model_alt_text'])));
            }
            
            // Process size settings with validation
            if (isset($_POST['viewer_size'])) {
                update_post_meta($model_id, '_explorexr_viewer_size', sanitize_text_field(wp_unslash($_POST['viewer_size'])));
            }
            
            // Validate and save desktop sizes
            $width_input = isset($_POST['viewer_width']) ? sanitize_text_field(wp_unslash($_POST['viewer_width'])) : '';
            $height_input = isset($_POST['viewer_height']) ? sanitize_text_field(wp_unslash($_POST['viewer_height'])) : '';
            
            if (!empty($width_input) && !empty($height_input)) {
                $validation_result = ExploreXR_Size_Validator::validate_size_pair($width_input, $height_input);
                
                if ($validation_result['valid']) {
                    update_post_meta($model_id, '_explorexr_viewer_width', $width_input);
                    update_post_meta($model_id, '_explorexr_viewer_height', $height_input);
                } else {
                    // Store error for display
                    $error_message = 'Desktop size: ' . $validation_result['error'];
                    
                    // Use fallback safe values
                    update_post_meta($model_id, '_explorexr_viewer_width', '100vw');
                    update_post_meta($model_id, '_explorexr_viewer_height', '500px');
                }
            } else if (!empty($width_input)) {
                $width_clean = ExploreXR_Size_Validator::sanitize_dimension($width_input, '100vw');
                update_post_meta($model_id, '_explorexr_viewer_width', $width_clean);
            } else if (!empty($height_input)) {
                $height_clean = ExploreXR_Size_Validator::sanitize_dimension($height_input, '500px');
                update_post_meta($model_id, '_explorexr_viewer_height', $height_clean);
            }
            
            // Validate and save tablet sizes (clear meta when fields are empty)
            $tablet_width = isset($_POST['tablet_viewer_width']) ? sanitize_text_field(wp_unslash($_POST['tablet_viewer_width'])) : '';
            $tablet_height = isset($_POST['tablet_viewer_height']) ? sanitize_text_field(wp_unslash($_POST['tablet_viewer_height'])) : '';

            if (!empty($tablet_width) && !empty($tablet_height)) {
                $tablet_validation = ExploreXR_Size_Validator::validate_size_pair($tablet_width, $tablet_height);
                if ($tablet_validation['valid']) {
                    update_post_meta($model_id, '_explorexr_tablet_viewer_width', $tablet_width);
                    update_post_meta($model_id, '_explorexr_tablet_viewer_height', $tablet_height);
                } else {
                    delete_post_meta($model_id, '_explorexr_tablet_viewer_width');
                    delete_post_meta($model_id, '_explorexr_tablet_viewer_height');
                    if (empty($error_message)) {
                        $error_message = 'Tablet size: ' . $tablet_validation['error'];
                    }
                }
            } elseif (!empty($tablet_width)) {
                $clean = ExploreXR_Size_Validator::sanitize_dimension($tablet_width, '');
                if ($clean) {
                    update_post_meta($model_id, '_explorexr_tablet_viewer_width', $clean);
                } else {
                    delete_post_meta($model_id, '_explorexr_tablet_viewer_width');
                }
                delete_post_meta($model_id, '_explorexr_tablet_viewer_height');
            } elseif (!empty($tablet_height)) {
                $clean = ExploreXR_Size_Validator::sanitize_dimension($tablet_height, '');
                if ($clean) {
                    update_post_meta($model_id, '_explorexr_tablet_viewer_height', $clean);
                } else {
                    delete_post_meta($model_id, '_explorexr_tablet_viewer_height');
                }
                delete_post_meta($model_id, '_explorexr_tablet_viewer_width');
            } else {
                // Both empty — clear old meta so stale values don't persist
                delete_post_meta($model_id, '_explorexr_tablet_viewer_width');
                delete_post_meta($model_id, '_explorexr_tablet_viewer_height');
            }

            // Validate and save mobile sizes (clear meta when fields are empty)
            $mobile_width = isset($_POST['mobile_viewer_width']) ? sanitize_text_field(wp_unslash($_POST['mobile_viewer_width'])) : '';
            $mobile_height = isset($_POST['mobile_viewer_height']) ? sanitize_text_field(wp_unslash($_POST['mobile_viewer_height'])) : '';

            if (!empty($mobile_width) && !empty($mobile_height)) {
                $mobile_validation = ExploreXR_Size_Validator::validate_size_pair($mobile_width, $mobile_height);
                if ($mobile_validation['valid']) {
                    update_post_meta($model_id, '_explorexr_mobile_viewer_width', $mobile_width);
                    update_post_meta($model_id, '_explorexr_mobile_viewer_height', $mobile_height);
                } else {
                    delete_post_meta($model_id, '_explorexr_mobile_viewer_width');
                    delete_post_meta($model_id, '_explorexr_mobile_viewer_height');
                    if (empty($error_message)) {
                        $error_message = 'Mobile size: ' . $mobile_validation['error'];
                    }
                }
            } elseif (!empty($mobile_width)) {
                $clean = ExploreXR_Size_Validator::sanitize_dimension($mobile_width, '');
                if ($clean) {
                    update_post_meta($model_id, '_explorexr_mobile_viewer_width', $clean);
                } else {
                    delete_post_meta($model_id, '_explorexr_mobile_viewer_width');
                }
                delete_post_meta($model_id, '_explorexr_mobile_viewer_height');
            } elseif (!empty($mobile_height)) {
                $clean = ExploreXR_Size_Validator::sanitize_dimension($mobile_height, '');
                if ($clean) {
                    update_post_meta($model_id, '_explorexr_mobile_viewer_height', $clean);
                } else {
                    delete_post_meta($model_id, '_explorexr_mobile_viewer_height');
                }
                delete_post_meta($model_id, '_explorexr_mobile_viewer_width');
            } else {
                // Both empty — clear old meta so stale values don't persist
                delete_post_meta($model_id, '_explorexr_mobile_viewer_width');
                delete_post_meta($model_id, '_explorexr_mobile_viewer_height');
            }
            
            // Process poster image
            if (isset($_POST['poster_method']) && $_POST['poster_method'] === 'upload') {
                if (isset($_FILES['model_poster']) && isset($_FILES['model_poster']['size']) && $_FILES['model_poster']['size'] > 0) {
                    // Upload new poster image
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/media.php');
                    
                    $poster_attachment_id = media_handle_upload('model_poster', $model_id);
                    if (!is_wp_error($poster_attachment_id)) {
                        $poster_url = wp_get_attachment_url($poster_attachment_id);
                        update_post_meta($model_id, '_explorexr_model_poster', $poster_url);
                        update_post_meta($model_id, '_explorexr_model_poster_id', $poster_attachment_id);
                    }
                }
            } else if (isset($_POST['poster_method']) && $_POST['poster_method'] === 'library') {
                if (isset($_POST['model_poster_id']) && !empty($_POST['model_poster_id'])) {
                    $new_poster_id = intval($_POST['model_poster_id']);
                    $new_poster_url = wp_get_attachment_url($new_poster_id);
                    
                    update_post_meta($model_id, '_explorexr_model_poster', $new_poster_url);
                    update_post_meta($model_id, '_explorexr_model_poster_id', $new_poster_id);
                }
            }
            
            // Handle removing poster if checkbox is checked
            if (isset($_POST['remove_poster']) && $_POST['remove_poster'] == '1') {
                delete_post_meta($model_id, '_explorexr_model_poster');
                delete_post_meta($model_id, '_explorexr_model_poster_id');
                $poster_url = '';
                $poster_id = '';
            }
            
            // Handle enable interactions
            $enable_interactions_value = isset($_POST['explorexr_enable_interactions']) ? 'on' : 'off';
            update_post_meta($model_id, '_explorexr_enable_interactions', $enable_interactions_value);

            // Handle auto-rotate
            $auto_rotate_value = isset($_POST['explorexr_auto_rotate']) ? 'on' : 'off';
            update_post_meta($model_id, '_explorexr_auto_rotate', $auto_rotate_value);

            // Handle auto-rotate delay and speed
            if (isset($_POST['explorexr_auto_rotate_delay'])) {
                $auto_rotate_delay = sanitize_text_field(wp_unslash($_POST['explorexr_auto_rotate_delay']));
                update_post_meta($model_id, '_explorexr_auto_rotate_delay', $auto_rotate_delay);
            }

            if (isset($_POST['explorexr_auto_rotate_speed'])) {
                $auto_rotate_speed = sanitize_text_field(wp_unslash($_POST['explorexr_auto_rotate_speed']));
                update_post_meta($model_id, '_explorexr_rotation_per_second', $auto_rotate_speed);
            }
            
            // Animation settings are not available in the Free version
            // This feature is available in the Pro version only

            // Loading options are handled by the core plugin in free version
            // Premium features like individual loading options per model are not available
            
            // Save addon-specific per-model settings
            // AR Addon Settings (new premium keys)
            $ar_enabled = isset($_POST['explorexr_premium_ar_enabled']) ? 'on' : 'off';
            update_post_meta($model_id, '_explorexr_premium_ar_enabled', $ar_enabled);
            // keep legacy key in sync for backward compatibility
            update_post_meta($model_id, '_explorexr_ar_enabled', $ar_enabled === 'on' ? '1' : '0');
            
            $ar_modes = array();
            if (isset($_POST['explorexr_premium_ar_modes']) && is_array($_POST['explorexr_premium_ar_modes'])) {
                $ar_modes = array_map('sanitize_text_field', wp_unslash($_POST['explorexr_premium_ar_modes']));
            }
            update_post_meta($model_id, '_explorexr_premium_ar_modes', $ar_modes);
            
            if (isset($_POST['explorexr_premium_ar_ios_src'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_ios_src', esc_url_raw(wp_unslash($_POST['explorexr_premium_ar_ios_src'])));
            }
            if (isset($_POST['explorexr_premium_ar_usdz_model'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_usdz_model', esc_url_raw(wp_unslash($_POST['explorexr_premium_ar_usdz_model'])));
            }
            if (isset($_POST['explorexr_premium_ar_placement'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_placement', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_placement'])));
            }
            if (isset($_POST['explorexr_premium_ar_scale'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_scale', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_scale'])));
            }
            if (isset($_POST['explorexr_premium_ar_min_height'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_min_height', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_min_height'])));
            }
            if (isset($_POST['explorexr_premium_ar_button_text'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_button_text', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_button_text'])));
            }
            if (isset($_POST['explorexr_premium_ar_button_bg_color'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_button_bg_color', sanitize_hex_color(wp_unslash($_POST['explorexr_premium_ar_button_bg_color'])));
            }
            if (isset($_POST['explorexr_premium_ar_button_text_color'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_button_text_color', sanitize_hex_color(wp_unslash($_POST['explorexr_premium_ar_button_text_color'])));
            }
            if (isset($_POST['explorexr_premium_ar_button_border_color'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_button_border_color', sanitize_hex_color(wp_unslash($_POST['explorexr_premium_ar_button_border_color'])));
            }
            if (isset($_POST['explorexr_premium_ar_button_size'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_button_size', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_button_size'])));
            }
            if (isset($_POST['explorexr_premium_ar_button_border_radius'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_button_border_radius', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_button_border_radius'])));
            }
            if (isset($_POST['explorexr_premium_ar_button_position'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_button_position', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_button_position'])));
            }
            if (isset($_POST['explorexr_premium_ar_button_icon'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_button_icon', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_button_icon'])));
            }
            if (isset($_POST['explorexr_premium_ar_button_icon_position'])) {
                update_post_meta($model_id, '_explorexr_premium_ar_button_icon_position', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_button_icon_position'])));
            }
            $ar_button_icon_enabled = isset($_POST['explorexr_premium_ar_button_icon_enabled']) ? '1' : '0';
            update_post_meta($model_id, '_explorexr_premium_ar_button_icon_enabled', $ar_button_icon_enabled);
            
            // Mouse3D Addon Settings
            $mouse3d_enabled = isset($_POST['explorexr_mouse3d_enabled']) ? '1' : '0';
            update_post_meta($model_id, '_explorexr_mouse3d_enabled', $mouse3d_enabled);
            if (isset($_POST['explorexr_mouse3d_enable_rotation'])) {
                update_post_meta($model_id, '_explorexr_mouse3d_enable_rotation', '1');
            } else {
                update_post_meta($model_id, '_explorexr_mouse3d_enable_rotation', '0');
            }
            
            if (isset($_POST['explorexr_mouse3d_rotation_direction'])) {
                update_post_meta($model_id, '_explorexr_mouse3d_rotation_direction', sanitize_text_field(wp_unslash($_POST['explorexr_mouse3d_rotation_direction'])));
            }
            
            if (isset($_POST['explorexr_mouse3d_rotation_strength'])) {
                update_post_meta($model_id, '_explorexr_mouse3d_rotation_strength', sanitize_text_field(wp_unslash($_POST['explorexr_mouse3d_rotation_strength'])));
            }
            
            if (isset($_POST['explorexr_mouse3d_enable_zoom'])) {
                update_post_meta($model_id, '_explorexr_mouse3d_enable_zoom', '1');
            } else {
                update_post_meta($model_id, '_explorexr_mouse3d_enable_zoom', '0');
            }
            
            if (isset($_POST['explorexr_mouse3d_reverse_rotation_x'])) {
                update_post_meta($model_id, '_explorexr_mouse3d_reverse_rotation_x', '1');
            } else {
                update_post_meta($model_id, '_explorexr_mouse3d_reverse_rotation_x', '0');
            }
            
            if (isset($_POST['explorexr_mouse3d_reverse_rotation_y'])) {
                update_post_meta($model_id, '_explorexr_mouse3d_reverse_rotation_y', '1');
            } else {
                update_post_meta($model_id, '_explorexr_mouse3d_reverse_rotation_y', '0');
            }
            
            // Annotations Addon - Save annotations data
            if (function_exists('explorexr_premium_annotations_addon_save_model_options')) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging behind WP_DEBUG
                    error_log('🔍 ExploreXR Edit Model: Calling annotation save function');
                }
                explorexr_premium_annotations_addon_save_model_options($model_id);
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging behind WP_DEBUG
                    error_log('🔍 ExploreXR Edit Model: Annotation save function not found');
                }
            }
            
            // Loading Addon Settings
            $loading_enabled = isset($_POST['explorexr_loading_enable']) ? '1' : '0';
            update_post_meta($model_id, '_explorexr_loading_enable', $loading_enabled);
            
            // Save use individual loading setting
            if (isset($_POST['explorexr_premium_use_individual_loading'])) {
                $use_individual_loading = $_POST['explorexr_premium_use_individual_loading'] === 'on' ? 'on' : 'off';
                update_post_meta($model_id, '_explorexr_premium_use_individual_loading', $use_individual_loading);
            }
            
            // Save loading bar color
            if (isset($_POST['explorexr_premium_loading_bar_color'])) {
                update_post_meta($model_id, '_explorexr_premium_loading_bar_color', sanitize_hex_color(wp_unslash($_POST['explorexr_premium_loading_bar_color'])));
            }
            
            // Save loading bar height
            if (isset($_POST['explorexr_premium_loading_bar_height'])) {
                update_post_meta($model_id, '_explorexr_premium_loading_bar_height', sanitize_text_field(wp_unslash($_POST['explorexr_premium_loading_bar_height'])));
            }
            
            // Save loading bar position
            if (isset($_POST['explorexr_premium_loading_bar_position'])) {
                update_post_meta($model_id, '_explorexr_premium_loading_bar_position', sanitize_text_field(wp_unslash($_POST['explorexr_premium_loading_bar_position'])));
            }
            
            // Save percentage show
            if (isset($_POST['explorexr_premium_percentage_show'])) {
                update_post_meta($model_id, '_explorexr_premium_percentage_show', 'on');
            } else {
                update_post_meta($model_id, '_explorexr_premium_percentage_show', 'off');
            }
            
            // Save percentage precision
            if (isset($_POST['explorexr_premium_percentage_precision'])) {
                update_post_meta($model_id, '_explorexr_premium_percentage_precision', intval($_POST['explorexr_premium_percentage_precision']));
            }
            
            // Save percentage suffix
            if (isset($_POST['explorexr_premium_percentage_suffix'])) {
                update_post_meta($model_id, '_explorexr_premium_percentage_suffix', sanitize_text_field(wp_unslash($_POST['explorexr_premium_percentage_suffix'])));
            }
            
            // Save loading text show
            if (isset($_POST['explorexr_premium_loading_text_show'])) {
                update_post_meta($model_id, '_explorexr_premium_loading_text_show', 'on');
            } else {
                update_post_meta($model_id, '_explorexr_premium_loading_text_show', 'off');
            }
            
            // Save loading text content
            if (isset($_POST['explorexr_premium_loading_text_content'])) {
                update_post_meta($model_id, '_explorexr_premium_loading_text_content', sanitize_text_field(wp_unslash($_POST['explorexr_premium_loading_text_content'])));
            }
            
            // Save loading text position
            if (isset($_POST['explorexr_premium_loading_text_position'])) {
                update_post_meta($model_id, '_explorexr_premium_loading_text_position', sanitize_text_field(wp_unslash($_POST['explorexr_premium_loading_text_position'])));
            }
            
            // Save loading text color
            if (isset($_POST['explorexr_premium_loading_text_color'])) {
                update_post_meta($model_id, '_explorexr_premium_loading_text_color', sanitize_hex_color(wp_unslash($_POST['explorexr_premium_loading_text_color'])));
            }
            
            // Save overlay show
            if (isset($_POST['explorexr_premium_overlay_show'])) {
                update_post_meta($model_id, '_explorexr_premium_overlay_show', 'on');
            } else {
                update_post_meta($model_id, '_explorexr_premium_overlay_show', 'off');
            }
            
            // Save overlay color
            if (isset($_POST['explorexr_premium_overlay_color'])) {
                update_post_meta($model_id, '_explorexr_premium_overlay_color', sanitize_text_field(wp_unslash($_POST['explorexr_premium_overlay_color'])));
            }
            
            // Save overlay blur
            if (isset($_POST['explorexr_premium_overlay_blur'])) {
                update_post_meta($model_id, '_explorexr_premium_overlay_blur', 'on');
            } else {
                update_post_meta($model_id, '_explorexr_premium_overlay_blur', 'off');
            }
            
            // Save per-model load behavior override (replaces legacy lazy_load_model checkbox)
            $explorexr_allowed_behaviors = array('', 'direct', 'poster_button', 'lazy');
            $explorexr_load_behavior = isset($_POST['explorexr_premium_load_behavior'])
                ? sanitize_text_field(wp_unslash($_POST['explorexr_premium_load_behavior']))
                : '';
            if (!in_array($explorexr_load_behavior, $explorexr_allowed_behaviors, true)) {
                $explorexr_load_behavior = '';
            }
            update_post_meta($model_id, '_explorexr_premium_load_behavior', $explorexr_load_behavior);

            // Save per-model poster lazy-load override
            $explorexr_allowed_poster_lazy = array('', 'on', 'off');
            $explorexr_poster_lazy = isset($_POST['explorexr_premium_lazy_load_poster'])
                ? sanitize_text_field(wp_unslash($_POST['explorexr_premium_lazy_load_poster']))
                : '';
            if (!in_array($explorexr_poster_lazy, $explorexr_allowed_poster_lazy, true)) {
                $explorexr_poster_lazy = '';
            }
            update_post_meta($model_id, '_explorexr_premium_lazy_load_poster', $explorexr_poster_lazy);
            
            // Save script location
            if (isset($_POST['explorexr_premium_script_location'])) {
                update_post_meta($model_id, '_explorexr_premium_script_location', sanitize_text_field(wp_unslash($_POST['explorexr_premium_script_location'])));
            }
            
            // NOTE: Materials addon now uses its own metabox save handler in settings.php
            // All materials configuration is saved via the save_post action hook: explorexr_premium_materials_save_metabox()
            
            // Animation Addon Settings
            // Save animation enabled status
            if (isset($_POST['explorexr_premium_animation_enabled'])) {
                update_post_meta($model_id, '_explorexr_premium_animation_enabled', 'on');
            } else {
                update_post_meta($model_id, '_explorexr_premium_animation_enabled', 'off');
            }
            
            // Save animation name
            if (isset($_POST['explorexr_premium_animation_name'])) {
                update_post_meta($model_id, '_explorexr_premium_animation_name', sanitize_text_field(wp_unslash($_POST['explorexr_premium_animation_name'])));
            }
            
            // Save animation autoplay
            if (isset($_POST['explorexr_premium_animation_autoplay'])) {
                update_post_meta($model_id, '_explorexr_premium_animation_autoplay', 'on');
            } else {
                update_post_meta($model_id, '_explorexr_premium_animation_autoplay', 'off');
            }
            
            // Save animation repeat mode
            if (isset($_POST['explorexr_premium_animation_repeat'])) {
                update_post_meta($model_id, '_explorexr_premium_animation_repeat', sanitize_text_field(wp_unslash($_POST['explorexr_premium_animation_repeat'])));
            }
            
            // Save animation loop (deprecated but kept for compatibility)
            if (isset($_POST['explorexr_premium_animation_loop'])) {
                update_post_meta($model_id, '_explorexr_premium_animation_loop', 'on');
            } else {
                update_post_meta($model_id, '_explorexr_premium_animation_loop', 'off');
            }
            
            // Save multiple animations enabled
            if (isset($_POST['explorexr_premium_multiple_animations_enabled'])) {
                update_post_meta($model_id, '_explorexr_premium_multiple_animations_enabled', 'on');
            } else {
                update_post_meta($model_id, '_explorexr_premium_multiple_animations_enabled', 'off');
            }
            
            // Save selected animations array
            if (isset($_POST['explorexr_selected_animations']) && is_array($_POST['explorexr_selected_animations'])) {
                $selected_animations = array_map('sanitize_text_field', wp_unslash((array) $_POST['explorexr_selected_animations']));
                update_post_meta($model_id, '_explorexr_premium_selected_animations', $selected_animations);
            }
            
            // Save frontend controls enabled
            if (isset($_POST['explorexr_premium_animation_show_frontend_controls'])) {
                update_post_meta($model_id, '_explorexr_premium_animation_show_frontend_controls', 'on');
            } else {
                update_post_meta($model_id, '_explorexr_premium_animation_show_frontend_controls', 'off');
            }
            
            // Save frontend control position
            if (isset($_POST['explorexr_premium_animation_control_position'])) {
                update_post_meta($model_id, '_explorexr_premium_animation_control_position', sanitize_text_field(wp_unslash($_POST['explorexr_premium_animation_control_position'])));
            }
            
            // Save frontend control style
            if (isset($_POST['explorexr_premium_animation_control_style'])) {
                update_post_meta($model_id, '_explorexr_premium_animation_control_style', sanitize_text_field(wp_unslash($_POST['explorexr_premium_animation_control_style'])));
            }
            
            // Save frontend control size
            if (isset($_POST['explorexr_premium_animation_control_size'])) {
                update_post_meta($model_id, '_explorexr_premium_animation_control_size', sanitize_text_field(wp_unslash($_POST['explorexr_premium_animation_control_size'])));
            }
            
            // Morphing Addon Settings
            // Canonical key: _explorexr_morphing_enable ('1' or '0').
            update_post_meta($model_id, '_explorexr_morphing_enable', isset($_POST['explorexr_morphing_enable']) ? '1' : '0');

            if (isset($_POST['explorexr_morphing_next_model'])) {
                update_post_meta($model_id, '_explorexr_morphing_next_model', absint($_POST['explorexr_morphing_next_model']));
            }
            if (isset($_POST['explorexr_morphing_prev_model'])) {
                update_post_meta($model_id, '_explorexr_morphing_prev_model', absint($_POST['explorexr_morphing_prev_model']));
            }
            if (isset($_POST['explorexr_morphing_trigger_mode'])) {
                $allowed_triggers = array('click', 'scroll');
                $trigger_val = sanitize_text_field(wp_unslash($_POST['explorexr_morphing_trigger_mode']));
                update_post_meta($model_id, '_explorexr_morphing_trigger_mode', in_array($trigger_val, $allowed_triggers, true) ? $trigger_val : 'click');
            }
            if (isset($_POST['explorexr_morphing_button_mode'])) {
                $allowed_modes = array('text', 'poster', 'icon');
                $mode_val = sanitize_text_field(wp_unslash($_POST['explorexr_morphing_button_mode']));
                update_post_meta($model_id, '_explorexr_morphing_button_mode', in_array($mode_val, $allowed_modes, true) ? $mode_val : 'text');
            }
            if (isset($_POST['explorexr_morphing_button_icon'])) {
                update_post_meta($model_id, '_explorexr_morphing_button_icon', esc_url_raw(wp_unslash($_POST['explorexr_morphing_button_icon'])));
            }
            if (isset($_POST['explorexr_morphing_button_text'])) {
                update_post_meta($model_id, '_explorexr_morphing_button_text', sanitize_text_field(wp_unslash($_POST['explorexr_morphing_button_text'])));
            }
            if (isset($_POST['explorexr_morphing_reverse_button_text'])) {
                update_post_meta($model_id, '_explorexr_morphing_reverse_button_text', sanitize_text_field(wp_unslash($_POST['explorexr_morphing_reverse_button_text'])));
            }
            if (isset($_POST['explorexr_morphing_forward_position'])) {
                $allowed_positions = array('top-left','top-center','top-right','center-left','center','center-right','bottom-left','bottom-center','bottom-right');
                $fwd_pos = sanitize_text_field(wp_unslash($_POST['explorexr_morphing_forward_position']));
                update_post_meta($model_id, '_explorexr_morphing_forward_position', in_array($fwd_pos, $allowed_positions, true) ? $fwd_pos : 'bottom-left');
            }
            if (isset($_POST['explorexr_morphing_reverse_position'])) {
                $allowed_positions = array('top-left','top-center','top-right','center-left','center','center-right','bottom-left','bottom-center','bottom-right');
                $rev_pos = sanitize_text_field(wp_unslash($_POST['explorexr_morphing_reverse_position']));
                update_post_meta($model_id, '_explorexr_morphing_reverse_position', in_array($rev_pos, $allowed_positions, true) ? $rev_pos : 'bottom-right');
            }
            if (isset($_POST['explorexr_morphing_forward_bg_color'])) {
                update_post_meta($model_id, '_explorexr_morphing_forward_bg_color', sanitize_hex_color(wp_unslash($_POST['explorexr_morphing_forward_bg_color'])));
            }
            if (isset($_POST['explorexr_morphing_forward_text_color'])) {
                update_post_meta($model_id, '_explorexr_morphing_forward_text_color', sanitize_hex_color(wp_unslash($_POST['explorexr_morphing_forward_text_color'])));
            }
            if (isset($_POST['explorexr_morphing_reverse_bg_color'])) {
                update_post_meta($model_id, '_explorexr_morphing_reverse_bg_color', sanitize_hex_color(wp_unslash($_POST['explorexr_morphing_reverse_bg_color'])));
            }
            if (isset($_POST['explorexr_morphing_reverse_text_color'])) {
                update_post_meta($model_id, '_explorexr_morphing_reverse_text_color', sanitize_hex_color(wp_unslash($_POST['explorexr_morphing_reverse_text_color'])));
            }
            if (isset($_POST['explorexr_morphing_forward_active_bg_color'])) {
                update_post_meta($model_id, '_explorexr_morphing_forward_active_bg_color', sanitize_hex_color(wp_unslash($_POST['explorexr_morphing_forward_active_bg_color'])));
            }
            if (isset($_POST['explorexr_morphing_forward_active_text_color'])) {
                update_post_meta($model_id, '_explorexr_morphing_forward_active_text_color', sanitize_hex_color(wp_unslash($_POST['explorexr_morphing_forward_active_text_color'])));
            }
            if (isset($_POST['explorexr_morphing_reverse_active_bg_color'])) {
                update_post_meta($model_id, '_explorexr_morphing_reverse_active_bg_color', sanitize_hex_color(wp_unslash($_POST['explorexr_morphing_reverse_active_bg_color'])));
            }
            if (isset($_POST['explorexr_morphing_reverse_active_text_color'])) {
                update_post_meta($model_id, '_explorexr_morphing_reverse_active_text_color', sanitize_hex_color(wp_unslash($_POST['explorexr_morphing_reverse_active_text_color'])));
            }
            if (isset($_POST['explorexr_morphing_animation_style'])) {
                $allowed_anim = array('fade', 'zoom', 'slide-left', 'slide-right', 'blur');
                $anim_val = sanitize_text_field(wp_unslash($_POST['explorexr_morphing_animation_style']));
                update_post_meta($model_id, '_explorexr_morphing_animation_style', in_array($anim_val, $allowed_anim, true) ? $anim_val : 'fade');
            }
            update_post_meta($model_id, '_explorexr_morphing_buttons_always_visible', isset($_POST['explorexr_morphing_buttons_always_visible']) ? '1' : '0');
            update_post_meta($model_id, '_explorexr_morphing_active_styling', isset($_POST['explorexr_morphing_active_styling']) ? '1' : '0');
            if (isset($_POST['explorexr_morphing_scroll_offset'])) {
                $scroll_offset = absint(wp_unslash($_POST['explorexr_morphing_scroll_offset']));
                update_post_meta($model_id, '_explorexr_morphing_scroll_offset', min($scroll_offset, 1000));
            }
            
            // Post-Processing settings are saved via the addon's own
            // save_post_explorexr_model hook (post-processing-metabox.php)
            // which fires through the do_action("save_post_{$post_type}") below.
            
            // Let Premium add-ons persist their per-model meta (annotations, loading, etc.)
            do_action('explorexr_premium_save_model_meta', $model_id);

            // Add the edit mode marker to track that we're using the custom editor
            update_post_meta($model_id, '_explorexr_custom_edit_mode', 'true');
            
            // Annotations are not available in the free version
            // This feature is only available in the Premium version
            
            // Mark save as successful
            $success_message = 'Model updated successfully!';
            
            // Trigger save_post action hooks so addons can save their settings
            $model = get_post($model_id);
            if ($model) {
                // Generic save_post hook
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core hook
                do_action('save_post', $model_id, $model, false);
                
                // Post-type-specific hook (explorexr_model or explorexr_premium_model)
                $post_type = $model->post_type;
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core hook with dynamic post type
                do_action("save_post_{$post_type}", $model_id, $model, false);
            }
            
              // Refresh the model data after save
            $model_title = $model ? $model->post_title : '';
            $model_description = $model ? $model->post_content : '';
            $model_file = get_post_meta($model_id, '_explorexr_model_file', true) ?: '';
            $model_name = get_post_meta($model_id, '_explorexr_model_name', true) ?: '';
            $model_alt_text = get_post_meta($model_id, '_explorexr_model_alt_text', true) ?: '';
            $viewer_size = get_post_meta($model_id, '_explorexr_viewer_size', true) ?: 'custom';
            $viewer_width = get_post_meta($model_id, '_explorexr_viewer_width', true) ?: '100vw';
            $viewer_height = get_post_meta($model_id, '_explorexr_viewer_height', true) ?: '500px';
            $tablet_viewer_width = get_post_meta($model_id, '_explorexr_tablet_viewer_width', true) ?: '';
            $tablet_viewer_height = get_post_meta($model_id, '_explorexr_tablet_viewer_height', true) ?: '';
            $mobile_viewer_width = get_post_meta($model_id, '_explorexr_mobile_viewer_width', true) ?: '';
            $mobile_viewer_height = get_post_meta($model_id, '_explorexr_mobile_viewer_height', true) ?: '';

            // Refresh interaction controls with the same backward compatibility logic
            $enable_interactions_meta = get_post_meta($model_id, '_explorexr_enable_interactions', true) ?: '';
            if ($enable_interactions_meta === '') {
                $enable_interactions = true; // Default to enabled for new models
                update_post_meta($model_id, '_explorexr_enable_interactions', 'on');
            } else {
                $enable_interactions = ($enable_interactions_meta === 'on');
            }
            
            $auto_rotate_meta = get_post_meta($model_id, '_explorexr_auto_rotate', true) ?: '';
            if ($auto_rotate_meta === '') {
                $auto_rotate = false; // Default to disabled for new models
                update_post_meta($model_id, '_explorexr_auto_rotate', 'off');
            } else {
                $auto_rotate = ($auto_rotate_meta === 'on');
            }
            $auto_rotate_delay = get_post_meta($model_id, '_explorexr_auto_rotate_delay', true) ?: '5000';
            $auto_rotate_speed = get_post_meta($model_id, '_explorexr_rotation_per_second', true) ?: '30deg';
            // Animation settings are not available in the Free version
        } else {
            $error_message = 'Unable to update model: ' . $update_result->get_error_message();
        }
    }
    // Generate the shortcode
    $shortcode = '[explorexr_model id="' . $model_id . '"]';
      // CSS styling is now handled by the functions.php file
    
    // Render the page
    ?>
    <div class="wrap">
        <h1>Edit 3D Model</h1>
        
        <!-- WordPress.org Compliance: This div.wp-header-end is required for WordPress to place admin notices properly -->
        <div class="wp-header-end"></div>
        
        <!-- ExploreXR Plugin Content -->
        <div class="ExploreXR-admin-page ExploreXR-edit-model-page ExploreXR-admin-menu-fix">
        
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php 
        $page_title = 'Edit 3D Model';
        $header_actions = '<a href="' . esc_url(admin_url('admin.php?page=explorexr-browse-models')) . '" class="button">
            <span class="dashicons dashicons-format-gallery"></span> Browse Models
        </a>
        <a href="' . esc_url(admin_url('admin.php?page=explorexr-create-model')) . '" class="button button-primary">
            <span class="dashicons dashicons-plus"></span> Create New Model
        </a>';
        include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; 
        ?>
        
        <?php if (!empty($success_message)) : ?>
        <div class="ExploreXR-alert success">
            <span class="dashicons dashicons-yes"></span>
            <div>
                <p><?php echo esc_html($success_message); ?></p>
                <p>Shortcode: <code><?php echo esc_html($shortcode); ?></code> <button type="button" class="copy-shortcode" data-shortcode="<?php echo esc_attr($shortcode); ?>"><span class="dashicons dashicons-clipboard"></span> Copy</button></p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)) : ?>
        <div class="ExploreXR-alert error">
            <span class="dashicons dashicons-warning"></span>
            <div>
                <p><strong>Size Validation Error:</strong> <?php echo esc_html($error_message); ?></p>
                <p style="margin-top: 8px;">Your changes were saved, but invalid size values were reset to safe defaults (100% × 500px). Please review the Display Size settings.</p>
            </div>
        </div>        <?php endif; ?>
          <!-- Model Preview Section -->
        <?php 
        $template_vars = array(
            'model_id' => $model_id,
            'shortcode' => $shortcode,
            'model_file' => $model_file,
            'poster_url' => $poster_url,
            'auto_rotate' => $auto_rotate
            // Animation settings are not available in the Free version
        );
        ExploreXR_safe_include_template(EXPLOREXR_PLUGIN_DIR . 'admin/templates/edit-model/model-preview-card.php', '', $template_vars); 
        ?>
          <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('explorexr_edit_model', 'explorexr_edit_nonce'); ?>
            <input type="hidden" id="ExploreXR_model_id" name="model_id" value="<?php echo esc_attr($model_id); ?>">
            <?php 
            // Create an array of variables to pass to the template
            $template_vars = array(
                'model_id' => $model_id,
                'model_title' => $model_title,
                'model_description' => $model_description,
                'model_name' => $model_name,
                'model_alt_text' => $model_alt_text
            );
            ExploreXR_safe_include_template(EXPLOREXR_PLUGIN_DIR . 'admin/templates/edit-model/basic-information-card.php', '', $template_vars); 
            ?>
              <!-- Display Size Settings -->
            <?php 
            $template_vars = array(
                'model_id' => $model_id,
                'viewer_size' => $viewer_size,
                'viewer_width' => $viewer_width,
                'viewer_height' => $viewer_height,
                'tablet_viewer_width' => $tablet_viewer_width,
                'tablet_viewer_height' => $tablet_viewer_height,
                'mobile_viewer_width' => $mobile_viewer_width,
                'mobile_viewer_height' => $mobile_viewer_height
            );
            ExploreXR_safe_include_template(EXPLOREXR_PLUGIN_DIR . 'admin/templates/edit-model/display-size-card.php', '', $template_vars); 
            ?>
              <!-- 3D Model File -->
            <?php 
            $template_vars = array(
                'model_id' => $model_id,
                'model_file' => $model_file,
                'existing_models' => $existing_models,
                'poster_url' => $poster_url,
                'poster_id' => $poster_id
            );
            ExploreXR_safe_include_template(EXPLOREXR_PLUGIN_DIR . 'admin/templates/edit-model/model-file-card.php', '', $template_vars); 
            ?>
              <!-- Poster Image -->
            <?php 
            $template_vars = array(
                'model_id' => $model_id,
                'poster_url' => $poster_url,
                'poster_id' => $poster_id
            );
            ExploreXR_safe_include_template(EXPLOREXR_PLUGIN_DIR . 'admin/templates/edit-model/poster-image-card.php', '', $template_vars); 
            ?>
              <!-- Viewer Controls -->
            <?php 
            $template_vars = array(
                'model_id' => $model_id,
                'enable_interactions' => $enable_interactions,
                'auto_rotate' => $auto_rotate
            );
            ExploreXR_safe_include_template(EXPLOREXR_PLUGIN_DIR . 'admin/templates/edit-model/viewer-controls-card.php', '', $template_vars); 
            ?>
            
            <!-- Add-ons Settings -->
            <?php
            $template_vars = array(
                'model_id' => $model_id,
            );
            ExploreXR_safe_include_template(EXPLOREXR_PLUGIN_DIR . 'admin/templates/edit-model/addons-card.php', '', $template_vars);
            ?>
            
            <!-- Form Actions Card -->
            <div class="explorexr-card explorexr-form-actions-card">
                <div class="explorexr-card-content">
                    <div class="ExploreXR-form-actions">
                        <button type="submit" name="ExploreXR_edit_model_submit" class="button button-primary button-large">
                            <span class="dashicons dashicons-update"></span> Update 3D Model
                        </button>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-browse-models')); ?>" class="button button-large">Cancel</a>
                    </div>
                </div>
            </div>
            
        </form>
        
        </div><!-- .ExploreXR-admin-page -->

        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    </div><!-- .wrap -->

    <?php

    // Include the model viewer modal
    include EXPLOREXR_PLUGIN_DIR . 'admin/templates/model-viewer-modal.php';
}

// Function to get model files from uploads directory
function explorexr_get_model_files_from_directory() {
    // Use the WordPress uploads models directory
    $models_dir = EXPLOREXR_MODELS_DIR;
    $models_url = EXPLOREXR_MODELS_URL;
    
    if (!file_exists($models_dir)) {
        return array();
    }
    
    $files = array();
    
    $dir = new DirectoryIterator($models_dir);
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot() && !$fileinfo->isDir()) {
            $extension = strtolower(pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION));
            if (in_array($extension, array('glb', 'gltf', 'usdz'))) {
                $file_url = $models_url . $fileinfo->getFilename();
                $files[] = array(
                    'name' => $fileinfo->getFilename(),
                    'url' => $file_url
                );
            }
        }
    }
    
    return $files;
}

/**
 * Function alias for WordPress admin menu compatibility
 * 
 * This provides a lowercase alias for the main edit model page function
 * to ensure compatibility with the menu registration system.
 */
if (!function_exists('explorexr_edit_model_page')) {
    function explorexr_edit_model_page() {
        return ExploreXR_edit_model_page();
    }
}
