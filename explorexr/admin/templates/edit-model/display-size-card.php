<?php
/**
 * Display Size Settings Card Template
 * 
 * Options for predefined and custom model viewer sizes
 *
  * @package ExploreXR
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if model_id is defined, if not try to get it from $_GET
if (!isset($model_id) || empty($model_id)) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for template display only
    $model_id = isset($_GET['model_id']) ? intval($_GET['model_id']) : 0;
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for template display only
    if (!$model_id) {
        echo '<div class="notice notice-error"><p>Error: Model ID not provided to display-size-card.php template.</p></div>';
        return;
    }
}

// Ensure required variables are defined
if (!isset($viewer_size)) {
    $viewer_size = get_post_meta($model_id, '_explorexr_viewer_size', true) ?: 'custom';
}

if (!isset($viewer_width)) {
    $viewer_width = get_post_meta($model_id, '_explorexr_viewer_width', true) ?: '100%';
}

if (!isset($viewer_height)) {
    $viewer_height = get_post_meta($model_id, '_explorexr_viewer_height', true) ?: '500px';
}

if (!isset($tablet_viewer_width)) {
    $tablet_viewer_width = get_post_meta($model_id, '_explorexr_tablet_viewer_width', true) ?: '';
}

if (!isset($tablet_viewer_height)) {
    $tablet_viewer_height = get_post_meta($model_id, '_explorexr_tablet_viewer_height', true) ?: '';
}

if (!isset($mobile_viewer_width)) {
    $mobile_viewer_width = get_post_meta($model_id, '_explorexr_mobile_viewer_width', true) ?: '';
}

if (!isset($mobile_viewer_height)) {
    $mobile_viewer_height = get_post_meta($model_id, '_explorexr_mobile_viewer_height', true) ?: '';
}
?>

<!-- Display Size Settings -->
<div class="explorexr-card">
    <div class="explorexr-card-header">
        <h2><span class="dashicons dashicons-editor-distractionfree"></span> Display Size</h2>
    </div>
    <div class="explorexr-card-content">
        <div class="explorexr-tabs">
            <button type="button" class="explorexr-tab <?php echo ($viewer_size !== 'custom') ? 'active' : ''; ?>" data-tab="predefined-sizes">Predefined Sizes</button>
            <button type="button" class="explorexr-tab <?php echo ($viewer_size === 'custom') ? 'active' : ''; ?>" data-tab="custom-sizes">Custom Sizes</button>
        </div>
        
        <div class="explorexr-tab-content <?php echo ($viewer_size !== 'custom') ? 'active' : ''; ?>" id="predefined-sizes">
            <div class="explorexr-size-options">
                <label class="explorexr-size-option">
                    <input type="radio" name="viewer_size" value="small" <?php checked($viewer_size, 'small'); ?>>
                    <div class="explorexr-size-preview">
                        <div class="explorexr-size-box explorexr-size-box-small"></div>
                        <span>Small (300x300px)</span>
                    </div>
                </label>
                
                <label class="explorexr-size-option">
                    <input type="radio" name="viewer_size" value="medium" <?php checked($viewer_size, 'medium'); ?>>
                    <div class="explorexr-size-preview">
                        <div class="explorexr-size-box explorexr-size-box-medium"></div>
                        <span>Medium (500x500px)</span>
                    </div>
                </label>
                
                <label class="explorexr-size-option">
                    <input type="radio" name="viewer_size" value="large" <?php checked($viewer_size, 'large'); ?>>
                    <div class="explorexr-size-preview">
                        <div class="explorexr-size-box explorexr-size-box-large"></div>
                        <span>Large (800x600px)</span>
                    </div>
                </label>
            </div>
        </div>
        
        <div class="explorexr-tab-content <?php echo ($viewer_size === 'custom') ? 'active' : ''; ?>" id="custom-sizes">
            <div class="explorexr-device-tabs">
                <button type="button" class="explorexr-device-tab active" data-device="desktop">
                    <span class="dashicons dashicons-desktop"></span> Desktop
                </button>
                <button type="button" class="explorexr-device-tab" data-device="tablet">
                    <span class="dashicons dashicons-tablet"></span> Tablet
                </button>
                <button type="button" class="explorexr-device-tab" data-device="mobile">
                    <span class="dashicons dashicons-smartphone"></span> Mobile
                </button>
            </div>
            
            <div class="explorexr-device-content active" id="desktop-size">
                <div class="explorexr-form-group">
                    <h3>Desktop Size</h3>
                    <div class="explorexr-form-row">
                        <label for="viewer_width">Width:</label>
                        <input type="text" name="viewer_width" id="viewer_width" value="<?php echo esc_attr($viewer_width); ?>" class="small-text">
                        <span class="description">(e.g., 500px, 100%, etc.)</span>
                    </div>
                    
                    <div class="explorexr-form-row">
                        <label for="viewer_height">Height:</label>
                        <input type="text" name="viewer_height" id="viewer_height" value="<?php echo esc_attr($viewer_height); ?>" class="small-text">
                        <span class="description">(e.g., 500px, 400px, etc.)</span>
                    </div>
                </div>
            </div>
            
            <div class="explorexr-device-content" id="tablet-size">
                <div class="explorexr-form-group">
                    <h3>Tablet Size</h3>
                    <div class="explorexr-form-row">
                        <label for="tablet_viewer_width">Width:</label>
                        <input type="text" name="tablet_viewer_width" id="tablet_viewer_width" value="<?php echo esc_attr($tablet_viewer_width); ?>" class="small-text">
                        <span class="description">(e.g., 500px, 100%, etc.)</span>
                    </div>
                    
                    <div class="explorexr-form-row">
                        <label for="tablet_viewer_height">Height:</label>
                        <input type="text" name="tablet_viewer_height" id="tablet_viewer_height" value="<?php echo esc_attr($tablet_viewer_height); ?>" class="small-text">
                        <span class="description">(e.g., 400px, 350px, etc.)</span>
                    </div>
                </div>
            </div>
            
            <div class="explorexr-device-content" id="mobile-size">
                <div class="explorexr-form-group">
                    <h3>Mobile Size</h3>
                    <div class="explorexr-form-row">
                        <label for="mobile_viewer_width">Width:</label>
                        <input type="text" name="mobile_viewer_width" id="mobile_viewer_width" value="<?php echo esc_attr($mobile_viewer_width); ?>" class="small-text">
                        <span class="description">(e.g., 300px, 100%, etc.)</span>
                    </div>
                    
                    <div class="explorexr-form-row">
                        <label for="mobile_viewer_height">Height:</label>
                        <input type="text" name="mobile_viewer_height" id="mobile_viewer_height" value="<?php echo esc_attr($mobile_viewer_height); ?>" class="small-text">
                        <span class="description">(e.g., 300px, 400px, etc.)</span>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="viewer_size" value="custom" id="custom_size_field">
        </div>
    </div>
</div>





