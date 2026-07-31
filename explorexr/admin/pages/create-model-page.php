<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Ensure model helper functions are available
if (!function_exists('explorexr_handle_model_upload')) {
    if (file_exists(EXPLOREXR_PLUGIN_DIR . 'includes/models/model-helper.php')) {
        require_once EXPLOREXR_PLUGIN_DIR . 'includes/models/model-helper.php';
    }
}

/**
 * Initialize addon toggle defaults for a newly created model.
 *
 * New models should start with all addon toggles disabled so the Edit Model
 * Addons card always opens in an untoggled state.
 *
 * @param int $post_id Model post ID.
 * @return void
 */
function explorexr_initialize_new_model_addon_defaults($post_id) {
    $post_id = absint($post_id);
    if ($post_id <= 0) {
        return;
    }

    $defaults = array(
        '_explorexr_premium_annotations_enabled'   => '0',
        '_explorexr_premium_ar_enabled'            => 'off',
        '_explorexr_premium_animation_enabled'     => 'off',
        '_explorexr_premium_camera_enabled'        => '0',
        '_explorexr_draggable_enabled'             => '0',
        '_explorexr_environment_enabled'           => '0',
        '_explorexr_loading_enable'                => '0',
        '_explorexr_premium_materials_enabled'     => 'off',
        '_explorexr_morphing_enable'               => '0',
        '_explorexr_morphing_enabled'              => '0', // Legacy key for backward compatibility.
        '_explorexr_mouse3d_enabled'               => '0',
        '_explorexr_pp_enable'                     => '0',
        '_explorexr_premium_wc_integration_enabled' => 'off',
    );

    foreach ($defaults as $meta_key => $meta_value) {
        update_post_meta($post_id, $meta_key, $meta_value);
    }
}

/**
 * Handle model creation form submission before any output
 */
function explorexr_handle_model_creation() {
    // Only process if we're on the right page and have form submission
    if (!isset($_GET['page']) || sanitize_text_field(wp_unslash($_GET['page'])) !== 'explorexr-create-model' || !isset($_POST['create_model_submit'])) {
        return;
    }
    
    // Verify nonce - REQUIRED for security
    if (!isset($_POST['explorexr_create_model_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['explorexr_create_model_nonce'])), 'explorexr_create_model')) {
        wp_die('Security check failed. Please try again.');
    }
    
    $model_title = isset($_POST['model_title']) ? sanitize_text_field(wp_unslash($_POST['model_title'])) : '';
    $model_description = isset($_POST['model_description']) ? wp_kses_post(wp_unslash($_POST['model_description'])) : '';
    
    // Ensure the post type is registered
    if (!post_type_exists('explorexr_model')) {
        wp_die('Model post type not available. Please contact administrator.');
    }
    
    $post_id = wp_insert_post([
        'post_title' => $model_title,
        'post_content' => $model_description,
        'post_status' => 'publish',
        'post_type' => 'explorexr_model'
    ]);
    
    if ($post_id && !is_wp_error($post_id)) {
        // Ensure all addon cards start untoggled for a brand-new model.
        explorexr_initialize_new_model_addon_defaults($post_id);

        $model_source = isset($_POST['model_source']) ? sanitize_text_field(wp_unslash($_POST['model_source'])) : 'upload';
        
        // Load size validator for validation
        require_once EXPLOREXR_PLUGIN_DIR . 'includes/utils/size-validator.php';
        
        // Validate and save model viewer size settings
        $width_input = isset($_POST['viewer_width']) ? sanitize_text_field(wp_unslash($_POST['viewer_width'])) : '100vw';
        $height_input = isset($_POST['viewer_height']) ? sanitize_text_field(wp_unslash($_POST['viewer_height'])) : '500px';
        
        if (!empty($width_input) && !empty($height_input)) {
            $validation_result = ExploreXR_Size_Validator::validate_size_pair($width_input, $height_input);
            
            if ($validation_result['valid']) {
                update_post_meta($post_id, '_explorexr_viewer_width', $width_input);
                update_post_meta($post_id, '_explorexr_viewer_height', $height_input);
            } else {
                // Use safe defaults and store warning
                update_post_meta($post_id, '_explorexr_viewer_width', '100vw');
                update_post_meta($post_id, '_explorexr_viewer_height', '500px');
                set_transient('explorexr_size_validation_warning', $validation_result['error'], 30);
            }
        } else {
            if (!empty($width_input)) {
                update_post_meta($post_id, '_explorexr_viewer_width', ExploreXR_Size_Validator::sanitize_dimension($width_input, '100vw'));
            }
            if (!empty($height_input)) {
                update_post_meta($post_id, '_explorexr_viewer_height', ExploreXR_Size_Validator::sanitize_dimension($height_input, '500px'));
            }
        }
        
        // Validate and save tablet size settings
        $tablet_width = isset($_POST['tablet_viewer_width']) ? sanitize_text_field(wp_unslash($_POST['tablet_viewer_width'])) : '';
        $tablet_height = isset($_POST['tablet_viewer_height']) ? sanitize_text_field(wp_unslash($_POST['tablet_viewer_height'])) : '';
        
        if (!empty($tablet_width) && !empty($tablet_height)) {
            $tablet_validation = ExploreXR_Size_Validator::validate_size_pair($tablet_width, $tablet_height);
            if ($tablet_validation['valid']) {
                update_post_meta($post_id, '_explorexr_tablet_viewer_width', $tablet_width);
                update_post_meta($post_id, '_explorexr_tablet_viewer_height', $tablet_height);
            } else {
                // Don't save invalid tablet sizes (will use desktop fallback)
                if (get_transient('explorexr_size_validation_warning') === false) {
                    set_transient('explorexr_size_validation_warning', 'Tablet: ' . $tablet_validation['error'], 30);
                }
            }
        } else {
            // Allow partial updates with validation
            if (!empty($tablet_width)) {
                $clean = ExploreXR_Size_Validator::sanitize_dimension($tablet_width, '');
                if ($clean) {
                    update_post_meta($post_id, '_explorexr_tablet_viewer_width', $clean);
                }
            }
            if (!empty($tablet_height)) {
                $clean = ExploreXR_Size_Validator::sanitize_dimension($tablet_height, '');
                if ($clean) {
                    update_post_meta($post_id, '_explorexr_tablet_viewer_height', $clean);
                }
            }
        }
        
        // Validate and save mobile size settings
        $mobile_width = isset($_POST['mobile_viewer_width']) ? sanitize_text_field(wp_unslash($_POST['mobile_viewer_width'])) : '';
        $mobile_height = isset($_POST['mobile_viewer_height']) ? sanitize_text_field(wp_unslash($_POST['mobile_viewer_height'])) : '';
        
        if (!empty($mobile_width) && !empty($mobile_height)) {
            $mobile_validation = ExploreXR_Size_Validator::validate_size_pair($mobile_width, $mobile_height);
            if ($mobile_validation['valid']) {
                update_post_meta($post_id, '_explorexr_mobile_viewer_width', $mobile_width);
                update_post_meta($post_id, '_explorexr_mobile_viewer_height', $mobile_height);
            } else {
                // Don't save invalid mobile sizes (will use desktop fallback)
                if (get_transient('explorexr_size_validation_warning') === false) {
                    set_transient('explorexr_size_validation_warning', 'Mobile: ' . $mobile_validation['error'], 30);
                }
            }
        } else {
            // Allow partial updates with validation
            if (!empty($mobile_width)) {
                $clean = ExploreXR_Size_Validator::sanitize_dimension($mobile_width, '');
                if ($clean) {
                    update_post_meta($post_id, '_explorexr_mobile_viewer_width', $clean);
                }
            }
            if (!empty($mobile_height)) {
                $clean = ExploreXR_Size_Validator::sanitize_dimension($mobile_height, '');
                if ($clean) {
                    update_post_meta($post_id, '_explorexr_mobile_viewer_height', $clean);
                }
            }
        }
        
        // Save predefined size if selected
        if (isset($_POST['viewer_size']) && !empty($_POST['viewer_size'])) {
            update_post_meta($post_id, '_explorexr_viewer_size', sanitize_text_field(wp_unslash($_POST['viewer_size'])));
        }
        
        // Handle poster image upload if available
        if (isset($_POST['poster_method']) && $_POST['poster_method'] === 'upload' && isset($_FILES['model_poster']) && isset($_FILES['model_poster']['size']) && $_FILES['model_poster']['size'] > 0) {
            $poster_attachment_id = media_handle_upload('model_poster', $post_id);
            if (!is_wp_error($poster_attachment_id)) {
                $poster_url = wp_get_attachment_url($poster_attachment_id);
                update_post_meta($post_id, '_explorexr_model_poster', $poster_url);
            }
        } elseif (isset($_POST['poster_method']) && $_POST['poster_method'] === 'library' && !empty($_POST['model_poster_id'])) {
            $poster_url = wp_get_attachment_url(sanitize_text_field(wp_unslash($_POST['model_poster_id'])));
            update_post_meta($post_id, '_explorexr_model_poster', $poster_url);
        }
        
        // Handle model file assignment
        if ($model_source === 'existing' && !empty($_POST['existing_model'])) {
            $model_file_url = EXPLOREXR_MODELS_URL . sanitize_text_field(wp_unslash($_POST['existing_model']));
            update_post_meta($post_id, '_explorexr_model_file', $model_file_url);
            set_transient('explorexr_model_created', array('type' => 'success', 'message' => 'Model created successfully with existing file.'), 30);
            wp_safe_redirect(admin_url('admin.php?page=explorexr-browse-models&created=true'));
            exit;
        } else if ($model_source === 'upload' && isset($_FILES['model_file']) && isset($_FILES['model_file']['size']) && $_FILES['model_file']['size'] > 0) {
            if (function_exists('explorexr_handle_model_upload')) {
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
                    set_transient('explorexr_model_error', 'File validation failed: ' . $sanitized_file->get_error_message(), 30);
                    wp_safe_redirect(admin_url('admin.php?page=explorexr-create-model&error=validation'));
                    exit;
                }
                
                // Pass sanitized file to upload handler
                $upload_result = explorexr_handle_model_upload($sanitized_file);
                
                if ($upload_result && !is_wp_error($upload_result)) {
                    update_post_meta($post_id, '_explorexr_model_file', $upload_result['file_url']);
                    set_transient('explorexr_model_created', array('type' => 'success', 'message' => 'Model created successfully with uploaded file.'), 30);
                    wp_safe_redirect(admin_url('admin.php?page=explorexr-browse-models&created=true'));
                    exit;
                } else {
                    if (is_wp_error($upload_result)) {
                        set_transient('explorexr_model_error', 'Upload failed: ' . $upload_result->get_error_message(), 30);
                    } else {
                        set_transient('explorexr_model_error', 'Unable to upload model file. Please check file format and try again.', 30);
                    }
                    wp_safe_redirect(admin_url('admin.php?page=explorexr-create-model&error=upload'));
                    exit;
                }
            } else {
                set_transient('explorexr_model_error', 'File upload function not available. Please contact administrator.', 30);
                wp_safe_redirect(admin_url('admin.php?page=explorexr-create-model&error=function'));
                exit;
            }
        } else {
            set_transient('explorexr_model_created', array('type' => 'warning', 'message' => '3D model created, but no file was assigned.'), 30);
            wp_safe_redirect(admin_url('admin.php?page=explorexr-browse-models&created=true'));
            exit;
        }
    } else {
        set_transient('explorexr_model_error', 'Unable to create 3D model. Please check your inputs and try again.', 30);
        wp_safe_redirect(admin_url('admin.php?page=explorexr-create-model&error=creation'));
        exit;
    }
}

// Hook into admin_init to handle form submission before any output
add_action('admin_init', 'explorexr_handle_model_creation');

// Create Model page callback
function explorexr_create_model_page() {
    // Initialize variables for error handling
    $error_message = '';
    $success_message = '';
    
    // Enqueue validation and preview scripts
    wp_enqueue_script(
        'explorexr-size-validation',
        EXPLOREXR_PLUGIN_URL . 'admin/js/size-validation.js',
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
    
    // Check for error messages from redirects
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for display purposes only
    if (isset($_GET['error'])) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for display purposes only
        $error_param = sanitize_text_field(wp_unslash($_GET['error']));
        $error_transient = get_transient('explorexr_model_error');
        if ($error_transient) {
            $error_message = $error_transient;
            delete_transient('explorexr_model_error');
        }
    }
    
    // Get existing 3D model files
    $models_dir = EXPLOREXR_MODELS_DIR;
    $existing_models = [];    
    if (file_exists($models_dir)) {
        // Use GLOB_BRACE if available, otherwise fallback to multiple glob calls
        if (defined('GLOB_BRACE')) {
            $files = glob($models_dir . '*.{glb,gltf,usdz}', GLOB_BRACE);
        } else {
            $files = array_merge(
                glob($models_dir . '*.glb') ?: [],
                glob($models_dir . '*.gltf') ?: [],
                glob($models_dir . '*.usdz') ?: []
            );
        }
        foreach ($files as $file) {
            $existing_models[] = basename($file);
        }
    }
    
    ?>
    <div class="wrap">
        <h1>Create New 3D Model</h1>
        
        <!-- ExploreXR Plugin Content -->
        <div class="explorexr-admin-container">
        
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php 
        $page_title = 'Create New 3D Model';
        $header_actions = '<a href="' . esc_url(admin_url('admin.php?page=explorexr-browse-models')) . '" class="button">
            <span class="dashicons dashicons-format-gallery" style="margin-right: 5px;"></span> Browse Models
        </a>';
        include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; 
        
        // Display any error messages (success/warning messages are shown on redirect page)
        if (!empty($error_message)) {
            echo '<div class="notice notice-error"><p>' . esc_html($error_message) . '</p></div>';
        }
        ?>
        
        <div class="explorexr-alert info">
            <span class="dashicons dashicons-info"></span>
            <div>
                <p>Additional features will be available after creating the model.</p>
            </div>
        </div>
        
        <div class="explorexr-card">
            <div class="explorexr-card-header">
                <h2>Basic Information</h2>
                <span class="dashicons dashicons-welcome-write-blog"></span>
            </div>
            <div class="explorexr-card-content">
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('explorexr_create_model', 'explorexr_create_model_nonce'); ?>
                    <div class="explorexr-form-grid">
                        <div class="explorexr-form-group">
                            <label for="model_title">Model Title</label>
                            <input name="model_title" type="text" id="model_title" class="regular-text" required placeholder="Enter a descriptive title" />
                        </div>
                        
                        <div class="explorexr-form-group" style="grid-column: 1 / -1;">
                            <label for="model_description">Description</label>
                            <textarea name="model_description" id="model_description" rows="3" placeholder="Add a description for this 3D model (optional)"></textarea>
                        </div>
                    </div>
            </div>
        </div>
        
        <div class="explorexr-card" id="explorexr-display-size-card">
            <div class="explorexr-card-header">
                <h2><span class="dashicons dashicons-editor-distractionfree"></span> Display Size</h2>
            </div>
            <div class="explorexr-card-content">
                <!-- Single authoritative hidden field for viewer_size -->
                <input type="hidden" name="viewer_size" id="explorexr_viewer_size_field" value="medium">

                <div class="explorexr-tabs">
                    <button type="button" class="explorexr-tab active" data-tab="predefined-sizes">Predefined Sizes</button>
                    <button type="button" class="explorexr-tab" data-tab="custom-sizes">Custom Sizes</button>
                </div>

                <div class="explorexr-tab-content active" id="predefined-sizes">
                    <p class="description" style="margin-bottom: 15px;">
                        <span class="dashicons dashicons-info"></span>
                        Predefined sizes automatically adapt for tablet and smartphone devices.
                    </p>
                    <div class="explorexr-size-options">
                        <label class="explorexr-size-option">
                            <input type="radio" name="explorexr_predefined_size" value="small">
                            <div class="explorexr-size-preview">
                                <div class="explorexr-size-box explorexr-size-box-small"></div>
                                <span>Small</span>
                                <small style="display: block; color: #666; margin-top: 4px;">
                                    Desktop: 300×300px<br>
                                    Tablet: 280×280px<br>
                                    Mobile: 100vw×250px
                                </small>
                            </div>
                        </label>

                        <label class="explorexr-size-option">
                            <input type="radio" name="explorexr_predefined_size" value="medium" checked>
                            <div class="explorexr-size-preview">
                                <div class="explorexr-size-box explorexr-size-box-medium"></div>
                                <span>Medium</span>
                                <small style="display: block; color: #666; margin-top: 4px;">
                                    Desktop: 500×500px<br>
                                    Tablet: 400×400px<br>
                                    Mobile: 100vw×350px
                                </small>
                            </div>
                        </label>

                        <label class="explorexr-size-option">
                            <input type="radio" name="explorexr_predefined_size" value="large">
                            <div class="explorexr-size-preview">
                                <div class="explorexr-size-box explorexr-size-box-large"></div>
                                <span>Large</span>
                                <small style="display: block; color: #666; margin-top: 4px;">
                                    Desktop: 800×600px<br>
                                    Tablet: 600×450px<br>
                                    Mobile: 100vw×400px
                                </small>
                            </div>
                        </label>

                        <label class="explorexr-size-option">
                            <input type="radio" name="explorexr_predefined_size" value="full">
                            <div class="explorexr-size-preview">
                                <div class="explorexr-size-box explorexr-size-box-full"></div>
                                <span>Full</span>
                                <small style="display: block; color: #666; margin-top: 4px;">
                                    Desktop: 100vw×90vh<br>
                                    Tablet: 100vw×70vh<br>
                                    Mobile: 100vw×60vh
                                </small>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="explorexr-tab-content" id="custom-sizes">
                    <!-- Important validation notice -->
                    <div class="notice notice-info inline" style="margin: 0 0 15px 0; padding: 8px 12px;">
                        <p style="margin: 0.5em 0;"><strong><span class="dashicons dashicons-info" style="vertical-align: middle;"></span> Important:</strong> Allowed units: <code>px</code>, <code>vw</code>, <code>vh</code>, <code>dvw</code>, <code>dvh</code>, <code>em</code>, <code>rem</code>. Percentage (%) is <strong>not</strong> supported — use <code>vw</code>/<code>vh</code> for viewport-relative sizing instead.</p>
                    </div>

                    <div class="explorexr-device-tabs">
                        <button type="button" class="explorexr-device-tab active" data-device="desktop">
                            <span class="dashicons dashicons-desktop"></span> Desktop
                        </button>
                        <button type="button" class="explorexr-device-tab" data-device="tablet">
                            <span class="dashicons dashicons-tablet"></span> Tablet
                        </button>
                        <button type="button" class="explorexr-device-tab" data-device="mobile">
                            <span class="dashicons dashicons-smartphone"></span> Mobile
                        </button>
                    </div>

                    <div class="explorexr-device-content active" id="desktop-size">
                        <div class="explorexr-form-group">
                            <h3>Desktop Size</h3>
                            <div class="explorexr-form-row">
                                <label for="viewer_width">Width:</label>
                                <input type="text" name="viewer_width" id="viewer_width" value="100vw" class="small-text">
                                <span class="description">(e.g., 500px, 100vw, 50dvw)</span>
                            </div>

                            <div class="explorexr-form-row">
                                <label for="viewer_height">Height:</label>
                                <input type="text" name="viewer_height" id="viewer_height" value="500px" class="small-text">
                                <span class="description">(e.g., 500px, 50vh, 90dvh)</span>
                            </div>
                        </div>
                    </div>

                    <div class="explorexr-device-content" id="tablet-size">
                        <div class="explorexr-form-group">
                            <h3>Tablet Size <span class="optional">(optional — leave empty to inherit desktop)</span></h3>
                            <div class="explorexr-form-row">
                                <label for="tablet_viewer_width">Width:</label>
                                <input type="text" name="tablet_viewer_width" id="tablet_viewer_width" value="" class="small-text">
                                <span class="description">(e.g., 100vw, 500px, 50dvw)</span>
                            </div>

                            <div class="explorexr-form-row">
                                <label for="tablet_viewer_height">Height:</label>
                                <input type="text" name="tablet_viewer_height" id="tablet_viewer_height" value="" class="small-text">
                                <span class="description">(e.g., 400px, 50vh, 70dvh)</span>
                            </div>
                        </div>
                    </div>

                    <div class="explorexr-device-content" id="mobile-size">
                        <div class="explorexr-form-group">
                            <h3>Mobile Size <span class="optional">(optional — leave empty to inherit desktop)</span></h3>
                            <div class="explorexr-form-row">
                                <label for="mobile_viewer_width">Width:</label>
                                <input type="text" name="mobile_viewer_width" id="mobile_viewer_width" value="" class="small-text">
                                <span class="description">(e.g., 100vw, 300px, 100dvw)</span>
                            </div>

                            <div class="explorexr-form-row">
                                <label for="mobile_viewer_height">Height:</label>
                                <input type="text" name="mobile_viewer_height" id="mobile_viewer_height" value="" class="small-text">
                                <span class="description">(e.g., 300px, 50vh, 60dvh)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
          <div class="explorexr-card">
            <div class="explorexr-card-header">
                <h2>3D Model File</h2>
            </div>
            <div class="explorexr-card-content">
                <div class="explorexr-tabs">
                    <button type="button" class="explorexr-tab active" data-tab="upload-model">Upload New Model</button>
                    <?php if (!empty($existing_models)) : ?>
                    <button type="button" class="explorexr-tab" data-tab="existing-model">Use Existing Model</button>
                    <?php endif; ?>
                </div>
                
                <div class="explorexr-tab-content active" id="upload-model">
                    <div class="explorexr-form-group">
                        <input type="hidden" name="model_source" value="upload" id="model_source_input">
                        <label for="model_file">Select 3D Model File</label>
                        <div class="explorexr-file-input-wrapper">
                            <input name="model_file" type="file" id="model_file" accept=".glb,.gltf,.usdz" class="explorexr-styled-file-input" />
                            <div class="explorexr-file-input-decoration">
                                <span class="dashicons dashicons-upload"></span>
                                <span class="explorexr-file-input-text">Choose a file or drag it here</span>
                            </div>
                        </div>
                        <p class="description">Accepted formats: GLB, GLTF, USDZ. Maximum file size: <?php echo esc_html(get_option('explorexr_max_upload_size', 50)); ?>MB</p>
                    </div>
                </div>
                
                <?php if (!empty($existing_models)) : ?>
                <div class="explorexr-tab-content" id="existing-model">
                    <div class="explorexr-form-group">
                        <label for="existing_model">Select Existing Model</label>
                        <select name="existing_model" id="existing_model" class="regular-text">
                            <?php foreach ($existing_models as $model) : ?>
                                <option value="<?php echo esc_attr($model); ?>"><?php echo esc_html($model); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Choose from models you've already uploaded to your site.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="explorexr-card">
            <div class="explorexr-card-header">
                <h2>Poster Image</h2>
                <span class="dashicons dashicons-format-image"></span>
            </div>
            <div class="explorexr-card-content">
                <p class="description explorexr-card-note">A poster image is displayed while your 3D model loads. It's especially important for large models when using the "Show Poster with Load Button" option.</p>
                
                <div class="explorexr-tabs">
                    <button type="button" class="explorexr-tab active" data-tab="upload-poster">Upload New Image</button>
                    <button type="button" class="explorexr-tab" data-tab="library-poster">Media Library</button>
                </div>
                
                <div class="explorexr-tab-content active" id="upload-poster">
                    <div class="explorexr-form-group">
                        <input type="hidden" name="poster_method" value="upload" id="poster_method_input">
                        <label for="model_poster">Select Image File</label>
                        <input name="model_poster" type="file" id="model_poster" accept="image/*" />
                        <p class="description">Accepted formats: JPG, PNG, GIF</p>
                    </div>
                </div>
                
                <div class="explorexr-tab-content" id="library-poster">
                    <div class="explorexr-form-group">
                        <input type="hidden" name="model_poster_id" id="model_poster_id" value="">
                        <div class="explorexr-input-group">
                            <input type="text" name="model_poster_url" id="model_poster_url" value="" readonly placeholder="No image selected">
                            <button type="button" class="button" id="explorexr-select-poster">
                                <span class="dashicons dashicons-admin-media"></span> Select Image
                            </button>
                        </div>
                        <div id="explorexr-poster-preview" style="margin-top: 15px; display: none;">
                            <?php 
                            // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Dynamic preview image for upload interface, no attachment ID available
                            printf('<img src="" alt="%s" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;" loading="lazy">', 
                                esc_attr__('Poster preview', 'explorexr')
                            );
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="explorexr-form-actions">
            <button type="submit" name="create_model_submit" class="button button-primary button-large">Create 3D Model</button>
            <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-browse-models')); ?>" class="button button-large">Cancel</a>
        </div>
        </form>
    </div>
    
 
    <?php
    // Enqueue required scripts for create model page
    wp_enqueue_script('jquery');
    wp_enqueue_media(); // For WordPress media uploader
    
    // Create model page functionality
    $create_model_script = "
        jQuery(document).ready(function($) {

            // Canonical predefined dimensions — MUST match shortcodes.php exactly.
            var PREDEFINED_SIZES = {
                small:  { width: '300px', height: '300px' },
                medium: { width: '500px', height: '500px' },
                large:  { width: '800px', height: '600px' },
                full:   { width: '100vw', height: '90vh'  }
            };

            // -----------------------------------------------------------------
            // Main tab functionality
            // -----------------------------------------------------------------
            $('.explorexr-tab').on('click', function() {
                var tabId = $(this).data('tab');
                var tabGroup = $(this).closest('.explorexr-tabs').parent();

                tabGroup.find('.explorexr-tab').removeClass('active');
                $(this).addClass('active');

                tabGroup.find('.explorexr-tab-content').removeClass('active');
                tabGroup.find('#' + tabId).addClass('active');

                // Keep hidden source/method fields in sync
                if (tabId === 'upload-model') {
                    $('#model_source_input').val('upload');
                } else if (tabId === 'existing-model') {
                    $('#model_source_input').val('existing');
                } else if (tabId === 'upload-poster') {
                    $('#poster_method_input').val('upload');
                } else if (tabId === 'library-poster') {
                    $('#poster_method_input').val('library');
                }

                // Display-size tab synchronisation
                if (tabId === 'custom-sizes') {
                    // Custom tab active — the form should submit viewer_size='custom'
                    $('#explorexr_viewer_size_field').val('custom');
                } else if (tabId === 'predefined-sizes') {
                    // Restore the value of the currently checked radio button
                    var checked = \$('input[name=\"explorexr_predefined_size\"]:checked').val();
                    if (checked) {
                        $('#explorexr_viewer_size_field').val(checked);
                    }
                }
            });

            // -----------------------------------------------------------------
            // Device tab functionality
            // -----------------------------------------------------------------
            $('.explorexr-device-tab').on('click', function() {
                var deviceId = $(this).data('device');
                var deviceGroup = $(this).closest('.explorexr-device-tabs').parent();

                deviceGroup.find('.explorexr-device-tab').removeClass('active');
                $(this).addClass('active');

                deviceGroup.find('.explorexr-device-content').removeClass('active');
                deviceGroup.find('#' + deviceId + '-size').addClass('active');
            });

            // -----------------------------------------------------------------
            // Predefined size radio selection
            // -----------------------------------------------------------------
            \$('input[name=\"explorexr_predefined_size\"]').on('change', function() {
                var selectedSize = $(this).val();
                var dims = PREDEFINED_SIZES[selectedSize];

                if (!dims) {
                    return; // Unknown size — leave fields as-is
                }

                // 1. Update the single authoritative hidden field.
                $('#explorexr_viewer_size_field').val(selectedSize);

                // 2. Mirror canonical values into visible inputs.
                $('#viewer_width').val(dims.width).trigger('input');
                $('#viewer_height').val(dims.height).trigger('input');
            });

            // -----------------------------------------------------------------
            // WordPress Media Uploader for poster image
            // -----------------------------------------------------------------
            var mediaUploader;

            $('#explorexr-select-poster').on('click', function(e) {
                e.preventDefault();

                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                mediaUploader = wp.media({
                    title: 'Select Model Poster Image',
                    button: { text: 'Use this image' },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#model_poster_id').val(attachment.id);
                    $('#model_poster_url').val(attachment.url);
                    var previewElement = $('#explorexr-poster-preview');
                    previewElement.show().find('img').attr('src', attachment.url);
                });

                mediaUploader.open();
            });

            // -----------------------------------------------------------------
            // Initialise hidden field from the default checked radio on load
            // -----------------------------------------------------------------
            (function initSizeState() {
                var checked = \$('input[name=\"explorexr_predefined_size\"]:checked').val();
                if (checked && $('#explorexr_viewer_size_field').val() !== 'custom') {
                    $('#explorexr_viewer_size_field').val(checked);
                }
            })();
        });
    ";

    wp_add_inline_script('jquery', $create_model_script);
    ?>
    
    <!-- ExploreXR Footer -->
    <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    
        </div><!-- .explorexr-admin-container -->
    </div><!-- .wrap -->
    <?php
}


