<?php
/**
 * Viewer Controls Card Template
 * 
 * Basic controls like camera controls and auto-rotate
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
        echo '<div class="notice notice-error"><p>Error: Model ID not provided to viewer-controls-card.php template.</p></div>';
        return;
    }
}

// Ensure interactions and auto_rotate variables are defined with proper defaults
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
if (!isset($enable_interactions)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $enable_interactions_meta = get_post_meta($model_id, '_explorexr_enable_interactions', true) ?: 'on';
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $enable_interactions = ($enable_interactions_meta === 'on');
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
if (!isset($auto_rotate)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $auto_rotate_meta = get_post_meta($model_id, '_explorexr_auto_rotate', true) ?: '';
    if ($auto_rotate_meta === '') {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
        $auto_rotate = false; // Default to disabled
        update_post_meta($model_id, '_explorexr_auto_rotate', 'off');
    } else {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
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
                <!-- Hidden state field to preserve checkbox state -->
                <input type="hidden" name="explorexr_enable_interactions_state" value="<?php echo esc_attr($enable_interactions ? '1' : '0'); ?>">
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
            <div id="auto-rotate-settings" class="explorexr-auto-rotate-settings<?php if (!$auto_rotate) echo ' explorexr-hidden'; ?>">
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
                <div class="explorexr-form-group explorexr-form-group-indented-top">
                    <label for="explorexr_auto_rotate_delay">Auto-rotate Delay</label>
                    <input type="text" name="explorexr_auto_rotate_delay" id="explorexr_auto_rotate_delay" value="<?php echo esc_attr($auto_rotate_delay); ?>" class="small-text">
                    <p class="description">Delay before auto-rotate starts (milliseconds)</p>
                </div>
                
                <div class="explorexr-form-group explorexr-form-group-indented">
                    <label for="explorexr_auto_rotate_speed">Auto-rotate Speed</label>
                    <input type="text" name="explorexr_auto_rotate_speed" id="explorexr_auto_rotate_speed" value="<?php echo esc_attr($auto_rotate_speed); ?>" class="small-text">
                    <p class="description">Speed of auto-rotation (e.g., 30deg, 45deg, 60deg)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// WordPress.org compliance: Convert inline script to wp_add_inline_script
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for inline script
$viewer_controls_script = '
jQuery(document).ready(function($) {
    // Update state field when enable interactions checkbox changes
    $("#explorexr_enable_interactions").on("change", function() {
        var stateValue = $(this).is(":checked") ? "1" : "0";
        $("input[name=\"explorexr_enable_interactions_state\"]").val(stateValue);
    });
    
    // Toggle auto-rotate settings visibility
    $("#explorexr_auto_rotate").on("change", function() {
        if ($(this).is(":checked")) {
            $("#auto-rotate-settings").slideDown();
            // Ensure the input fields are enabled
            $("#explorexr_auto_rotate_delay, #explorexr_auto_rotate_speed").prop("disabled", false);
        } else {
            $("#auto-rotate-settings").slideUp();
            // Optionally disable the fields when auto-rotate is off
            // This ensures they are not submitted with the form when auto-rotate is off
            // $("#explorexr_auto_rotate_delay, #explorexr_auto_rotate_speed").prop("disabled", true);
        }
    });
    
    // Initialize fields on page load
    if (!$("#explorexr_auto_rotate").is(":checked")) {
        $("#auto-rotate-settings").hide();
    }
});
';

wp_add_inline_script('jquery', $viewer_controls_script);
?>





