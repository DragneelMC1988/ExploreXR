<?php
/**
 * Basic Information Card Template
 * 
 * Form fields for model title, name, alt text, and description
 *
 * @package ExpoXR
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if model_id is defined, if not try to get it from $_GET
if (!isset($model_id) || empty($model_id)) {
    $model_id = isset($_GET['model_id']) ? intval($_GET['model_id']) : 0;
    if (!$model_id) {
        echo '<div class="notice notice-error"><p>Error: Model ID not provided to basic-information-card.php template.</p></div>';
        return;
    }
}

// Ensure required variables are defined
if (!isset($model_title)) {
    $model = get_post($model_id);
    $model_title = $model ? $model->post_title : '';
}

if (!isset($model_description)) {
    $model = isset($model) ? $model : get_post($model_id);
    $model_description = $model ? $model->post_content : '';
}

if (!isset($model_name)) {
    $model_name = get_post_meta($model_id, '_expoxr_model_name', true) ?: '';
}

if (!isset($model_alt_text)) {
    $model_alt_text = get_post_meta($model_id, '_expoxr_model_alt_text', true) ?: '';
}
?>

<!-- Basic Information -->
<div class="expoxr-card">
    <div class="expoxr-card-header">
        <h2><span class="dashicons dashicons-welcome-write-blog"></span> Basic Information</h2>
    </div>
    <div class="expoxr-card-content">
        <div class="expoxr-form-grid">
            <div class="expoxr-form-group">
                <label for="model_title">Model Title <span class="required">*</span></label>
                <input name="model_title" type="text" id="model_title" class="regular-text" required value="<?php echo esc_attr($model_title); ?>" placeholder="Enter a descriptive title" />
            </div>
            
            <div class="expoxr-form-group">
                <label for="model_name">Display Name <span class="optional">(optional)</span></label>
                <input name="model_name" type="text" id="model_name" class="regular-text" value="<?php echo esc_attr($model_name); ?>" placeholder="Name shown in model lists" />
            </div>
            
            <div class="expoxr-form-group">
                <label for="model_alt_text">Alt Text <span class="optional">(optional)</span></label>
                <input name="model_alt_text" type="text" id="model_alt_text" class="regular-text" value="<?php echo esc_attr($model_alt_text); ?>" placeholder="Alt text for accessibility" />
            </div>                          
            
            <div class="expoxr-form-group expoxr-full-width">
                <label for="model_description">Description <span class="optional">(optional)</span></label>
                <textarea name="model_description" id="model_description" rows="3" placeholder="Add a description for this 3D model"><?php echo esc_textarea($model_description); ?></textarea>
            </div>
        </div>
    </div>
</div>





