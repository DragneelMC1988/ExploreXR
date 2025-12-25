<?php
/**
 * Poster Image Card Template
 * 
 * Handles poster image uploads and media library selection
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
        echo '<div class="notice notice-error"><p>Error: Model ID not provided to poster-image-card.php template.</p></div>';
        return;
    }
}

// Ensure required variables are defined
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
if (!isset($poster_url)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $poster_url = get_post_meta($model_id, '_explorexr_model_poster', true) ?: '';
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
if (!isset($poster_id)) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed via safe include
    $poster_id = get_post_meta($model_id, '_explorexr_model_poster_id', true) ?: '';
}
?>

<!-- Poster Image -->
<div class="explorexr-card">
    <div class="explorexr-card-header">
        <h2><span class="dashicons dashicons-format-image"></span> Poster Image</h2>
    </div>
    <div class="explorexr-card-content">
        <p class="description explorexr-card-note">A poster image is displayed while your 3D model loads. It's especially important for large models when using the "Show Poster with Load Button" option.</p>
        
        <div class="explorexr-tabs">
            <button type="button" class="explorexr-tab active" data-tab="upload-poster">Upload New Image</button>
            <button type="button" class="explorexr-tab" data-tab="library-poster">Media Library</button>
        </div>
        
        <div class="explorexr-tab-content active" id="upload-poster">
            <div class="explorexr-form-group">
                <input type="hidden" name="poster_method" value="upload" id="poster_method_input">
                <label for="model_poster">Select Image File</label>
                <input name="model_poster" type="file" id="model_poster" accept="image/*" />
                <p class="description">Accepted formats: JPG, PNG, GIF</p>
                
                <!-- Poster Preview for Upload Tab -->
                <?php if (!empty($poster_url)) : ?>
                <div class="explorexr-poster-preview">
                    <h4>Current Poster:</h4>
                    <?php 
                    if (!empty($poster_id)) {
                        echo wp_get_attachment_image($poster_id, 'medium', false, array('alt' => esc_attr__('Poster preview', 'explorexr')));
                    } else {
                        // Fallback for cases where we have URL but no attachment ID
                        // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Fallback for external URL posters
                        printf('<img src="%s" alt="%s" loading="lazy">', 
                            esc_url($poster_url), 
                            esc_attr__('Poster preview', 'explorexr')
                        );
                    }
                    ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="explorexr-tab-content" id="library-poster">
            <div class="explorexr-form-group">
                <input type="hidden" name="model_poster_id" id="model_poster_id" value="<?php echo esc_attr($poster_id); ?>">
                <div class="explorexr-input-group">
                    <input type="text" name="model_poster_url" id="model_poster_url" value="<?php echo esc_attr($poster_url); ?>" readonly placeholder="No image selected">
                    <button type="button" class="button" id="explorexr-select-poster">
                        <span class="dashicons dashicons-admin-media"></span> Select Image
                    </button>
                </div>                              <div id="explorexr-poster-preview" class="explorexr-poster-preview<?php echo empty($poster_url) ? ' explorexr-hidden' : ''; ?>">
                    <?php 
                    if (!empty($poster_id)) {
                        echo wp_get_attachment_image($poster_id, 'medium', false, array('alt' => esc_attr__('Poster preview', 'explorexr')));
                    } elseif (!empty($poster_url)) {
                        // Fallback for cases where we have URL but no attachment ID
                        // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Fallback for external URL posters
                        printf('<img src="%s" alt="%s" loading="lazy">', 
                            esc_url($poster_url), 
                            esc_attr__('Poster preview', 'explorexr')
                        );
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($poster_url)) : ?>
        <div class="explorexr-form-group">
            <label class="explorexr-checkbox-label">
                <input type="checkbox" name="remove_poster" value="1">
                <span>Remove current poster image</span>
            </label>
            <p class="description">Check this box to remove the current poster image.</p>
        </div>
        <?php endif; ?>
    </div>
</div>





