<?php
/**
 * Model Preview Card Template
 * 
 * Displays a preview of the 3D model with the shortcode
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
        echo '<div class="notice notice-error"><p>Error: Model ID not provided to model-preview-card.php template.</p></div>';
        return;
    }
}

// Ensure required variables are defined
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
if (!isset($shortcode)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $shortcode = '[explorexr_model id="' . $model_id . '"]';
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
if (!isset($model_file)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $model_file = get_post_meta($model_id, '_explorexr_model_file', true) ?: '';
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
if (!isset($poster_url)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $poster_url = get_post_meta($model_id, '_explorexr_model_poster', true) ?: '';
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
if (!isset($auto_rotate)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $auto_rotate = get_post_meta($model_id, '_explorexr_auto_rotate', true) === 'on';
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
if (!isset($camera_controls)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $enable_interactions = get_post_meta($model_id, '_explorexr_enable_interactions', true) ?: 'on';
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $camera_controls = ($enable_interactions === 'on');
}

// Animation settings are not available in the Free version
// This feature is available in the Pro version only
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
        <div id="explorexr-model-preview-container">
            <?php if (!empty($model_file)) : ?>                    
            <model-viewer 
                src="<?php echo esc_url($model_file); ?>"
                <?php if (!empty($poster_url)) : ?>poster="<?php echo esc_url($poster_url); ?>"<?php endif; ?>
                <?php if ($auto_rotate) : ?>auto-rotate<?php endif; ?>
                camera-controls
                shadow-intensity="1"
                class="explorexr-model-preview">
            </model-viewer>
            <?php else : ?>
            <div class="explorexr-empty-preview">
                <span class="dashicons dashicons-format-image"></span>
                <p>No model file available</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>





