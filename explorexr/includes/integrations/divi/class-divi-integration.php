<?php
/**
 * Divi Builder Integration for ExploreXR Premium
 *
 * Registers an ExploreXR 3D Model module in the Divi Builder so users
 * can drop a model into any page via the visual editor with live preview.
 *
 * @package ExploreXR
 * @since 1.1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ExploreXR_Divi_Integration
 */
class ExploreXR_Divi_Integration {

    /**
     * Initialise hooks.
     */
    public static function init() {
        add_action( 'et_builder_ready', array( __CLASS__, 'register_module' ) );

        // Enqueue assets in the Divi visual builder iframe.
        add_action( 'et_builder_frontend_enqueue_scripts', array( __CLASS__, 'builder_assets' ) );
    }

    /**
     * Load and register the Divi module.
     */
    public static function register_module() {
        if ( ! class_exists( 'ET_Builder_Module' ) ) {
            return;
        }
        require_once __DIR__ . '/class-divi-module.php';
    }

    /**
     * Enqueue frontend assets inside the Divi visual builder iframe.
     */
    public static function builder_assets() {
        wp_enqueue_style(
            'explorexr-model-viewer',
            EXPLOREXR_PREMIUM_PLUGIN_URL . 'assets/css/model-viewer.css',
            array(),
            EXPLOREXR_PREMIUM_VERSION
        );

        if ( ! wp_script_is( 'explorexr-model-loader', 'registered' ) ) {
            wp_register_script(
                'explorexr-model-loader',
                EXPLOREXR_PREMIUM_PLUGIN_URL . 'assets/js/model-loader.js',
                array( 'jquery' ),
                EXPLOREXR_PREMIUM_VERSION,
                true
            );
        }
    }
}
