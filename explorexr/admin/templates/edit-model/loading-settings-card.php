<?php
/**
 * Loading Settings Card Template
 * 
 * Loading bar, overlay, and performance settings
 *
  * @package ExploreXR
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if Loading customization is available (Premium feature)
if (function_exists('explorexr_is_premium_available') && explorexr_is_premium_available()):

// Get loading settings
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables for loading settings
$loading_bar_color = get_post_meta($model_id, '_explorexr_loading_bar_color', true) ?: '#4285f4';
$loading_bar_height = get_post_meta($model_id, '_explorexr_loading_bar_height', true) ?: '4px';
$loading_bar_position = get_post_meta($model_id, '_explorexr_loading_bar_position', true) ?: 'bottom';
$percentage_show = get_post_meta($model_id, '_explorexr_percentage_show', true) === 'on';
$percentage_precision = get_post_meta($model_id, '_explorexr_percentage_precision', true) ?: '0';
$percentage_suffix = get_post_meta($model_id, '_explorexr_percentage_suffix', true) ?: '%';
$loading_text_show = get_post_meta($model_id, '_explorexr_loading_text_show', true) === 'on';
$loading_text_content = get_post_meta($model_id, '_explorexr_loading_text_content', true) ?: 'Loading 3D Model...';
$loading_text_position = get_post_meta($model_id, '_explorexr_loading_text_position', true) ?: 'center';
$loading_text_color = get_post_meta($model_id, '_explorexr_loading_text_color', true) ?: '#333333';
$overlay_show = get_post_meta($model_id, '_explorexr_overlay_show', true) === 'on';
$overlay_color = get_post_meta($model_id, '_explorexr_overlay_color', true) ?: 'rgba(255,255,255,0.9)';
$overlay_blur = get_post_meta($model_id, '_explorexr_overlay_blur', true) === 'on';
$lazy_load_model = get_post_meta($model_id, '_explorexr_lazy_load_model', true) === 'on';
$script_location = get_post_meta($model_id, '_explorexr_script_location', true) ?: 'header';
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>

<div class="explorexr-card">
    <div class="explorexr-card-header">
        <h2><span class="dashicons dashicons-update"></span> Loading Settings</h2>
    </div>
    <div class="explorexr-card-content">
        <div class="explorexr-form-grid explorexr-form-grid-3">
            <!-- Loading Bar Customization -->
            <div class="explorexr-form-group">
                <label for="explorexr_loading_bar_color">Loading Bar Color</label>
                <input type="color" name="explorexr_loading_bar_color" id="explorexr_loading_bar_color" value="<?php echo esc_attr($loading_bar_color); ?>" class="regular-text">
                <p class="description">Color of the loading progress bar</p>
            </div>
            
            <div class="explorexr-form-group">
                <label for="explorexr_loading_bar_height">Loading Bar Height</label>
                <input type="text" name="explorexr_loading_bar_height" id="explorexr_loading_bar_height" value="<?php echo esc_attr($loading_bar_height); ?>" class="small-text">
                <p class="description">Height of loading bar (e.g., 4px)</p>
            </div>
            
            <div class="explorexr-form-group">
                <label for="explorexr_loading_bar_position">Loading Bar Position</label>
                <select name="explorexr_loading_bar_position" id="explorexr_loading_bar_position">
                    <option value="top" <?php selected($loading_bar_position, 'top'); ?>>Top</option>
                    <option value="bottom" <?php selected($loading_bar_position, 'bottom'); ?>>Bottom</option>
                </select>
                <p class="description">Position of loading bar</p>
            </div>
            
            <!-- Percentage Display -->
            <div class="explorexr-form-group">
                <label class="explorexr-checkbox-label">
                    <input type="checkbox" name="explorexr_percentage_show" id="explorexr_percentage_show" <?php checked($percentage_show, true); ?>>
                    <span>Show Loading Percentage</span>
                </label>
                <p class="description">Display loading percentage text</p>
            </div>
            
            <div class="explorexr-form-group">
                <label for="explorexr_percentage_precision">Percentage Precision</label>
                <select name="explorexr_percentage_precision" id="explorexr_percentage_precision">
                    <option value="0" <?php selected($percentage_precision, '0'); ?>>0 decimals (50%)</option>
                    <option value="1" <?php selected($percentage_precision, '1'); ?>>1 decimal (50.5%)</option>
                    <option value="2" <?php selected($percentage_precision, '2'); ?>>2 decimals (50.55%)</option>
                </select>
                <p class="description">Decimal precision for percentage</p>
            </div>
            
            <div class="explorexr-form-group">
                <label for="explorexr_percentage_suffix">Percentage Suffix</label>
                <input type="text" name="explorexr_percentage_suffix" id="explorexr_percentage_suffix" value="<?php echo esc_attr($percentage_suffix); ?>" class="small-text">
                <p class="description">Text after percentage (e.g., %)</p>
            </div>
            
            <!-- Loading Text -->
            <div class="explorexr-form-group">
                <label class="explorexr-checkbox-label">
                    <input type="checkbox" name="explorexr_loading_text_show" id="explorexr_loading_text_show" <?php checked($loading_text_show, true); ?>>
                    <span>Show Loading Text</span>
                </label>
                <p class="description">Display loading text message</p>
            </div>
            
            <div class="explorexr-form-group">
                <label for="explorexr_loading_text_content">Loading Text</label>
                <input type="text" name="explorexr_loading_text_content" id="explorexr_loading_text_content" value="<?php echo esc_attr($loading_text_content); ?>" class="regular-text">
                <p class="description">Text displayed while loading</p>
            </div>
            
            <div class="explorexr-form-group">
                <label for="explorexr_loading_text_position">Text Position</label>
                <select name="explorexr_loading_text_position" id="explorexr_loading_text_position">
                    <option value="center" <?php selected($loading_text_position, 'center'); ?>>Center</option>
                    <option value="top" <?php selected($loading_text_position, 'top'); ?>>Top</option>
                    <option value="bottom" <?php selected($loading_text_position, 'bottom'); ?>>Bottom</option>
                </select>
                <p class="description">Position of loading text</p>
            </div>
            
            <!-- Overlay Settings -->
            <div class="explorexr-form-group">
                <label class="explorexr-checkbox-label">
                    <input type="checkbox" name="explorexr_overlay_show" id="explorexr_overlay_show" <?php checked($overlay_show, true); ?>>
                    <span>Show Loading Overlay</span>
                </label>
                <p class="description">Show overlay during loading</p>
            </div>
            
            <div class="explorexr-form-group">
                <label for="explorexr_overlay_color">Overlay Color</label>
                <input type="text" name="explorexr_overlay_color" id="explorexr_overlay_color" value="<?php echo esc_attr($overlay_color); ?>" class="regular-text">
                <p class="description">Overlay background color (CSS format)</p>
            </div>
            
            <div class="explorexr-form-group">
                <label class="explorexr-checkbox-label">
                    <input type="checkbox" name="explorexr_overlay_blur" id="explorexr_overlay_blur" <?php checked($overlay_blur, true); ?>>
                    <span>Blur Background</span>
                </label>
                <p class="description">Apply blur effect to overlay</p>
            </div>
            
            <!-- Performance Settings -->
            <div class="explorexr-form-group">
                <label class="explorexr-checkbox-label">
                    <input type="checkbox" name="explorexr_lazy_load_model" id="explorexr_lazy_load_model" <?php checked($lazy_load_model, true); ?>>
                    <span>Lazy Load Model</span>
                </label>
                <p class="description">Load model only when visible</p>
            </div>
            
            <div class="explorexr-form-group">
                <label for="explorexr_script_location">Script Location</label>
                <select name="explorexr_script_location" id="explorexr_script_location">
                    <option value="header" <?php selected($script_location, 'header'); ?>>Header</option>
                    <option value="footer" <?php selected($script_location, 'footer'); ?>>Footer</option>
                </select>                <p class="description">Where to load model viewer scripts</p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Premium Feature Notice -->
<div class="explorexr-card explorexr-premium-feature-card">
    <div class="explorexr-card-header">
        <h2><span class="dashicons dashicons-performance"></span> Advanced Loading Options <span class="explorexr-premium-badge">Premium</span></h2>
    </div>
    <div class="explorexr-card-content">
        <div class="explorexr-premium-notice">
            <div class="explorexr-premium-icon">⚡</div>
            <div class="explorexr-premium-content">
                <h3>Advanced Loading & Performance</h3>
                <p>Customize loading behavior and optimize performance with advanced options:</p>
                <ul class="explorexr-feature-list">
                    <li>🎨 Custom loading animations</li>
                    <li>⚡ Performance optimization</li>
                    <li>📱 Progressive loading</li>
                    <li>🎭 Branded loading screens</li>
                    <li>⚙️ Advanced caching options</li>
                </ul>
                <a href="<?php echo esc_url(explorexr_get_premium_upgrade_url()); ?>" class="button button-primary" target="_blank">
                    Upgrade to Premium
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; // End loading customization check ?>





