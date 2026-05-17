<?php
/**
 * Viewer Controls Card Template
 * 
 * Basic controls like camera controls and auto-rotate
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
        echo '<div class="notice notice-error"><p>Error: Model ID not provided to viewer-controls-card.php template.</p></div>';
        return;
    }
}

// Ensure interactions and auto_rotate variables are defined with proper defaults
if (!isset($enable_interactions)) {
    $enable_interactions_meta = get_post_meta($model_id, '_explorexr_enable_interactions', true) ?: 'on';
    $enable_interactions = ($enable_interactions_meta === 'on');
}

if (!isset($auto_rotate)) {
    $auto_rotate_meta = get_post_meta($model_id, '_explorexr_auto_rotate', true) ?: '';
    if ($auto_rotate_meta === '') {
        $auto_rotate = false; // Default to disabled
        update_post_meta($model_id, '_explorexr_auto_rotate', 'off');
    } else {
        $auto_rotate = ($auto_rotate_meta === 'on');
    }
}
?>

<!-- Viewer Controls -->
<div class="explorexr-card">
    <div class="explorexr-card-header">
        <h2><span class="dashicons dashicons-admin-generic"></span> Viewer Controls</h2>
    </div>
    <div class="explorexr-card-content">
        <div class="explorexr-form-grid">
            <div class="explorexr-form-group">
                <label class="explorexr-checkbox-label">
                    <input type="checkbox" name="explorexr_enable_interactions" id="explorexr_enable_interactions" <?php checked($enable_interactions, true); ?>>
                    <span>Enable interactions</span>
                </label>
                <p class="description">Allow user interactions (rotate, zoom, pan). Models have interactions enabled by default.</p>
            </div>
        </div>
        
        <!-- Auto-rotate Section (separate from grid to stack vertically) -->
        <div class="explorexr-form-group">
            <label class="explorexr-checkbox-label">
                <input type="checkbox" name="explorexr_auto_rotate" id="explorexr_auto_rotate" <?php checked($auto_rotate, true); ?>>
                <span>Auto-rotate model</span>
            </label>
            <p class="description">Automatically rotate the model when the page loads.</p>
            
            <!-- Basic Auto-rotate Settings (shown when auto-rotate is enabled) -->
            <div id="auto-rotate-settings" <?php if (!$auto_rotate) echo 'class="explorexr-hidden"'; ?>>
                <?php 
                // Get auto-rotate delay with default value if not set
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
                $auto_rotate_delay = get_post_meta($model_id, '_explorexr_auto_rotate_delay', true);
                if (empty($auto_rotate_delay)) {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
                    $auto_rotate_delay = '5000';
                }
                
                // Get rotation speed with default value if not set
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
                $auto_rotate_speed = get_post_meta($model_id, '_explorexr_rotation_per_second', true);
                if (empty($auto_rotate_speed)) {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
                    $auto_rotate_speed = '30deg';
                }
                ?>
                <div class="explorexr-form-group explorexr-form-group-indent">
                    <label for="explorexr_auto_rotate_delay">Auto-rotate Delay</label>
                    <input type="text" name="explorexr_auto_rotate_delay" id="explorexr_auto_rotate_delay" value="<?php echo esc_attr($auto_rotate_delay); ?>" class="small-text">
                    <p class="description">Delay before auto-rotate starts (milliseconds)</p>
                </div>
                
                <div class="explorexr-form-group explorexr-form-group-indent">
                    <label for="explorexr_auto_rotate_speed">Auto-rotate Speed</label>
                    <input type="text" name="explorexr_auto_rotate_speed" id="explorexr_auto_rotate_speed" value="<?php echo esc_attr($auto_rotate_speed); ?>" class="small-text">
                    <p class="description">Speed of auto-rotation (e.g., 30deg, 45deg, 60deg)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Viewer controls JS is loaded via admin/js/viewer-controls-card.js (enqueued in admin-menu.php).

// Re-enable PHPCS global prefix check
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

