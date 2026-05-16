<?php
/**
 * Template for displaying standard 3D models.
 *
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Available variables:
 * $attributes_html - HTML string of attributes for model-viewer
 * $model_attributes - Array of model attributes
 */
?>

<div class="ExploreXR-model-container">
    <model-viewer<?php echo wp_kses($attributes_html, 'post'); ?>>
    </model-viewer>
</div>
