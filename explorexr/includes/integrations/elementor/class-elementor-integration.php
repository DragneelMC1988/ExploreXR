<?php
/**
 * Elementor Integration for ExploreXR Premium
 *
 * Registers the ExploreXR 3D Model widget in Elementor and ensures
 * model-viewer assets load correctly in the Elementor editor iframe.
 *
 * @package ExploreXR
 * @since 1.1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ExploreXR_Elementor_Integration
 *
 * Bootstraps the ExploreXR widget into Elementor.
 */
class ExploreXR_Elementor_Integration {

    /**
     * Initialise hooks.
     */
    public static function init() {
        // Register the widget category and widget.
        add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widget' ) );

        // Enqueue editor-specific styles.
        add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'editor_styles' ) );

        // Ensure frontend assets are available in the preview iframe.
        add_action( 'elementor/preview/enqueue_scripts', array( __CLASS__, 'preview_assets' ) );

        // Register a dedicated widget category.
        add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
    }

    /**
     * Register the ExploreXR widget category.
     *
     * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
     */
    public static function register_category( $elements_manager ) {
        $elements_manager->add_category(
            'explorexr',
            array(
                'title' => esc_html__( 'ExploreXR 3D', 'explorexr' ),
                'icon'  => 'eicon-plug',
            )
        );
    }

    /**
     * Register the widget with Elementor.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     */
    public static function register_widget( $widgets_manager ) {
        require_once __DIR__ . '/class-elementor-widget.php';
        $widgets_manager->register( new ExploreXR_Elementor_Widget() );
    }

    /**
     * Enqueue Elementor editor panel styles.
     */
    public static function editor_styles() {
        wp_enqueue_style(
            'explorexr-elementor-editor',
            EXPLOREXR_PLUGIN_URL . 'assets/css/elementor-editor.css',
            array(),
            EXPLOREXR_VERSION
        );
    }

    /**
     * Enqueue scripts/styles in the Elementor preview iframe so the
     * shortcode output (which includes <model-viewer>) renders correctly.
     */
    public static function preview_assets() {
        // Core model-viewer CSS.
        wp_enqueue_style(
            'explorexr-model-viewer',
            EXPLOREXR_PLUGIN_URL . 'assets/css/model-viewer.css',
            array(),
            EXPLOREXR_VERSION
        );

        // Register the model-loader so the shortcode can enqueue it.
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
