<?php
/**
 * ExploreXR (Free) helper functions.
 *
 * Mirrors the Premium helper API so existing addons continue to work, but
 * implements every license check as a Free-tier stub: there is no license
 * server, no tier-aware quota, and exactly one of the whitelisted addons
 * may be active at a time.
 *
 * Naming convention: the `explorexr_premium_*` function names and the
 * `EXPLOREXR_PREMIUM_*` constants are an intentional compatibility surface —
 * addons are built against the Premium API and must run unchanged on Free.
 * Do not rename these symbols; doing so breaks the addon contract.
 *
 * @package ExploreXR
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('explorexr_model_viewer_script_url')) {
    /**
     * Resolve the model-viewer library URL, preferring the minified bundle.
     *
     * Falls back to the unminified UMD build when the minified file is absent
     * or SCRIPT_DEBUG is enabled.
     *
     * @return string Fully qualified URL to the model-viewer script.
     */
    function explorexr_model_viewer_script_url() {
        static $url = null;
        if ($url !== null) {
            return $url;
        }
        $use_min = !(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)
            && file_exists(EXPLOREXR_PLUGIN_DIR . 'assets/js/model-viewer-umd.min.js');
        $url = EXPLOREXR_PLUGIN_URL . 'assets/js/' . ($use_min ? 'model-viewer-umd.min.js' : 'model-viewer-umd.js');
        return $url;
    }
}

if (!function_exists('explorexr_premium_is_active')) {
    /**
     * Addons use this to detect whether the host plugin is running.
     * Free plugin counts as an active host.
     */
    function explorexr_premium_is_active() {
        return class_exists('ExploreXR_Addon_Manager');
    }
}

if (!function_exists('explorexr_premium_get_license_tier')) {
    function explorexr_premium_get_license_tier() {
        return 'free';
    }
}

if (!function_exists('explorexr_premium_is_license_active')) {
    function explorexr_premium_is_license_active() {
        return true;
    }
}

if (!function_exists('explorexr_premium_get_max_addons')) {
    function explorexr_premium_get_max_addons() {
        return class_exists('ExploreXR_Addon_Manager') ? ExploreXR_Addon_Manager::MAX_ACTIVE : 1;
    }
}

if (!function_exists('explorexr_premium_is_pro_or_higher')) {
    function explorexr_premium_is_pro_or_higher() {
        return false;
    }
}

if (!function_exists('explorexr_premium_is_addon_licensed')) {
    /**
     * In Free, "licensed" means the slug is in the curated whitelist.
     */
    function explorexr_premium_is_addon_licensed($addon_slug) {
        if (!class_exists('ExploreXR_Addon_Manager')) {
            return false;
        }
        return in_array($addon_slug, ExploreXR_Addon_Manager::WHITELIST, true);
    }
}

if (!function_exists('explorexr_premium_get_selected_addons')) {
    function explorexr_premium_get_selected_addons() {
        if (!class_exists('ExploreXR_Addon_Manager')) {
            return array();
        }
        return array_keys(ExploreXR_Addon_Manager::get_instance()->get_active_addons());
    }
}

if (!function_exists('explorexr_premium_is_addon_active')) {
    function explorexr_premium_is_addon_active($addon_slug, $require_license = true) {
        if (!class_exists('ExploreXR_Addon_Manager')) {
            return false;
        }
        $manager = ExploreXR_Addon_Manager::get_instance();
        if (!$manager->is_addon_active($addon_slug)) {
            return false;
        }
        return !$require_license || explorexr_premium_is_addon_licensed($addon_slug);
    }
}

if (!function_exists('explorexr_premium_has_model_viewers')) {
    /**
     * Check if the current request likely contains ExploreXR model viewers.
     */
    function explorexr_premium_has_model_viewers($post = null) {
        if (!$post) {
            $post = get_post();
        }

        if (!$post instanceof WP_Post) {
            if (is_404() || is_category() || is_tag() || is_archive() || is_search()) {
                return false;
            }
            // Only load assets on singular-type pages or recognized front-page contexts.
            // Avoids loading model-viewer scripts on listing pages, REST requests, etc.
            return is_singular() || is_front_page() || is_home();
        }

        if ($post->post_type === 'explorexr_model') {
            return true;
        }

        $content    = (string) $post->post_content;
        $shortcodes = array('explorexr_model', 'explorexr', 'explorexr-premium', 'EXPLOREXR_model');
        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($content, $shortcode)) {
                return true;
            }
        }
        if (strpos($content, '[explorexr') !== false) {
            return true;
        }

        if (function_exists('is_product') && is_product()) {
            $product_id = get_the_ID();
            $model_id   = get_post_meta($product_id, '_explorexr_premium_model_id', true);
            if ($model_id) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('explorexr_premium_addon_license_notice')) {
    /**
     * Free-tier notice shown by an addon when it cannot run (either it is
     * outside the whitelist, or the single-active-addon slot is taken).
     */
    function explorexr_premium_addon_license_notice($addon_name, $addon_slug = '') {
        $whitelisted = $addon_slug === '' ? false : explorexr_premium_is_addon_licensed($addon_slug);
        $go_premium  = esc_url(admin_url('admin.php?page=explorexr-go-premium'));
        $addons_url  = esc_url(admin_url('admin.php?page=explorexr-addons'));

        if (!$whitelisted) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php echo esc_html($addon_name); ?></strong>
                    <?php esc_html_e('is not available in the free version of ExploreXR. Upgrade to Premium to unlock it.', 'explorexr'); ?>
                    <a href="<?php echo $go_premium; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" class="button button-primary"><?php esc_html_e('Go Premium', 'explorexr'); ?></a>
                </p>
            </div>
            <?php
            return;
        }
        ?>
        <div class="notice notice-info">
            <p>
                <strong><?php echo esc_html($addon_name); ?></strong>
                <?php esc_html_e('cannot be activated because the free version already has another addon running. Deactivate the current addon first, or upgrade to Premium for multi-addon support.', 'explorexr'); ?>
                <a href="<?php echo $addons_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" class="button button-secondary"><?php esc_html_e('Manage Addons', 'explorexr'); ?></a>
                <a href="<?php echo $go_premium; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" class="button button-primary"><?php esc_html_e('Go Premium', 'explorexr'); ?></a>
            </p>
        </div>
        <?php
    }
}

if (!function_exists('explorexr_get_premium_upgrade_url')) {
    /**
     * URL of the in-plugin Go Premium screen. Used by upsell UI scattered
     * across templates.
     */
    function explorexr_get_premium_upgrade_url() {
        return admin_url('admin.php?page=explorexr-go-premium');
    }
}

if (!function_exists('EXPLOREXR_get_premium_upgrade_url')) {
    function EXPLOREXR_get_premium_upgrade_url() {
        return explorexr_get_premium_upgrade_url();
    }
}

if (!function_exists('explorexr_premium_is_premium_available')) {
    /**
     * Legacy "is the host plugin available?" check used by several admin
     * cards to decide whether to render their per-model settings UI. In
     * the new Free plugin the host IS available (rendering core, loading
     * settings, compression decoders all ship), so this returns true.
     */
    function explorexr_premium_is_premium_available() {
        return true;
    }
}

if (!function_exists('explorexr_is_premium_available')) {
    function explorexr_is_premium_available() {
        return true;
    }
}

if (!function_exists('explorexr_sanitize_hex_color')) {
    /**
     * Sanitize hex color value (3- or 6-digit hex, with optional `#`).
     */
    function explorexr_sanitize_hex_color($color, $default = '#000000') {
        $color = trim((string) $color);
        if ($color === '') {
            return $default;
        }
        if (substr($color, 0, 1) !== '#') {
            $color = '#' . $color;
        }
        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            return $color;
        }
        return $default;
    }
}
