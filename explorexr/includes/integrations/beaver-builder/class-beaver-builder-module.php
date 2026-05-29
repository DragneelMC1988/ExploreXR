<?php
/**
 * Beaver Builder Module: ExploreXR 3D Model
 *
 * @package ExploreXR
 * @since 1.1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ExploreXR_Beaver_Builder_Module
 */
class ExploreXR_Beaver_Builder_Module extends FLBuilderModule {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( array(
            'name'          => esc_html__( 'ExploreXR 3D Model', 'explorexr' ),
            'description'   => esc_html__( 'Display an interactive 3D model.', 'explorexr' ),
            'group'         => esc_html__( 'ExploreXR', 'explorexr' ),
            'category'      => esc_html__( 'Media', 'explorexr' ),
            'icon'          => 'format-gallery.svg',
            'dir'           => __DIR__ . '/',
            'url'           => EXPLOREXR_PLUGIN_URL . 'includes/integrations/beaver-builder/',
        ) );
    }

    /**
     * Enqueue assets needed by this module.
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'explorexr-model-viewer',
            EXPLOREXR_PLUGIN_URL . 'assets/css/model-viewer.css',
            array(),
            EXPLOREXR_VERSION
        );

        if ( ! wp_script_is( 'explorexr-model-loader', 'registered' ) ) {
            wp_register_script(
                'explorexr-model-loader',
                EXPLOREXR_PLUGIN_URL . 'assets/js/model-loader.js',
                array( 'jquery' ),
                EXPLOREXR_VERSION,
                true
            );
        }
    }
}
