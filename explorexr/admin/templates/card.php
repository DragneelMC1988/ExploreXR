<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Card component with header, content, and optional footer -->
<div class="explorexr-card">    <div class="explorexr-card-header">
        <h2><?php echo esc_html($card_title); ?></h2>
        <?php if (!empty($card_icon)) : ?>
        <span class="dashicons dashicons-<?php echo esc_attr($card_icon); ?>"></span>
        <?php endif; ?>
        <?php if (!empty($card_actions)) : ?>
        <div class="card-header-actions">
            <?php echo wp_kses_post($card_actions); ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="explorexr-card-content">
        <?php 
        // Admin content - allow form elements and other HTML
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable for wp_kses configuration
        $allowed_html = wp_kses_allowed_html('post');
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable for wp_kses configuration
        $allowed_html['form'] = array(
            'action' => array(),
            'method' => array(),
            'id' => array(),
            'class' => array(),
            'enctype' => array(),
            'onsubmit' => array(),
        );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable for wp_kses configuration
        $allowed_html['input'] = array(
            'type' => array(),
            'name' => array(),
            'id' => array(),
            'value' => array(),
            'class' => array(),
            'min' => array(),
            'max' => array(),
            'checked' => array(),
            'readonly' => array(),
            'placeholder' => array(),
            'required' => array(),
            'accept' => array(),
        );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable for wp_kses configuration
        $allowed_html['select'] = array(
            'name' => array(),
            'id' => array(),
            'class' => array(),
        );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable for wp_kses configuration
        $allowed_html['option'] = array(
            'value' => array(),
            'selected' => array(),
        );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable for wp_kses configuration
        $allowed_html['textarea'] = array(
            'name' => array(),
            'id' => array(),
            'class' => array(),
            'rows' => array(),
            'cols' => array(),
        );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable for wp_kses configuration
        $allowed_html['fieldset'] = array(
            'class' => array(),
        );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable for wp_kses configuration
        $allowed_html['legend'] = array(
            'class' => array(),
        );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable for wp_kses configuration
        $allowed_html['button'] = array(
            'type' => array(),
            'class' => array(),
            'id' => array(),
            'data-delete-url' => array(),
            'data-model-id' => array(),
            'data-model-name' => array(),
            'data-shortcode' => array(),
            'title' => array(),
            'style' => array(),
        );
        
        echo wp_kses($card_content, $allowed_html);
        ?>
    </div>
    <?php if (!empty($card_footer)) : ?>
    <div class="explorexr-card-footer explorexr-card-footer-fullwidth">
        <?php echo wp_kses_post($card_footer); ?>
    </div>
    <?php endif; ?>
</div>





