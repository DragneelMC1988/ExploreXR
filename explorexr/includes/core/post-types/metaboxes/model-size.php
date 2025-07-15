<?php
/**
 * Model Size Metabox
 *
 * @package ExpoXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the CSS and JS enqueue functions
require_once dirname(__FILE__) . '/model-size-enqueue.php';

/**
 * Render the model size metabox
 *
 * @param WP_Post $post Current post object
 */
function expoxr_edit_model_size_box($post) {
    $viewer_size = get_post_meta($post->ID, '_expoxr_viewer_size', true);
    $viewer_width = get_post_meta($post->ID, '_expoxr_viewer_width', true) ?: '100%';
    $viewer_height = get_post_meta($post->ID, '_expoxr_viewer_height', true) ?: '500px';
    $poster_url = get_post_meta($post->ID, '_expoxr_model_poster', true);
    $poster_id = get_post_meta($post->ID, '_expoxr_model_poster_id', true);
    
    // Get tablet and mobile specific sizes if they exist
    $tablet_viewer_width = get_post_meta($post->ID, '_expoxr_tablet_viewer_width', true) ?: '';
    $tablet_viewer_height = get_post_meta($post->ID, '_expoxr_tablet_viewer_height', true) ?: '';
    $mobile_viewer_width = get_post_meta($post->ID, '_expoxr_mobile_viewer_width', true) ?: '';
    $mobile_viewer_height = get_post_meta($post->ID, '_expoxr_mobile_viewer_height', true) ?: '';
    
    $size_is_custom = empty($viewer_size) || $viewer_size === 'custom';
    ?>
    <div>
        <div class="expoxr-card">
            <div class="expoxr-tabs">
                <button type="button" class="expoxr-tab <?php echo !$size_is_custom ? 'active' : ''; ?>" data-tab="predefined-sizes">Predefined Sizes</button>
                <button type="button" class="expoxr-tab <?php echo esc_attr($size_is_custom ? 'active' : ''); ?>" data-tab="custom-sizes">Custom Sizes</button>
            </div>
            
            <div class="expoxr-tab-content <?php echo !$size_is_custom ? 'active' : ''; ?>" id="predefined-sizes">
                <div class="expoxr-size-options">
                    <label class="expoxr-size-option">
                        <input type="radio" name="viewer_size" value="small" <?php checked($viewer_size, 'small'); ?>>
                        <div class="expoxr-size-preview">
                            <div class="expoxr-size-box" style="width: 60px; height: 60px;"></div>
                            <span>Small (300x300px)</span>
                        </div>
                    </label>
                    
                    <label class="expoxr-size-option">
                        <input type="radio" name="viewer_size" value="medium" <?php checked($viewer_size, 'medium'); ?>>
                        <div class="expoxr-size-preview">
                            <div class="expoxr-size-box" style="width: 80px; height: 80px;"></div>
                            <span>Medium (500x500px)</span>
                        </div>
                    </label>
                    
                    <label class="expoxr-size-option">
                        <input type="radio" name="viewer_size" value="large" <?php checked($viewer_size, 'large'); ?>>
                        <div class="expoxr-size-preview">
                            <div class="expoxr-size-box" style="width: 100px; height: 80px;"></div>
                            <span>Large (800x600px)</span>
                        </div>
                    </label>
                    
                    <label class="expoxr-size-option">
                        <input type="radio" name="viewer_size" value="full" <?php checked($viewer_size, 'full'); ?>>
                        <div class="expoxr-size-preview">
                            <div class="expoxr-size-box" style="width: 120px; height: 90px;"></div>
                            <span>Full Screen (100vw × 100vh)</span>
                        </div>
                    </label>
                </div>
            </div>
            
            <div class="expoxr-tab-content <?php echo esc_attr($size_is_custom ? 'active' : ''); ?>" id="custom-sizes">
                <div class="expoxr-device-tabs">
                    <button type="button" class="expoxr-device-tab active" data-device="desktop">
                        <span class="dashicons dashicons-desktop"></span> Desktop
                    </button>
                    <button type="button" class="expoxr-device-tab" data-device="tablet">
                        <span class="dashicons dashicons-tablet"></span> Tablet
                    </button>
                    <button type="button" class="expoxr-device-tab" data-device="mobile">
                        <span class="dashicons dashicons-smartphone"></span> Mobile
                    </button>
                </div>
                
                <div class="expoxr-device-content active" id="desktop-size">
                    <div class="expoxr-form-group">
                        <h3>Desktop Size</h3>
                        <div class="expoxr-form-row">
                            <label for="viewer_width">Width:</label>
                            <input type="text" name="viewer_width" id="viewer_width" value="<?php echo esc_attr($viewer_width); ?>" class="small-text">
                            <span class="description">(e.g., 500px, 100%, etc.)</span>
                        </div>
                        
                        <div class="expoxr-form-row">
                            <label for="viewer_height">Height:</label>
                            <input type="text" name="viewer_height" id="viewer_height" value="<?php echo esc_attr($viewer_height); ?>" class="small-text">
                            <span class="description">(e.g., 400px, 600px, etc.)</span>
                        </div>
                    </div>
                </div>
                
                <div class="expoxr-device-content" id="tablet-size">
                    <div class="expoxr-form-group">
                        <h3>Tablet Size <span class="optional">(optional)</span></h3>
                        <p class="description">If left empty, desktop size will be used for tablet devices.</p>
                        <div class="expoxr-form-row">
                            <label for="tablet_viewer_width">Width:</label>
                            <input type="text" name="tablet_viewer_width" id="tablet_viewer_width" value="<?php echo esc_attr($tablet_viewer_width); ?>" class="small-text">
                            <span class="description">(e.g., 500px, 100%, etc.)</span>
                        </div>
                        
                        <div class="expoxr-form-row">
                            <label for="tablet_viewer_height">Height:</label>
                            <input type="text" name="tablet_viewer_height" id="tablet_viewer_height" value="<?php echo esc_attr($tablet_viewer_height); ?>" class="small-text">
                            <span class="description">(e.g., 400px, 500px, etc.)</span>
                        </div>
                    </div>
                </div>
                
                <div class="expoxr-device-content" id="mobile-size">
                    <div class="expoxr-form-group">
                        <h3>Mobile Size <span class="optional">(optional)</span></h3>
                        <p class="description">If left empty, desktop size will be used for mobile devices.</p>
                        <div class="expoxr-form-row">
                            <label for="mobile_viewer_width">Width:</label>
                            <input type="text" name="mobile_viewer_width" id="mobile_viewer_width" value="<?php echo esc_attr($mobile_viewer_width); ?>" class="small-text">
                            <span class="description">(e.g., 100%, 300px, etc.)</span>
                        </div>
                        
                        <div class="expoxr-form-row">
                            <label for="mobile_viewer_height">Height:</label>
                            <input type="text" name="mobile_viewer_height" id="mobile_viewer_height" value="<?php echo esc_attr($mobile_viewer_height); ?>" class="small-text">
                            <span class="description">(e.g., 300px, 400px, etc.)</span>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="viewer_size" value="custom" id="custom_size_field" <?php if($size_is_custom) echo 'checked'; ?>>
            </div>
        </div>

        <div class="expoxr-poster-section">
            <h4>Model Loading Poster</h4>
            <p class="description">This image will be shown while the 3D model is loading. A poster image is especially important for large models when using the "Show Poster with Load Button" option.</p>
            
            <div class="expoxr-poster-options">
                <?php if ($poster_url) : ?>
                    <div class="expoxr-current-poster">
                        <h5>Current Poster:</h5>
                        <div class="expoxr-current-poster-container">
                            <?php
                            if (!empty($poster_id)) {
                                echo wp_get_attachment_image($poster_id, 'medium', false, array('alt' => esc_attr__('Model poster', 'explorexr')));
                            } else {
                                // Fallback for cases where we have URL but no attachment ID
                                // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Fallback for external URL posters
                                printf('<img src="%s" alt="%s" loading="lazy">', 
                                    esc_url($poster_url), 
                                    esc_attr__('Model poster', 'explorexr')
                                );
                            }
                            ?>
                            <div>
                                <label>
                                    <input type="checkbox" name="remove_poster" value="1">
                                    Remove current poster
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="expoxr-tabs">
                    <button type="button" class="expoxr-tab active" data-tab="upload-poster">Upload New Image</button>
                    <button type="button" class="expoxr-tab" data-tab="library-poster">Media Library</button>
                </div>
                
                <div class="expoxr-tab-content active" id="upload-poster">
                    <div class="expoxr-form-group">
                        <input type="hidden" name="poster_method" value="upload" id="poster_method_input">
                        <label for="model_poster">Select Image File</label>
                        <input name="model_poster" type="file" id="model_poster" accept="image/*" />
                        <p class="description">Accepted formats: JPG, PNG, GIF</p>
                    </div>
                </div>
                
                <div class="expoxr-tab-content" id="library-poster">
                    <div class="expoxr-form-group">
                        <input type="hidden" name="model_poster_id" id="model_poster_id" value="<?php echo esc_attr($poster_id); ?>">
                        <div class="expoxr-input-group">
                            <input type="text" name="model_poster_url" id="model_poster_url" value="<?php echo esc_attr($poster_url); ?>" readonly placeholder="No image selected">
                            <button type="button" class="button" id="expoxr-select-poster">
                                <span class="dashicons dashicons-admin-media"></span> Select Image
                            </button>
                        </div>
                        <div id="expoxr-poster-preview" class="<?php echo empty($poster_url) ? 'hidden' : ''; ?>">
                            <?php if (!empty($poster_url)) : ?>
                                <?php if (!empty($poster_id)) : ?>
                                    <?php echo wp_get_attachment_image($poster_id, 'medium', false, array('alt' => esc_attr__('Poster preview', 'explorexr'), 'loading' => 'lazy')); ?>
                                <?php else : ?>
                                    <!-- phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Fallback for external URL posters when attachment ID unavailable -->
                                    <img src="<?php echo esc_url($poster_url); ?>" alt="<?php esc_attr_e('Poster preview', 'explorexr'); ?>" loading="lazy">
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}





