<?php
/**
 * Page Builder Integrations Loader
 *
 * Conditionally loads integration classes for detected page builders.
 * Each integration is only loaded when its corresponding page builder
 * plugin is active, avoiding unnecessary file includes.
 *
 * @package ExploreXR
 * @since 1.1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ExploreXR_Page_Builder_Loader
 */
class ExploreXR_Page_Builder_Loader {

    /**
     * Bootstrap all page builder integrations.
     *
     * Called from the main plugin init at plugins_loaded priority 5.
     */
    public static function init() {
        // Elementor — check for the core plugin class.
        if ( did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' ) ) {
            require_once __DIR__ . '/elementor/class-elementor-integration.php';
            ExploreXR_Elementor_Integration::init();
        } else {
            // Elementor may not be loaded yet at priority 5 — hook later.
            add_action( 'elementor/loaded', array( __CLASS__, 'load_elementor' ) );
        }

        // Divi — check for the builder framework class.
        if ( class_exists( 'ET_Builder_Module' ) || function_exists( 'et_setup_theme' ) ) {
            require_once __DIR__ . '/divi/class-divi-integration.php';
            ExploreXR_Divi_Integration::init();
        } else {
            // Divi theme may load later — hook into after_setup_theme.
            add_action( 'after_setup_theme', array( __CLASS__, 'load_divi' ) );
        }

        // Beaver Builder — check for the builder class.
        if ( class_exists( 'FLBuilder' ) ) {
            require_once __DIR__ . '/beaver-builder/class-beaver-builder-integration.php';
            ExploreXR_Beaver_Builder_Integration::init();
        } else {
            add_action( 'init', array( __CLASS__, 'load_beaver_builder' ), 11 );
        }

        // WPBakery (Visual Composer) — used by Avada and standalone.
        if ( defined( 'WPB_VC_VERSION' ) || function_exists( 'vc_map' ) ) {
            require_once __DIR__ . '/wpbakery/class-wpbakery-integration.php';
            ExploreXR_WPBakery_Integration::init();
        } else {
            add_action( 'vc_before_init', array( __CLASS__, 'load_wpbakery' ) );
        }

        // Gutenberg — always available on WordPress 5.0+.
        if ( function_exists( 'register_block_type' ) ) {
            require_once __DIR__ . '/gutenberg/class-gutenberg-integration.php';
            ExploreXR_Gutenberg_Integration::init();
        }
    }

    /**
     * Late-load Elementor integration.
     */
    public static function load_elementor() {
        if ( ! class_exists( 'ExploreXR_Elementor_Integration' ) ) {
            require_once __DIR__ . '/elementor/class-elementor-integration.php';
            ExploreXR_Elementor_Integration::init();
        }
    }

    /**
     * Late-load Divi integration.
     */
    public static function load_divi() {
        if ( function_exists( 'et_setup_theme' ) && ! class_exists( 'ExploreXR_Divi_Integration' ) ) {
            require_once __DIR__ . '/divi/class-divi-integration.php';
            ExploreXR_Divi_Integration::init();
        }
    }

    /**
     * Late-load Beaver Builder integration.
     */
    public static function load_beaver_builder() {
        if ( class_exists( 'FLBuilder' ) && ! class_exists( 'ExploreXR_Beaver_Builder_Integration' ) ) {
            require_once __DIR__ . '/beaver-builder/class-beaver-builder-integration.php';
            ExploreXR_Beaver_Builder_Integration::init();
        }
    }

    /**
     * Late-load WPBakery integration.
     */
    public static function load_wpbakery() {
        if ( ! class_exists( 'ExploreXR_WPBakery_Integration' ) ) {
            require_once __DIR__ . '/wpbakery/class-wpbakery-integration.php';
            ExploreXR_WPBakery_Integration::init();
        }
    }
}
