<?php
/**
 * Beaver Builder Module Frontend Template: ExploreXR 3D Model
 *
 * @package ExploreXR
 * @since 1.1.3
 *
 * @var object $module   Module instance.
 * @var object $settings Module settings.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$explorexr_bb_model_id = ! empty( $settings->model_id ) ? absint( $settings->model_id ) : 0;

if ( ! $explorexr_bb_model_id ) {
    echo '<div style="padding:40px;text-align:center;background:#f7f7f7;border:1px dashed #ccc;">';
    echo esc_html__( 'Select a 3D model from the module settings.', 'explorexr' );
    echo '</div>';
    return;
}

// Delegate to the existing shortcode for full pipeline support.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shortcode output is escaped internally.
echo do_shortcode( '[explorexr_model id="' . $explorexr_bb_model_id . '"]' );
