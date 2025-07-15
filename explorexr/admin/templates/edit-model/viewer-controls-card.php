<?php
/**
 * Viewer Controls Card Template
 * 
 * Basic controls like camera controls and auto-rotate
 *
 * @package ExpoXR
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
        echo '<div class="notice notice-error"><p>Error: Model ID not provided to viewer-controls-card.php template.</p></div>';
        return;
    }
}

// Ensure interactions and auto_rotate variables are defined
if (!isset($enable_interactions)) {
    // Same pattern as auto_rotate
    $enable_interactions = get_post_meta($model_id, '_expoxr_enable_interactions', true) === 'on';
    
    // For backward compatibility, if enable_interactions is not set, check legacy fields
    if (get_post_meta($model_id, '_expoxr_enable_interactions', true) === '') {
        $interactions_disabled = get_post_meta($model_id, '_expoxr_disable_interactions', true) === 'on';
        $camera_controls_legacy = get_post_meta($model_id, '_expoxr_camera_controls', true);
        
        if ($interactions_disabled) {
            $enable_interactions = false;
        } else if ($camera_controls_legacy === 'off') {
            $enable_interactions = false;
        } else {
            $enable_interactions = true; // Default is enabled
        }
        
        // Save the migrated value for future use
        update_post_meta($model_id, '_expoxr_enable_interactions', $enable_interactions ? 'on' : 'off');
    }
}

if (!isset($auto_rotate)) {
    $auto_rotate = get_post_meta($model_id, '_expoxr_auto_rotate', true) === 'on';
}
?>

<!-- Viewer Controls -->
<div class="expoxr-card">
    <div class="expoxr-card-header">
        <h2><span class="dashicons dashicons-admin-generic"></span> Viewer Controls</h2>
    </div>
    <div class="expoxr-card-content">
        <div class="expoxr-form-grid">
            <div class="expoxr-form-group">
                <label class="expoxr-checkbox-label">
                    <input type="checkbox" name="expoxr_enable_interactions" id="expoxr_enable_interactions" <?php checked($enable_interactions, true); ?>>
                    <span>Enable interactions</span>
                </label>
                <!-- Hidden state field to preserve checkbox state -->
                <input type="hidden" name="expoxr_enable_interactions_state" value="<?php echo esc_attr($enable_interactions ? '1' : '0'); ?>">
                <p class="description">Allow user interactions (rotate, zoom, pan). Models have interactions enabled by default.</p>
            </div>
        </div>
        
        <!-- Auto-rotate Section (separate from grid to stack vertically) -->
        <div class="expoxr-form-group">
            <label class="expoxr-checkbox-label">
                <input type="checkbox" name="expoxr_auto_rotate" id="expoxr_auto_rotate" <?php checked($auto_rotate, true); ?>>
                <span>Auto-rotate model</span>
            </label>
            <p class="description">Automatically rotate the model when the page loads.</p>
            
            <!-- Basic Auto-rotate Settings (shown when auto-rotate is enabled) -->
            <div id="auto-rotate-settings" <?php if (!$auto_rotate) echo 'style="display: none;"'; ?>>
                <?php 
                // Get auto-rotate delay with default value if not set
                $auto_rotate_delay = get_post_meta($model_id, '_expoxr_auto_rotate_delay', true);
                if (empty($auto_rotate_delay)) {
                    $auto_rotate_delay = '5000';
                }
                
                // Get rotation speed with default value if not set
                $auto_rotate_speed = get_post_meta($model_id, '_expoxr_rotation_per_second', true);
                if (empty($auto_rotate_speed)) {
                    $auto_rotate_speed = '30deg';
                }
                ?>
                <div class="expoxr-form-group" style="margin-top: 15px; margin-left: 20px;">
                    <label for="expoxr_auto_rotate_delay">Auto-rotate Delay</label>
                    <input type="text" name="expoxr_auto_rotate_delay" id="expoxr_auto_rotate_delay" value="<?php echo esc_attr($auto_rotate_delay); ?>" class="small-text">
                    <p class="description">Delay before auto-rotate starts (milliseconds)</p>
                </div>
                
                <div class="expoxr-form-group" style="margin-left: 20px;">
                    <label for="expoxr_auto_rotate_speed">Auto-rotate Speed</label>
                    <input type="text" name="expoxr_auto_rotate_speed" id="expoxr_auto_rotate_speed" value="<?php echo esc_attr($auto_rotate_speed); ?>" class="small-text">
                    <p class="description">Speed of auto-rotation (e.g., 30deg, 45deg, 60deg)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Update state field when enable interactions checkbox changes
    $('#expoxr_enable_interactions').on('change', function() {
        var stateValue = $(this).is(':checked') ? '1' : '0';
        $('input[name="expoxr_enable_interactions_state"]').val(stateValue);
    });
    
    // Toggle auto-rotate settings visibility and handle state
    $('#expoxr_auto_rotate').on('change', function() {
        if ($(this).is(':checked')) {
            $('#auto-rotate-settings').slideDown();
            // Ensure the input fields are enabled
            $('#expoxr_auto_rotate_delay, #expoxr_auto_rotate_speed').prop('disabled', false);
        } else {
            $('#auto-rotate-settings').slideUp();
            // Optionally disable the fields when auto-rotate is off
            // This ensures they're not submitted with the form when auto-rotate is off
            // $('#expoxr_auto_rotate_delay, #expoxr_auto_rotate_speed').prop('disabled', true);
        }
    });
    
    // Initialize fields on page load
    if (!$('#expoxr_auto_rotate').is(':checked')) {
        $('#auto-rotate-settings').hide();
    }
});
</script>





