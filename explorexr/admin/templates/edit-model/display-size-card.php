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
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for display
    $model_id = isset($_GET['model_id']) ? intval($_GET['model_id']) : 0;
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for template display only
    if (!$model_id) {
        echo '<div class="notice notice-error"><p>Error: Model ID not provided to display-size-card.php template.</p></div>';
        return;
    }
}

// Ensure required variables are defined
if (!isset($viewer_size)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $viewer_size = get_post_meta($model_id, '_explorexr_viewer_size', true) ?: 'custom';
}

if (!isset($viewer_width)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $viewer_width = get_post_meta($model_id, '_explorexr_viewer_width', true) ?: '100%';
}

if (!isset($viewer_height)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $viewer_height = get_post_meta($model_id, '_explorexr_viewer_height', true) ?: '500px';
}

if (!isset($tablet_viewer_width)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $tablet_viewer_width = get_post_meta($model_id, '_explorexr_tablet_viewer_width', true) ?: '';
}

if (!isset($tablet_viewer_height)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $tablet_viewer_height = get_post_meta($model_id, '_explorexr_tablet_viewer_height', true) ?: '';
}

if (!isset($mobile_viewer_width)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $mobile_viewer_width = get_post_meta($model_id, '_explorexr_mobile_viewer_width', true) ?: '';
}

if (!isset($mobile_viewer_height)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
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
            <p class="description" style="margin-bottom: 15px;">
                <span class="dashicons dashicons-info"></span> 
                Predefined sizes automatically adapt for tablet and smartphone devices.
            </p>
            <div class="explorexr-size-options">
                <label class="explorexr-size-option">
                    <input type="radio" name="viewer_size" value="small" <?php checked($viewer_size, 'small'); ?>>
                    <div class="explorexr-size-preview">
                        <div class="explorexr-size-box explorexr-size-box-small"></div>
                        <span>Small</span>
                        <small class="explorexr-responsive-info">
                            Desktop: 300×300px<br>
                            Tablet: 280×280px<br>
                            Mobile: 100%×280px
                        </small>
                    </div>
                </label>
                
                <label class="explorexr-size-option">
                    <input type="radio" name="viewer_size" value="medium" <?php checked($viewer_size, 'medium'); ?>>
                    <div class="explorexr-size-preview">
                        <div class="explorexr-size-box explorexr-size-box-medium"></div>
                        <span>Medium</span>
                        <small class="explorexr-responsive-info">
                            Desktop: 500×500px<br>
                            Tablet: 450×450px<br>
                            Mobile: 100%×400px
                        </small>
                    </div>
                </label>
                
                <label class="explorexr-size-option">
                    <input type="radio" name="viewer_size" value="large" <?php checked($viewer_size, 'large'); ?>>
                    <div class="explorexr-size-preview">
                        <div class="explorexr-size-box explorexr-size-box-large"></div>
                        <span>Large</span>
                        <small class="explorexr-responsive-info">
                            Desktop: 800×600px<br>
                            Tablet: 600×450px<br>
                            Mobile: 100%×400px
                        </small>
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
                    <span class="dashicons dashicons-smartphone"></span> Smartphone
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
                    <h3>Tablet Size <span class="explorexr-breakpoint-hint">(768px - 1024px)</span></h3>
                    <p class="description explorexr-form-description"><?php esc_html_e('Leave empty to use desktop size on tablets. Accepts CSS units: px, %, vw, vh, em, rem.', 'explorexr'); ?></p>
                    <div class="explorexr-form-row">
                        <label for="tablet_viewer_width">Width:</label>
                        <input type="text" name="tablet_viewer_width" id="tablet_viewer_width" value="<?php echo esc_attr($tablet_viewer_width); ?>" class="small-text" placeholder="<?php echo esc_attr($viewer_width); ?>">
                        <span class="description">(e.g., 600px, 90%, etc.)</span>
                    </div>
                    
                    <div class="explorexr-form-row">
                        <label for="tablet_viewer_height">Height:</label>
                        <input type="text" name="tablet_viewer_height" id="tablet_viewer_height" value="<?php echo esc_attr($tablet_viewer_height); ?>" class="small-text" placeholder="<?php echo esc_attr($viewer_height); ?>">
                        <span class="description">(e.g., 450px, 80vh, etc.)</span>
                    </div>
                </div>
            </div>
            
            <div class="explorexr-device-content" id="mobile-size">
                <div class="explorexr-form-group">
                    <h3>Smartphone Size <span class="explorexr-breakpoint-hint">(up to 767px)</span></h3>
                    <p class="description explorexr-form-description"><?php esc_html_e('Leave empty to use desktop size on smartphones. Accepts CSS units: px, %, vw, vh, em, rem.', 'explorexr'); ?></p>
                    <div class="explorexr-form-row">
                        <label for="mobile_viewer_width">Width:</label>
                        <input type="text" name="mobile_viewer_width" id="mobile_viewer_width" value="<?php echo esc_attr($mobile_viewer_width); ?>" class="small-text" placeholder="<?php echo esc_attr($viewer_width); ?>">
                        <span class="description">(e.g., 100%, 390px, etc.)</span>
                    </div>
                    
                    <div class="explorexr-form-row">
                        <label for="mobile_viewer_height">Height:</label>
                        <input type="text" name="mobile_viewer_height" id="mobile_viewer_height" value="<?php echo esc_attr($mobile_viewer_height); ?>" class="small-text" placeholder="<?php echo esc_attr($viewer_height); ?>">
                        <span class="description">(e.g., 350px, 60vh, etc.)</span>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="viewer_size" value="custom" id="custom_size_field">
        </div>
    </div>
</div>





