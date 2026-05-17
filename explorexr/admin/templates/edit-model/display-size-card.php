<?php
/**
 * Display Size Settings Card Template
 *
 * Options for predefined and custom model viewer sizes.
 *
 * FIX: Uses a single hidden field for viewer_size that is updated by JavaScript
 * based on the active tab and selected radio button. This eliminates the
 * dual-field conflict where predefined radio and custom hidden field both
 * submitted name="viewer_size" causing overwrites.
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
    $viewer_width = get_post_meta($model_id, '_explorexr_viewer_width', true) ?: '100vw';
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

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable
$is_predefined = in_array($viewer_size, array('small', 'medium', 'large', 'full'), true);
?>

<!-- Display Size Settings -->
<div class="explorexr-card" id="explorexr-display-size-card">
    <div class="explorexr-card-header">
        <h2><span class="dashicons dashicons-editor-distractionfree"></span> Display Size</h2>
    </div>
    <div class="explorexr-card-content">
        <!-- Single authoritative hidden field for viewer_size -->
        <input type="hidden" name="viewer_size" id="explorexr_viewer_size_field" value="<?php echo esc_attr($viewer_size); ?>">

        <div class="explorexr-tabs">
            <button type="button" class="explorexr-tab <?php echo $is_predefined ? 'active' : ''; ?>" data-tab="predefined-sizes">Predefined Sizes</button>
            <button type="button" class="explorexr-tab <?php echo (!$is_predefined) ? 'active' : ''; ?>" data-tab="custom-sizes">Custom Sizes</button>
        </div>

        <div class="explorexr-tab-content <?php echo $is_predefined ? 'active' : ''; ?>" id="predefined-sizes">
            <p class="description" style="margin-bottom: 15px;">
                <span class="dashicons dashicons-info"></span>
                Predefined sizes automatically adapt for tablet and smartphone devices.
            </p>
            <div class="explorexr-size-options">
                <label class="explorexr-size-option">
                    <input type="radio" name="explorexr_predefined_size" value="small" <?php checked($viewer_size, 'small'); ?>>
                    <div class="explorexr-size-preview">
                        <div class="explorexr-size-box explorexr-size-box-small"></div>
                        <span>Small</span>
                        <small style="display: block; color: #666; margin-top: 4px;">
                            Desktop: 300×300px<br>
                            Tablet: 280×280px<br>
                            Mobile: 100vw×250px
                        </small>
                    </div>
                </label>

                <label class="explorexr-size-option">
                    <input type="radio" name="explorexr_predefined_size" value="medium" <?php checked($viewer_size, 'medium'); ?>>
                    <div class="explorexr-size-preview">
                        <div class="explorexr-size-box explorexr-size-box-medium"></div>
                        <span>Medium</span>
                        <small style="display: block; color: #666; margin-top: 4px;">
                            Desktop: 500×500px<br>
                            Tablet: 400×400px<br>
                            Mobile: 100vw×350px
                        </small>
                    </div>
                </label>

                <label class="explorexr-size-option">
                    <input type="radio" name="explorexr_predefined_size" value="large" <?php checked($viewer_size, 'large'); ?>>
                    <div class="explorexr-size-preview">
                        <div class="explorexr-size-box explorexr-size-box-large"></div>
                        <span>Large</span>
                        <small style="display: block; color: #666; margin-top: 4px;">
                            Desktop: 800×600px<br>
                            Tablet: 600×450px<br>
                            Mobile: 100vw×400px
                        </small>
                    </div>
                </label>

                <label class="explorexr-size-option">
                    <input type="radio" name="explorexr_predefined_size" value="full" <?php checked($viewer_size, 'full'); ?>>
                    <div class="explorexr-size-preview">
                        <div class="explorexr-size-box explorexr-size-box-full"></div>
                        <span>Full</span>
                        <small style="display: block; color: #666; margin-top: 4px;">
                            Desktop: 100vw×90vh<br>
                            Tablet: 100vw×70vh<br>
                            Mobile: 100vw×60vh
                        </small>
                    </div>
                </label>
            </div>
        </div>

        <div class="explorexr-tab-content <?php echo (!$is_predefined) ? 'active' : ''; ?>" id="custom-sizes">
            <!-- Important validation notice -->
            <div class="notice notice-info inline" style="margin: 0 0 15px 0; padding: 8px 12px;">
                <p style="margin: 0.5em 0;"><strong><span class="dashicons dashicons-info" style="vertical-align: middle;"></span> Important:</strong> Allowed units: <code>px</code>, <code>vw</code>, <code>vh</code>, <code>dvw</code>, <code>dvh</code>, <code>em</code>, <code>rem</code>. Percentage (%) is <strong>not</strong> supported — use <code>vw</code>/<code>vh</code> for viewport-relative sizing instead.</p>
            </div>

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
                        <span class="description">(e.g., 500px, 100vw, 50dvw)</span>
                    </div>

                    <div class="explorexr-form-row">
                        <label for="viewer_height">Height:</label>
                        <input type="text" name="viewer_height" id="viewer_height" value="<?php echo esc_attr($viewer_height); ?>" class="small-text">
                        <span class="description">(e.g., 500px, 50vh, 90dvh)</span>
                    </div>
                </div>
            </div>

            <div class="explorexr-device-content" id="tablet-size">
                <div class="explorexr-form-group">
                    <h3>Tablet Size <span class="optional">(optional — leave empty to inherit desktop)</span></h3>
                    <div class="explorexr-form-row">
                        <label for="tablet_viewer_width">Width:</label>
                        <input type="text" name="tablet_viewer_width" id="tablet_viewer_width" value="<?php echo esc_attr($tablet_viewer_width); ?>" class="small-text">
                        <span class="description">(e.g., 100vw, 500px, 50dvw)</span>
                    </div>

                    <div class="explorexr-form-row">
                        <label for="tablet_viewer_height">Height:</label>
                        <input type="text" name="tablet_viewer_height" id="tablet_viewer_height" value="<?php echo esc_attr($tablet_viewer_height); ?>" class="small-text">
                        <span class="description">(e.g., 400px, 50vh, 70dvh)</span>
                    </div>
                </div>
            </div>

            <div class="explorexr-device-content" id="mobile-size">
                <div class="explorexr-form-group">
                    <h3>Mobile Size <span class="optional">(optional — leave empty to inherit desktop)</span></h3>
                    <div class="explorexr-form-row">
                        <label for="mobile_viewer_width">Width:</label>
                        <input type="text" name="mobile_viewer_width" id="mobile_viewer_width" value="<?php echo esc_attr($mobile_viewer_width); ?>" class="small-text">
                        <span class="description">(e.g., 100vw, 300px, 100dvw)</span>
                    </div>

                    <div class="explorexr-form-row">
                        <label for="mobile_viewer_height">Height:</label>
                        <input type="text" name="mobile_viewer_height" id="mobile_viewer_height" value="<?php echo esc_attr($mobile_viewer_height); ?>" class="small-text">
                        <span class="description">(e.g., 300px, 50vh, 60dvh)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
