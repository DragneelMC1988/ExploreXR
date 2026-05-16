<?php
/**
 * Template for displaying large models with a load button.
 * 
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Available variables:
 * $model_instance_id - Unique ID for this model instance
 * $model_file - URL to the 3D model
 * $width - Display width
 * $height - Display height
 * $model_poster - URL to the poster image
 * $model_attributes_json - JSON encoded model attributes
 * $model_attributes - Array of model attributes
 */
?>

<div class="ExploreXR-model-container" style="width: <?php echo esc_attr($width); ?>; height: <?php echo esc_attr($height); ?>; position: relative;">
    <div class="ExploreXR-model-poster" id="<?php echo esc_attr($model_instance_id); ?>-poster" style="width: 100%; height: 100%; position: relative;">
        <?php if (!empty($model_poster_id)) : ?>
            <?php 
            // Use wp_get_attachment_image for better WordPress compliance
            echo wp_get_attachment_image($model_poster_id, 'large', false, array(
                'alt' => esc_attr__('3D Model Poster', 'explorexr'),
                'style' => 'width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;'
            ));
            ?>
        <?php elseif (!empty($model_poster)) : ?>
            <!-- Fallback for direct URL when attachment ID is not available -->
            <div class="ExploreXR-model-poster-wrapper">
                <?php
                // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
                printf(
                    '<img src="%s" alt="%s" style="%s">',
                    esc_url($model_poster),
                    esc_attr__('3D Model Poster', 'explorexr'),
                    esc_attr('width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;')
                );
                // phpcs:enable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
                ?>
            </div>
        <?php endif; ?>
        <button class="ExploreXR-load-model-btn" id="<?php echo esc_attr($model_instance_id); ?>-btn" style="position: relative; z-index: 10; background-color: <?php echo esc_attr(get_option('explorexr_load_button_bg_color', '#1e88e5')); ?>; color: <?php echo esc_attr(get_option('explorexr_load_button_text_color', '#ffffff')); ?>; border-radius: <?php echo absint(get_option('explorexr_load_button_border_radius', 4)); ?>px;">
            <?php echo esc_html(get_option('explorexr_load_button_text', __('Load 3D Model', 'explorexr'))); ?>
        </button>
        
        <?php
        // WordPress.org compliance: Convert inline script to wp_add_inline_script
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for inline script
        $large_model_script = '
        document.addEventListener("DOMContentLoaded", function() {
            // Get the button element
            var loadButton = document.getElementById("' . esc_js($model_instance_id) . '-btn");
            
            // Add both click and touchend events for better mobile compatibility
            loadButton.addEventListener("click", loadModel);
            loadButton.addEventListener("touchend", function(e) {
                e.preventDefault(); // Prevent default touch behavior
                loadModel(e);
            });
            
            function loadModel(e) {
                e.stopPropagation(); // Prevent event bubbling
                loadExploreXRModel("' . esc_js($model_instance_id) . '", "' . esc_js($model_file) . '", ' . wp_json_encode($model_attributes_json) . ');
            }
        });
        ';
        wp_add_inline_script('explorexr-model-loader', $large_model_script);
        ?>
    </div>
    <div id="<?php echo esc_attr($model_instance_id); ?>-viewer" style="width: 100%; height: 100%; display: none;">
        <!-- Model viewer will be inserted here via JavaScript -->
    </div>
</div>

