<?php
/**
 * ExploreXR – Page Builder Integrations Bootstrap
 *
 * Loads the appropriate page-builder integration only when the
 * corresponding builder is active, keeping the free version lean.
 *
 * Supported builders:
 *  - Elementor  (widget via elementor/widgets/register)
 *  - Divi       (module via et_builder_ready)
 *  - Avada Fusion Builder (element via fusion_builder_before_init)
 *
 * @package ExploreXR
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ---- Elementor ---------------------------------------------------------- */
// Load only when Elementor is active; the file itself hooks to
// 'elementor/widgets/register' so there is no risk of early class loading.
if ( did_action( 'elementor/loaded' ) || function_exists( 'elementor_load_plugin_textdomain' ) ) {
    $explorexr_elementor_widget = EXPLOREXR_PLUGIN_DIR . 'includes/integrations/elementor/class-elementor-widget.php';
    if ( file_exists( $explorexr_elementor_widget ) ) {
        require_once $explorexr_elementor_widget;
    }
} else {
    // Defer until plugins_loaded in case Elementor loads after us.
    add_action( 'plugins_loaded', function () {
        if ( did_action( 'elementor/loaded' ) || function_exists( 'elementor_load_plugin_textdomain' ) ) {
            $explorexr_elementor_widget = EXPLOREXR_PLUGIN_DIR . 'includes/integrations/elementor/class-elementor-widget.php';
            if ( file_exists( $explorexr_elementor_widget ) ) {
                require_once $explorexr_elementor_widget;
            }
        }
    }, 20 );
}

/* ---- Divi Builder ------------------------------------------------------- */
// Divi sets 'ET_BUILDER_PLUGIN_ACTIVE' or defines the ET_Builder_Module class.
// We load on plugins_loaded (priority 20) so Divi has had time to initialise.
add_action( 'plugins_loaded', function () {
    if (
        defined( 'ET_BUILDER_PLUGIN_ACTIVE' ) ||
        defined( 'ET_CORE_VERSION' ) ||
        class_exists( 'ET_Builder_Module' )
    ) {
        $divi_module = EXPLOREXR_PLUGIN_DIR . 'includes/integrations/divi/class-divi-module.php';
        if ( file_exists( $divi_module ) ) {
            require_once $divi_module;
        }
    }
}, 20 );

/* ---- Avada Fusion Builder ----------------------------------------------- */
// Fusion Builder defines the 'FusionBuilder' class or the
// 'fusion_builder_before_init' action.
add_action( 'plugins_loaded', function () {
    if (
        class_exists( 'FusionBuilder' ) ||
        class_exists( 'Fusion_Builder_Element' ) ||
        defined( 'FUSION_BUILDER_VERSION' )
    ) {
        $avada_element = EXPLOREXR_PLUGIN_DIR . 'includes/integrations/avada/class-fusion-element.php';
        if ( file_exists( $avada_element ) ) {
            require_once $avada_element;
        }
    }
}, 20 );
