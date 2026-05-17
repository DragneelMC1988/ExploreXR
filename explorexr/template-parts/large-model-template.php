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

<div class="ExploreXR-model-container"
     id="<?php echo esc_attr($model_css_id); ?>"
     data-width="<?php echo esc_attr($width); ?>"
     data-height="<?php echo esc_attr($height); ?>">
    <div class="ExploreXR-model-poster" id="<?php echo esc_attr($model_instance_id); ?>-poster">
        <?php if (!empty($model_poster_id)) : ?>
            <?php 
            // Use wp_get_attachment_image for better WordPress compliance
            echo wp_get_attachment_image($model_poster_id, 'large', false, array(
                'alt'      => esc_attr__('3D Model Poster', 'explorexr'),
                'loading'  => 'lazy',
                'decoding' => 'async'
            ));
            ?>
        <?php elseif (!empty($model_poster)) : ?>
            <!-- Fallback for direct URL when attachment ID is not available -->
            <div class="ExploreXR-model-poster-wrapper">
                <?php
                // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
                printf(
                    '<img src="%s" alt="%s" loading="lazy" decoding="async">',
                    esc_url($model_poster),
                    esc_attr__('3D Model Poster', 'explorexr')
                );
                // phpcs:enable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
                ?>
            </div>
        <?php endif; ?>
        <button class="ExploreXR-load-model-btn"
                id="<?php echo esc_attr($model_instance_id); ?>-btn"
                data-instance-id="<?php echo esc_attr($model_instance_id); ?>"
                data-model-url="<?php echo esc_url($model_file); ?>"
                data-model-attrs="<?php echo esc_attr($model_attributes_json); ?>">
            <?php
            $explorexr_button_text = get_option('explorexr_load_button_text', '');
            if ($explorexr_button_text === '') {
                $explorexr_button_text = __('Load 3D Model', 'explorexr');
            }
            echo esc_html($explorexr_button_text);
            ?>
        </button>
        
        <?php
        // Load button JS is handled by assets/js/large-model-handler.js
        // (enqueued in template-parts/model-viewer-script.php).
        ?>
    </div>
    <div id="<?php echo esc_attr($model_instance_id); ?>-viewer" class="ExploreXR-model-viewer-wrapper explorexr-hidden">
        <!-- Model viewer will be inserted here via JavaScript -->
    </div>
</div>



