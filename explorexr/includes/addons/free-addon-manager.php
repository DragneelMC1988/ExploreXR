<?php
/**
 * Free Addon Manager.
 *
 * Mirrors the public API surface of the Premium ExploreXR_Addon_Manager so
 * existing addon entry files keep working unchanged, but enforces the
 * Free-tier rules: a single active addon, drawn from a fixed whitelist.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Minimal stand-in for the Premium options-manager so addon code that calls
 * $manager->get_options_manager()->is_addon_meta_disabled(...) etc keeps working.
 * MUST be defined BEFORE ExploreXR_Addon_Manager because it's instantiated inside that class.
 */
if (!class_exists('ExploreXR_Free_Options_Manager_Stub')) {
    class ExploreXR_Free_Options_Manager_Stub {
        public function is_addon_meta_disabled($slug)        { return false; }
        public function restore_addon_settings($slug)        {}
        public function set_addon_default_options($slug, $opts) {}
        public function ensure_addon_options_active($slug)   { return true; }
        public function ensure_addon_options_inactive($slug) { return true; }
    }
}

if (class_exists('ExploreXR_Addon_Manager')) {
    return;
}

class ExploreXR_Addon_Manager {

    const WHITELIST      = array('ar', 'animation', 'loading');
    const MAX_ACTIVE     = 1;
    /** Addons allowed on Free regardless of WHITELIST; do not count against MAX_ACTIVE. */
    const ALWAYS_ALLOWED = array('debug');
    const ERROR_TRANSIENT = 'explorexr_free_addon_block_msg';
    const NOTICE_TTL = 60;

    /** @var self|null */
    private static $instance = null;

    /** @var array<string,array> */
    private $registered_addons = array();

    /** Friendly display names for every known commercial addon slug. */
    private static $slug_names = array(
        'ar'              => 'AR Viewer',
        'animation'       => 'Animation',
        'loading'         => 'Loading Options',
        'annotations'     => 'Annotations',
        'camera'          => 'Camera Controls',
        'environment'     => 'Environment & Lighting',
        'materials'       => 'Materials Variants',
        'morphing'        => 'Morphing',
        'mouse3d'         => 'Mouse3D',
        'draggable'       => 'Draggable Viewer',
        'post-processing' => 'Post-Processing',
        'woocommerce'     => 'WooCommerce',
        'debug'           => 'Debug Toolkit',
    );

    public static function friendly_name($slug) {
        return isset(self::$slug_names[$slug]) ? self::$slug_names[$slug] : $slug;
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('activate_plugin', array($this, 'gate_addon_activation'), 10, 1);
        add_action('activated_plugin', array($this, 'enforce_after_activation'), 10, 1);
        add_action('admin_init', array($this, 'enforce_single_addon'), 20);
        add_action('admin_notices', array($this, 'maybe_show_block_notice'));
    }

    /** Map premium-era slug aliases to canonical Free slugs. */
    public function resolve_slug($slug) {
        $slug    = (string) $slug;
        $aliases = array(
            'explorexr-premium-ar'           => 'ar',
            'explorexr-premium-animation'    => 'animation',
            'explorexr-premium-woocommerce'  => 'woocommerce',
            'explorexr-premium-annotations'  => 'annotations',
            'explorexr-premium-camera'       => 'camera',
            'explorexr-premium-loading'      => 'loading',
            'explorexr-premium-materials'    => 'materials',
            'explorexr-premium-morphing'     => 'morphing',
            'explorexr-premium-mouse3d'      => 'mouse3d',
            'explorexr-premium-draggable'    => 'draggable',
            'explorexr-premium-post-processing' => 'post-processing',
            'explorexr-premium-environment'  => 'environment',
            'explorexr-premium-debug'        => 'debug',
        );
        return isset($aliases[$slug]) ? $aliases[$slug] : $slug;
    }

    /** Addons call this on init priority 15. */
    public function register_addon($slug, $data) {
        $slug = $this->resolve_slug($slug);
        if (!is_array($data)) {
            return false;
        }
        $this->registered_addons[$slug] = $data;

        // Seed default options on first registration (autoload=false). Mirrors
        // Premium options-manager behaviour so addons get their defaults on Free too.
        if (!empty($data['default_options']) && is_array($data['default_options'])) {
            foreach ($data['default_options'] as $option_name => $default_value) {
                if (get_option($option_name, '__explorexr_unset__') === '__explorexr_unset__') {
                    add_option($option_name, $default_value, '', false);
                }
            }
        }
        return true;
    }

    /** @var object|null */
    private $options_manager = null;

    /** Returns a stub matching the Premium options-manager surface that addons call. */
    public function get_options_manager() {
        if ($this->options_manager === null) {
            $this->options_manager = new ExploreXR_Free_Options_Manager_Stub();
        }
        return $this->options_manager;
    }

    public function get_registered_addons() {
        return $this->registered_addons;
    }

    public function get_active_addons() {
        $active = array();
        foreach ($this->registered_addons as $slug => $data) {
            if ($this->is_addon_active($slug)) {
                $active[$slug] = $data;
            }
        }
        return $active;
    }

    public function is_addon_active($slug) {
        $slug = $this->resolve_slug($slug);
        if (!isset($this->registered_addons[$slug]['file'])) {
            return false;
        }
        $plugin_file = plugin_basename($this->registered_addons[$slug]['file']);
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active($plugin_file);
    }

    /** Compatibility shims for callers expecting the Premium options manager. */
    public function ensure_addon_options_active($slug)   { return true; }
    public function ensure_addon_options_inactive($slug) { return true; }
    public function is_addon_meta_disabled($slug)        { return false; }
    public function activate_all_addon_options()         { return true; }

    public function update_addon_options($addon_slug, $settings) {
        static $is_updating = false;

        if ($is_updating || !is_array($settings) || empty($settings)) {
            return false;
        }

        $is_updating = true;
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        $is_updating = false;

        return true;
    }

    public function get_addon_options($addon_slug) {
        $addon_slug = $this->resolve_slug($addon_slug);
        $options    = array();

        if (!empty($this->registered_addons[$addon_slug]['default_options']) && is_array($this->registered_addons[$addon_slug]['default_options'])) {
            foreach ($this->registered_addons[$addon_slug]['default_options'] as $option_name => $default_value) {
                $options[$option_name] = get_option($option_name, $default_value);
            }
        }

        return $options;
    }

    /**
     * `activate_plugin` hook: block activation if the addon is not whitelisted
     * or if it would push the active count above MAX_ACTIVE.
     */
    public function gate_addon_activation($plugin_file) {
        if (!is_string($plugin_file) || $plugin_file === '') {
            return;
        }

        $slug = $this->slug_for_plugin_file($plugin_file);
        if ($slug === null) {
            return;
        }

        $friendly = self::friendly_name($slug);

        // License-free addons (e.g. Debug) are always allowed and don't count against MAX_ACTIVE.
        if (in_array($slug, self::ALWAYS_ALLOWED, true)) {
            return;
        }

        if (!in_array($slug, self::WHITELIST, true)) {
            $this->block_and_deactivate(
                $plugin_file,
                sprintf(
                    /* translators: %s: friendly addon name */
                    __('%s is not available in the free version of ExploreXR. Upgrade to Premium to unlock it.', 'explorexr'),
                    $friendly
                )
            );
            return;
        }

        $other_active = 0;
        foreach (self::WHITELIST as $whitelisted) {
            if ($whitelisted === $slug) {
                continue;
            }
            if ($this->is_whitelisted_addon_active($whitelisted)) {
                $other_active++;
            }
        }
        if ($other_active >= self::MAX_ACTIVE) {
            $this->block_and_deactivate(
                $plugin_file,
                sprintf(
                    /* translators: %s: friendly addon name */
                    __('%s cannot be activated — ExploreXR Free allows one addon at a time. Deactivate the current addon first, or upgrade to Premium.', 'explorexr'),
                    $friendly
                )
            );
        }
    }

    /**
     * `activated_plugin` hook: fires AFTER WordPress has already persisted the
     * activation into the `active_plugins` option, so unlike `gate_addon_activation()`
     * (which runs during `activate_plugin`, before that option is saved),
     * `deactivate_plugins()` here is guaranteed to actually take effect —
     * regardless of AJAX/cron context. This closes the window where the direct-install
     * AJAX handler's own `activate_plugin()` call could leave two whitelisted addons
     * simultaneously active because `gate_addon_activation()`'s block relies on an
     * `exit`/redirect that is deliberately skipped during `wp_doing_ajax()`.
     */
    public function enforce_after_activation($plugin_file) {
        if (!is_string($plugin_file) || $plugin_file === '') {
            return;
        }

        $slug = $this->slug_for_plugin_file($plugin_file);
        if ($slug === null || in_array($slug, self::ALWAYS_ALLOWED, true)) {
            return;
        }

        $friendly = self::friendly_name($slug);

        if (!in_array($slug, self::WHITELIST, true)) {
            $this->deactivate_now(
                $plugin_file,
                sprintf(
                    /* translators: %s: friendly addon name */
                    __('%s is not available in the free version of ExploreXR. Upgrade to Premium to unlock it.', 'explorexr'),
                    $friendly
                )
            );
            return;
        }

        $other_active = 0;
        foreach (self::WHITELIST as $whitelisted) {
            if ($whitelisted === $slug) {
                continue;
            }
            if ($this->is_whitelisted_addon_active($whitelisted)) {
                $other_active++;
            }
        }
        if ($other_active >= self::MAX_ACTIVE) {
            $this->deactivate_now(
                $plugin_file,
                sprintf(
                    /* translators: %s: friendly addon name */
                    __('%s cannot be activated — ExploreXR Free allows one addon at a time. Deactivate the current addon first, or upgrade to Premium.', 'explorexr'),
                    $friendly
                )
            );
        }
    }

    /**
     * `admin_init` hook: if more than one whitelisted addon ended up active
     * (e.g. WP-CLI bypass), deactivate the extras keeping only one.
     */
    public function enforce_single_addon() {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        // Use direct WordPress plugin state so WP-CLI activations that run before
        // an addon's plugins_loaded self-registration are still detected. Check
        // both the conventional folder layout and the file each addon registered
        // itself with, so a renamed addon folder (e.g. a GitHub zip suffix)
        // cannot bypass the limit.
        $active = array();
        foreach (self::WHITELIST as $slug) {
            $candidates = array("explorexr-{$slug}-addon/explorexr-{$slug}-addon.php");
            if (isset($this->registered_addons[$slug]['file'])) {
                $candidates[] = plugin_basename($this->registered_addons[$slug]['file']);
            }
            foreach (array_unique($candidates) as $plugin_file) {
                if (is_plugin_active($plugin_file)) {
                    $active[$slug] = $plugin_file;
                    break;
                }
            }
        }
        if (count($active) <= self::MAX_ACTIVE) {
            return;
        }

        // Keep the addon that appears first in the site's active_plugins list
        // (deterministic WordPress ordering) rather than always favouring the
        // first whitelist entry; deactivate the rest.
        $active_plugins = (array) get_option('active_plugins', array());
        uasort($active, static function($a, $b) use ($active_plugins) {
            $pos_a = array_search($a, $active_plugins, true);
            $pos_b = array_search($b, $active_plugins, true);
            $pos_a = ($pos_a === false) ? PHP_INT_MAX : $pos_a;
            $pos_b = ($pos_b === false) ? PHP_INT_MAX : $pos_b;
            return $pos_a <=> $pos_b;
        });

        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $kept = false;
        foreach ($active as $slug => $file) {
            if (!$kept) {
                $kept = true;
                continue;
            }
            deactivate_plugins($file, true);
        }
        set_transient(self::ERROR_TRANSIENT, __('ExploreXR Free only allows a single addon. Extra addons have been deactivated.', 'explorexr'), self::NOTICE_TTL);
    }

    public function maybe_show_block_notice() {
        $msg = get_transient(self::ERROR_TRANSIENT);
        if (!$msg) {
            return;
        }
        delete_transient(self::ERROR_TRANSIENT);
        if (!current_user_can('activate_plugins')) {
            return;
        }
        printf(
            '<div class="notice notice-warning is-dismissible"><p>%s <a href="%s">%s</a></p></div>',
            esc_html($msg),
            esc_url(admin_url('admin.php?page=explorexr-go-premium')),
            esc_html__('See premium plans →', 'explorexr')
        );
    }

    /**
     * Registration-independent active check for a whitelisted addon: looks at
     * both the conventional folder layout and the registered file, so it works
     * even before the addon's own init-time self-registration has run.
     */
    private function is_whitelisted_addon_active($slug) {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $candidates = array("explorexr-{$slug}-addon/explorexr-{$slug}-addon.php");
        if (isset($this->registered_addons[$slug]['file'])) {
            $candidates[] = plugin_basename($this->registered_addons[$slug]['file']);
        }
        foreach (array_unique($candidates) as $plugin_file) {
            if (is_plugin_active($plugin_file)) {
                return true;
            }
        }
        return false;
    }

    /** Try to resolve a plugin file path to one of the registered addon slugs. */
    private function slug_for_plugin_file($plugin_file) {
        foreach ($this->registered_addons as $slug => $data) {
            if (!isset($data['file'])) {
                continue;
            }
            if (plugin_basename($data['file']) === $plugin_file) {
                return $slug;
            }
        }
        // Fall back to filename pattern: explorexr-{slug}-addon/explorexr-{slug}-addon.php
        if (preg_match('#^explorexr-([a-z0-9\-]+)-addon/#', $plugin_file, $m)) {
            return $this->resolve_slug($m[1]);
        }
        return null;
    }

    private function block_and_deactivate($plugin_file, $message) {
        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        deactivate_plugins($plugin_file, true);
        set_transient(self::ERROR_TRANSIENT, $message, self::NOTICE_TTL);

        // Mirror Premium's redirect-to-plugins pattern so the user sees the notice.
        if (!wp_doing_ajax() && !defined('DOING_CRON')) {
            wp_safe_redirect(self_admin_url('plugins.php?error=true'));
            exit;
        }
    }

    /**
     * Unconditional deactivation used by `enforce_after_activation()`. No
     * exit/redirect here — this runs post-persistence (see that method's
     * docblock) and must close the enforcement gap in every request context,
     * including AJAX and cron, not just interactive admin page loads.
     */
    private function deactivate_now($plugin_file, $message) {
        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        deactivate_plugins($plugin_file, true);
        set_transient(self::ERROR_TRANSIENT, $message, self::NOTICE_TTL);
    }
}
