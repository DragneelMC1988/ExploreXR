<?php
/**
 * Divi Builder Module: ExploreXR 3D Model
 *
 * @package ExploreXR
 * @since 1.1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ExploreXR_Divi_Module
 */
class ExploreXR_Divi_Module extends ET_Builder_Module {

    public $slug       = 'explorexr_3d_model';
    public $vb_support = 'on';

    /**
     * Module initialisation.
     */
    public function init() {
        $this->name = esc_html__( 'ExploreXR 3D Model', 'explorexr' );
        $this->icon = 'E';

        $this->settings_modal_toggles = array(
            'general' => array(
                'toggles' => array(
                    'main_content' => esc_html__( '3D Model', 'explorexr' ),
                ),
            ),
        );
    }

    /**
     * Define module fields.
     *
     * @return array
     */
    public function get_fields() {
        $models  = $this->get_model_options();

        return array(
            'model_id' => array(
                'label'           => esc_html__( 'Select Model', 'explorexr' ),
                'type'            => 'select',
                'options'         => $models,
                'default'         => '',
                'toggle_slug'     => 'main_content',
                'description'     => esc_html__( 'Choose an ExploreXR 3D model to display.', 'explorexr' ),
                'computed_affects' => array( '__model_html' ),
            ),
            '__model_html' => array(
                'type'                => 'computed',
                'computed_callback'   => array( __CLASS__, 'compute_model_html' ),
                'computed_depends_on' => array( 'model_id' ),
            ),
        );
    }

    /**
     * Build the model options array.
     *
     * @return array
     */
    private function get_model_options() {
        $options = array( '' => esc_html__( '— Select a model —', 'explorexr' ) );

        $models = get_posts( array(
            'post_type'      => array( 'explorexr_model', 'explorexr_premium_model' ),
            'posts_per_page' => 100,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        foreach ( $models as $model ) {
            $options[ (string) $model->ID ] = $model->post_title;
        }

        return $options;
    }

    /**
     * Computed callback for Visual Builder AJAX render.
     *
     * @param array $args Module attributes.
     * @return string HTML output.
     */
    public static function compute_model_html( $args = array() ) {
        $model_id = ! empty( $args['model_id'] ) ? absint( $args['model_id'] ) : 0;

        if ( ! $model_id ) {
            return '<div style="padding:40px;text-align:center;background:#f7f7f7;border:1px dashed #ccc;">'
                 . esc_html__( 'Select a 3D model from the module settings.', 'explorexr' )
                 . '</div>';
        }

        return do_shortcode( '[explorexr_model id="' . $model_id . '"]' );
    }

    /**
     * Render the module on the frontend (standard Divi render).
     *
     * @param array  $attrs       Module attributes.
     * @param string $content     Module content (unused).
     * @param string $render_slug Module slug.
     * @return string
     */
    public function render( $attrs, $content, $render_slug ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
        $model_id = ! empty( $this->props['model_id'] ) ? absint( $this->props['model_id'] ) : 0;

        if ( ! $model_id ) {
            return '<div style="padding:40px;text-align:center;background:#f7f7f7;border:1px dashed #ccc;">'
                 . esc_html__( 'Select a 3D model from the module settings.', 'explorexr' )
                 . '</div>';
        }

        return do_shortcode( '[explorexr_model id="' . $model_id . '"]' );
    }
}

new ExploreXR_Divi_Module();
