<?php
/**
 * Shared admin partial: flex/grid layout controls for addon UI containers.
 *
 * Used by Materials and Annotations addons to expose CSS layout controls
 * (flex-direction, justify-content, gap, grid-template-columns, etc.) for
 * any UI slot/group rendered inside .explorexr-overlay-group on the frontend.
 *
 * Inputs in this partial have no name attribute. The addon's serializer JS
 * collects values via the .explorexr-layout-input class lookup and packs
 * them into the existing JSON post meta managed by that addon.
 *
 * Provides:
 *   explorexr_premium_render_layout_controls( $args )
 *   explorexr_premium_sanitize_layout_config( $raw )
 *
 * @package ExploreXR_Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'explorexr_premium_render_layout_controls' ) ) {
    function explorexr_premium_render_layout_controls( $args = array() ) {
        $defaults = array(
            'layout'      => array(),
            'ui_style'    => 'buttons',
            'prefix'      => 'explorexr-layout',
            'title'       => __( 'Layout', 'explorexr' ),
            'hide_header' => false,
        );
        $args        = wp_parse_args( $args, $defaults );
        $layout      = is_array( $args['layout'] ) ? $args['layout'] : array();
        $ui_style    = sanitize_text_field( $args['ui_style'] );
        $prefix      = sanitize_html_class( $args['prefix'] );
        $title       = sanitize_text_field( $args['title'] );
        $hide_header = (bool) $args['hide_header'];

        $g = function ( $key, $default = '' ) use ( $layout ) {
            return ( isset( $layout[ $key ] ) && $layout[ $key ] !== '' ) ? $layout[ $key ] : $default;
        };

        $is_flex     = ( $ui_style === 'buttons' );
        $is_grid     = ( $ui_style === 'grid' );
        $any_layout  = $is_flex || $is_grid;

        $direction_options = array(
            ''               => __( 'Default', 'explorexr' ),
            'row'            => 'row',
            'row-reverse'    => 'row-reverse',
            'column'         => 'column',
            'column-reverse' => 'column-reverse',
        );
        $wrap_options = array(
            ''             => __( 'Default', 'explorexr' ),
            'nowrap'       => 'nowrap',
            'wrap'         => 'wrap',
            'wrap-reverse' => 'wrap-reverse',
        );
        $auto_flow_options = array(
            ''             => __( 'Default', 'explorexr' ),
            'row'          => 'row',
            'column'       => 'column',
            'row dense'    => 'row dense',
            'column dense' => 'column dense',
        );
        $justify_options = array(
            ''              => __( 'Default', 'explorexr' ),
            'flex-start'    => 'flex-start',
            'center'        => 'center',
            'flex-end'      => 'flex-end',
            'space-between' => 'space-between',
            'space-around'  => 'space-around',
            'space-evenly'  => 'space-evenly',
        );
        $align_items_options = array(
            ''           => __( 'Default', 'explorexr' ),
            'stretch'    => 'stretch',
            'flex-start' => 'flex-start',
            'center'     => 'center',
            'flex-end'   => 'flex-end',
            'baseline'   => 'baseline',
        );
        $align_content_options = array(
            ''              => __( 'Default', 'explorexr' ),
            'stretch'       => 'stretch',
            'flex-start'    => 'flex-start',
            'center'        => 'center',
            'flex-end'      => 'flex-end',
            'space-between' => 'space-between',
            'space-around'  => 'space-around',
        );

        ?>
        <div class="explorexr-layout-controls <?php echo esc_attr( $prefix ); ?>"
             data-layout-prefix="<?php echo esc_attr( $prefix ); ?>"
             data-ui-style="<?php echo esc_attr( $ui_style ); ?>">
            <?php if ( ! $hide_header ) : ?>
            <div class="explorexr-layout-controls-header">
                <span class="dashicons dashicons-layout"></span>
                <span class="explorexr-layout-controls-title"><?php echo esc_html( $title ); ?></span>
                <button type="button" class="explorexr-layout-controls-toggle button-link" aria-expanded="false">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>
            </div>
            <?php endif; ?>
            <div class="explorexr-layout-controls-body" <?php echo $hide_header ? '' : 'style="display:none;"'; ?>>

                <div class="explorexr-layout-flex-only" <?php echo $is_flex ? '' : 'style="display:none;"'; ?>>
                    <div class="explorexr-layout-field-row">
                        <div class="explorexr-layout-field">
                            <label><?php esc_html_e( 'Direction', 'explorexr' ); ?></label>
                            <select class="explorexr-layout-input" data-layout-key="flex_direction">
                                <?php foreach ( $direction_options as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $g( 'flex_direction' ), $val ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="explorexr-layout-field">
                            <label><?php esc_html_e( 'Wrap', 'explorexr' ); ?></label>
                            <select class="explorexr-layout-input" data-layout-key="flex_wrap">
                                <?php foreach ( $wrap_options as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $g( 'flex_wrap' ), $val ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="explorexr-layout-grid-only" <?php echo $is_grid ? '' : 'style="display:none;"'; ?>>
                    <div class="explorexr-layout-field">
                        <label><?php esc_html_e( 'Grid Template Columns', 'explorexr' ); ?></label>
                        <input type="text" class="explorexr-layout-input"
                               data-layout-key="grid_template_columns"
                               value="<?php echo esc_attr( $g( 'grid_template_columns' ) ); ?>"
                               placeholder="repeat(auto-fit, minmax(140px, 1fr))">
                        <p class="description"><?php esc_html_e( 'CSS grid-template-columns value. Examples: "1fr 1fr", "repeat(3, 1fr)", "repeat(auto-fit, minmax(120px, 1fr))".', 'explorexr' ); ?></p>
                    </div>
                    <div class="explorexr-layout-field">
                        <label><?php esc_html_e( 'Auto Flow', 'explorexr' ); ?></label>
                        <select class="explorexr-layout-input" data-layout-key="grid_auto_flow">
                            <?php foreach ( $auto_flow_options as $val => $label ) : ?>
                                <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $g( 'grid_auto_flow' ), $val ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="explorexr-layout-align-only" <?php echo $any_layout ? '' : 'style="display:none;"'; ?>>
                    <div class="explorexr-layout-field-row">
                        <div class="explorexr-layout-field">
                            <label><?php esc_html_e( 'Justify Content', 'explorexr' ); ?></label>
                            <select class="explorexr-layout-input" data-layout-key="justify_content">
                                <?php foreach ( $justify_options as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $g( 'justify_content' ), $val ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="explorexr-layout-field">
                            <label><?php esc_html_e( 'Align Items', 'explorexr' ); ?></label>
                            <select class="explorexr-layout-input" data-layout-key="align_items">
                                <?php foreach ( $align_items_options as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $g( 'align_items' ), $val ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="explorexr-layout-field">
                            <label><?php esc_html_e( 'Align Content', 'explorexr' ); ?></label>
                            <select class="explorexr-layout-input" data-layout-key="align_content">
                                <?php foreach ( $align_content_options as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $g( 'align_content' ), $val ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="explorexr-layout-gap-only" <?php echo $any_layout ? '' : 'style="display:none;"'; ?>>
                    <div class="explorexr-layout-field-row">
                        <div class="explorexr-layout-field">
                            <label><?php esc_html_e( 'Gap (px)', 'explorexr' ); ?></label>
                            <input type="number" min="0" max="200" step="1" class="explorexr-layout-input" data-layout-key="gap" value="<?php echo esc_attr( $g( 'gap' ) ); ?>">
                        </div>
                        <div class="explorexr-layout-field">
                            <label><?php esc_html_e( 'Row Gap', 'explorexr' ); ?></label>
                            <input type="number" min="0" max="200" step="1" class="explorexr-layout-input" data-layout-key="row_gap" value="<?php echo esc_attr( $g( 'row_gap' ) ); ?>">
                        </div>
                        <div class="explorexr-layout-field">
                            <label><?php esc_html_e( 'Column Gap', 'explorexr' ); ?></label>
                            <input type="number" min="0" max="200" step="1" class="explorexr-layout-input" data-layout-key="column_gap" value="<?php echo esc_attr( $g( 'column_gap' ) ); ?>">
                        </div>
                    </div>
                </div>

                <?php if ( ! $any_layout ) : ?>
                    <p class="description"><?php esc_html_e( 'Layout options apply when UI style is "Buttons" (flex) or "Grid".', 'explorexr' ); ?></p>
                <?php endif; ?>

            </div>
        </div>
        <?php
    }
}

if ( ! function_exists( 'explorexr_premium_sanitize_layout_config' ) ) {
    /**
     * Sanitize a layout config array against the allowed schema.
     * Returns an empty array when no valid keys are present.
     */
    function explorexr_premium_sanitize_layout_config( $raw ) {
        if ( ! is_array( $raw ) ) {
            return array();
        }

        $out = array();

        $enums = array(
            'flex_direction'  => array( 'row', 'row-reverse', 'column', 'column-reverse' ),
            'flex_wrap'       => array( 'nowrap', 'wrap', 'wrap-reverse' ),
            'grid_auto_flow'  => array( 'row', 'column', 'row dense', 'column dense' ),
            'justify_content' => array( 'flex-start', 'center', 'flex-end', 'space-between', 'space-around', 'space-evenly' ),
            'align_items'     => array( 'stretch', 'flex-start', 'center', 'flex-end', 'baseline' ),
            'align_content'   => array( 'stretch', 'flex-start', 'center', 'flex-end', 'space-between', 'space-around' ),
        );
        foreach ( $enums as $key => $allowed ) {
            if ( isset( $raw[ $key ] ) && is_string( $raw[ $key ] ) && in_array( $raw[ $key ], $allowed, true ) ) {
                $out[ $key ] = $raw[ $key ];
            }
        }

        foreach ( array( 'gap', 'row_gap', 'column_gap' ) as $key ) {
            if ( isset( $raw[ $key ] ) && $raw[ $key ] !== '' ) {
                $n = intval( $raw[ $key ] );
                if ( $n >= 0 && $n <= 1000 ) {
                    $out[ $key ] = $n;
                }
            }
        }

        if ( isset( $raw['grid_template_columns'] ) && is_string( $raw['grid_template_columns'] ) ) {
            $val = trim( $raw['grid_template_columns'] );
            if ( $val !== '' && strlen( $val ) <= 200 && preg_match( '/^[a-zA-Z0-9_\-\(\)\,\.\%\s]+$/', $val ) ) {
                $out['grid_template_columns'] = $val;
            }
        }

        return $out;
    }
}
