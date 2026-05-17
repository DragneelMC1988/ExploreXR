<?php
/**
 * Plugin Name: ExploreXR - AR Add-On
 * Plugin URI: https://expoxr.com/addon/ar/
 * Description: Adds Augmented Reality functionality to the ExploreXR 3D Model Viewer
 * Version: 1.3.1
 * Author: Ayal Othman
 * Author URI: https://ExploreXR.de
 * Text Domain: explorexr-ar-addon
 * License: GPL2
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants (correct EXPLOREXR_ prefix per naming convention)
define( 'EXPLOREXR_AR_VERSION', '1.3.1' );
define( 'EXPLOREXR_AR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EXPLOREXR_AR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Backward-compat aliases for any external code referencing old constant names
if ( ! defined( 'explorexr_premium_AR_VERSION' ) ) {
	define( 'explorexr_premium_AR_VERSION', EXPLOREXR_AR_VERSION );
}
if ( ! defined( 'explorexr_premium_AR_PLUGIN_DIR' ) ) {
	define( 'explorexr_premium_AR_PLUGIN_DIR', EXPLOREXR_AR_PLUGIN_DIR );
}
if ( ! defined( 'explorexr_premium_AR_PLUGIN_URL' ) ) {
	define( 'explorexr_premium_AR_PLUGIN_URL', EXPLOREXR_AR_PLUGIN_URL );
}

// Load shared addon notice helper from whichever host (Premium or Free) ships it.
foreach ( array(
	WP_PLUGIN_DIR . '/explorexr-premium/includes/shared/addon-notices-helper.php',
	WP_PLUGIN_DIR . '/explorexr/includes/shared/addon-notices-helper.php',
) as $explorexr_shared_helper ) {
	if ( file_exists( $explorexr_shared_helper ) ) {
		require_once $explorexr_shared_helper;
		break;
	}
}
unset( $explorexr_shared_helper );

/**
 * Check if ExploreXR Premium plugin is active.
 */
function explorexr_premium_ar_addon_check_main_plugin() {
	if ( function_exists( 'explorexr_premium_is_active' ) ) {
		if ( ! explorexr_premium_is_active() ) {
			add_action( 'admin_notices', 'explorexr_premium_ar_addon_missing_premium_notice' );
			return false;
		}
		return true;
	}

	return class_exists( 'ExploreXR_Addon_Manager' );
}

/**
 * Display notice if ExploreXR Premium plugin is missing.
 */
function explorexr_premium_ar_addon_missing_premium_notice() {
	if ( function_exists( 'explorexr_addon_missing_premium_notice' ) ) {
		explorexr_addon_missing_premium_notice( 'AR Add-On' );
	}
}

/**
 * Check if this add-on is licensed for the current installation.
 */
function explorexr_premium_ar_addon_is_licensed() {
	if ( function_exists( 'explorexr_premium_is_addon_licensed' ) ) {
		return explorexr_premium_is_addon_licensed( 'ar' );
	}
	return false;
}

/**
 * Register addon with the Addon Manager.
 * Runs at init priority 15 (required per addon contract).
 */
function explorexr_premium_ar_addon_register() {
	// Register with whichever host (Free or Premium) provides the Addon Manager.
	if ( ! class_exists( 'ExploreXR_Addon_Manager' ) ) {
		return;
	}

	$addon_manager = ExploreXR_Addon_Manager::get_instance();
	if ( $addon_manager ) {
		$addon_manager->register_addon(
			'ar',
			array(
				'name'             => 'AR Add-On',
				'version'          => EXPLOREXR_AR_VERSION,
				'min_core_version' => '0.2.0',
				'max_core_version' => '2.0.0',
				'file'             => __FILE__,
				'main_class'       => 'ExploreXR_AR_Handler',
				'dependencies'     => array(),
				'description'      => 'Adds augmented reality functionality to ExploreXR 3D model viewer.',
				'settings_page'    => 'explorexr-premium-ar-settings',
				'default_options'  => array(
					'explorexr_premium_ar_ios_supported'     => 'on',
					'explorexr_premium_ar_android_supported' => 'on',
					'explorexr_premium_ar_webxr_supported'   => 'on',
					'explorexr_premium_ar_button_text'       => '',
					'explorexr_premium_ar_placement'         => 'floor',
					'explorexr_premium_ar_button_image'      => '',
					'explorexr_premium_ar_usdz_model'        => '',
					'explorexr_premium_ar_xr_environment'    => '',
					'explorexr_premium_ar_min_height'        => '400px',
				),
			)
		);
	}
}
add_action( 'init', 'explorexr_premium_ar_addon_register', 15 );

/**
 * Initialize the plugin (plugins_loaded priority 10 per addon contract).
 */
function explorexr_premium_ar_addon_init() {
	if ( ! explorexr_premium_ar_addon_check_main_plugin() ) {
		return;
	}

	// Always load settings (needed for settings page even if not licensed)
	if ( file_exists( EXPLOREXR_AR_PLUGIN_DIR . 'includes/settings.php' ) ) {
		require_once EXPLOREXR_AR_PLUGIN_DIR . 'includes/settings.php';
	}

	if ( ! explorexr_premium_ar_addon_is_licensed() ) {
		add_action( 'admin_notices', 'explorexr_premium_ar_addon_license_notice' );
		return;
	}

	// Load addon functionality
	require_once EXPLOREXR_AR_PLUGIN_DIR . 'includes/ar-handler.php';
	require_once EXPLOREXR_AR_PLUGIN_DIR . 'includes/migration.php';
	require_once EXPLOREXR_AR_PLUGIN_DIR . 'includes/migration-defaults.php';

	// Ensure AR meta is accessible when the addon is active
	explorexr_premium_ar_addon_restore_meta_access();

	// Register scripts/styles (admin and frontend)
	add_action( 'wp_enqueue_scripts', 'explorexr_premium_ar_addon_register_assets', 5 );
	add_action( 'wp_enqueue_scripts', 'explorexr_premium_ar_addon_enqueue_assets' );
	add_action( 'admin_enqueue_scripts', 'explorexr_premium_ar_addon_register_admin_assets' );

	// Register integration filter
	add_filter( 'explorexr_premium_addon_available', 'explorexr_premium_ar_addon_register_availability', 10, 2 );
}
add_action( 'plugins_loaded', 'explorexr_premium_ar_addon_init' );

/**
 * Restore AR meta access if it was disabled while the addon was inactive.
 */
function explorexr_premium_ar_addon_restore_meta_access() {
	if ( class_exists( 'ExploreXR_Addon_Manager' ) ) {
		$addon_manager = ExploreXR_Addon_Manager::get_instance();
		if ( ! $addon_manager ) {
			return;
		}
		$options_manager = $addon_manager->get_options_manager();
		if ( $options_manager && $options_manager->is_addon_meta_disabled( 'ar' ) ) {
			$options_manager->restore_addon_settings( 'ar' );
		}
		return;
	}

	if ( get_option( 'explorexr_premium_ar_meta_disabled', false ) ) {
		delete_option( 'explorexr_premium_ar_meta_disabled' );
	}
}

/**
 * Display license notice.
 */
function explorexr_premium_ar_addon_license_notice() {
	if ( function_exists( 'explorexr_addon_license_notice' ) ) {
		explorexr_addon_license_notice( 'AR Add-On', 'ar' );
	}
}

/**
 * Initialize plugin update checker (plugins_loaded priority 20 per addon contract).
 */
function explorexr_ar_addon_init_updater() {
	if ( ! is_admin() ) {
		return;
	}

	foreach ( array(
		WP_PLUGIN_DIR . '/explorexr-premium/includes/shared/class-explorexr-addon-updater.php',
		WP_PLUGIN_DIR . '/explorexr/includes/shared/class-explorexr-addon-updater.php',
	) as $shared_updater ) {
		if ( file_exists( $shared_updater ) ) {
			require_once $shared_updater;
			if ( class_exists( 'ExploreXR_Addon_Updater' ) ) {
				new ExploreXR_Addon_Updater(
					__FILE__,
					'https://update.expoxr.com/explorexr/premium/addon-ar/explorexr-ar-addon.json',
					'explorexr-ar-addon'
				);
			}
			break;
		}
	}
}
add_action( 'plugins_loaded', 'explorexr_ar_addon_init_updater', 20 );

/**
 * Register frontend scripts and styles (priority 5 — register only, no enqueue).
 */
function explorexr_premium_ar_addon_register_assets() {
	// AR feature detection — must load first
	wp_register_script(
		'explorexr-premium-ar-features',
		EXPLOREXR_AR_PLUGIN_URL . 'assets/js/ar-features.js',
		array(),
		EXPLOREXR_AR_VERSION,
		true
	);

	// AR activation control — manages element visibility
	wp_register_script(
		'explorexr-premium-ar-activation-control',
		EXPLOREXR_AR_PLUGIN_URL . 'assets/js/ar-activation-control.js',
		array( 'jquery', 'explorexr-premium-ar-features' ),
		EXPLOREXR_AR_VERSION,
		true
	);

	// Main AR handler
	wp_register_script(
		'explorexr-premium-ar-handler',
		EXPLOREXR_AR_PLUGIN_URL . 'assets/js/ar-handler.js',
		array( 'jquery', 'explorexr-premium-ar-features', 'explorexr-premium-ar-activation-control' ),
		EXPLOREXR_AR_VERSION,
		true
	);

	// Enhanced AR integration
	wp_register_script(
		'explorexr-premium-ar-enhanced',
		EXPLOREXR_AR_PLUGIN_URL . 'assets/js/ar-enhanced.js',
		array( 'jquery', 'explorexr-premium-ar-handler' ),
		EXPLOREXR_AR_VERSION,
		true
	);

	// AR styles
	wp_register_style(
		'explorexr-premium-ar-styles',
		EXPLOREXR_AR_PLUGIN_URL . 'assets/css/ar-styles.css',
		array(),
		EXPLOREXR_AR_VERSION
	);
}

/**
 * Conditionally enqueue frontend AR scripts/styles (priority 10).
 * Only loads when the page contains model viewers.
 */
function explorexr_premium_ar_addon_enqueue_assets() {
	if ( is_admin() ) {
		return;
	}

	if ( ! explorexr_premium_has_model_viewers() ) {
		return;
	}

	wp_enqueue_script( 'explorexr-premium-ar-features' );
	wp_enqueue_script( 'explorexr-premium-ar-activation-control' );
	wp_enqueue_script( 'explorexr-premium-ar-handler' );
	wp_enqueue_script( 'explorexr-premium-ar-enhanced' );
	wp_enqueue_style( 'explorexr-premium-ar-styles' );

	wp_localize_script(
		'explorexr-premium-ar-activation-control',
		'explorexrARActivationSettings',
		array(
			'enabled'      => true,
			'globalEnabled' => true,
			'fallbackText' => get_option( 'explorexr_premium_ar_fallback_text', 'AR not supported on this device' ),
			'debug'        => defined( 'WP_DEBUG' ) && WP_DEBUG,
		)
	);

	wp_localize_script(
		'explorexr-premium-ar-handler',
		'explorexrARSettings',
		array(
			'ajaxurl'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'explorexr_premium_ar_nonce' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'enabled'        => true,
			'buttonText'     => get_option( 'explorexr_premium_ar_button_text', '' ),
			'fallbackText'   => get_option( 'explorexr_premium_ar_fallback_text', 'AR not supported on this device' ),
			'enableTracking' => false,
		)
	);
}

/**
 * Register and enqueue admin scripts/styles.
 *
 * @param string $hook Current admin page hook.
 */
function explorexr_premium_ar_addon_register_admin_assets( $hook ) {
	$allowed_hooks = array(
		'post.php',
		'post-new.php',
		'explorexr_premium_page_explorexr-premium-ar-settings',
		'explorexr_premium_page_explorexr-edit-model',
		'admin_page_explorexr-edit-model',
		'toplevel_page_explorexr-edit-model',
	);

	$is_explorexr_page = false;
	if ( isset( $_GET['page'] ) && strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'explorexr' ) === 0 ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_explorexr_page = true;
	}

	$is_model_edit = false;
	if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
		global $post, $typenow;
		$post_type = $typenow;
		if ( ! $post_type && $post ) {
			$post_type = $post->post_type;
		}
		if ( 'explorexr_model' === $post_type ) {
			$is_model_edit = true;
		}
	}

	if ( ! in_array( $hook, $allowed_hooks, true ) && ! $is_explorexr_page && ! $is_model_edit ) {
		return;
	}

	$is_ar_settings_page = ( 'explorexr_premium_page_explorexr-premium-ar-settings' === $hook );

	if ( $is_ar_settings_page ) {
		wp_enqueue_script(
			'explorexr-premium-ar-admin',
			EXPLOREXR_AR_PLUGIN_URL . 'assets/js/ar-admin.js',
			array( 'jquery' ),
			EXPLOREXR_AR_VERSION,
			true
		);
	}

	wp_enqueue_style(
		'explorexr-premium-ar-admin-styles',
		EXPLOREXR_AR_PLUGIN_URL . 'assets/css/ar-admin.css',
		array(),
		EXPLOREXR_AR_VERSION
	);
}

/**
 * Add settings page stub (kept for backward compatibility — global settings removed).
 */
function explorexr_premium_ar_addon_add_settings_page() {
	// All AR settings are now per-model. Kept for backward compatibility.
}

/**
 * Register addon availability with main plugin.
 *
 * @param bool   $available  Current availability.
 * @param string $addon_slug Addon slug being checked.
 * @return bool
 */
function explorexr_premium_ar_addon_register_availability( $available, $addon_slug ) {
	if ( 'explorexr-ar-addon' === $addon_slug || 'ar' === $addon_slug ) {
		return true;
	}
	return $available;
}

/**
 * Plugin activation hook.
 */
function explorexr_premium_ar_addon_activate() {
	if ( ! explorexr_premium_ar_addon_check_main_plugin() ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'ExploreXR AR Add-On requires ExploreXR Premium to be installed and activated.', 'explorexr-ar-addon' ),
			'Plugin Activation Error',
			array( 'back_link' => true )
		);
	}

	if ( class_exists( 'ExploreXR_Addon_Manager' ) ) {
		$addon_manager = ExploreXR_Addon_Manager::get_instance();
		$addon_manager->ensure_addon_options_active( 'ar' );
	} else {
		add_option( 'explorexr_premium_ar_button_text', '' );
		add_option( 'explorexr_premium_ar_fallback_text', 'AR not supported on this device' );
		add_option( 'explorexr_premium_ar_placement', 'floor' );
	}

	set_transient( 'explorexr_premium_ar_addon_activated', true, 60 );
	do_action( 'explorexr_premium_ar_addon_after_activation' );
}
register_activation_hook( __FILE__, 'explorexr_premium_ar_addon_activate' );

/**
 * Plugin deactivation hook.
 */
function explorexr_premium_ar_addon_deactivate() {
	delete_transient( 'explorexr_premium_ar_addon_activated' );
}
register_deactivation_hook( __FILE__, 'explorexr_premium_ar_addon_deactivate' );

/**
 * Display welcome notice after activation.
 */
function explorexr_premium_ar_addon_welcome_notice() {
	if ( ! get_transient( 'explorexr_premium_ar_addon_activated' ) ) {
		return;
	}

	delete_transient( 'explorexr_premium_ar_addon_activated' );
	?>
	<div class="notice notice-success is-dismissible">
		<p>
			<strong><?php esc_html_e( 'ExploreXR AR Add-On activated!', 'explorexr-ar-addon' ); ?></strong>
			<?php esc_html_e( 'You can now enable augmented reality features for your 3D models.', 'explorexr-ar-addon' ); ?>
		</p>
		<?php
		$explorexr_models_url = ( defined( 'EXPLOREXR_IS_PREMIUM' ) && EXPLOREXR_IS_PREMIUM )
			? admin_url( 'edit.php?post_type=explorexr_premium_model' )
			: admin_url( 'edit.php?post_type=explorexr_model' );
		?>
		<p>
			<a href="<?php echo esc_url( $explorexr_models_url ); ?>" class="button button-primary">
				<?php esc_html_e( 'Edit Models', 'explorexr-ar-addon' ); ?>
			</a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'explorexr_premium_ar_addon_welcome_notice' );

/**
 * Render per-model AR settings card.
 *
 * @param int $model_id The model post ID.
 */
function explorexr_ar_addon_settings( $model_id ) {
	$post = get_post( $model_id );

	if ( ! $post ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid model ID', 'explorexr-ar-addon' ) . '</p></div>';
		return;
	}

	$model_file = get_post_meta( $model_id, '_explorexr_model_file', true );

	$template_file = EXPLOREXR_AR_PLUGIN_DIR . 'templates/admin/ar-options-card.php';
	if ( file_exists( $template_file ) ) {
		include $template_file;
	} else {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'AR settings template not found.', 'explorexr-ar-addon' ) . '</p></div>';
	}
}
