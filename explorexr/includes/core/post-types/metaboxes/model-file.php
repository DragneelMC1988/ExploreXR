<?php
/**
 * Model File Metabox
 *
  * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the model file metabox
 *
 * @param WP_Post $post Current post object
 */
function explorexr_model_file_meta_box($post) {
    $model_file = get_post_meta($post->ID, '_explorexr_model_file', true) ?: '';
    $model_name = get_post_meta($post->ID, '_explorexr_model_name', true) ?: '';
    $alt_text = get_post_meta($post->ID, '_explorexr_model_alt_text', true) ?: '';
    
    // If no model name exists yet, try to extract it from the file path
    if (empty($model_name) && !empty($model_file)) {
        $model_name = basename($model_file);
        // Remove file extension
        $model_name = preg_replace('/\.[^.]+$/', '', $model_name);
    }
    
    // Add a nonce field for security
    wp_nonce_field('explorexr_save_model', 'explorexr_nonce');
    ?>
    <div class="explorexr-model-details">
        <div class="explorexr-field-row">
            <label for="explorexr_model_name">Model Name:</label>
            <input type="text" id="explorexr_model_name" name="explorexr_model_name" value="<?php echo esc_attr($model_name); ?>" style="width: 100%;" placeholder="Enter a name for this 3D model" />
            <p class="description">This name will be used for identification in admin listings.</p>
        </div>
        
        <div class="explorexr-field-row">
            <label for="explorexr_model_alt_text">Alt Text:</label>
            <input type="text" id="explorexr_model_alt_text" name="explorexr_model_alt_text" value="<?php echo esc_attr($alt_text); ?>" style="width: 100%;" placeholder="Enter alternative text for accessibility" />
            <p class="description">Provide descriptive alt text to improve accessibility for users with screen readers.</p>
        </div>
        
        <div class="explorexr-field-row" style="margin-top: 15px;">
            <label for="explorexr_model_file">Model File URL:</label>
            <div style="display: flex;">
                <input type="text" id="explorexr_model_file" name="explorexr_model_file" value="<?php echo esc_attr($model_file); ?>" style="width: 100%;" />
                <button type="button" class="button" id="explorexr_change_model_btn" style="margin-left: 10px;">Change Model</button>
            </div>
            <?php if (!empty($model_file)) : ?>
                <p>Current model: <strong><?php echo esc_html(basename($model_file)); ?></strong></p>
            <?php endif; ?>
        </div>
        
        <div id="explorexr_model_upload" style="margin-top: 15px; display: none;">
            <label for="explorexr_new_model">Upload New Model:</label>
            <input type="file" id="explorexr_model_file_upload" name="explorexr_new_model" accept=".glb,.gltf,.usdz" />
            <p class="description">Accepted formats: GLB, GLTF, USDZ</p>
        </div>
        
        <!-- Hidden fields for tracking changes -->
        <input type="hidden" id="explorexr_model_file_name" name="explorexr_model_file_name" value="" />
        <input type="hidden" id="explorexr_model_has_new_file" name="explorexr_model_has_new_file" value="0" />
        
        <!-- Preview section -->
        <div id="explorexr-model-preview" style="margin-top: 20px; min-height: 300px; display: <?php echo !empty($model_file) ? 'block' : 'none'; ?>;" 
             data-model-url="<?php echo esc_attr($model_file); ?>">
            <!-- Model viewer will be inserted here by JavaScript -->
        </div>
    </div>
    
    <?php
    // Enqueue the model file handler script
    wp_enqueue_script('explorexr-model-file-handler', EXPLOREXR_PLUGIN_URL . 'includes/post-types/assets/js/model-file-handler.js', array('jquery'), '1.0', true);
    
    // Add inline script for model file metabox functionality
    $inline_script = "
    jQuery(document).ready(function($) {
        // Show/hide the file upload field
        $('#explorexr_change_model_btn').on('click', function() {
            $('#explorexr_model_upload').toggle();
        });
        
        // Make sure the enhanced uploader JS is loaded
        if (typeof setupCheckboxTracking !== 'function') {
            console.log('Enhanced model uploader not detected, loading it manually');
        }
    });
    ";
    
    wp_add_inline_script('explorexr-model-file-handler', $inline_script);
    ?>
    
    <?php
}





