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
        add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'dequeue_content_assets_from_editor_shell' ), PHP_INT_MAX );
        add_action( 'admin_print_scripts', array( __CLASS__, 'dequeue_content_assets_from_editor_shell' ), PHP_INT_MAX );
        add_action( 'admin_print_styles', array( __CLASS__, 'dequeue_content_assets_from_editor_shell' ), PHP_INT_MAX );
        add_action( 'save_post_explorexr_model', array( __CLASS__, 'clear_model_options_cache' ) );
        add_action( 'save_post_explorexr_premium_model', array( __CLASS__, 'clear_model_options_cache' ) );
        add_action( 'deleted_post', array( __CLASS__, 'clear_model_options_cache' ) );
    }

    /**
     * Keep content-only assets in the WordPress 7.1 editor iframe.
     *
     * WordPress builds the iframe with a separate asset queue after this hook,
     * so dequeuing here removes the duplicate editor-shell copy only.
     */
    public static function dequeue_content_assets_from_editor_shell() {
        global $wp_version;

        if ( version_compare( $wp_version, '7.1', '<' ) ) {
            return;
        }

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( $screen && method_exists( $screen, 'is_block_editor' ) && ! $screen->is_block_editor() ) {
            return;
        }

        wp_dequeue_script( 'model-viewer-script' );
        wp_dequeue_style( 'explorexr-model-viewer' );
    }

    /**
     * Register the Gutenberg block.
     */
    public static function register_block() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        explorexr_free_register_frontend_assets();

        // Register the editor script.
        wp_register_script(
            'explorexr-gutenberg-block',
            EXPLOREXR_PLUGIN_URL . 'includes/integrations/gutenberg/block.js',
            array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-data' ),
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

        $block_args = array(
            'api_version'     => 3,
            'script'          => 'model-viewer-script',
            'style'           => 'explorexr-model-viewer',
            'editor_script'   => 'explorexr-gutenberg-block',
            'editor_style'    => 'explorexr-gutenberg-editor',
            'render_callback' => array( __CLASS__, 'render_block' ),
            'attributes'      => array(
                'modelId' => array(
                    'type'    => 'string',
                    'default' => '',
                ),
            ),
        );

        // Retain the historical server-side name for integrations that query it.
        register_block_type( 'explorexr/3d-model', $block_args );

        // WordPress block names must start with a letter after the namespace.
        register_block_type( 'explorexr/model-3d', $block_args );
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
