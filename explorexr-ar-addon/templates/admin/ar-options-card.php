<?php
/**
 * AR Options Card Template
 *
 * Three-tab layout:
 *   Tab 1 — AR Settings     (modes, placement, scale, auto-rotate)
 *   Tab 2 — Button Styling  (preview + text, icon, colors, size, position)
 *   Tab 3 — Files & Advanced (USDZ, XR environment, min height)
 *
 * @package ExploreXR
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template scope vars
$ar_enabled               = get_post_meta( $model_id, '_explorexr_premium_ar_enabled', true ) === 'on';
$ar_modes                 = get_post_meta( $model_id, '_explorexr_premium_ar_modes', true );
if ( ! is_array( $ar_modes ) ) {
	$ar_modes = array( 'webxr', 'scene-viewer', 'quick-look' );
}
$ar_placement             = get_post_meta( $model_id, '_explorexr_premium_ar_placement', true ) ?: 'floor';
$ar_scale                 = get_post_meta( $model_id, '_explorexr_premium_ar_scale', true ) ?: 'auto';
$ar_auto_rotate           = get_post_meta( $model_id, '_explorexr_premium_ar_auto_rotate', true ) === 'on';
$ar_usdz_model            = get_post_meta( $model_id, '_explorexr_premium_ar_usdz_model', true ) ?: '';
$ar_xr_environment        = get_post_meta( $model_id, '_explorexr_premium_ar_xr_environment', true ) ?: '';
$ar_min_height            = get_post_meta( $model_id, '_explorexr_premium_ar_min_height', true ) ?: '';
$ar_button_text           = get_post_meta( $model_id, '_explorexr_premium_ar_button_text', true ) ?: '';
$ar_button_icon           = get_post_meta( $model_id, '_explorexr_premium_ar_button_icon', true ) ?: '';
$ar_button_icon_position  = get_post_meta( $model_id, '_explorexr_premium_ar_button_icon_position', true ) ?: 'left';
$ar_button_icon_enabled_raw = get_post_meta( $model_id, '_explorexr_premium_ar_button_icon_enabled', true );
$ar_button_icon_enabled   = '1' === $ar_button_icon_enabled_raw;
$ar_button_bg_color       = get_post_meta( $model_id, '_explorexr_premium_ar_button_bg_color', true ) ?: '#000000';
$ar_button_text_color     = get_post_meta( $model_id, '_explorexr_premium_ar_button_text_color', true ) ?: '#ffffff';
$ar_button_border_color   = get_post_meta( $model_id, '_explorexr_premium_ar_button_border_color', true ) ?: '';
$ar_button_size           = get_post_meta( $model_id, '_explorexr_premium_ar_button_size', true ) ?: 'medium';
$ar_button_border_radius  = get_post_meta( $model_id, '_explorexr_premium_ar_button_border_radius', true );
if ( '' === $ar_button_border_radius || false === $ar_button_border_radius ) {
	$ar_button_border_radius = '4';
}
$ar_button_position = get_post_meta( $model_id, '_explorexr_premium_ar_button_position', true ) ?: 'bottom-center';

// Count configured items for badges.
$modes_count    = count( $ar_modes );
$has_usdz       = ! empty( $ar_usdz_model ) || ! empty( $ar_xr_environment );

// Ensure media library available.
if ( function_exists( 'wp_enqueue_media' ) ) {
	wp_enqueue_media();
}
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>

<?php if ( empty( $model_file ) ) : ?>
	<div class="notice notice-warning inline">
		<p><?php esc_html_e( 'Please add a 3D model first before configuring AR options.', 'explorexr-ar-addon' ); ?></p>
	</div>
<?php else : ?>

<div class="explorexr-ar-card" id="explorexr-ar-card">
	<input type="hidden" name="explorexr_premium_ar_present" value="1">

	<!-- ── Master Enable Row ───────────────────────────────────────────── -->
	<div class="explorexr-ar-top-row">
		<div class="explorexr-ar-enable-row">
			<label class="explorexr-premium-checkbox-label">
				<input type="checkbox" name="explorexr_premium_ar_enabled" id="explorexr_premium_ar_enabled" <?php checked( $ar_enabled, true ); ?>>
				<span><?php esc_html_e( 'Enable Augmented Reality', 'explorexr-ar-addon' ); ?></span>
			</label>
			<p class="description"><?php esc_html_e( 'Allow users to view this model in AR on compatible mobile devices.', 'explorexr-ar-addon' ); ?></p>
		</div>
	</div>

	<div id="explorexr-ar-card-body" <?php echo $ar_enabled ? '' : 'style="display:none;"'; ?>>

		<!-- ── Tab Navigation ──────────────────────────────────────────── -->
		<div class="explorexr-ar-tabs">
			<button type="button" class="explorexr-ar-tab is-active" data-tab="ar-settings">
				<span class="dashicons dashicons-admin-settings"></span>
				<?php esc_html_e( 'AR Settings', 'explorexr-ar-addon' ); ?>
				<?php if ( $modes_count > 0 ) : ?>
					<span class="explorexr-ar-tab-badge"><?php echo esc_html( $modes_count ); ?></span>
				<?php endif; ?>
			</button>
			<button type="button" class="explorexr-ar-tab" data-tab="button-styling">
				<span class="dashicons dashicons-admin-customizer"></span>
				<?php esc_html_e( 'Button Styling', 'explorexr-ar-addon' ); ?>
			</button>
			<button type="button" class="explorexr-ar-tab" data-tab="files-advanced">
				<span class="dashicons dashicons-upload"></span>
				<?php esc_html_e( 'Files & Advanced', 'explorexr-ar-addon' ); ?>
				<?php if ( $has_usdz ) : ?>
					<span class="explorexr-ar-tab-badge explorexr-ar-tab-badge--active"><?php esc_html_e( 'Set', 'explorexr-ar-addon' ); ?></span>
				<?php endif; ?>
			</button>
		</div>

		<!-- ══════════════════════════════════════════════════════════════
		     TAB 1 — AR Settings
		     ══════════════════════════════════════════════════════════════ -->
		<div class="explorexr-ar-tab-content is-active" data-tab="ar-settings">

			<div class="explorexr-ar-tab-intro">
				<p><?php esc_html_e( 'Configure AR technology, placement, and behaviour for this model.', 'explorexr-ar-addon' ); ?></p>
			</div>

			<!-- AR Modes -->
			<div class="explorexr-premium-form-group">
				<label class="explorexr-ar-field-label"><?php esc_html_e( 'AR Modes', 'explorexr-ar-addon' ); ?></label>
				<div class="explorexr-premium-checkbox-group">
					<label class="explorexr-premium-checkbox-label">
						<input type="checkbox" name="explorexr_premium_ar_modes[]" value="webxr" <?php checked( in_array( 'webxr', $ar_modes, true ) ); ?>>
						<span><?php esc_html_e( 'WebXR (AR in web browsers)', 'explorexr-ar-addon' ); ?></span>
					</label>
					<label class="explorexr-premium-checkbox-label">
						<input type="checkbox" name="explorexr_premium_ar_modes[]" value="scene-viewer" <?php checked( in_array( 'scene-viewer', $ar_modes, true ) ); ?>>
						<span><?php esc_html_e( 'Scene Viewer (Android)', 'explorexr-ar-addon' ); ?></span>
					</label>
					<label class="explorexr-premium-checkbox-label">
						<input type="checkbox" name="explorexr_premium_ar_modes[]" value="quick-look" <?php checked( in_array( 'quick-look', $ar_modes, true ) ); ?>>
						<span><?php esc_html_e( 'Quick Look (iOS)', 'explorexr-ar-addon' ); ?></span>
					</label>
				</div>
				<p class="description"><?php esc_html_e( 'Enable all for maximum device compatibility. Uncheck a mode only to exclude that platform.', 'explorexr-ar-addon' ); ?></p>
			</div>

			<!-- AR Placement -->
			<div class="explorexr-premium-form-group">
				<label for="explorexr_premium_ar_placement" class="explorexr-ar-field-label"><?php esc_html_e( 'AR Placement', 'explorexr-ar-addon' ); ?></label>
				<select name="explorexr_premium_ar_placement" id="explorexr_premium_ar_placement" class="regular-text">
					<option value="floor" <?php selected( $ar_placement, 'floor' ); ?>><?php esc_html_e( 'Floor', 'explorexr-ar-addon' ); ?></option>
					<option value="wall" <?php selected( $ar_placement, 'wall' ); ?>><?php esc_html_e( 'Wall', 'explorexr-ar-addon' ); ?></option>
					<option value="table" <?php selected( $ar_placement, 'table' ); ?>><?php esc_html_e( 'Table / Flat Surface', 'explorexr-ar-addon' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Where the model should be placed in the real world when AR starts.', 'explorexr-ar-addon' ); ?></p>
			</div>

			<!-- AR Scale -->
			<div class="explorexr-premium-form-group">
				<label class="explorexr-premium-checkbox-label">
					<input type="checkbox" name="explorexr_premium_ar_allow_scaling" id="explorexr_premium_ar_allow_scaling" <?php checked( 'fixed' !== $ar_scale ); ?>>
					<span><?php esc_html_e( 'Allow scaling in AR (pinch to resize)', 'explorexr-ar-addon' ); ?></span>
				</label>
				<p class="description"><?php esc_html_e( 'Unchecked = fixed real-world size. Checked = user can resize with pinch gesture.', 'explorexr-ar-addon' ); ?></p>
			</div>

			<!-- AR Auto-rotate -->
			<div class="explorexr-premium-form-group">
				<label class="explorexr-premium-checkbox-label">
					<input type="checkbox" name="explorexr_premium_ar_auto_rotate" id="explorexr_premium_ar_auto_rotate" <?php checked( $ar_auto_rotate ); ?>>
					<span><?php esc_html_e( 'Auto-rotate in AR', 'explorexr-ar-addon' ); ?></span>
				</label>
				<p class="description"><?php esc_html_e( 'Automatically spin the model when placed in AR.', 'explorexr-ar-addon' ); ?></p>
			</div>

			<!-- Device Compatibility Info -->
			<div class="explorexr-ar-compat-notice">
				<p><strong><?php esc_html_e( 'Device Compatibility', 'explorexr-ar-addon' ); ?></strong></p>
				<ul>
					<li><strong><?php esc_html_e( 'iOS:', 'explorexr-ar-addon' ); ?></strong> <?php esc_html_e( 'iPhone/iPad with iOS 12+ using Safari (Quick Look)', 'explorexr-ar-addon' ); ?></li>
					<li><strong><?php esc_html_e( 'Android:', 'explorexr-ar-addon' ); ?></strong> <?php esc_html_e( 'ARCore-compatible devices with Android 8.0+ using Chrome (Scene Viewer)', 'explorexr-ar-addon' ); ?></li>
					<li><strong><?php esc_html_e( 'WebXR:', 'explorexr-ar-addon' ); ?></strong> <?php esc_html_e( 'Supported browsers on desktop and mobile', 'explorexr-ar-addon' ); ?></li>
				</ul>
			</div>

		</div><!-- /TAB 1 -->

		<!-- ══════════════════════════════════════════════════════════════
		     TAB 2 — Button Styling
		     ══════════════════════════════════════════════════════════════ -->
		<div class="explorexr-ar-tab-content" data-tab="button-styling">

			<div class="explorexr-ar-tab-intro">
				<p><?php esc_html_e( 'Customise the AR button appearance. The live preview updates as you change settings.', 'explorexr-ar-addon' ); ?></p>
			</div>

			<!-- Live Preview -->
			<div class="explorexr-ar-preview-block">
				<div class="explorexr-ar-preview-header">
					<h4><span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Live Preview', 'explorexr-ar-addon' ); ?></h4>
					<div class="explorexr-ar-device-toggle">
						<button type="button" class="explorexr-device-btn active" data-device="mobile">
							<span class="dashicons dashicons-smartphone"></span> <?php esc_html_e( 'Mobile', 'explorexr-ar-addon' ); ?>
						</button>
						<button type="button" class="explorexr-device-btn" data-device="tablet">
							<span class="dashicons dashicons-tablet"></span> <?php esc_html_e( 'Tablet', 'explorexr-ar-addon' ); ?>
						</button>
					</div>
				</div>
				<div class="explorexr-ar-canvas-wrapper">
					<div id="explorexr-ar-canvas" class="explorexr-ar-canvas mobile">
						<div class="explorexr-ar-canvas-label"><?php esc_html_e( 'Mobile View (375 × 667 px)', 'explorexr-ar-addon' ); ?></div>
						<!-- AR button injected here by JavaScript -->
					</div>
				</div>
			</div>

			<!-- Button Settings -->
			<div class="explorexr-ar-styling-grid">

				<!-- Button Text -->
				<div class="explorexr-premium-form-group">
					<label for="explorexr_premium_ar_button_text" class="explorexr-ar-field-label"><?php esc_html_e( 'Button Text', 'explorexr-ar-addon' ); ?></label>
					<input type="text" id="explorexr_premium_ar_button_text" name="explorexr_premium_ar_button_text" value="<?php echo esc_attr( $ar_button_text ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'View in AR (default)', 'explorexr-ar-addon' ); ?>">
					<p class="description"><?php esc_html_e( 'Leave empty to use the default "View in AR" label.', 'explorexr-ar-addon' ); ?></p>
				</div>

				<!-- Button Size -->
				<div class="explorexr-premium-form-group">
					<label for="explorexr_premium_ar_button_size" class="explorexr-ar-field-label"><?php esc_html_e( 'Button Size', 'explorexr-ar-addon' ); ?></label>
					<select id="explorexr_premium_ar_button_size" name="explorexr_premium_ar_button_size">
						<option value="small"  <?php selected( $ar_button_size, 'small' ); ?>><?php esc_html_e( 'Small', 'explorexr-ar-addon' ); ?></option>
						<option value="medium" <?php selected( $ar_button_size, 'medium' ); ?>><?php esc_html_e( 'Medium', 'explorexr-ar-addon' ); ?></option>
						<option value="large"  <?php selected( $ar_button_size, 'large' ); ?>><?php esc_html_e( 'Large', 'explorexr-ar-addon' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Controls padding and font size of the AR button.', 'explorexr-ar-addon' ); ?></p>
				</div>

				<!-- Button Position -->
				<div class="explorexr-premium-form-group">
					<label for="explorexr_premium_ar_button_position" class="explorexr-ar-field-label"><?php esc_html_e( 'Button Position', 'explorexr-ar-addon' ); ?></label>
					<select id="explorexr_premium_ar_button_position" name="explorexr_premium_ar_button_position">
						<option value="bottom-center" <?php selected( $ar_button_position, 'bottom-center' ); ?>><?php esc_html_e( 'Bottom Center', 'explorexr-ar-addon' ); ?></option>
						<option value="bottom-left"   <?php selected( $ar_button_position, 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'explorexr-ar-addon' ); ?></option>
						<option value="bottom-right"  <?php selected( $ar_button_position, 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'explorexr-ar-addon' ); ?></option>
						<option value="top-left"      <?php selected( $ar_button_position, 'top-left' ); ?>><?php esc_html_e( 'Top Left', 'explorexr-ar-addon' ); ?></option>
						<option value="top-right"     <?php selected( $ar_button_position, 'top-right' ); ?>><?php esc_html_e( 'Top Right', 'explorexr-ar-addon' ); ?></option>
						<option value="center-left"   <?php selected( $ar_button_position, 'center-left' ); ?>><?php esc_html_e( 'Center Left', 'explorexr-ar-addon' ); ?></option>
						<option value="center-right"  <?php selected( $ar_button_position, 'center-right' ); ?>><?php esc_html_e( 'Center Right', 'explorexr-ar-addon' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Where the AR button appears inside the model viewer.', 'explorexr-ar-addon' ); ?></p>
				</div>

				<!-- Border Radius -->
				<div class="explorexr-premium-form-group">
					<label for="explorexr_premium_ar_button_border_radius" class="explorexr-ar-field-label"><?php esc_html_e( 'Border Radius (px)', 'explorexr-ar-addon' ); ?></label>
					<input type="number" id="explorexr_premium_ar_button_border_radius" name="explorexr_premium_ar_button_border_radius" value="<?php echo esc_attr( $ar_button_border_radius ); ?>" min="0" max="50" step="1" class="small-text">
					<p class="description"><?php esc_html_e( 'Corner rounding. 0 = square, higher = rounder.', 'explorexr-ar-addon' ); ?></p>
				</div>

				<!-- Background Color -->
				<div class="explorexr-premium-form-group">
					<label for="explorexr_premium_ar_button_bg_color" class="explorexr-ar-field-label"><?php esc_html_e( 'Background Color', 'explorexr-ar-addon' ); ?></label>
					<input type="color" id="explorexr_premium_ar_button_bg_color" name="explorexr_premium_ar_button_bg_color" value="<?php echo esc_attr( $ar_button_bg_color ); ?>" class="explorexr-ar-color-picker">
					<p class="description"><?php esc_html_e( 'AR button background color.', 'explorexr-ar-addon' ); ?></p>
				</div>

				<!-- Text Color -->
				<div class="explorexr-premium-form-group">
					<label for="explorexr_premium_ar_button_text_color" class="explorexr-ar-field-label"><?php esc_html_e( 'Text Color', 'explorexr-ar-addon' ); ?></label>
					<input type="color" id="explorexr_premium_ar_button_text_color" name="explorexr_premium_ar_button_text_color" value="<?php echo esc_attr( $ar_button_text_color ); ?>" class="explorexr-ar-color-picker">
					<p class="description"><?php esc_html_e( 'AR button label color.', 'explorexr-ar-addon' ); ?></p>
				</div>

				<!-- Border Color -->
				<div class="explorexr-premium-form-group">
					<label for="explorexr_premium_ar_button_border_color" class="explorexr-ar-field-label"><?php esc_html_e( 'Border Color', 'explorexr-ar-addon' ); ?></label>
					<input type="color" id="explorexr_premium_ar_button_border_color" name="explorexr_premium_ar_button_border_color" value="<?php echo esc_attr( $ar_button_border_color ?: '#000000' ); ?>" class="explorexr-ar-color-picker">
					<p class="description"><?php esc_html_e( 'Leave default if no border is needed.', 'explorexr-ar-addon' ); ?></p>
				</div>

			</div><!-- /explorexr-ar-styling-grid -->

			<!-- Icon Section -->
			<div class="explorexr-ar-icon-section">
				<div class="explorexr-premium-form-group">
					<label class="explorexr-premium-checkbox-label">
						<input type="checkbox" id="explorexr_premium_ar_button_icon_enabled" name="explorexr_premium_ar_button_icon_enabled" value="1" <?php checked( $ar_button_icon_enabled, true ); ?>>
						<span><?php esc_html_e( 'Show an icon on the button', 'explorexr-ar-addon' ); ?></span>
					</label>
					<p class="description"><?php esc_html_e( 'Enable to show an icon alongside the button text.', 'explorexr-ar-addon' ); ?></p>
				</div>

				<div id="explorexr-ar-icon-options" <?php echo $ar_button_icon_enabled ? '' : 'style="display:none;"'; ?>>

					<div class="explorexr-premium-form-group">
						<label for="explorexr_premium_ar_button_icon" class="explorexr-ar-field-label"><?php esc_html_e( 'Icon (class, SVG, or image URL)', 'explorexr-ar-addon' ); ?></label>
						<div class="explorexr-ar-upload-field">
							<input type="text" id="explorexr_premium_ar_button_icon" name="explorexr_premium_ar_button_icon" value="<?php echo esc_attr( $ar_button_icon ); ?>" class="regular-text" placeholder="dashicons-format-gallery, &lt;svg&gt;…, or https://…">
							<button type="button" class="button explorexr-premium-icon-upload-button" data-target="explorexr_premium_ar_button_icon"><?php esc_html_e( 'Choose Image', 'explorexr-ar-addon' ); ?></button>
							<?php if ( ! empty( $ar_button_icon ) ) : ?>
								<button type="button" class="button explorexr-premium-icon-remove-button" data-target="explorexr_premium_ar_button_icon"><?php esc_html_e( 'Remove', 'explorexr-ar-addon' ); ?></button>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $ar_button_icon ) && filter_var( $ar_button_icon, FILTER_VALIDATE_URL ) ) : ?>
							<div class="explorexr-ar-icon-preview" style="margin-top:6px;">
								<img src="<?php echo esc_url( $ar_button_icon ); ?>" alt="" style="max-height:40px; max-width:120px; border-radius:4px;">
							</div>
						<?php elseif ( ! empty( $ar_button_icon ) ) : ?>
							<p class="description"><?php echo esc_html__( 'Current icon: ', 'explorexr-ar-addon' ) . '<code>' . esc_html( $ar_button_icon ) . '</code>'; ?></p>
						<?php endif; ?>
						<p class="description"><?php esc_html_e( 'Use a Dashicon class name, inline SVG, or image URL.', 'explorexr-ar-addon' ); ?></p>
					</div>

					<div class="explorexr-premium-form-group">
						<label for="explorexr_premium_ar_button_icon_position" class="explorexr-ar-field-label"><?php esc_html_e( 'Icon Position', 'explorexr-ar-addon' ); ?></label>
						<select id="explorexr_premium_ar_button_icon_position" name="explorexr_premium_ar_button_icon_position">
							<option value="left"  <?php selected( $ar_button_icon_position, 'left' ); ?>><?php esc_html_e( 'Left of text', 'explorexr-ar-addon' ); ?></option>
							<option value="right" <?php selected( $ar_button_icon_position, 'right' ); ?>><?php esc_html_e( 'Right of text', 'explorexr-ar-addon' ); ?></option>
						</select>
					</div>

				</div><!-- /icon-options -->
			</div><!-- /icon-section -->

		</div><!-- /TAB 2 -->

		<!-- ══════════════════════════════════════════════════════════════
		     TAB 3 — Files & Advanced
		     ══════════════════════════════════════════════════════════════ -->
		<div class="explorexr-ar-tab-content" data-tab="files-advanced">

			<div class="explorexr-ar-tab-intro">
				<p><?php esc_html_e( 'Upload platform-specific files and fine-tune advanced AR behaviour.', 'explorexr-ar-addon' ); ?></p>
			</div>

			<!-- USDZ Source -->
			<div class="explorexr-premium-form-group">
				<label for="explorexr_premium_ar_usdz_model" class="explorexr-ar-field-label">
					<?php esc_html_e( 'iOS USDZ File', 'explorexr-ar-addon' ); ?>
					<span class="explorexr-ar-label-hint"><?php esc_html_e( '(optional)', 'explorexr-ar-addon' ); ?></span>
				</label>
				<div class="explorexr-ar-upload-field">
					<input type="text" id="explorexr_premium_ar_usdz_model" name="explorexr_premium_ar_usdz_model" value="<?php echo esc_attr( $ar_usdz_model ); ?>" class="regular-text" placeholder="https://example.com/model.usdz">
					<button type="button" class="button explorexr-premium-upload-button" data-target="explorexr_premium_ar_usdz_model"><?php esc_html_e( 'Upload', 'explorexr-ar-addon' ); ?></button>
					<?php
					// Show existing USDZ attachments in a quick-select dropdown.
					$existing_usdz = new WP_Query(
						array(
							'post_type'      => 'attachment',
							'post_status'    => 'inherit',
							'posts_per_page' => 50,
							'post_mime_type' => array( 'model/vnd.usdz+zip', 'application/octet-stream', 'application/zip' ),
						)
					);
					if ( $existing_usdz->have_posts() ) :
						?>
						<select id="explorexr_premium_ar_usdz_existing" class="regular-text" title="<?php esc_attr_e( 'Choose from existing USDZ files', 'explorexr-ar-addon' ); ?>">
							<option value=""><?php esc_html_e( '— Choose existing USDZ —', 'explorexr-ar-addon' ); ?></option>
							<?php foreach ( $existing_usdz->posts as $usdz_post ) : ?>
								<option value="<?php echo esc_url( wp_get_attachment_url( $usdz_post->ID ) ); ?>">
									<?php echo esc_html( $usdz_post->post_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					<?php
					endif;
					wp_reset_postdata();
					?>
				</div>
				<?php if ( ! empty( $ar_usdz_model ) ) : ?>
					<div class="explorexr-ar-file-preview" style="margin-top:6px;">
						<a href="<?php echo esc_url( $ar_usdz_model ); ?>" target="_blank" rel="noopener noreferrer" class="explorexr-premium-media-preview-view">
							<?php esc_html_e( 'View current USDZ', 'explorexr-ar-addon' ); ?> ↗
						</a>
					</div>
				<?php endif; ?>
				<p class="description"><?php esc_html_e( 'For better iOS AR support, provide a USDZ version of your model. If empty, the main GLB file is used via Quick Look (may not work on all iOS versions).', 'explorexr-ar-addon' ); ?></p>
			</div>

			<!-- XR Environment Image -->
			<div class="explorexr-premium-form-group">
				<label for="explorexr_premium_ar_xr_environment" class="explorexr-ar-field-label">
					<?php esc_html_e( 'XR Environment Image', 'explorexr-ar-addon' ); ?>
					<span class="explorexr-ar-label-hint"><?php esc_html_e( '(optional)', 'explorexr-ar-addon' ); ?></span>
				</label>
				<div class="explorexr-ar-upload-field">
					<input type="text" id="explorexr_premium_ar_xr_environment" name="explorexr_premium_ar_xr_environment" value="<?php echo esc_attr( $ar_xr_environment ); ?>" class="regular-text" placeholder="https://example.com/environment.hdr">
					<button type="button" class="button explorexr-premium-upload-button" data-target="explorexr_premium_ar_xr_environment"><?php esc_html_e( 'Upload', 'explorexr-ar-addon' ); ?></button>
				</div>
				<p class="description"><?php esc_html_e( 'HDR environment map for lighting the model in AR. Leave empty for the device default environment.', 'explorexr-ar-addon' ); ?></p>
			</div>

			<!-- Min Height -->
			<div class="explorexr-premium-form-group">
				<label for="explorexr_premium_ar_min_height" class="explorexr-ar-field-label"><?php esc_html_e( 'Minimum Viewer Height', 'explorexr-ar-addon' ); ?></label>
				<input type="text" id="explorexr_premium_ar_min_height" name="explorexr_premium_ar_min_height" value="<?php echo esc_attr( $ar_min_height ); ?>" class="regular-text" placeholder="400px">
				<p class="description"><?php esc_html_e( 'Minimum height (CSS value, e.g. 400px) of the model viewer when in AR mode on mobile. Leave empty to inherit the viewer\'s default height.', 'explorexr-ar-addon' ); ?></p>
			</div>

		</div><!-- /TAB 3 -->

	</div><!-- /#explorexr-ar-card-body -->
</div><!-- /.explorexr-ar-card -->

<script>
(function($) {
	'use strict';

	$(document).ready(function() {

		// ── Enable/disable card body ──────────────────────────────────────
		$('#explorexr_premium_ar_enabled').on('change', function() {
			const checked = $(this).is(':checked');
			$('#explorexr-ar-card-body').toggle(checked);
			if (checked) {
				setTimeout(renderARButtonPreview, 100);
			} else {
				$('#ar-btn-preview').remove();
			}
		});

		// ── Tab switching ─────────────────────────────────────────────────
		$('.explorexr-ar-tab').on('click', function() {
			const tab = $(this).data('tab');
			$('.explorexr-ar-tab').removeClass('is-active');
			$(this).addClass('is-active');
			$('.explorexr-ar-tab-content').removeClass('is-active');
			$('.explorexr-ar-tab-content[data-tab="' + tab + '"]').addClass('is-active');
			if ('button-styling' === tab) {
				renderARButtonPreview();
			}
		});

		// ── Device toggle ─────────────────────────────────────────────────
		$('.explorexr-device-btn').on('click', function() {
			const device = $(this).data('device');
			$('.explorexr-device-btn').removeClass('active');
			$(this).addClass('active');
			const $canvas = $('#explorexr-ar-canvas');
			$canvas.removeClass('mobile tablet').addClass(device);
			$canvas.find('.explorexr-ar-canvas-label').text(
				'tablet' === device ? 'Tablet View (768 × 1024 px)' : 'Mobile View (375 × 667 px)'
			);
		});

		// ── Icon toggle ───────────────────────────────────────────────────
		$('#explorexr_premium_ar_button_icon_enabled').on('change', function() {
			$('#explorexr-ar-icon-options').toggle($(this).is(':checked'));
			$('#ar-btn-preview').remove();
			renderARButtonPreview();
		});

		// ── Live preview ──────────────────────────────────────────────────
		function renderARButtonPreview() {
			const $canvas = $('#explorexr-ar-canvas');
			$('#ar-btn-preview').remove();

			const position    = $('#explorexr_premium_ar_button_position').val()      || 'bottom-center';
			const size        = $('#explorexr_premium_ar_button_size').val()           || 'medium';
			const bgColor     = $('#explorexr_premium_ar_button_bg_color').val()       || '#000000';
			const textColor   = $('#explorexr_premium_ar_button_text_color').val()     || '#ffffff';
			const borderColor = $('#explorexr_premium_ar_button_border_color').val()   || '';
			const radius      = $('#explorexr_premium_ar_button_border_radius').val()  || '4';
			const buttonText  = $('#explorexr_premium_ar_button_text').val()           || 'View in AR';
			const iconEnabled = $('#explorexr_premium_ar_button_icon_enabled').is(':checked');
			const icon        = $('#explorexr_premium_ar_button_icon').val()           || '';
			const iconPos     = $('#explorexr_premium_ar_button_icon_position').val()  || 'left';

			const $btn = $('<button id="ar-btn-preview" type="button"></button>');
			$canvas.css('position', 'relative');

			// Padding and font by size.
			const padding  = { small: '6px 12px',  medium: '8px 16px',  large: '12px 24px' };
			const fontSize = { small: '12px',       medium: '14px',       large: '16px' };
			$btn.css({
				position:        'absolute',
				zIndex:          5,
				backgroundColor: bgColor,
				color:           textColor,
				border:          borderColor ? ('2px solid ' + borderColor) : 'none',
				borderRadius:    radius + 'px',
				padding:         (padding[size] || padding.medium),
				fontSize:        (fontSize[size] || fontSize.medium),
				fontFamily:      '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
				fontWeight:      '600',
				cursor:          'default',
				boxShadow:       '0 2px 8px rgba(0,0,0,.2)',
			});

			// Build inner content.
			if (iconEnabled && icon) {
				let iconHtml;
				if (icon.indexOf('<svg') !== -1) {
					iconHtml = icon;
				} else if (/^https?:\/\//i.test(icon)) {
					iconHtml = '<img src="' + icon + '" alt="" style="width:20px;height:20px;vertical-align:middle;">';
				} else {
					iconHtml = '<span class="' + icon + '" aria-hidden="true" style="font-size:16px;"></span>';
				}
				const textSpan = '<span>' + buttonText + '</span>';
				$btn.html('right' === iconPos ? (textSpan + ' ' + iconHtml) : (iconHtml + ' ' + textSpan));
				$btn.css({ display: 'inline-flex', alignItems: 'center', gap: '6px' });
			} else {
				$btn.text(buttonText);
			}

			// Position inside canvas.
			$btn.css({ top: '', bottom: '', left: '', right: '', transform: 'none' });
			switch (position) {
				case 'top-left':     $btn.css({ top:    '12px', left:  '12px' }); break;
				case 'top-right':    $btn.css({ top:    '12px', right: '12px' }); break;
				case 'bottom-left':  $btn.css({ bottom: '12px', left:  '12px' }); break;
				case 'bottom-right': $btn.css({ bottom: '12px', right: '12px' }); break;
				case 'center-left':  $btn.css({ top: '50%', left: '12px',  transform: 'translateY(-50%)' }); break;
				case 'center-right': $btn.css({ top: '50%', right: '12px', transform: 'translateY(-50%)' }); break;
				case 'bottom-center':
				default:             $btn.css({ bottom: '12px', left: '50%', transform: 'translateX(-50%)' });
			}

			$canvas.append($btn);
		}

		// Refresh preview on any styling field change.
		$(document).on('change input',
			'#explorexr_premium_ar_button_position, ' +
			'#explorexr_premium_ar_button_size, ' +
			'#explorexr_premium_ar_button_bg_color, ' +
			'#explorexr_premium_ar_button_text_color, ' +
			'#explorexr_premium_ar_button_border_color, ' +
			'#explorexr_premium_ar_button_border_radius, ' +
			'#explorexr_premium_ar_button_text, ' +
			'#explorexr_premium_ar_button_icon, ' +
			'#explorexr_premium_ar_button_icon_position',
			function() {
				$('#ar-btn-preview').remove();
				renderARButtonPreview();
			}
		);

		// ── Sync existing USDZ dropdown to text field ─────────────────────
		$('#explorexr_premium_ar_usdz_existing').on('change', function() {
			const val = $(this).val();
			if (val) {
				$('#explorexr_premium_ar_usdz_model').val(val).trigger('change');
			}
		});

		// ── Media uploader (USDZ + XR environment) ───────────────────────
		const mediaFrames = {};
		function openMediaFrame(targetId, type) {
			const cacheKey = targetId + '_' + type;
			if (!mediaFrames[cacheKey]) {
				const libType = ('usdz' === type)
					? ['model/vnd.usdz+zip', 'application/octet-stream', 'application/zip']
					: ['image'];
				mediaFrames[cacheKey] = wp.media({
					title:    ('usdz' === type) ? 'Select USDZ file' : 'Select file',
					button:   { text: 'Use this file' },
					multiple: false,
				});
				mediaFrames[cacheKey].on('select', function() {
					const attachment = mediaFrames[cacheKey].state().get('selection').first().toJSON();
					$('#' + targetId).val(attachment.url).trigger('change');
				});
			}
			mediaFrames[cacheKey].open();
		}

		$('.explorexr-premium-upload-button').on('click', function(e) {
			e.preventDefault();
			const target = $(this).data('target');
			const type   = (target.indexOf('usdz') !== -1) ? 'usdz' : 'generic';
			openMediaFrame(target, type);
		});

		// ── Media uploader for icon ───────────────────────────────────────
		let iconFrame = null;
		$('.explorexr-premium-icon-upload-button').on('click', function(e) {
			e.preventDefault();
			const target = $(this).data('target');
			if (!iconFrame) {
				iconFrame = wp.media({ title: 'Select icon image', button: { text: 'Use this icon' }, multiple: false });
				iconFrame.on('select', function() {
					const attachment = iconFrame.state().get('selection').first().toJSON();
					$('#' + target).val(attachment.url).trigger('change');
					if (!$('#explorexr_premium_ar_button_icon_enabled').is(':checked')) {
						$('#explorexr_premium_ar_button_icon_enabled').prop('checked', true).trigger('change');
					}
				});
			}
			iconFrame.open();
		});

		$('.explorexr-premium-icon-remove-button').on('click', function(e) {
			e.preventDefault();
			$('#' + $(this).data('target')).val('').trigger('change');
			$(this).remove();
		});

		// ── Initial state ─────────────────────────────────────────────────
		if ($('#explorexr_premium_ar_enabled').is(':checked')) {
			renderARButtonPreview();
		}

	}); // document.ready
})(jQuery);
</script>

<?php endif; ?>
