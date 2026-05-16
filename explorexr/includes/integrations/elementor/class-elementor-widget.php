<?php
/**
 * ExploreXR – Elementor Widget
 *
 * Registers a native Elementor widget that renders a 3D model using the
 * [explorexr_model] shortcode.  Because Elementor's preview pane is a full
 * frontend iframe, the model-viewer custom element is loaded normally and the
 * 3D model appears live inside the Elementor editor.
 *
 * @package ExploreXR
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the widget once Elementor's widget manager is ready.
 */
add_action( 'elementor/widgets/register', 'explorexr_register_elementor_widget' );

function explorexr_register_elementor_widget( $widgets_manager ) {
    // Guard: only load if the base class exists.
    if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
        return;
    }

    require_once __DIR__ . '/class-elementor-widget-inner.php';
    $widgets_manager->register( new \ExploreXR\Integrations\Elementor\Widget_3D_Model() );
}
