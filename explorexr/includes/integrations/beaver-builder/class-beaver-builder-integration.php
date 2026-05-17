<?php
/**
 * Beaver Builder Integration for ExploreXR Premium
 *
 * Registers an ExploreXR 3D Model module in Beaver Builder.
 *
 * @package ExploreXR
 * @since 1.1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ExploreXR_Beaver_Builder_Integration
 */
class ExploreXR_Beaver_Builder_Integration {

    /**
     * Initialise hooks.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_module' ), 20 );
    }

    /**
     * Register the Beaver Builder module.
     */
    public static function register_module() {
        if ( ! class_exists( 'FLBuilder' ) ) {
            return;
        }

        require_once __DIR__ . '/class-beaver-builder-module.php';

        FLBuilder::register_module( 'ExploreXR_Beaver_Builder_Module', array(
            'general' => array(
                'title'    => esc_html__( '3D Model', 'explorexr' ),
                'sections' => array(
                    'model_section' => array(
                        'title'  => esc_html__( 'Model Settings', 'explorexr' ),
                        'fields' => array(
                            'model_id' => array(
                                'type'    => 'select',
                                'label'   => esc_html__( 'Select Model', 'explorexr' ),
                                'options' => self::get_model_options(),
                                'preview' => array( 'type' => 'refresh' ),
                            ),
                        ),
                    ),
                ),
            ),
        ) );
    }

    /**
     * Build model options for the select field.
     *
     * @return array
     */
    private static function get_model_options() {
        $options = array( '' => esc_html__( '— Select a model —', 'explorexr' ) );

        $models = get_posts( array(
            'post_type'      => array( 'explorexr_model', 'explorexr_premium_model' ),
            'posts_per_page' => 100,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        foreach ( $models as $model ) {
            $options[ $model->ID ] = $model->post_title;
        }

        return $options;
    }
}
