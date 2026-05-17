<?php
/**
 * Model Preview Card Template
 *
 * Displays a preview of the 3D model with the shortcode
 *
 * @package ExploreXR_Premium
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Template variables passed from parent scope - disable PHPCS global prefix check
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Check if model_id is defined, if not try to get it from $_GET
if (!isset($model_id) || empty($model_id)) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for display
    $model_id = isset($_GET['model_id']) ? intval($_GET['model_id']) : 0;
    if (!$model_id) {
        echo '<div class="notice notice-error"><p>Error: Model ID not provided to model-preview-card.php template.</p></div>';
        return;
    }
}

// Ensure required variables are defined
if (!isset($shortcode)) {
    $shortcode = '[explorexr_model id="' . $model_id . '"]';
}

// Always get fresh model file from database to ensure latest value
$model_file = get_post_meta($model_id, '_explorexr_model_file', true);
if (empty($model_file)) {
    $model_file = '';
}

if (!isset($poster_url)) {
    $poster_url = get_post_meta($model_id, '_explorexr_model_poster', true) ?: '';
}

if (!isset($auto_rotate)) {
    $auto_rotate = get_post_meta($model_id, '_explorexr_auto_rotate', true) === 'on';
}

if (!isset($camera_controls)) {
    $enable_interactions = get_post_meta($model_id, '_explorexr_enable_interactions', true) ?: 'on';
    $camera_controls = ($enable_interactions === 'on');
}

// Animation settings are not available in the Free version
// This feature is available in the Pro version only

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- HTML class attributes, not PHP class declarations
?>
<!-- Model Preview Section -->
<div class="explorexr-card explorexr-preview-card">
    <div class="explorexr-card-header">
        <h2><span class="dashicons dashicons-visibility"></span> Model Preview</h2>
        <div class="explorexr-model-shortcode">
            <code><?php echo esc_html($shortcode); ?></code>
            <button type="button" class="copy-shortcode" data-shortcode="<?php echo esc_attr($shortcode); ?>">
                <span class="dashicons dashicons-clipboard"></span> Copy
            </button>
        </div>
    </div>
    <div class="explorexr-card-content">
        <div id="explorexr-model-preview-container" style="position: relative;" data-model-id="<?php echo intval($model_id); ?>">
            <?php if (!empty($model_file)) : ?>
            <?php
            /**
             * Build preview attributes using the centralized shortcode function
             * so the admin preview matches the frontend rendering exactly.
             *
             * EXPLOREXR_build_model_attributes() reads ALL per-model post_meta
             * (camera orbit, FOV, limits, auto-rotate, etc.) and runs the
             * 'explorexr_premium_model_viewer_attributes' filter which lets
             * every active addon inject its data-* attributes.
             */
            $preview_attributes = EXPLOREXR_build_model_attributes(
                $model_id,
                $model_file,
                get_post_meta($model_id, '_explorexr_model_alt_text', true) ?: get_the_title($model_id),
                '100%',
                '100%',
                $poster_url
            );

            // Add admin-specific attributes
            $preview_attributes['id']      = 'main-preview-model-viewer';
            $preview_attributes['class']   = 'explorexr-model-preview';
            $preview_attributes['style']   = 'width:100%;height:100%;background:#f5f5f5;';
            $preview_attributes['loading'] = 'eager';

            // CRITICAL: The main admin preview must always remain interactive regardless
            // of per-model interaction settings. Addons (e.g. Camera) may remove
            // camera-controls via the filter above, but admin always needs it for editing.
            $preview_attributes['camera-controls']    = true;
            $preview_attributes['interaction-prompt'] = 'none';
            $preview_attributes['reveal']             = 'auto';

            // Use the centralized attribute-to-HTML converter
            $attribute_string = EXPLOREXR_generate_attributes_html($preview_attributes);
            ?>
            <model-viewer<?php echo $attribute_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes are escaped in EXPLOREXR_generate_attributes_html ?>>
            </model-viewer>
            <?php
            // Model preview JS is loaded via admin/js/model-preview-card.js (enqueued in admin-menu.php).
            ?>
            <?php else : ?>
            <div class="explorexr-empty-preview">
                <span class="dashicons dashicons-format-image"></span>
                <p>No model file available</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
// Re-enable PHPCS global prefix check
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
