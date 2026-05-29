<?php
/**
 * WPBakery (Visual Composer) Integration for ExploreXR Premium
 *
 * Maps the existing [explorexr_model] shortcode to WPBakery's visual editor
 * so it appears as a drag-and-drop element. Avada uses WPBakery internally,
 * so this integration covers both.
 *
 * @package ExploreXR
 * @since 1.1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ExploreXR_WPBakery_Integration
 */
class ExploreXR_WPBakery_Integration {

    /**
     * Initialise hooks.
     */
    public static function init() {
        add_action( 'vc_before_init', array( __CLASS__, 'map_shortcode' ) );
    }

    /**
     * Map the shortcode to WPBakery so it shows in the element picker.
     */
    public static function map_shortcode() {
        if ( ! function_exists( 'vc_map' ) ) {
            return;
        }

        $models = self::get_model_options();

        vc_map( array(
            'name'        => esc_html__( 'ExploreXR 3D Model', 'explorexr' ),
            'base'        => 'explorexr_model',
            'icon'        => EXPLOREXR_PLUGIN_URL . 'assets/css/elementor-editor.css', // fallback icon
            'category'    => esc_html__( 'ExploreXR', 'explorexr' ),
            'description' => esc_html__( 'Display an interactive 3D model.', 'explorexr' ),
            'params'      => array(
                array(
                    'type'        => 'dropdown',
                    'heading'     => esc_html__( 'Select Model', 'explorexr' ),
                    'param_name'  => 'id',
                    'value'       => $models,
                    'description' => esc_html__( 'Choose an ExploreXR 3D model to display.', 'explorexr' ),
                    'admin_label' => true,
                ),
            ),
        ) );
    }

    /**
     * Build model options — WPBakery uses value => label (inverted).
     *
     * @return array
     */
    private static function get_model_options() {
        $options = array( esc_html__( '— Select a model —', 'explorexr' ) => '' );

        $models = get_posts( array(
            'post_type'      => array( 'explorexr_model', 'explorexr_premium_model' ),
            'posts_per_page' => 100,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        foreach ( $models as $model ) {
            $options[ $model->post_title ] = (string) $model->ID;
        }

        return $options;
    }
}
