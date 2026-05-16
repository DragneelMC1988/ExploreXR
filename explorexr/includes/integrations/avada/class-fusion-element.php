<?php
/**
 * ExploreXR – Avada Fusion Builder Element
 *
 * Registers a custom Fusion Builder element (Avada's page builder) so
 * editors can drag "3D Model (ExploreXR)" into any layout and see the live
 * 3D model in the Fusion Builder preview.
 *
 * Fusion Builder renders shortcodes server-side and streams the HTML back to
 * the browser preview, so the model-viewer element renders normally.
 *
 * @package ExploreXR
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Bootstrap: hook into Fusion Builder's element registration action.
 */
add_action( 'fusion_builder_before_init', 'explorexr_register_fusion_element' );

function explorexr_register_fusion_element() {
    // Guard: Fusion_Builder_Element base class must exist.
    if ( ! class_exists( 'Fusion_Builder_Element' ) ) {
        return;
    }

    /**
     * Class ExploreXR_Fusion_Element
     *
     * A Fusion Builder element that wraps the [explorexr_model] shortcode.
     */
    class ExploreXR_Fusion_Element extends Fusion_Builder_Element { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound

        /**
         * Element metadata.
         */
        public function __construct() {
            parent::__construct(
                'explorexr_3d_model',  // Shortcode tag used by Fusion Builder internally.
                esc_html__( '3D Model (ExploreXR)', 'explorexr' ),
                EXPLOREXR_PLUGIN_URL . 'assets/img/icons/Icon.png', // Element icon.
                [],   // Default attributes (see get_element_defaults).
                [],   // Additional args.
                'explorexr_fusion_element_render' // Render callback function.
            );
        }

        /**
         * Return default attribute values.
         *
         * @return array
         */
        public function get_element_defaults() {
            return [
                'model_id'        => '0',
                'model_id_manual' => '',
            ];
        }

        /**
         * Return the element's UI options for the Fusion Builder panel.
         *
         * @return array
         */
        public function get_options() {
            // Build select options from all published ExploreXR models.
            $model_posts   = get_posts( [
                'post_type'      => 'explorexr_model',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ] );

            $model_choices = [ '0' => esc_html__( '— Select a model —', 'explorexr' ) ];
            foreach ( $model_posts as $model ) {
                $model_choices[ $model->ID ] = sprintf( '%s (ID: %d)', $model->post_title, $model->ID );
            }

            return [
                [
                    'type'        => 'select',
                    'heading'     => esc_html__( '3D Model', 'explorexr' ),
                    'description' => esc_html__( 'Choose the 3D model to display.', 'explorexr' ),
                    'param_name'  => 'model_id',
                    'value'       => $model_choices,
                    'default'     => '0',
                ],
                [
                    'type'        => 'textfield',
                    'heading'     => esc_html__( 'Or enter Model ID manually', 'explorexr' ),
                    'description' => esc_html__( 'Overrides the dropdown above. Find the ID in ExploreXR → Browse Models.', 'explorexr' ),
                    'param_name'  => 'model_id_manual',
                    'value'       => '',
                ],
            ];
        }
    }

    new ExploreXR_Fusion_Element(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
}

/**
 * Render callback for the Fusion Builder element.
 *
 * @param array  $args    Element attributes.
 * @param string $content Inner content (unused for this element).
 * @return string Rendered HTML.
 */
function explorexr_fusion_element_render( $args, $content = '' ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
    $model_id = ! empty( $args['model_id_manual'] ) && ctype_digit( trim( $args['model_id_manual'] ) )
        ? intval( $args['model_id_manual'] )
        : intval( $args['model_id'] ?? 0 );

    if ( ! $model_id ) {
        return '<p style="padding:20px;background:#f0f0f0;text-align:center;">'
             . esc_html__( 'ExploreXR: Please select a 3D model in the element settings.', 'explorexr' )
             . '</p>';
    }

    $post = get_post( $model_id );
    if ( ! $post || $post->post_type !== 'explorexr_model' || $post->post_status !== 'publish' ) {
        return '<p style="padding:20px;background:#f0f0f0;text-align:center;">'
             . sprintf(
                 /* translators: %d: model ID */
                 esc_html__( 'ExploreXR: Model ID %d not found or not published.', 'explorexr' ),
                 $model_id
             )
             . '</p>';
    }

    return do_shortcode( '[explorexr_model id="' . $model_id . '"]' );
}
