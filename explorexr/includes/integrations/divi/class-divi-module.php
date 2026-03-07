<?php
/**
 * ExploreXR – Divi Builder Module
 *
 * Registers a custom Divi Builder module so editors can search for
 * "3D Model" in the Divi module list, pick a model from a dropdown and
 * see it rendered live in the Divi Visual Builder.
 *
 * Divi Builder renders shortcodes in its frontend Visual Builder preview,
 * so the model-viewer custom element works without any extra workaround.
 *
 * @package ExploreXR
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Bootstrap: hook into Divi's module init action.
 */
add_action( 'et_builder_ready', 'explorexr_register_divi_module' );

function explorexr_register_divi_module() {
    // Guard: only load if ET_Builder_Module exists (Divi / Extra / Divi Builder plugin).
    if ( ! class_exists( 'ET_Builder_Module' ) ) {
        return;
    }

    /**
     * Class ExploreXR_Divi_Module
     *
     * A Divi Builder module that wraps the [explorexr_model] shortcode.
     */
    class ExploreXR_Divi_Module extends ET_Builder_Module { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound

        /** @var string Unique module slug. */
        public $slug       = 'explorexr_3d_model';

        /** @var string Visual Builder render method. */
        public $vb_support = 'on';

        /**
         * Module metadata.
         */
        public function init() {
            $this->name             = esc_html__( '3D Model (ExploreXR)', 'explorexr' );
            $this->icon_path        = EXPLOREXR_PLUGIN_DIR . 'assets/img/icons/Icon.png';
            $this->main_css_element = '%%order_class%%';
        }

        /**
         * Module fields (controls shown in the Divi editor sidebar).
         *
         * @return array
         */
        public function get_fields() {
            // Build select options from all published ExploreXR models.
            $model_posts   = get_posts( [
                'post_type'      => 'explorexr_model',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ] );

            $model_options = [ '0' => esc_html__( '— Select a model —', 'explorexr' ) ];
            foreach ( $model_posts as $model ) {
                $model_options[ $model->ID ] = sprintf( '%s (ID: %d)', $model->post_title, $model->ID );
            }

            return [
                'model_id' => [
                    'label'           => esc_html__( '3D Model', 'explorexr' ),
                    'type'            => 'select',
                    'option_category' => 'basic_option',
                    'options'         => $model_options,
                    'default'         => '0',
                    'description'     => esc_html__( 'Choose the 3D model to display. Manage models under ExploreXR → Browse Models.', 'explorexr' ),
                ],
                'model_id_manual' => [
                    'label'           => esc_html__( 'Or enter Model ID manually', 'explorexr' ),
                    'type'            => 'text',
                    'option_category' => 'basic_option',
                    'default'         => '',
                    'description'     => esc_html__( 'Overrides the dropdown. Find the ID in ExploreXR → Browse Models.', 'explorexr' ),
                ],
            ];
        }

        /**
         * Render the module's HTML output.
         *
         * @param array  $attrs   Module attributes.
         * @param string $content Module inner content (unused).
         * @param string $render_slug The module slug being rendered.
         * @return string HTML output.
         */
        public function render( $attrs, $content, $render_slug ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
            $model_id = ! empty( $this->props['model_id_manual'] ) && ctype_digit( trim( $this->props['model_id_manual'] ) )
                ? intval( $this->props['model_id_manual'] )
                : intval( $this->props['model_id'] ?? 0 );

            if ( ! $model_id ) {
                return '<p style="padding:20px;background:#f0f0f0;text-align:center;">'
                     . esc_html__( 'ExploreXR: Please select a 3D model in the module settings.', 'explorexr' )
                     . '</p>';
            }

            // Verify the post.
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
    }

    new ExploreXR_Divi_Module(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
}
