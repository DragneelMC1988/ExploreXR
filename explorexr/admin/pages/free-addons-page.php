<?php
/**
 * ExploreXR Free — Free Add-on Admin Page
 *
 * @package ExploreXR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Free Add-on admin page.
 */
function explorexr_free_addons_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'explorexr' ) );
	}

	$selected_addon = explorexr_free_get_selected_addon();

	$addons = array(
		'ar'          => array(
			'name'        => __( 'AR (Augmented Reality)', 'explorexr' ),
			'description' => __( 'Let visitors view your 3D models in their real environment using their phone camera. Works on iOS (USDZ) and Android (WebXR).', 'explorexr' ),
			'icon'        => 'dashicons-smartphone',
		),
		'animation'   => array(
			'name'        => __( 'Animation', 'explorexr' ),
			'description' => __( 'Play, pause and loop GLTF animations embedded in your 3D models. Add playback controls and ping-pong support.', 'explorexr' ),
			'icon'        => 'dashicons-controls-play',
		),
		'camera'      => array(
			'name'        => __( 'Camera Controls', 'explorexr' ),
			'description' => __( 'Fine-tune orbit sensitivity, zoom, and pan behaviour per model. Give your visitors a polished navigation experience.', 'explorexr' ),
			'icon'        => 'dashicons-camera',
		),
		'annotations' => array(
			'name'        => __( 'Annotations', 'explorexr' ),
			'description' => __( 'Pin interactive hotspot labels to specific points on your 3D model. Great for product explainers and guided tours.', 'explorexr' ),
			'icon'        => 'dashicons-admin-comments',
		),
	);

	$hint_dismissed = (bool) get_user_meta( get_current_user_id(), 'explorexr_free_addon_hint_dismissed', true );

	include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-header.php';
	?>
	<div class="wrap explorexr-free-addons-wrap">

		<?php if ( ! $hint_dismissed ) : ?>
		<div class="notice notice-info explorexr-new-feature-notice is-dismissible" id="explorexr-new-feature-notice">
			<p>
				<strong><?php esc_html_e( 'New: Free Add-on!', 'explorexr' ); ?></strong>
				<?php esc_html_e( 'You can now activate one premium add-on completely free. Choose AR, Animation, Camera Controls, or Annotations below.', 'explorexr' ); ?>
			</p>
		</div>
		<?php endif; ?>

		<h1 class="explorexr-page-title">
			<span class="dashicons dashicons-admin-plugins"></span>
			<?php esc_html_e( 'Free Add-on', 'explorexr' ); ?>
		</h1>

		<p class="explorexr-free-addons-intro">
			<?php esc_html_e( 'Choose one premium add-on to use for free. Select the one that best fits your project — you can change your selection at any time.', 'explorexr' ); ?>
		</p>

		<?php if ( '' !== $selected_addon ) : ?>
		<div class="explorexr-free-active-notice">
			<span class="dashicons dashicons-yes-alt"></span>
			<?php
			$active_name = isset( $addons[ $selected_addon ] ) ? $addons[ $selected_addon ]['name'] : $selected_addon;
			/* translators: %s: Add-on name */
			printf( esc_html__( 'Currently active: %s', 'explorexr' ), '<strong>' . esc_html( $active_name ) . '</strong>' );
			?>
		</div>
		<?php endif; ?>

		<div class="explorexr-free-addons-grid">
			<?php foreach ( $addons as $slug => $addon ) :
				$is_selected  = ( $slug === $selected_addon );
				$is_installed = explorexr_free_is_addon_installed( $slug );
				$is_active    = explorexr_free_is_addon_active( $slug );
				?>
			<div class="explorexr-free-addon-card <?php echo $is_selected ? 'is-selected' : ''; ?>">
				<div class="explorexr-free-addon-card__icon">
					<span class="dashicons <?php echo esc_attr( $addon['icon'] ); ?>"></span>
				</div>
				<div class="explorexr-free-addon-card__body">
					<h3 class="explorexr-free-addon-card__name"><?php echo esc_html( $addon['name'] ); ?></h3>
					<p class="explorexr-free-addon-card__desc"><?php echo esc_html( $addon['description'] ); ?></p>

					<div class="explorexr-free-addon-card__status">
						<?php if ( $is_selected ) : ?>
							<span class="explorexr-badge explorexr-badge--active"><?php esc_html_e( 'Active &amp; Selected', 'explorexr' ); ?></span>
						<?php elseif ( $is_active ) : ?>
							<span class="explorexr-badge explorexr-badge--installed"><?php esc_html_e( 'Installed', 'explorexr' ); ?></span>
						<?php elseif ( $is_installed ) : ?>
							<span class="explorexr-badge explorexr-badge--installed"><?php esc_html_e( 'Installed', 'explorexr' ); ?></span>
						<?php else : ?>
							<span class="explorexr-badge explorexr-badge--not-installed"><?php esc_html_e( 'Not Installed', 'explorexr' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="explorexr-free-addon-card__actions">
					<?php if ( $is_selected ) : ?>
						<button class="button button-secondary" disabled>
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Active', 'explorexr' ); ?>
						</button>
					<?php elseif ( $is_installed ) : ?>
						<button
							class="button button-primary explorexr-select-addon-btn"
							data-slug="<?php echo esc_attr( $slug ); ?>"
							data-action="select"
						>
							<?php esc_html_e( 'Select', 'explorexr' ); ?>
						</button>
					<?php else : ?>
						<button
							class="button button-primary explorexr-install-addon-btn"
							data-slug="<?php echo esc_attr( $slug ); ?>"
							data-action="install"
						>
							<span class="dashicons dashicons-download"></span>
							<?php esc_html_e( 'Install &amp; Select', 'explorexr' ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div><!-- .explorexr-free-addons-grid -->

		<div class="explorexr-free-addons-footer">
			<p>
				<?php
				printf(
					/* translators: %s: link to premium upgrade page */
					esc_html__( 'Need more than one add-on? %s to unlock up to 3 (Pro), 5 (Plus), or all (Ultra).', 'explorexr' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=explorexr-premium' ) ) . '">'
						. esc_html__( 'Go Premium', 'explorexr' )
					. '</a>'
				);
				?>
			</p>
		</div>

	</div><!-- .explorexr-free-addons-wrap -->
	<?php
	include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-footer.php';
}
