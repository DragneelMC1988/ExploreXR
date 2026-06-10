<?php
/**
 * Gutenberg Block Integration for ExploreXR Premium
 *
 * Registers a Gutenberg block with a server-side render callback so the
 * 3D model previews live inside the block editor.
 *
 * @package ExploreXR
 * @since 1.1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ExploreXR_Gutenberg_Integration
 */
class ExploreXR_Gutenberg_Integration {

    /**
     * Initialise hooks.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_block' ) );
        add_action( 'save_post_explorexr_model', array( __CLASS__, 'clear_model_options_cache' ) );
        add_action( 'save_post_explorexr_premium_model', array( __CLASS__, 'clear_model_options_cache' ) );
        add_action( 'deleted_post', array( __CLASS__, 'clear_model_options_cache' ) );
    }

    /**
     * Register the Gutenberg block.
     */
    public static function register_block() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        // Register the editor script.
        wp_register_script(
            'explorexr-gutenberg-block',
            EXPLOREXR_PLUGIN_URL . 'includes/integrations/gutenberg/block.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-server-side-render', 'wp-data' ),
            EXPLOREXR_VERSION,
            false
        );

        // Pass model list to the editor script.
        $models  = self::get_model_options();
        wp_localize_script( 'explorexr-gutenberg-block', 'explorexrBlockData', array(
            'models' => $models,
        ) );

        // Register the editor style.
        wp_register_style(
            'explorexr-gutenberg-editor',
            EXPLOREXR_PLUGIN_URL . 'assets/css/elementor-editor.css',
            array(),
            EXPLOREXR_VERSION
        );

        register_block_type( 'explorexr/3d-model', array(
            'editor_script'   => 'explorexr-gutenberg-block',
            'editor_style'    => 'explorexr-gutenberg-editor',
            'render_callback' => array( __CLASS__, 'render_block' ),
            'attributes'      => array(
                'modelId' => array(
                    'type'    => 'string',
                    'default' => '',
                ),
            ),
        ) );
    }

    /**
     * Server-side render callback.
     *
     * @param array $attributes Block attributes.
     * @return string
     */
    public static function render_block( $attributes ) {
        $model_id = ! empty( $attributes['modelId'] ) ? absint( $attributes['modelId'] ) : 0;

        if ( ! $model_id ) {
            // In the editor context, show placeholder.
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                return '<div style="padding:40px;text-align:center;background:#f7f7f7;border:1px dashed #ccc;">'
                     . esc_html__( 'Select a 3D model from the block settings.', 'explorexr' )
                     . '</div>';
            }
            return '';
        }

        return do_shortcode( '[explorexr_model id="' . $model_id . '"]' );
    }

    /**
     * Build the model list for the block editor.
     *
     * @return array Array of { value, label } objects.
     */
    private static function get_model_options() {
        $cached_options = get_transient( 'explorexr_block_model_list' );
        if ( false !== $cached_options && is_array( $cached_options ) ) {
            return $cached_options;
        }

        $options = array(
            array(
                'value' => '',
                'label' => esc_html__( '— Select a model —', 'explorexr' ),
            ),
        );

        $models = get_posts( array(
            'post_type'      => array( 'explorexr_model', 'explorexr_premium_model' ),
            'posts_per_page' => 100,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        foreach ( $models as $model ) {
            $options[] = array(
                'value' => (string) $model->ID,
                'label' => $model->post_title,
            );
        }

        set_transient( 'explorexr_block_model_list', $options, HOUR_IN_SECONDS );

        return $options;
    }

    /**
     * Clear the cached model list used by the block editor.
     *
     * @return void
     */
    public static function clear_model_options_cache() {
        delete_transient( 'explorexr_block_model_list' );
    }
}

