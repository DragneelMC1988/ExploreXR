<?php
/**
 * AR Options Metabox
 *
 * @package ExploreXR AR Add-On
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the AR options metabox
 *
 * @param WP_Post $post Current post object
 */
function explorexr_premium_ar_model_metabox($post) {
    // Add nonce for security
    wp_nonce_field('explorexr_ar_metabox', 'explorexr_ar_metabox_nonce');
    
    // Get saved AR settings
    $ar_enabled = get_post_meta($post->ID, '_explorexr_premium_ar_enabled', true) === 'on';
    $ar_modes = get_post_meta($post->ID, '_explorexr_premium_ar_modes', true) ?: array('webxr', 'scene-viewer', 'quick-look');
    if (!is_array($ar_modes)) {
        $ar_modes = explode(',', $ar_modes);
    }
    $ar_scale = get_post_meta($post->ID, '_explorexr_premium_ar_scale', true) ?: 'auto';
    $ar_placement = get_post_meta($post->ID, '_explorexr_premium_ar_placement', true) ?: 'floor';
    $ar_usdz_model = get_post_meta($post->ID, '_explorexr_premium_ar_usdz_model', true) ?: '';
    $ar_button_text = get_post_meta($post->ID, '_explorexr_premium_ar_button_text', true) ?: '';
    $ar_button_image = get_post_meta($post->ID, '_explorexr_premium_ar_button_image', true) ?: '';
    $ar_xr_environment = get_post_meta($post->ID, '_explorexr_premium_ar_xr_environment', true) ?: '';
    $ar_min_height = get_post_meta($post->ID, '_explorexr_premium_ar_min_height', true) ?: '400px';
    
    $model_file = get_post_meta($post->ID, '_explorexr_premium_model_file', true);
    $has_model = !empty($model_file);
    ?>
    <div class="explorexr-premium-ar-settings-container">
        <?php if (!$has_model) : ?>
            <p class="notice notice-warning" style="padding: 10px;">
                Please add a 3D model first before configuring AR options.
            </p>
        <?php else : ?>
            <div class="explorexr-premium-setting-section">
                <h4>Augmented Reality Options</h4>
                <p class="description">
                    Configure how your 3D model appears in augmented reality experiences. 
                    AR allows users to place your 3D model in their real-world environment using their mobile device.
                </p>
                
                <div class="explorexr-premium-field-row">
                    <label>
                        <input type="checkbox" name="explorexr_premium_ar_enabled" <?php checked($ar_enabled); ?>>
                        Enable AR Mode
                    </label>
                    <p class="description">When enabled, visitors can view this model in AR on supported devices.</p>
                </div>
                
                <div id="ar-settings-container" <?php echo !$ar_enabled ? 'style="display: none;"' : ''; ?>>
                    <div class="explorexr-premium-field-row">
                        <label>AR Modes:</label>
                        <div class="explorexr-premium-checkbox-group">
                            <label>
                                <input type="checkbox" name="explorexr_premium_ar_modes[]" value="webxr" <?php checked(in_array('webxr', $ar_modes)); ?>>
                                WebXR (AR on web browsers)
                            </label>
                            <label>
                                <input type="checkbox" name="explorexr_premium_ar_modes[]" value="scene-viewer" <?php checked(in_array('scene-viewer', $ar_modes)); ?>>
                                Scene Viewer (Android)
                            </label>
                            <label>
                                <input type="checkbox" name="explorexr_premium_ar_modes[]" value="quick-look" <?php checked(in_array('quick-look', $ar_modes)); ?>>
                                Quick Look (iOS)
                            </label>
                        </div>
                        <p class="description">Choose which AR technologies to support. It's recommended to enable all for maximum compatibility.</p>
                    </div>
                    
                    <div class="explorexr-premium-field-row">
                        <label for="explorexr_premium_ar_scale">AR Scale:</label>
                        <select id="explorexr_premium_ar_scale" name="explorexr_premium_ar_scale">
                            <option value="auto" <?php selected($ar_scale, 'auto'); ?>>Auto (default)</option>
                            <option value="fixed" <?php selected($ar_scale, 'fixed'); ?>>Fixed (use model's true size)</option>
                        </select>
                        <p class="description">Determines how the model is scaled in AR. 'Auto' adjusts to reasonable size, 'Fixed' uses the model's actual dimensions.</p>
                    </div>
                    
                    <div class="explorexr-premium-field-row">
                        <label for="explorexr_premium_ar_placement">AR Placement:</label>
                        <select id="explorexr_premium_ar_placement" name="explorexr_premium_ar_placement">
                            <option value="floor" <?php selected($ar_placement, 'floor'); ?>>Floor (default)</option>
                            <option value="wall" <?php selected($ar_placement, 'wall'); ?>>Wall</option>
                            <option value="ceiling" <?php selected($ar_placement, 'ceiling'); ?>>Ceiling</option>
                        </select>
                        <p class="description">Where the model should be placed in the real world. Choose the option most appropriate for your model type.</p>
                    </div>
                    
                    <div class="explorexr-premium-field-row">
                        <label for="explorexr_premium_ar_min_height">Minimum Height (mobile):</label>
                        <input type="text" id="explorexr_premium_ar_min_height" name="explorexr_premium_ar_min_height" value="<?php echo esc_attr($ar_min_height); ?>" class="small-text">
                        <p class="description">Minimum height of the model viewer on mobile devices when in AR mode. (e.g., 400px)</p>
                    </div>
                    
                    <div class="explorexr-premium-field-row">
                        <label for="explorexr_premium_ar_button_text">AR Button Text:</label>
                        <input type="text" id="explorexr_premium_ar_button_text" name="explorexr_premium_ar_button_text" value="<?php echo esc_attr($ar_button_text); ?>" class="regular-text">
                        <p class="description">Text displayed on the AR button. NO default text - button will not render if empty.</p>
                    </div>
                    
                    <div class="explorexr-premium-field-row">
                        <label for="explorexr_premium_ar_button_image">Custom AR Button Image:</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="text" id="explorexr_premium_ar_button_image" name="explorexr_premium_ar_button_image" value="<?php echo esc_attr($ar_button_image); ?>" class="regular-text" readonly>
                            <button type="button" class="button" id="explorexr-premium-select-ar-button">Select Image</button>
                            <?php if (!empty($ar_button_image)) : ?>
                                <button type="button" class="button" id="explorexr-premium-remove-ar-button">Remove</button>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($ar_button_image)) : ?>
                            <div id="ar-button-preview" style="margin-top: 10px;">
                                <img src="<?php echo esc_url($ar_button_image); ?>" alt="AR Button" style="max-height: 50px; max-width: 200px;">
                            </div>
                        <?php else : ?>
                            <div id="ar-button-preview" style="margin-top: 10px; display: none;"></div>
                        <?php endif; ?>
                        <p class="description">Optional: Use a custom image for the AR button instead of text.</p>
                    </div>
                    
                    <div class="explorexr-premium-field-row">
                        <label for="explorexr_premium_ar_usdz_model">iOS USDZ Model (Optional):</label>
                        <div style="display: flex;">
                            <input type="text" id="explorexr_premium_ar_usdz_model" name="explorexr_premium_ar_usdz_model" value="<?php echo esc_attr($ar_usdz_model); ?>" style="width: 100%;" placeholder="URL to .usdz file for iOS Quick Look">
                            <button type="button" class="button" id="explorexr_premium_upload_usdz_btn" style="margin-left: 10px;">Upload USDZ</button>
                        </div>
                        <p class="description">For better iOS AR support, provide a USDZ version of your model. If not provided, the plugin will try to use the main model.</p>
                        <div id="explorexr_premium_usdz_upload" style="margin-top: 10px; display: none;">
                            <input type="file" id="explorexr_premium_usdz_file" name="explorexr_premium_usdz_file" accept=".usdz">
                        </div>
                    </div>
                    
                    <div class="explorexr-premium-field-row">
                        <label for="explorexr_premium_ar_xr_environment">XR Environment:</label>
                        <input type="text" id="explorexr_premium_ar_xr_environment" name="explorexr_premium_ar_xr_environment" value="<?php echo esc_attr($ar_xr_environment); ?>" class="regular-text" placeholder="URL to HDR environment map">
                        <p class="description">Optional: URL to an HDR environment map for lighting the model in AR. Leave empty for default lighting.</p>
                    </div>
                    
                    <div class="explorexr-premium-advanced-notice notice notice-info inline" style="margin-top: 20px; padding: 10px;">
                        <p><strong>Device Compatibility Note:</strong></p>
                        <ul style="margin-left: 15px; list-style-type: disc;">
                            <li><strong>iOS:</strong> iPhone/iPad with iOS 12+ using Safari</li>
                            <li><strong>Android:</strong> ARCore compatible devices with Android 8.0+ using Chrome</li>
                            <li><strong>WebXR:</strong> Compatible browsers on supported devices</li>
                        </ul>
                        <p>AR features are device and browser specific. We recommend testing on target devices.</p>
                    </div>
                </div>
            </div>      
        <?php endif; ?>
    </div>

    <?php wp_enqueue_script('explorexr-premium-ar-options', explorexr_premium_AR_PLUGIN_URL . 'assets/js/ar-options.js', array('jquery'), explorexr_premium_AR_VERSION, true); ?>
    <?php
}

/**
 * Save AR metabox data
 * 
 * @param int $post_id The post ID
 */
if (!function_exists('explorexr_premium_ar_save_metabox_data')) {
    function explorexr_premium_ar_save_metabox_data($post_id) {
        // Check nonce (only if it exists - Edit Model page doesn't have it)
        if (isset($_POST['explorexr_ar_metabox_nonce'])) {
            if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['explorexr_ar_metabox_nonce'])), 'explorexr_ar_metabox')) {
                return;
            }
        }
        
        // Skip autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check post type
        $post_type = get_post_type($post_id);
        if ($post_type !== 'explorexr_premium_model' && $post_type !== 'explorexr_model') {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save AR enabled state
        $ar_enabled = isset($_POST['explorexr_premium_ar_enabled']) ? 'on' : '';
        update_post_meta($post_id, '_explorexr_premium_ar_enabled', $ar_enabled);
    
        // Save AR modes
        $ar_modes = isset($_POST['explorexr_premium_ar_modes']) ? array_map('sanitize_text_field', array_map('wp_unslash', $_POST['explorexr_premium_ar_modes'])) : array();
        update_post_meta($post_id, '_explorexr_premium_ar_modes', $ar_modes);
        
        // Save AR scale
        if (isset($_POST['explorexr_premium_ar_scale'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_scale', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_scale'])));
        }
        
        // Save AR placement
        if (isset($_POST['explorexr_premium_ar_placement'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_placement', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_placement'])));
        }
        
        // Save AR button text
        if (isset($_POST['explorexr_premium_ar_button_text'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_button_text', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_button_text'])));
        }
        
        // Save AR button styling
        if (isset($_POST['explorexr_premium_ar_button_bg_color'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_button_bg_color', sanitize_hex_color(wp_unslash($_POST['explorexr_premium_ar_button_bg_color'])));
        }
        
        if (isset($_POST['explorexr_premium_ar_button_text_color'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_button_text_color', sanitize_hex_color(wp_unslash($_POST['explorexr_premium_ar_button_text_color'])));
        }
        
        if (isset($_POST['explorexr_premium_ar_button_border_color'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_button_border_color', sanitize_hex_color(wp_unslash($_POST['explorexr_premium_ar_button_border_color'])));
        }
        
        if (isset($_POST['explorexr_premium_ar_button_size'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_button_size', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_button_size'])));
        }
        
        if (isset($_POST['explorexr_premium_ar_button_border_radius'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_button_border_radius', absint($_POST['explorexr_premium_ar_button_border_radius']));
        }
        
        if (isset($_POST['explorexr_premium_ar_button_position'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_button_position', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_button_position'])));
        }
        
        // Save AR button image
        if (isset($_POST['explorexr_premium_ar_button_image'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_button_image', esc_url_raw(wp_unslash($_POST['explorexr_premium_ar_button_image'])));
        }
        
        // Save USDZ model
        if (isset($_POST['explorexr_premium_ar_usdz_model'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_usdz_model', esc_url_raw(wp_unslash($_POST['explorexr_premium_ar_usdz_model'])));
        }
        
        // Save XR environment
        if (isset($_POST['explorexr_premium_ar_xr_environment'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_xr_environment', esc_url_raw(wp_unslash($_POST['explorexr_premium_ar_xr_environment'])));
        }
          // Save minimum height
        if (isset($_POST['explorexr_premium_ar_min_height'])) {
            update_post_meta($post_id, '_explorexr_premium_ar_min_height', sanitize_text_field(wp_unslash($_POST['explorexr_premium_ar_min_height'])));
        }
    }
}
