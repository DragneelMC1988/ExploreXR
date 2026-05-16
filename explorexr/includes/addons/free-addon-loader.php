<?php
/**
 * ExploreXR Free — Addon Loader Helpers
 *
 * Defines constants and helper functions for the Free plan's one-addon feature.
 * Loaded inside explorexr_free_load_includes() (plugins_loaded priority 10).
 *
 * @package ExploreXR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Map of allowed slugs to their plugin file paths. */
const EXPLOREXR_FREE_ALLOWED_ADDONS = array(
	'ar'          => 'explorexr-ar-addon/explorexr-ar-addon.php',
	'animation'   => 'explorexr-animation-addon/explorexr-animation-addon.php',
	'camera'      => 'explorexr-camera-addon/explorexr-camera-addon.php',
	'annotations' => 'explorexr-annotations-addon/explorexr-annotations-addon.php',
);

function explorexr_free_get_allowed_addons(): array {
	return EXPLOREXR_FREE_ALLOWED_ADDONS;
}

function explorexr_free_get_selected_addon(): string {
	return (string) get_option( 'explorexr_free_selected_addon', '' );
}

function explorexr_free_is_addon_installed( string $slug ): bool {
	$addons = explorexr_free_get_allowed_addons();
	if ( ! isset( $addons[ $slug ] ) ) {
		return false;
	}
	return file_exists( WP_PLUGIN_DIR . '/' . $addons[ $slug ] );
}

function explorexr_free_is_addon_active( string $slug ): bool {
	$addons = explorexr_free_get_allowed_addons();
	if ( ! isset( $addons[ $slug ] ) ) {
		return false;
	}
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	return is_plugin_active( $addons[ $slug ] );
}
