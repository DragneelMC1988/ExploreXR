<?php
/**
 * Elementor Widget: ExploreXR 3D Model
 *
 * Renders an ExploreXR 3D model inside the Elementor editor and frontend
 * by delegating to the existing [explorexr_model] shortcode.
 *
 * @package ExploreXR
 * @since 1.1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ExploreXR_Elementor_Widget
 */
class ExploreXR_Elementor_Widget extends \Elementor\Widget_Base {

    /**
     * Widget machine name.
     *
     * @return string
     */
    public function get_name() {
        return 'explorexr_3d_model';
    }

    /**
     * Widget display title.
     *
     * @return string
     */
    public function get_title() {
        return esc_html__( 'ExploreXR 3D Model', 'explorexr' );
    }

    /**
     * Widget icon in the Elementor panel.
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-eye';
    }

    /**
     * Widget categories.
     *
     * @return array
     */
    public function get_categories() {
        return array( 'explorexr', 'general' );
    }

    /**
     * Search keywords.
     *
     * @return array
     */
    public function get_keywords() {
        return array( '3d', 'model', 'viewer', 'glb', 'gltf', 'ar', 'explorexr' );
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        $this->start_controls_section(
            'section_model',
            array(
                'label' => esc_html__( '3D Model', 'explorexr' ),
            )
        );

        // Build the options list from existing ExploreXR model posts.
        $models = $this->get_model_options();

        $this->add_control(
            'model_id',
            array(
                'label'       => esc_html__( 'Select Model', 'explorexr' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'options'     => $models,
                'default'     => '',
                'label_block' => true,
                'description' => esc_html__( 'Choose an ExploreXR 3D model to display.', 'explorexr' ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Fetch ExploreXR model posts for the dropdown.
     *
     * @return array Associative array of model_id => title.
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
            $options[ $model->ID ] = $model->post_title;
        }

        return $options;
    }

    /**
     * Render widget output on the frontend and inside the editor preview.
     *
     * Elementor calls this method for both the live preview iframe and the
     * published page — so the shortcode fires in both contexts, giving us
     * a real model-viewer preview in the editor.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $model_id = ! empty( $settings['model_id'] ) ? absint( $settings['model_id'] ) : 0;

        if ( ! $model_id ) {
            echo '<div class="explorexr-elementor-preview"><div class="explorexr-preview-placeholder">';
            echo '<i class="eicon-eye"></i>';
            echo '<span class="explorexr-preview-text">' . esc_html__( 'Select a 3D model from the widget settings.', 'explorexr' ) . '</span>';
            echo '</div></div>';
            return;
        }

        // Verify the model exists.
        $model_post = get_post( $model_id );
        if ( ! $model_post || ! in_array( $model_post->post_type, array( 'explorexr_model', 'explorexr_premium_model' ), true ) ) {
            echo '<div class="explorexr-elementor-error">' . esc_html__( 'The selected model could not be found.', 'explorexr' ) . '</div>';
            return;
        }

        // Delegate to the existing shortcode — this ensures full rendering
        // pipeline including addons, attributes filter, template selection, etc.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shortcode output is escaped internally
        echo do_shortcode( '[explorexr_model id="' . $model_id . '"]' );
    }
}
