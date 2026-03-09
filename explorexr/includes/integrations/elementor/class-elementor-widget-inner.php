<?php
/**
 * ExploreXR – Elementor Widget (inner class definition)
 *
 * @package ExploreXR
 */

namespace ExploreXR\Integrations\Elementor;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

/**
 * Class Widget_3D_Model
 *
 * Elementor widget that embeds a 3D model using the ExploreXR shortcode.
 * The model renders live in Elementor's preview iframe just like it does on
 * the published page.
 */
class Widget_3D_Model extends Widget_Base {

    /* ------------------------------------------------------------------ */
    /*  Identity                                                            */
    /* ------------------------------------------------------------------ */

    public function get_name() {
        return 'explorexr_3d_model';
    }

    public function get_title() {
        return esc_html__( '3D Model (ExploreXR)', 'explorexr' );
    }

    public function get_icon() {
        return 'eicon-3d-box';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_keywords() {
        return [ '3d', 'model', 'glb', 'gltf', 'ar', 'explorexr', 'xr' ];
    }

    /**
     * Declare which registered script handles must be present in the page
     * when this widget is rendered.  Elementor will ensure these are loaded
     * in both the frontend and the editor preview iframe.
     */
    public function get_script_depends() {
        return [
            'model-viewer-script',
            'explorexr-model-viewer-loader-manager',
            'explorexr-model-loader',
            'explorexr-model-viewer-wrapper',
        ];
    }

    public function get_style_depends() {
        return [ 'explorexr-model-viewer' ];
    }

    /* ------------------------------------------------------------------ */
    /*  Controls                                                            */
    /* ------------------------------------------------------------------ */

    protected function register_controls() {

        /* ---- Model Selection ----------------------------------------- */
        $this->start_controls_section(
            'section_model',
            [
                'label' => esc_html__( 'Model', 'explorexr' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        // Build a key=>label map from all published ExploreXR models.
        $model_options = $this->get_model_options();

        if ( ! empty( $model_options ) ) {
            $this->add_control(
                'model_id',
                [
                    'label'       => esc_html__( 'Select 3D Model', 'explorexr' ),
                    'type'        => Controls_Manager::SELECT,
                    'options'     => $model_options,
                    'default'     => array_key_first( $model_options ),
                    'description' => esc_html__( 'Choose the 3D model to display. You can manage models under ExploreXR → Browse Models.', 'explorexr' ),
                ]
            );
        } else {
            $this->add_control(
                'no_models_notice',
                [
                    'type'            => Controls_Manager::RAW_HTML,
                    'raw'             => sprintf(
                        /* translators: %s: URL to create model page */
                        __( 'No 3D models found. <a href="%s" target="_blank">Create your first model</a> in ExploreXR.', 'explorexr' ),
                        esc_url( admin_url( 'admin.php?page=explorexr-create-model' ) )
                    ),
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
                ]
            );
        }

        // Manual ID override (useful for large sites or models created after
        // the widget was added to the page).
        $this->add_control(
            'model_id_manual',
            [
                'label'       => esc_html__( 'Or enter Model ID manually', 'explorexr' ),
                'type'        => Controls_Manager::NUMBER,
                'min'         => 1,
                'placeholder' => esc_html__( 'e.g. 42', 'explorexr' ),
                'description' => esc_html__( 'Overrides the dropdown above. Find the ID in ExploreXR → Browse Models.', 'explorexr' ),
            ]
        );

        $this->end_controls_section();
    }

    /* ------------------------------------------------------------------ */
    /*  Rendering                                                           */
    /* ------------------------------------------------------------------ */

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Manual ID takes precedence over the dropdown.
        $model_id = ! empty( $settings['model_id_manual'] )
            ? intval( $settings['model_id_manual'] )
            : intval( $settings['model_id'] ?? 0 );

        if ( ! $model_id ) {
            $this->render_placeholder(
                esc_html__( 'Please select or enter a 3D model ID in the widget settings.', 'explorexr' )
            );
            return;
        }

        // Verify the post exists and is the right type.
        $post = get_post( $model_id );
        if ( ! $post || $post->post_type !== 'explorexr_model' || $post->post_status !== 'publish' ) {
            $this->render_placeholder(
                sprintf(
                    /* translators: %d: model ID */
                    esc_html__( 'Model ID %d not found or not published.', 'explorexr' ),
                    $model_id
                )
            );
            return;
        }

        // In Elementor's editor preview the shortcode runs inside the iframe
        // which is a full frontend page load — model-viewer renders normally.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- do_shortcode output is already escaped by our template
        echo do_shortcode( '[explorexr_model id="' . $model_id . '"]' );
    }

    /**
     * Return a placeholder block when no valid model is selected.
     * Only shown inside the Elementor editor – never on the frontend.
     *
     * @param string $message Human-readable reason.
     */
    private function render_placeholder( $message ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            return; // Silent on frontend.
        }
        printf(
            '<div style="padding:20px;background:#f0f0f0;border:2px dashed #ccc;text-align:center;color:#555;">
                <span class="dashicons dashicons-format-image" style="font-size:32px;display:block;margin-bottom:8px;"></span>
                <strong>ExploreXR 3D Model</strong><br><small>%s</small>
            </div>',
            esc_html( $message )
        );
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Return an array of [ post_id => 'Title (ID: X)' ] for all published models.
     *
     * @return array
     */
    private function get_model_options() {
        $models = get_posts( [
            'post_type'      => 'explorexr_model',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );

        $options = [];
        foreach ( $models as $model ) {
            /* translators: 1: model title  2: post ID */
            $options[ $model->ID ] = sprintf( '%s (ID: %d)', $model->post_title, $model->ID );
        }

        return $options;
    }
}
