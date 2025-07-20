<?php
/**
 * Template for displaying standard 3D models.
 * 
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Available variables:
 * $attributes_html - HTML string of attributes for model-viewer
 * $model_attributes - Array of model attributes
 */
?>

<div class="ExploreXR-model-container">
    <model-viewer<?php echo wp_kses($attributes_html, 'post'); ?>>
        <!-- Annotations are not available in the Free version -->
          <?php if (isset($model_attributes['ar'])) : ?>
            <?php if (isset($model_attributes['ar-button-image']) && !empty($model_attributes['ar-button-image'])) : ?>
                <!-- Custom AR button with image -->
                <button slot="ar-button" class="ExploreXR-ar-button" data-ExploreXR-ar-button="true">
                    <?php 
                    // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- AR button image from user settings
                    printf('<img src="%s" alt="%s" loading="lazy">', 
                        esc_url($model_attributes['ar-button-image']), 
                        esc_attr(isset($model_attributes['ar-button-text']) ? $model_attributes['ar-button-text'] : esc_html__('View in AR', 'explorexr'))
                    );
                    ?>
                </button>
            <?php elseif (isset($model_attributes['ar-button-text']) && !empty($model_attributes['ar-button-text'])) : ?>
                <!-- Custom AR button with text -->
                <button slot="ar-button" class="ExploreXR-ar-button" data-ExploreXR-ar-button="true">
                    <?php echo esc_html($model_attributes['ar-button-text']); ?>
                </button>
            <?php endif; ?>
            
            <!-- AR not supported message (hidden by default, shown by JavaScript if needed) -->
            <div class="ExploreXR-ar-not-supported" style="display: none; position: absolute; bottom: 16px; right: 16px; background: rgba(0,0,0,0.8); color: white; padding: 8px 12px; border-radius: 4px; font-size: 14px; z-index: 10;">
                <?php esc_html_e('AR not supported on this device', 'explorexr'); ?>
            </div>
        <?php endif; ?>
    </model-viewer>
</div>





