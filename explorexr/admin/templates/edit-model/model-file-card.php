<?php
/**
 * 3D Model File Upload Card Template
 * 
 * Handles model file uploads and existing model selection
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
        echo '<div class="notice notice-error"><p>Error: Model ID not provided to model-file-card.php template.</p></div>';
        return;
    }
}

// Ensure required variables are defined
if (!isset($model_file)) {
    $model_file = get_post_meta($model_id, '_expoxr_model_file', true) ?: '';
}

if (!isset($existing_models)) {
    $existing_models = array();
    if (function_exists('expoxr_get_model_files_from_directory')) {
        $uploaded_files = expoxr_get_model_files_from_directory();
        foreach ($uploaded_files as $file) {
            $existing_models[$file['url']] = $file['name'];
        }
    }
}

if (!isset($poster_url)) {
    $poster_url = get_post_meta($model_id, '_expoxr_model_poster', true) ?: '';
}

if (!isset($poster_id)) {
    $poster_id = get_post_meta($model_id, '_expoxr_model_poster_id', true) ?: '';
}
?>

<!-- 3D Model File -->
<div class="expoxr-card">
    <div class="expoxr-card-header">
        <h2><span class="dashicons dashicons-media-default"></span> 3D Model File</h2>
    </div>
    <div class="expoxr-card-content">
        <?php if (!empty($model_file)) : ?>
        <div class="expoxr-current-model">
            <h3>Current Model File:</h3>
            <div class="expoxr-current-model-info">
                <div class="expoxr-model-file-info">
                    <span class="dashicons dashicons-media-code"></span>
                    <span><?php echo esc_html(basename($model_file)); ?></span>
                </div>
                <div class="expoxr-model-file-path">
                    <code><?php echo esc_html($model_file); ?></code>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="expoxr-tabs">
            <button type="button" class="expoxr-tab active" data-tab="upload-model">Upload New Model</button>
            <?php if (!empty($existing_models)) : ?>
            <button type="button" class="expoxr-tab" data-tab="existing-model">Use Existing Model</button>
            <?php endif; ?>
        </div>
        
        <div class="expoxr-tab-content active" id="upload-model">
            <div class="expoxr-form-group">
                <input type="hidden" name="model_source" value="upload" id="model_source_input">
                <label for="model_file">Select Model File</label>
                <input name="model_file" type="file" id="model_file" accept=".glb,.gltf,.usdz" />
                <p class="description">Accepted formats: GLB, GLTF, USDZ (Max size: 50MB)</p>
            </div>
        </div>
        
        <?php if (!empty($existing_models)) : ?>
        <div class="expoxr-tab-content" id="existing-model">
            <div class="expoxr-form-group">
                <label for="existing_model">Select Existing Model</label>
                <select name="existing_model" id="existing_model" class="regular-text">
                    <?php foreach ($existing_models as $file_url => $file_name) : ?>
                        <option value="<?php echo esc_attr($file_url); ?>" <?php selected($model_file, $file_url); ?>><?php echo esc_html($file_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description">Choose from models you've already uploaded to your site.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>





