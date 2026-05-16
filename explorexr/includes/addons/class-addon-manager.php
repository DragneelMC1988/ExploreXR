<?php
/**
 * ExploreXR Free — Addon Manager (Compatibility Stub)
 *
 * Addons check class_exists('ExploreXR_Addon_Manager') and call
 * ExploreXR_Addon_Manager::get_instance()->register_addon(). This stub
 * satisfies those calls while enforcing the Free-tier one-addon restriction.
 *
 * @package ExploreXR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'ExploreXR_Addon_Manager' ) ) {
	return;
}

class ExploreXR_Addon_Manager {

	/** Slugs the Free plan is allowed to load. */
	const FREE_ALLOWED_SLUGS = array( 'ar', 'animation', 'camera', 'annotations' );

	/** @var self|null */
	private static ?self $instance = null;

	/** @var array<string, array<string, mixed>> */
	private array $registered_addons = array();

	/** @var array<string, object> */
	private array $active_addons = array();

	private function __construct() {}

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register an addon. Silently ignores slugs not in FREE_ALLOWED_SLUGS.
	 *
	 * @param string               $slug Addon slug.
	 * @param array<string, mixed> $data Addon registration data.
	 */
	public function register_addon( string $slug, array $data ): void {
		if ( ! in_array( $slug, self::FREE_ALLOWED_SLUGS, true ) ) {
			return;
		}
		$this->registered_addons[ $slug ] = $data;
	}

	/** @return array<string, array<string, mixed>> */
	public function get_registered_addons(): array {
		return $this->registered_addons;
	}

	/** @return array<string, object> */
	public function get_active_addons(): array {
		return $this->active_addons;
	}

	public function is_addon_active( string $slug ): bool {
		$selected = get_option( 'explorexr_free_selected_addon', '' );
		return $slug === $selected && in_array( $slug, self::FREE_ALLOWED_SLUGS, true );
	}

	public function resolve_slug( string $slug ): string {
		return $slug;
	}

	public function ensure_addon_options_active( string $slug ): void {}

	public function ensure_addon_options_inactive( string $slug ): void {}

	public function is_addon_meta_disabled( string $slug ): bool {
		return false;
	}
}
