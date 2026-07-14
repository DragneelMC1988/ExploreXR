<?php
/**
 * Free version Addons page.
 *
 * Lists the three free-eligible addons (AR, Animation, Loading)
 * with the same card layout used by the Premium addons page. Installation
 * goes through a direct download from update.expoxr.com via the
 * explorexr_direct_download_addon AJAX endpoint.
 */

if (!defined('ABSPATH')) {
    exit;
}

function explorexr_free_addons_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'explorexr'));
    }

    $addons = array(
        'ar'          => array(
            'name'        => __('AR Viewer', 'explorexr'),
            'description' => __('Augmented Reality viewing on mobile devices (iOS Quick Look + Android Scene Viewer + WebXR).', 'explorexr'),
            'icon'        => 'dashicons-smartphone',
            'path'        => 'explorexr-ar-addon/explorexr-ar-addon.php',
        ),
        'animation'   => array(
            'name'        => __('Animation', 'explorexr'),
            'description' => __('Play, pause, loop and ping-pong glTF animation clips on the model viewer.', 'explorexr'),
            'icon'        => 'dashicons-controls-play',
            'path'        => 'explorexr-animation-addon/explorexr-animation-addon.php',
        ),
        'loading'     => array(
            'name'        => __('Loading Options', 'explorexr'),
            'description' => __('Custom loading bars, percentage counters, overlays and progress text.', 'explorexr'),
            'icon'        => 'dashicons-update',
            'path'        => 'explorexr-loading-addon/explorexr-loading-addon.php',
        ),
    );

    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $manager     = class_exists('ExploreXR_Addon_Manager') ? ExploreXR_Addon_Manager::get_instance() : null;
    $active_slug = null;
    if ($manager) {
        foreach (array_keys($addons) as $check_slug) {
            if ($manager->is_addon_active($check_slug)) {
                $active_slug = $check_slug;
                break;
            }
        }
    }
    ?>
    <div class="wrap explorexr-addons-page">
        <?php settings_errors(); ?>

        <?php
        $page_title = esc_html__('ExploreXR Add-ons', 'explorexr');
        $insert_header_end_marker = true;
        include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-header.php';
        ?>

        <!-- Free-tier summary -->
        <div class="explorexr-license-summary">
            <div class="license-badge active">
                <span class="dashicons dashicons-info-outline"></span>
                <span><?php esc_html_e('ExploreXR Free', 'explorexr'); ?></span>
            </div>
            <div class="addon-quota">
                <span class="dashicons dashicons-admin-plugins"></span>
                <span>
                    <?php
                    printf(
                        /* translators: %1$s: active count, %2$s: total slots */
                        esc_html__('Add-ons: %1$s / %2$s', 'explorexr'),
                        '<strong>' . esc_html((string) ($active_slug !== null ? 1 : 0)) . '</strong>',
                        '<strong>1</strong>'
                    );
                    ?>
                </span>
            </div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-go-premium')); ?>" class="button button-primary">
                <span class="dashicons dashicons-star-filled"></span>
                <?php esc_html_e('Go Premium', 'explorexr'); ?>
            </a>
        </div>

        <?php if ($active_slug !== null) : ?>
            <div class="notice notice-info inline">
                <p>
                    <?php
                    printf(
                        /* translators: %s: addon name */
                        esc_html__('Active addon: %s. Deactivate it from the Plugins screen to switch to a different addon.', 'explorexr'),
                        '<strong>' . esc_html($addons[$active_slug]['name']) . '</strong>'
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Add-ons Selection Section -->
        <div class="explorexr-premium-addons-section">
            <h2><span class="dashicons dashicons-admin-plugins"></span> <?php esc_html_e('Available Add-ons', 'explorexr'); ?></h2>

            <div class="explorexr-premium-addons-grid">
                <?php foreach ($addons as $slug => $addon) :
                    $is_installed = file_exists(WP_PLUGIN_DIR . '/' . $addon['path']);
                    $is_active    = $is_installed && is_plugin_active($addon['path']);
                    $is_blocked   = ($active_slug !== null && $slug !== $active_slug);
                    ?>
                    <div class="explorexr-premium-addon-card <?php echo $is_active ? 'activated' : ''; ?>" data-addon="<?php echo esc_attr($slug); ?>">
                        <div class="addon-info">
                            <h3>
                                <span class="dashicons <?php echo esc_attr($addon['icon']); ?>"></span>
                                <?php echo esc_html($addon['name']); ?>
                            </h3>
                            <p><?php echo esc_html($addon['description']); ?></p>

                            <div class="addon-status">
                                <?php if ($is_active) : ?>
                                    <span class="status-text"><?php esc_html_e('Active', 'explorexr'); ?></span>
                                <?php elseif ($is_installed) : ?>
                                    <span class="status-text"><?php esc_html_e('Installed', 'explorexr'); ?></span>
                                <?php else : ?>
                                    <span class="status-text"><?php esc_html_e('Not Installed', 'explorexr'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="addon-actions">
                            <?php if ($is_active) : ?>
                                <a href="<?php echo esc_url(admin_url('plugins.php')); ?>" class="button">
                                    <span class="dashicons dashicons-admin-plugins"></span>
                                    <?php esc_html_e('Manage', 'explorexr'); ?>
                                </a>
                            <?php elseif ($is_installed) : ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('plugins.php?action=activate&plugin=' . urlencode($addon['path'])), 'activate-plugin_' . $addon['path'])); ?>"
                                   class="button button-primary"
                                   <?php echo $is_blocked ? 'aria-disabled="true" onclick="return false;" style="opacity:.5;pointer-events:none;"' : ''; ?>>
                                    <span class="dashicons dashicons-yes"></span>
                                    <?php esc_html_e('Enable Plugin', 'explorexr'); ?>
                                </a>
                            <?php else : ?>
                                <button type="button"
                                        class="button button-primary explorexr-direct-install-btn"
                                        data-slug="<?php echo esc_attr($slug); ?>"
                                        <?php disabled($is_blocked, true); ?>>
                                    <span class="dashicons dashicons-download"></span>
                                    <?php echo $is_blocked
                                        ? esc_html__('Disable current addon first', 'explorexr')
                                        : esc_html__('Install Add-on', 'explorexr'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Upsell -->
        <div class="explorexr-premium-addons-section">
            <div class="explorexr-premium-addon-card" style="text-align:center;">
                <div class="addon-info" style="width:100%;">
                    <h3 style="justify-content:center;">
                        <span class="dashicons dashicons-star-filled"></span>
                        <?php esc_html_e('Need more than one addon?', 'explorexr'); ?>
                    </h3>
                    <p>
                        <?php esc_html_e('ExploreXR Premium unlocks the full commercial catalog (12 addons) plus multi-addon support, page-builder widgets, and priority updates.', 'explorexr'); ?>
                    </p>
                </div>
                <div class="addon-actions" style="justify-content:center;">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-go-premium')); ?>" class="button button-primary">
                        <?php esc_html_e('See Premium plans', 'explorexr'); ?>
                    </a>
                </div>
            </div>
        </div>

        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    </div>
    <?php
}
