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
function expoxr_safe_include_template($template_path, $fallback_path = '', $vars = array()) {
    // If no additional vars provided, share all variables from the calling scope
    if (empty($vars)) {
        // Import variables from the parent scope
        $vars = array();
        foreach ($GLOBALS as $key => $value) {
            if ($key != 'GLOBALS' && !is_object($value) && !is_array($value)) {
                $vars[$key] = $value;
            }
        }
        
        // Get variables from calling function's symbol table (only in debug mode)
        if (get_option('expoxr_debug_mode', false)) {
            // Gate debug_backtrace() behind WP_DEBUG check for WordPress coding standards
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace -- Used for debugging purposes only
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
                if (isset($backtrace[0]['args'])) {
                    // Skip this as it could be resource intensive
                }
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
        // Output HTML comment for debugging
        echo '<!-- Template file not found: ' . esc_html($template_path) . ' -->';
        // Log error if logging function is available
        if (function_exists('expoxr_log_error')) {
            expoxr_log_error('Template file not found: ' . $template_path);
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
 * @package ExpoXR
 */
function expoxr_edit_model_page() {
    // Get the model ID from the URL
    $model_id = isset($_GET['model_id']) ? intval($_GET['model_id']) : 0;
    
    // Check if the model exists and is valid
    if (!$model_id || get_post_type($model_id) !== 'expoxr_model') {
        // Set error notification and redirect to dashboard
        add_option('expoxr_admin_notice', array(
            'type' => 'error',
            'message' => 'The requested model could not be found. Please check the model ID and try again.'
        ));
        wp_redirect(admin_url('admin.php?page=expoxr-dashboard'));
        exit;
    }
    
    // Verify the model post exists
    $model = get_post($model_id);
    if (!$model) {
        // Set error notification and redirect to dashboard
        add_option('expoxr_admin_notice', array(
            'type' => 'error',
            'message' => 'The requested model no longer exists. It may have been deleted.'
        ));
        wp_redirect(admin_url('admin.php?page=expoxr-dashboard'));
        exit;
    }// Include the model-viewer script
    expoxr_safe_include_template(EXPOXR_PLUGIN_DIR . 'template-parts/model-viewer-script.php', '', array('model_id' => $model_id));
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
    $model_file = get_post_meta($model_id, '_expoxr_model_file', true) ?: '';
    $model_name = get_post_meta($model_id, '_expoxr_model_name', true) ?: '';
    $model_alt_text = get_post_meta($model_id, '_expoxr_model_alt_text', true) ?: '';
      // Get size settings
    $viewer_size = get_post_meta($model_id, '_expoxr_viewer_size', true) ?: 'custom';
    $viewer_width = get_post_meta($model_id, '_expoxr_viewer_width', true) ?: '100%';
    $viewer_height = get_post_meta($model_id, '_expoxr_viewer_height', true) ?: '500px';
    $tablet_viewer_width = get_post_meta($model_id, '_expoxr_tablet_viewer_width', true) ?: '';
    $tablet_viewer_height = get_post_meta($model_id, '_expoxr_tablet_viewer_height', true) ?: '';
    $mobile_viewer_width = get_post_meta($model_id, '_expoxr_mobile_viewer_width', true) ?: '';
    $mobile_viewer_height = get_post_meta($model_id, '_expoxr_mobile_viewer_height', true) ?: '';
    
    // Get poster information
    $poster_url = get_post_meta($model_id, '_expoxr_model_poster', true) ?: '';
    $poster_id = get_post_meta($model_id, '_expoxr_model_poster_id', true) ?: '';
    
    // Camera controls and animation settings
    $camera_controls = get_post_meta($model_id, '_expoxr_camera_controls', true) === 'on';
    
    // Interaction controls
    $enable_interactions = get_post_meta($model_id, '_expoxr_enable_interactions', true) === 'on';
    
    $auto_rotate = get_post_meta($model_id, '_expoxr_auto_rotate', true) === 'on';
    $animation_enabled = get_post_meta($model_id, '_expoxr_animation_enabled', true) === 'on';
    $animation_autoplay = get_post_meta($model_id, '_expoxr_animation_autoplay', true) === 'on';
    $animation_name = get_post_meta($model_id, '_expoxr_animation_name', true);
    
    // Get available existing models (for model dropdown)
    $uploaded_files = expoxr_get_model_files_from_directory();
    $existing_models = array();
    foreach ($uploaded_files as $file) {
        $existing_models[$file['url']] = $file['name'];
    }
    
    // Handle form submission
    if (isset($_POST['expoxr_edit_model_submit']) && check_admin_referer('expoxr_edit_model', 'expoxr_edit_nonce')) {
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
                    // Handle file upload
                    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- File upload array is handled by expoxr_handle_model_upload()
                    $upload_result = expoxr_handle_model_upload($_FILES['model_file']);
                    
                    if ($upload_result && !is_wp_error($upload_result)) {
                        update_post_meta($model_id, '_expoxr_model_file', $upload_result['file_url']);
                        
                        // If model name is empty, set it from the filename
                        if (empty($_POST['model_name'])) {
                            $filename = basename($upload_result['file_url']);
                            $model_name = preg_replace('/\.[^.]+$/', '', $filename);
                            update_post_meta($model_id, '_expoxr_model_name', $model_name);
                        }
                    } else {
                        $error_message = 'Unable to upload model file: ' . ($upload_result['error'] ?? 'Unknown error');
                    }
                }
            } else if (isset($_POST['model_source']) && $_POST['model_source'] === 'existing' && !empty($_POST['existing_model'])) {
                // Use existing model
                update_post_meta($model_id, '_expoxr_model_file', sanitize_text_field(wp_unslash($_POST['existing_model'])));
            }
            
            // Process model name and alt text
            if (isset($_POST['model_name'])) {
                update_post_meta($model_id, '_expoxr_model_name', sanitize_text_field(wp_unslash($_POST['model_name'])));
            }
            
            if (isset($_POST['model_alt_text'])) {
                update_post_meta($model_id, '_expoxr_model_alt_text', sanitize_text_field(wp_unslash($_POST['model_alt_text'])));
            }
            
            // Process size settings
            if (isset($_POST['viewer_size'])) {
                update_post_meta($model_id, '_expoxr_viewer_size', sanitize_text_field(wp_unslash($_POST['viewer_size'])));
            }
            
            if (isset($_POST['viewer_width'])) {
                update_post_meta($model_id, '_expoxr_viewer_width', sanitize_text_field(wp_unslash($_POST['viewer_width'])));
            }
            
            if (isset($_POST['viewer_height'])) {
                update_post_meta($model_id, '_expoxr_viewer_height', sanitize_text_field(wp_unslash($_POST['viewer_height'])));
            }
            
            if (isset($_POST['tablet_viewer_width'])) {
                update_post_meta($model_id, '_expoxr_tablet_viewer_width', sanitize_text_field(wp_unslash($_POST['tablet_viewer_width'])));
            }
            
            if (isset($_POST['tablet_viewer_height'])) {
                update_post_meta($model_id, '_expoxr_tablet_viewer_height', sanitize_text_field(wp_unslash($_POST['tablet_viewer_height'])));
            }
            
            if (isset($_POST['mobile_viewer_width'])) {
                update_post_meta($model_id, '_expoxr_mobile_viewer_width', sanitize_text_field(wp_unslash($_POST['mobile_viewer_width'])));
            }
            
            if (isset($_POST['mobile_viewer_height'])) {
                update_post_meta($model_id, '_expoxr_mobile_viewer_height', sanitize_text_field(wp_unslash($_POST['mobile_viewer_height'])));
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
                        update_post_meta($model_id, '_expoxr_model_poster', $poster_url);
                        update_post_meta($model_id, '_expoxr_model_poster_id', $poster_attachment_id);
                    }
                }
            } else if (isset($_POST['poster_method']) && $_POST['poster_method'] === 'library') {
                if (isset($_POST['model_poster_id']) && !empty($_POST['model_poster_id'])) {
                    $new_poster_id = intval($_POST['model_poster_id']);
                    $new_poster_url = wp_get_attachment_url($new_poster_id);
                    
                    update_post_meta($model_id, '_expoxr_model_poster', $new_poster_url);
                    update_post_meta($model_id, '_expoxr_model_poster_id', $new_poster_id);
                }
            }
            
            // Handle removing poster if checkbox is checked
            if (isset($_POST['remove_poster']) && $_POST['remove_poster'] == '1') {
                delete_post_meta($model_id, '_expoxr_model_poster');
                delete_post_meta($model_id, '_expoxr_model_poster_id');
                $poster_url = '';
                $poster_id = '';
            }
            
            // Handle camera controls
            $camera_controls_value = isset($_POST['expoxr_camera_controls']) ? 'on' : 'off';
            update_post_meta($model_id, '_expoxr_camera_controls', $camera_controls_value);
            
            // Handle enable interactions (same pattern as auto-rotate)
            $enable_interactions_value = isset($_POST['expoxr_enable_interactions']) ? 'on' : 'off';
            update_post_meta($model_id, '_expoxr_enable_interactions', $enable_interactions_value);
            
            // Also update the disable_interactions field for backward compatibility
            $disable_interactions = ($enable_interactions_value === 'off') ? 'on' : 'off';
            update_post_meta($model_id, '_expoxr_disable_interactions', $disable_interactions);
            
            // Handle auto-rotate
            $auto_rotate_value = isset($_POST['expoxr_auto_rotate']) ? 'on' : 'off';
            update_post_meta($model_id, '_expoxr_auto_rotate', $auto_rotate_value);
            
            // Handle auto-rotate delay and speed
            if (isset($_POST['expoxr_auto_rotate_delay'])) {
                $auto_rotate_delay = sanitize_text_field(wp_unslash($_POST['expoxr_auto_rotate_delay']));
                update_post_meta($model_id, '_expoxr_auto_rotate_delay', $auto_rotate_delay);
                if (get_option('expoxr_debug_mode')) {
                    expoxr_log('ExpoXR: Explicitly saved auto-rotate delay: ' . $auto_rotate_delay);
                }
            }
            
            if (isset($_POST['expoxr_auto_rotate_speed'])) {
                $auto_rotate_speed = sanitize_text_field(wp_unslash($_POST['expoxr_auto_rotate_speed']));
                update_post_meta($model_id, '_expoxr_rotation_per_second', $auto_rotate_speed);
                if (get_option('expoxr_debug_mode')) {
                    expoxr_log('ExpoXR: Explicitly saved rotation speed: ' . $auto_rotate_speed);
                }
            }
            
            // Handle animation settings
            $animation_enabled_value = isset($_POST['expoxr_animation_enabled']) ? 'on' : 'off';
            update_post_meta($model_id, '_expoxr_animation_enabled', $animation_enabled_value);
            
            $animation_autoplay_value = isset($_POST['expoxr_animation_autoplay']) ? 'on' : 'off';
            update_post_meta($model_id, '_expoxr_animation_autoplay', $animation_autoplay_value);
              if (isset($_POST['expoxr_animation_name'])) {
                update_post_meta($model_id, '_expoxr_animation_name', sanitize_text_field(wp_unslash($_POST['expoxr_animation_name'])));
            }
            
            // Handle animation repeat mode
            if (isset($_POST['expoxr_animation_repeat'])) {
                update_post_meta($model_id, '_expoxr_animation_repeat', sanitize_text_field(wp_unslash($_POST['expoxr_animation_repeat'])));
            }
            
            // Handle advanced animation settings (from Animation Addon)
            $animation_loop_value = isset($_POST['expoxr_animation_loop']) ? 'on' : 'off';
            update_post_meta($model_id, '_expoxr_animation_loop', $animation_loop_value);
            
            $multiple_animations_enabled_value = isset($_POST['expoxr_multiple_animations_enabled']) ? 'on' : 'off';
            update_post_meta($model_id, '_expoxr_multiple_animations_enabled', $multiple_animations_enabled_value);
            
            // Handle selected animations (array)
            if (isset($_POST['expoxr_selected_animations']) && is_array($_POST['expoxr_selected_animations'])) {
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array is sanitized using array_map below
                $selected_animations = array_map('sanitize_text_field', array_map('wp_unslash', wp_unslash($_POST['expoxr_selected_animations'])));
                update_post_meta($model_id, '_expoxr_selected_animations', $selected_animations);
            } else {
                update_post_meta($model_id, '_expoxr_selected_animations', array());
            }
            
            // Handle frontend control settings
            $show_frontend_controls_value = isset($_POST['expoxr_animation_show_frontend_controls']) ? 'on' : 'off';
            update_post_meta($model_id, '_expoxr_animation_show_frontend_controls', $show_frontend_controls_value);
            
            if (isset($_POST['expoxr_animation_control_position'])) {
                update_post_meta($model_id, '_expoxr_animation_control_position', sanitize_text_field(wp_unslash($_POST['expoxr_animation_control_position'])));
            }
            
            if (isset($_POST['expoxr_animation_control_style'])) {
                update_post_meta($model_id, '_expoxr_animation_control_style', sanitize_text_field(wp_unslash($_POST['expoxr_animation_control_style'])));
            }
            
            if (isset($_POST['expoxr_animation_control_size'])) {
                update_post_meta($model_id, '_expoxr_animation_control_size', sanitize_text_field(wp_unslash($_POST['expoxr_animation_control_size'])));
            }

            // Loading options are handled by the core plugin in free version
            // Premium features like individual loading options per model are not available
            
            // Addon features are not available in the free version
            // All addon functionality is handled in the Premium version
            
            // Add the edit mode marker to track that we're using the custom editor
            update_post_meta($model_id, '_expoxr_custom_edit_mode', 'true');
            
            // Annotations are not available in the free version
            // This feature is only available in the Premium version
            
            // Mark save as successful
            $success_message = 'Model updated successfully!';
              // Refresh the model data after save
            $model = get_post($model_id);
            $model_title = $model ? $model->post_title : '';
            $model_description = $model ? $model->post_content : '';
            $model_file = get_post_meta($model_id, '_expoxr_model_file', true);
            $model_name = get_post_meta($model_id, '_expoxr_model_name', true);
            $model_alt_text = get_post_meta($model_id, '_expoxr_model_alt_text', true);
            $viewer_size = get_post_meta($model_id, '_expoxr_viewer_size', true) ?: 'custom';
            $viewer_width = get_post_meta($model_id, '_expoxr_viewer_width', true) ?: '100%';
            $viewer_height = get_post_meta($model_id, '_expoxr_viewer_height', true) ?: '500px';
            $camera_controls = get_post_meta($model_id, '_expoxr_camera_controls', true) === 'on';
            
            // Refresh interaction controls (same pattern as auto-rotate)
            $enable_interactions = get_post_meta($model_id, '_expoxr_enable_interactions', true) === 'on';
            
            $auto_rotate = get_post_meta($model_id, '_expoxr_auto_rotate', true) === 'on';
            $auto_rotate_delay = get_post_meta($model_id, '_expoxr_auto_rotate_delay', true) ?: '5000';
            $auto_rotate_speed = get_post_meta($model_id, '_expoxr_rotation_per_second', true) ?: '30deg';
            $animation_enabled = get_post_meta($model_id, '_expoxr_animation_enabled', true) === 'on';
            $animation_autoplay = get_post_meta($model_id, '_expoxr_animation_autoplay', true) === 'on';
            $animation_name = get_post_meta($model_id, '_expoxr_animation_name', true);
        } else {
            $error_message = 'Unable to update model: ' . $update_result->get_error_message();
        }
    }
      // Generate the shortcode
    $shortcode = '[expoxr_model id="' . $model_id . '"]';
      // CSS styling is now handled by the functions.php file
    
    // Render the page
    ?>
    <div class="wrap expoxr-admin-page expoxr-edit-model-page expoxr-admin-menu-fix">
        <!-- WordPress admin notices appear here automatically before our custom content -->
        
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php 
        $page_title = 'Edit 3D Model';
        $header_actions = '<a href="' . esc_url(admin_url('admin.php?page=expoxr-browse-models')) . '" class="button">
            <span class="dashicons dashicons-format-gallery"></span> Browse Models
        </a>
        <a href="' . esc_url(admin_url('admin.php?page=expoxr-create-model')) . '" class="button button-primary">
            <span class="dashicons dashicons-plus"></span> Create New Model
        </a>';
        include EXPOXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; 
        ?>
        
        <?php if (!empty($success_message)) : ?>
        <div class="expoxr-alert success">
            <span class="dashicons dashicons-yes"></span>
            <div>
                <p><?php echo esc_html($success_message); ?></p>
                <p>Shortcode: <code><?php echo esc_html($shortcode); ?></code> <button type="button" class="copy-shortcode" data-shortcode="<?php echo esc_attr($shortcode); ?>"><span class="dashicons dashicons-clipboard"></span> Copy</button></p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)) : ?>
        <div class="expoxr-alert error">
            <span class="dashicons dashicons-warning"></span>
            <div>
                <p><?php echo esc_html($error_message); ?></p>
            </div>
        </div>        <?php endif; ?>
          <!-- Model Preview Section -->
        <?php 
        $template_vars = array(
            'model_id' => $model_id,
            'shortcode' => $shortcode,
            'model_file' => $model_file,
            'poster_url' => $poster_url,
            'auto_rotate' => $auto_rotate,
            'camera_controls' => $camera_controls,
            'animation_enabled' => $animation_enabled,
            'animation_name' => $animation_name,
            'animation_autoplay' => $animation_autoplay
        );
        expoxr_safe_include_template(EXPOXR_PLUGIN_DIR . 'admin/templates/edit-model/model-preview-card.php', '', $template_vars); 
        ?>
          <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('expoxr_edit_model', 'expoxr_edit_nonce'); ?>
            <input type="hidden" id="expoxr_model_id" name="model_id" value="<?php echo esc_attr($model_id); ?>">
            <?php 
            // Create an array of variables to pass to the template
            $template_vars = array(
                'model_id' => $model_id,
                'model_title' => $model_title,
                'model_description' => $model_description,
                'model_name' => $model_name,
                'model_alt_text' => $model_alt_text
            );
            expoxr_safe_include_template(EXPOXR_PLUGIN_DIR . 'admin/templates/edit-model/basic-information-card.php', '', $template_vars); 
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
            expoxr_safe_include_template(EXPOXR_PLUGIN_DIR . 'admin/templates/edit-model/display-size-card.php', '', $template_vars); 
            ?>
              <!-- 3D Model File -->
            <?php 
            $template_vars = array(
                'model_id' => $model_id,
                'model_file' => $model_file,
                'existing_models' => $existing_models,
                'poster_url' => $poster_url,
                'poster_id' => $poster_id
            );            expoxr_safe_include_template(EXPOXR_PLUGIN_DIR . 'admin/templates/edit-model/model-file-card.php', '', $template_vars); 
            ?>
              <!-- Poster Image -->
            <?php 
            $template_vars = array(
                'model_id' => $model_id,
                'poster_url' => $poster_url,
                'poster_id' => $poster_id
            );
            expoxr_safe_include_template(EXPOXR_PLUGIN_DIR . 'admin/templates/edit-model/poster-image-card.php', '', $template_vars); 
            ?>
              <!-- Viewer Controls -->
            <?php 
            $template_vars = array(
                'model_id' => $model_id,
                'camera_controls' => $camera_controls,
                'enable_interactions' => $enable_interactions,
                'auto_rotate' => $auto_rotate
            );
            expoxr_safe_include_template(EXPOXR_PLUGIN_DIR . 'admin/templates/edit-model/viewer-controls-card.php', '', $template_vars); 
            ?>
            
            <div class="expoxr-form-actions"><button type="submit" name="expoxr_edit_model_submit" class="button button-primary button-large">
                    <span class="dashicons dashicons-update"></span> Update 3D Model
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-browse-models')); ?>" class="button button-large">Cancel</a>            </div>        </form>
    </div>
    
    <?php
    
    // Include the model viewer modal
    include EXPOXR_PLUGIN_DIR . 'admin/templates/model-viewer-modal.php';
}

// Function to get model files from uploads directory
function expoxr_get_model_files_from_directory() {
    // Use the plugin's defined models directory
    $models_dir = EXPOXR_MODELS_DIR;
    $models_url = EXPOXR_MODELS_URL;
    
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





