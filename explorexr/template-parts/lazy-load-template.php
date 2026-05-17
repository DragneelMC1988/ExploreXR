<?php
/**
 * Template for lazy loading large models when they become visible.
 * Uses Intersection Observer API to load models only when scrolled into view.
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
 * $model_attributes - Array of model attributes
 */
?>

<div class="ExploreXR-lazy-container"
     id="<?php echo esc_attr($model_css_id); ?>"
     data-width="<?php echo esc_attr($width); ?>"
     data-height="<?php echo esc_attr($height); ?>"
     data-lazy-load="true"
     data-instance-id="<?php echo esc_attr($model_instance_id); ?>-container">
    
    <?php if (!empty($model_poster)) : ?>
        <!-- Display poster image as placeholder while model is not loaded -->
        <div class="ExploreXR-lazy-placeholder" id="<?php echo esc_attr($model_instance_id); ?>-placeholder">
            <?php if (!empty($model_poster_id)) : ?>
                <?php 
                echo wp_get_attachment_image($model_poster_id, 'large', false, array(
                    'alt' => esc_attr__('3D Model Loading...', 'explorexr'),
                    'class' => 'ExploreXR-lazy-poster-image',
                    'loading' => 'lazy',
                    'decoding' => 'async'
                ));
                ?>
            <?php else : ?>
                <img src="<?php echo esc_url($model_poster); ?>" 
                     alt="<?php esc_attr_e('3D Model Loading...', 'explorexr'); ?>"
                     class="ExploreXR-lazy-poster-image"
                     loading="lazy"
                     decoding="async" />
            <?php endif; ?>
            
            <!-- Loading indicator -->
            <div class="ExploreXR-lazy-loading-indicator" style="display: none;">
                <div class="ExploreXR-spinner"></div>
                <p><?php esc_html_e('Loading 3D Model...', 'explorexr'); ?></p>
            </div>
        </div>
    <?php else : ?>
        <!-- No poster - show simple loading placeholder -->
        <div class="ExploreXR-lazy-placeholder ExploreXR-lazy-no-poster" id="<?php echo esc_attr($model_instance_id); ?>-placeholder">
            <div class="ExploreXR-lazy-loading-indicator">
                <div class="ExploreXR-spinner"></div>
                <p><?php esc_html_e('Preparing 3D Model...', 'explorexr'); ?></p>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Hidden model-viewer that will be revealed when loaded -->
    <div class="ExploreXR-lazy-viewer-wrapper" 
         id="<?php echo esc_attr($model_instance_id); ?>-wrapper"
         style="display: none;"
         data-model-url="<?php echo esc_url($model_file); ?>"
         data-model-attributes="<?php echo esc_attr(wp_json_encode($model_attributes)); ?>"
         data-model-id="<?php echo esc_attr($model_id); ?>"
         data-instance-id="<?php echo esc_attr($model_instance_id); ?>">
        <!-- Model viewer will be dynamically inserted here by JavaScript -->
    </div>
</div>

