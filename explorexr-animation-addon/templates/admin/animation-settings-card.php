<?php
/**
 * Animation Settings Card Template
 * Modern UI following ADDON_UI_DEVELOPMENT_GUIDE.md
 * 
 * @package ExploreXR_Animation_Addon
 * @version 1.0.5
 */

if (!defined('ABSPATH')) {
    exit;
}

// Template variables passed from parent scope - disable PHPCS global prefix check
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Get model ID (prefer template variable, fallback to request)
if (!isset($model_id) || !is_numeric($model_id)) {
    $model_id = isset($_GET['model_id']) ? intval($_GET['model_id']) : 0;
} else {
    $model_id = intval($model_id);
}
if (!$model_id) {
    echo '<div class="notice notice-error"><p>Error: Model ID not provided to animation-settings-card.php template.</p></div>';
    return;
}

// Get model file URL
$model_file = get_post_meta($model_id, '_explorexr_model_file', true);
if (empty($model_file)) {
    echo '<div class="notice notice-warning"><p>No 3D model file found for this model.</p></div>';
    return;
}

// Get animation settings
$animation_enabled = get_post_meta($model_id, '_explorexr_premium_animation_enabled', true) === 'on';
$animation_autoplay = get_post_meta($model_id, '_explorexr_premium_animation_autoplay', true) === 'on';
$animation_name = get_post_meta($model_id, '_explorexr_premium_animation_name', true) ?: '';
$animation_repeat = get_post_meta($model_id, '_explorexr_premium_animation_repeat', true) ?: 'once';
$multiple_animations_enabled = get_post_meta($model_id, '_explorexr_premium_multiple_animations_enabled', true) === 'on';
$selected_animations = get_post_meta($model_id, '_explorexr_premium_selected_animations', true) ?: array();

// Get scroll-based animation trigger setting
$scroll_trigger = get_post_meta($model_id, '_explorexr_premium_animation_scroll_trigger', true) === 'on';

// Get scroll animation speed setting (1-100%, default 50%)
$scroll_speed = get_post_meta($model_id, '_explorexr_premium_animation_scroll_speed', true);
if ($scroll_speed === '' || $scroll_speed === false) {
    $scroll_speed = '50';
}

// Get frontend control settings
$show_frontend_controls = get_post_meta($model_id, '_explorexr_premium_animation_show_frontend_controls', true) !== 'off';
$control_position = get_post_meta($model_id, '_explorexr_premium_animation_control_position', true) ?: 'bottom-left';
$control_style = get_post_meta($model_id, '_explorexr_premium_animation_control_style', true) ?: 'default';
$control_size = get_post_meta($model_id, '_explorexr_premium_animation_control_size', true) ?: 'medium';
?>

<!-- Content wrapper with ID for scoped CSS -->
<div id="explorexr-animation-settings">
        <div class="explorexr-premium-form-group explorexr-premium-full-width">
            <label class="explorexr-premium-checkbox-label">
                <input type="checkbox" name="explorexr_premium_animation_enabled" id="explorexr_premium_animation_enabled" class="animation-setting" <?php checked($animation_enabled, true); ?>>
                <span><?php esc_html_e('Enable Animation', 'explorexr-animation-addon'); ?></span>
            </label>
            <p class="description"><?php esc_html_e('Disable to skip injecting animation settings for this model.', 'explorexr-animation-addon'); ?></p>
        </div>

        <div id="explorexr-animation-card-body" <?php echo $animation_enabled ? '' : 'style="display:none;"'; ?>>
            <!-- Preview Container (100% width, outside grid) -->
            <div class="explorexr-premium-model-preview-container">
                <h3><?php esc_html_e('Interactive Animation Preview', 'explorexr-animation-addon'); ?></h3>
                <p class="description">
                    <?php esc_html_e('Preview your model animations in real-time. Select an animation from the dropdown to preview it.', 'explorexr-animation-addon'); ?>
                </p>
                
                <!-- Model Viewer Preview -->
                <div class="explorexr-animation-preview-wrapper">
                    <?php
                    if (function_exists('explorexr_premium_render_model_viewer')) {
                        explorexr_premium_render_model_viewer($model_id, array(
                            'id' => 'explorexr-animation-preview-model',
                            'class' => 'explorexr-animation-preview-model',
                            'interaction-prompt' => 'none',
                            'style' => 'width: 100%; height: 600px; background-color: #f5f5f5;',
                        ));
                    } else {
                        echo explorexr_render_model_viewer($model_id, 'admin', [
                            'id' => 'explorexr-animation-preview-model',
                            'class' => 'explorexr-animation-preview-model',
                            'interaction-prompt' => 'none',
                            'style' => 'width: 100%; height: 600px; background-color: #f5f5f5;',
                        ]);
                    }
                    ?>
                </div>
                
                <!-- Animation Controls -->
                <div class="explorexr-animation-controls">
                    <button type="button" id="animation-play" class="button">
                        <span class="dashicons dashicons-controls-play"></span> <?php esc_html_e('Play', 'explorexr-animation-addon'); ?>
                    </button>
                    <button type="button" id="animation-pause" class="button">
                        <span class="dashicons dashicons-controls-pause"></span> <?php esc_html_e('Pause', 'explorexr-animation-addon'); ?>
                    </button>
                    <button type="button" id="animation-reset" class="button">
                        <span class="dashicons dashicons-controls-back"></span> <?php esc_html_e('Reset', 'explorexr-animation-addon'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Settings Panel (full width below preview) -->
            <div class="explorexr-premium-settings-panel">
                
                <!-- Form Grid (contains feature cards) -->
                <div class="explorexr-premium-form-grid">
                
                <!-- Feature Card 1: Basic Animation Settings -->
                <div class="explorexr-premium-settings-card">
                    <h3>
                        <span class="dashicons dashicons-video-alt3"></span>
                        <?php esc_html_e('Basic Animation Settings', 'explorexr-animation-addon'); ?>
                    </h3>
                    
                    <!-- Animation Name -->
                    <div class="explorexr-premium-form-group">
                        <label for="explorexr_premium_animation_name">
                            <?php esc_html_e('Animation', 'explorexr-animation-addon'); ?>
                        </label>
                        <select name="explorexr_premium_animation_name" id="explorexr_premium_animation_name" class="regular-text animation-setting" data-saved-value="<?php echo esc_attr($animation_name); ?>">
                            <option value=""><?php esc_html_e('Loading animations...', 'explorexr-animation-addon'); ?></option>
                            <?php if (!empty($animation_name)) : ?>
                            <option value="<?php echo esc_attr($animation_name); ?>" selected><?php echo esc_html($animation_name); ?></option>
                            <?php endif; ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Select which animation to play from your model', 'explorexr-animation-addon'); ?>
                        </p>
                    </div>
                    
                    <!-- Autoplay -->
                    <div class="explorexr-premium-form-group">
                        <label class="explorexr-premium-checkbox-label">
                            <input type="checkbox" name="explorexr_premium_animation_autoplay" id="explorexr_premium_animation_autoplay" class="animation-setting" <?php checked($animation_autoplay, true); ?>>
                            <span><?php esc_html_e('Autoplay animation', 'explorexr-animation-addon'); ?></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Start animation automatically when the model loads', 'explorexr-animation-addon'); ?>
                        </p>
                    </div>
                    
                    <!-- Repeat Mode -->
                    <div class="explorexr-premium-form-group">
                        <label for="explorexr_premium_animation_repeat">
                            <?php esc_html_e('Animation Repeat Mode', 'explorexr-animation-addon'); ?>
                        </label>
                        <select name="explorexr_premium_animation_repeat" id="explorexr_premium_animation_repeat" class="regular-text animation-setting">
                            <option value="once" <?php selected($animation_repeat, 'once'); ?>><?php esc_html_e('Play Once', 'explorexr-animation-addon'); ?></option>
                            <option value="loop" <?php selected($animation_repeat, 'loop'); ?>><?php esc_html_e('Loop', 'explorexr-animation-addon'); ?></option>
                            <option value="pingpong" <?php selected($animation_repeat, 'pingpong'); ?>><?php esc_html_e('Ping Pong (Back and Forth)', 'explorexr-animation-addon'); ?></option>
                        </select>
                        <p class="description">
                            <?php esc_html_e('How the animation should repeat after completion', 'explorexr-animation-addon'); ?>
                        </p>
                    </div>
                    
                </div><!-- Close Feature Card 1 -->
                
                <!-- Feature Card 2: Playback Options -->
                <div class="explorexr-premium-settings-card">
                    <h3>
                        <span class="dashicons dashicons-controls-play"></span>
                        <?php esc_html_e('Animation Playback Options', 'explorexr-animation-addon'); ?>
                    </h3>
                    
                    <!-- Scroll-Based Animation Trigger -->
                    <div class="explorexr-premium-form-group">
                        <label class="explorexr-premium-checkbox-label">
                            <input type="checkbox" name="explorexr_premium_animation_scroll_trigger" id="explorexr_premium_animation_scroll_trigger" class="animation-setting" <?php checked($scroll_trigger); ?>>
                            <span><?php esc_html_e('Scroll-Based Animation', 'explorexr-animation-addon'); ?></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Drive animation playback with scroll. Scrolling down or up advances or reverses the animation, faster scroll means faster playback, and the animation pauses when scrolling stops.', 'explorexr-animation-addon'); ?>
                        </p>
                    </div>

                    <!-- Scroll-Based Animation Notice -->
                    <div id="scroll-trigger-notice" class="explorexr-premium-scroll-notice" <?php if (!$scroll_trigger) echo 'style="display: none;"'; ?>>
                        <span class="dashicons dashicons-info"></span>
                        <div class="explorexr-premium-scroll-notice-content">
                            <strong><?php esc_html_e('Scroll Animation Active', 'explorexr-animation-addon'); ?></strong>
                            <p><?php esc_html_e('For scroll-based animation to work correctly, Autoplay and Repeat Mode (Loop/Ping Pong) should be turned off. These settings conflict with scroll-driven playback since the animation timeline is controlled entirely by scroll position.', 'explorexr-animation-addon'); ?></p>
                            <button type="button" id="scroll-trigger-auto-fix" class="button button-small">
                                <?php esc_html_e('Auto-fix: Disable conflicting settings', 'explorexr-animation-addon'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Scroll Animation Speed -->
                    <div id="scroll-speed-settings" class="explorexr-premium-form-group" <?php if (!$scroll_trigger) echo 'style="display: none;"'; ?>>
                        <label for="explorexr_premium_animation_scroll_speed">
                            <?php esc_html_e('Scroll Animation Speed', 'explorexr-animation-addon'); ?>
                        </label>
                        <div class="explorexr-premium-range-slider">
                            <input type="range" name="explorexr_premium_animation_scroll_speed" id="explorexr_premium_animation_scroll_speed" class="animation-setting" min="1" max="100" step="1" value="<?php echo esc_attr($scroll_speed); ?>">
                            <span id="scroll-speed-value" class="explorexr-premium-range-value"><?php echo esc_html($scroll_speed); ?>%</span>
                        </div>
                        <p class="description">
                            <?php esc_html_e('Controls how fast the animation responds to scrolling. 1% = Very slow (about 1 frame per scroll), 100% = Fast (about 30 frames per scroll). Default: 50%.', 'explorexr-animation-addon'); ?>
                        </p>
                    </div>

                    <!-- Multiple Animations -->
                    <div class="explorexr-premium-form-group">
                        <label class="explorexr-premium-checkbox-label">
                            <input type="checkbox" name="explorexr_premium_multiple_animations_enabled" id="explorexr_premium_multiple_animations_enabled" class="animation-setting" <?php checked($multiple_animations_enabled); ?>>
                            <span><?php esc_html_e('Enable Multiple Animation Selection', 'explorexr-animation-addon'); ?></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Allow users to select from multiple animations in the frontend controls', 'explorexr-animation-addon'); ?>
                        </p>
                    </div>
                    
                    <!-- Available Animations (shown when multiple enabled) -->
                    <div id="multiple-animations-settings" class="explorexr-premium-form-group" <?php if (!$multiple_animations_enabled) echo 'style="display: none;"'; ?>>
                        <label><?php esc_html_e('Available Animations:', 'explorexr-animation-addon'); ?></label>
                        <div id="animation-list" class="animation-list">
                            <p class="description" id="animation-detection-status"><?php esc_html_e('Loading animations from model...', 'explorexr-animation-addon'); ?></p>
                            <div id="animation-checkboxes" data-selected="<?php echo esc_attr(wp_json_encode($selected_animations)); ?>" style="display: none;"></div>
                        </div>
                        <p class="description">
                            <?php esc_html_e('Select which animations should be available in the frontend controls', 'explorexr-animation-addon'); ?>
                        </p>
                    </div>
                    
                </div><!-- Close Feature Card 2 -->
                
                <!-- Feature Card 3: Frontend Controls -->
                <div class="explorexr-premium-settings-card">
                    <h3>
                        <span class="dashicons dashicons-admin-generic"></span>
                        <?php esc_html_e('Frontend Animation Controls', 'explorexr-animation-addon'); ?>
                    </h3>
                    
                    <!-- Show Frontend Controls -->
                    <div class="explorexr-premium-form-group">
                        <label class="explorexr-premium-checkbox-label">
                            <input type="checkbox" name="explorexr_premium_animation_show_frontend_controls" id="explorexr_premium_animation_show_frontend_controls" class="animation-setting" <?php checked($show_frontend_controls); ?>>
                            <span><?php esc_html_e('Show Animation Controls in Frontend', 'explorexr-animation-addon'); ?></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Display play/pause buttons for visitors to control animations', 'explorexr-animation-addon'); ?>
                        </p>
                    </div>
                    
                    <div id="frontend-control-settings" <?php if (!$show_frontend_controls) echo 'style="display: none;"'; ?>>
                        
                        <!-- Control Position -->
                        <div class="explorexr-premium-form-group">
                            <label for="explorexr_premium_animation_control_position">
                                <?php esc_html_e('Control Position', 'explorexr-animation-addon'); ?>
                            </label>
                            <select name="explorexr_premium_animation_control_position" id="explorexr_premium_animation_control_position" class="regular-text animation-setting">
                                <option value="bottom-left" <?php selected($control_position, 'bottom-left'); ?>><?php esc_html_e('Bottom Left', 'explorexr-animation-addon'); ?></option>
                                <option value="bottom-right" <?php selected($control_position, 'bottom-right'); ?>><?php esc_html_e('Bottom Right', 'explorexr-animation-addon'); ?></option>
                                <option value="top-left" <?php selected($control_position, 'top-left'); ?>><?php esc_html_e('Top Left', 'explorexr-animation-addon'); ?></option>
                                <option value="top-right" <?php selected($control_position, 'top-right'); ?>><?php esc_html_e('Top Right', 'explorexr-animation-addon'); ?></option>
                                <option value="bottom-center" <?php selected($control_position, 'bottom-center'); ?>><?php esc_html_e('Bottom Center', 'explorexr-animation-addon'); ?></option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Where to position the animation controls on the model viewer', 'explorexr-animation-addon'); ?>
                            </p>
                        </div>
                        
                        <!-- Control Style -->
                        <div class="explorexr-premium-form-group">
                            <label for="explorexr_premium_animation_control_style">
                                <?php esc_html_e('Control Style', 'explorexr-animation-addon'); ?>
                            </label>
                            <select name="explorexr_premium_animation_control_style" id="explorexr_premium_animation_control_style" class="regular-text animation-setting">
                                <option value="default" <?php selected($control_style, 'default'); ?>><?php esc_html_e('Default', 'explorexr-animation-addon'); ?></option>
                                <option value="minimal" <?php selected($control_style, 'minimal'); ?>><?php esc_html_e('Minimal', 'explorexr-animation-addon'); ?></option>
                                <option value="rounded" <?php selected($control_style, 'rounded'); ?>><?php esc_html_e('Rounded', 'explorexr-animation-addon'); ?></option>
                                <option value="flat" <?php selected($control_style, 'flat'); ?>><?php esc_html_e('Flat', 'explorexr-animation-addon'); ?></option>
                                <option value="glass" <?php selected($control_style, 'glass'); ?>><?php esc_html_e('Glass Effect', 'explorexr-animation-addon'); ?></option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Visual style of the animation controls', 'explorexr-animation-addon'); ?>
                            </p>
                        </div>
                        
                        <!-- Control Size -->
                        <div class="explorexr-premium-form-group">
                            <label for="explorexr_premium_animation_control_size">
                                <?php esc_html_e('Control Size', 'explorexr-animation-addon'); ?>
                            </label>
                            <select name="explorexr_premium_animation_control_size" id="explorexr_premium_animation_control_size" class="regular-text animation-setting">
                                <option value="small" <?php selected($control_size, 'small'); ?>><?php esc_html_e('Small', 'explorexr-animation-addon'); ?></option>
                                <option value="medium" <?php selected($control_size, 'medium'); ?>><?php esc_html_e('Medium', 'explorexr-animation-addon'); ?></option>
                                <option value="large" <?php selected($control_size, 'large'); ?>><?php esc_html_e('Large', 'explorexr-animation-addon'); ?></option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Size of the animation control buttons', 'explorexr-animation-addon'); ?>
                            </p>
                        </div>
                        
                    </div><!-- Close frontend-control-settings -->
                    
                </div><!-- Close Feature Card 3 -->
                
            </div><!-- Close form-grid -->
            
        </div><!-- Close settings-panel -->
        </div><!-- Close animation-card-body -->
        
</div><!-- Close main addon container -->

<script>
jQuery(function($){
    $('#explorexr_premium_animation_enabled').on('change', function(){
        $('#explorexr-animation-card-body').toggle($(this).is(':checked'));
    });
});
</script>

<?php
// Re-enable PHPCS global prefix check
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
