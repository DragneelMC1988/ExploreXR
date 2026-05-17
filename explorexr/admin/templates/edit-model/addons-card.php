<?php
/**
 * Edit Model - Add-ons Settings Card
 * 
 * Shows available addons and their settings for the current model
 *
 * @package ExploreXR_Premium
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Template variables passed from parent scope - disable PHPCS global prefix check
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Get addon manager instance (works on both Free and Premium — the Free
// plugin ships a slim ExploreXR_Addon_Manager with the same public API).
$addon_manager = class_exists('ExploreXR_Addon_Manager') ? ExploreXR_Addon_Manager::get_instance() : null;

// Get license handler
$license_handler = class_exists('ExploreXR_License_Handler') ? ExploreXR_License_Handler::instance() : null;

// Get active WordPress plugins that are addons
$active_addons = array();

if ($license_handler) {
    $all_addons = $license_handler->get_addons();
    
    // Check each addon to see if it's active in WordPress
    foreach ($all_addons as $addon_slug => $addon_data) {
        $addon_path = isset($addon_data['path']) ? $addon_data['path'] : '';
        
        // Check if addon plugin is active (allow rendering even if license is pending to match prior behavior)
        $addon_active = false;
        if (function_exists('explorexr_premium_is_addon_active')) {
            $addon_active = explorexr_premium_is_addon_active($addon_slug, false);
        } elseif (!empty($addon_path)) {
            if (!function_exists('is_plugin_active')) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $addon_active = is_plugin_active($addon_path);
        }

        if (!empty($addon_path) && $addon_active) {
            $active_addons[$addon_slug] = $addon_data;
        }
    }
}

// Fallback: include any registered addons that are active even if not present in license map
if ($addon_manager && method_exists($addon_manager, 'get_registered_addons')) {
    foreach ($addon_manager->get_registered_addons() as $registered_slug => $registered_data) {
        if (isset($active_addons[$registered_slug])) {
            continue;
        }
        $addon_file = isset($registered_data['file']) ? $registered_data['file'] : '';
        if (empty($addon_file)) {
            continue;
        }

        $addon_active = false;
        if (function_exists('explorexr_premium_is_addon_active')) {
            $addon_active = explorexr_premium_is_addon_active($registered_slug, false);
        } else {
            if (!function_exists('is_plugin_active')) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $addon_active = is_plugin_active(plugin_basename($addon_file));
        }

        if (!$addon_active) {
            continue;
        }
        $active_addons[$registered_slug] = array(
            'name' => isset($registered_data['name']) ? $registered_data['name'] : ucfirst($registered_slug),
            'path' => plugin_basename($addon_file)
        );
    }
}

// If no addons are active, show a message
if (empty($active_addons)) {
    ?>
    <div class="explorexr-card">
        <div class="explorexr-card-header">
            <h3><span class="dashicons dashicons-admin-plugins"></span> Add-ons</h3>
        </div>
        <div class="explorexr-card-content">
            <div class="notice notice-warning inline">
                <p>
                    <strong>No add-ons activated yet.</strong><br>
                    Activate add-ons from the <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-addons')); ?>">Add-ons Manager</a> page or the WordPress <a href="<?php echo esc_url(admin_url('plugins.php')); ?>">Plugins</a> page to enhance your 3D models with advanced features.
                </p>
            </div>
            <div class="explorexr-addon-features">
                <h4><?php esc_html_e('Available Free Add-ons:', 'explorexr'); ?></h4>
                <ul>
                    <li><strong>📝 <?php esc_html_e('Annotations', 'explorexr'); ?>:</strong> <?php esc_html_e('Add interactive hotspots and labels to your 3D models', 'explorexr'); ?></li>
                    <li><strong>📱 <?php esc_html_e('AR (Augmented Reality)', 'explorexr'); ?>:</strong> <?php esc_html_e('View models in real-world environments', 'explorexr'); ?></li>
                    <li><strong>🎬 <?php esc_html_e('Animation', 'explorexr'); ?>:</strong> <?php esc_html_e('Add animated sequences and controls', 'explorexr'); ?></li>
                    <li><strong>⏳ <?php esc_html_e('Loading', 'explorexr'); ?>:</strong> <?php esc_html_e('Custom loading indicators and progress bars', 'explorexr'); ?></li>
                </ul>
                <p style="margin-top: 8px;">
                    <?php esc_html_e('Activate one of these add-ons from the', 'explorexr'); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-addons')); ?>"><?php esc_html_e('Add-ons Manager', 'explorexr'); ?></a>.
                </p>
            </div>
        </div>
    </div>
    <?php
    return;
}

// Show upgrade nudge when at least one addon is already active
?>
<div class="notice notice-info inline" style="margin: 0 0 16px;">
    <p>
        <?php
        printf(
            wp_kses(
                /* translators: %s: URL to the premium upgrade page */
                __('Want more add-ons? <a href="%s">Upgrade to Premium</a> for unlimited access to Camera, Materials, Environment, Morphing, Post-Processing, WooCommerce, and more.', 'explorexr'),
                array('a' => array('href' => array()))
            ),
            esc_url(admin_url('admin.php?page=explorexr-go-premium'))
        );
        ?>
    </p>
</div>
<?php

// Extract model_id from template vars
$model_id = isset($model_id) ? $model_id : 0;

// Display settings for each active addon as separate cards
foreach ($active_addons as $addon_slug => $addon_data) {
    $addon_name = isset($addon_data['name']) ? $addon_data['name'] : ucfirst($addon_slug);
    
    // Get addon icon based on slug
    $addon_icons = array(
        'annotations' => 'dashicons-tag',
        'ar' => 'dashicons-smartphone',
        'animation' => 'dashicons-video-alt3',
        'camera' => 'dashicons-camera',
        'materials' => 'dashicons-art',
        'morphing' => 'dashicons-image-rotate',
        'mouse3d' => 'dashicons-move',
        'environment' => 'dashicons-admin-site-alt3',
        'post-processing' => 'dashicons-admin-appearance',
        'loading' => 'dashicons-update',
        'woocommerce' => 'dashicons-cart',
        'draggable' => 'dashicons-screenoptions'
    );
    
    $icon = isset($addon_icons[$addon_slug]) ? $addon_icons[$addon_slug] : 'dashicons-admin-plugins';
    
    // Check if addon has settings function
    // Convert hyphens to underscores for function names (post-processing → post_processing)
    $function_slug = str_replace('-', '_', $addon_slug);
    $settings_function = "explorexr_{$function_slug}_addon_settings";
    
    ?>
    <div class="explorexr-card explorexr-addon-card explorexr-addon-card--<?php echo esc_attr($addon_slug); ?>" data-addon="<?php echo esc_attr($addon_slug); ?>">
        <div class="explorexr-card-header">
            <h3>
                <span class="dashicons <?php echo esc_attr($icon); ?>"></span> 
                <?php echo esc_html($addon_name); ?>
            </h3>
            <span class="addon-status-badge">
                <span class="dashicons dashicons-yes-alt"></span> Active
            </span>
        </div>
        <div class="explorexr-card-content">
            <?php
            if (function_exists($settings_function)) {
                // Call the addon's settings function
                call_user_func($settings_function, $model_id);
            } else {
                // Show message that addon has no per-model settings
                ?>
                <div class="notice notice-info inline" style="margin: 0;">
                    <p>
                        <strong><?php echo esc_html($addon_name); ?></strong> is active for this model.<br>
                        This addon uses global settings configured in the 
                        <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-addons')); ?>">Add-ons Manager</a>.
                    </p>
                    <p style="font-size: 11px; color: #666;">
                        Debug: Looking for function <code><?php echo esc_html($settings_function); ?></code>
                    </p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}

// Re-enable PHPCS global prefix check
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
