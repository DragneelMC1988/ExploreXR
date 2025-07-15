<?php
/**
 * Animation Metabox
 *
 * @package ExpoXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the animation metabox
 *
 * @param WP_Post $post Current post object
 */
function expoxr_model_animation_box($post) {
    // Get saved animation settings
    $animation_enabled = get_post_meta($post->ID, '_expoxr_animation_enabled', true) === 'on';
    $animation_name = get_post_meta($post->ID, '_expoxr_animation_name', true);
    $animation_crossfade_duration = get_post_meta($post->ID, '_expoxr_animation_crossfade_duration', true) ?: '300';
    $animation_autoplay = get_post_meta($post->ID, '_expoxr_animation_autoplay', true) === 'on';
    $animation_repeat = get_post_meta($post->ID, '_expoxr_animation_repeat', true) ?: 'once';
    
    // Get available animations from the model (this would be retrieved dynamically in a real implementation)
    $model_file = get_post_meta($post->ID, '_expoxr_model_file', true);
    $has_model = !empty($model_file);
    ?>
    <div class="expoxr-animation-settings">
        <?php if (!$has_model) : ?>
            <p class="notice notice-warning" style="padding: 10px;">
                Please add a 3D model first before configuring animations.
            </p>
        <?php else : ?>
            <div class="expoxr-setting-section">
                <div class="expoxr-field-row">
                    <label>
                        <input type="checkbox" name="expoxr_animation_enabled" <?php checked($animation_enabled); ?>>
                        Enable Animation
                    </label>
                    <p class="description">Enable this option if your model contains animations you want to control.</p>
                </div>
                
                <div id="animation-settings" <?php if (!$animation_enabled) echo 'style="display: none;"'; ?>>
                    <div class="expoxr-field-row">
                        <label for="expoxr_animation_name">Animation Name:</label>
                        <input type="text" id="expoxr_animation_name" name="expoxr_animation_name" value="<?php echo esc_attr($animation_name); ?>" class="regular-text">
                        <p class="description">Enter the name of the animation in your model. Leave empty to play the default animation.</p>
                    </div>
                    
                    <div class="expoxr-field-row">
                        <label>
                            <input type="checkbox" name="expoxr_animation_autoplay" <?php checked($animation_autoplay); ?>>
                            Autoplay Animation
                        </label>
                        <p class="description">Animation will start playing as soon as the model loads.</p>
                    </div>
                    
                    <div class="expoxr-field-row">
                        <label for="expoxr_animation_crossfade_duration">Animation Crossfade Duration (ms):</label>
                        <input type="number" id="expoxr_animation_crossfade_duration" name="expoxr_animation_crossfade_duration" value="<?php echo esc_attr($animation_crossfade_duration); ?>" class="small-text" min="0" step="50">
                        <p class="description">The duration of the crossfade when transitioning between animations, in milliseconds.</p>
                    </div>
                    
                    <div class="expoxr-field-row">
                        <label for="expoxr_animation_repeat">Repeat Mode:</label>
                        <select id="expoxr_animation_repeat" name="expoxr_animation_repeat">
                            <option value="once" <?php selected($animation_repeat, 'once'); ?>>Once (default)</option>
                            <option value="loop" <?php selected($animation_repeat, 'loop'); ?>>Loop</option>
                            <option value="pingpong" <?php selected($animation_repeat, 'pingpong'); ?>>Ping Pong (back and forth)</option>
                        </select>
                        <p class="description">Defines how the animation should repeat.</p>
                    </div>
                </div>
            </div>
            
            <div class="expoxr-setting-section" id="animation-preview" <?php if (!$animation_enabled) echo 'style="display: none;"'; ?>>
                <h4>Animation Preview</h4>
                <div class="expoxr-model-preview-container" style="position: relative; width: 100%; height: 400px; margin-top: 10px; background-color: #f0f0f0; border: 1px solid #ddd;">
                    <model-viewer id="expoxr-animation-preview-model" 
                                 src="<?php echo esc_url($model_file); ?>" 
                                 camera-controls
                                 autoplay
                                 animation-name="<?php echo esc_attr($animation_name); ?>"
                                 style="width: 100%; height: 100%;">
                    </model-viewer>
                </div>
                
                <div class="expoxr-animation-controls" style="margin-top: 10px; display: flex; gap: 10px;">
                    <button type="button" id="animation-play" class="button button-secondary">Play</button>
                    <button type="button" id="animation-pause" class="button button-secondary">Pause</button>
                    <button type="button" id="animation-reset" class="button button-secondary">Reset</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php 
    // Make sure we use the guard script and centralized model-viewer
    wp_enqueue_script('expoxr-model-viewer-guard');
    wp_enqueue_script('expoxr-model-viewer');
    
    // Now load our animation handler with the proper dependencies
    wp_enqueue_script('expoxr-animation-handler', EXPOXR_PLUGIN_URL . 'includes/core/post-types/assets/js/animation-handler.js', array('jquery', 'expoxr-model-viewer'), EXPOXR_VERSION, true);
}





