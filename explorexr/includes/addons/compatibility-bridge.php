<?php
/**
 * ExploreXR Free — Premium Compatibility Bridge
 *
 * Provides the same function signatures that addon plugins call when checking
 * for Premium presence and license validity. Loaded at file-include time
 * (before plugins_loaded fires) so all functions are available before addons
 * run their own plugins_loaded priority-10 callbacks.
 *
 * @package ExploreXR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Premium presence — addons call function_exists('explorexr_premium_is_active')
// ---------------------------------------------------------------------------

if ( ! function_exists( 'explorexr_premium_is_active' ) ) {
	function explorexr_premium_is_active(): bool {
		return true;
	}
}

// ---------------------------------------------------------------------------
// License checks — addons call explorexr_premium_is_addon_licensed($slug)
// ---------------------------------------------------------------------------

if ( ! function_exists( 'explorexr_premium_is_addon_licensed' ) ) {
	/**
	 * Returns true only for the single addon the Free user has selected.
	 */
	function explorexr_premium_is_addon_licensed( string $slug ): bool {
		$selected = get_option( 'explorexr_free_selected_addon', '' );
		return '' !== $selected && $slug === $selected;
	}
}

if ( ! function_exists( 'explorexr_premium_get_license_tier' ) ) {
	function explorexr_premium_get_license_tier(): string {
		return 'free';
	}
}

if ( ! function_exists( 'explorexr_premium_is_license_active' ) ) {
	function explorexr_premium_is_license_active(): bool {
		return true;
	}
}

if ( ! function_exists( 'explorexr_premium_get_max_addons' ) ) {
	function explorexr_premium_get_max_addons(): int {
		return 1;
	}
}

if ( ! function_exists( 'explorexr_premium_get_selected_addons' ) ) {
	function explorexr_premium_get_selected_addons(): array {
		$selected = get_option( 'explorexr_free_selected_addon', '' );
		return '' !== $selected ? array( $selected ) : array();
	}
}

if ( ! function_exists( 'explorexr_premium_is_pro_or_higher' ) ) {
	function explorexr_premium_is_pro_or_higher(): bool {
		return false;
	}
}

// ---------------------------------------------------------------------------
// Content detection — addons may call this to guard asset enqueuing
// ---------------------------------------------------------------------------

if ( ! function_exists( 'explorexr_premium_has_model_viewers' ) ) {
	/**
	 * Returns true when the current (or given) post contains an ExploreXR shortcode.
	 */
	function explorexr_premium_has_model_viewers( ?WP_Post $post = null ): bool {
		if ( null === $post ) {
			global $post;
		}
		if ( ! ( $post instanceof WP_Post ) ) {
			return false;
		}
		return has_shortcode( $post->post_content, 'EXPLOREXR_model' )
			|| has_shortcode( $post->post_content, 'explorexr_model' )
			|| has_shortcode( $post->post_content, 'explorexr' );
	}
}

// ---------------------------------------------------------------------------
// Utilities
// ---------------------------------------------------------------------------

if ( ! function_exists( 'explorexr_sanitize_hex_color' ) ) {
	function explorexr_sanitize_hex_color( string $color, string $default = '#000000' ): string {
		$sanitized = sanitize_hex_color( $color );
		return '' !== (string) $sanitized ? (string) $sanitized : $default;
	}
}

if ( ! function_exists( 'explorexr_premium_addon_license_notice' ) ) {
	/** No-op stub — Free plugin never shows Premium license notices. */
	function explorexr_premium_addon_license_notice( string $addon_name, string $addon_slug = '' ): void {
		// Intentionally empty — addons call this but Free has no license UI.
	}
}

