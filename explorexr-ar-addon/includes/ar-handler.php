<?php
/**
 * ExploreXR AR Add-On - AR Handler
 *
 * Handles AR functionality for 3D models.
 *
 * @package ExploreXR AR Add-On
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate color value — supports both hex and rgba formats.
 *
 * @param string $color Color value to validate.
 * @return string Sanitized color value or empty string if invalid.
 */
function explorexr_premium_ar_validate_color( $color ) {
	if ( empty( $color ) ) {
		return '';
	}

	// Allow rgba format.
	if ( preg_match( '/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(?:,\s*[\d.]+\s*)?\)$/i', $color ) ) {
		return sanitize_text_field( $color );
	}

	// Allow hex format.
	$sanitized = sanitize_hex_color( $color );
	return $sanitized ? $sanitized : '';
}

/**
 * Save AR model meta options.
 *
 * Hooked to explorexr_premium_save_model_meta which fires from the main
 * plugin's save_post callback after nonce and capability checks are done.
 *
 * @param int $post_id Post ID being saved.
 */
function explorexr_premium_ar_save_model_options( $post_id ) {
	// Verify nonce (uses the main edit model nonce).
	if ( ! isset( $_POST['explorexr_edit_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['explorexr_edit_nonce'] ) ), 'explorexr_edit_model' ) ) {
		return;
	}

	// Verify user capability.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Skip autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Presence marker — AR card must be in the form for saves to proceed.
	$has_presence = isset( $_POST['explorexr_premium_ar_present'] )
		|| isset( $_POST['explorexr_premium_ar_enabled'] );
	if ( ! $has_presence ) {
		return;
	}

	// --- AR enabled toggle ---
	$ar_enabled = isset( $_POST['explorexr_premium_ar_enabled'] ) ? 'on' : 'off';
	update_post_meta( $post_id, '_explorexr_premium_ar_enabled', $ar_enabled );

	// --- AR modes ---
	if ( isset( $_POST['explorexr_premium_ar_modes'] ) && is_array( $_POST['explorexr_premium_ar_modes'] ) ) {
		$valid_modes = array( 'webxr', 'scene-viewer', 'quick-look' );
		$modes       = array_intersect(
			array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['explorexr_premium_ar_modes'] ) ),
			$valid_modes
		);
		update_post_meta( $post_id, '_explorexr_premium_ar_modes', $modes );
	} else {
		// No modes submitted means all unchecked; save empty array.
		if ( isset( $_POST['explorexr_premium_ar_present'] ) ) {
			update_post_meta( $post_id, '_explorexr_premium_ar_modes', array() );
		}
	}

	// --- AR placement ---
	if ( isset( $_POST['explorexr_premium_ar_placement'] ) ) {
		$valid_placements = array( 'floor', 'wall', 'table' );
		$placement        = sanitize_text_field( wp_unslash( $_POST['explorexr_premium_ar_placement'] ) );
		if ( ! in_array( $placement, $valid_placements, true ) ) {
			$placement = 'floor';
		}
		update_post_meta( $post_id, '_explorexr_premium_ar_placement', $placement );
	}

	// --- AR scale — Allow Scaling checkbox maps to auto/fixed ---
	if ( isset( $_POST['explorexr_premium_ar_allow_scaling'] ) ) {
		update_post_meta( $post_id, '_explorexr_premium_ar_scale', 'auto' );
	} elseif ( isset( $_POST['explorexr_premium_ar_present'] ) ) {
		// Presence marker present but checkbox missing = explicitly unchecked.
		update_post_meta( $post_id, '_explorexr_premium_ar_scale', 'fixed' );
	}

	// --- AR auto-rotate ---
	$auto_rotate = isset( $_POST['explorexr_premium_ar_auto_rotate'] ) ? 'on' : 'off';
	update_post_meta( $post_id, '_explorexr_premium_ar_auto_rotate', $auto_rotate );

	// --- iOS USDZ file ---
	if ( isset( $_POST['explorexr_premium_ar_usdz_model'] ) ) {
		update_post_meta( $post_id, '_explorexr_premium_ar_usdz_model', esc_url_raw( wp_unslash( $_POST['explorexr_premium_ar_usdz_model'] ) ) );
	}

	// Legacy meta key kept for backward compat.
	if ( isset( $_POST['explorexr_premium_ar_ios_src'] ) ) {
		update_post_meta( $post_id, '_explorexr_premium_ar_ios_src', esc_url_raw( wp_unslash( $_POST['explorexr_premium_ar_ios_src'] ) ) );
	}

	// --- XR environment ---
	if ( isset( $_POST['explorexr_premium_ar_xr_environment'] ) ) {
		update_post_meta( $post_id, '_explorexr_premium_ar_xr_environment', esc_url_raw( wp_unslash( $_POST['explorexr_premium_ar_xr_environment'] ) ) );
	}

	// --- Min height ---
	if ( isset( $_POST['explorexr_premium_ar_min_height'] ) ) {
		update_post_meta( $post_id, '_explorexr_premium_ar_min_height', sanitize_text_field( wp_unslash( $_POST['explorexr_premium_ar_min_height'] ) ) );
	}

	// --- Button text ---
	if ( isset( $_POST['explorexr_premium_ar_button_text'] ) ) {
		update_post_meta( $post_id, '_explorexr_premium_ar_button_text', sanitize_text_field( wp_unslash( $_POST['explorexr_premium_ar_button_text'] ) ) );
	}

	// --- Button colors ---
	if ( isset( $_POST['explorexr_premium_ar_button_bg_color'] ) ) {
		$raw = wp_unslash( $_POST['explorexr_premium_ar_button_bg_color'] );
		update_post_meta( $post_id, '_explorexr_premium_ar_button_bg_color', explorexr_premium_ar_validate_color( $raw ) );
	}

	if ( isset( $_POST['explorexr_premium_ar_button_text_color'] ) ) {
		update_post_meta(
			$post_id,
			'_explorexr_premium_ar_button_text_color',
			explorexr_premium_ar_validate_color( wp_unslash( $_POST['explorexr_premium_ar_button_text_color'] ) )
		);
	}

	if ( isset( $_POST['explorexr_premium_ar_button_border_color'] ) ) {
		update_post_meta(
			$post_id,
			'_explorexr_premium_ar_button_border_color',
			explorexr_premium_ar_validate_color( wp_unslash( $_POST['explorexr_premium_ar_button_border_color'] ) )
		);
	}

	// --- Button size ---
	if ( isset( $_POST['explorexr_premium_ar_button_size'] ) ) {
		$valid_sizes = array( 'small', 'medium', 'large' );
		$size        = sanitize_text_field( wp_unslash( $_POST['explorexr_premium_ar_button_size'] ) );
		update_post_meta( $post_id, '_explorexr_premium_ar_button_size', in_array( $size, $valid_sizes, true ) ? $size : 'medium' );
	}

	// --- Button border radius ---
	if ( isset( $_POST['explorexr_premium_ar_button_border_radius'] ) ) {
		update_post_meta( $post_id, '_explorexr_premium_ar_button_border_radius', absint( wp_unslash( $_POST['explorexr_premium_ar_button_border_radius'] ) ) );
	}

	// --- Button position ---
	if ( isset( $_POST['explorexr_premium_ar_button_position'] ) ) {
		$valid_positions = array( 'bottom-center', 'bottom-left', 'bottom-right', 'top-center', 'top-left', 'top-right', 'center-left', 'center-right' );
		$position        = sanitize_text_field( wp_unslash( $_POST['explorexr_premium_ar_button_position'] ) );
		update_post_meta( $post_id, '_explorexr_premium_ar_button_position', in_array( $position, $valid_positions, true ) ? $position : 'bottom-center' );
	}

	// --- Button icon ---
	if ( isset( $_POST['explorexr_premium_ar_button_icon'] ) ) {
		update_post_meta( $post_id, '_explorexr_premium_ar_button_icon', sanitize_text_field( wp_unslash( $_POST['explorexr_premium_ar_button_icon'] ) ) );
	}

	if ( isset( $_POST['explorexr_premium_ar_button_icon_position'] ) ) {
		$icon_pos = sanitize_text_field( wp_unslash( $_POST['explorexr_premium_ar_button_icon_position'] ) );
		update_post_meta( $post_id, '_explorexr_premium_ar_button_icon_position', in_array( $icon_pos, array( 'left', 'right' ), true ) ? $icon_pos : 'left' );
	}

	$icon_enabled = isset( $_POST['explorexr_premium_ar_button_icon_enabled'] ) ? '1' : '0';
	update_post_meta( $post_id, '_explorexr_premium_ar_button_icon_enabled', $icon_enabled );
}
add_action( 'explorexr_premium_save_model_meta', 'explorexr_premium_ar_save_model_options' );

/**
 * Inject AR attributes into the model-viewer element.
 *
 * Runs at priority 10 on explorexr_premium_model_viewer_attributes.
 *
 * @param array    $attributes Current model-viewer attributes.
 * @param int|null $model_id   The model post ID.
 * @return array Modified attributes.
 */
function explorexr_premium_ar_add_model_attributes( $attributes, $model_id ) {
	// Default to disabled unless explicitly enabled per model.
	$ar_enabled = false;

	if ( $model_id ) {
		$model_ar_enabled = get_post_meta( $model_id, '_explorexr_premium_ar_enabled', true );
		// Backward compat with legacy meta key.
		$legacy_ar_enabled = get_post_meta( $model_id, '_explorexr_ar_enabled', true );

		if ( 'on' === $model_ar_enabled || '1' === $legacy_ar_enabled || 'on' === $legacy_ar_enabled ) {
			$ar_enabled = true;
		}
	}

	if ( ! $ar_enabled ) {
		// Remove any stale AR attributes and mark as disabled.
		$ar_attrs = array( 'ar', 'ar-modes', 'ar-scale', 'ar-placement', 'ios-src', 'environment-image' );
		foreach ( $ar_attrs as $attr ) {
			unset( $attributes[ $attr ] );
		}
		$attributes['data-explorexr-premium-ar-disabled'] = 'true';
		return $attributes;
	}

	// --- Core AR attributes ---
	$attributes['ar']                               = '';
	$attributes['data-ar-enabled']                  = 'true';
	$attributes['data-explorexr-premium-ar-enabled'] = 'true';

	// --- AR modes (model-specific, falls back to global) ---
	$ar_modes = get_option( 'explorexr_premium_ar_modes', array( 'webxr', 'scene-viewer', 'quick-look' ) );
	if ( $model_id ) {
		$model_modes = get_post_meta( $model_id, '_explorexr_premium_ar_modes', true );
		if ( ! empty( $model_modes ) && is_array( $model_modes ) ) {
			$ar_modes = $model_modes;
		}
	}
	if ( ! empty( $ar_modes ) ) {
		$attributes['ar-modes'] = is_array( $ar_modes ) ? implode( ' ', $ar_modes ) : $ar_modes;
	}

	// --- AR placement ---
	$ar_placement = get_post_meta( $model_id, '_explorexr_premium_ar_placement', true ) ?: 'floor';
	$attributes['ar-placement'] = esc_attr( $ar_placement );

	// --- AR scale ---
	$ar_scale = get_post_meta( $model_id, '_explorexr_premium_ar_scale', true ) ?: 'auto';
	$attributes['ar-scale'] = esc_attr( $ar_scale );

	// --- iOS USDZ model (check two meta keys for backward compat) ---
	$usdz_src = get_post_meta( $model_id, '_explorexr_premium_ar_usdz_model', true );
	if ( empty( $usdz_src ) ) {
		$usdz_src = get_post_meta( $model_id, '_explorexr_premium_ar_ios_src', true );
	}
	if ( ! empty( $usdz_src ) ) {
		$attributes['ios-src'] = esc_url( $usdz_src );
	}

	// --- XR environment image ---
	$xr_environment = get_post_meta( $model_id, '_explorexr_premium_ar_xr_environment', true );
	if ( ! empty( $xr_environment ) ) {
		$attributes['environment-image'] = esc_url( $xr_environment );
	}

	// --- Button styling data-* attributes (read by ar-enhanced.js) ---
	$button_text = get_post_meta( $model_id, '_explorexr_premium_ar_button_text', true );
	if ( empty( $button_text ) ) {
		$button_text = 'View in AR';
	}
	$attributes['data-ar-button-text'] = esc_attr( $button_text );

	$button_image = get_post_meta( $model_id, '_explorexr_premium_ar_button_image', true );
	if ( ! empty( $button_image ) ) {
		$attributes['data-ar-button-image'] = esc_url( $button_image );
	}

	$button_bg_color = get_post_meta( $model_id, '_explorexr_premium_ar_button_bg_color', true );
	if ( empty( $button_bg_color ) ) {
		$button_bg_color = '#000000';
	}
	$attributes['data-ar-button-bg-color'] = esc_attr( $button_bg_color );

	$button_text_color = get_post_meta( $model_id, '_explorexr_premium_ar_button_text_color', true );
	if ( empty( $button_text_color ) ) {
		$button_text_color = '#ffffff';
	}
	$attributes['data-ar-button-text-color'] = esc_attr( $button_text_color );

	$button_border_color = get_post_meta( $model_id, '_explorexr_premium_ar_button_border_color', true );
	$attributes['data-ar-button-border-color'] = esc_attr( $button_border_color );

	$button_size = get_post_meta( $model_id, '_explorexr_premium_ar_button_size', true );
	if ( empty( $button_size ) ) {
		$button_size = 'medium';
	}
	$attributes['data-ar-button-size'] = esc_attr( $button_size );

	$button_border_radius = get_post_meta( $model_id, '_explorexr_premium_ar_button_border_radius', true );
	if ( '' === $button_border_radius || false === $button_border_radius ) {
		$button_border_radius = '4';
	}
	$attributes['data-ar-button-border-radius'] = esc_attr( (string) $button_border_radius );

	$button_position = get_post_meta( $model_id, '_explorexr_premium_ar_button_position', true );
	if ( empty( $button_position ) ) {
		$button_position = 'bottom-center';
	}
	$attributes['data-ar-button-position'] = esc_attr( $button_position );

	// --- Button icon ---
	$icon_enabled = get_post_meta( $model_id, '_explorexr_premium_ar_button_icon_enabled', true );
	$attributes['data-ar-button-icon-enabled'] = '1' === $icon_enabled ? '1' : '0';

	if ( '1' === $icon_enabled ) {
		$button_icon = get_post_meta( $model_id, '_explorexr_premium_ar_button_icon', true );
		if ( ! empty( $button_icon ) ) {
			$attributes['data-ar-button-icon'] = esc_attr( $button_icon );
		}
		$icon_position = get_post_meta( $model_id, '_explorexr_premium_ar_button_icon_position', true );
		if ( ! empty( $icon_position ) ) {
			$attributes['data-ar-button-icon-position'] = esc_attr( $icon_position );
		}
	}

	// Flag that this model uses AR addon custom button styling.
	$attributes['data-explorexr-premium-ar-custom-button'] = 'true';

	// --- Fallback text ---
	$fallback_text = get_option( 'explorexr_premium_ar_fallback_text', __( 'AR not supported on this device', 'explorexr-ar-addon' ) );
	$model_fallback = get_post_meta( $model_id, '_explorexr_premium_ar_fallback_text', true );
	if ( ! empty( $model_fallback ) ) {
		$fallback_text = $model_fallback;
	}
	$attributes['data-ar-fallback-text'] = esc_attr( $fallback_text );

	return $attributes;
}
add_filter( 'explorexr_premium_model_viewer_attributes', 'explorexr_premium_ar_add_model_attributes', 10, 2 );

/**
 * Track AR usage statistics via AJAX.
 */
function explorexr_premium_ar_addon_track_usage() {
	check_ajax_referer( 'explorexr_premium_ar_nonce', 'nonce' );

	$model_id   = isset( $_POST['model_id'] ) ? absint( wp_unslash( $_POST['model_id'] ) ) : 0;
	$event_type = isset( $_POST['event_type'] ) ? sanitize_text_field( wp_unslash( $_POST['event_type'] ) ) : '';
	$device_type = isset( $_POST['device_type'] ) ? sanitize_text_field( wp_unslash( $_POST['device_type'] ) ) : '';

	if ( ! $model_id || ! $event_type ) {
		wp_send_json_error( 'Missing data' );
	}

	$valid_events = array( 'ar_view', 'ar_session_start' );
	if ( ! in_array( $event_type, $valid_events, true ) ) {
		wp_send_json_error( 'Invalid event type' );
	}

	switch ( $event_type ) {
		case 'ar_view':
			$count = (int) get_post_meta( $model_id, 'explorexr_premium_ar_view_count', true );
			update_post_meta( $model_id, 'explorexr_premium_ar_view_count', $count + 1 );
			break;

		case 'ar_session_start':
			$count = (int) get_post_meta( $model_id, 'explorexr_premium_ar_session_count', true );
			update_post_meta( $model_id, 'explorexr_premium_ar_session_count', $count + 1 );

			if ( $device_type ) {
				$device_counts = get_post_meta( $model_id, 'explorexr_premium_ar_device_counts', true );
				if ( ! is_array( $device_counts ) ) {
					$device_counts = array();
				}
				$safe_device = sanitize_key( $device_type );
				$device_counts[ $safe_device ] = isset( $device_counts[ $safe_device ] ) ? $device_counts[ $safe_device ] + 1 : 1;
				update_post_meta( $model_id, 'explorexr_premium_ar_device_counts', $device_counts );
			}
			break;
	}

	wp_send_json_success( 'Tracking successful' );
}
add_action( 'wp_ajax_explorexr_premium_ar_track_usage', 'explorexr_premium_ar_addon_track_usage' );
add_action( 'wp_ajax_nopriv_explorexr_premium_ar_track_usage', 'explorexr_premium_ar_addon_track_usage' );

/**
 * Handle AR tracking events via AJAX.
 */
function explorexr_premium_ar_handle_tracking_event() {
	check_ajax_referer( 'explorexr_premium_ar_nonce', 'nonce' );

	$event = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( $_POST['event'] ) ) : '';
	if ( empty( $event ) ) {
		wp_send_json_error( 'Missing event name' );
	}

	$data_raw      = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '{}';
	$data          = json_decode( stripslashes( $data_raw ), true );
	$sanitized_data = array();
	if ( is_array( $data ) ) {
		foreach ( $data as $key => $value ) {
			$sanitized_data[ sanitize_key( $key ) ] = sanitize_text_field( (string) $value );
		}
	}

	$existing_events   = get_option( 'explorexr_premium_ar_tracking_events', array() );
	$existing_events[] = array(
		'event' => $event,
		'data'  => $sanitized_data,
		'time'  => current_time( 'mysql' ),
	);

	// Keep only the last 100 events.
	if ( count( $existing_events ) > 100 ) {
		$existing_events = array_slice( $existing_events, -100 );
	}

	update_option( 'explorexr_premium_ar_tracking_events', $existing_events );
	do_action( 'explorexr_premium_ar_tracking_event', $event, $sanitized_data );

	wp_send_json_success( 'Event tracked' );
}
add_action( 'wp_ajax_explorexr_premium_ar_track_event', 'explorexr_premium_ar_handle_tracking_event' );
add_action( 'wp_ajax_nopriv_explorexr_premium_ar_track_event', 'explorexr_premium_ar_handle_tracking_event' );
