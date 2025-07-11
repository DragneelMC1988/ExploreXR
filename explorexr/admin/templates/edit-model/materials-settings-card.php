<?php
/**
 * Materials Settings Card Template
 * Template for the materials addon settings section in the edit model page
 * 
 * @package ExpoXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if Material Editor is available (Premium feature)
if (function_exists('expoxr_is_premium_available') && expoxr_is_premium_available()) :
    // Premium version: Get material editor settings
    $materials_enabled = get_post_meta($model_id, '_expoxr_materials_enabled', true) === 'on';
    $materials_show_ui = get_post_meta($model_id, '_expoxr_materials_show_ui', true) === 'on';
    $materials_ui_position = get_post_meta($model_id, '_expoxr_materials_ui_position', true) ?: 'bottom-right';
    $materials_ui_style = get_post_meta($model_id, '_expoxr_materials_ui_style', true) ?: 'buttons';
    $materials_default_variant = get_post_meta($model_id, '_expoxr_materials_default_variant', true) ?: '';
    $materials_display_mode = get_post_meta($model_id, '_expoxr_materials_display_mode', true) ?: 'hover';
    $materials_custom_button_text = get_post_meta($model_id, '_expoxr_materials_custom_button_text', true) ?: 'Materials';
    $materials_custom_button_icon = get_post_meta($model_id, '_expoxr_materials_custom_button_icon', true) ?: '';
    $materials_custom_button_color = get_post_meta($model_id, '_expoxr_materials_custom_button_color', true) ?: '#1e88e5';
?>
<div class="expoxr-card">
    <div class="expoxr-card-header">
        <h2><span class="dashicons dashicons-art"></span> Materials Settings</h2>
    </div>
    <div class="expoxr-card-content">
        <div class="expoxr-form-grid expoxr-form-grid-3">
            <!-- Materials Control -->
            <div class="expoxr-form-group">
                <label class="expoxr-checkbox-label">
                    <input type="checkbox" name="expoxr_materials_enabled" id="expoxr_materials_enabled" <?php checked($materials_enabled, true); ?>>
                    <span>Enable Materials</span>
                </label>
                <p class="description">Allow material variant switching</p>
            </div>
            
            <div class="expoxr-form-group">
                <label class="expoxr-checkbox-label">
                    <input type="checkbox" name="expoxr_materials_show_ui" id="expoxr_materials_show_ui" <?php checked($materials_show_ui, true); ?>>
                    <span>Show Materials UI</span>
                </label>
                <p class="description">Display material selection interface</p>
            </div>
            
            <div class="expoxr-form-group">
                <label for="expoxr_materials_ui_position">UI Position</label>
                <select name="expoxr_materials_ui_position" id="expoxr_materials_ui_position">
                    <option value="top-left" <?php selected($materials_ui_position, 'top-left'); ?>>Top Left</option>
                    <option value="top-right" <?php selected($materials_ui_position, 'top-right'); ?>>Top Right</option>
                    <option value="bottom-left" <?php selected($materials_ui_position, 'bottom-left'); ?>>Bottom Left</option>
                    <option value="bottom-right" <?php selected($materials_ui_position, 'bottom-right'); ?>>Bottom Right</option>
                    <option value="center" <?php selected($materials_ui_position, 'center'); ?>>Center</option>
                </select>
                <p class="description">Position of materials UI</p>
            </div>
            
            <div class="expoxr-form-group">
                <label for="expoxr_materials_ui_style">UI Style</label>
                <select name="expoxr_materials_ui_style" id="expoxr_materials_ui_style">
                    <option value="buttons" <?php selected($materials_ui_style, 'buttons'); ?>>Buttons</option>
                    <option value="dropdown" <?php selected($materials_ui_style, 'dropdown'); ?>>Dropdown</option>
                    <option value="grid" <?php selected($materials_ui_style, 'grid'); ?>>Grid</option>
                    <option value="slider" <?php selected($materials_ui_style, 'slider'); ?>>Slider</option>
                </select>
                <p class="description">Style of materials selection UI</p>
            </div>
            
            <div class="expoxr-form-group">
                <label for="expoxr_materials_default_variant">Default Material Variant</label>
                <input type="text" name="expoxr_materials_default_variant" id="expoxr_materials_default_variant" value="<?php echo esc_attr($materials_default_variant); ?>" class="regular-text">
                <p class="description">Default material variant name (auto-detected if empty)</p>
            </div>
            
            <div class="expoxr-form-group">
                <label for="expoxr_materials_display_mode">Display Mode</label>
                <select name="expoxr_materials_display_mode" id="expoxr_materials_display_mode">
                    <option value="always" <?php selected($materials_display_mode, 'always'); ?>>Always Visible</option>
                    <option value="hover" <?php selected($materials_display_mode, 'hover'); ?>>Show on Hover</option>
                    <option value="click" <?php selected($materials_display_mode, 'click'); ?>>Show on Click</option>
                </select>
                <p class="description">When to show materials UI</p>
            </div>
            
            <!-- Custom Button Settings -->
            <div class="expoxr-form-group">
                <label for="expoxr_materials_custom_button_text">Custom Button Text</label>
                <input type="text" name="expoxr_materials_custom_button_text" id="expoxr_materials_custom_button_text" value="<?php echo esc_attr($materials_custom_button_text); ?>" class="regular-text">
                <p class="description">Text for custom materials button</p>
            </div>
            
            <div class="expoxr-form-group">
                <label for="expoxr_materials_custom_button_icon">Custom Button Icon</label>
                <input type="text" name="expoxr_materials_custom_button_icon" id="expoxr_materials_custom_button_icon" value="<?php echo esc_attr($materials_custom_button_icon); ?>" class="regular-text">
                <p class="description">Icon class or URL for button</p>
            </div>
            
            <div class="expoxr-form-group">
                <label for="expoxr_materials_custom_button_color">Custom Button Color</label>
                <input type="color" name="expoxr_materials_custom_button_color" id="expoxr_materials_custom_button_color" value="<?php echo esc_attr($materials_custom_button_color); ?>">
                <p class="description">Color of the materials button</p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Premium Feature Notice -->
<div class="expoxr-card expoxr-premium-feature-card">
    <div class="expoxr-card-header">
        <h2><span class="dashicons dashicons-art"></span> Material Editor <span class="expoxr-premium-badge">Premium</span></h2>
    </div>
    <div class="expoxr-card-content">
        <div class="expoxr-premium-notice">
            <div class="expoxr-premium-icon">🎨</div>
            <div class="expoxr-premium-content">
                <h3>Advanced Material Editor</h3>
                <p>Edit and customize materials, textures, and colors of your 3D models in real-time:</p>
                <ul class="expoxr-feature-list">
                    <li>✨ Real-time material editing</li>
                    <li>🎨 Texture swapping</li>
                    <li>🌈 Color variants</li>
                    <li>✨ Surface properties control</li>
                    <li>🎭 Multiple material presets</li>
                </ul>
                <a href="<?php echo esc_url(expoxr_get_premium_upgrade_url()); ?>" class="button button-primary" target="_blank">
                    Upgrade to Premium
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>





