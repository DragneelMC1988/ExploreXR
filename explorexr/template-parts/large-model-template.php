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
        <button class="ExploreXR-load-model-btn" id="<?php echo esc_attr($model_instance_id); ?>-btn" style="position: relative; z-index: 10;">
            <?php echo esc_html__('Load 3D Model', 'explorexr'); ?>
        </button>
        
        <?php
        // WordPress.org compliance: Convert inline script to wp_add_inline_script
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
    
    <?php if (isset($model_attributes['ar']) && !empty($model_attributes['ar'])) : ?>
    <?php
    // WordPress.org compliance: Convert inline script to wp_add_inline_script
    $ar_customization_script = '
    // Add AR button customization after model is loaded
    document.addEventListener("ExploreXRModelLoaded", function(event) {
        if (event.detail.instanceId === "' . esc_js($model_instance_id) . '") {
            const modelViewer = document.querySelector("#' . esc_js($model_instance_id) . '-viewer model-viewer");
            if (modelViewer) {';
    
    if (isset($model_attributes['ar-button-image']) && !empty($model_attributes['ar-button-image'])) {
        $ar_customization_script .= '
                // Add custom AR button with image
                const arButton = document.createElement("button");
                arButton.setAttribute("slot", "ar-button");
                arButton.className = "ExploreXR-ar-button";
                
                const arButtonImg = document.createElement("img");
                arButtonImg.src = "' . esc_js($model_attributes['ar-button-image']) . '";
                arButtonImg.alt = "' . (isset($model_attributes['ar-button-text']) ? esc_js($model_attributes['ar-button-text']) : 'View in AR') . '";
                
                arButton.appendChild(arButtonImg);
                modelViewer.appendChild(arButton);';
    } elseif (isset($model_attributes['ar-button-text']) && !empty($model_attributes['ar-button-text'])) {
        $ar_customization_script .= '
                // Add custom AR button with text
                const arButton = document.createElement("button");
                arButton.setAttribute("slot", "ar-button");
                arButton.className = "ExploreXR-ar-button";
                arButton.textContent = "' . esc_js($model_attributes['ar-button-text']) . '";
                
                modelViewer.appendChild(arButton);';
    }
    
    $ar_customization_script .= '
            }
        }
    });
    ';
    wp_add_inline_script('explorexr-model-loader', $ar_customization_script);
    ?>
    <?php endif; ?>
</div>





